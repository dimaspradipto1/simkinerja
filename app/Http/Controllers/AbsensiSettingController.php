<?php

namespace App\Http\Controllers;

use App\Models\AbsensiSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class AbsensiSettingController extends Controller
{
    /**
     * Ensure only authorized administrators can access.
     */
    private function checkAdmin()
    {
        $authUser = Auth::user();
        if (!$authUser || (!$authUser->isAdmin() && !$authUser->isSuperAdmin())) {
            abort(403, 'Unauthorized action. Hanya Admin atau Super Admin yang dapat mengelola status sesi absensi.');
        }
    }

    /**
     * Display a listing of attendance session statuses.
     */
    public function index()
    {
        $this->checkAdmin();

        $settings = AbsensiSetting::getAllGrouped();

        return view('pages.pengaturan-absensi.index', compact('settings'));
    }

    /**
     * Toggle the active status of an attendance session.
     */
    public function toggle(Request $request, $slug)
    {
        $this->checkAdmin();

        $setting = AbsensiSetting::toggle($slug);

        $statusText = $setting->is_active ? 'Aktif' : 'Nonaktif (Tutup)';
        $message = "Status {$setting->nama} berhasil diubah menjadi {$statusText}.";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_active' => $setting->is_active,
                'status_text' => $statusText,
                'message' => $message,
            ]);
        }

        Alert::success('Berhasil', $message);
        return redirect()->back();
    }
}
