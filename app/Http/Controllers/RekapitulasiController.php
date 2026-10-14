<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use App\Models\RencanaKerja;
use App\Models\PeriodeAkademik;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yajra\DataTables\Facades\DataTables;

class RekapitulasiController extends Controller
{
    /**
     * Display the Rekapitulasi page.
     */
    public function index()
    {
        $authUser = Auth::user();
        if (!$authUser) {
            return redirect()->route('login');
        }

        $periodeAkademiks = PeriodeAkademik::orderBy('nama_periode', 'desc')->get();
        $defaultPeriode = PeriodeAkademik::first();
        $defaultPeriodeId = $defaultPeriode ? $defaultPeriode->id : null;

        $usersWithJabatan = $this->getDirectSubordinatesQuery($authUser)->get();

        return view('pages.rekapitulasi.index', compact('periodeAkademiks', 'defaultPeriodeId', 'usersWithJabatan'));
    }

    /**
     * Build base query for Rekapitulasi based on filters and user role.
     */
    private function buildRekapQuery(Request $request)
    {
        $authUser = Auth::user();
        $query = Kegiatan::with(['user', 'periodeAkademik', 'taggedUsers']);

        $isPureAdminOrRektorat = $this->isPureAdminOrRektorat($authUser);

        if (!$request->filled('jabatan') && !$isPureAdminOrRektorat) {
            $subordinateIds = $this->getDirectSubordinatesQuery($authUser)->pluck('id')->toArray();
            $query->where(function ($q) use ($subordinateIds) {
                $q->whereIn('user_id', $subordinateIds)
                  ->orWhereHas('taggedUsers', function ($qu) use ($subordinateIds) {
                      $qu->whereIn('users.id', $subordinateIds);
                  });
            });
        }

        $filterType = $request->input('filter_type', 'periode');
        $startDate = $request->input('tanggal_mulai') ?: $request->input('start_date');
        $endDate = $request->input('tanggal_selesai') ?: $request->input('end_date');

        if ($filterType === 'range' || (!$request->filled('periode_akademik_id') && ($startDate || $endDate))) {
            if ($startDate && $endDate) {
                $query->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('tanggal_mulai', [$startDate, $endDate])
                      ->orWhereBetween('tanggal_selesai', [$startDate, $endDate])
                      ->orWhereBetween('estimasi_tanggal_mulai', [$startDate, $endDate])
                      ->orWhereBetween('estimasi_tanggal_selesai', [$startDate, $endDate])
                      ->orWhere(function ($sub) use ($startDate, $endDate) {
                          $sub->whereDate('created_at', '>=', $startDate)
                              ->whereDate('created_at', '<=', $endDate);
                      });
                });
            } elseif ($startDate) {
                $query->where(function ($q) use ($startDate) {
                    $q->whereDate('tanggal_mulai', '>=', $startDate)
                      ->orWhereDate('estimasi_tanggal_mulai', '>=', $startDate)
                      ->orWhereDate('created_at', '>=', $startDate);
                });
            } elseif ($endDate) {
                $query->where(function ($q) use ($endDate) {
                    $q->whereDate('tanggal_selesai', '<=', $endDate)
                      ->orWhereDate('estimasi_tanggal_selesai', '<=', $endDate)
                      ->orWhereDate('created_at', '<=', $endDate);
                });
            }
        } else {
            if ($request->filled('periode_akademik_id')) {
                $query->where('periode_akademik_id', $request->periode_akademik_id);
            }
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->filled('status_filter')) {
            $st = $request->status_filter;
            if ($st === 'belum_selesai') {
                $query->where('status', '!=', 'Selesai');
            } elseif ($st === 'terlambat') {
                $nowStr = now()->format('Y-m-d');
                $query->where(function ($q) use ($nowStr) {
                    $q->where(function ($sub) use ($nowStr) {
                        $sub->where('status', '!=', 'Selesai')
                            ->whereNotNull('estimasi_tanggal_selesai')
                            ->where('estimasi_tanggal_selesai', '<', $nowStr);
                    })->orWhere(function ($sub) {
                        $sub->where('status', 'Selesai')
                            ->whereNotNull('estimasi_tanggal_selesai')
                            ->whereNotNull('tanggal_selesai')
                            ->whereColumn('tanggal_selesai', '>', 'estimasi_tanggal_selesai');
                    });
                });
            } elseif ($st === 'proses') {
                $query->whereIn('status', ['Proses', 'Berjalan']);
            } elseif ($st === 'belum_dimulai') {
                $query->where('status', 'Belum Dimulai');
            } elseif ($st === 'selesai') {
                $query->where('status', 'Selesai');
            }
        }

        if ($request->filled('jabatan')) {
            $filterValue = $request->jabatan;
            if (is_numeric($filterValue)) {
                $query->where(function ($q) use ($filterValue) {
                    $q->whereHas('taggedUsers', function ($qu) use ($filterValue) {
                        $qu->where('users.id', $filterValue);
                    })->orWhere(function ($sub) use ($filterValue) {
                        $sub->doesntHave('taggedUsers')
                            ->where('user_id', $filterValue);
                    });
                });
            } else {
                $query->where(function ($q) use ($request) {
                    $q->whereHas('taggedUsers', function ($qt) use ($request) {
                        $qt->where('jabatan', $request->jabatan);
                    })->orWhere(function ($sub) use ($request) {
                        $sub->doesntHave('taggedUsers')
                            ->whereHas('user', function ($qu) use ($request) {
                                $qu->where('jabatan', $request->jabatan);
                            });
                    });
                });
            }
        }

        return $query;
    }

    /**
     * Calculate KPI counts and performance analysis for given query.
     */
    private function calculateRekapStats($query)
    {
        $nowStr = now()->format('Y-m-d');
        $total = (clone $query)->count();
        $belum = (clone $query)->where('status', 'Belum Dimulai')->count();
        $proses = (clone $query)->whereIn('status', ['Proses', 'Berjalan'])->count();
        $selesai = (clone $query)->where('status', 'Selesai')->count();
        $terlambat = (clone $query)->where(function ($q) use ($nowStr) {
            $q->where(function ($sub) use ($nowStr) {
                $sub->where('status', '!=', 'Selesai')
                    ->whereNotNull('estimasi_tanggal_selesai')
                    ->where('estimasi_tanggal_selesai', '<', $nowStr);
            })->orWhere(function ($sub) {
                $sub->where('status', 'Selesai')
                    ->whereNotNull('estimasi_tanggal_selesai')
                    ->whereNotNull('tanggal_selesai')
                    ->whereColumn('tanggal_selesai', '>', 'estimasi_tanggal_selesai');
            });
        })->count();
        $percent = $total > 0 ? round(($selesai / $total) * 100) : 0;

        $predikat = 'Sangat Kurang';
        if ($percent >= 90) {
            $predikat = 'Sangat Baik';
        } elseif ($percent >= 75) {
            $predikat = 'Baik';
        } elseif ($percent >= 60) {
            $predikat = 'Cukup';
        } elseif ($percent >= 40) {
            $predikat = 'Kurang';
        }

        return [
            'total' => $total,
            'belum' => $belum,
            'proses' => $proses,
            'terlambat' => $terlambat,
            'selesai' => $selesai,
            'percent' => $percent,
            'predikat' => $predikat,
        ];
    }

    /**
     * Get DataTable source and overall rekap counts for Rencana Kerja Tab.
     */
    public function getData(Request $request)
    {
        $authUser = Auth::user();
        if (!$authUser) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $query = RencanaKerja::with(['user', 'periodeAkademik', 'taggedUsers']);

        $isPureAdminOrRektorat = $this->isPureAdminOrRektorat($authUser);

        if (!$isPureAdminOrRektorat) {
            $subordinateIds = $this->getDirectSubordinatesQuery($authUser)->pluck('id')->toArray();
            if (!$request->filled('jabatan')) {
                $query->where(function ($q) use ($subordinateIds) {
                    $q->whereIn('user_id', $subordinateIds)
                      ->orWhereHas('taggedUsers', function ($qu) use ($subordinateIds) {
                          $qu->whereIn('users.id', $subordinateIds);
                      });
                });
            }
        }

        if ($request->filled('periode_akademik_id')) {
            $query->where('periode_akademik_id', $request->periode_akademik_id);
        }

        if ($request->filled('jabatan')) {
            $filterValue = $request->jabatan;
            if (is_numeric($filterValue)) {
                $query->where(function ($q) use ($filterValue) {
                    $q->where('user_id', $filterValue)
                      ->orWhereHas('taggedUsers', function ($qu) use ($filterValue) {
                          $qu->where('users.id', $filterValue);
                      });
                });
            } else {
                $query->where(function ($q) use ($request) {
                    $q->whereHas('user', function ($qu) use ($request) {
                        $qu->where('jabatan', $request->jabatan);
                    })->orWhereHas('taggedUsers', function ($qt) use ($request) {
                        $qt->where('jabatan', $request->jabatan);
                    });
                });
            }
        }

        $nowStr = now()->format('Y-m-d');
        $total = (clone $query)->count();
        $belum = (clone $query)->where('status', 'Belum Dimulai')->count();
        $proses = (clone $query)->whereIn('status', ['Proses', 'Berjalan'])->count();
        $selesai = (clone $query)->where('status', 'Selesai')->count();
        $terlambat = (clone $query)->where(function ($q) use ($nowStr) {
            $q->where(function ($sub) use ($nowStr) {
                $sub->where('status', '!=', 'Selesai')
                    ->whereNotNull('estimasi_tanggal_selesai')
                    ->where('estimasi_tanggal_selesai', '<', $nowStr);
            })->orWhere(function ($sub) {
                $sub->where('status', 'Selesai')
                    ->whereNotNull('estimasi_tanggal_selesai')
                    ->whereNotNull('tanggal_selesai')
                    ->whereColumn('tanggal_selesai', '>', 'estimasi_tanggal_selesai');
            });
        })->count();
        $percent = $total > 0 ? round(($selesai / $total) * 100) : 0;

        // Tentukan subIds untuk filter tampilan kolom pembuat (reuse dari subordinateIds di atas)
        if ($isPureAdminOrRektorat) {
            $subIds = null; // null = tampilkan semua
        } elseif ($request->filled('jabatan') && is_numeric($request->jabatan)) {
            $subIds = [(int) $request->jabatan];
        } else {
            // $subordinateIds sudah dihitung di atas (baris filter query), reuse
            $subIds = $subordinateIds ?? $this->getDirectSubordinatesQuery($authUser)->pluck('id')->toArray();
        }

        return DataTables::of($query)
            ->addColumn('pembuat', function ($row) use ($subIds) {
                if ($row->taggedUsers && $row->taggedUsers->count() > 0) {
                    $visible = $subIds !== null
                        ? $row->taggedUsers->filter(fn($u) => in_array($u->id, $subIds))
                        : $row->taggedUsers;
                    if ($visible->count() > 0) {
                        return $visible->map(function ($u) {
                            return $u->name . ($u->jabatan ? ' (' . $u->jabatan . ')' : '');
                        })->implode(', ');
                    }
                }
                return $row->user ? $row->user->name . ' (' . ($row->user->jabatan ?? '-') . ')' : '-';
            })
            ->addColumn('rekan_kerja', function ($row) {
                if ($row->taggedUsers->count() > 0) {
                    return $row->taggedUsers->pluck('name')->implode(', ');
                }
                return '-';
            })
            ->addColumn('estimasi_waktu', function ($row) {
                $html = '<div class="small">';
                if ($row->estimasi_tanggal_mulai && $row->estimasi_tanggal_selesai) {
                    $html .= '<div class="fw-semibold text-dark"><i class="bi bi-calendar3 me-1 text-secondary"></i>' . date('d/m/Y', strtotime($row->estimasi_tanggal_mulai)) . ' - ' . date('d/m/Y', strtotime($row->estimasi_tanggal_selesai)) . '</div>';
                } elseif ($row->estimasi_tanggal_selesai) {
                    $html .= '<div class="fw-semibold text-dark"><i class="bi bi-calendar-event me-1 text-secondary"></i>s/d ' . date('d/m/Y', strtotime($row->estimasi_tanggal_selesai)) . '</div>';
                }
                if ($row->estimasi_jam_mulai && $row->estimasi_jam_selesai) {
                    $html .= '<div class="text-muted"><i class="bi bi-clock me-1"></i>' . substr($row->estimasi_jam_mulai, 0, 5) . ' - ' . substr($row->estimasi_jam_selesai, 0, 5) . '</div>';
                }
                if ($row->estimasi_durasi_hari || $row->estimasi_durasi_jam) {
                    $durStr = \App\Helpers\KinerjaWaktuHelper::formatDurasiTarget($row->estimasi_durasi_hari, $row->estimasi_durasi_jam);
                    if ($durStr) {
                        $html .= '<div class="mt-1"><span class="badge bg-success-subtle text-success border border-success-subtle px-1.5 py-0.5" style="font-size:0.75rem;"><i class="bi bi-stopwatch me-1"></i>Target: ' . e($durStr) . '</span></div>';
                    }
                }
                $html .= '</div>';
                return $html ?: '<span class="text-muted small">-</span>';
            })
            ->addColumn('realisasi_waktu', function ($row) {
                $html = '<div class="small">';
                $realDate = '-';
                if ($row->tanggal_mulai && $row->tanggal_selesai) {
                    $realDate = date('d/m/Y', strtotime($row->tanggal_mulai)) . ' - ' . date('d/m/Y', strtotime($row->tanggal_selesai));
                } elseif ($row->tanggal_mulai) {
                    $realDate = 'Mulai ' . date('d/m/Y', strtotime($row->tanggal_mulai));
                } elseif ($row->tanggal_selesai) {
                    $realDate = 'Selesai ' . date('d/m/Y', strtotime($row->tanggal_selesai));
                }
                if ($realDate !== '-') {
                    $html .= '<div class="fw-semibold text-dark"><i class="bi bi-calendar-check me-1 text-primary"></i>' . $realDate . '</div>';
                }
                $durasiStr = \App\Helpers\KinerjaWaktuHelper::formatDurasiRealisasi($row);
                if ($durasiStr) {
                    $html .= '<div class="mt-0.5" style="color:#0f766e;"><i class="bi bi-hourglass-split me-1"></i>Lama: <strong>' . e($durasiStr) . '</strong></div>';
                }
                $html .= '</div>';
                return $html ?: '<span class="text-muted small">-</span>';
            })
            ->addColumn('status_badge', function ($row) {
                if ($row->status === 'Selesai') {
                    return '<span class="badge bg-success px-2 py-1"><i class="bi bi-check-circle me-1"></i>Selesai</span>';
                } elseif ($row->status === 'Proses' || $row->status === 'Berjalan') {
                    if ($row->is_paused) {
                        return '<span class="badge bg-warning text-dark px-2 py-1"><i class="bi bi-pause-circle me-1"></i>Dijeda</span>';
                    }
                    return '<span class="badge bg-primary px-2 py-1"><i class="bi bi-play-circle me-1"></i>Proses</span>';
                }
                return '<span class="badge bg-secondary px-2 py-1"><i class="bi bi-x-circle me-1"></i>Belum Dimulai</span>';
            })
            ->addColumn('ketepatan_badge', function ($row) {
                $eval = \App\Helpers\KinerjaWaktuHelper::evaluasiKetepatanWaktu($row);
                $res = '<div class="mb-1">' . $eval['badge_html'] . '</div>';
                $res .= '<div class="small text-muted" style="font-size:0.78rem; line-height: 1.3;"><i class="bi bi-info-circle me-1"></i>' . e($eval['keterangan']) . '</div>';
                return $res;
            })
            ->rawColumns(['pembuat', 'status_badge', 'estimasi_waktu', 'realisasi_waktu', 'ketepatan_badge'])
            ->with('rekap', [
                'total' => $total,
                'belum' => $belum,
                'proses' => $proses,
                'terlambat' => $terlambat,
                'selesai' => $selesai,
                'percent' => $percent
            ])
            ->make(true);
    }

    /**
     * Format total duration of a task in hours and minutes.
     */
    private function formatDurasiPengerjaan($item)
    {
        $durasiDetik = 0;
        $isBerjalan = false;

        if (!empty($item->waktu_mulai) && !empty($item->waktu_selesai) && $item->waktu_selesai !== '00:00:00') {
            $tglM = !empty($item->tanggal_mulai) ? $item->tanggal_mulai : now()->format('Y-m-d');
            $tglS = !empty($item->tanggal_selesai) ? $item->tanggal_selesai : $tglM;
            $startTs = strtotime($tglM . ' ' . $item->waktu_mulai);
            $endTs = strtotime($tglS . ' ' . $item->waktu_selesai);
            if ($endTs > $startTs) {
                $durasiDetik = $endTs - $startTs;
            } else {
                $tDiff = strtotime($item->waktu_selesai) - strtotime($item->waktu_mulai);
                if ($tDiff > 0) $durasiDetik = $tDiff;
            }
        }

        if ($durasiDetik <= 0 && !empty($item->durasi_detik) && $item->durasi_detik > 0) {
            $durasiDetik = (int) $item->durasi_detik;
        }

        if ($durasiDetik <= 0 && in_array($item->status, ['Proses', 'Berjalan'])) {
            if (!empty($item->waktu_mulai)) {
                $tglM = !empty($item->tanggal_mulai) ? $item->tanggal_mulai : now()->format('Y-m-d');
                $diff = time() - strtotime($tglM . ' ' . $item->waktu_mulai);
                if ($diff > 0) {
                    $durasiDetik = $diff;
                    $isBerjalan = true;
                }
            } elseif (!empty($item->last_started_at)) {
                $diff = now()->diffInSeconds($item->last_started_at);
                if ($diff > 0) {
                    $durasiDetik = (int) $diff + (int) ($item->durasi_detik ?? 0);
                    $isBerjalan = true;
                }
            }
        }

        if ($durasiDetik <= 0 && !empty($item->tanggal_mulai) && !empty($item->tanggal_selesai)) {
            $d1 = strtotime($item->tanggal_mulai);
            $d2 = strtotime($item->tanggal_selesai);
            if ($d2 >= $d1) {
                $days = floor(($d2 - $d1) / 86400) + 1;
                $durasiDetik = $days * 8 * 3600;
            }
        }

        if ($durasiDetik <= 0) {
            return null;
        }

        $h = floor($durasiDetik / 3600);
        $m = floor(($durasiDetik % 3600) / 60);

        if ($h > 0 && $m > 0) {
            $str = $h . ' jam ' . $m . ' menit';
        } elseif ($h > 0) {
            $str = $h . ' jam';
        } elseif ($m > 0) {
            $str = $m . ' menit';
        } else {
            $str = '< 1 menit';
        }

        return $isBerjalan ? $str . ' (berjalan)' : $str;
    }

    /**
     * Resolve metadata text labels for export.
     */
    private function resolveMetaTexts(Request $request)
    {
        $filterType = $request->input('filter_type', 'periode');
        $startDate = $request->input('tanggal_mulai') ?: $request->input('start_date');
        $endDate = $request->input('tanggal_selesai') ?: $request->input('end_date');

        $periodeText = 'SEMUA PERIODE';
        if ($filterType === 'range' || (!$request->filled('periode_akademik_id') && ($startDate || $endDate))) {
            if ($startDate && $endDate) {
                $periodeText = date('d/m/Y', strtotime($startDate)) . ' s/d ' . date('d/m/Y', strtotime($endDate));
            } elseif ($startDate) {
                $periodeText = 'DARI ' . date('d/m/Y', strtotime($startDate));
            } elseif ($endDate) {
                $periodeText = 'SAMPAI ' . date('d/m/Y', strtotime($endDate));
            } else {
                $periodeText = 'RENTANG TANGGAL (SEMUA)';
            }
        } elseif ($request->filled('periode_akademik_id') && ($p = PeriodeAkademik::find($request->periode_akademik_id))) {
            $periodeText = strtoupper($p->nama_periode);
        }

        $kategoriText = 'SEMUA KEGIATAN';
        if ($request->filled('kategori')) {
            $kategoriText = strtoupper($request->kategori);
        }

        $statusFilterText = 'SEMUA STATUS';
        if ($request->filled('status_filter')) {
            $statusMap = [
                'belum_selesai' => 'BELUM SELESAI (SEMUA KENDALA)',
                'terlambat' => 'TERLAMBAT (LEWAT ESTIMASI)',
                'proses' => 'SEDANG PROSES',
                'belum_dimulai' => 'BELUM DIMULAI',
                'selesai' => 'SUDAH SELESAI',
            ];
            $statusFilterText = $statusMap[$request->status_filter] ?? strtoupper($request->status_filter);
        }

        $staffText = 'SEMUA PEGAWAI';
        if ($request->filled('jabatan')) {
            if (is_numeric($request->jabatan) && ($targetU = User::find($request->jabatan))) {
                $staffText = strtoupper($targetU->name . ' (' . ($targetU->jabatan ?? '-') . ')');
            } else {
                $staffText = strtoupper($request->jabatan);
            }
        } elseif (!$this->isPureAdminOrRektorat(Auth::user())) {
            if (Auth::user()->isPimpinanUnit() && !empty(Auth::user()->unit)) {
                $staffText = 'SEMUA PEGAWAI UNIT ' . strtoupper(Auth::user()->unit);
            } else {
                $staffText = strtoupper(Auth::user()->name . ' (' . (Auth::user()->jabatan ?? '-') . ')');
            }
        }

        return [$periodeText, $kategoriText, $statusFilterText, $staffText];
    }

    /**
     * Export Rekapitulasi Capaian & Analisis Kendala to Excel.
     */
    public function exportExcel(Request $request)
    {
        $authUser = Auth::user();
        $query = $this->buildRekapQuery($request);
        $rekap = $this->calculateRekapStats($query);
        $items = $query->latest()->get();

        [$periodeText, $kategoriText, $statusFilterText, $staffText] = $this->resolveMetaTexts($request);

        // Tentukan subordinateIds agar Excel hanya menampilkan tagged users dari unit yang relevan
        $isPureAdminOrRektorat = $this->isPureAdminOrRektorat($authUser);
        if ($isPureAdminOrRektorat) {
            $excelSubIds = null;
        } elseif ($request->filled('jabatan') && is_numeric($request->jabatan)) {
            $excelSubIds = [(int) $request->jabatan];
        } else {
            $excelSubIds = $this->getDirectSubordinatesQuery($authUser)->pluck('id')->toArray();
        }

        $spreadsheet = new Spreadsheet();

        // =========================================================================
        // SHEET 1: DAFTAR KEGIATAN (Detail Data & Realisasi Kinerja)
        // =========================================================================
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Daftar Kegiatan');

        // Row 1: Institution Header
        $sheet1->mergeCells('A1:H1');
        $sheet1->setCellValue('A1', 'UNIVERSITAS IBNU SINA');
        $sheet1->getStyle('A1:H1')->getFont()->setName('Arial')->setSize(11)->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet1->getStyle('A1:H1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF15432D');
        $sheet1->getStyle('A1:H1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet1->getRowDimension(1)->setRowHeight(24);

        // Row 2: Title
        $sheet1->mergeCells('A2:H2');
        $sheet1->setCellValue('A2', 'REKAPITULASI CAPAIAN & REALISASI KINERJA');
        $sheet1->getStyle('A2:H2')->getFont()->setName('Arial')->setSize(13)->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet1->getStyle('A2:H2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF15432D');
        $sheet1->getStyle('A2:H2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet1->getRowDimension(2)->setRowHeight(28);

        // Rows 4-5: Compact filter metadata
        foreach (['A4:B4', 'C4:D4', 'E4:F4', 'G4:H4', 'A5:B5', 'C5:D5', 'E5:F5', 'G5:H5'] as $range) {
            $sheet1->mergeCells($range);
        }
        $sheet1->fromArray([
            ['PERIODE / WAKTU :', null, $periodeText, null, 'STATUS :', null, $statusFilterText, null],
            ['KATEGORI :', null, $kategoriText, null, 'PEGAWAI :', null, $staffText, null],
        ], null, 'A4');
        $sheet1->getStyle('A4:H5')->getFont()->setName('Arial')->setSize(9.5)->setBold(true)->getColor()->setARGB('FF15432D');
        $sheet1->getStyle('A4:H5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF0FDF4');
        $sheet1->getStyle('A4:H5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
        $sheet1->getStyle('A4:H5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FF86EFAC');
        $sheet1->getRowDimension(4)->setRowHeight(22);
        $sheet1->getRowDimension(5)->setRowHeight(30);

        // Row 6: Data Table Headers
        $tableHeaders = [
            'A6' => 'NO',
            'B6' => 'KATEGORI',
            'C6' => 'URAIAN KEGIATAN / TUGAS',
            'D6' => 'PEGAWAI / JABATAN',
            'E6' => 'ESTIMASI WAKTU',
            'F6' => 'REALISASI WAKTU',
            'G6' => 'STATUS',
            'H6' => 'ANALISIS KENDALA & TINDAK LANJUT',
        ];
        foreach ($tableHeaders as $cell => $val) {
            $sheet1->setCellValue($cell, $val);
        }
        $sheet1->getStyle('A6:H6')->getFont()->setName('Arial')->setSize(9)->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet1->getStyle('A6:H6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF15432D');
        $sheet1->getStyle('A6:H6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet1->getRowDimension(6)->setRowHeight(25);

        // Rows 7+: Data Rows
        $rowNum = 7;
        $no = 1;
        foreach ($items as $item) {
            $isSelesai = $item->status === 'Selesai';
            $eval = \App\Helpers\KinerjaWaktuHelper::evaluasiKetepatanWaktu($item);
            $isTerlambat = $eval['is_terlambat'];

            $kat = $item->kategori ?: 'Rencana Kerja';

            if ($item->taggedUsers && $item->taggedUsers->count() > 0) {
                $staffParts = [];
                // Filter tagged users: hanya tampilkan bawahan unit kepala yang login
                $visibleTaggedExcel = $excelSubIds !== null
                    ? $item->taggedUsers->filter(fn($u) => in_array($u->id, $excelSubIds))
                    : $item->taggedUsers;
                $sourceUsers = $visibleTaggedExcel->count() > 0 ? $visibleTaggedExcel : collect([$item->user])->filter();
                foreach ($sourceUsers as $tUser) {
                    $part = $tUser->name;
                    if (!empty($tUser->jabatan)) {
                        $part .= "\n(" . $tUser->jabatan . ')';
                    }
                    $staffParts[] = $part;
                }
                $staffInfo = implode("\n", $staffParts);
            } else {
                $staffInfo = $item->user ? $item->user->name : '-';
                if ($item->user && $item->user->jabatan) {
                    $staffInfo .= "\n(" . $item->user->jabatan . ')';
                }
            }

            $estFormatted = '-';
            if ($item->estimasi_tanggal_mulai && $item->estimasi_tanggal_selesai) {
                $estFormatted = date('d/m/Y', strtotime($item->estimasi_tanggal_mulai)) . ' - ' . date('d/m/Y', strtotime($item->estimasi_tanggal_selesai));
            } elseif ($item->estimasi_tanggal_selesai) {
                $estFormatted = 's/d ' . date('d/m/Y', strtotime($item->estimasi_tanggal_selesai));
            }

            if ($item->estimasi_jam_mulai && $item->estimasi_jam_selesai) {
                $jamStr = substr($item->estimasi_jam_mulai, 0, 5) . ' - ' . substr($item->estimasi_jam_selesai, 0, 5) . ' WIB';
                $estFormatted = ($estFormatted !== '-' ? $estFormatted . "\n" : '') . $jamStr;
            }

            if ($item->estimasi_durasi_hari || $item->estimasi_durasi_jam) {
                $durTarget = \App\Helpers\KinerjaWaktuHelper::formatDurasiTarget($item->estimasi_durasi_hari, $item->estimasi_durasi_jam);
                if ($durTarget) {
                    $estFormatted = ($estFormatted !== '-' ? $estFormatted . "\n" : '') . "Target Durasi: " . $durTarget;
                }
            }

            $realFormatted = '-';
            if ($item->tanggal_mulai && $item->tanggal_selesai) {
                $realFormatted = date('d/m/Y', strtotime($item->tanggal_mulai)) . ' - ' . date('d/m/Y', strtotime($item->tanggal_selesai));
            } elseif ($item->tanggal_mulai) {
                $realFormatted = 'Mulai ' . date('d/m/Y', strtotime($item->tanggal_mulai));
            } elseif ($item->tanggal_selesai) {
                $realFormatted = 'Selesai ' . date('d/m/Y', strtotime($item->tanggal_selesai));
            }

            if ($item->waktu_selesai && $item->waktu_selesai !== '00:00:00') {
                $realFormatted = ($realFormatted !== '-' ? $realFormatted . ' ' : '') . '(' . substr($item->waktu_selesai, 0, 5) . ' WIB)';
            }

            $durasiStr = \App\Helpers\KinerjaWaktuHelper::formatDurasiRealisasi($item);
            if ($durasiStr) {
                if ($realFormatted === '-') {
                    $realFormatted = "Lama Pengerjaan:\n" . $durasiStr;
                } else {
                    $realFormatted .= "\nLama Pengerjaan:\n" . $durasiStr;
                }
            }

            $statusText = $item->status ?? 'Belum Dimulai';

            $kendalaParts = [];
            $kendalaParts[] = '[' . strtoupper($eval['sublabel']) . '] ' . $eval['keterangan'];

            if ($item->alasan_pause) {
                $kendalaParts[] = "[ALASAN PAUSE] " . $item->alasan_pause;
            }
            if ($item->rencana_tindak_lanjut) {
                $kendalaParts[] = "[TINDAK LANJUT] " . trim(strip_tags($item->rencana_tindak_lanjut));
            }
            $kendalaText = implode("\n", $kendalaParts);

            $sheet1->setCellValue('A' . $rowNum, $no++);
            $sheet1->setCellValue('B' . $rowNum, $kat);
            $sheet1->setCellValue('C' . $rowNum, $item->uraian_tugas ?: '-');
            $sheet1->setCellValue('D' . $rowNum, $staffInfo);
            $sheet1->setCellValue('E' . $rowNum, $estFormatted);
            $sheet1->setCellValue('F' . $rowNum, $realFormatted);
            $sheet1->setCellValue('G' . $rowNum, $statusText);
            $sheet1->setCellValue('H' . $rowNum, trim($kendalaText));

            $sheet1->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_TOP);
            $sheet1->getStyle('B' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_TOP);
            $sheet1->getStyle('C' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_TOP);
            $sheet1->getStyle('D' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_TOP);
            $sheet1->getStyle('E' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_TOP);
            $sheet1->getStyle('F' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_TOP);
            $sheet1->getStyle('G' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_TOP);
            $sheet1->getStyle('H' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_TOP);

            // Berikan warna latar dan teks pada STATUS agar mudah dipahami
            $statusFill = 'FFF1F5F9'; // default light gray
            $statusFont = 'FF475569'; // default slate gray

            if ($isSelesai) {
                $statusFill = 'FFDCFCE7'; // light green
                $statusFont = 'FF15803D'; // dark green
            } elseif ($item->status === 'Berjalan' || $item->status === 'Proses') {
                $statusFill = 'FFDBEAFE'; // light blue
                $statusFont = 'FF1D4ED8'; // dark blue
            } elseif ($item->status === 'Di-pause') {
                $statusFill = 'FFFEF3C7'; // light amber
                $statusFont = 'FFB45309'; // dark amber
            }

            $sheet1->getStyle('G' . $rowNum)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB($statusFill);
            $sheet1->getStyle('G' . $rowNum)->getFont()
                ->setBold(true)
                ->getColor()->setARGB($statusFont);

            if ($eval['is_terlambat']) {
                $sheet1->getStyle('H' . $rowNum)->getFont()->getColor()->setARGB('FFDC2626');
            } elseif ($eval['is_lebih_cepat']) {
                $sheet1->getStyle('H' . $rowNum)->getFont()->getColor()->setARGB('FF0284C7');
            } elseif ($eval['is_on_time'] && $isSelesai) {
                $sheet1->getStyle('H' . $rowNum)->getFont()->getColor()->setARGB('FF15803D');
            }

            $rowNum++;
        }

        if ($items->isEmpty()) {
            $sheet1->mergeCells('A7:H7');
            $sheet1->setCellValue('A7', 'Tidak ada data kegiatan yang sesuai dengan filter yang dipilih.');
            $sheet1->getStyle('A7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet1->getRowDimension(7)->setRowHeight(30);
            $rowNum = 8;
        }

        $lastRow = max(6, $rowNum - 1);
        $sheet1->getStyle('A6:H' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFCBD5E1');
        $sheet1->getStyle('C7:D' . $lastRow)->getAlignment()->setWrapText(true);
        $sheet1->getStyle('H7:H' . $lastRow)->getAlignment()->setWrapText(true);

        // Keterangan / Legend di bagian bawah Sheet 1
        $legRow = $rowNum + 1;
        $sheet1->mergeCells('A' . $legRow . ':H' . $legRow);
        $sheet1->setCellValue('A' . $legRow, 'KETERANGAN STATUS & PREDIKAT KINERJA');
        $sheet1->getStyle('A' . $legRow . ':H' . $legRow)->getFont()->setName('Arial')->setSize(9.5)->setBold(true)->getColor()->setARGB('FF15432D');
        $sheet1->getStyle('A' . $legRow . ':H' . $legRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F5F9');
        $sheet1->getStyle('A' . $legRow . ':H' . $legRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet1->getStyle('A' . $legRow . ':H' . $legRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFCBD5E1');
        $sheet1->getRowDimension($legRow)->setRowHeight(22);

        $legRow1 = $legRow + 1;
        $sheet1->mergeCells('A' . $legRow1 . ':H' . $legRow1);
        $sheet1->setCellValue('A' . $legRow1, 'Indikator Status: [SELESAI (Hijau)] Kegiatan tuntas  |  [PROSES (Biru)] Sedang berjalan  |  [DI-PAUSE (Kuning)] Dihentikan sementara  |  [BELUM MULAI (Abu)] Belum dieksekusi  |  [TERLAMBAT (Merah)] Melewati batas estimasi.');
        $sheet1->getStyle('A' . $legRow1)->getFont()->setSize(8.5)->getColor()->setARGB('FF334155');
        $sheet1->getStyle('A' . $legRow1 . ':H' . $legRow1)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFCBD5E1');

        $legRow2 = $legRow1 + 1;
        $sheet1->mergeCells('A' . $legRow2 . ':H' . $legRow2);
        $sheet1->setCellValue('A' . $legRow2, 'Kriteria Predikat: Sangat Baik (90% - 100%)  |  Baik (75% - 89%)  |  Cukup (60% - 74%)  |  Kurang (40% - 59%)  |  Sangat Kurang (< 40%).  (Detail matriks diagnosis lengkap tersedia pada Sheet "Analisis Kinerja")');
        $sheet1->getStyle('A' . $legRow2)->getFont()->setSize(8.5)->getColor()->setARGB('FF334155');
        $sheet1->getStyle('A' . $legRow2 . ':H' . $legRow2)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFCBD5E1');

        // Column Widths Sheet 1
        $colWidths = [
            'A' => 6,
            'B' => 16,
            'C' => 38,
            'D' => 26,
            'E' => 24,
            'F' => 24,
            'G' => 16,
            'H' => 38,
        ];
        foreach ($colWidths as $col => $w) {
            $sheet1->getColumnDimension($col)->setWidth($w);
        }

        // =========================================================================
        // SHEET 2: HASIL ANALISIS & DIAGNOSIS KINERJA (Dipisahkan Sheet-nya)
        // =========================================================================
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Analisis Kinerja');

        // Row 1: Header
        $sheet2->mergeCells('A1:H1');
        $sheet2->setCellValue('A1', 'UNIVERSITAS IBNU SINA');
        $sheet2->getStyle('A1:H1')->getFont()->setName('Arial')->setSize(11)->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet2->getStyle('A1:H1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF15432D');
        $sheet2->getStyle('A1:H1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet2->getRowDimension(1)->setRowHeight(24);

        // Row 2: Title
        $sheet2->mergeCells('A2:H2');
        $sheet2->setCellValue('A2', 'HASIL ANALISIS & DIAGNOSIS KINERJA PEGAWAI');
        $sheet2->getStyle('A2:H2')->getFont()->setName('Arial')->setSize(13)->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet2->getStyle('A2:H2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF15432D');
        $sheet2->getStyle('A2:H2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet2->getRowDimension(2)->setRowHeight(28);

        // Rows 4-5: Compact filter metadata
        foreach (['A4:B4', 'C4:D4', 'E4:F4', 'G4:H4', 'A5:B5', 'C5:D5', 'E5:F5', 'G5:H5'] as $range) {
            $sheet2->mergeCells($range);
        }
        $sheet2->fromArray([
            ['PERIODE / WAKTU :', null, $periodeText, null, 'STATUS :', null, $statusFilterText, null],
            ['KATEGORI :', null, $kategoriText, null, 'PEGAWAI :', null, $staffText, null],
        ], null, 'A4');
        $sheet2->getStyle('A4:H5')->getFont()->setName('Arial')->setSize(9.5)->setBold(true)->getColor()->setARGB('FF15432D');
        $sheet2->getStyle('A4:H5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF0FDF4');
        $sheet2->getStyle('A4:H5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
        $sheet2->getStyle('A4:H5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FF86EFAC');
        $sheet2->getRowDimension(4)->setRowHeight(22);
        $sheet2->getRowDimension(5)->setRowHeight(30);

        // Row 6: KPI Header
        $sheet2->mergeCells('A6:H6');
        $sheet2->setCellValue('A6', '1. RINGKASAN CAPAIAN & EVALUASI KINERJA');
        $sheet2->getStyle('A6:H6')->getFont()->setName('Arial')->setSize(10.5)->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet2->getStyle('A6:H6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF15432D');
        $sheet2->getStyle('A6:H6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet2->getRowDimension(6)->setRowHeight(24);

        // Row 7: KPI Headers
        $kpiHeaders = [
            'A7' => 'TOTAL TUGAS',
            'B7' => 'BELUM MULAI',
            'C7' => 'SEDANG PROSES',
            'D7' => 'TERLAMBAT (KENDALA)',
            'E7' => 'SUDAH SELESAI',
            'F7' => '% CAPAIAN KERJA',
            'G7' => 'PREDIKAT KINERJA',
            'H7' => 'STATUS EVALUASI',
        ];
        foreach ($kpiHeaders as $cell => $val) {
            $sheet2->setCellValue($cell, $val);
        }
        $sheet2->getStyle('A7:H7')->getFont()->setName('Arial')->setSize(8.5)->setBold(true)->getColor()->setARGB('FF334155');
        $sheet2->getStyle('A7:H7')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F5F9');
        $sheet2->getStyle('A7:H7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet2->getStyle('A7:H7')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFCBD5E1');
        $sheet2->getRowDimension(7)->setRowHeight(20);

        // Row 8: KPI Values
        $sheet2->setCellValue('A8', $rekap['total']);
        $sheet2->setCellValue('B8', $rekap['belum']);
        $sheet2->setCellValue('C8', $rekap['proses']);
        $sheet2->setCellValue('D8', $rekap['terlambat']);
        $sheet2->setCellValue('E8', $rekap['selesai']);
        $sheet2->setCellValue('F8', $rekap['percent'] . '%');
        $sheet2->setCellValue('G8', $rekap['predikat']);
        $sheet2->setCellValue('H8', $rekap['selesai'] . ' dari ' . $rekap['total'] . ' Selesai');

        $sheet2->getStyle('A8:F8')->getFont()->setName('Arial')->setSize(12)->setBold(true);
        $sheet2->getStyle('G8:H8')->getFont()->setName('Arial')->setSize(10)->setBold(true);
        $sheet2->getStyle('A8')->getFont()->getColor()->setARGB('FF0284C7'); // Cyan
        $sheet2->getStyle('B8')->getFont()->getColor()->setARGB('FF64748B'); // Gray
        $sheet2->getStyle('C8')->getFont()->getColor()->setARGB('FF2563EB'); // Blue
        $sheet2->getStyle('D8')->getFont()->getColor()->setARGB('FFDC2626'); // Red
        $sheet2->getStyle('E8')->getFont()->getColor()->setARGB('FF16A34A'); // Green
        $sheet2->getStyle('F8')->getFont()->getColor()->setARGB('FF15803D'); // Dark Green
        $sheet2->getStyle('G8')->getFont()->getColor()->setARGB('FF15803D');
        $sheet2->getStyle('H8')->getFont()->getColor()->setARGB('FF334155');

        $sheet2->getStyle('A8:H8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet2->getStyle('A8:H8')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFCBD5E1');
        $sheet2->getRowDimension(8)->setRowHeight(28);

        // Row 9: Narasi Diagnosis
        $narasiDiagnosis = 'Kesimpulan Diagnosis: Persentase penyelesaian kegiatan mencapai ' . $rekap['percent'] . '%. ';
        if ($rekap['terlambat'] > 0) {
            $narasiDiagnosis .= 'Terdapat ' . $rekap['terlambat'] . ' kegiatan mengalami kendala keterlambatan melewati batas estimasi tanggal selesai. ';
        } else {
            $narasiDiagnosis .= 'Tidak ada kegiatan yang mengalami keterlambatan. ';
        }
        if ($rekap['proses'] > 0) {
            $narasiDiagnosis .= 'Sebanyak ' . $rekap['proses'] . ' kegiatan sedang dalam proses pengerjaan.';
        }
        $sheet2->mergeCells('A9:H9');
        $sheet2->setCellValue('A9', $narasiDiagnosis);
        $sheet2->getStyle('A9')->getFont()->setName('Arial')->setSize(8.5)->setItalic(true)->getColor()->setARGB('FF475569');
        $sheet2->getStyle('A9:H9')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFCBD5E1');
        $sheet2->getRowDimension(9)->setRowHeight(22);

        // Row 11: Section Standar Kriteria Penilaian
        $sheet2->mergeCells('A11:H11');
        $sheet2->setCellValue('A11', '2. STANDAR KRITERIA PENILAIAN CAPAIAN KINERJA (PEDOMAN EVALUASI)');
        $sheet2->getStyle('A11:H11')->getFont()->setName('Arial')->setSize(10.5)->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet2->getStyle('A11:H11')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF15432D');
        $sheet2->getStyle('A11:H11')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet2->getRowDimension(11)->setRowHeight(24);

        $critHeaders = [
            'A12' => 'NO',
            'B12' => 'PREDIKAT KINERJA',
            'C12' => 'RENTANG CAPAIAN',
            'D12' => 'KATEGORI TARGET',
            'E12' => 'DESKRIPSI STANDAR & INDIKATOR EVALUASI',
        ];
        $sheet2->mergeCells('E12:H12');
        foreach ($critHeaders as $cell => $val) {
            $sheet2->setCellValue($cell, $val);
        }
        $sheet2->getStyle('A12:H12')->getFont()->setName('Arial')->setSize(8.5)->setBold(true)->getColor()->setARGB('FF334155');
        $sheet2->getStyle('A12:H12')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F5F9');
        $sheet2->getStyle('A12:D12')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet2->getStyle('E12:H12')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet2->getStyle('A12:H12')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFCBD5E1');
        $sheet2->getRowDimension(12)->setRowHeight(20);

        $kriteriaData = [
            [
                'no' => 1,
                'predikat' => 'Sangat Baik',
                'rentang' => '90% - 100%',
                'kategori' => 'Melampaui Target',
                'deskripsi' => 'Capaian kinerja sangat memuaskan, seluruh tugas utama terlaksana secara tepat waktu dan berkualitas prima.',
                'color' => 'FF15803D',
                'fill' => 'FFDCFCE7'
            ],
            [
                'no' => 2,
                'predikat' => 'Baik',
                'rentang' => '75% - 89%',
                'kategori' => 'Memenuhi Target',
                'deskripsi' => 'Capaian kinerja memenuhi target operasional dengan baik, sebagian besar sasaran tercapai.',
                'color' => 'FF16A34A',
                'fill' => 'FFF0FDF4'
            ],
            [
                'no' => 3,
                'predikat' => 'Cukup',
                'rentang' => '60% - 74%',
                'kategori' => 'Perlu Evaluasi',
                'deskripsi' => 'Capaian kinerja cukup memenuhi sasaran dasar, memerlukan monitoring dan tindak lanjut perbaikan kendala.',
                'color' => 'FFD97706',
                'fill' => 'FFFEF3C7'
            ],
            [
                'no' => 4,
                'predikat' => 'Kurang',
                'rentang' => '40% - 59%',
                'kategori' => 'Di Bawah Target',
                'deskripsi' => 'Capaian kinerja di bawah target minimal, memerlukan percepatan intensif dan pendampingan.',
                'color' => 'FFEA580C',
                'fill' => 'FFFFEDD5'
            ],
            [
                'no' => 5,
                'predikat' => 'Sangat Kurang',
                'rentang' => '< 40%',
                'kategori' => 'Evaluasi Khusus',
                'deskripsi' => 'Capaian kinerja jauh di bawah target standar, memerlukan evaluasi kendala mendalam dan bimbingan terfokus.',
                'color' => 'FF991B1B',
                'fill' => 'FFFEE2E2'
            ],
        ];

        $cRow = 13;
        foreach ($kriteriaData as $kd) {
            $sheet2->setCellValue('A' . $cRow, $kd['no']);
            $sheet2->setCellValue('B' . $cRow, $kd['predikat']);
            $sheet2->setCellValue('C' . $cRow, $kd['rentang']);
            $sheet2->setCellValue('D' . $cRow, $kd['kategori']);
            $sheet2->mergeCells('E' . $cRow . ':H' . $cRow);
            $sheet2->setCellValue('E' . $cRow, $kd['deskripsi']);

            $sheet2->getStyle('A' . $cRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet2->getStyle('B' . $cRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet2->getStyle('C' . $cRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet2->getStyle('D' . $cRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet2->getStyle('E' . $cRow . ':H' . $cRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);

            $sheet2->getStyle('B' . $cRow)->getFont()->setBold(true)->getColor()->setARGB($kd['color']);
            $sheet2->getStyle('B' . $cRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($kd['fill']);
            $sheet2->getStyle('A' . $cRow . ':H' . $cRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFCBD5E1');
            $sheet2->getRowDimension($cRow)->setRowHeight(22);
            $cRow++;
        }

        // Section 3: Definisi Status
        $sRow = $cRow + 1;
        $sheet2->mergeCells('A' . $sRow . ':H' . $sRow);
        $sheet2->setCellValue('A' . $sRow, '3. KETERANGAN DEFINISI STATUS & INDIKATOR KENDALA');
        $sheet2->getStyle('A' . $sRow . ':H' . $sRow)->getFont()->setName('Arial')->setSize(10.5)->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet2->getStyle('A' . $sRow . ':H' . $sRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF15432D');
        $sheet2->getStyle('A' . $sRow . ':H' . $sRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet2->getRowDimension($sRow)->setRowHeight(24);

        $statHeadRow = $sRow + 1;
        $sheet2->setCellValue('A' . $statHeadRow, 'NO');
        $sheet2->setCellValue('B' . $statHeadRow, 'STATUS TUGAS');
        $sheet2->setCellValue('C' . $statHeadRow, 'WARNA INDIKATOR');
        $sheet2->mergeCells('D' . $statHeadRow . ':E' . $statHeadRow);
        $sheet2->setCellValue('D' . $statHeadRow, 'DEFINISI OPERASIONAL');
        $sheet2->mergeCells('F' . $statHeadRow . ':H' . $statHeadRow);
        $sheet2->setCellValue('F' . $statHeadRow, 'TINDAKAN / REKOMENDASI SISTEM');

        $sheet2->getStyle('A' . $statHeadRow . ':H' . $statHeadRow)->getFont()->setName('Arial')->setSize(8.5)->setBold(true)->getColor()->setARGB('FF334155');
        $sheet2->getStyle('A' . $statHeadRow . ':H' . $statHeadRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F5F9');
        $sheet2->getStyle('A' . $statHeadRow . ':C' . $statHeadRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet2->getStyle('D' . $statHeadRow . ':H' . $statHeadRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet2->getStyle('A' . $statHeadRow . ':H' . $statHeadRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFCBD5E1');
        $sheet2->getRowDimension($statHeadRow)->setRowHeight(20);

        $statusList = [
            ['no' => 1, 'nama' => 'Selesai', 'warna' => 'Hijau', 'definisi' => 'Kegiatan telah tuntas dikerjakan dan berkas bukti telah diunggah', 'tindakan' => 'Terverifikasi tuntas, dihitung dalam persentase capaian', 'color' => 'FF15803D', 'fill' => 'FFDCFCE7'],
            ['no' => 2, 'nama' => 'Sedang Proses', 'warna' => 'Biru', 'definisi' => 'Kegiatan sedang berjalan dalam tahap pengerjaan aktif oleh pegawai', 'tindakan' => 'Lanjutkan pengerjaan sesuai estimasi waktu yang direncanakan', 'color' => 'FF1D4ED8', 'fill' => 'FFDBEAFE'],
            ['no' => 3, 'nama' => 'Di-pause', 'warna' => 'Kuning', 'definisi' => 'Kegiatan dihentikan sementara waktu karena kendala atau hambatan operasional', 'tindakan' => 'Tinjau alasan pause dan selesaikan kendala agar dapat dilanjutkan', 'color' => 'FFB45309', 'fill' => 'FFFEF3C7'],
            ['no' => 4, 'nama' => 'Belum Dimulai', 'warna' => 'Abu-abu', 'definisi' => 'Kegiatan telah terjadwal namun belum dimulai pelaksanaan awalnya', 'tindakan' => 'Mulai pengerjaan kegiatan sesuai jadwal kalender akademik', 'color' => 'FF475569', 'fill' => 'FFF1F5F9'],
            ['no' => 5, 'nama' => 'Terlambat', 'warna' => 'Merah', 'definisi' => 'Kegiatan belum berstatus selesai dan telah melewati estimasi tanggal selesai', 'tindakan' => 'Perhatian khusus (kendala waktu), prioritaskan percepatan atau pengajuan perpanjangan', 'color' => 'FFDC2626', 'fill' => 'FFFEF2F2'],
        ];

        $curStatRow = $statHeadRow + 1;
        foreach ($statusList as $st) {
            $sheet2->setCellValue('A' . $curStatRow, $st['no']);
            $sheet2->setCellValue('B' . $curStatRow, $st['nama']);
            $sheet2->setCellValue('C' . $curStatRow, $st['warna']);
            $sheet2->mergeCells('D' . $curStatRow . ':E' . $curStatRow);
            $sheet2->setCellValue('D' . $curStatRow, $st['definisi']);
            $sheet2->mergeCells('F' . $curStatRow . ':H' . $curStatRow);
            $sheet2->setCellValue('F' . $curStatRow, $st['tindakan']);

            $sheet2->getStyle('A' . $curStatRow . ':C' . $curStatRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet2->getStyle('D' . $curStatRow . ':H' . $curStatRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);

            $sheet2->getStyle('B' . $curStatRow)->getFont()->setBold(true)->getColor()->setARGB($st['color']);
            $sheet2->getStyle('B' . $curStatRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($st['fill']);
            $sheet2->getStyle('A' . $curStatRow . ':H' . $curStatRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFCBD5E1');
            $sheet2->getRowDimension($curStatRow)->setRowHeight(22);
            $curStatRow++;
        }

        // Column Widths Sheet 2
        foreach ($colWidths as $col => $w) {
            $sheet2->getColumnDimension($col)->setWidth($w);
        }

        // Aktifkan kembali Sheet 1 sebagai sheet default yang terbuka
        $spreadsheet->setActiveSheetIndex(0);

        $safePeriode = trim(preg_replace('/[^A-Za-z0-9\-\s]/', '', str_replace(['/', '\\'], '-', $periodeText)));
        $safeKategori = trim(preg_replace('/[^A-Za-z0-9\-\s]/', '', str_replace(['/', '\\'], '-', $kategoriText)));
        $filename = 'rekapitulasi_kinerja_' . strtolower(str_replace(' ', '_', $safeKategori ?: 'semua')) . '_' . strtolower(str_replace(' ', '_', $safePeriode ?: 'periode')) . '.xlsx';

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Resolve signers (Kepala/Atasan di kiri, Pegawai di kanan) for PDF export.
     */
    private function resolveSigners(Request $request, $items)
    {
        $authUser = Auth::user();
        $targetPegawai = null;

        if ($request->filled('jabatan')) {
            if (is_numeric($request->jabatan)) {
                $targetPegawai = User::find($request->jabatan);
            } else {
                $targetPegawai = User::where('jabatan', $request->jabatan)->first();
            }
        }

        if (!$targetPegawai && $authUser) {
            if (!$authUser->isAdmin() && !$authUser->isPimpinanRektorat()) {
                $targetPegawai = $authUser;
            } elseif ($items->isNotEmpty()) {
                $userIds = $items->pluck('user_id')->unique();
                if ($userIds->count() === 1) {
                    $targetPegawai = User::find($userIds->first());
                }
            }
        }

        if (!$targetPegawai) {
            $targetPegawai = $authUser;
        }

        // Tentukan Kepala / Atasan di sebelah kiri
        $unit = $targetPegawai ? $targetPegawai->unit : ($authUser ? $authUser->unit : null);
        $kepala = null;

        if ($targetPegawai && ($targetPegawai->isPimpinanRektorat() || str_contains(strtoupper($targetPegawai->jabatan ?? ''), 'REKTOR'))) {
            $kepalaJabatan = 'Ketua Yayasan Pendidikan Ibnu Sina Batam';
            $kepalaNama = 'H. Andi Ibrahim, B.A.';
            $kepalaNidn = '-';
        } else {
            if (!empty($unit) && $unit !== '-') {
                $kepala = User::where('unit', $unit)
                    ->when($targetPegawai, fn($q) => $q->where('id', '!=', $targetPegawai->id))
                    ->where(function ($q) {
                        $q->where('roles', 'LIKE', '%pimpinan%')
                          ->orWhere('jabatan', 'LIKE', '%KEPALA%')
                          ->orWhere('jabatan', 'LIKE', '%KA.%')
                          ->orWhere('jabatan', 'LIKE', '%DEKAN%')
                          ->orWhere('jabatan', 'LIKE', '%KETUA%')
                          ->orWhere('jabatan', 'LIKE', '%KAPRODI%');
                    })
                    ->first();
            }

            if (!$kepala) {
                // Cari pimpinan universitas / Rektor
                $kepala = User::where('jabatan', 'LIKE', '%REKTOR%')
                    ->where('jabatan', 'NOT LIKE', '%WAKIL%')
                    ->where('jabatan', 'NOT LIKE', '%KASIR%')
                    ->first();
            }

            if ($kepala) {
                $kepalaJabatan = $kepala->jabatan ?: 'Kepala Unit / Atasan Langsung';
                $kepalaNama = $kepala->name;
                $kepalaNidn = $kepala->nidn ?: '-';
            } else {
                $kepalaJabatan = !empty($unit) && $unit !== '-' ? 'Kepala ' . $unit : 'Kepala / Atasan Langsung';
                $kepalaNama = '...................................';
                $kepalaNidn = '-';
            }
        }

        $pegawaiLabel = 'Pegawai Yang Bersangkutan';
        if ($targetPegawai && ($targetPegawai->isPimpinanRektorat() || $targetPegawai->isAdmin())) {
            $pegawaiLabel = 'Pimpinan Universitas Ibnu Sina';
        } elseif ($targetPegawai && $targetPegawai->isPimpinanUnit()) {
            $pegawaiLabel = $targetPegawai->jabatan ?: ('Kepala Unit ' . $targetPegawai->unit);
        }

        return [
            'kepalaJabatan' => $kepalaJabatan,
            'kepalaNama'    => $kepalaNama,
            'kepalaNidn'    => $kepalaNidn,
            'pegawaiLabel'  => $pegawaiLabel,
            'pegawaiNama'   => $targetPegawai ? $targetPegawai->name : ($authUser ? $authUser->name : '...................................'),
            'pegawaiNidn'   => $targetPegawai && $targetPegawai->nidn ? $targetPegawai->nidn : ($authUser && $authUser->nidn ? $authUser->nidn : '-'),
        ];
    }

    /**
     * Export Rekapitulasi Capaian & Analisis Kendala to PDF.
     */
    public function exportPdf(Request $request)
    {
        $authUser = Auth::user();
        $query = $this->buildRekapQuery($request);
        $rekap = $this->calculateRekapStats($query);
        $items = $query->latest()->get();

        [$periodeText, $kategoriText, $statusFilterText, $staffText] = $this->resolveMetaTexts($request);
        $signer = $this->resolveSigners($request, $items);

        // Tentukan subordinateIds agar PDF hanya menampilkan tagged users dari unit yang relevan
        $isPureAdminOrRektorat = $this->isPureAdminOrRektorat($authUser);
        if ($isPureAdminOrRektorat) {
            $subordinateIds = null; // null = tampilkan semua
        } elseif ($request->filled('jabatan') && is_numeric($request->jabatan)) {
            $subordinateIds = [(int) $request->jabatan]; // filter ke 1 user spesifik
        } else {
            $subordinateIds = $this->getDirectSubordinatesQuery($authUser)->pluck('id')->toArray();
        }

        $data = [
            'items' => $items,
            'rekap' => $rekap,
            'periodeText' => $periodeText,
            'kategoriText' => $kategoriText,
            'statusFilterText' => $statusFilterText,
            'staffText' => $staffText,
            'signer' => $signer,
            'subordinateIds' => $subordinateIds, // null = semua; array = filter ke IDs tersebut
        ];

        $pdf = Pdf::loadView('pages.rekapitulasi.pdf', $data);
        $pdf->setPaper('a4', 'landscape');

        $safePeriode = trim(preg_replace('/[^A-Za-z0-9\-\s]/', '', str_replace(['/', '\\'], '-', $periodeText)));
        $safeKategori = trim(preg_replace('/[^A-Za-z0-9\-\s]/', '', str_replace(['/', '\\'], '-', $kategoriText)));
        $filename = 'rekapitulasi_kinerja_' . strtolower(str_replace(' ', '_', $safeKategori ?: 'semua')) . '_' . strtolower(str_replace(' ', '_', $safePeriode ?: 'periode')) . '.pdf';

        return $pdf->stream($filename);
    }

    /**
     * Check if user is pure system admin or rektorat leader (overseeing all units).
     */
    private function isPureAdminOrRektorat($authUser): bool
    {
        if (!$authUser) {
            return false;
        }

        // Jika user adalah Kepala/Pimpinan Unit (Kepala LPTI, Dekan, Kaprodi, Ka Biro, dll),
        // maka otoritas operasionalnya dibatasi ke unit/bawahannya sendiri
        if ($authUser->isPimpinanUnit()) {
            return false;
        }

        // Pimpinan Rektorat murni (Rektor / Wakil Rektor di unit REKTORAT)
        if ($authUser->isPimpinanRektorat() && $authUser->unit === 'REKTORAT') {
            return true;
        }

        // Super Admin murni
        if (in_array(strtolower(trim($authUser->email ?? '')), ['admin@uis.ac.id', 'superadmin@uis.ac.id', 'admin@gmail.com'])
            || in_array($authUser->id, [1, 2])) {
            return true;
        }

        return false;
    }

    /**
     * Get query for users directly reporting to / under the given leader (Kepala).
     */
    private function getDirectSubordinatesQuery($authUser)
    {
        $ids = $this->resolveSubordinateIds($authUser);
        return User::whereIn('id', $ids)->where('is_active', true)->orderBy('name', 'asc');
    }

    /**
     * Ambil subordinate IDs untuk seorang user.
     * Prioritas: dari DB hierarchy (supervisor_id) yang berjenjang. Fallback: unit sendiri.
     *
     * @return int[]
     */
    private function resolveSubordinateIds($authUser): array
    {
        if (!$authUser) {
            return [];
        }

        // 1. Jika Super Admin murni atau Pimpinan Rektorat: seluruh civitas
        if ($this->isPureAdminOrRektorat($authUser)) {
            return User::where('is_active', true)->pluck('id')->toArray();
        }

        // 2. Prioritas utama: gunakan DB hierarchy (recursive)
        $dbIds = $authUser->getSubordinateIdsFromDb();
        if ($dbIds !== null && count($dbIds) > 0) {
            return $dbIds;
        }

        // 3. Fallback jika pimpinan unit belum di-set di DB
        if ($authUser->isPimpinanUnit()) {
            $unit = trim($authUser->unit ?? '');
            return User::where('is_active', true)
                ->where(function ($q) use ($authUser, $unit) {
                    $q->where('id', $authUser->id);
                    if (!empty($unit) && $unit !== '-') {
                        $q->orWhere('unit', $unit);
                    }
                })
                ->pluck('id')
                ->toArray();
        }

        // 4. Jika Staff Biasa: hanya diri sendiri
        return [$authUser->id];
    }
}
