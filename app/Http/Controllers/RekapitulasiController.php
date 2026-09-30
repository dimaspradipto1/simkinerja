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
        $periodeText = 'SEMUA PERIODE';
        if ($request->filled('periode_akademik_id') && ($p = PeriodeAkademik::find($request->periode_akademik_id))) {
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
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekapitulasi Kinerja');

        // Row 1: Institution Header
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', 'UNIVERSITAS IBNU SINA - YAYASAN PENDIDIKAN IBNU SINA BATAM');
        $sheet->getStyle('A1:H1')->getFont()->setName('Arial')->setSize(11)->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A1:H1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF15432D');
        $sheet->getStyle('A1:H1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(24);

        // Row 2: Title
        $sheet->mergeCells('A2:H2');
        $sheet->setCellValue('A2', 'REKAPITULASI CAPAIAN & ANALISIS KENDALA KINERJA');
        $sheet->getStyle('A2:H2')->getFont()->setName('Arial')->setSize(13)->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A2:H2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF15432D');
        $sheet->getStyle('A2:H2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(2)->setRowHeight(28);

        // Row 4: Filter Meta Banner
        $sheet->mergeCells('A4:H4');
        $metaBannerText = 'PERIODE: ' . $periodeText . '  |  KATEGORI: ' . $kategoriText . '  |  STATUS: ' . $statusFilterText . '  |  PEGAWAI: ' . $staffText;
        $sheet->setCellValue('A4', $metaBannerText);
        $sheet->getStyle('A4:H4')->getFont()->setName('Arial')->setSize(9.5)->setBold(true)->getColor()->setARGB('FF15432D');
        $sheet->getStyle('A4:H4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF0FDF4');
        $sheet->getStyle('A4:H4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A4:H4')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FF86EFAC');
        $sheet->getRowDimension(4)->setRowHeight(22);

        // Row 6: KPI Analysis Section Header
        $sheet->mergeCells('A6:H6');
        $sheet->setCellValue('A6', 'HASIL ANALISIS & DIAGNOSIS KINERJA');
        $sheet->getStyle('A6:H6')->getFont()->setName('Arial')->setSize(10.5)->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A6:H6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF15432D');
        $sheet->getStyle('A6:H6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(6)->setRowHeight(22);

        // Row 7: KPI Headers
        $kpiHeaders = [
            'A7' => 'TOTAL TUGAS',
            'B7' => 'BELUM MULAI',
            'C7' => 'SEDANG PROSES',
            'D7' => 'TERLAMBAT (KENDALA)',
            'E7' => 'SUDAH SELESAI',
            'F7' => '% CAPAIAN KERJA',
            'G7' => 'PREDIKAT',
            'H7' => 'RINGKASAN DIAGNOSIS',
        ];
        foreach ($kpiHeaders as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }
        $sheet->getStyle('A7:H7')->getFont()->setName('Arial')->setSize(8.5)->setBold(true)->getColor()->setARGB('FF334155');
        $sheet->getStyle('A7:H7')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F5F9');
        $sheet->getStyle('A7:H7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A7:H7')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFCBD5E1');
        $sheet->getRowDimension(7)->setRowHeight(20);

        // Row 8: KPI Values
        $diagnosisText = $rekap['selesai'] . '/' . $rekap['total'] . ' Selesai (' . $rekap['percent'] . '%)';
        if ($rekap['terlambat'] > 0) {
            $diagnosisText .= ', ' . $rekap['terlambat'] . ' Terlambat';
        }
        if ($rekap['proses'] > 0) {
            $diagnosisText .= ', ' . $rekap['proses'] . ' Sedang Proses';
        }

        $sheet->setCellValue('A8', $rekap['total']);
        $sheet->setCellValue('B8', $rekap['belum']);
        $sheet->setCellValue('C8', $rekap['proses']);
        $sheet->setCellValue('D8', $rekap['terlambat']);
        $sheet->setCellValue('E8', $rekap['selesai']);
        $sheet->setCellValue('F8', $rekap['percent'] . '%');
        $sheet->setCellValue('G8', $rekap['predikat']);
        $sheet->setCellValue('H8', $diagnosisText);

        $sheet->getStyle('A8:F8')->getFont()->setName('Arial')->setSize(12)->setBold(true);
        $sheet->getStyle('G8:H8')->getFont()->setName('Arial')->setSize(9.5)->setBold(true);
        $sheet->getStyle('A8')->getFont()->getColor()->setARGB('FF0284C7'); // Cyan
        $sheet->getStyle('B8')->getFont()->getColor()->setARGB('FF64748B'); // Gray
        $sheet->getStyle('C8')->getFont()->getColor()->setARGB('FF2563EB'); // Blue
        $sheet->getStyle('D8')->getFont()->getColor()->setARGB('FFDC2626'); // Red
        $sheet->getStyle('E8')->getFont()->getColor()->setARGB('FF16A34A'); // Green
        $sheet->getStyle('F8')->getFont()->getColor()->setARGB('FF15803D'); // Dark Green
        $sheet->getStyle('G8')->getFont()->getColor()->setARGB('FF15803D');
        $sheet->getStyle('H8')->getFont()->getColor()->setARGB('FF334155');

        $sheet->getStyle('A8:H8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A8:H8')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFCBD5E1');
        $sheet->getRowDimension(8)->setRowHeight(26);

        // Row 10: Detail Data Table Headers
        $tableHeaders = [
            'A10' => 'NO',
            'B10' => 'KATEGORI',
            'C10' => 'URAIAN KEGIATAN / TUGAS',
            'D10' => 'PEGAWAI / JABATAN',
            'E10' => 'ESTIMASI WAKTU',
            'F10' => 'REALISASI WAKTU',
            'G10' => 'STATUS',
            'H10' => 'ANALISIS KENDALA & TINDAK LANJUT',
        ];
        foreach ($tableHeaders as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }
        $sheet->getStyle('A10:H10')->getFont()->setName('Arial')->setSize(9)->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A10:H10')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF15432D');
        $sheet->getStyle('A10:H10')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(10)->setRowHeight(25);

        // Rows 11+: Data
        $rowNum = 11;
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

            $staffInfo = $item->user ? $item->user->name : '-';
            if ($item->user && $item->user->jabatan) {
                $staffInfo .= "\n(" . $item->user->jabatan . ')';
            }
            if ($item->taggedUsers && $item->taggedUsers->count() > 0) {
                $staffInfo .= "\n+ " . $item->taggedUsers->pluck('name')->implode(', ');
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

            $sheet->setCellValue('A' . $rowNum, $no++);
            $sheet->setCellValue('B' . $rowNum, $kat);
            $sheet->setCellValue('C' . $rowNum, $item->uraian_tugas ?: '-');
            $sheet->setCellValue('D' . $rowNum, $staffInfo);
            $sheet->setCellValue('E' . $rowNum, $estFormatted);
            $sheet->setCellValue('F' . $rowNum, $realFormatted);
            $sheet->setCellValue('G' . $rowNum, $statusText);
            $sheet->setCellValue('H' . $rowNum, trim($kendalaText));

            $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_TOP);
            $sheet->getStyle('B' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_TOP);
            $sheet->getStyle('C' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_TOP);
            $sheet->getStyle('D' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_TOP);
            $sheet->getStyle('E' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_TOP);
            $sheet->getStyle('F' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_TOP);
            $sheet->getStyle('G' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_TOP);
            $sheet->getStyle('H' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_TOP);

            if ($isTerlambat) {
                $sheet->getStyle('H' . $rowNum)->getFont()->getColor()->setARGB('FFDC2626');
            }

            $rowNum++;
        }

        if ($items->isEmpty()) {
            $sheet->mergeCells('A11:H11');
            $sheet->setCellValue('A11', 'Tidak ada data kegiatan yang sesuai dengan filter yang dipilih.');
            $sheet->getStyle('A11')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getRowDimension(11)->setRowHeight(30);
            $rowNum = 12;
        }

        $lastRow = max(10, $rowNum - 1);
        $sheet->getStyle('A10:H' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFCBD5E1');
        $sheet->getStyle('C11:D' . $lastRow)->getAlignment()->setWrapText(true);
        $sheet->getStyle('H11:H' . $lastRow)->getAlignment()->setWrapText(true);

        // Column Widths
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
            $sheet->getColumnDimension($col)->setWidth($w);
        }

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
