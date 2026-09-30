<?php

namespace App\Http\Controllers;

use App\Models\LaporanPengembangan;
use App\Models\PeriodeAkademik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class LaporanPengembanganController extends Controller
{
    /**
     * Tampilkan daftar laporan pengembangan universitas
     */
    public function index(Request $request)
    {
        $query = LaporanPengembangan::with(['periodeAkademik', 'creator', 'updater'])->latest();

        if ($request->filled('periode_akademik_id')) {
            $query->where('periode_akademik_id', $request->periode_akademik_id);
        }

        $laporans = $query->paginate(10);
        $periodeAkademiks = PeriodeAkademik::orderBy('nama_periode', 'desc')->get();
        $latestLaporan = LaporanPengembangan::with('periodeAkademik')->latest()->first();

        return view('pages.laporan-pengembangan.index', compact('laporans', 'periodeAkademiks', 'latestLaporan'));
    }

    /**
     * Form tambah laporan pengembangan baru
     */
    public function create()
    {
        $periodeAkademiks = PeriodeAkademik::orderBy('nama_periode', 'desc')->get();
        
        // Data inisialisasi default untuk prodi Universitas Ibnu Sina
        $defaultProdi = [
            ['nama' => 'Teknik Informatika', 'jenjang' => 'S1', 'akreditasi' => 'Baik Sekali', 'lembaga' => 'LAM INFOKOM', 'kadaluarsa' => '2028-08-30', 'status' => 'Pengembangan Kurikulum OBE'],
            ['nama' => 'Sistem Informasi', 'jenjang' => 'S1', 'akreditasi' => 'Baik Sekali', 'lembaga' => 'LAM INFOKOM', 'kadaluarsa' => '2027-10-15', 'status' => 'Peningkatan Akreditasi Unggul'],
            ['nama' => 'Teknik Industri', 'jenjang' => 'S1', 'akreditasi' => 'Baik Sekali', 'lembaga' => 'LAM TEKNIK', 'kadaluarsa' => '2028-05-20', 'status' => 'Kerjasama Magang Industri & Lab'],
            ['nama' => 'Kesehatan Masyarakat', 'jenjang' => 'S1', 'akreditasi' => 'B', 'lembaga' => 'LAM-PTKes', 'kadaluarsa' => '2027-12-10', 'status' => 'Re-akreditasi Baik Sekali'],
            ['nama' => 'Manajemen', 'jenjang' => 'S1', 'akreditasi' => 'Baik Sekali', 'lembaga' => 'LAMEMBA', 'kadaluarsa' => '2028-03-12', 'status' => 'Pengembangan Kelas Eksekutif'],
            ['nama' => 'Akuntansi', 'jenjang' => 'S1', 'akreditasi' => 'B', 'lembaga' => 'LAMEMBA', 'kadaluarsa' => '2027-06-25', 'status' => 'Sertifikasi Kompetensi Akuntansi'],
            ['nama' => 'Hukum', 'jenjang' => 'S1', 'akreditasi' => 'Baik Sekali', 'lembaga' => 'BAN-PT', 'kadaluarsa' => '2028-09-01', 'status' => 'Pemberdayaan LBH Kampus'],
            ['nama' => 'Magister Manajemen (S2)', 'jenjang' => 'S2', 'akreditasi' => 'Baik', 'lembaga' => 'LAMEMBA', 'kadaluarsa' => '2029-01-15', 'status' => 'Prodi Pascasarjana Baru'],
        ];

        return view('pages.laporan-pengembangan.form', [
            'laporan' => new LaporanPengembangan(),
            'periodeAkademiks' => $periodeAkademiks,
            'defaultProdi' => $defaultProdi,
            'isEdit' => false
        ]);
    }

    /**
     * Simpan laporan pengembangan baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'periode_akademik_id' => 'nullable|exists:periode_akademiks,id',
            'tahun_akademik' => 'nullable|string|max:100',
        ]);

        $data = $request->except(['_token', '_method']);
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();

        LaporanPengembangan::create($data);

        return redirect()->route('laporan-pengembangan.index')
            ->with('success', 'Laporan Pencapaian Pengembangan Universitas berhasil disimpan.');
    }

    /**
     * Tampilkan detail laporan pencapaian pengembangan universitas
     */
    public function show($id)
    {
        $laporan = LaporanPengembangan::with(['periodeAkademik', 'creator', 'updater'])->findOrFail($id);
        return view('pages.laporan-pengembangan.show', compact('laporan'));
    }

    /**
     * Form edit laporan pengembangan
     */
    public function edit($id)
    {
        $laporan = LaporanPengembangan::findOrFail($id);
        $periodeAkademiks = PeriodeAkademik::orderBy('nama_periode', 'desc')->get();

        return view('pages.laporan-pengembangan.form', [
            'laporan' => $laporan,
            'periodeAkademiks' => $periodeAkademiks,
            'defaultProdi' => $laporan->data_prodi ?? [],
            'isEdit' => true
        ]);
    }

    /**
     * Update laporan pengembangan
     */
    public function update(Request $request, $id)
    {
        $laporan = LaporanPengembangan::findOrFail($id);

        $request->validate([
            'judul' => 'required|string|max:255',
            'periode_akademik_id' => 'nullable|exists:periode_akademiks,id',
            'tahun_akademik' => 'nullable|string|max:100',
        ]);

        $data = $request->except(['_token', '_method']);
        $data['updated_by'] = Auth::id();

        $laporan->update($data);

        return redirect()->route('laporan-pengembangan.show', $laporan->id)
            ->with('success', 'Laporan Pencapaian Pengembangan Universitas berhasil diperbarui.');
    }

    /**
     * Hapus laporan pengembangan
     */
    public function destroy($id)
    {
        $laporan = LaporanPengembangan::findOrFail($id);
        $laporan->delete();

        return redirect()->route('laporan-pengembangan.index')
            ->with('success', 'Laporan Pencapaian Pengembangan Universitas berhasil dihapus.');
    }

    /**
     * Cetak PDF Resmi Laporan Pencapaian Pengembangan Universitas
     */
    public function exportPdf($id)
    {
        $laporan = LaporanPengembangan::with(['periodeAkademik', 'creator'])->findOrFail($id);

        $safeTahun = trim(preg_replace('/[^A-Za-z0-9\-\s]/', '', str_replace(['/', '\\'], '-', $laporan->tahun_akademik ?: 'Tahun')));
        $filename = 'Laporan_Pengembangan_Universitas_Ibnu_Sina_' . ($safeTahun ?: 'UIS') . '.pdf';

        $pdf = Pdf::loadView('pages.laporan-pengembangan.pdf', compact('laporan'))
            ->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }

    /**
     * Ekspor Excel Laporan Pengembangan
     */
    public function exportExcel($id)
    {
        $laporan = LaporanPengembangan::with(['periodeAkademik', 'creator'])->findOrFail($id);
        $spreadsheet = new Spreadsheet();

        // Sheet 1: Ringkasan Metrik Institusi
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Metrik Pengembangan UIS');

        $sheet->setCellValue('A1', 'UNIVERSITAS IBNU SINA');
        $sheet->setCellValue('A2', strtoupper($laporan->judul));
        $sheet->setCellValue('A3', 'Tahun Akademik: ' . ($laporan->tahun_akademik ?: '-'));
        $sheet->getStyle('A1:A3')->getFont()->setBold(true);

        $row = 5;
        $sheet->setCellValue('A' . $row, '1. PERKEMBANGAN MAHASISWA');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;

        $mhs = $laporan->data_mahasiswa ?? [];
        $sheet->setCellValue('A' . $row, 'Target Mahasiswa Baru');
        $sheet->setCellValue('B' . $row, $mhs['target_pmb'] ?? 0);
        $row++;
        $sheet->setCellValue('A' . $row, 'Realisasi Mahasiswa Baru');
        $sheet->setCellValue('B' . $row, $mhs['realisasi_pmb'] ?? 0);
        $row++;
        $sheet->setCellValue('A' . $row, 'Total Mahasiswa Aktif');
        $sheet->setCellValue('B' . $row, $mhs['total_aktif'] ?? 0);
        $row++;
        $sheet->setCellValue('A' . $row, 'Tingkat Retensi (%)');
        $sheet->setCellValue('B' . $row, ($mhs['tingkat_retensi'] ?? 0) . '%');
        $row++;
        $sheet->setCellValue('A' . $row, 'Jumlah Lulusan / Wisudawan');
        $sheet->setCellValue('B' . $row, $mhs['jumlah_lulusan'] ?? 0);
        $row += 2;

        // Prodi Table
        $sheet->setCellValue('A' . $row, '2. STATUS PROGRAM STUDI');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;

        $headers = ['Program Studi', 'Jenjang', 'Akreditasi', 'Lembaga', 'Masa Berlaku', 'Status Pengembangan'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . $row, $h);
            $col++;
        }
        $sheet->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A' . $row . ':F' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('15432D');
        $row++;

        foreach (($laporan->data_prodi ?? []) as $prodi) {
            $sheet->setCellValue('A' . $row, $prodi['nama'] ?? '-');
            $sheet->setCellValue('B' . $row, $prodi['jenjang'] ?? '-');
            $sheet->setCellValue('C' . $row, $prodi['akreditasi'] ?? '-');
            $sheet->setCellValue('D' . $row, $prodi['lembaga'] ?? '-');
            $sheet->setCellValue('E' . $row, $prodi['kadaluarsa'] ?? '-');
            $sheet->setCellValue('F' . $row, $prodi['status'] ?? '-');
            $row++;
        }

        foreach (range('A', 'F') as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'Laporan_Pengembangan_UIS_' . date('Ymd_His') . '.xlsx';

        return response()->stream(
            function () use ($writer) {
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'max-age=0',
            ]
        );
    }
}
