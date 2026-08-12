<?php

namespace App\Http\Controllers;

use App\DataTables\AbsensiPkkmbPertamaDataTable;
use App\Models\AbsensiPkkmbPertama;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert;

class AbsensiPkkmbPertamaController extends Controller
{
    private function checkAdmin()
    {
        $authUser = auth()->user();
        if (!$authUser || (!$authUser->isAdmin() && !$authUser->isSuperAdmin())) {
            abort(403, 'Unauthorized action. Only Admin or Super Admin can manage PKKMB attendance.');
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(AbsensiPkkmbPertamaDataTable $dataTable)
    {
        return $dataTable->render('pages.absensi-pkkmb-pertama.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->checkAdmin();
        $users = User::orderBy('name', 'asc')->get();
        return view('pages.absensi-pkkmb-pertama.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->checkAdmin();
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

        if ($request->hasFile('bukti_izin')) {
            $validated['bukti_izin'] = $request->file('bukti_izin')->store('absensi_pkkmb', 'public');
        }

        AbsensiPkkmbPertama::create($validated);

        Alert::success('Berhasil', 'Absensi PKKMB Hari Pertama berhasil ditambahkan.');
        return redirect()->route('absensi-pkkmb-pertama.index');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $absensi = AbsensiPkkmbPertama::with('user')->findOrFail($id);
        return view('pages.absensi-pkkmb-pertama.show', compact('absensi'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $this->checkAdmin();
        $absensi = AbsensiPkkmbPertama::findOrFail($id);
        $users = User::orderBy('name', 'asc')->get();
        return view('pages.absensi-pkkmb-pertama.edit', compact('absensi', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $this->checkAdmin();
        $absensi = AbsensiPkkmbPertama::findOrFail($id);

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

        if ($request->hasFile('bukti_izin')) {
            if ($absensi->bukti_izin && Storage::disk('public')->exists($absensi->bukti_izin)) {
                Storage::disk('public')->delete($absensi->bukti_izin);
            }
            $validated['bukti_izin'] = $request->file('bukti_izin')->store('absensi_pkkmb', 'public');
        }

        $absensi->update($validated);

        Alert::success('Berhasil', 'Data absensi PKKMB Hari Pertama berhasil diperbarui.');
        return redirect()->route('absensi-pkkmb-pertama.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $this->checkAdmin();
        $absensi = AbsensiPkkmbPertama::findOrFail($id);

        if ($absensi->bukti_izin && Storage::disk('public')->exists($absensi->bukti_izin)) {
            Storage::disk('public')->delete($absensi->bukti_izin);
        }

        $absensi->delete();

        return response()->json([
            'success' => true,
            'message' => 'Absensi PKKMB Hari Pertama berhasil dihapus.'
        ]);
    }

    /**
     * Export attendance data to Excel.
     */
    public function exportExcel(Request $request)
    {
        $this->checkAdmin();
        $items = AbsensiPkkmbPertama::with('user')->orderBy('id', 'asc')->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Absensi PKKMB Pertama');

        // Row 1: Title
        $titleText = 'LAPORAN ABSENSI PKKMB HARI PERTAMA';
        $sheet->mergeCells('A1:I1');
        $sheet->setCellValue('A1', $titleText);
        $sheet->getStyle('A1:I1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1:I1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('1E40AF'); // Dark Blue
        $sheet->getStyle('A1:I1')->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(35);

        // Row 3: Table Header Columns
        $headers = [
            'A3' => 'NO',
            'B3' => 'NAMA PENGGUNA',
            'C3' => 'HADIR DATANG',
            'D3' => 'WAKTU DATANG',
            'E3' => 'CATATAN DATANG',
            'F3' => 'HADIR PULANG',
            'G3' => 'WAKTU PULANG',
            'H3' => 'CATATAN PULANG',
            'I3' => 'BUKTI IZIN',
        ];

        foreach ($headers as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }

        $headerRange = 'A3:I3';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('3B82F6'); // Light Blue
        $sheet->getStyle($headerRange)->getFont()->getColor()->setRGB('FFFFFF');
        $sheet->getStyle($headerRange)->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(3)->setRowHeight(25);

        $rowNum = 4;
        $no = 1;

        foreach ($items as $item) {
            $waktuDatang = $item->waktu_datang ? substr($item->waktu_datang, 0, 5) : '-';
            $waktuPulang = $item->waktu_pulang ? substr($item->waktu_pulang, 0, 5) : '-';
            $buktiIzin = $item->bukti_izin ? asset('storage/' . $item->bukti_izin) : 'Tidak Ada';

            $sheet->setCellValue('A' . $rowNum, $no++);
            $sheet->setCellValue('B' . $rowNum, $item->user ? $item->user->name : '-');
            $sheet->setCellValue('C' . $rowNum, $item->hadir_datang ?? '-');
            $sheet->setCellValue('D' . $rowNum, $waktuDatang);
            $sheet->setCellValue('E' . $rowNum, $item->catatan_hadir_datang ?? '-');
            $sheet->setCellValue('F' . $rowNum, $item->hadir_pulang ?? '-');
            $sheet->setCellValue('G' . $rowNum, $waktuPulang);
            $sheet->setCellValue('H' . $rowNum, $item->catatan_hadir_pulang ?? '-');
            $sheet->setCellValue('I' . $rowNum, $buktiIzin);

            // Alignments
            $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $rowNum++;
        }

        // Apply Borders to all cells
        $lastRow = $rowNum - 1;
        if ($lastRow >= 3) {
            $tableRange = 'A3:I' . $lastRow;
            $sheet->getStyle($tableRange)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $sheet->getStyle($tableRange)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        }

        // Set column widths auto or fixed
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

        $filename = 'Absensi_PKKMB_Hari_Pertama_' . date('YmdHis') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Show the QR Code scanner view for student self-attendance.
     */
    public function scan()
    {
        return view('pages.absensi-pkkmb-pertama.scan');
    }
}
