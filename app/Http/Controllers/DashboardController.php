<?php

namespace App\Http\Controllers;

use App\Models\Insidentil;
use App\Models\Kepanitiaan;
use App\Models\RencanaKerja;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class DashboardController extends Controller
{
    public function index(Request $request = null)
    {
        $request = $request ?? request();
        $user = auth()->user();

        // Auto-stop tugas kepanitiaan yang telah melewati batas estimasi waktu
        Kepanitiaan::autoStopExpiredTasks();

        $tasksQuery = RencanaKerja::query();
        $kepanitiaanQuery = Kepanitiaan::query();
        $insidentilQuery = Insidentil::query();
        $usersQuery = User::query();

        $isAdminOrLeader = $user && ($user->isAdmin() || $user->isSuperAdmin() || $user->isPimpinanRektorat());

        $userScope = function ($q) use ($user, $isAdminOrLeader) {
            if ($user && !$isAdminOrLeader) {
                $q->where(function ($sub) use ($user) {
                    $sub->where('user_id', $user->id)
                        ->orWhereHas('taggedUsers', function ($qu) use ($user) {
                            $qu->where('users.id', $user->id);
                        });
                });
            }
        };

        if ($user) {
            // Jika bukan admin / rektorat, tampilkan data yang dibuat oleh user login ATAU di mana user login di-tag
            if (!$isAdminOrLeader) {
                $tasksQuery->where($userScope);
                $kepanitiaanQuery->where($userScope);
                $insidentilQuery->where($userScope);

                if ($user->unit) {
                    $usersQuery->where('unit', $user->unit);
                }
            }
        }

        // EdLink-style Weekly Calendar Days (Senin - Minggu)
        $selectedDate = $request->query('selected_date', date('Y-m-d'));
        $todayCarbon = Carbon::parse($selectedDate);
        $startOfWeek = $todayCarbon->copy()->startOfWeek(Carbon::MONDAY);
        
        $weekDays = [];
        $dayNames = [1 => 'Sen', 2 => 'Sel', 3 => 'Rab', 4 => 'Kam', 5 => 'Jum', 6 => 'Sab', 7 => 'Min'];
        for ($i = 0; $i < 7; $i++) {
            $day = $startOfWeek->copy()->addDays($i);
            $dateStr = $day->format('Y-m-d');
            $weekDays[] = [
                'day_name' => $dayNames[$day->dayOfWeekIso] ?? $day->format('D'),
                'day_num' => $day->format('d'),
                'date' => $dateStr,
                'is_selected' => ($dateStr === $selectedDate),
                'is_today' => ($dateStr === date('Y-m-d')),
            ];
        }

        // Query tasks scheduled/active on selected_date from 3 modules
        $getTasksOnDate = function ($queryModel, $kategori, $kategoriCode, $badgeClass) use ($selectedDate, $userScope) {
            return $queryModel->where($userScope)
                ->where(function ($q) use ($selectedDate) {
                    $q->where(function ($qSub) use ($selectedDate) {
                        $qSub->whereDate('estimasi_tanggal_mulai', '<=', $selectedDate)
                             ->whereDate('estimasi_tanggal_selesai', '>=', $selectedDate);
                    })
                    ->orWhereDate('tanggal_mulai', $selectedDate)
                    ->orWhereDate('estimasi_tanggal_mulai', $selectedDate);
                })
                ->with(['user', 'taggedUsers'])
                ->get()
                ->map(function ($item) use ($kategori, $kategoriCode, $badgeClass) {
                    $item->kategori_label = $kategori;
                    $item->kategori_code = $kategoriCode;
                    $item->badge_class = $badgeClass;
                    return $item;
                });
        };

        $schUtama = $getTasksOnDate(RencanaKerja::query(), 'Utama', 'rencana-kerja', 'bg-primary');
        $schKepanitiaan = $getTasksOnDate(Kepanitiaan::query(), 'Kepanitiaan', 'kepanitiaan', 'bg-info text-dark');
        $schInsidentil = $getTasksOnDate(Insidentil::query(), 'Insidentil', 'insidentil', 'bg-warning text-dark');

        $scheduledTasksOnDate = $schUtama->concat($schKepanitiaan)->concat($schInsidentil)
            ->sortBy(function ($item) {
                return ($item->status === 'Proses' || $item->status === 'Berjalan' ? '1_' : ($item->status === 'Belum Dimulai' ? '2_' : '3_')) . ($item->estimasi_jam_mulai ?? '00:00');
            })->values();

        // Search Filter for Rencana Kerja Utama
        if ($request->filled('search_utama')) {
            $search = $request->query('search_utama');
            $tasksQuery->where(function ($q) use ($search) {
                $q->where('uraian_tugas', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($qu) use ($search) {
                      $qu->where('name', 'like', "%{$search}%")
                        ->orWhere('jabatan', 'like', "%{$search}%");
                  });
            });
        }

        // Search Filter for Kepanitiaan
        if ($request->filled('search_kepanitiaan')) {
            $search = $request->query('search_kepanitiaan');
            $kepanitiaanQuery->where(function ($q) use ($search) {
                $q->where('uraian_tugas', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($qu) use ($search) {
                      $qu->where('name', 'like', "%{$search}%")
                        ->orWhere('jabatan', 'like', "%{$search}%");
                  });
            });
        }

        // Search Filter for Insidentil
        if ($request->filled('search_insidentil')) {
            $search = $request->query('search_insidentil');
            $insidentilQuery->where(function ($q) use ($search) {
                $q->where('uraian_tugas', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($qu) use ($search) {
                      $qu->where('name', 'like', "%{$search}%")
                        ->orWhere('jabatan', 'like', "%{$search}%");
                  });
            });
        }

        // Helper ordering by Status priority and Real Date / Estimasi Date
        $applyOrdering = function ($query) {
            return $query->with(['user', 'taggedUsers'])
                ->orderByRaw("CASE WHEN status IN ('Proses', 'Berjalan') THEN 1 WHEN status = 'Belum Dimulai' THEN 2 ELSE 3 END")
                ->orderByRaw("COALESCE(tanggal_mulai, estimasi_tanggal_mulai, created_at) DESC");
        };

        // Rencana Kerja Utama Stats & Paginated List (10 per page)
        $totalTugas = (clone $tasksQuery)->count();
        $tugasBelumMulai = (clone $tasksQuery)->where('status', 'Belum Dimulai')->count();
        $tugasProses = (clone $tasksQuery)->whereIn('status', ['Proses', 'Berjalan'])->count();
        $tugasSelesai = (clone $tasksQuery)->where('status', 'Selesai')->count();
        $persentaseSelesai = $totalTugas > 0 ? round(($tugasSelesai / $totalTugas) * 100) : 0;
        $recentTasks = $applyOrdering(clone $tasksQuery)->paginate(10, ['*'], 'page_utama')->withQueryString();

        // Kepanitiaan Stats & Paginated List (10 per page)
        $totalKepanitiaan = (clone $kepanitiaanQuery)->count();
        $kepanitiaanSelesai = (clone $kepanitiaanQuery)->where('status', 'Selesai')->count();
        $recentKepanitiaan = $applyOrdering(clone $kepanitiaanQuery)->paginate(10, ['*'], 'page_kepanitiaan')->withQueryString();

        // Insidentil Stats & Paginated List (10 per page)
        $totalInsidentil = (clone $insidentilQuery)->count();
        $insidentilSelesai = (clone $insidentilQuery)->where('status', 'Selesai')->count();
        $recentInsidentil = $applyOrdering(clone $insidentilQuery)->paginate(10, ['*'], 'page_insidentil')->withQueryString();

        $totalPegawai = $usersQuery->count();

        // Ambil hanya kepanitiaan yang memiliki sesi absensi AKTIF (sesuai dengan yang tampil di sidebar)
        $dynamicKepanitiaanList = Kepanitiaan::whereHas('sesiAbsensis', function ($query) {
                $query->where('is_active', true);
            })
            ->with(['sesiAbsensis' => function ($query) {
                $query->where('is_active', true)->orderBy('tanggal', 'asc')->orderBy('id', 'asc');
            }])
            ->orderBy('id', 'asc')
            ->get();

        // Ambil semua sesi absensi aktif secara flat untuk tab switcher monitoring
        $activeSessions = \App\Models\KepanitiaanSesiAbsensi::where('is_active', true)
            ->with('kepanitiaan')
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'asc')
            ->get();

        $defaultSession = $activeSessions->first();

        return view('layouts.dashboard.index', compact(
            'totalTugas',
            'tugasBelumMulai',
            'tugasProses',
            'tugasSelesai',
            'persentaseSelesai',
            'recentTasks',
            'totalKepanitiaan',
            'kepanitiaanSelesai',
            'recentKepanitiaan',
            'totalInsidentil',
            'insidentilSelesai',
            'recentInsidentil',
            'totalPegawai',
            'weekDays',
            'selectedDate',
            'scheduledTasksOnDate',
            'dynamicKepanitiaanList',
            'activeSessions',
            'defaultSession',
            'isAdminOrLeader'
        ));
    }

    /**
     * AJAX endpoint for real-time Kepanitiaan Attendance DataTables.
     */
    public function getAttendanceData(Request $request)
    {
        $sesiId = $request->query('sesi_id');

        if ($sesiId) {
            $sesi = \App\Models\KepanitiaanSesiAbsensi::find($sesiId);
        } else {
            $sesi = \App\Models\KepanitiaanSesiAbsensi::where('is_active', true)
                ->orderBy('tanggal', 'desc')
                ->orderBy('id', 'asc')
                ->first();
        }

        // Fallback jika tidak ada sesi aktif
        if (!$sesi) {
            $sesi = \App\Models\KepanitiaanSesiAbsensi::latest('id')->first();
        }

        $sesiActiveId = $sesi ? $sesi->id : 0;

        $authUser = auth()->user();
        $canViewAll = false;

        if ($authUser) {
            $isSuperAdmin = $authUser->isSuperAdmin();
            $isAdmin = $authUser->isAdmin();
            $isRektor = $authUser->hasRole('REKTOR') 
                     || $authUser->hasRole('rektor') 
                     || (trim(strtoupper($authUser->jabatan ?? '')) === 'REKTOR');

            $canViewAll = $isSuperAdmin || $isAdmin || $isRektor;
        }

        $usersQuery = User::query()
            ->leftJoin('kepanitiaan_presensis', function ($join) use ($sesiActiveId) {
                $join->on('users.id', '=', 'kepanitiaan_presensis.user_id')
                     ->where('kepanitiaan_presensis.kepanitiaan_sesi_absensi_id', '=', $sesiActiveId);
            })
            ->select([
                'users.id as id',
                'users.name as name',
                'users.nidn as nidn',
                'users.unit as unit',
                'users.jabatan as jabatan',
                'kepanitiaan_presensis.hadir_datang as hadir_datang',
                'kepanitiaan_presensis.waktu_datang as waktu_datang',
                'kepanitiaan_presensis.hadir_pulang as hadir_pulang',
                'kepanitiaan_presensis.waktu_pulang as waktu_pulang',
            ]);

        if ($canViewAll) {
            $excludedUserIds = User::all()
                ->filter(fn($u) => $u->isAdmin() || $u->isSuperAdmin())
                ->pluck('id');

            $usersQuery->whereNotIn('users.id', $excludedUserIds);
        } else {
            $usersQuery->where('users.id', $authUser ? $authUser->id : 0);
        }

        // Urutkan yang sudah hadir/absen di atas agar langsung terlihat sesuai sesi yang dipilih
        $usersQuery->orderByRaw("CASE WHEN (kepanitiaan_presensis.hadir_datang IS NOT NULL OR kepanitiaan_presensis.hadir_pulang IS NOT NULL) THEN 0 ELSE 1 END")
                   ->orderBy('users.name', 'asc');

        return DataTables::of($usersQuery)
            ->addIndexColumn()
            ->editColumn('name', function ($user) {
                return '<div>'
                     . '<div class="fw-bold text-dark" style="font-size: 0.92rem;">' . e($user->name) . '</div>'
                     . '<small class="text-muted" style="font-size: 0.76rem;">' . e($user->jabatan ?? '-') . ' &bull; ' . e($user->unit ?? '-') . '</small>'
                     . '</div>';
            })
            ->addColumn('datang', function ($user) {
                $status = $user->hadir_datang;
                $waktu = $user->waktu_datang ? '<div class="small text-muted mt-0.5"><i class="bi bi-clock me-1"></i>' . substr($user->waktu_datang, 0, 5) . ' WIB</div>' : '';

                if ($status === 'Hadir') {
                    return '<span class="badge bg-success">Hadir</span>' . $waktu;
                } elseif ($status === 'Izin') {
                    return '<span class="badge bg-warning text-dark">Izin</span>' . $waktu;
                } elseif ($status === 'Sakit') {
                    return '<span class="badge bg-info text-dark">Sakit</span>' . $waktu;
                } elseif ($status === 'Alpha' || $status === 'Alpa') {
                    return '<span class="badge bg-danger">Alpha</span>' . $waktu;
                }
                return '<span class="badge bg-secondary">Belum Absen</span>';
            })
            ->addColumn('pulang', function ($user) {
                $status = $user->hadir_pulang;
                $waktu = $user->waktu_pulang ? '<div class="small text-muted mt-0.5"><i class="bi bi-clock me-1"></i>' . substr($user->waktu_pulang, 0, 5) . ' WIB</div>' : '';

                if ($status === 'Hadir') {
                    return '<span class="badge bg-success">Hadir</span>' . $waktu;
                } elseif ($status === 'Izin') {
                    return '<span class="badge bg-warning text-dark">Izin</span>' . $waktu;
                } elseif ($status === 'Sakit') {
                    return '<span class="badge bg-info text-dark">Sakit</span>' . $waktu;
                } elseif ($status === 'Alpha' || $status === 'Alpa') {
                    return '<span class="badge bg-danger">Alpha</span>' . $waktu;
                }
                return '<span class="badge bg-secondary">Belum Absen</span>';
            })
            ->rawColumns(['name', 'datang', 'pulang'])
            ->make(true);
    }
}
