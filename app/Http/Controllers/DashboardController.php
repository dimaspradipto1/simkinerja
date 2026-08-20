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

        $tasksQuery = RencanaKerja::query();
        $kepanitiaanQuery = Kepanitiaan::query();
        $insidentilQuery = Insidentil::query();
        $usersQuery = User::query();

        $userScope = function ($q) use ($user) {
            if ($user) {
                $q->where(function ($sub) use ($user) {
                    $sub->where('user_id', $user->id)
                        ->orWhereHas('taggedUsers', function ($qu) use ($user) {
                            $qu->where('users.id', $user->id);
                        });
                });
            }
        };

        if ($user) {
            // Tampilkan data yang dibuat oleh user login ATAU di mana user login di-tag (rekan kerja)
            $tasksQuery->where($userScope);
            $kepanitiaanQuery->where($userScope);
            $insidentilQuery->where($userScope);

            if ($user->unit) {
                $usersQuery->where('unit', $user->unit);
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

        // Data Pegawai yang sudah absen untuk masing-masing kepanitiaan (Datang & Pulang)
        $kepanitiaanAbsensiList = [
            'pkkmb' => [
                'nama' => 'Kepanitiaan PKKMB',
                'badge_color' => 'bg-success',
                'text_color' => 'text-success',
                'border_color' => '#198754',
                'icon' => 'bi-mortarboard-fill',
                'jabatan_key' => 'jabatan_pkkmb',
                'sessions' => [
                    [
                        'id' => 'pkkmb_1',
                        'title' => 'Hari Ke-1',
                        'route' => route('absensi-pkkmb-pertama.index'),
                        'records' => \App\Models\AbsensiPkkmbPertama::with('user')->orderBy('id', 'desc')->get(),
                    ],
                    [
                        'id' => 'pkkmb_2',
                        'title' => 'Hari Ke-2',
                        'route' => route('absensi-pkkmb-kedua.index'),
                        'records' => \App\Models\AbsensiPkkmbKedua::with('user')->orderBy('id', 'desc')->get(),
                    ],
                    [
                        'id' => 'pkkmb_3',
                        'title' => 'Hari Ke-3',
                        'route' => route('absensi-pkkmb-ketiga.index'),
                        'records' => \App\Models\AbsensiPkkmbKetiga::with('user')->orderBy('id', 'desc')->get(),
                    ],
                ],
            ],
            'esq' => [
                'nama' => 'Kepanitiaan ESQ',
                'badge_color' => 'bg-primary',
                'text_color' => 'text-primary',
                'border_color' => '#0d6efd',
                'icon' => 'bi-heart-pulse-fill',
                'jabatan_key' => 'jabatan_esq',
                'sessions' => [
                    [
                        'id' => 'esq_1',
                        'title' => 'Hari Ke-1',
                        'route' => route('absensi-esq-pertama.index'),
                        'records' => \App\Models\AbsensiEsqPertama::with('user')->orderBy('id', 'desc')->get(),
                    ],
                    [
                        'id' => 'esq_2',
                        'title' => 'Hari Ke-2',
                        'route' => route('absensi-esq-kedua.index'),
                        'records' => \App\Models\AbsensiEsqKedua::with('user')->orderBy('id', 'desc')->get(),
                    ],
                    [
                        'id' => 'esq_3',
                        'title' => 'Hari Ke-3',
                        'route' => route('absensi-esq-ketiga.index'),
                        'records' => \App\Models\AbsensiEsqKetiga::with('user')->orderBy('id', 'desc')->get(),
                    ],
                ],
            ],
            'milad' => [
                'nama' => 'Kepanitiaan MILAD',
                'badge_color' => 'bg-warning text-dark',
                'text_color' => 'text-warning',
                'border_color' => '#ffc107',
                'icon' => 'bi-stars',
                'jabatan_key' => 'jabatan_milad',
                'sessions' => [
                    [
                        'id' => 'milad_1',
                        'title' => 'Hari Ke-1',
                        'route' => route('absensi-milad-pertama.index'),
                        'records' => \App\Models\AbsensiMiladPertama::with('user')->orderBy('id', 'desc')->get(),
                    ],
                    [
                        'id' => 'milad_2',
                        'title' => 'Hari Ke-2',
                        'route' => route('absensi-milad-kedua.index'),
                        'records' => \App\Models\AbsensiMiladKedua::with('user')->orderBy('id', 'desc')->get(),
                    ],
                ],
            ],
            'kuliah_umum' => [
                'nama' => 'Kepanitiaan Kuliah Umum',
                'badge_color' => 'bg-info text-dark',
                'text_color' => 'text-info',
                'border_color' => '#0dcaf0',
                'icon' => 'bi-book-half',
                'jabatan_key' => 'jabatan_kuliah_umum',
                'sessions' => [
                    [
                        'id' => 'kuliah_umum_1',
                        'title' => 'Kuliah Umum',
                        'route' => route('absensi-kuliah-umum-pertama.index'),
                        'records' => \App\Models\AbsensiKuliahUmumPertama::with('user')->orderBy('id', 'desc')->get(),
                    ],
                ],
            ],
        ];

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
            'kepanitiaanAbsensiList'
        ));
    }

    /**
     * AJAX endpoint for real-time Kepanitiaan Attendance DataTables.
     */
    public function getAttendanceData(Request $request)
    {
        $kepanitiaan = $request->query('kepanitiaan', 'pkkmb');
        $day = (int) $request->query('day', 1);

        $modelClass = match($kepanitiaan) {
            'pkkmb' => match($day) {
                1 => \App\Models\AbsensiPkkmbPertama::class,
                2 => \App\Models\AbsensiPkkmbKedua::class,
                default => \App\Models\AbsensiPkkmbKetiga::class,
            },
            'esq' => match($day) {
                1 => \App\Models\AbsensiEsqPertama::class,
                2 => \App\Models\AbsensiEsqKedua::class,
                default => \App\Models\AbsensiEsqKetiga::class,
            },
            'milad' => match($day) {
                1 => \App\Models\AbsensiMiladPertama::class,
                default => \App\Models\AbsensiMiladKedua::class,
            },
            'kuliah_umum' => \App\Models\AbsensiKuliahUmumPertama::class,
            default => \App\Models\AbsensiPkkmbPertama::class,
        };

        // Get all attendances keyed by user_id
        $attendances = $modelClass::all()->keyBy('user_id');

        $authUser = auth()->user();
        $canViewAll = false;

        if ($authUser) {
            $isSuperAdmin = $authUser->isSuperAdmin();
            $isAdmin = $authUser->isAdmin();
            $isRektor = $authUser->hasRole('REKTOR') 
                     || $authUser->hasRole('rektor') 
                     || (trim(strtoupper($authUser->jabatan ?? '')) === 'REKTOR');

            // HANYA Rektor, Super Admin, dan Admin yang dapat melihat daftar seluruh pegawai
            $canViewAll = $isSuperAdmin || $isAdmin || $isRektor;
        }

        if ($canViewAll) {
            // Rektor, Super Admin, dan Admin dapat melihat seluruh pegawai (kecuali akun admin/superadmin)
            $excludedUserIds = User::all()
                ->filter(fn($u) => $u->isAdmin() || $u->isSuperAdmin())
                ->pluck('id');

            $usersQuery = User::select('id', 'name', 'nidn', 'unit', 'jabatan')
                ->whereNotIn('id', $excludedUserIds);
        } else {
            // Selain Rektor, Super Admin, dan Admin (termasuk Wakil Rektor, Dekan, Staff, dll) HANYA menampilkan absensi dirinya sendiri
            $usersQuery = User::select('id', 'name', 'nidn', 'unit', 'jabatan')
                ->where('id', $authUser ? $authUser->id : 0);
        }

        return DataTables::of($usersQuery)
            ->addIndexColumn()
            ->editColumn('name', function ($user) {
                return '<span class="fw-bold text-dark" style="font-size: 0.95rem;">' . e($user->name) . '</span>';
            })
            ->addColumn('datang', function ($user) use ($attendances) {
                $att = $attendances->get($user->id);
                $status = $att ? $att->hadir_datang : null;
                $waktu = $att && $att->waktu_datang ? ' (' . substr($att->waktu_datang, 0, 5) . ' WIB)' : '';

                if ($status === 'Hadir') {
                    return '<span class="badge rounded px-2 py-1 shadow-xs" style="background-color: #198754; color: white; font-weight: 700; font-size: 0.85rem; min-width: 28px; display: inline-block; text-align: center;" title="Hadir' . $waktu . '">H</span>';
                } elseif ($status === 'Izin') {
                    return '<span class="badge rounded px-2 py-1 shadow-xs" style="background-color: #ffc107; color: #000; font-weight: 700; font-size: 0.85rem; min-width: 28px; display: inline-block; text-align: center;" title="Izin' . $waktu . '">I</span>';
                } elseif ($status === 'Sakit') {
                    return '<span class="badge rounded px-2 py-1 shadow-xs" style="background-color: #0dcaf0; color: #fff; font-weight: 700; font-size: 0.85rem; min-width: 28px; display: inline-block; text-align: center;" title="Sakit' . $waktu . '">S</span>';
                } elseif ($status === 'Alpa') {
                    return '<span class="badge rounded px-2 py-1 shadow-xs" style="background-color: #dc3545; color: #fff; font-weight: 700; font-size: 0.85rem; min-width: 28px; display: inline-block; text-align: center;" title="Alpa">A</span>';
                }
                return '<span class="badge rounded px-2 py-1 border" style="background-color: #f8fafc; color: #94a3b8; font-weight: 600; font-size: 0.85rem; min-width: 28px; display: inline-block; text-align: center; border-color: #e2e8f0 !important;" title="Belum Absen">B</span>';
            })
            ->addColumn('pulang', function ($user) use ($attendances) {
                $att = $attendances->get($user->id);
                $status = $att ? $att->hadir_pulang : null;
                $waktu = $att && $att->waktu_pulang ? ' (' . substr($att->waktu_pulang, 0, 5) . ' WIB)' : '';

                if ($status === 'Hadir') {
                    return '<span class="badge rounded px-2 py-1 shadow-xs" style="background-color: #198754; color: white; font-weight: 700; font-size: 0.85rem; min-width: 28px; display: inline-block; text-align: center;" title="Hadir Pulang' . $waktu . '">H</span>';
                } elseif ($status === 'Izin') {
                    return '<span class="badge rounded px-2 py-1 shadow-xs" style="background-color: #ffc107; color: #000; font-weight: 700; font-size: 0.85rem; min-width: 28px; display: inline-block; text-align: center;" title="Izin' . $waktu . '">I</span>';
                } elseif ($status === 'Sakit') {
                    return '<span class="badge rounded px-2 py-1 shadow-xs" style="background-color: #0dcaf0; color: #fff; font-weight: 700; font-size: 0.85rem; min-width: 28px; display: inline-block; text-align: center;" title="Sakit' . $waktu . '">S</span>';
                } elseif ($status === 'Alpa') {
                    return '<span class="badge rounded px-2 py-1 shadow-xs" style="background-color: #dc3545; color: #fff; font-weight: 700; font-size: 0.85rem; min-width: 28px; display: inline-block; text-align: center;" title="Alpa">A</span>';
                }
                return '<span class="badge rounded px-2 py-1 border" style="background-color: #f8fafc; color: #94a3b8; font-weight: 600; font-size: 0.85rem; min-width: 28px; display: inline-block; text-align: center; border-color: #e2e8f0 !important;" title="Belum Pulang">B</span>';
            })
            ->rawColumns(['name', 'datang', 'pulang'])
            ->make(true);
    }
}
