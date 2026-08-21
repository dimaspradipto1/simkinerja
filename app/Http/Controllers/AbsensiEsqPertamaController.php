<?php

namespace App\Http\Controllers;

use App\DataTables\AbsensiEsqPertamaDataTable;
use App\Models\AbsensiEsqPertama;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert;

class AbsensiEsqPertamaController extends Controller
{
    private function checkAdmin()
    {
        $authUser = auth()->user();
        if (!$authUser || (!$authUser->isAdmin() && !$authUser->isSuperAdmin())) {
            abort(403, 'Unauthorized action. Only Admin or Super Admin can manage ESQ attendance.');
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(AbsensiEsqPertamaDataTable $dataTable)
    {
        return $dataTable->render('pages.absensi-esq-pertama.index');
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
        return view('pages.absensi-esq-pertama.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $authUser = auth()->user();        $validated = $request->validate([
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
        if (!empty($validated['hadir_datang']) && empty($validated['waktu_datang'])) {
            $validated['waktu_datang'] = $currentTime;
        }
        if (!empty($validated['hadir_pulang']) && empty($validated['waktu_pulang'])) {
            $validated['waktu_pulang'] = $currentTime;
        }

        if ($request->hasFile('bukti_izin')) {
            $validated['bukti_izin'] = $request->file('bukti_izin')->store('absensi_esq', 'public');
        }

        AbsensiEsqPertama::create($validated);

        Alert::success('Berhasil', 'Absensi ESQ Hari Pertama berhasil ditambahkan.');
        return redirect()->route('absensi-esq-pertama.index');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $authUser = auth()->user();
        $absensi = AbsensiEsqPertama::with('user')->findOrFail($id);
        if ($authUser && !$authUser->isAdmin() && !$authUser->isSuperAdmin() && $absensi->user_id !== $authUser->id) {
            abort(403, 'Unauthorized action.');
        }
        return view('pages.absensi-esq-pertama.show', compact('absensi'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $this->checkAdmin();
        $absensi = AbsensiEsqPertama::findOrFail($id);
        $users = User::orderBy('name', 'asc')->get();
        return view('pages.absensi-esq-pertama.edit', compact('absensi', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $this->checkAdmin();
        $absensi = AbsensiEsqPertama::findOrFail($id);

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
        if (!empty($validated['hadir_datang']) && empty($validated['waktu_datang'])) {
            $validated['waktu_datang'] = $currentTime;
        }
        if (!empty($validated['hadir_pulang']) && empty($validated['waktu_pulang'])) {
            $validated['waktu_pulang'] = $currentTime;
        }

        if ($request->hasFile('bukti_izin')) {
            if ($absensi->bukti_izin && Storage::disk('public')->exists($absensi->bukti_izin)) {
                Storage::disk('public')->delete($absensi->bukti_izin);
            }
            $validated['bukti_izin'] = $request->file('bukti_izin')->store('absensi_esq', 'public');
        }

        $absensi->update($validated);

        Alert::success('Berhasil', 'Data absensi ESQ Hari Pertama berhasil diperbarui.');
        return redirect()->route('absensi-esq-pertama.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $this->checkAdmin();
        $absensi = AbsensiEsqPertama::findOrFail($id);

        if ($absensi->bukti_izin && Storage::disk('public')->exists($absensi->bukti_izin)) {
            Storage::disk('public')->delete($absensi->bukti_izin);
        }

        $absensi->delete();

        return response()->json([
            'success' => true,
            'message' => 'Absensi ESQ Hari Pertama berhasil dihapus.'
        ]);
    }

    /**
     * Export attendance data to Excel.
     */
    public function exportExcel(Request $request)
    {
        $this->checkAdmin();
        $items = AbsensiEsqPertama::with('user')->orderBy('id', 'asc')->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Absensi ESQ Pertama');

        // Row 1: Title
        $titleText = 'LAPORAN ABSENSI ESQ HARI PERTAMA';
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

        $filename = 'Absensi_ESQ_Hari_Pertama_' . date('YmdHis') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
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
            return redirect()->route('absensi-esq-pertama.index');
        }

        $currentTime = now()->format('H:i');
        $absensi = AbsensiEsqPertama::where('user_id', $authUser->id)->first();

        if (!$absensi) {
            $absensi = new AbsensiEsqPertama();
            $absensi->user_id = $authUser->id;
        }

        if ($session === 'datang') {
            if ($absensi->hadir_datang === 'Hadir') {
                Alert::warning('Info', 'Anda sudah melakukan absensi datang sebelumnya.');
                return redirect()->route('absensi-esq-pertama.index');
            }
            $absensi->hadir_datang = 'Hadir';
            $absensi->waktu_datang = $currentTime;
            $msg = 'Absensi Datang berhasil dicatat otomatis pada pukul ' . $currentTime . ' WIB.';
        } else {
            if ($absensi->hadir_pulang === 'Hadir') {
                Alert::warning('Info', 'Anda sudah melakukan absensi pulang sebelumnya.');
                return redirect()->route('absensi-esq-pertama.index');
            }
            $absensi->hadir_pulang = 'Hadir';
            $absensi->waktu_pulang = $currentTime;
            $msg = 'Absensi Pulang berhasil dicatat otomatis pada pukul ' . $currentTime . ' WIB.';
        }

        $absensi->save();

        Alert::success('Berhasil', $msg);
        return redirect()->route('absensi-esq-pertama.index');
    }

    /**
     * Show the QR Code scanner view for student self-attendance.
     */
    public function scan()
    {
        return view('pages.absensi-esq-pertama.scan');
    }
}
