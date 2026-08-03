<?php

namespace App\Http\Controllers;

use App\Models\Kepanitiaan;
use App\Models\PeriodeAkademik;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class RekapitulasiKepanitiaanController extends Controller
{
    /**
     * Display the Rekapitulasi page for Kepanitiaan.
     */
    public function index()
    {
        $authUser = Auth::user();
        if (!$authUser) {
            return redirect()->route('login');
        }

        $periodeAkademiks = PeriodeAkademik::orderBy('nama_periode', 'desc')->get();
        $defaultPeriodeId = null;

        $usersWithJabatan = [];
        if ($authUser->isAdmin() || $authUser->isPimpinanRektorat()) {
            $usersWithJabatan = User::whereNotNull('jabatan')
                ->where('jabatan', '!=', '')
                ->orderBy('jabatan', 'asc')
                ->get();
        } elseif ($authUser->isPimpinanUnit()) {
            $usersWithJabatan = User::whereNotNull('jabatan')
                ->where('jabatan', '!=', '')
                ->where('unit', $authUser->unit)
                ->orderBy('jabatan', 'asc')
                ->get();
        }

        return view('pages.rekapitulasi-kepanitiaan.index', compact('periodeAkademiks', 'defaultPeriodeId', 'usersWithJabatan'));
    }

    /**
     * Get DataTable source and overall rekap counts for Kepanitiaan.
     */
    public function getData(Request $request)
    {
        $authUser = Auth::user();
        if (!$authUser) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $query = Kepanitiaan::with(['user', 'periodeAkademik', 'taggedUsers']);

        if ($authUser->isAdmin() || $authUser->isPimpinanRektorat()) {
            // Seluruh data
        } elseif ($authUser->isPimpinanUnit()) {
            $query->where(function ($q) use ($authUser) {
                $q->whereHas('user', function ($qu) use ($authUser) {
                    $qu->where('unit', $authUser->unit);
                })->orWhereHas('taggedUsers', function ($qt) use ($authUser) {
                    $qt->where('users.id', $authUser->id);
                });
            });
        } else {
            $query->where(function ($q) use ($authUser) {
                $q->where('user_id', $authUser->id)
                  ->orWhereHas('taggedUsers', function ($qu) use ($authUser) {
                      $qu->where('users.id', $authUser->id);
                  });
            });
        }

        if ($request->filled('periode_akademik_id')) {
            $query->where('periode_akademik_id', $request->periode_akademik_id);
        }

        if ($request->filled('jabatan')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('user', function ($qu) use ($request) {
                    $qu->where('jabatan', $request->jabatan);
                })->orWhereHas('taggedUsers', function ($qt) use ($request) {
                    $qt->where('jabatan', $request->jabatan);
                });
            });
        }

        // Calculate Rekapitulasi counts based on the filtered query
        $total = (clone $query)->count();
        $belum = (clone $query)->where('status', 'Belum Dimulai')->count();
        $proses = (clone $query)->whereIn('status', ['Proses', 'Berjalan'])->count();
        $selesai = (clone $query)->where('status', 'Selesai')->count();
        $percent = $total > 0 ? round(($selesai / $total) * 100) : 0;

        return DataTables::of($query)
            ->addColumn('pembuat', function ($row) {
                return $row->user ? $row->user->name . ' (' . ($row->user->jabatan ?? '-') . ')' : '-';
            })
            ->addColumn('rekan_kerja', function ($row) {
                if ($row->taggedUsers->count() > 0) {
                    return $row->taggedUsers->pluck('name')->implode(', ');
                }
                return '-';
            })
            ->addColumn('status_badge', function ($row) {
                if ($row->status === 'Selesai') {
                    return '<span class="badge bg-success px-2 py-1"><i class="bi bi-check-circle me-1"></i>Selesai</span>';
                } elseif ($row->status === 'Proses' || $row->status === 'Berjalan') {
                    return '<span class="badge bg-primary px-2 py-1"><i class="bi bi-play-circle me-1"></i>Proses</span>';
                }
                return '<span class="badge bg-secondary px-2 py-1"><i class="bi bi-x-circle me-1"></i>Belum Dimulai</span>';
            })
            ->rawColumns(['status_badge'])
            ->with('rekap', [
                'total' => $total,
                'belum' => $belum,
                'proses' => $proses,
                'selesai' => $selesai,
                'percent' => $percent
            ])
            ->make(true);
    }
}
