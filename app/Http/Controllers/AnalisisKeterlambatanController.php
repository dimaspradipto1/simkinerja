<?php

namespace App\Http\Controllers;

use App\Models\RencanaKerja;
use App\Models\Kepanitiaan;
use App\Models\Insidentil;
use App\Models\PeriodeAkademik;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class AnalisisKeterlambatanController extends Controller
{
    /**
     * Display the Analisis Keterlambatan Dashboard view.
     */
    public function index(Request $request)
    {
        $authUser = Auth::user();
        $periodeAkademiks = PeriodeAkademik::orderBy('id', 'asc')->get();
        $defaultPeriode = PeriodeAkademik::first();
        $defaultPeriodeId = $defaultPeriode ? $defaultPeriode->id : null;

        $isStaffOnly = true;
        $usersQuery = User::whereNotNull('jabatan')->where('jabatan', '!=', '');

        if ($authUser) {
            if ($authUser->isAdmin() || $authUser->isPimpinanRektorat()) {
                $isStaffOnly = false;
            } elseif ($authUser->isPimpinanUnit()) {
                $isStaffOnly = false;
                $keywords = $this->getUnitKeywords($authUser);
                $usersQuery->where(function ($q) use ($authUser, $keywords) {
                    $q->where('id', $authUser->id);
                    if (!empty($keywords)) {
                        foreach ($keywords as $kw) {
                            $q->orWhere('unit', 'like', '%' . $kw . '%')
                              ->orWhere('jabatan', 'like', '%' . $kw . '%');
                        }
                    } elseif (!empty($authUser->unit)) {
                        $q->orWhere('unit', 'like', '%' . $authUser->unit . '%');
                    }
                });
            } else {
                $usersQuery->where('id', $authUser->id);
            }
        }

        $allUsers = $usersQuery->orderBy('name')->get(['id', 'name', 'jabatan', 'unit'])->unique('name');

        $groupedUsers = [
            '👑 LEVEL 1: REKTORAT' => collect(),
            '🏛️ LEVEL 2: PIMPINAN UNIT & DEKANAT' => collect(),
            '📋 LEVEL 3: PIMPINAN OPERASIONAL (KAPRODI / KABID)' => collect(),
            '👤 LEVEL 4: STAF PELAKSANA & SUPPORT' => collect(),
        ];

        $units = $allUsers->pluck('unit')->unique()->filter()->values();

        foreach ($allUsers as $u) {
            $level = $this->getLeadershipLevel($u);
            $u->level = $level;
            if ($level === 1) {
                $groupedUsers['👑 LEVEL 1: REKTORAT']->push($u);
            } elseif ($level === 2) {
                $groupedUsers['🏛️ LEVEL 2: PIMPINAN UNIT & DEKANAT']->push($u);
            } elseif ($level === 3) {
                $groupedUsers['📋 LEVEL 3: PIMPINAN OPERASIONAL (KAPRODI / KABID)']->push($u);
            } else {
                $groupedUsers['👤 LEVEL 4: STAF PELAKSANA & SUPPORT']->push($u);
            }
        }

        foreach ($groupedUsers as $key => $collection) {
            $groupedUsers[$key] = $collection->sortBy(function ($u) {
                return sprintf('%03d_%s', $this->getPositionRank($u), $u->name);
            })->values();
        }

        $allUsers = $allUsers->sortBy(function ($u) {
            return sprintf('%03d_%s', $this->getPositionRank($u), $u->name);
        })->values();

        $usersWithJabatan = $allUsers;
        $hierarchyTree = $this->getHierarchyTree($allUsers);

        return view('pages.analisis_keterlambatan.index', compact('periodeAkademiks', 'defaultPeriodeId', 'usersWithJabatan', 'groupedUsers', 'units', 'hierarchyTree', 'isStaffOnly'));
    }

    /**
     * Get DataTables JSON dataset with overlap delay diagnosis.
     */
    public function getData(Request $request)
    {
        $authUser = Auth::user();
        $today = Carbon::now()->format('Y-m-d');

        $query = RencanaKerja::with(['user', 'periodeAkademik', 'taggedUsers', 'milestones']);

        // Scope by explicit user_id filter or user permissions
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        } elseif ($authUser) {
            if ($authUser->isAdmin() || $authUser->isPimpinanRektorat()) {
                // Universal Scope: Rektorat & Admin
            } elseif ($authUser->isPimpinanUnit()) {
                $keywords = $this->getUnitKeywords($authUser);
                $query->where(function ($q) use ($authUser, $keywords) {
                    $q->where('user_id', $authUser->id)
                      ->orWhereHas('user', function ($qu) use ($keywords, $authUser) {
                          $qu->where(function ($nested) use ($keywords, $authUser) {
                              if (!empty($keywords)) {
                                  foreach ($keywords as $kw) {
                                      $nested->orWhere('unit', 'like', '%' . $kw . '%')
                                             ->orWhere('jabatan', 'like', '%' . $kw . '%');
                                  }
                              } elseif (!empty($authUser->unit)) {
                                  $nested->orWhere('unit', 'like', '%' . $authUser->unit . '%');
                              }
                          });
                      })
                      ->orWhereHas('taggedUsers', function ($qu) use ($authUser) {
                          $qu->where('users.id', $authUser->id);
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
        }

        if ($request->filled('periode_akademik_id')) {
            $query->where('periode_akademik_id', $request->periode_akademik_id);
        }

        if ($request->filled('jabatan')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('jabatan', $request->jabatan);
            });
        }

        // Fetch all candidates to analyze delay condition
        $allTasks = $query->latest()->get();

        // Filter by Leadership Level if requested
        if ($request->filled('leadership_level')) {
            $levelReq = (int) $request->leadership_level;
            $allTasks = $allTasks->filter(function ($task) use ($levelReq) {
                return $this->getLeadershipLevel($task->user) === $levelReq;
            });
        }

        // Filter by Unit if requested
        if ($request->filled('unit')) {
            $unitReq = strtoupper($request->unit);
            $allTasks = $allTasks->filter(function ($task) use ($unitReq) {
                return $task->user && str_contains(strtoupper($task->user->unit ?? ''), $unitReq);
            });
        }

        // Filter by Tingkat 1
        if ($request->filled('tingkat_1')) {
            $t1 = strtoupper($request->tingkat_1);
            $allTasks = $allTasks->filter(function ($task) use ($t1) {
                $struct = $this->getHierarchyStructure($task->user);
                return str_contains(strtoupper($struct['divisi_key']), $t1) || str_contains(strtoupper($struct['divisi']), $t1);
            });
        }

        // Filter by Tingkat 2
        if ($request->filled('tingkat_2')) {
            $t2 = strtoupper($request->tingkat_2);
            $allTasks = $allTasks->filter(function ($task) use ($t2) {
                $struct = $this->getHierarchyStructure($task->user);
                return str_contains(strtoupper($struct['sub_unit']), $t2) || str_contains(strtoupper($task->user->jabatan ?? ''), $t2);
            });
        }

        // Filter by Tingkat 3
        if ($request->filled('tingkat_3')) {
            $t3 = strtoupper($request->tingkat_3);
            $allTasks = $allTasks->filter(function ($task) use ($t3) {
                return $task->user && str_contains(strtoupper($task->user->jabatan ?? ''), $t3);
            });
        }

        $lateTasks = collect();
        $countInsidentil = 0;
        $countKepanitiaan = 0;
        $countBebanGanda = 0;
        $countMurni = 0;

        foreach ($allTasks as $task) {
            $isLate = false;
            $estimasiSelesai = $task->estimasi_tanggal_selesai;

            if (!empty($task->tanggal_selesai) && !empty($estimasiSelesai) && $task->tanggal_selesai > $estimasiSelesai) {
                $isLate = true;
            } elseif (($task->status !== 'Selesai') && !empty($estimasiSelesai) && $estimasiSelesai < $today) {
                $isLate = true;
            }

            if (!$isLate) {
                continue;
            }

            // Analyze overlap timeframe
            $startDate = $task->estimasi_tanggal_mulai ?? $task->tanggal_mulai ?? $task->created_at->format('Y-m-d');
            $endDate = !empty($task->tanggal_selesai) ? $task->tanggal_selesai : $today;

            // Search overlapping Insidentil
            $overlappingInsidentil = Insidentil::where('user_id', $task->user_id)
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->where(function ($sub) use ($startDate, $endDate) {
                        $sub->whereNotNull('estimasi_tanggal_mulai')
                            ->where('estimasi_tanggal_mulai', '<=', $endDate)
                            ->where('estimasi_tanggal_selesai', '>=', $startDate);
                    })->orWhere(function ($sub) use ($startDate, $endDate) {
                        $sub->whereNotNull('tanggal_mulai')
                            ->where('tanggal_mulai', '<=', $endDate)
                            ->where(function ($nest) use ($startDate) {
                                $nest->where('tanggal_selesai', '>=', $startDate)
                                    ->orWhereNull('tanggal_selesai');
                            });
                    });
                })->get();

            // Search overlapping Kepanitiaan
            $overlappingKepanitiaan = Kepanitiaan::where(function ($q) use ($task) {
                $q->where('user_id', $task->user_id)
                    ->orWhereHas('taggedUsers', function ($tu) use ($task) {
                        $tu->where('users.id', $task->user_id);
                    });
            })->where(function ($q) use ($startDate, $endDate) {
                $q->where(function ($sub) use ($startDate, $endDate) {
                    $sub->whereNotNull('estimasi_tanggal_mulai')
                        ->where('estimasi_tanggal_mulai', '<=', $endDate)
                        ->where('estimasi_tanggal_selesai', '>=', $startDate);
                })->orWhere(function ($sub) use ($startDate, $endDate) {
                    $sub->whereNotNull('tanggal_mulai')
                        ->where('tanggal_mulai', '<=', $endDate)
                        ->where(function ($nest) use ($startDate) {
                            $nest->where('tanggal_selesai', '>=', $startDate)
                                ->orWhereNull('tanggal_selesai');
                        });
                });
            })->get();

            $hasInsidentil = $overlappingInsidentil->count() > 0;
            $hasKepanitiaan = $overlappingKepanitiaan->count() > 0;

            $kategoriKendala = 'murni';
            if ($hasInsidentil && $hasKepanitiaan) {
                $kategoriKendala = 'beban_ganda';
                $countBebanGanda++;
            } elseif ($hasInsidentil) {
                $kategoriKendala = 'insidentil';
                $countInsidentil++;
            } elseif ($hasKepanitiaan) {
                $kategoriKendala = 'kepanitiaan';
                $countKepanitiaan++;
            } else {
                $kategoriKendala = 'murni';
                $countMurni++;
            }

            // Filter by requested category if specified
            if ($request->filled('kategori_kendala') && $request->kategori_kendala !== 'semua') {
                if ($request->kategori_kendala !== $kategoriKendala) {
                    continue;
                }
            }

            $task->kategori_kendala = $kategoriKendala;
            $task->overlapping_insidentil = $overlappingInsidentil;
            $task->overlapping_kepanitiaan = $overlappingKepanitiaan;
            
            // Calculate delay days
            $estDateObj = Carbon::parse($estimasiSelesai);
            $actualEndObj = Carbon::parse($endDate);
            $task->delay_days = max(1, (int) $estDateObj->diffInDays($actualEndObj));

            $lateTasks->push($task);
        }

        $lateTasks = $lateTasks->sortBy(function ($task) {
            return sprintf('%03d_%s', $this->getPositionRank($task->user), $task->user ? $task->user->name : '');
        })->values();

        $totalLateCount = $lateTasks->count();
        $stats = [
            'total'         => $totalLateCount,
            'insidentil'    => $countInsidentil,
            'kepanitiaan'   => $countKepanitiaan,
            'beban_ganda'   => $countBebanGanda,
            'murni'         => $countMurni,
            'percent_ins'   => $totalLateCount > 0 ? round(($countInsidentil / $totalLateCount) * 100) : 0,
            'percent_pan'   => $totalLateCount > 0 ? round(($countKepanitiaan / $totalLateCount) * 100) : 0,
            'percent_ganda' => $totalLateCount > 0 ? round(($countBebanGanda / $totalLateCount) * 100) : 0,
            'percent_murni' => $totalLateCount > 0 ? round(($countMurni / $totalLateCount) * 100) : 0,
        ];

        return DataTables::of($lateTasks)
            ->addIndexColumn()
            ->addColumn('staff_info', function ($row) {
                $struct = $this->getHierarchyStructure($row->user);
                $html = '<div class="mb-1"><span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.68rem;"><i class="bi bi-diagram-3 me-1"></i>' . e($struct['divisi']) . '</span></div>';
                if (!empty($struct['sub_unit'])) {
                    $html .= '<div class="mb-1"><span class="badge bg-light text-dark border" style="font-size: 0.65rem;"><i class="bi bi-diagram-2 me-1 text-muted"></i>' . e($struct['sub_unit']) . '</span></div>';
                }
                $html .= '<div class="fw-bold text-dark fs-6">' . e($row->user ? $row->user->name : '-') . '</div>';
                $html .= '<div class="small text-muted fw-semibold">' . e($row->user ? ($row->user->jabatan ?? '-') : '-') . '</div>';
                return $html;
            })
            ->addColumn('task_details', function ($row) {
                $estMulai = $row->estimasi_tanggal_mulai ? date('d/m/Y', strtotime($row->estimasi_tanggal_mulai)) : '-';
                $estSelesai = $row->estimasi_tanggal_selesai ? date('d/m/Y', strtotime($row->estimasi_tanggal_selesai)) : '-';
                $realSelesai = $row->tanggal_selesai ? date('d/m/Y', strtotime($row->tanggal_selesai)) : 'Belum Selesai (Current)';

                $html = '<div class="fw-bold text-dark mb-1">' . e($row->uraian_tugas) . '</div>';
                $html .= '<div class="small text-muted mb-1"><i class="bi bi-calendar-event me-1 text-secondary"></i><strong>Estimasi:</strong> ' . $estMulai . ' s/d ' . $estSelesai . '</div>';
                $html .= '<div class="small text-danger mb-1"><i class="bi bi-clock-history me-1"></i><strong>Realisasi:</strong> ' . $realSelesai . ' <span class="badge bg-danger-subtle text-danger border border-danger ms-1">Terlambat ' . $row->delay_days . ' Hari</span></div>';
                return $html;
            })
            ->addColumn('diagnostik_kendala', function ($row) {
                if ($row->kategori_kendala === 'beban_ganda') {
                    return '<span class="badge bg-warning text-dark px-2 py-1 fs-7"><i class="bi bi-exclamation-octagon-fill me-1"></i>Beban Ganda (Panitia & Insidentil)</span>';
                } elseif ($row->kategori_kendala === 'insidentil') {
                    return '<span class="badge bg-danger px-2 py-1 fs-7"><i class="bi bi-lightning-charge-fill me-1"></i>Terganggu Tugas Insidentil</span>';
                } elseif ($row->kategori_kendala === 'kepanitiaan') {
                    return '<span class="badge bg-info text-dark px-2 py-1 fs-7"><i class="bi bi-people-fill me-1"></i>Beban Tugas Kepanitiaan</span>';
                }
                return '<span class="badge bg-secondary px-2 py-1 fs-7"><i class="bi bi-person-x-fill me-1"></i>Keterlambatan Murni Staff</span>';
            })
            ->addColumn('rincian_bentrokan', function ($row) {
                $html = '';
                $insCount = $row->overlapping_insidentil ? $row->overlapping_insidentil->count() : 0;
                $panCount = $row->overlapping_kepanitiaan ? $row->overlapping_kepanitiaan->count() : 0;

                if ($insCount === 0 && $panCount === 0) {
                    return '<span class="text-muted small"><em>Tidak ada tugas panitia/insidentil yang bersamaan.</em></span>';
                }

                if ($insCount > 0) {
                    $html .= '<div class="small fw-bold text-danger mb-1"><i class="bi bi-lightning-charge me-1"></i>Tugas Insidentil (' . $insCount . '):</div>';
                    $html .= '<ul class="ps-3 mb-2 small text-dark" style="margin-bottom: 0;">';
                    foreach ($row->overlapping_insidentil as $ins) {
                        $html .= '<li>' . e($ins->uraian_tugas) . ' <span class="text-muted">(' . ($ins->status ?? '-') . ')</span></li>';
                    }
                    $html .= '</ul>';
                }

                if ($panCount > 0) {
                    $html .= '<div class="small fw-bold text-info mb-1"><i class="bi bi-people me-1"></i>Kegiatan Kepanitiaan (' . $panCount . '):</div>';
                    $html .= '<ul class="ps-3 mb-0 small text-dark">';
                    foreach ($row->overlapping_kepanitiaan as $pan) {
                        $html .= '<li>' . e($pan->nama_kegiatan ?? $pan->uraian_tugas) . ' <span class="text-muted">(' . ($pan->status ?? '-') . ')</span></li>';
                    }
                    $html .= '</ul>';
                }

                return $html;
            })
            ->addColumn('rekomendasi_evaluasi', function ($row) {
                if (!empty($row->saran_pimpinan)) {
                    return '<div class="small text-dark p-2 bg-light rounded border border-primary-subtle">'
                         . '<div class="mb-1">'
                         . '<span class="badge bg-primary text-white"><i class="bi bi-shield-check me-1"></i>Catatan &amp; Saran Pimpinan</span>'
                         . '</div>'
                         . '<div>' . nl2br(e($row->saran_pimpinan)) . '</div>'
                         . '</div>';
                }

                $userLevel = $this->getLeadershipLevel($row->user);
                $saranSys = '';
                $borderClass = 'border';
                $iconClass = 'bi-info-circle text-secondary';

                if ($row->kategori_kendala === 'beban_ganda' || $row->kategori_kendala === 'insidentil') {
                    $borderClass = 'border border-warning-subtle';
                    $iconClass = 'bi-info-circle text-warning';
                    if ($userLevel === 1) {
                        $saranSys = 'Diperlukan delegasi tugas strategis &amp; penataan ulang prioritas kebijakan tingkat universitas.';
                    } elseif ($userLevel === 2) {
                        $saranSys = 'Diperlukan evaluasi beban kerja manajerial unit &amp; redistribusi porsi penugasan lintas prodi/bidang.';
                    } elseif ($userLevel === 3) {
                        $saranSys = 'Diperlukan koordinasi penataan jadwal operasional prodi/bidang &amp; penyesuaian deadline tugas utama.';
                    } else {
                        $saranSys = 'Diperlukan redistribusi porsi tugas mendesak atau penyesuaian ulang estimasi deadline tugas utama.';
                    }
                } elseif ($row->kategori_kendala === 'kepanitiaan') {
                    $borderClass = 'border border-info-subtle';
                    $iconClass = 'bi-info-circle text-info';
                    if ($userLevel === 1) {
                        $saranSys = 'Evaluasi porsi keterlibatan eksekutif dalam kepanitiaan strategis agar target universitas terjaga.';
                    } elseif ($userLevel === 2) {
                        $saranSys = 'Evaluasi alokasi kepanitiaan tingkat unit/fakultas agar tidak mengganggu manajerial unit.';
                    } elseif ($userLevel === 3) {
                        $saranSys = 'Evaluasi porsi keterlibatan tugas panitia agar tidak mengganggu target kinerja operasional prodi/bidang.';
                    } else {
                        $saranSys = 'Evaluasi porsi keterlibatan anggota panitia agar tidak mengganggu performa target rutin bulanan.';
                    }
                } else {
                    if ($userLevel === 1) {
                        $saranSys = 'Perlu peninjauan alokasi waktu supervisi strategis dan koordinasi antar pimpinan universitas.';
                    } elseif ($userLevel === 2) {
                        $saranSys = 'Perlu evaluasi efektivitas manajemen internal unit &amp; penguatan pengawasan manajerial unit.';
                    } elseif ($userLevel === 3) {
                        $saranSys = 'Perlu penguatan manajemen waktu pengerjaan target operasional dan optimalisasi koordinasi tim prodi/bidang.';
                    } else {
                        $saranSys = 'Perlu pendampingan dan monitoring manajemen waktu pengerjaan tugas rutin staf.';
                    }
                }

                return '<div class="small text-dark p-2 bg-light rounded ' . $borderClass . '">'
                     . '<div class="mb-1"><span><i class="bi ' . $iconClass . ' me-1"></i><strong>Saran Pimpinan (Otomatis):</strong></span></div>'
                     . '<div class="mb-1">' . $saranSys . '</div>'
                     . '</div>';
            })
            ->addColumn('aksi', function ($row) use ($authUser) {
                $canEdit = $authUser && ($authUser->isAdmin() || $authUser->isPimpinanRektorat() || $authUser->isPimpinanUnit());
                if (!$canEdit) return '<span class="text-muted small">-</span>';
                $saranAttr = e($row->saran_pimpinan ?? '');
                return '<div class="text-center">'
                     . '<button type="button" class="btn btn-sm btn-outline-primary btn-edit-saran"'
                     . ' data-id="' . $row->id . '"'
                     . ' data-task="' . e($row->uraian_tugas) . '"'
                     . ' data-staff="' . e($row->user ? $row->user->name : '-') . '"'
                     . ' data-saran="' . $saranAttr . '"'
                     . ' title="Input / Edit Rekomendasi Evaluasi Pimpinan">'
                     . '<i class="bi bi-pencil-square me-1"></i>Edit'
                     . '</button>'
                     . '</div>';
            })
            ->with('stats', $stats)
            ->rawColumns(['staff_info', 'task_details', 'diagnostik_kendala', 'rincian_bentrokan', 'rekomendasi_evaluasi', 'aksi'])
            ->make(true);
    }

    /**
     * Export Analisis Keterlambatan to Excel.
     */
    public function exportExcel(Request $request)
    {
        $authUser = Auth::user();
        $today = Carbon::now()->format('Y-m-d');

        $query = RencanaKerja::with(['user', 'periodeAkademik', 'taggedUsers']);

        if ($authUser) {
            if ($authUser->isAdmin() || $authUser->isPimpinanRektorat() || $authUser->isPimpinanUnit()) {
                if ($request->filled('user_id')) {
                    $query->where('user_id', $request->user_id);
                }
                if ($request->filled('jabatan')) {
                    $query->whereHas('user', function ($q) use ($request) {
                        $q->where('jabatan', $request->jabatan);
                    });
                }
            } else {
                $query->where(function ($q) use ($authUser) {
                    $q->where('user_id', $authUser->id)
                      ->orWhereHas('taggedUsers', function ($qu) use ($authUser) {
                          $qu->where('users.id', $authUser->id);
                      });
                });
            }
        }

        if ($request->filled('periode_akademik_id')) {
            $query->where('periode_akademik_id', $request->periode_akademik_id);
        }

        $items = $query->latest()->get();

        if ($request->filled('leadership_level')) {
            $levelReq = (int) $request->leadership_level;
            $items = $items->filter(function ($task) use ($levelReq) {
                return $this->getLeadershipLevel($task->user) === $levelReq;
            });
        }

        if ($request->filled('unit')) {
            $unitReq = strtoupper($request->unit);
            $items = $items->filter(function ($task) use ($unitReq) {
                return $task->user && str_contains(strtoupper($task->user->unit ?? ''), $unitReq);
            });
        }

        $items = $items->sortBy(function ($task) {
            return sprintf('%03d_%s', $this->getPositionRank($task->user), $task->user ? $task->user->name : '');
        })->values();

        $namaStaff = 'SEMUA STAFF';
        $jabatanStaff = 'SEMUA JABATAN';
        $unitStaff = 'SEMUA UNIT';

        if ($request->filled('user_id') && ($u = User::find($request->user_id))) {
            $namaStaff = strtoupper($u->name);
            $jabatanStaff = strtoupper($u->jabatan ?? '-');
            $unitStaff = strtoupper($u->unit ?? '-');
        }

        $periodeText = 'SEMUA PERIODE';
        if ($request->filled('periode_akademik_id') && ($p = PeriodeAkademik::find($request->periode_akademik_id))) {
            $periodeText = strtoupper($p->nama_periode);
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Analisis Keterlambatan');

        // Row 1: Title
        $titleText = 'LAPORAN ANALISIS KENDALA & KETERLAMBATAN KINERJA STAFF (' . $periodeText . ')';
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', $titleText);
        $sheet->getStyle('A1:H1')->getFont()->setBold(true)->setSize(11)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1:H1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('8B0000'); // Dark Red Accent for Delay Analysis
        $sheet->getStyle('A1:H1')->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Metadata
        $sheet->setCellValue('A3', 'NAMA STAFF');
        $sheet->setCellValue('B3', ': ' . $namaStaff);
        $sheet->setCellValue('A4', 'JABATAN');
        $sheet->setCellValue('B4', ': ' . $jabatanStaff);
        $sheet->setCellValue('A5', 'UNIT');
        $sheet->setCellValue('B5', ': ' . $unitStaff);
        $sheet->getStyle('A3:A5')->getFont()->setBold(true);

        // Headers
        $headers = [
            'A7' => 'NO',
            'B7' => 'NAMA STAFF & JABATAN',
            'C7' => 'TUGAS UTAMA (TERLAMBAT)',
            'D7' => 'ESTIMASI VS REALISASI',
            'E7' => 'KETERLAMBATAN',
            'F7' => 'DIAGNOSTIK KENDALA',
            'G7' => 'RINCIAN PEKERJAAN BENTROK',
            'H7' => 'REKOMENDASI EVALUASI',
        ];

        foreach ($headers as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }

        $headerRange = 'A7:H7';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(9)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('8B0000');
        $sheet->getStyle($headerRange)->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(7)->setRowHeight(25);

        $rowNum = 8;
        $no = 1;

        foreach ($items as $task) {
            $isLate = false;
            $estimasiSelesai = $task->estimasi_tanggal_selesai;

            if (!empty($task->tanggal_selesai) && !empty($estimasiSelesai) && $task->tanggal_selesai > $estimasiSelesai) {
                $isLate = true;
            } elseif (($task->status !== 'Selesai') && !empty($estimasiSelesai) && $estimasiSelesai < $today) {
                $isLate = true;
            }

            if (!$isLate) continue;

            $startDate = $task->estimasi_tanggal_mulai ?? $task->tanggal_mulai ?? $task->created_at->format('Y-m-d');
            $endDate = !empty($task->tanggal_selesai) ? $task->tanggal_selesai : $today;

            $overlappingInsidentil = Insidentil::where('user_id', $task->user_id)
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->where(function ($sub) use ($startDate, $endDate) {
                        $sub->whereNotNull('estimasi_tanggal_mulai')
                            ->where('estimasi_tanggal_mulai', '<=', $endDate)
                            ->where('estimasi_tanggal_selesai', '>=', $startDate);
                    })->orWhere(function ($sub) use ($startDate, $endDate) {
                        $sub->whereNotNull('tanggal_mulai')
                            ->where('tanggal_mulai', '<=', $endDate)
                            ->where(function ($nest) use ($startDate) {
                                $nest->where('tanggal_selesai', '>=', $startDate)
                                    ->orWhereNull('tanggal_selesai');
                            });
                    });
                })->get();

            $overlappingKepanitiaan = Kepanitiaan::where(function ($q) use ($task) {
                $q->where('user_id', $task->user_id)
                    ->orWhereHas('taggedUsers', function ($tu) use ($task) {
                        $tu->where('users.id', $task->user_id);
                    });
            })->where(function ($q) use ($startDate, $endDate) {
                $q->where(function ($sub) use ($startDate, $endDate) {
                    $sub->whereNotNull('estimasi_tanggal_mulai')
                        ->where('estimasi_tanggal_mulai', '<=', $endDate)
                        ->where('estimasi_tanggal_selesai', '>=', $startDate);
                })->orWhere(function ($sub) use ($startDate, $endDate) {
                    $sub->whereNotNull('tanggal_mulai')
                        ->where('tanggal_mulai', '<=', $endDate)
                        ->where(function ($nest) use ($startDate) {
                            $nest->where('tanggal_selesai', '>=', $startDate)
                                ->orWhereNull('tanggal_selesai');
                        });
                });
            })->get();

            $hasIns = $overlappingInsidentil->count() > 0;
            $hasPan = $overlappingKepanitiaan->count() > 0;

            $diagLabel = 'Keterlambatan Murni Staff';
            $rekomendasi = 'Pendampingan & monitoring manajemen waktu rutin.';
            if ($hasIns && $hasPan) {
                $diagLabel = 'Beban Ganda (Panitia & Insidentil)';
                $rekomendasi = 'Redistribusi porsi tugas mendesak & penyesuaian deadline.';
            } elseif ($hasIns) {
                $diagLabel = 'Terganggu Tugas Insidentil';
                $rekomendasi = 'Redistribusi porsi tugas insidentil dari pimpinan.';
            } elseif ($hasPan) {
                $diagLabel = 'Beban Tugas Kepanitiaan';
                $rekomendasi = 'Evaluasi porsi keterlibatan anggota panitia.';
            }

            if (!empty($task->saran_pimpinan)) {
                $rekomendasi = '[Saran Rektor] ' . $task->saran_pimpinan;
            }

            $bentrokanText = [];
            if ($hasIns) {
                $insList = $overlappingInsidentil->pluck('uraian_tugas')->implode(', ');
                $bentrokanText[] = 'Insidentil: ' . $insList;
            }
            if ($hasPan) {
                $panList = $overlappingKepanitiaan->pluck('nama_kegiatan')->implode(', ');
                $bentrokanText[] = 'Kepanitiaan: ' . ($panList ?: 'Kegiatan Panitia');
            }
            $bentrokanStr = !empty($bentrokanText) ? implode("\n", $bentrokanText) : 'Tidak Ada Bentrokan';

            $delayDays = max(1, (int) Carbon::parse($estimasiSelesai)->diffInDays(Carbon::parse($endDate)));

            $sheet->setCellValue('A' . $rowNum, $no++);
            $sheet->setCellValue('B' . $rowNum, ($task->user ? $task->user->name : '-') . "\n(" . ($task->user ? ($task->user->jabatan ?? '-') : '-') . ')');
            $sheet->setCellValue('C' . $rowNum, $task->uraian_tugas);
            $sheet->setCellValue('D' . $rowNum, 'Est: ' . ($task->estimasi_tanggal_selesai ? date('d/m/Y', strtotime($task->estimasi_tanggal_selesai)) : '-') . "\nReal: " . ($task->tanggal_selesai ? date('d/m/Y', strtotime($task->tanggal_selesai)) : 'Belum Selesai'));
            $sheet->setCellValue('E' . $rowNum, $delayDays . ' Hari');
            $sheet->setCellValue('F' . $rowNum, $diagLabel);
            $sheet->setCellValue('G' . $rowNum, $bentrokanStr);
            $sheet->setCellValue('H' . $rowNum, $rekomendasi);

            $sheet->getStyle('A' . $rowNum . ':E' . $rowNum)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
            $sheet->getStyle('F' . $rowNum . ':H' . $rowNum)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
            $rowNum++;
        }

        $lastRow = max(7, $rowNum - 1);
        $tableRange = 'A7:H' . $lastRow;
        $sheet->getStyle($tableRange)->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
            ->getColor()->setRGB('CCCCCC');
        $sheet->getStyle($tableRange)->getAlignment()->setWrapText(true);

        $widths = ['A' => 6, 'B' => 25, 'C' => 35, 'D' => 22, 'E' => 15, 'F' => 28, 'G' => 35, 'H' => 35];
        foreach ($widths as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        $filename = 'Laporan_Analisis_Keterlambatan_' . date('Ymd_His') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'analisis_export_');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Export Analisis Keterlambatan to PDF.
     */
    public function exportPdf(Request $request)
    {
        $authUser = Auth::user();
        $today = Carbon::now()->format('Y-m-d');

        $query = RencanaKerja::with(['user', 'periodeAkademik', 'taggedUsers']);

        if ($authUser) {
            if ($authUser->isAdmin() || $authUser->isPimpinanRektorat() || $authUser->isPimpinanUnit()) {
                if ($request->filled('user_id')) {
                    $query->where('user_id', $request->user_id);
                }
                if ($request->filled('jabatan')) {
                    $query->whereHas('user', function ($q) use ($request) {
                        $q->where('jabatan', $request->jabatan);
                    });
                }
            } else {
                $query->where(function ($q) use ($authUser) {
                    $q->where('user_id', $authUser->id)
                      ->orWhereHas('taggedUsers', function ($qu) use ($authUser) {
                          $qu->where('users.id', $authUser->id);
                      });
                });
            }
        }

        if ($request->filled('periode_akademik_id')) {
            $query->where('periode_akademik_id', $request->periode_akademik_id);
        }

        $allTasks = $query->latest()->get();

        if ($request->filled('leadership_level')) {
            $levelReq = (int) $request->leadership_level;
            $allTasks = $allTasks->filter(function ($task) use ($levelReq) {
                return $this->getLeadershipLevel($task->user) === $levelReq;
            });
        }

        if ($request->filled('unit')) {
            $unitReq = strtoupper($request->unit);
            $allTasks = $allTasks->filter(function ($task) use ($unitReq) {
                return $task->user && str_contains(strtoupper($task->user->unit ?? ''), $unitReq);
            });
        }
        $lateTasks = collect();

        foreach ($allTasks as $task) {
            $isLate = false;
            $estimasiSelesai = $task->estimasi_tanggal_selesai;

            if (!empty($task->tanggal_selesai) && !empty($estimasiSelesai) && $task->tanggal_selesai > $estimasiSelesai) {
                $isLate = true;
            } elseif (($task->status !== 'Selesai') && !empty($estimasiSelesai) && $estimasiSelesai < $today) {
                $isLate = true;
            }

            if (!$isLate) continue;

            $startDate = $task->estimasi_tanggal_mulai ?? $task->tanggal_mulai ?? $task->created_at->format('Y-m-d');
            $endDate = !empty($task->tanggal_selesai) ? $task->tanggal_selesai : $today;

            $overlappingInsidentil = Insidentil::where('user_id', $task->user_id)
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->where(function ($sub) use ($startDate, $endDate) {
                        $sub->whereNotNull('estimasi_tanggal_mulai')
                            ->where('estimasi_tanggal_mulai', '<=', $endDate)
                            ->where('estimasi_tanggal_selesai', '>=', $startDate);
                    })->orWhere(function ($sub) use ($startDate, $endDate) {
                        $sub->whereNotNull('tanggal_mulai')
                            ->where('tanggal_mulai', '<=', $endDate)
                            ->where(function ($nest) use ($startDate) {
                                $nest->where('tanggal_selesai', '>=', $startDate)
                                    ->orWhereNull('tanggal_selesai');
                            });
                    });
                })->get();

            $overlappingKepanitiaan = Kepanitiaan::where(function ($q) use ($task) {
                $q->where('user_id', $task->user_id)
                    ->orWhereHas('taggedUsers', function ($tu) use ($task) {
                        $tu->where('users.id', $task->user_id);
                    });
            })->where(function ($q) use ($startDate, $endDate) {
                $q->where(function ($sub) use ($startDate, $endDate) {
                    $sub->whereNotNull('estimasi_tanggal_mulai')
                        ->where('estimasi_tanggal_mulai', '<=', $endDate)
                        ->where('estimasi_tanggal_selesai', '>=', $startDate);
                })->orWhere(function ($sub) use ($startDate, $endDate) {
                    $sub->whereNotNull('tanggal_mulai')
                        ->where('tanggal_mulai', '<=', $endDate)
                        ->where(function ($nest) use ($startDate) {
                            $nest->where('tanggal_selesai', '>=', $startDate)
                                ->orWhereNull('tanggal_selesai');
                        });
                });
            })->get();

            $hasIns = $overlappingInsidentil->count() > 0;
            $hasPan = $overlappingKepanitiaan->count() > 0;

            if ($hasIns && $hasPan) {
                $task->kategori_kendala = 'beban_ganda';
            } elseif ($hasIns) {
                $task->kategori_kendala = 'insidentil';
            } elseif ($hasPan) {
                $task->kategori_kendala = 'kepanitiaan';
            } else {
                $task->kategori_kendala = 'murni';
            }

            $task->overlapping_insidentil = $overlappingInsidentil;
            $task->overlapping_kepanitiaan = $overlappingKepanitiaan;
            $task->delay_days = max(1, (int) Carbon::parse($estimasiSelesai)->diffInDays(Carbon::parse($endDate)));

            $lateTasks->push($task);
        }

        $lateTasks = $lateTasks->sortBy(function ($task) {
            return sprintf('%03d_%s', $this->getPositionRank($task->user), $task->user ? $task->user->name : '');
        })->values();

        $namaStaff = 'SEMUA STAFF';
        $jabatanStaff = 'SEMUA JABATAN';
        $unitStaff = 'SEMUA UNIT';

        if ($request->filled('user_id') && ($u = User::find($request->user_id))) {
            $namaStaff = strtoupper($u->name);
            $jabatanStaff = strtoupper($u->jabatan ?? '-');
            $unitStaff = strtoupper($u->unit ?? '-');
        }

        $periodeText = 'SEMUA PERIODE';
        if ($request->filled('periode_akademik_id') && ($p = PeriodeAkademik::find($request->periode_akademik_id))) {
            $periodeText = strtoupper($p->nama_periode);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pages.analisis_keterlambatan.pdf', compact('lateTasks', 'namaStaff', 'jabatanStaff', 'unitStaff', 'periodeText'));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('Laporan_Analisis_Keterlambatan_' . date('Ymd_His') . '.pdf');
    }

    /**
     * Update Saran Pimpinan / Rektor for delayed task.
     */
    public function updateSaranPimpinan(Request $request, $id)
    {
        $authUser = Auth::user();
        if (!$authUser || (!$authUser->isAdmin() && !$authUser->isPimpinanRektorat() && !$authUser->isPimpinanUnit())) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk memberikan saran / rekomendasi evaluasi pimpinan.'
            ], 403);
        }

        $request->validate([
            'saran_pimpinan' => 'nullable|string|max:2000',
        ]);

        $task = RencanaKerja::findOrFail($id);
        $task->saran_pimpinan = $request->saran_pimpinan;
        $task->save();

        return response()->json([
            'success' => true,
            'message' => 'Catatan & Rekomendasi Evaluasi Pimpinan berhasil disimpan.',
        ]);
    }

    /**
     * Get search keywords for filtering staff belonging to a unit leader.
     */
    protected function getUnitKeywords($user): array
    {
        if (!$user) {
            return [];
        }

        $unit = strtoupper(trim($user->unit ?? ''));
        $jabatan = strtoupper(trim($user->jabatan ?? ''));

        // 1. LPTI / ICT
        if (str_contains($unit, 'LPTI') || str_contains($jabatan, 'LPTI') || str_contains($unit, 'ICT')) {
            return ['LPTI', 'ICT', 'PROGRAMMER', 'INFRASTRUKTUR', 'TROUBLESHOOTING', 'SISTEM INFORMASI'];
        }

        // 2. FEB (Fakultas Ekonomi dan Bisnis)
        if (str_contains($unit, 'FEB') || str_contains($unit, 'EKONOMI') || str_contains($jabatan, 'FEB') || str_contains($jabatan, 'EKONOMI')) {
            return ['FEB', 'EKONOMI', 'MANAJEMEN', 'AKUNTANSI', 'PASCASARJANA MM'];
        }

        // 3. FST (Fakultas Sains dan Teknologi)
        if (str_contains($unit, 'FST') || str_contains($unit, 'SAINS') || str_contains($jabatan, 'FST') || str_contains($jabatan, 'TEKNIK')) {
            return ['FST', 'SAINS', 'TEKNOLOGI', 'TEKNIK', 'INDUSTRI', 'INFORMATIKA', 'LOGISTIK', 'PERKAPALAN', 'LABORATORIUM'];
        }

        // 4. FIKES (Fakultas Ilmu Kesehatan)
        if (str_contains($unit, 'FIKES') || str_contains($unit, 'KESEHATAN') || str_contains($jabatan, 'FIKES') || str_contains($jabatan, 'K3') || str_contains($jabatan, 'KESLING')) {
            return ['FIKES', 'KESEHATAN', 'K3', 'KESLING', 'LABORAN'];
        }

        // 5. BAAK
        if (str_contains($unit, 'BAAK') || str_contains($unit, 'AKADEMIK') || str_contains($jabatan, 'BAAK') || str_contains($jabatan, 'PUSTAKA') || str_contains($jabatan, 'PERPUSTAKAAN')) {
            return ['BAAK', 'AKADEMIK', 'LAYANAN', 'PUSTAKA', 'PERPUSTAKAAN', 'IJAZAH'];
        }

        // 6. BAUK
        if (str_contains($unit, 'BAUK') || str_contains($unit, 'UMUM') || str_contains($jabatan, 'BAUK') || str_contains($jabatan, 'KEUANGAN') || str_contains($jabatan, 'SARPRAS') || str_contains($jabatan, 'SDM')) {
            return ['BAUK', 'KEUANGAN', 'SDM', 'SARPRAS', 'TATA USAHA', 'KASIR', 'KEPEGAWAIAN'];
        }

        // 7. LPPM
        if (str_contains($unit, 'LPPM') || str_contains($jabatan, 'LPPM')) {
            return ['LPPM', 'PENELITIAN', 'PENGABDIAN', 'HAKI'];
        }

        // 8. LPMI
        if (str_contains($unit, 'LPMI') || str_contains($jabatan, 'LPMI')) {
            return ['LPMI', 'MUTU', 'SPMI', 'AKREDITASI', 'AUDIT'];
        }

        // 9. BIRO KEMAHASISWAAN / HUMAS
        if (str_contains($unit, 'KEMAHASISWAAN') || str_contains($unit, 'HUMAS') || str_contains($jabatan, 'HUMAS') || str_contains($jabatan, 'ALUMNI') || str_contains($jabatan, 'KERJASAMA')) {
            return ['KEMAHASISWAAN', 'HUMAS', 'KERJASAMA', 'ALUMNI', 'KARIR', 'PERENCANAAN'];
        }

        if (!empty($unit)) {
            return [$unit];
        }

        return [];
    }

    /**
     * Get hierarchy priority rank for sorting (1 = highest rank, 100 = lowest).
     */
    protected function getPositionRank($user): int
    {
        if (!$user || empty($user->jabatan)) {
            return 99;
        }

        $j = strtoupper(trim($user->jabatan));

        // Rektorat
        if (str_contains($j, 'REKTOR') && !str_contains($j, 'WAKIL REKTOR')) return 1;
        if (str_contains($j, 'WAKIL REKTOR I') || str_contains($j, 'WAREK I') || str_contains($j, 'WAREK 1')) return 2;
        if (str_contains($j, 'WAKIL REKTOR II') || str_contains($j, 'WAREK II') || str_contains($j, 'WAREK 2')) return 3;
        if (str_contains($j, 'WAKIL REKTOR III') || str_contains($j, 'WAREK III') || str_contains($j, 'WAREK 3')) return 4;

        // Dekanat
        if (str_contains($j, 'DEKAN') && !str_contains($j, 'WAKIL DEKAN')) return 5;
        // Cek II sebelum I agar tidak ada substring collision ('WAKIL DEKAN I' ada dalam 'WAKIL DEKAN II')
        if (str_contains($j, 'WAKIL DEKAN II') || str_contains($j, 'WAKIL DEKAN 2') || str_contains($j, 'WADEK II') || str_contains($j, 'WADEK 2')) return 7;
        if (str_contains($j, 'WAKIL DEKAN I') || str_contains($j, 'WAKIL DEKAN 1') || str_contains($j, 'WADEK I') || str_contains($j, 'WADEK 1')) return 6;
        if (str_contains($j, 'WAKIL DEKAN')) return 8;

        // Kepala Lembaga, Biro & UPT
        if (str_contains($j, 'KA. LPPM') || str_contains($j, 'KEPALA LPPM')) return 10;
        if (str_contains($j, 'KA. LPMI') || str_contains($j, 'KEPALA LPMI')) return 11;
        if (str_contains($j, 'KA. BIRO') || str_contains($j, 'KEPALA BIRO')) return 12;
        if (str_contains($j, 'KEPALA ICT') || str_contains($j, 'KEPALA LPTI') || str_contains($j, 'KA. LPTI')) return 13;
        if (str_contains($j, 'KEPALA PERPUSTAKAAN') || (str_contains($j, 'KEPALA') && !str_contains($j, 'LAB'))) return 14;

        // Pimpinan Operasional / Kaprodi / Kabid
        if (str_contains($j, 'KETUA PROGRAM STUDI') || str_contains($j, 'KAPRODI') || str_contains($j, 'KA. PRODI')) return 15;
        if (str_contains($j, 'SEKRETARIS PRODI') || str_contains($j, 'SEKPRODI')) return 16;
        if (str_contains($j, 'KABID')) return 17;
        if (str_contains($j, 'KA. LABORATORIUM') || str_contains($j, 'KA. UPPM') || str_contains($j, 'KA. HUMAS') || str_contains($j, 'UPMI') || str_contains($j, 'GKM')) return 18;

        // Staf Pelaksana & Support
        if (str_contains($j, 'PROGRAMMER') || str_contains($j, 'DIVISI')) return 19;
        if (str_contains($j, 'STAFF') || str_contains($j, 'STAF') || str_contains($j, 'KASIR') || str_contains($j, 'LABORAN') || str_contains($j, 'PUSTAKAWAN')) return 20;

        return 50;
    }

    /**
     * Map user position to leadership level (1: Eksekutif/Rektorat, 2: Dekanat/Biro, 3: Kaprodi/Kabid, 4: Staf).
     */
    protected function getLeadershipLevel($user): int
    {
        if (!$user || empty($user->jabatan)) {
            return 4;
        }

        $jabatan = strtoupper(trim($user->jabatan));

        // Level 1: Pimpinan Universitas / Rektorat Utama (Rektor, Warek I, Warek II, Warek III)
        if (in_array($jabatan, ['REKTOR', 'WAKIL REKTOR I', 'WAKIL REKTOR II', 'WAKIL REKTOR III']) || $jabatan === 'REKTOR' || str_starts_with($jabatan, 'WAKIL REKTOR')) {
            return 1;
        }

        // Level 2: Pimpinan Unit / Dekanat / Kepala Biro / Kepala Lembaga / UPT
        if (
            (str_contains($jabatan, 'DEKAN') && !str_contains($jabatan, 'WAKIL DEKAN')) ||
            str_contains($jabatan, 'KA. LPPM') || str_contains($jabatan, 'KEPALA LPPM') ||
            str_contains($jabatan, 'KA. LPMI') || str_contains($jabatan, 'KEPALA LPMI') ||
            str_contains($jabatan, 'KA. BIRO') || str_contains($jabatan, 'KEPALA BIRO') ||
            str_contains($jabatan, 'Ka LPTI') || str_contains($jabatan, 'KEPALA LPTI') ||
            str_contains($jabatan, 'KEPALA PERPUSTAKAAN')
        ) {
            return 2;
        }

        // Level 3: Pimpinan Operasional & Akademik (Wadek, Kaprodi, Sekprodi, Kabid, Ka. Lab, Ka. UPPM, Ka. Humas, UPMI, GKM)
        if (
            str_contains($jabatan, 'WAKIL DEKAN') ||
            str_contains($jabatan, 'KETUA PROGRAM STUDI') || str_contains($jabatan, 'KAPRODI') ||
            str_contains($jabatan, 'SEKRETARIS PRODI') || str_contains($jabatan, 'SEKPRODI') ||
            str_contains($jabatan, 'KABID') ||
            str_contains($jabatan, 'KA. LABORATORIUM') || str_contains($jabatan, 'KA. UPPM') ||
            str_contains($jabatan, 'KA. HUMAS') || str_contains($jabatan, 'UPMI') ||
            str_contains($jabatan, 'GKM')
        ) {
            return 3;
        }

        // Level 4: Staf Pelaksana / Support
        return 4;
    }

    /**
     * Map user position to full organizational hierarchy path & keys.
     */
    protected function getHierarchyStructure($user): array
    {
        if (!$user || empty($user->jabatan)) {
            return [
                'divisi_key' => '2. Rektorat UIS',
                'divisi' => 'Rektorat UIS',
                'sub_unit' => 'Umum',
                'full_path' => 'Universitas Ibnu Sina ➔ Staf Pelaksana'
            ];
        }

        $j = strtoupper($user->jabatan);
        $u = strtoupper($user->unit ?? '');

        // 1. Rektor
        if (in_array($j, ['REKTOR', 'WAKIL REKTOR I', 'WAKIL REKTOR II', 'WAKIL REKTOR III'])) {
            return [
                'divisi_key' => '1. Rektor (UIS)',
                'divisi' => 'Rektor (UIS)',
                'sub_unit' => 'Rektorat Utama',
                'full_path' => 'Rektor (UIS) ➔ ' . $user->jabatan
            ];
        }

        // 2. Rektorat - Warek I
        if (str_contains($u, 'BAAK') || str_contains($j, 'AKADEMIK') || str_contains($j, 'PERPUSTAKAAN') || str_contains($j, 'PUSTAKAWAN') || str_contains($j, 'IJAZAH') || str_contains($j, 'NILAI')) {
            $sub = 'Biro Administrasi Akademik Kemahasiswaan (BAAK)';
            if (str_contains($j, 'PERPUSTAKAAN') || str_contains($j, 'PUSTAKAWAN')) {
                $sub = 'Perpustakaan';
            }
            return [
                'divisi_key' => '2. Rektorat UIS',
                'divisi' => 'Rektorat UIS (Warek I)',
                'sub_unit' => 'A. Wakil Rektor I (BAAK & Perpustakaan)',
                'full_path' => 'Rektorat UIS ➔ Warek I ➔ ' . $sub . ' ➔ ' . $user->jabatan
            ];
        }

        // 3. Rektorat - Warek II
        if (str_contains($u, 'BAUK') || str_contains($u, 'SDM') || str_contains($u, 'SARPRAS') || str_contains($j, 'KEUANGAN') || str_contains($j, 'KASIR') || str_contains($j, 'KEPEGAWAIAN') || str_contains($j, 'SARPRAS')) {
            return [
                'divisi_key' => '2. Rektorat UIS',
                'divisi' => 'Rektorat UIS (Warek II)',
                'sub_unit' => 'B. Wakil Rektor II (BAUK, SDM, Keuangan & Sarpras)',
                'full_path' => 'Rektorat UIS ➔ Warek II ➔ ' . $user->jabatan
            ];
        }

        // 4. UPT ICT / LPTI
        if (str_contains($u, 'LPTI') || str_contains($u, 'ICT') || str_contains($j, 'ICT') || str_contains($j, 'LPTI') || str_contains($j, 'PROGRAMMER') || str_contains($j, 'IT SUPPORT')) {
            return [
                'divisi_key' => '2. Rektorat UIS',
                'divisi' => 'Rektorat UIS (UPT ICT)',
                'sub_unit' => 'C. UPT Teknik Informasi dan Komunikasi (ICT/LPTI)',
                'full_path' => 'UPT ICT ➔ ' . $user->jabatan
            ];
        }

        // 5. Rektorat - Warek III
        if (str_contains($u, 'BIRO KEMAHASISWAAN') || str_contains($u, 'HUMAS') || str_contains($u, 'KERJASAMA') || str_contains($u, 'PUSAT KARIR') || str_contains($u, 'PERENCANAAN') || str_contains($j, 'HUMAS') || str_contains($j, 'KERJASAMA') || str_contains($j, 'PUSAT KARIR') || str_contains($j, 'PERENCANAAN') || str_contains($j, 'PRESTASI')) {
            return [
                'divisi_key' => '2. Rektorat UIS',
                'divisi' => 'Rektorat UIS (Warek III)',
                'sub_unit' => 'D. Wakil Rektor III (Kemahasiswaan, Humas, Kerjasama & Perencanaan)',
                'full_path' => 'Rektorat UIS ➔ Warek III ➔ ' . $user->jabatan
            ];
        }

        // 6. LPPM
        if (str_contains($u, 'LPPM') || str_contains($j, 'LPPM') || str_contains($j, 'PENELITIAN') || str_contains($j, 'PENGABDIAN') || str_contains($j, 'HAKI')) {
            return [
                'divisi_key' => '3. LPPM (Penelitian & Pengabdian)',
                'divisi' => 'LPPM UIS',
                'sub_unit' => 'LPPM UIS',
                'full_path' => 'LPPM ➔ ' . $user->jabatan
            ];
        }

        // 7. LPMI
        if (str_contains($u, 'LPMI') || str_contains($j, 'LPMI') || str_contains($j, 'SPMI') || str_contains($j, 'AKREDITASI') || str_contains($j, 'AUDIT MUTU')) {
            return [
                'divisi_key' => '4. LPMI (Penjamin Mutu Internal)',
                'divisi' => 'LPMI UIS',
                'sub_unit' => 'LPMI UIS',
                'full_path' => 'LPMI ➔ ' . $user->jabatan
            ];
        }

        // 8. FEB
        if (str_contains($u, 'FEB') || str_contains($u, 'EKONOMI') || str_contains($u, 'MANAJEMEN') || str_contains($u, 'AKUNTANSI') || str_contains($j, 'FEB') || str_contains($j, 'MANAJEMEN') || str_contains($j, 'AKUNTANSI')) {
            $sub = 'Dekanat FEB';
            if (str_contains($j, 'MANAJEMEN') && str_contains($j, 'S2')) $sub = 'Prodi Pascasarjana S2 Magister Manajemen';
            elseif (str_contains($j, 'MANAJEMEN')) $sub = 'Prodi S1 Manajemen';
            elseif (str_contains($j, 'AKUNTANSI')) $sub = 'Prodi S1 Akuntansi';

            return [
                'divisi_key' => '5. Fakultas Ekonomi dan Bisnis (FEB)',
                'divisi' => 'Fakultas Ekonomi dan Bisnis',
                'sub_unit' => $sub,
                'full_path' => 'FEB ➔ ' . $sub . ' ➔ ' . $user->jabatan
            ];
        }

        // 9. FST / FT
        if (str_contains($u, 'FST') || str_contains($u, 'TEKNIK') || str_contains($j, 'FST') || str_contains($j, 'INDUSTRI') || str_contains($j, 'INFORMATIKA') || str_contains($j, 'LOGISTIK') || str_contains($j, 'LABORATORIUM')) {
            $sub = 'Dekanat FST';
            if (str_contains($j, 'INDUSTRI')) $sub = 'Prodi Teknik Industri';
            elseif (str_contains($j, 'INFORMATIKA') || str_contains($j, 'SISTEM INFORMASI')) $sub = 'Prodi Teknik Informatika & Sistem Informasi';
            elseif (str_contains($j, 'LOGISTIK') || str_contains($j, 'PERKAPALAN')) $sub = 'Prodi Teknik Logistik & Perkapalan';
            elseif (str_contains($j, 'LABOR') || str_contains($j, 'LABORATORIUM')) $sub = 'Laboratorium FST';

            return [
                'divisi_key' => '6. Fakultas Sains dan Teknologi (FST)',
                'divisi' => 'Fakultas Sains dan Teknologi',
                'sub_unit' => $sub,
                'full_path' => 'FST ➔ ' . $sub . ' ➔ ' . $user->jabatan
            ];
        }

        // 10. FIKES
        if (str_contains($u, 'FIKES') || str_contains($u, 'KESEHATAN') || str_contains($j, 'FIKES') || str_contains($j, 'K3') || str_contains($j, 'KESLING') || str_contains($j, 'LABORAN')) {
            $sub = 'Dekanat FIKES';
            if (str_contains($j, 'K3')) $sub = 'Prodi K3';
            elseif (str_contains($j, 'KESLING')) $sub = 'Prodi Kesling';

            return [
                'divisi_key' => '7. Fakultas Ilmu Kesehatan (FIKES)',
                'divisi' => 'Fakultas Ilmu Kesehatan',
                'sub_unit' => $sub,
                'full_path' => 'FIKES ➔ ' . $sub . ' ➔ ' . $user->jabatan
            ];
        }

        return [
            'divisi_key' => '2. Rektorat UIS',
            'divisi' => 'Universitas Ibnu Sina',
            'sub_unit' => $user->unit ?? 'Unit Kerja',
            'full_path' => 'UIS ➔ ' . ($user->unit ?? 'Unit') . ' ➔ ' . $user->jabatan
        ];
    }

    /**
     * Build nested organizational hierarchy tree for interactive modal navigator.
     */
    protected function getHierarchyTree($allUsers): array
    {
        $tree = [
            '1. Rektor (UIS)' => [
                'icon' => 'bi-award-fill text-warning',
                'sub' => [
                    'Rektorat Utama' => []
                ]
            ],
            '2. Rektorat UIS' => [
                'icon' => 'bi-building-fill text-primary',
                'sub' => [
                    'A. Wakil Rektor I (BAAK & Perpustakaan)' => [],
                    'B. Wakil Rektor II (BAUK, SDM, Keuangan & Sarpras)' => [],
                    'C. UPT Teknik Informasi dan Komunikasi (ICT/LPTI)' => [],
                    'D. Wakil Rektor III (Kemahasiswaan, Humas, Kerjasama & Perencanaan)' => [],
                ]
            ],
            '3. LPPM (Penelitian & Pengabdian)' => [
                'icon' => 'bi-journal-bookmark-fill text-success',
                'sub' => [
                    'LPPM UIS' => []
                ]
            ],
            '4. LPMI (Penjamin Mutu Internal)' => [
                'icon' => 'bi-shield-check text-info',
                'sub' => [
                    'LPMI UIS' => []
                ]
            ],
            '5. Fakultas Ekonomi dan Bisnis (FEB)' => [
                'icon' => 'bi-mortarboard-fill text-danger',
                'sub' => [
                    'Dekanat FEB' => [],
                    'Prodi S1 Manajemen' => [],
                    'Prodi S1 Akuntansi' => [],
                    'Prodi Pascasarjana S2 Magister Manajemen' => [],
                ]
            ],
            '6. Fakultas Sains dan Teknologi (FST)' => [
                'icon' => 'bi-gear-wide-connected text-dark',
                'sub' => [
                    'Dekanat FST' => [],
                    'Prodi Teknik Industri' => [],
                    'Prodi Teknik Informatika & Sistem Informasi' => [],
                    'Prodi Teknik Logistik & Perkapalan' => [],
                    'Laboratorium FST' => [],
                ]
            ],
            '7. Fakultas Ilmu Kesehatan (FIKES)' => [
                'icon' => 'bi-heart-pulse-fill text-success',
                'sub' => [
                    'Dekanat FIKES' => [],
                    'Prodi K3' => [],
                    'Prodi Kesling' => [],
                ]
            ],
        ];

        foreach ($allUsers as $u) {
            $struct = $this->getHierarchyStructure($u);
            $mainKey = $struct['divisi_key'] ?? '2. Rektorat UIS';
            $subKey = $struct['sub_unit'] ?? 'Rektorat Utama';

            if (!isset($tree[$mainKey])) {
                $mainKey = '2. Rektorat UIS';
            }
            if (!isset($tree[$mainKey]['sub'][$subKey])) {
                $tree[$mainKey]['sub'][$subKey] = [];
            }

            $tree[$mainKey]['sub'][$subKey][] = [
                'id' => $u->id,
                'name' => $u->name,
                'jabatan' => $u->jabatan,
                'unit' => $u->unit,
                'level' => $u->level ?? $this->getLeadershipLevel($u)
            ];
        }

        return $tree;
    }
}
