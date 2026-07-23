<?php

namespace App\Http\Controllers;

use App\Http\Requests\RencanaKerjaRequest;
use App\Models\RencanaKerja;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert;
use Yajra\DataTables\Facades\DataTables;

class RencanaKerjaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $authUser = Auth::user();

        if ($request->ajax()) {
            $query = RencanaKerja::with('user');

            if ($authUser) {
                if ($authUser->isAdmin() || $authUser->isPimpinanRektorat()) {
                    // Superadmin, Admin, Pimpinan Rektorat -> Akses seluruh tugas universitas
                } elseif ($authUser->isPimpinanUnit()) {
                    // Pimpinan Unit -> Akses seluruh tugas di unitnya
                    $query->whereHas('user', function ($q) use ($authUser) {
                        $q->where('unit', $authUser->unit);
                    });
                } else {
                    // Staff / Pegawai Regular -> Hanya akses tugas milik sendiri
                    $query->where('user_id', $authUser->id);
                }
            }

            if ($request->filled('jabatan')) {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->where('jabatan', $request->jabatan);
                });
            }

            $query->latest();

            return DataTables::of($query)
                ->addIndexColumn()
                ->filterColumn('task_details', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('uraian_tugas', 'like', "%{$keyword}%")
                          ->orWhereHas('user', function ($u) use ($keyword) {
                              $u->where('name', 'like', "%{$keyword}%")
                                ->orWhere('jabatan', 'like', "%{$keyword}%");
                          });
                    });
                })
                ->addColumn('task_details', function ($row) {
                    $html = '<div class="d-flex flex-column gap-1">';
                    $html .= '<div class="text-dark fw-normal fs-6">' . e($row->uraian_tugas) . '</div>';

                    $badges = [];
                    if ($row->user) {
                        $badges[] = '<span class="badge bg-light text-dark border"><i class="bi bi-person me-1"></i>' . e($row->user->name) . ' (' . e($row->user->jabatan ?? '-') . ')</span>';
                    }

                    if ($row->waktu_mulai) {
                        $tglMulai = $row->tanggal_mulai ? date('d/m/Y', strtotime($row->tanggal_mulai)) . ' ' : '';
                        $badges[] = '<span class="badge bg-primary-subtle text-primary border border-primary-subtle"><i class="bi bi-clock me-1"></i>Mulai: ' . $tglMulai . substr($row->waktu_mulai, 0, 5) . '</span>';
                    }

                    if ($row->waktu_selesai) {
                        $tglSelesai = $row->tanggal_selesai ? date('d/m/Y', strtotime($row->tanggal_selesai)) . ' ' : '';
                        $badges[] = '<span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-clock-history me-1"></i>Selesai: ' . $tglSelesai . substr($row->waktu_selesai, 0, 5) . '</span>';
                    }

                    if (!empty($row->waktu_mulai) && !empty($row->waktu_selesai) && $row->waktu_selesai !== '00:00:00') {
                        try {
                            $tglMulaiStr = !empty($row->tanggal_mulai) ? $row->tanggal_mulai : now()->format('Y-m-d');
                            $tglSelesaiStr = !empty($row->tanggal_selesai) ? $row->tanggal_selesai : $tglMulaiStr;

                            $startTs = strtotime($tglMulaiStr . ' ' . $row->waktu_mulai);
                            $endTs = strtotime($tglSelesaiStr . ' ' . $row->waktu_selesai);

                            $diffInSeconds = max(0, $endTs - $startTs);

                            $days = floor($diffInSeconds / 86400);
                            $hours = floor(($diffInSeconds % 86400) / 3600);
                            $minutes = floor(($diffInSeconds % 3600) / 60);
                            $seconds = $diffInSeconds % 60;

                            $durasiParts = [];
                            if ($days > 0) {
                                $durasiParts[] = $days . ' hari';
                            }
                            if ($hours > 0) {
                                $durasiParts[] = $hours . ' jam';
                            }
                            if ($minutes > 0) {
                                $durasiParts[] = $minutes . ' menit';
                            }
                            if ($seconds > 0 || empty($durasiParts)) {
                                $durasiParts[] = $seconds . ' detik';
                            }

                            $durasiStr = implode(' ', $durasiParts);
                            $badges[] = '<span class="badge bg-warning-subtle text-dark border border-warning-subtle fw-semibold"><i class="bi bi-hourglass-split me-1"></i>Durasi: ' . $durasiStr . '</span>';
                        } catch (\Exception $e) {
                            // ignore calculation error
                        }
                    }

                    if (!empty($badges)) {
                        $html .= '<div class="d-flex flex-wrap gap-1 align-items-center mt-1">' . implode(' ', $badges) . '</div>';
                    }

                    // Form Inline Upload Berkas & Link di bawah nama jika tugas Selesai / Waktu Selesai terisi
                    if (!empty($row->waktu_selesai) && $row->waktu_selesai !== '00:00:00') {
                        $html .= '<form class="form-inline-upload mt-2 p-3 bg-light rounded border shadow-sm" data-id="' . $row->id . '" enctype="multipart/form-data">';
                        $html .= '<div class="row g-2 align-items-center">';
                        
                        $html .= '<div class="col-12 col-md-6">';
                        $html .= '<label class="form-label mb-1 text-secondary small fw-semibold text-nowrap" style="font-size: 0.78rem;"><i class="bi bi-file-earmark-plus me-1"></i>Unggah Berkas <span class="text-muted fw-normal">(Opsional)</span>:</label>';
                        $html .= '<input type="file" name="file" class="form-control form-control-sm bg-white input-file-inline">';
                        $html .= '</div>';

                        $html .= '<div class="col-12 col-md-6">';
                        $html .= '<label class="form-label mb-1 text-secondary small fw-semibold text-nowrap" style="font-size: 0.78rem;"><i class="bi bi-link-45deg me-1"></i>Link Eksternal <span class="text-muted fw-normal">(Opsional)</span>:</label>';
                        $html .= '<input type="url" name="url_external" class="form-control form-control-sm bg-white input-url-inline" placeholder="https://drive.google.com/..." value="' . e($row->url_external ?? '') . '">';
                        $html .= '</div>';

                        $html .= '<div class="col-12 mt-2 text-start">';
                        $html .= '<button type="submit" class="btn btn-sm text-white px-4 fw-bold btn-simpan-inline" style="background-color: #15432d; border-color: #15432d; height: 32px;"><i class="bi bi-cloud-arrow-up-fill me-1"></i> Simpan</button>';
                        $html .= '</div>';

                        // Hasil Tersimpan (Di bawah Tombol Simpan)
                        $html .= '<div class="col-12 mt-3 pt-2 border-top">';
                        $html .= '<div class="d-flex align-items-center flex-wrap gap-2">';
                        $html .= '<span class="text-dark small fw-bold me-2"><i class="bi bi-folder-check me-1"></i>Hasil Tersimpan:</span>';
                        
                        $hasResult = false;
                        if ($row->file) {
                            $hasResult = true;
                            $fileUrl = asset('storage/' . $row->file);
                            $html .= '<a href="' . $fileUrl . '" target="_blank" class="btn btn-sm btn-outline-success text-nowrap fw-semibold px-3 d-inline-flex align-items-center" style="font-size: 0.8rem; height: 30px;"><i class="bi bi-file-earmark-check-fill me-1"></i> Unduh / Lihat Berkas</a>';
                        }

                        if ($row->url_external) {
                            $hasResult = true;
                            $html .= '<a href="' . e($row->url_external) . '" target="_blank" class="btn btn-sm btn-outline-primary text-nowrap fw-semibold px-3 d-inline-flex align-items-center" style="font-size: 0.8rem; height: 30px;"><i class="bi bi-box-arrow-up-right me-1"></i> Buka Link Eksternal</a>';
                        }

                        if (!$hasResult) {
                            $html .= '<span class="text-muted small fst-italic">Belum ada berkas atau link tersimpan</span>';
                        }
                        $html .= '</div>'; // end flex
                        $html .= '</div>'; // end col-12

                        $html .= '</div>'; // end row
                        $html .= '</form>';
                    }

                    $html .= '</div>';
                    return $html;
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="d-inline-flex gap-1 flex-wrap flex-sm-nowrap align-items-center justify-content-end action-buttons-wrap">';

                    $waktuMulai = !empty($row->waktu_mulai) && $row->waktu_mulai !== '00:00:00';
                    $waktuSelesai = !empty($row->waktu_selesai) && $row->waktu_selesai !== '00:00:00';

                    if (!$waktuMulai) {
                        $btn .= '<button type="button" data-id="' . $row->id . '" onclick="window.startTimer(' . $row->id . ')" class="btn btn-sm text-white fw-bold text-nowrap px-3 me-1 btn-start-timer" style="background-color: #15432d; border-color: #15432d; height: 32px; min-width: 90px; cursor: pointer;"><i class="bi bi-play-fill me-1"></i> Mulai</button>';
                    } elseif ($waktuMulai && !$waktuSelesai) {
                        $btn .= '<button type="button" data-id="' . $row->id . '" onclick="window.stopTimer(' . $row->id . ')" class="btn btn-sm btn-danger text-white fw-bold text-nowrap px-3 me-1 btn-stop-timer" style="background-color: #8b0000; border-color: #8b0000; height: 32px; min-width: 90px; cursor: pointer;"><i class="bi bi-stop-fill me-1"></i> Selesai</button>';
                    } else {
                        $btn .= '<button type="button" class="btn btn-sm text-white fw-bold text-nowrap px-3 me-1 disabled" style="background-color: #2d6a4f; border-color: #2d6a4f; opacity: 1; height: 32px; min-width: 90px; cursor: default;"><i class="bi bi-check-circle-fill me-1"></i> Selesai</button>';
                    }

                    $editUrl = route('rencana-kerja.edit', $row->id);
                    $btn .= '<a href="' . $editUrl . '" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center justify-content-center me-1" style="height: 32px; width: 32px; padding: 0;" title="Edit Tugas"><i class="bi bi-pencil"></i></a>';
                    $btn .= '<button type="button" data-id="' . $row->id . '" onclick="window.deleteRencanaKerja(' . $row->id . ')" class="btn btn-sm btn-outline-danger btn-delete-timer d-inline-flex align-items-center justify-content-center" style="height: 32px; width: 32px; padding: 0;" title="Hapus Tugas"><i class="bi bi-trash"></i></button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['task_details', 'action'])
                ->make(true);
        }

        $usersQuery = User::whereNotNull('jabatan')
            ->where('jabatan', '!=', '-');

        if ($authUser) {
            if ($authUser->isAdmin() || $authUser->isPimpinanRektorat()) {
                // Superadmin, Admin, Pimpinan Rektorat -> Seluruh Jabatan
            } elseif ($authUser->isPimpinanUnit()) {
                // Pimpinan Unit -> Jabatan di unitnya
                $usersQuery->where('unit', $authUser->unit);
            } else {
                // Staff Regular -> Jabatan milik sendiri
                $usersQuery->where('id', $authUser->id);
            }
        }

        $usersWithJabatan = $usersQuery->orderBy('jabatan')->get(['id', 'name', 'jabatan']);

        return view('pages.rencanakerja.index', compact('usersWithJabatan'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $authUser = Auth::user();
        $usersQuery = User::query();

        if ($authUser && !$authUser->isAdmin()) {
            $usersQuery->where('unit', $authUser->unit);
        }

        $users = $usersQuery->orderBy('name')->get();
        return view('pages.rencanakerja.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RencanaKerjaRequest $request)
    {
        $validated = $request->validated();
        $validated['user_id'] = $validated['user_id'] ?? Auth::id();

        if ($request->hasFile('file')) {
            $validated['file'] = $request->file('file')->store('rencana_kerja', 'public');
        }

        if ($request->has('start_now') && $request->start_now) {
            $validated['waktu_mulai'] = now()->format('H:i:s');
            $validated['tanggal_mulai'] = now()->format('Y-m-d');
            $validated['status'] = 'Berjalan';
        } else {
            $validated['status'] = 'Belum Dimulai';
        }

        RencanaKerja::create($validated);

        Alert::success('Berhasil', 'Rencana Kerja berhasil ditambahkan')
            ->toToast()
            ->autoClose(4000)
            ->timerProgressBar();

        return redirect()->route('rencana-kerja.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RencanaKerja $rencanaKerja)
    {
        $authUser = Auth::user();
        $usersQuery = User::query();

        if ($authUser && !$authUser->isAdmin()) {
            $usersQuery->where('unit', $authUser->unit);
        }

        $users = $usersQuery->orderBy('name')->get();
        return view('pages.rencanakerja.edit', compact('rencanaKerja', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RencanaKerjaRequest $request, RencanaKerja $rencanaKerja)
    {
        $validated = $request->validated();

        if ($request->hasFile('file')) {
            if ($rencanaKerja->file && Storage::disk('public')->exists($rencanaKerja->file)) {
                Storage::disk('public')->delete($rencanaKerja->file);
            }
            $validated['file'] = $request->file('file')->store('rencana_kerja', 'public');
        }

        $rencanaKerja->update($validated);

        Alert::success('Berhasil', 'Rencana Kerja berhasil diperbarui')
            ->toToast()
            ->autoClose(4000)
            ->timerProgressBar();

        return redirect()->route('rencana-kerja.index');
    }

    /**
     * Start task timer (Play button).
     */
    public function start(Request $request, RencanaKerja $rencanaKerja)
    {
        $clientTime = $request->input('client_time') ?? now()->format('H:i:s');
        $clientDate = $request->input('client_date') ?? now()->format('Y-m-d');

        $rencanaKerja->update([
            'waktu_mulai' => $clientTime,
            'tanggal_mulai' => $clientDate,
            'status' => 'Berjalan',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Waktu mulai pelaksanaan tugas (' . substr($clientTime, 0, 5) . ') berhasil dicatat.',
        ]);
    }

    /**
     * Stop task timer (Stop button).
     */
    public function stop(Request $request, RencanaKerja $rencanaKerja)
    {
        $clientTime = $request->input('client_time') ?? now()->format('H:i:s');
        $clientDate = $request->input('client_date') ?? now()->format('Y-m-d');

        $updateData = [
            'waktu_selesai' => $clientTime,
            'tanggal_selesai' => $clientDate,
            'status' => 'Selesai',
        ];

        if ($request->hasFile('file')) {
            if ($rencanaKerja->file && Storage::disk('public')->exists($rencanaKerja->file)) {
                Storage::disk('public')->delete($rencanaKerja->file);
            }
            $updateData['file'] = $request->file('file')->store('rencana_kerja', 'public');
        }

        if ($request->filled('url_external')) {
            $updateData['url_external'] = $request->input('url_external');
        }

        $rencanaKerja->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Waktu penyelesaian tugas (' . substr($clientTime, 0, 5) . ') berhasil dicatat.',
        ]);
    }

    /**
     * Upload attachment (file & url_external) inline.
     */
    public function uploadAttachment(Request $request, RencanaKerja $rencanaKerja)
    {
        $updateData = [];

        if ($request->hasFile('file')) {
            $uploadedFile = $request->file('file');
            if ($uploadedFile->isValid()) {
                if ($rencanaKerja->file && Storage::disk('public')->exists($rencanaKerja->file)) {
                    Storage::disk('public')->delete($rencanaKerja->file);
                }
                $updateData['file'] = $uploadedFile->store('rencana_kerja', 'public');
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengunggah berkas: ' . $uploadedFile->getErrorMessage(),
                ], 422);
            }
        }

        if ($request->has('url_external')) {
            $updateData['url_external'] = $request->input('url_external');
        }

        if (!empty($updateData)) {
            $rencanaKerja->update($updateData);
        }

        return response()->json([
            'success' => true,
            'message' => 'Berkas dan link eksternal berhasil disimpan.',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, RencanaKerja $rencanaKerja)
    {
        if ($rencanaKerja->file && Storage::disk('public')->exists($rencanaKerja->file)) {
            Storage::disk('public')->delete($rencanaKerja->file);
        }

        $rencanaKerja->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Rencana Kerja berhasil dihapus'
            ]);
        }

        Alert::success('Berhasil', 'Rencana Kerja berhasil dihapus')
            ->toToast()
            ->autoClose(4000)
            ->timerProgressBar();

        return redirect()->route('rencana-kerja.index');
    }

    /**
     * Download Excel Import Template
     */
    public function downloadTemplate()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Rencana Kerja');

        // Header (Hanya uraian_tugas)
        $sheet->setCellValue('A1', 'uraian_tugas');

        // Style Header
        $sheet->getStyle('A1')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('15432D');
        $sheet->getStyle('A1')->getFont()->getColor()->setARGB('FFFFFF');

        // Sample Rows
        $sheet->setCellValue('A2', 'Menyusun Laporan Kinerja Mingguan');
        $sheet->setCellValue('A3', 'Mengikuti Rapat Koordinasi Tim');

        $sheet->getColumnDimension('A')->setWidth(60);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'template_import_rencana_kerja.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Import Rencana Kerja from Excel File
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $file = $request->file('file_excel');
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            if (empty($rows) || count($rows) < 2) {
                Alert::error('Gagal', 'File Excel kosong atau tidak memiliki data.')->toToast();
                return redirect()->back();
            }

            // Detect header columns
            $header = array_map(function ($h) {
                return strtolower(trim((string) $h));
            }, $rows[0]);

            $uraianIndex = array_search('uraian_tugas', $header);
            if ($uraianIndex === false) {
                $uraianIndex = 0;
            }

            $count = 0;
            $authUserId = auth()->id();

            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                $uraianTugas = isset($row[$uraianIndex]) ? trim((string) $row[$uraianIndex]) : '';

                if (empty($uraianTugas)) {
                    continue;
                }

                RencanaKerja::create([
                    'user_id' => $authUserId,
                    'uraian_tugas' => $uraianTugas,
                    'status' => 'Belum Dimulai',
                ]);

                $count++;
            }

            Alert::success('Berhasil', "Berhasil mengimpor {$count} data Rencana Kerja.")
                ->toToast()
                ->autoClose(4000)
                ->timerProgressBar();

            return redirect()->back();
        } catch (\Exception $e) {
            Alert::error('Gagal', 'Gagal memproses file Excel: ' . $e->getMessage())->toToast();
            return redirect()->back();
        }
    }

    /**
     * Export Rencana Kerja to Excel (Khusus Pimpinan & Admin)
     */
    public function exportExcel(Request $request)
    {
        $authUser = Auth::user();

        if (!$authUser || (!$authUser->isPimpinanUnit() && !$authUser->isAdmin())) {
            Alert::error('Gagal', 'Akses ditolak. Fitur ini khusus Pimpinan dan Admin.')->toToast();
            return redirect()->back();
        }

        $query = RencanaKerja::with('user');

        if ($authUser) {
            if ($authUser->isAdmin() || $authUser->isPimpinanRektorat()) {
                // Superadmin, Admin, Pimpinan Rektorat -> Seluruh data
            } elseif ($authUser->isPimpinanUnit()) {
                // Pimpinan Unit -> Unit miliknya
                $query->whereHas('user', function ($q) use ($authUser) {
                    $q->where('unit', $authUser->unit);
                });
            } else {
                // Staff regular -> Milik sendiri
                $query->where('user_id', $authUser->id);
            }
        }

        if ($request->filled('jabatan')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('jabatan', $request->jabatan);
            });
        }

        $items = $query->latest()->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header Columns
        $sheet->setCellValue('A1', 'NO');
        $sheet->setCellValue('B1', 'NAMA PEGAWAI');
        $sheet->setCellValue('C1', 'JABATAN');
        $sheet->setCellValue('D1', 'URAIAN TUGAS');
        $sheet->setCellValue('E1', 'TANGGAL MULAI');
        $sheet->setCellValue('F1', 'WAKTU MULAI');
        $sheet->setCellValue('G1', 'TANGGAL SELESAI');
        $sheet->setCellValue('H1', 'WAKTU SELESAI');
        $sheet->setCellValue('I1', 'DURASI');
        $sheet->setCellValue('J1', 'LINK EKSTERNAL');
        $sheet->setCellValue('K1', 'STATUS BERKAS');

        $headerRange = 'A1:K1';
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('15432D');
        $sheet->getStyle($headerRange)->getFont()->getColor()->setRGB('FFFFFF');

        $rowNum = 2;
        $no = 1;

        foreach ($items as $item) {
            $durasiStr = '-';
            if (!empty($item->waktu_mulai) && !empty($item->waktu_selesai) && $item->waktu_selesai !== '00:00:00') {
                try {
                    $tglMulaiStr = !empty($item->tanggal_mulai) ? $item->tanggal_mulai : now()->format('Y-m-d');
                    $tglSelesaiStr = !empty($item->tanggal_selesai) ? $item->tanggal_selesai : $tglMulaiStr;
                    $startTs = strtotime($tglMulaiStr . ' ' . $item->waktu_mulai);
                    $endTs = strtotime($tglSelesaiStr . ' ' . $item->waktu_selesai);
                    $diffInSeconds = max(0, $endTs - $startTs);

                    $days = floor($diffInSeconds / 86400);
                    $hours = floor(($diffInSeconds % 86400) / 3600);
                    $minutes = floor(($diffInSeconds % 3600) / 60);
                    $seconds = $diffInSeconds % 60;

                    $durasiParts = [];
                    if ($days > 0) $durasiParts[] = $days . ' hari';
                    if ($hours > 0) $durasiParts[] = $hours . ' jam';
                    if ($minutes > 0) $durasiParts[] = $minutes . ' menit';
                    if ($seconds > 0 || empty($durasiParts)) $durasiParts[] = $seconds . ' detik';
                    $durasiStr = implode(' ', $durasiParts);
                } catch (\Exception $e) {
                    $durasiStr = '-';
                }
            }

            $sheet->setCellValue('A' . $rowNum, $no++);
            $sheet->setCellValue('B' . $rowNum, $item->user->name ?? '-');
            $sheet->setCellValue('C' . $rowNum, $item->user->jabatan ?? '-');
            $sheet->setCellValue('D' . $rowNum, $item->uraian_tugas);
            $sheet->setCellValue('E' . $rowNum, $item->tanggal_mulai ? date('d/m/Y', strtotime($item->tanggal_mulai)) : '-');
            $sheet->setCellValue('F' . $rowNum, $item->waktu_mulai ? substr($item->waktu_mulai, 0, 5) : '-');
            $sheet->setCellValue('G' . $rowNum, $item->tanggal_selesai ? date('d/m/Y', strtotime($item->tanggal_selesai)) : '-');
            $sheet->setCellValue('H' . $rowNum, $item->waktu_selesai && $item->waktu_selesai !== '00:00:00' ? substr($item->waktu_selesai, 0, 5) : '-');
            $sheet->setCellValue('I' . $rowNum, $durasiStr);
            $sheet->setCellValue('J' . $rowNum, $item->url_external ?? '-');
            $sheet->setCellValue('K' . $rowNum, $item->file ? 'Ada Berkas' : 'Tidak Ada');

            $rowNum++;
        }

        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'export_rencana_kerja_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
