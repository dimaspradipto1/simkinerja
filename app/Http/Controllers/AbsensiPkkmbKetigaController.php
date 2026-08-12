<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AbsensiPkkmbKetiga;
use App\Models\User;
use App\DataTables\AbsensiPkkmbKetigaDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AbsensiPkkmbKetigaController extends Controller
{
    /**
     * Helper to verify if the authenticated user is an administrator.
     */
    private function checkAdmin()
    {
        $user = auth()->user();
        if (!$user || (!$user->isAdmin() && !$user->isSuperAdmin())) {
            abort(403, 'Unauthorized action.');
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(AbsensiPkkmbKetigaDataTable $dataTable)
    {
        return $dataTable->render('pages.absensi-pkkmb-ketiga.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $authUser = auth()->user();
        if ($authUser->isAdmin() || $authUser->isSuperAdmin()) {
            $users = User::orderBy('name', 'asc')->get();
        } else {
            $users = collect([$authUser]);
        }
        return view('pages.absensi-pkkmb-ketiga.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $authUser = auth()->user();
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'hadir_datang' => 'nullable|string',
            'waktu_datang' => 'nullable',
            'catatan_hadir_datang' => 'nullable|string',
            'hadir_pulang' => 'nullable|string',
            'waktu_pulang' => 'nullable',
            'catatan_hadir_pulang' => 'nullable|string',
            'bukti_izin' => 'nullable|file|max:5120|mimes:jpeg,png,jpg,pdf,doc,docx',
        ]);

        if (!$authUser->isAdmin() && !$authUser->isSuperAdmin()) {
            $validated['user_id'] = $authUser->id;
        }

        $currentTime = now()->format('H:i');
        if ($validated['hadir_datang'] && empty($validated['waktu_datang'])) {
            $validated['waktu_datang'] = $currentTime;
        }
        if ($validated['hadir_pulang'] && empty($validated['waktu_pulang'])) {
            $validated['waktu_pulang'] = $currentTime;
        }

        if ($request->hasFile('bukti_izin')) {
            $validated['bukti_izin'] = $request->file('bukti_izin')->store('absensi_pkkmb', 'public');
        }

        AbsensiPkkmbKetiga::create($validated);

        Alert::success('Berhasil', 'Absensi PKKMB Hari Ketiga berhasil ditambahkan.');
        return redirect()->route('absensi-pkkmb-ketiga.index');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $absensi = AbsensiPkkmbKetiga::with('user')->findOrFail($id);
        return view('pages.absensi-pkkmb-ketiga.show', compact('absensi'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $this->checkAdmin();
        $absensi = AbsensiPkkmbKetiga::findOrFail($id);
        $users = User::orderBy('name', 'asc')->get();
        return view('pages.absensi-pkkmb-ketiga.edit', compact('absensi', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $this->checkAdmin();
        $absensi = AbsensiPkkmbKetiga::findOrFail($id);
        
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'hadir_datang' => 'nullable|string',
            'waktu_datang' => 'nullable',
            'catatan_hadir_datang' => 'nullable|string',
            'hadir_pulang' => 'nullable|string',
            'waktu_pulang' => 'nullable',
            'catatan_hadir_pulang' => 'nullable|string',
            'bukti_izin' => 'nullable|file|max:5120|mimes:jpeg,png,jpg,pdf,doc,docx',
        ]);

        $currentTime = now()->format('H:i');
        if ($validated['hadir_datang'] && empty($validated['waktu_datang'])) {
            $validated['waktu_datang'] = $currentTime;
        }
        if ($validated['hadir_pulang'] && empty($validated['waktu_pulang'])) {
            $validated['waktu_pulang'] = $currentTime;
        }

        if ($request->hasFile('bukti_izin')) {
            if ($absensi->bukti_izin) {
                Storage::disk('public')->delete($absensi->bukti_izin);
            }
            $validated['bukti_izin'] = $request->file('bukti_izin')->store('absensi_pkkmb', 'public');
        }

        $absensi->update($validated);

        Alert::success('Berhasil', 'Absensi PKKMB Hari Ketiga berhasil diperbarui.');
        return redirect()->route('absensi-pkkmb-ketiga.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $this->checkAdmin();
        $absensi = AbsensiPkkmbKetiga::findOrFail($id);

        if ($absensi->bukti_izin) {
            Storage::disk('public')->delete($absensi->bukti_izin);
        }

        $absensi->delete();

        return response()->json([
            'success' => true,
            'message' => 'Absensi PKKMB Hari Ketiga berhasil dihapus.'
        ]);
    }

    /**
     * Process scanned QR Code to record attendance automatically.
     */
    public function scanProses(Request $request)
    {
        $authUser = auth()->user();
        if (!$authUser) {
            abort(401, 'Silakan login terlebih dahulu.');
        }

        $session = $request->query('session');
        if (!in_array($session, ['datang', 'pulang'])) {
            Alert::error('Gagal', 'Sesi absensi tidak valid.');
            return redirect()->route('absensi-pkkmb-ketiga.index');
        }

        $currentTime = now()->format('H:i');
        $absensi = AbsensiPkkmbKetiga::where('user_id', $authUser->id)->first();

        if (!$absensi) {
            $absensi = new AbsensiPkkmbKetiga();
            $absensi->user_id = $authUser->id;
        }

        if ($session === 'datang') {
            if ($absensi->hadir_datang === 'Hadir') {
                Alert::warning('Info', 'Anda sudah melakukan absensi datang sebelumnya.');
                return redirect()->route('absensi-pkkmb-ketiga.index');
            }
            $absensi->hadir_datang = 'Hadir';
            $absensi->waktu_datang = $currentTime;
            $msg = 'Absensi Datang berhasil dicatat otomatis pada pukul ' . $currentTime . ' WIB.';
        } else {
            if ($absensi->hadir_pulang === 'Hadir') {
                Alert::warning('Info', 'Anda sudah melakukan absensi pulang sebelumnya.');
                return redirect()->route('absensi-pkkmb-ketiga.index');
            }
            $absensi->hadir_pulang = 'Hadir';
            $absensi->waktu_pulang = $currentTime;
            $msg = 'Absensi Pulang berhasil dicatat otomatis pada pukul ' . $currentTime . ' WIB.';
        }

        $absensi->save();

        Alert::success('Berhasil', $msg);
        return redirect()->route('absensi-pkkmb-ketiga.index');
    }

    /**
     * Show the QR Code scanner view for student self-attendance.
     */
    public function scan()
    {
        return view('pages.absensi-pkkmb-ketiga.scan');
    }

    /**
     * Export the attendance data to Excel.
     */
    public function exportExcel()
    {
        $this->checkAdmin();
        $data = AbsensiPkkmbKetiga::with('user')->orderBy('id', 'asc')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Merged Title Header
        $sheet->mergeCells('A1:I2');
        $sheet->setCellValue('A1', "LAPORAN ABSENSI PKKMB HARI KETIGA");
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // Header columns
        $headers = [
            'A3' => 'NO',
            'B3' => 'NAMA PENGGUNA',
            'C3' => 'HADIR DATANG',
            'D3' => 'WAKTU DATANG',
            'E3' => 'CATATAN DATANG',
            'F3' => 'HADIR PULANG',
            'G3' => 'WAKTU PULANG',
            'H3' => 'CATATAN PULANG',
            'I3' => 'BUKTI IZIN'
        ];

        foreach ($headers as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }

        // Header Styling
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '198754'], // Green theme
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A3:I3')->applyFromArray($headerStyle);
        $sheet->getRowDimension(3)->setRowHeight(28);

        // Fill Data rows
        $rowNum = 4;
        $index = 1;
        foreach ($data as $row) {
            $sheet->setCellValue('A' . $rowNum, $index++);
            $sheet->setCellValue('B' . $rowNum, $row->user ? $row->user->name : '-');
            $sheet->setCellValue('C' . $rowNum, $row->hadir_datang ?? '-');
            $sheet->setCellValue('D' . $rowNum, $row->waktu_datang ? substr($row->waktu_datang, 0, 5) : '-');
            $sheet->setCellValue('E' . $rowNum, $row->catatan_hadir_datang ?? '-');
            $sheet->setCellValue('F' . $rowNum, $row->hadir_pulang ?? '-');
            $sheet->setCellValue('G' . $rowNum, $row->waktu_pulang ? substr($row->waktu_pulang, 0, 5) : '-');
            $sheet->setCellValue('H' . $rowNum, $row->catatan_hadir_pulang ?? '-');
            $sheet->setCellValue('I' . $rowNum, $row->bukti_izin ? asset('storage/' . $row->bukti_izin) : 'Tidak ada');

            $bodyStyle = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'D3D3D3'],
                    ],
                ],
            ];
            $sheet->getStyle('A' . $rowNum . ':I' . $rowNum)->applyFromArray($bodyStyle);
            $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->getRowDimension($rowNum)->setRowHeight(20);
            $rowNum++;
        }

        // Set column widths
        $colWidths = [
            'A' => 6,
            'B' => 35,
            'C' => 18,
            'D' => 18,
            'E' => 30,
            'F' => 18,
            'G' => 18,
            'H' => 30,
            'I' => 45,
        ];
        foreach ($colWidths as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        $filename = 'Absensi_PKKMB_Hari_Ketiga_' . date('YmdHis') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
