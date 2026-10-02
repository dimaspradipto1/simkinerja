<?php

namespace App\Http\Controllers;

use App\Models\AbsensiSetting;
use App\Models\Kepanitiaan;
use App\Models\KepanitiaanSesiAbsensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class AbsensiSettingController extends Controller
{
    /**
     * Ensure only authorized administrators or leadership can access.
     */
    private function checkAdmin()
    {
        $authUser = Auth::user();
        if (!$authUser || (!$authUser->isAdmin() && !$authUser->isSuperAdmin() && !$authUser->isPimpinanRektorat() && !$authUser->isPimpinanUnit())) {
            abort(403, 'Unauthorized action. Hanya Admin atau Pimpinan yang dapat mengelola status sesi absensi.');
        }
    }

    /**
     * Display a listing of attendance session statuses dynamically linked to Kepanitiaan.
     */
    public function index()
    {
        $this->checkAdmin();
        KepanitiaanSesiAbsensi::deleteWithoutKepanitiaan();

        $kepanitiaans = Kepanitiaan::with(['sesiAbsensis' => function ($q) {
            $q->withCount('presensis')
              ->orderBy('is_active', 'desc')
              ->orderBy('tanggal', 'asc')
              ->orderBy('id', 'asc');
        }])
        ->whereHas('sesiAbsensis')
        ->orderBy('id', 'desc')
        ->get();

        [$activeCount, $totalCount] = $this->sessionCounts();
        $closedCount = $totalCount - $activeCount;

        return view('pages.pengaturan-absensi.index', compact('kepanitiaans', 'activeCount', 'totalCount', 'closedCount'));
    }

    /**
     * Toggle the active status of an attendance session.
     */
    public function toggle(Request $request, $slug)
    {
        $this->checkAdmin();
        KepanitiaanSesiAbsensi::deleteWithoutKepanitiaan();

        $session = null;
        if (is_numeric($slug)) {
            $session = KepanitiaanSesiAbsensi::find((int) $slug);
        }
        if (!$session) {
            $session = KepanitiaanSesiAbsensi::where('legacy_slug', $slug)
                ->orWhere('token', $slug)
                ->first();
        }
        if (!$session && str_starts_with($slug, 'sesi-')) {
            $id = (int) str_replace('sesi-', '', $slug);
            $session = KepanitiaanSesiAbsensi::find($id);
        }

        if ($session) {
            $session->is_active = !$session->is_active;
            $session->save();

            // Sync legacy status if legacy_slug exists
            if (!empty($session->legacy_slug)) {
                AbsensiSetting::setStatus($session->legacy_slug, $session->is_active);
            }

            $statusText = $session->is_active ? 'Aktif (Tampil di Sidebar)' : 'Nonaktif (Tutup)';
            $message = "Status sesi \"{$session->nama_sesi}\" berhasil diubah menjadi {$statusText}.";

            [$activeCount, $totalCount] = $this->sessionCounts();
            $closedCount = $totalCount - $activeCount;

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'is_active' => $session->is_active,
                    'status_text' => $statusText,
                    'message' => $message,
                    'active_count' => $activeCount,
                    'closed_count' => $closedCount,
                    'total_count' => $totalCount,
                ]);
            }

            Alert::success('Berhasil', $message);
            return redirect()->back();
        }

        // Fallback to legacy AbsensiSetting::toggle
        $setting = AbsensiSetting::toggle($slug);

        $statusText = $setting->is_active ? 'Aktif (Tampil di Sidebar)' : 'Nonaktif (Tutup)';
        $message = "Status {$setting->nama} berhasil diubah menjadi {$statusText}.";

        [$activeCount, $totalCount] = $this->sessionCounts();
        $closedCount = $totalCount - $activeCount;

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_active' => $setting->is_active,
                'status_text' => $statusText,
                'message' => $message,
                'active_count' => $activeCount,
                'closed_count' => $closedCount,
                'total_count' => $totalCount,
            ]);
        }

        Alert::success('Berhasil', $message);
        return redirect()->back();
    }

    private function sessionCounts(): array
    {
        $sessions = KepanitiaanSesiAbsensi::whereHas('kepanitiaan', function ($query) {
            $query->where('kategori', 'Kepanitiaan');
        });

        $total = (clone $sessions)->count();
        $active = (clone $sessions)->where('is_active', true)->count();

        return [$active, $total];
    }
}
