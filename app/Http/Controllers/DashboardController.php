<?php

namespace App\Http\Controllers;

use App\Models\Insidentil;
use App\Models\Kepanitiaan;
use App\Models\RencanaKerja;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
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

        // Pimpinan Rektorat murni (Rektor / Warek) atau Pure Admin tanpa bawahan OTK
        $jabatanUpper = strtoupper(trim($user->jabatan ?? ''));
        $unitUpper = strtoupper(trim($user->unit ?? ''));
        $isRealRektorat = in_array($jabatanUpper, ['REKTOR', 'WAKIL REKTOR I', 'WAKIL REKTOR II', 'WAKIL REKTOR III'])
            || $unitUpper === 'REKTORAT';
        $isPureAdmin = $user && $user->isSystemAdmin() && !$user->subordinates()->exists();
        $isCampusWide = $isRealRektorat || $isPureAdmin;

        // Tentukan daftar pegawai yang bernaung di bawah pimpinan sesuai struktur OTK (sinkron dengan Pemantauan Staff)
        $otkUserIds = null;
        if ($user) {
            if ($isCampusWide) {
                // Rektorat / Pure Admin mengawasi seluruh universitas
                $otkUserIds = null;
            } else {
                // Tentukan pimpinan tim OTK terkait (baik jika user adalah pimpinan, maupun jika user adalah anggota tim)
                $targetSupervisorId = null;
                if ($user->subordinates()->exists()) {
                    $targetSupervisorId = $user->id;
                } elseif ($user->supervisor_id) {
                    $targetSupervisorId = $user->supervisor_id;
                }

                if ($targetSupervisorId) {
                    $pimpinan = User::find($targetSupervisorId);
                    $subs = $pimpinan ? $pimpinan->getAllSubordinateIds() : [];
                    // Ambil anggota tim yang terdaftar pada OTK
                    $otkUserIds = User::where('users.is_active', true)
                        ->join('otk_members', 'users.id', '=', 'otk_members.user_id')
                        ->whereIn('users.id', $subs)
                        ->pluck('users.id')
                        ->map(fn($id) => (int)$id)
                        ->all();
                } elseif ($user->unit) {
                    $uName = trim($user->unit);
                    $otkUserIds = User::where('is_active', true)
                        ->where(function ($q) use ($uName) {
                            $q->where('unit', $uName)->orWhere('unit', 'like', "%{$uName}%");
                        })
                        ->pluck('id')
                        ->map(fn($id) => (int)$id)
                        ->all();
                } else {
                    $otkUserIds = [(int) $user->id];
                }
            }
        }

        // Scope untuk tabel kegiatan dashboard: hanya menampilkan tugas milik pengguna yang sedang login
        $personalScope = function ($q) use ($user) {
            if ($user) {
                $q->where(function ($sub) use ($user) {
                    $sub->where('user_id', $user->id)
                        ->orWhereHas('taggedUsers', function ($qu) use ($user) {
                            $qu->where('users.id', $user->id);
                        });
                });
            }
        };

        $tasksQuery->where($personalScope);
        $kepanitiaanQuery->where($personalScope);
        $insidentilQuery->where($personalScope);

        if ($user && !$isCampusWide && !empty($otkUserIds)) {
            $usersQuery->whereIn('id', $otkUserIds);
        }

        $isAdminOrLeader = $isCampusWide;

        // EdLink-style Weekly Calendar Days (Senin - Minggu)
        Carbon::setLocale('id');
        $selectedDate = $request->query('selected_date', date('Y-m-d'));
        $todayCarbon = Carbon::parse($selectedDate);
        $startOfWeek = $todayCarbon->copy()->startOfWeek(Carbon::MONDAY);
        $endOfWeek = $startOfWeek->copy()->endOfWeek(Carbon::SUNDAY);
        $todayFormatted = Carbon::now()->translatedFormat('d F Y');

        // Filter Jadwal Minggu Ini khusus untuk user yang sedang login (masing-masing pengguna melihat kegiatan pribadinya)
        $personalScope = function ($q) use ($user) {
            if ($user) {
                $q->where(function ($sub) use ($user) {
                    $sub->where('user_id', $user->id)
                        ->orWhereHas('taggedUsers', function ($qu) use ($user) {
                            $qu->where('users.id', $user->id);
                        });
                });
            } else {
                $q->whereRaw('1 = 0');
            }
        };

        // Pre-query all tasks for the entire week from the 3 modules in one batch
        $getWeeklyTasks = function ($queryModel, $kategori, $kategoriCode, $badgeClass) use ($startOfWeek, $endOfWeek, $personalScope) {
            return $queryModel->where($personalScope)
                ->where(function ($q) use ($startOfWeek, $endOfWeek) {
                    $q->whereBetween('estimasi_tanggal_mulai', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
                      ->orWhereBetween('estimasi_tanggal_selesai', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
                      ->orWhereBetween('tanggal_mulai', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
                      ->orWhere(function ($qSub) use ($startOfWeek, $endOfWeek) {
                          $qSub->whereDate('estimasi_tanggal_mulai', '<=', $startOfWeek->format('Y-m-d'))
                               ->whereDate('estimasi_tanggal_selesai', '>=', $endOfWeek->format('Y-m-d'));
                      });
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

        $allWeeklyTasks = $getWeeklyTasks(RencanaKerja::query(), 'Rutinitas', 'rencana-kerja', 'bg-success')
            ->concat($getWeeklyTasks(Kepanitiaan::query(), 'Kepanitiaan', 'kepanitiaan', 'bg-info text-dark'))
            ->concat($getWeeklyTasks(Insidentil::query(), 'Insidentil', 'insidentil', 'bg-warning text-dark'));

        $weekDays = [];
        $scheduledTasksOnDate = collect();
        $dayNames = [1 => 'Sen', 2 => 'Sel', 3 => 'Rab', 4 => 'Kam', 5 => 'Jum', 6 => 'Sab', 7 => 'Min'];
        for ($i = 0; $i < 7; $i++) {
            $day = $startOfWeek->copy()->addDays($i);
            $dateStr = $day->format('Y-m-d');

            // Filter tasks active on this specific day
            $tasksOnThisDay = $allWeeklyTasks->filter(function ($task) use ($dateStr) {
                $start = $task->estimasi_tanggal_mulai ?? $task->tanggal_mulai;
                $end = $task->estimasi_tanggal_selesai ?? $start;
                if ($start && $end) {
                    return $dateStr >= $start && $dateStr <= $end;
                }
                return $task->tanggal_mulai === $dateStr || $task->estimasi_tanggal_mulai === $dateStr;
            })->sortBy(function ($item) {
                return ($item->status === 'Proses' || $item->status === 'Berjalan' ? '1_' : ($item->status === 'Belum Dimulai' ? '2_' : '3_')) . ($item->estimasi_jam_mulai ?? '00:00');
            })->values();

            $isSelected = ($dateStr === $selectedDate);
            if ($isSelected) {
                $scheduledTasksOnDate = $tasksOnThisDay;
            }

            $weekDays[] = [
                'day_name' => $dayNames[$day->dayOfWeekIso] ?? $day->format('D'),
                'day_num' => $day->format('d'),
                'date' => $dateStr,
                'formatted_date' => $day->translatedFormat('l, d F Y'),
                'has_tasks' => $tasksOnThisDay->isNotEmpty(),
                'tasks_count' => $tasksOnThisDay->count(),
                'tasks' => $tasksOnThisDay,
                'is_selected' => $isSelected,
                'is_today' => ($dateStr === date('Y-m-d')),
            ];
        }

        // Stats counts (berdasarkan data pengguna / personal scope)
        $totalTugas = (clone $tasksQuery)->count();
        $tugasBelumMulai = (clone $tasksQuery)->where('status', 'Belum Dimulai')->count();
        $tugasProses = (clone $tasksQuery)->whereIn('status', ['Proses', 'Berjalan'])->count();
        $tugasSelesai = (clone $tasksQuery)->where('status', 'Selesai')->count();
        $persentaseSelesai = $totalTugas > 0 ? round(($tugasSelesai / $totalTugas) * 100) : 0;

        $totalKepanitiaan = (clone $kepanitiaanQuery)->count();
        $kepanitiaanSelesai = (clone $kepanitiaanQuery)->where('status', 'Selesai')->count();

        $totalInsidentil = (clone $insidentilQuery)->count();
        $insidentilSelesai = (clone $insidentilQuery)->where('status', 'Selesai')->count();

        $totalPegawai = $usersQuery->count();

        // -------------------------------------------------------------
        // Unified Table: Gabungan Rutinitas, Kepanitiaan, dan Insidentil
        // -------------------------------------------------------------
        $searchKegiatan = $request->query('search_kegiatan', $request->query('search_utama', $request->query('search_kepanitiaan', $request->query('search_insidentil'))));
        $filterKategori = $request->query('filter_kategori', 'all');
        $filterStatus = $request->query('filter_status', 'all');

        $applySearch = function ($query) use ($searchKegiatan) {
            if (!empty($searchKegiatan)) {
                $query->where(function ($q) use ($searchKegiatan) {
                    $q->where('uraian_tugas', 'like', "%{$searchKegiatan}%")
                      ->orWhereHas('user', function ($qu) use ($searchKegiatan) {
                          $qu->where('name', 'like', "%{$searchKegiatan}%")
                            ->orWhere('jabatan', 'like', "%{$searchKegiatan}%");
                      });
                });
            }
        };

        $applyStatusFilter = function ($query) use ($filterStatus) {
            if (!empty($filterStatus) && $filterStatus !== 'all') {
                if ($filterStatus === 'Proses') {
                    $query->whereIn('status', ['Proses', 'Berjalan']);
                } else {
                    $query->where('status', $filterStatus);
                }
            }
        };

        $allTasksList = collect();

        // 1. Modul Rutinitas
        if (in_array($filterKategori, ['all', 'rutinitas'])) {
            $qRutinitas = clone $tasksQuery;
            $applySearch($qRutinitas);
            $applyStatusFilter($qRutinitas);
            $rutinitasItems = $qRutinitas->with(['user', 'taggedUsers'])->get()->map(function ($item) {
                $item->kategori_type = 'rutinitas';
                $item->module_type = 'rencana-kerja';
                $item->kategori_label = 'Rutinitas';
                $item->kategori_badge_class = 'bg-success-subtle text-success border border-success-subtle';
                $item->kategori_icon = 'bi-journal-bookmark';
                return $item;
            });
            $allTasksList = $allTasksList->concat($rutinitasItems);
        }

        // 2. Modul Kepanitiaan
        if (in_array($filterKategori, ['all', 'kepanitiaan'])) {
            $qKepanitiaan = clone $kepanitiaanQuery;
            $applySearch($qKepanitiaan);
            $applyStatusFilter($qKepanitiaan);
            $kepanitiaanItems = $qKepanitiaan->with(['user', 'taggedUsers'])->get()->map(function ($item) {
                $item->kategori_type = 'kepanitiaan';
                $item->module_type = 'kepanitiaan';
                $item->kategori_label = 'Kepanitiaan';
                $item->kategori_badge_class = 'bg-primary-subtle text-primary border border-primary-subtle';
                $item->kategori_icon = 'bi-people';
                return $item;
            });
            $allTasksList = $allTasksList->concat($kepanitiaanItems);
        }

        // 3. Modul Insidentil
        if (in_array($filterKategori, ['all', 'insidentil'])) {
            $qInsidentil = clone $insidentilQuery;
            $applySearch($qInsidentil);
            $applyStatusFilter($qInsidentil);
            $insidentilItems = $qInsidentil->with(['user', 'taggedUsers'])->get()->map(function ($item) {
                $item->kategori_type = 'insidentil';
                $item->module_type = 'insidentil';
                $item->kategori_label = 'Insidentil';
                $item->kategori_badge_class = 'bg-warning-subtle text-dark border border-warning-subtle';
                $item->kategori_icon = 'bi-lightning-charge';
                return $item;
            });
            $allTasksList = $allTasksList->concat($insidentilItems);
        }

        // Urutkan gabungan tugas: 1. Proses/Berjalan, 2. Belum Dimulai, 3. Selesai, lalu tanggal terbaru
        $sortedAllTasks = $allTasksList->sort(function ($a, $b) {
            $statusRank = function ($s) {
                if (in_array($s, ['Proses', 'Berjalan'])) return 1;
                if ($s === 'Belum Dimulai') return 2;
                return 3;
            };
            $rankA = $statusRank($a->status);
            $rankB = $statusRank($b->status);
            if ($rankA !== $rankB) {
                return $rankA <=> $rankB;
            }
            $dateA = $a->tanggal_mulai ?? $a->estimasi_tanggal_mulai ?? $a->created_at;
            $dateB = $b->tanggal_mulai ?? $b->estimasi_tanggal_mulai ?? $b->created_at;
            return $dateB <=> $dateA;
        })->values();

        // Paginasi gabungan (10 item per halaman)
        $perPage = 10;
        $currentPage = (int) $request->input('page', 1);
        $totalItems = $sortedAllTasks->count();
        $pagedSlice = $sortedAllTasks->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $allTasks = new LengthAwarePaginator(
            $pagedSlice,
            $totalItems,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $recentTasks = $allTasks;
        $recentKepanitiaan = $allTasks;
        $recentInsidentil = $allTasks;

        \App\Models\KepanitiaanSesiAbsensi::deleteWithoutKepanitiaan();

        // Ambil hanya sesi milik rencana kerja kepanitiaan yang masih ada.
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
            ->whereHas('kepanitiaan', fn ($query) => $query->where('kategori', 'Kepanitiaan'))
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
            'allTasks',
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
            'todayFormatted',
            'scheduledTasksOnDate',
            'dynamicKepanitiaanList',
            'activeSessions',
            'defaultSession',
            'isAdminOrLeader',
            'isCampusWide',
            'otkUserIds'
        ));
    }

    /**
     * AJAX endpoint for real-time Kepanitiaan Attendance DataTables.
     */
    public function getAttendanceData(Request $request)
    {
        \App\Models\KepanitiaanSesiAbsensi::deleteWithoutKepanitiaan();
        $sesiId = $request->query('sesi_id');

        if ($sesiId) {
            $sesi = \App\Models\KepanitiaanSesiAbsensi::find($sesiId);
        } else {
            $sesi = \App\Models\KepanitiaanSesiAbsensi::where('is_active', true)
                ->whereHas('kepanitiaan', fn ($query) => $query->where('kategori', 'Kepanitiaan'))
                ->orderBy('tanggal', 'desc')
                ->orderBy('id', 'asc')
                ->first();
        }

        // Fallback jika tidak ada sesi aktif
        if (!$sesi) {
            $sesi = \App\Models\KepanitiaanSesiAbsensi::whereHas('kepanitiaan', fn ($query) => $query->where('kategori', 'Kepanitiaan'))
                ->latest('id')
                ->first();
        }

        $sesiActiveId = $sesi ? $sesi->id : 0;

        $authUser = auth()->user();
        $canViewAll = false;

        if ($authUser) {
            $jabatanUpper = strtoupper(trim($authUser->jabatan ?? ''));
            $unitUpper = strtoupper(trim($authUser->unit ?? ''));
            $isRealRektorat = in_array($jabatanUpper, ['REKTOR', 'WAKIL REKTOR I', 'WAKIL REKTOR II', 'WAKIL REKTOR III'])
                || $unitUpper === 'REKTORAT';
            $isPureAdmin = $authUser->isSystemAdmin() && !$authUser->subordinates()->exists();
            $canViewAll = $isRealRektorat || $isPureAdmin;
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
        } elseif ($authUser && $authUser->subordinates()->exists()) {
            $otkIds = array_values(array_unique([(int) $authUser->id, ...$authUser->getAllSubordinateIds()]));
            $usersQuery->whereIn('users.id', $otkIds);
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
