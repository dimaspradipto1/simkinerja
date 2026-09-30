<?php

namespace App\Http\Controllers;

use App\Models\Kepanitiaan;
use App\Models\KepanitiaanPresensi;
use App\Models\KepanitiaanSesiAbsensi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class RekapitulasiAbsensiController extends Controller
{
    /**
     * Helper to resolve target user query based on current user role
     */
    private function getBaseUsersQuery()
    {
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

        if ($canViewAll) {
            $excludedUserIds = User::all()
                ->filter(fn($u) => $u->isAdmin() || $u->isSuperAdmin())
                ->pluck('id');

            return User::select('id', 'name', 'nidn', 'unit', 'jabatan', 'email')
                ->whereNotIn('id', $excludedUserIds);
        }

        return User::select('id', 'name', 'nidn', 'unit', 'jabatan', 'email')
            ->where('id', $authUser ? $authUser->id : 0);
    }

    /**
     * Helper to resolve Kepanitiaan, Sesi, and all-session mode dynamically
     */
    private function resolveSesiAndKepanitiaan(Request $request)
    {
        $sesiId = $request->query('sesi_id');
        $kepanitiaanId = $request->query('kepanitiaan_id');
        $kepanitiaanParam = $request->query('kepanitiaan');
        $day = $request->query('day');

        $kepanitiaan = null;
        $sesi = null;
        $isAllDays = false;

        // Check if all sessions / days requested
        if ($sesiId === 'all' || $sesiId === '0' || (isset($day) && (string)$day === '0')) {
            $isAllDays = true;
        }

        // 1. Direct sesi_id given
        if (!$isAllDays && !empty($sesiId) && is_numeric($sesiId) && (int)$sesiId > 0) {
            $sesi = KepanitiaanSesiAbsensi::with('kepanitiaan')->find((int) $sesiId);
            if ($sesi) {
                $kepanitiaan = $sesi->kepanitiaan;
            }
        }

        // 2. Resolve kepanitiaan if not found yet
        if (!$kepanitiaan) {
            if (!empty($kepanitiaanId) && is_numeric($kepanitiaanId)) {
                $kepanitiaan = Kepanitiaan::with(['sesiAbsensis' => fn($q) => $q->orderBy('is_active', 'desc')->orderBy('tanggal', 'asc')->orderBy('id', 'asc')])->find((int) $kepanitiaanId);
            } elseif (!empty($kepanitiaanParam)) {
                if (is_numeric($kepanitiaanParam)) {
                    $kepanitiaan = Kepanitiaan::with(['sesiAbsensis' => fn($q) => $q->orderBy('is_active', 'desc')->orderBy('tanggal', 'asc')->orderBy('id', 'asc')])->find((int) $kepanitiaanParam);
                } else {
                    $slugPattern = match ($kepanitiaanParam) {
                        'pkkmb' => '%pkkmb%',
                        'esq' => '%esq%',
                        'milad' => '%milad%',
                        'kuliah_umum' => '%kuliah%umum%',
                        default => "%{$kepanitiaanParam}%",
                    };
                    $kepanitiaan = Kepanitiaan::with(['sesiAbsensis' => fn($q) => $q->orderBy('is_active', 'desc')->orderBy('tanggal', 'asc')->orderBy('id', 'asc')])
                        ->where('uraian_tugas', 'like', $slugPattern)
                        ->first();
                }
            }
        }

        // 3. Fallback: prioritize active sessions, then any kepanitiaan with sessions
        if (!$kepanitiaan) {
            $firstActiveSesi = KepanitiaanSesiAbsensi::where('is_active', true)->with('kepanitiaan')->first();
            if ($firstActiveSesi && $firstActiveSesi->kepanitiaan) {
                $kepanitiaan = $firstActiveSesi->kepanitiaan;
                $kepanitiaan->load(['sesiAbsensis' => fn($q) => $q->orderBy('is_active', 'desc')->orderBy('tanggal', 'asc')->orderBy('id', 'asc')]);
            } else {
                $kepanitiaan = Kepanitiaan::whereHas('sesiAbsensis')
                    ->with(['sesiAbsensis' => fn($q) => $q->orderBy('is_active', 'desc')->orderBy('tanggal', 'asc')->orderBy('id', 'asc')])
                    ->first();
            }
        }

        // 4. Resolve sesi if not all days
        if (!$isAllDays && !$sesi && $kepanitiaan && $kepanitiaan->sesiAbsensis->isNotEmpty()) {
            if (isset($day) && is_numeric($day) && (int)$day > 0) {
                $dayIndex = (int) $day - 1;
                $sesi = $kepanitiaan->sesiAbsensis->values()->get($dayIndex) ?? $kepanitiaan->sesiAbsensis->first();
            } else {
                // Default to active session if available, else first session
                $sesi = $kepanitiaan->sesiAbsensis->where('is_active', true)->first() ?? $kepanitiaan->sesiAbsensis->first();
            }
        }

        return [$kepanitiaan, $sesi, $isAllDays];
    }

    /**
     * Display the Rekapitulasi Absensi page.
     */
    public function index(Request $request)
    {
        // 1. Ambil kepanitiaan yang memiliki sesi absensi AKTIF
        $activeKepanitiaans = Kepanitiaan::whereHas('sesiAbsensis', function ($q) {
                $q->where('is_active', true);
            })
            ->with(['sesiAbsensis' => function ($q) {
                $q->orderBy('is_active', 'desc')->orderBy('tanggal', 'asc')->orderBy('id', 'asc');
            }])
            ->orderBy('id', 'asc')
            ->get();

        // 2. Ambil seluruh kepanitiaan yang memiliki sesi absensi untuk fallback / mode semua
        $allKepanitiaans = Kepanitiaan::whereHas('sesiAbsensis')
            ->with(['sesiAbsensis' => function ($q) {
                $q->orderBy('is_active', 'desc')->orderBy('tanggal', 'asc')->orderBy('id', 'asc');
            }])
            ->orderBy('id', 'desc')
            ->get();

        // Mode: default 'active' jika ada sesi aktif, else 'all'
        $mode = $request->query('mode', $activeKepanitiaans->isNotEmpty() ? 'active' : 'all');
        $displayKepanitiaans = ($mode === 'active' && $activeKepanitiaans->isNotEmpty()) ? $activeKepanitiaans : $allKepanitiaans;

        [$selectedKepanitiaan, $selectedSesi, $isAllDays] = $this->resolveSesiAndKepanitiaan($request);

        $selectedKepanitiaanId = $selectedKepanitiaan?->id;
        $selectedSesiId = $isAllDays ? 'all' : ($selectedSesi?->id ?? 'all');
        $kepanitiaanParam = $request->query('kepanitiaan', $selectedKepanitiaan?->id ?? 'pkkmb');
        $day = $isAllDays ? 0 : 1;

        // Siapkan data JSON untuk JS switch di frontend
        $prepareJson = function ($list) {
            return $list->map(function ($k) {
                return [
                    'id' => $k->id,
                    'name' => $k->uraian_tugas,
                    'active_count' => $k->sesiAbsensis->where('is_active', true)->count(),
                    'total_count' => $k->sesiAbsensis->count(),
                    'sesis' => $k->sesiAbsensis->map(function ($s) {
                        return [
                            'id' => $s->id,
                            'name' => $s->nama_sesi,
                            'is_active' => (bool)$s->is_active,
                            'tanggal' => $s->tanggal ? $s->tanggal->format('d/m/Y') : null,
                        ];
                    })->values(),
                ];
            })->values();
        };

        $activeKepanitiaansJson = $prepareJson($activeKepanitiaans);
        $allKepanitiaansJson = $prepareJson($allKepanitiaans);

        return view('pages.rekapitulasi-absensi.index', compact(
            'activeKepanitiaans',
            'allKepanitiaans',
            'displayKepanitiaans',
            'activeKepanitiaansJson',
            'allKepanitiaansJson',
            'selectedKepanitiaan',
            'selectedSesi',
            'selectedKepanitiaanId',
            'selectedSesiId',
            'isAllDays',
            'mode',
            'kepanitiaanParam',
            'day'
        ));
    }

    /**
     * Helper to format badge status
     */
    private function formatStatusBadge($status, $waktu = null, $prefix = '')
    {
        if (empty($status)) {
            return '<span class="badge bg-light text-muted border px-2 py-1 small">' . $prefix . 'Belum</span>';
        }

        $style = match ($status) {
            'Hadir' => 'background-color: #198754; color: white;',
            'Izin' => 'background-color: #ffc107; color: #000;',
            'Sakit' => 'background-color: #0dcaf0; color: #fff;',
            'Alpa' => 'background-color: #dc3545; color: white;',
            default => 'background-color: #6c757d; color: white;',
        };

        $timeStr = $waktu ? ' (' . substr($waktu, 0, 5) . ')' : '';
        return '<span class="badge rounded px-2 py-1 fw-bold" style="' . $style . '">' . $prefix . e($status) . $timeStr . '</span>';
    }

    /**
     * Get DataTables data for users who HAVE attended.
     */
    public function getDataSudah(Request $request)
    {
        [$kepanitiaan, $sesi, $isAllDays] = $this->resolveSesiAndKepanitiaan($request);

        if (!$kepanitiaan) {
            return DataTables::of(collect())->make(true);
        }

        if ($isAllDays) {
            $sesis = $kepanitiaan->sesiAbsensis;
            if ($sesis->isEmpty()) {
                return DataTables::of(collect())->make(true);
            }

            $attendancesBySesi = [];
            $allAttendedIds = [];

            foreach ($sesis as $s) {
                $att = KepanitiaanPresensi::where('kepanitiaan_sesi_absensi_id', $s->id)->get()->keyBy('user_id');
                $attendancesBySesi[$s->id] = $att;
                foreach ($att as $p) {
                    if (!empty($p->hadir_datang) || !empty($p->hadir_pulang)) {
                        $allAttendedIds[] = $p->user_id;
                    }
                }
            }
            $allAttendedIds = array_values(array_unique($allAttendedIds));

            $usersQuery = $this->getBaseUsersQuery()->whereIn('id', $allAttendedIds);

            return DataTables::of($usersQuery)
                ->addIndexColumn()
                ->editColumn('name', function ($user) {
                    return '<div class="fw-bold text-dark" style="font-size: 0.95rem;">' . e($user->name) . '</div>' .
                           '<small class="text-muted"><i class="bi bi-person-vcard me-1"></i>NUP: ' . e($user->nidn ?: '-') . '</small>';
                })
                ->filterColumn('name', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%")
                          ->orWhere('nidn', 'like', "%{$keyword}%")
                          ->orWhere('email', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('unit_jabatan', function ($user) {
                    return '<div><span class="badge bg-light text-dark border me-1">' . e($user->unit ?: '-') . '</span></div>' .
                           '<small class="text-muted">' . e($user->jabatan ?: '-') . '</small>';
                })
                ->filterColumn('unit_jabatan', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('unit', 'like', "%{$keyword}%")
                          ->orWhere('jabatan', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('absen_datang', function ($user) use ($sesis, $attendancesBySesi) {
                    $badges = [];
                    foreach ($sesis as $idx => $s) {
                        $lbl = 'S' . ($idx + 1);
                        if (str_contains(strtolower($s->nama_sesi), 'hari 1') || str_contains(strtolower($s->nama_sesi), 'hari ke-1')) $lbl = 'H1';
                        elseif (str_contains(strtolower($s->nama_sesi), 'hari 2') || str_contains(strtolower($s->nama_sesi), 'hari ke-2')) $lbl = 'H2';
                        elseif (str_contains(strtolower($s->nama_sesi), 'hari 3') || str_contains(strtolower($s->nama_sesi), 'hari ke-3')) $lbl = 'H3';
                        elseif (str_contains(strtolower($s->nama_sesi), 'hari 4') || str_contains(strtolower($s->nama_sesi), 'hari ke-4')) $lbl = 'H4';

                        $att = $attendancesBySesi[$s->id]->get($user->id);
                        if ($att && $att->hadir_datang) {
                            $badges[] = $this->formatStatusBadge($att->hadir_datang, $att->waktu_datang, $lbl . ': ');
                        } else {
                            $badges[] = '<span class="badge bg-light text-muted border">' . $lbl . ': -</span>';
                        }
                    }
                    return '<div class="d-flex flex-wrap gap-1">' . implode('', $badges) . '</div>';
                })
                ->addColumn('absen_pulang', function ($user) use ($sesis, $attendancesBySesi) {
                    $badges = [];
                    foreach ($sesis as $idx => $s) {
                        $lbl = 'S' . ($idx + 1);
                        if (str_contains(strtolower($s->nama_sesi), 'hari 1') || str_contains(strtolower($s->nama_sesi), 'hari ke-1')) $lbl = 'H1';
                        elseif (str_contains(strtolower($s->nama_sesi), 'hari 2') || str_contains(strtolower($s->nama_sesi), 'hari ke-2')) $lbl = 'H2';
                        elseif (str_contains(strtolower($s->nama_sesi), 'hari 3') || str_contains(strtolower($s->nama_sesi), 'hari ke-3')) $lbl = 'H3';
                        elseif (str_contains(strtolower($s->nama_sesi), 'hari 4') || str_contains(strtolower($s->nama_sesi), 'hari ke-4')) $lbl = 'H4';

                        $att = $attendancesBySesi[$s->id]->get($user->id);
                        if ($att && $att->hadir_pulang) {
                            $badges[] = $this->formatStatusBadge($att->hadir_pulang, $att->waktu_pulang, $lbl . ': ');
                        } else {
                            $badges[] = '<span class="badge bg-light text-muted border">' . $lbl . ': -</span>';
                        }
                    }
                    return '<div class="d-flex flex-wrap gap-1">' . implode('', $badges) . '</div>';
                })
                ->addColumn('bukti_izin', function ($user) use ($sesis, $attendancesBySesi) {
                    $links = [];
                    foreach ($sesis as $idx => $s) {
                        $att = $attendancesBySesi[$s->id]->get($user->id);
                        if ($att && !empty($att->bukti_izin)) {
                            $links[] = '<a href="' . Storage::url($att->bukti_izin) . '" target="_blank" class="badge bg-primary-subtle text-primary border text-decoration-none">S' . ($idx + 1) . ' Bukti</a>';
                        }
                    }
                    return !empty($links) ? implode(' ', $links) : '<span class="text-muted small">-</span>';
                })
                ->rawColumns(['name', 'unit_jabatan', 'absen_datang', 'absen_pulang', 'bukti_izin'])
                ->make(true);
        }

        // Single Session Mode
        if (!$sesi) {
            return DataTables::of(collect())->make(true);
        }

        $attendances = KepanitiaanPresensi::where('kepanitiaan_sesi_absensi_id', $sesi->id)
            ->where(function ($q) {
                $q->whereNotNull('hadir_datang')
                  ->orWhereNotNull('hadir_pulang');
            })->get()->keyBy('user_id');

        $attendedUserIds = $attendances->keys()->toArray();
        $usersQuery = $this->getBaseUsersQuery()->whereIn('id', $attendedUserIds);

        return DataTables::of($usersQuery)
            ->addIndexColumn()
            ->editColumn('name', function ($user) {
                return '<div class="fw-bold text-dark" style="font-size: 0.95rem;">' . e($user->name) . '</div>' .
                       '<small class="text-muted"><i class="bi bi-person-vcard me-1"></i>NUP: ' . e($user->nidn ?: '-') . '</small>';
            })
            ->filterColumn('name', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                      ->orWhere('nidn', 'like', "%{$keyword}%")
                      ->orWhere('email', 'like', "%{$keyword}%");
                });
            })
            ->addColumn('unit_jabatan', function ($user) {
                return '<div><span class="badge bg-light text-dark border me-1">' . e($user->unit ?: '-') . '</span></div>' .
                       '<small class="text-muted">' . e($user->jabatan ?: '-') . '</small>';
            })
            ->filterColumn('unit_jabatan', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('unit', 'like', "%{$keyword}%")
                      ->orWhere('jabatan', 'like', "%{$keyword}%");
                });
            })
            ->addColumn('absen_datang', function ($user) use ($attendances) {
                $att = $attendances->get($user->id);
                if (!$att || empty($att->hadir_datang)) {
                    return '<span class="badge bg-secondary-subtle text-secondary border px-2 py-1">Belum Absen</span>';
                }

                $status = $att->hadir_datang;
                $waktu = $att->waktu_datang ? '<div class="small text-muted mt-1"><i class="bi bi-clock me-1"></i>' . substr($att->waktu_datang, 0, 5) . ' WIB</div>' : '';
                $catatan = $att->catatan_hadir_datang ? '<div class="small text-secondary fst-italic mt-1"><i class="bi bi-chat-left-text me-1"></i>' . e($att->catatan_hadir_datang) . '</div>' : '';

                $badgeClass = match ($status) {
                    'Hadir' => 'background-color: #198754; color: white;',
                    'Izin' => 'background-color: #ffc107; color: #000;',
                    'Sakit' => 'background-color: #0dcaf0; color: #fff;',
                    'Alpa' => 'background-color: #dc3545; color: white;',
                    default => 'background-color: #6c757d; color: white;',
                };

                return '<span class="badge rounded px-2 py-1 fw-bold" style="' . $badgeClass . '">' . e($status) . '</span>' . $waktu . $catatan;
            })
            ->addColumn('absen_pulang', function ($user) use ($attendances) {
                $att = $attendances->get($user->id);
                if (!$att || empty($att->hadir_pulang)) {
                    return '<span class="badge bg-secondary-subtle text-secondary border px-2 py-1">Belum Pulang</span>';
                }

                $status = $att->hadir_pulang;
                $waktu = $att->waktu_pulang ? '<div class="small text-muted mt-1"><i class="bi bi-clock me-1"></i>' . substr($att->waktu_pulang, 0, 5) . ' WIB</div>' : '';
                $catatan = $att->catatan_hadir_pulang ? '<div class="small text-secondary fst-italic mt-1"><i class="bi bi-chat-left-text me-1"></i>' . e($att->catatan_hadir_pulang) . '</div>' : '';

                $badgeClass = match ($status) {
                    'Hadir' => 'background-color: #198754; color: white;',
                    'Izin' => 'background-color: #ffc107; color: #000;',
                    'Sakit' => 'background-color: #0dcaf0; color: #fff;',
                    'Alpa' => 'background-color: #dc3545; color: white;',
                    default => 'background-color: #6c757d; color: white;',
                };

                return '<span class="badge rounded px-2 py-1 fw-bold" style="' . $badgeClass . '">' . e($status) . '</span>' . $waktu . $catatan;
            })
            ->addColumn('bukti_izin', function ($user) use ($attendances) {
                $att = $attendances->get($user->id);
                if ($att && !empty($att->bukti_izin)) {
                    $url = Storage::url($att->bukti_izin);
                    return '<a href="' . $url . '" target="_blank" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1"><i class="bi bi-file-earmark-arrow-down"></i> Lihat Bukti</a>';
                }
                return '<span class="text-muted small">-</span>';
            })
            ->rawColumns(['name', 'unit_jabatan', 'absen_datang', 'absen_pulang', 'bukti_izin'])
            ->make(true);
    }

    /**
     * Get DataTables data for users who HAVE NOT attended.
     */
    public function getDataBelum(Request $request)
    {
        [$kepanitiaan, $sesi, $isAllDays] = $this->resolveSesiAndKepanitiaan($request);

        if (!$kepanitiaan) {
            return DataTables::of(collect())->make(true);
        }

        if ($isAllDays) {
            $sesis = $kepanitiaan->sesiAbsensis;
            if ($sesis->isEmpty()) {
                return DataTables::of(collect())->make(true);
            }

            $allAttendedIds = [];
            foreach ($sesis as $s) {
                $att = KepanitiaanPresensi::where('kepanitiaan_sesi_absensi_id', $s->id)->get();
                foreach ($att as $p) {
                    if (!empty($p->hadir_datang) || !empty($p->hadir_pulang)) {
                        $allAttendedIds[] = $p->user_id;
                    }
                }
            }
            $allAttendedIds = array_values(array_unique($allAttendedIds));

            $usersQuery = $this->getBaseUsersQuery()->whereNotIn('id', $allAttendedIds);

            return DataTables::of($usersQuery)
                ->addIndexColumn()
                ->editColumn('name', function ($user) {
                    return '<div class="fw-bold text-dark" style="font-size: 0.95rem;">' . e($user->name) . '</div>' .
                           '<small class="text-muted"><i class="bi bi-envelope me-1"></i>' . e($user->email ?: '-') . '</small>';
                })
                ->filterColumn('name', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%")
                          ->orWhere('email', 'like', "%{$keyword}%")
                          ->orWhere('nidn', 'like', "%{$keyword}%");
                    });
                })
                ->editColumn('nidn', function ($user) {
                    return '<span class="badge bg-light text-dark border">' . e($user->nidn ?: '-') . '</span>';
                })
                ->editColumn('unit', function ($user) {
                    return e($user->unit ?: '-');
                })
                ->editColumn('jabatan', function ($user) {
                    return e($user->jabatan ?: '-');
                })
                ->addColumn('status_datang', function () {
                    return '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 fw-semibold"><i class="bi bi-x-circle me-1"></i>Belum Hadir</span>';
                })
                ->addColumn('status_pulang', function () {
                    return '<span class="badge bg-secondary-subtle text-secondary border px-2 py-1 fw-semibold"><i class="bi bi-dash-circle me-1"></i>Belum Pulang</span>';
                })
                ->rawColumns(['name', 'nidn', 'unit', 'jabatan', 'status_datang', 'status_pulang'])
                ->make(true);
        }

        // Single Session Mode
        if (!$sesi) {
            return DataTables::of(collect())->make(true);
        }

        $attendedUserIds = KepanitiaanPresensi::where('kepanitiaan_sesi_absensi_id', $sesi->id)
            ->where(function ($q) {
                $q->whereNotNull('hadir_datang')
                  ->orWhereNotNull('hadir_pulang');
            })->pluck('user_id')->toArray();

        $usersQuery = $this->getBaseUsersQuery()->whereNotIn('id', $attendedUserIds);

        return DataTables::of($usersQuery)
            ->addIndexColumn()
            ->editColumn('name', function ($user) {
                return '<div class="fw-bold text-dark" style="font-size: 0.95rem;">' . e($user->name) . '</div>' .
                       '<small class="text-muted"><i class="bi bi-envelope me-1"></i>' . e($user->email ?: '-') . '</small>';
            })
            ->filterColumn('name', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                      ->orWhere('email', 'like', "%{$keyword}%")
                      ->orWhere('nidn', 'like', "%{$keyword}%");
                });
            })
            ->editColumn('nidn', function ($user) {
                return '<span class="badge bg-light text-dark border">' . e($user->nidn ?: '-') . '</span>';
            })
            ->editColumn('unit', function ($user) {
                return e($user->unit ?: '-');
            })
            ->editColumn('jabatan', function ($user) {
                return e($user->jabatan ?: '-');
            })
            ->addColumn('status_datang', function () {
                return '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 fw-semibold"><i class="bi bi-x-circle me-1"></i>Belum Datang</span>';
            })
            ->addColumn('status_pulang', function () {
                return '<span class="badge bg-secondary-subtle text-secondary border px-2 py-1 fw-semibold"><i class="bi bi-dash-circle me-1"></i>Belum Pulang</span>';
            })
            ->rawColumns(['name', 'nidn', 'unit', 'jabatan', 'status_datang', 'status_pulang'])
            ->make(true);
    }

    /**
     * Get summary attendance statistics (Total, Datang, Pulang, Belum Datang, Belum Pulang).
     */
    public function getStats(Request $request)
    {
        [$kepanitiaan, $sesi, $isAllDays] = $this->resolveSesiAndKepanitiaan($request);

        $baseUsersQuery = $this->getBaseUsersQuery();
        $totalPegawai = $baseUsersQuery->count();
        $targetUserIds = $baseUsersQuery->pluck('id')->toArray();

        if (!$kepanitiaan) {
            return response()->json([
                'total_pegawai' => $totalPegawai,
                'sudah_absen' => 0,
                'belum_absen' => $totalPegawai,
                'sudah_datang' => 0,
                'belum_datang' => $totalPegawai,
                'sudah_pulang' => 0,
                'belum_pulang' => $totalPegawai,
                'is_all_days' => false,
                'sesi_nama' => '-',
            ]);
        }

        if ($isAllDays) {
            $sesis = $kepanitiaan->sesiAbsensis;
            $sudahDatangTotal = 0;
            $sudahPulangTotal = 0;
            $allAttendedIds = [];

            foreach ($sesis as $s) {
                $att = KepanitiaanPresensi::where('kepanitiaan_sesi_absensi_id', $s->id)
                    ->whereIn('user_id', $targetUserIds)
                    ->get();

                $sudahDatangTotal += $att->whereNotNull('hadir_datang')->count();
                $sudahPulangTotal += $att->whereNotNull('hadir_pulang')->count();

                foreach ($att as $p) {
                    if (!empty($p->hadir_datang) || !empty($p->hadir_pulang)) {
                        $allAttendedIds[] = $p->user_id;
                    }
                }
            }

            $allAttendedIds = array_unique($allAttendedIds);
            $sudahAbsen = count($allAttendedIds);
            $belumAbsen = max(0, $totalPegawai - $sudahAbsen);

            $totalTargetPresensi = $totalPegawai * max(1, $sesis->count());
            $belumDatangTotal = max(0, $totalTargetPresensi - $sudahDatangTotal);
            $belumPulangTotal = max(0, $totalTargetPresensi - $sudahPulangTotal);

            return response()->json([
                'total_pegawai' => $totalPegawai,
                'sudah_absen' => $sudahAbsen,
                'belum_absen' => $belumAbsen,
                'sudah_datang' => $sudahDatangTotal,
                'belum_datang' => $belumDatangTotal,
                'sudah_pulang' => $sudahPulangTotal,
                'belum_pulang' => $belumPulangTotal,
                'is_all_days' => true,
                'sesi_nama' => 'Rekap Semua Sesi (' . $kepanitiaan->uraian_tugas . ')',
            ]);
        }

        if (!$sesi) {
            return response()->json([
                'total_pegawai' => $totalPegawai,
                'sudah_absen' => 0,
                'belum_absen' => $totalPegawai,
                'sudah_datang' => 0,
                'belum_datang' => $totalPegawai,
                'sudah_pulang' => 0,
                'belum_pulang' => $totalPegawai,
                'is_all_days' => false,
                'sesi_nama' => '-',
            ]);
        }

        $attendances = KepanitiaanPresensi::where('kepanitiaan_sesi_absensi_id', $sesi->id)
            ->whereIn('user_id', $targetUserIds)
            ->get();

        $sudahDatang = $attendances->whereNotNull('hadir_datang')->count();
        $sudahPulang = $attendances->whereNotNull('hadir_pulang')->count();
        $sudahLengkap = $attendances->filter(fn($a) => !empty($a->hadir_datang) && !empty($a->hadir_pulang))->count();

        $sudahAbsen = $attendances->filter(fn($a) => !empty($a->hadir_datang) || !empty($a->hadir_pulang))->count();
        $belumAbsen = max(0, $totalPegawai - $sudahAbsen);

        $belumDatang = max(0, $totalPegawai - $sudahDatang);
        $belumPulang = max(0, $totalPegawai - $sudahPulang);

        return response()->json([
            'total_pegawai' => $totalPegawai,
            'sudah_absen' => $sudahAbsen,
            'belum_absen' => $belumAbsen,
            'sudah_datang' => $sudahDatang,
            'belum_datang' => $belumDatang,
            'sudah_pulang' => $sudahPulang,
            'belum_pulang' => $belumPulang,
            'sudah_lengkap' => $sudahLengkap,
            'is_all_days' => false,
            'sesi_nama' => $sesi->nama_sesi,
            'sesi_active' => (bool)$sesi->is_active,
        ]);
    }
}
