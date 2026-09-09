<?php

namespace App\Http\Controllers;

use App\Models\Kepanitiaan;
use App\Models\KepanitiaanPresensi;
use App\Models\KepanitiaanSesiAbsensi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RealRashid\SweetAlert\Facades\Alert;
use Yajra\DataTables\Facades\DataTables;

class UniversalPresensiController extends Controller
{
    /**
     * Get list of attendance sessions for a specific kepanitiaan.
     */
    public function getSesiByKepanitiaan($kepanitiaanId)
    {
        $kepanitiaan = Kepanitiaan::with('sesiAbsensis.presensis')->findOrFail($kepanitiaanId);
        $sesis = $kepanitiaan->sesiAbsensis->map(function ($s) {
            return [
                'id' => $s->id,
                'nama_sesi' => $s->nama_sesi,
                'tanggal' => $s->tanggal ? date('d/m/Y', strtotime($s->tanggal)) : '-',
                'token' => $s->token,
                'is_active' => (bool) $s->is_active,
                'total_presensi' => $s->presensis->count(),
                'scan_url' => route('presensi.scan-proses', ['token' => $s->token]),
                'export_url' => route('presensi.sesi.export', ['id' => $s->id]),
            ];
        });

        return response()->json([
            'success' => true,
            'kepanitiaan' => $kepanitiaan->uraian_tugas,
            'sesis' => $sesis,
        ]);
    }
    /**
     * Process scanned QR Code to record attendance automatically via session token.
     */
    public function scanProses(Request $request, $token)
    {
        $isAjax = $request->ajax() || $request->wantsJson();

        $sesi = KepanitiaanSesiAbsensi::where('token', $token)->first();
        if (!$sesi) {
            $msg = 'Sesi absensi tidak ditemukan atau token tidak valid.';
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $msg], 404);
            }
            Alert::error('Gagal', $msg);
            return redirect()->route('kepanitiaan.index');
        }

        if (!$sesi->is_active) {
            $msg = 'Sesi absensi "' . $sesi->nama_sesi . '" sedang dinonaktifkan / ditutup oleh panitia.';
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $msg], 400);
            }
            Alert::warning('Pemberitahuan', $msg);
            return redirect()->route('kepanitiaan.index');
        }

        $authUser = Auth::user();
        if (!$authUser) {
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => 'Silakan login terlebih dahulu.'], 401);
            }
            abort(401, 'Silakan login terlebih dahulu.');
        }

        $sessionType = strtolower($request->query('session', 'datang'));
        if (!in_array($sessionType, ['datang', 'pulang'])) {
            $sessionType = 'datang';
        }

        $currentTime = now()->format('H:i');
        $currentDateTime = now()->format('Y-m-d H:i:s');

        $presensi = KepanitiaanPresensi::firstOrNew([
            'kepanitiaan_sesi_absensi_id' => $sesi->id,
            'user_id' => $authUser->id,
        ]);

        if ($sessionType === 'datang') {
            if (!empty($presensi->hadir_datang) && (string)$presensi->hadir_datang === 'Hadir') {
                $msg = 'Anda sudah melakukan absensi datang sebelumnya pada jam ' . ($presensi->waktu_datang ?? '-') . ' WIB.';
                if ($isAjax) {
                    return response()->json(['success' => false, 'message' => $msg]);
                }
                Alert::warning('Info', $msg);
                return redirect()->route('kepanitiaan.index');
            }

            $presensi->hadir_datang = 'Hadir';
            $presensi->waktu_datang = $currentTime;
            $msg = 'Absensi Datang untuk "' . $sesi->nama_sesi . '" berhasil dicatat otomatis pada pukul ' . $currentTime . ' WIB.';
        } else {
            if (!empty($presensi->hadir_pulang) && (string)$presensi->hadir_pulang === 'Hadir') {
                $msg = 'Anda sudah melakukan absensi pulang sebelumnya pada jam ' . ($presensi->waktu_pulang ?? '-') . ' WIB.';
                if ($isAjax) {
                    return response()->json(['success' => false, 'message' => $msg]);
                }
                Alert::warning('Info', $msg);
                return redirect()->route('kepanitiaan.index');
            }

            $presensi->hadir_pulang = 'Hadir';
            $presensi->waktu_pulang = $currentTime;
            $msg = 'Absensi Pulang untuk "' . $sesi->nama_sesi . '" berhasil dicatat otomatis pada pukul ' . $currentTime . ' WIB.';
        }

        $presensi->save();

        if ($isAjax) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'user' => $authUser->name,
                'sesi' => $sesi->nama_sesi,
                'waktu' => $currentTime,
            ]);
        }

        Alert::success('Berhasil', $msg);
        return redirect()->route('kepanitiaan.index');
    }

    /**
     * Toggle status aktif / tutup sesi presensi.
     */
    public function toggleStatus(Request $request, $id)
    {
        $authUser = Auth::user();
        if (!$authUser || (!$authUser->isAdmin() && !$authUser->isSuperAdmin() && !$authUser->isPimpinanRektorat() && !$authUser->isPimpinanUnit())) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki hak akses untuk mengubah status sesi.'], 403);
        }

        $sesi = KepanitiaanSesiAbsensi::findOrFail($id);
        $sesi->is_active = !$sesi->is_active;
        $sesi->save();

        // Sync legacy status if legacy_slug exists
        if (!empty($sesi->legacy_slug)) {
            \App\Models\AbsensiSetting::setStatus($sesi->legacy_slug, $sesi->is_active);
        }

        $statusText = $sesi->is_active ? 'Aktif (Buka)' : 'Nonaktif (Tutup / Dikunci)';

        return response()->json([
            'success' => true,
            'is_active' => $sesi->is_active,
            'status_label' => $statusText,
            'message' => 'Status sesi "' . $sesi->nama_sesi . '" berhasil diubah menjadi ' . $statusText . '.',
        ]);
    }

    /**
     * Tambah sesi presensi baru pada suatu kepanitiaan.
     */
    public function storeSesi(Request $request, $kepanitiaanId)
    {
        $authUser = Auth::user();
        $kepanitiaan = Kepanitiaan::findOrFail($kepanitiaanId);

        // Check ownership or admin
        if (!$authUser || (!$authUser->isAdmin() && !$authUser->isSuperAdmin() && $kepanitiaan->user_id !== $authUser->id)) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
            }
            Alert::error('Akses Ditolak', 'Anda tidak memiliki izin mengelola sesi ini.');
            return redirect()->back();
        }

        $validated = $request->validate([
            'nama_sesi' => 'required|string|max:255',
            'tanggal' => 'nullable|date',
            'waktu_mulai_datang' => 'nullable|string',
            'waktu_selesai_datang' => 'nullable|string',
            'waktu_mulai_pulang' => 'nullable|string',
            'waktu_selesai_pulang' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['kepanitiaan_id'] = $kepanitiaan->id;
        $validated['token'] = Str::random(24);
        $validated['is_active'] = $request->has('is_active') ? (bool)$request->is_active : true;

        $sesi = KepanitiaanSesiAbsensi::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Sesi presensi "' . $sesi->nama_sesi . '" berhasil ditambahkan.',
                'data' => $sesi,
            ]);
        }

        Alert::success('Berhasil', 'Sesi presensi "' . $sesi->nama_sesi . '" berhasil ditambahkan.');
        return redirect()->back();
    }

    /**
     * Update data sesi presensi.
     */
    public function updateSesi(Request $request, $id)
    {
        $authUser = Auth::user();
        $sesi = KepanitiaanSesiAbsensi::findOrFail($id);

        if (!$authUser || (!$authUser->isAdmin() && !$authUser->isSuperAdmin() && $sesi->kepanitiaan?->user_id !== $authUser->id)) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
            }
            Alert::error('Akses Ditolak', 'Anda tidak memiliki izin mengubah sesi ini.');
            return redirect()->back();
        }

        $validated = $request->validate([
            'nama_sesi' => 'required|string|max:255',
            'tanggal' => 'nullable|date',
            'waktu_mulai_datang' => 'nullable|string',
            'waktu_selesai_datang' => 'nullable|string',
            'waktu_mulai_pulang' => 'nullable|string',
            'waktu_selesai_pulang' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $sesi->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Sesi presensi berhasil diperbarui.',
                'data' => $sesi,
            ]);
        }

        Alert::success('Berhasil', 'Sesi presensi berhasil diperbarui.');
        return redirect()->back();
    }

    /**
     * Hapus sesi presensi beserta seluruh data presensinya.
     */
    public function destroySesi(Request $request, $id)
    {
        $authUser = Auth::user();
        $sesi = KepanitiaanSesiAbsensi::findOrFail($id);

        if (!$authUser || (!$authUser->isAdmin() && !$authUser->isSuperAdmin() && $sesi->kepanitiaan?->user_id !== $authUser->id)) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
            }
            Alert::error('Akses Ditolak', 'Anda tidak memiliki izin menghapus sesi ini.');
            return redirect()->back();
        }

        $namaSesi = $sesi->nama_sesi;
        // Presensis cascade delete
        $sesi->presensis()->delete();
        $sesi->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Sesi presensi "' . $namaSesi . '" beserta datanya berhasil dihapus.',
            ]);
        }

        Alert::success('Berhasil', 'Sesi presensi "' . $namaSesi . '" berhasil dihapus.');
        return redirect()->back();
    }

    /**
     * Export data presensi suatu sesi ke Excel.
     */
    public function exportExcel($sesiId)
    {
        $sesi = KepanitiaanSesiAbsensi::with(['kepanitiaan', 'presensis.user'])->findOrFail($sesiId);
        $presensis = $sesi->presensis()->with('user')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Presensi Sesi');

        // Header Title
        $sheet->mergeCells('A1:J1');
        $sheet->setCellValue('A1', 'DAFTAR PRESENSI ' . strtoupper($sesi->nama_sesi));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $kepanitiaanNama = $sesi->kepanitiaan ? $sesi->kepanitiaan->uraian_tugas : '-';
        $sheet->mergeCells('A2:J2');
        $sheet->setCellValue('A2', 'Kepanitiaan: ' . $kepanitiaanNama . ' | Tanggal: ' . ($sesi->tanggal ? date('d-m-Y', strtotime($sesi->tanggal)) : '-'));
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Table Header
        $headers = [
            'A4' => 'NO',
            'B4' => 'NAMA PEGAWAI',
            'C4' => 'NIDN / NIP',
            'D4' => 'JABATAN',
            'E4' => 'UNIT KERJA',
            'F4' => 'STATUS DATANG',
            'G4' => 'WAKTU DATANG',
            'H4' => 'STATUS PULANG',
            'I4' => 'WAKTU PULANG',
            'J4' => 'CATATAN',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $sheet->getStyle('A4:J4')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A4:J4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('1E3A8A');
        $sheet->getStyle('A4:J4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(4)->setRowHeight(28);

        $rowNum = 5;
        $no = 1;
        foreach ($presensis as $p) {
            $user = $p->user;
            $sheet->setCellValue('A' . $rowNum, $no++);
            $sheet->setCellValue('B' . $rowNum, $user->name ?? '-');
            $sheet->setCellValue('C' . $rowNum, $user->nidn ?? '-');
            $sheet->setCellValue('D' . $rowNum, $user->jabatan ?? '-');
            $sheet->setCellValue('E' . $rowNum, $user->unit ?? '-');
            $sheet->setCellValue('F' . $rowNum, $p->hadir_datang ?? '-');
            $sheet->setCellValue('G' . $rowNum, $p->waktu_datang ?? '-');
            $sheet->setCellValue('H' . $rowNum, $p->hadir_pulang ?? '-');
            $sheet->setCellValue('I' . $rowNum, $p->waktu_pulang ?? '-');
            $sheet->setCellValue('J' . $rowNum, $p->catatan ?? '-');

            $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $rowNum . ':I' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $rowNum++;
        }

        $lastRow = max(5, $rowNum - 1);
        $tableRange = 'A4:J' . $lastRow;
        $sheet->getStyle($tableRange)->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
            ->getColor()->setRGB('CCCCCC');

        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $safeTitle = preg_replace('/[^A-Za-z0-9_\-]/', '_', $sesi->nama_sesi);
        $filename = 'Presensi_' . $safeTitle . '_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
