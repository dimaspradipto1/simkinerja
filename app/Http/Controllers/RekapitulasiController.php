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

        return view('pages.rekapitulasi.index', compact('periodeAkademiks', 'defaultPeriodeId', 'usersWithJabatan'));
    }

    /**
     * Build base query for Rekapitulasi based on filters and user role.
     */
    private function buildRekapQuery(Request $request)
    {
        $authUser = Auth::user();
        $query = Kegiatan::with(['user', 'periodeAkademik', 'taggedUsers']);

        if (!$request->filled('jabatan') && !$authUser->isAdmin() && !$authUser->isPimpinanRektorat()) {
            if ($authUser->isPimpinanUnit()) {
                $query->whereHas('user', function ($qu) use ($authUser) {
                    $qu->where('unit', $authUser->unit);
                });
            } else {
                $query->where(function ($q) use ($authUser) {
                    $q->whereHas('taggedUsers', function ($qu) use ($authUser) {
                        $qu->where('users.id', $authUser->id);
                    })->orWhere(function ($sub) use ($authUser) {
                        $sub->doesntHave('taggedUsers')
                            ->where('user_id', $authUser->id);
                    });
                });
            }
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
                $query->where('status', '!=', 'Selesai')
                      ->whereNotNull('estimasi_tanggal_selesai')
                      ->where('estimasi_tanggal_selesai', '<', $nowStr);
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
        $terlambat = (clone $query)->where('status', '!=', 'Selesai')
            ->whereNotNull('estimasi_tanggal_selesai')
            ->where('estimasi_tanggal_selesai', '<', $nowStr)
            ->count();
        $percent = $total > 0 ? round(($selesai / $total) * 100) : 0;

        $predikat = 'Kurang';
        if ($percent >= 85) {
            $predikat = 'Sangat Baik';
        } elseif ($percent >= 70) {
            $predikat = 'Baik';
        } elseif ($percent >= 55) {
            $predikat = 'Cukup';
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

        if (!$authUser->isAdmin() && !$authUser->isPimpinanRektorat()) {
            if ($authUser->isPimpinanUnit()) {
                $query->where(function ($q) use ($authUser) {
                    $q->whereHas('user', function ($qu) use ($authUser) {
                        $qu->where('unit', $authUser->unit);
                    })->orWhereHas('taggedUsers', function ($qu) use ($authUser) {
                        $qu->where('unit', $authUser->unit);
                    });
                });
            } else {
                if (!$request->filled('jabatan')) {
                    $query->where(function ($q) use ($authUser) {
                        $q->where('user_id', $authUser->id)
                          ->orWhereHas('taggedUsers', function ($qu) use ($authUser) {
                              $qu->where('users.id', $authUser->id);
                          });
                    });
                }
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
        $terlambat = (clone $query)->where('status', '!=', 'Selesai')
            ->whereNotNull('estimasi_tanggal_selesai')
            ->where('estimasi_tanggal_selesai', '<', $nowStr)
            ->count();
        $percent = $total > 0 ? round(($selesai / $total) * 100) : 0;

        return DataTables::of($query)
            ->addColumn('pembuat', function ($row) {
                if ($row->taggedUsers && $row->taggedUsers->count() > 0) {
                    return $row->taggedUsers->map(function ($u) {
                        return $u->name . ($u->jabatan ? ' (' . $u->jabatan . ')' : '');
                    })->implode(', ');
                }
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
                'terlambat' => $terlambat,
                'selesai' => $selesai,
                'percent' => $percent
            ])
            ->make(true);
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
        } elseif (!Auth::user()->isAdmin() && !Auth::user()->isPimpinanRektorat() && !Auth::user()->isPimpinanUnit()) {
            $staffText = strtoupper(Auth::user()->name . ' (' . (Auth::user()->jabatan ?? '-') . ')');
        }

        return [$periodeText, $kategoriText, $statusFilterText, $staffText];
    }

    /**
     * Export Rekapitulasi Capaian & Analisis Kendala to Excel.
     */
    public function exportExcel(Request $request)
    {
        $query = $this->buildRekapQuery($request);
        $rekap = $this->calculateRekapStats($query);
        $items = $query->latest()->get();

        [$periodeText, $kategoriText, $statusFilterText, $staffText] = $this->resolveMetaTexts($request);

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

        // Row 4: Filter Meta Banner
        $sheet1->mergeCells('A4:H4');
        $metaBannerText = 'PERIODE / WAKTU: ' . $periodeText . '  |  KATEGORI: ' . $kategoriText . '  |  STATUS: ' . $statusFilterText . '  |  PEGAWAI: ' . $staffText;
        $sheet1->setCellValue('A4', $metaBannerText);
        $sheet1->getStyle('A4:H4')->getFont()->setName('Arial')->setSize(9.5)->setBold(true)->getColor()->setARGB('FF15432D');
        $sheet1->getStyle('A4:H4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF0FDF4');
        $sheet1->getStyle('A4:H4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet1->getStyle('A4:H4')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FF86EFAC');
        $sheet1->getRowDimension(4)->setRowHeight(22);

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
            $isTerlambat = false;
            if (!$isSelesai && !empty($item->estimasi_tanggal_selesai)) {
                $endTime = $item->estimasi_jam_selesai ?: '23:59:59';
                try {
                    $estimasiEnd = \Carbon\Carbon::parse($item->estimasi_tanggal_selesai . ' ' . $endTime);
                    if (\Carbon\Carbon::now()->gt($estimasiEnd)) {
                        $isTerlambat = true;
                    }
                } catch (\Throwable $e) {}
            }

            $kat = $item->kategori ?: 'Rencana Kerja';

            if ($item->taggedUsers && $item->taggedUsers->count() > 0) {
                $staffParts = [];
                foreach ($item->taggedUsers as $tUser) {
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

            $realFormatted = '-';
            if ($item->tanggal_mulai && $item->tanggal_selesai) {
                $realFormatted = date('d/m/Y', strtotime($item->tanggal_mulai)) . ' - ' . date('d/m/Y', strtotime($item->tanggal_selesai));
            } elseif ($item->tanggal_mulai) {
                $realFormatted = 'Mulai ' . date('d/m/Y', strtotime($item->tanggal_mulai));
            } elseif ($item->tanggal_selesai) {
                $realFormatted = 'Selesai ' . date('d/m/Y', strtotime($item->tanggal_selesai));
            }

            $statusText = $item->status ?? 'Belum Dimulai';

            $kendalaText = '';
            if ($isTerlambat) {
                $kendalaText .= "[TERLAMBAT] Melewati estimasi tanggal selesai\n";
            }
            if ($item->alasan_pause) {
                $kendalaText .= "[ALASAN PAUSE] " . $item->alasan_pause . "\n";
            }
            if ($item->rencana_tindak_lanjut) {
                $kendalaText .= "[TINDAK LANJUT] " . trim(strip_tags($item->rencana_tindak_lanjut));
            }
            if (empty(trim($kendalaText))) {
                $kendalaText = '-';
            }

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

            if ($isTerlambat) {
                $sheet1->getStyle('H' . $rowNum)->getFont()->getColor()->setARGB('FFDC2626');
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
        $sheet1->setCellValue('A' . $legRow2, 'Kriteria Predikat: Sangat Baik (Capaian ≥ 85%)  |  Baik (70% - 84%)  |  Cukup (55% - 69%)  |  Kurang (< 55%).  (Detail matriks diagnosis lengkap tersedia pada Sheet "Analisis Kinerja")');
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

        // Row 4: Meta Banner
        $sheet2->mergeCells('A4:H4');
        $sheet2->setCellValue('A4', $metaBannerText);
        $sheet2->getStyle('A4:H4')->getFont()->setName('Arial')->setSize(9.5)->setBold(true)->getColor()->setARGB('FF15432D');
        $sheet2->getStyle('A4:H4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF0FDF4');
        $sheet2->getStyle('A4:H4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet2->getStyle('A4:H4')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FF86EFAC');
        $sheet2->getRowDimension(4)->setRowHeight(22);

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
                'rentang' => '≥ 85%',
                'kategori' => 'Melampaui Target',
                'deskripsi' => 'Capaian kinerja sangat memuaskan, seluruh tugas utama terlaksana secara tepat waktu dan berkualitas.',
                'color' => 'FF15803D',
                'fill' => 'FFDCFCE7'
            ],
            [
                'no' => 2,
                'predikat' => 'Baik',
                'rentang' => '70% - 84%',
                'kategori' => 'Memenuhi Target',
                'deskripsi' => 'Capaian kinerja memenuhi target operasional dengan baik, sebagian besar sasaran tercapai.',
                'color' => 'FF16A34A',
                'fill' => 'FFF0FDF4'
            ],
            [
                'no' => 3,
                'predikat' => 'Cukup',
                'rentang' => '55% - 69%',
                'kategori' => 'Perlu Evaluasi',
                'deskripsi' => 'Capaian kinerja cukup memenuhi kebutuhan dasar, memerlukan monitoring dan tindak lanjut percepatan.',
                'color' => 'FFD97706',
                'fill' => 'FFFEF3C7'
            ],
            [
                'no' => 4,
                'predikat' => 'Kurang',
                'rentang' => '< 55%',
                'kategori' => 'Di Bawah Target',
                'deskripsi' => 'Capaian kinerja belum memenuhi standar minimal, memerlukan evaluasi kendala mendalam dan bimbingan.',
                'color' => 'FFDC2626',
                'fill' => 'FFFEF2F2'
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
     * Export Rekapitulasi Capaian & Analisis Kendala to PDF.
     */
    public function exportPdf(Request $request)
    {
        $query = $this->buildRekapQuery($request);
        $rekap = $this->calculateRekapStats($query);
        $items = $query->latest()->get();

        [$periodeText, $kategoriText, $statusFilterText, $staffText] = $this->resolveMetaTexts($request);

        $data = [
            'items' => $items,
            'rekap' => $rekap,
            'periodeText' => $periodeText,
            'kategoriText' => $kategoriText,
            'statusFilterText' => $statusFilterText,
            'staffText' => $staffText,
        ];

        $pdf = Pdf::loadView('pages.rekapitulasi.pdf', $data);
        $pdf->setPaper('a4', 'landscape');

        $safePeriode = trim(preg_replace('/[^A-Za-z0-9\-\s]/', '', str_replace(['/', '\\'], '-', $periodeText)));
        $safeKategori = trim(preg_replace('/[^A-Za-z0-9\-\s]/', '', str_replace(['/', '\\'], '-', $kategoriText)));
        $filename = 'rekapitulasi_kinerja_' . strtolower(str_replace(' ', '_', $safeKategori ?: 'semua')) . '_' . strtolower(str_replace(' ', '_', $safePeriode ?: 'periode')) . '.pdf';

        return $pdf->stream($filename);
    }
}
