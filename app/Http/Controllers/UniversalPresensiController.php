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
                'detail_url' => route('presensi.sesi.show', ['id' => $s->id]),
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
            return redirect()->route('presensi.sesi.show', $sesi->id);
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

        $targetRedirect = ($authUser->isAdmin() || $authUser->isSuperAdmin())
            ? route('presensi.sesi.show', $sesi->id)
            : route('kepanitiaan.index');

        if ($sessionType === 'datang') {
            if (!empty($presensi->hadir_datang) && (string)$presensi->hadir_datang === 'Hadir') {
                $msg = 'Anda sudah melakukan absensi datang sebelumnya pada jam ' . ($presensi->waktu_datang ?? '-') . ' WIB.';
                if ($isAjax) {
                    return response()->json(['success' => false, 'message' => $msg]);
                }
                Alert::warning('Info', $msg);
                return redirect($targetRedirect);
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
                return redirect($targetRedirect);
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
        return redirect($targetRedirect);
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
        $authUser = Auth::user();
        if (!$authUser || (!$authUser->isAdmin() && !$authUser->isSuperAdmin())) {
            abort(403, 'Akses ditolak. Hanya administrator yang dapat mengunduh rekap presensi.');
        }

        $sesi = KepanitiaanSesiAbsensi::with(['kepanitiaan', 'presensis.user'])->findOrFail($sesiId);
        $presensis = $sesi->presensis()->with('user')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Presensi Sesi');

        // Header Title
        $sheet->mergeCells('A1:K1');
        $sheet->setCellValue('A1', 'DAFTAR PRESENSI ' . strtoupper($sesi->nama_sesi));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $kepanitiaanNama = $sesi->kepanitiaan ? $sesi->kepanitiaan->uraian_tugas : '-';
        $sheet->mergeCells('A2:K2');
        $sheet->setCellValue('A2', 'Kepanitiaan: ' . $kepanitiaanNama . ' | Tanggal Sesi: ' . ($sesi->tanggal ? date('d-m-Y', strtotime($sesi->tanggal)) : '-'));
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Table Header
        $headers = [
            'A4' => 'NO',
            'B4' => 'TANGGAL',
            'C4' => 'NAMA PEGAWAI',
            'D4' => 'NIDN / NIP',
            'E4' => 'JABATAN',
            'F4' => 'UNIT KERJA',
            'G4' => 'STATUS DATANG',
            'H4' => 'WAKTU DATANG',
            'I4' => 'STATUS PULANG',
            'J4' => 'WAKTU PULANG',
            'K4' => 'CATATAN',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $sheet->getStyle('A4:K4')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A4:K4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('1E3A8A');
        $sheet->getStyle('A4:K4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(4)->setRowHeight(28);

        $rowNum = 5;
        $no = 1;
        foreach ($presensis as $p) {
            $user = $p->user;
            $sheet->setCellValue('A' . $rowNum, $no++);
            $sheet->setCellValue('B' . $rowNum, $p->created_at ? $p->created_at->format('d/m/Y') : '-');
            $sheet->setCellValue('C' . $rowNum, $user->name ?? '-');
            $sheet->setCellValue('D' . $rowNum, $user->nidn ?? '-');
            $sheet->setCellValue('E' . $rowNum, $user->jabatan ?? '-');
            $sheet->setCellValue('F' . $rowNum, $user->unit ?? '-');
            $sheet->setCellValue('G' . $rowNum, $p->hadir_datang ?? '-');
            $sheet->setCellValue('H' . $rowNum, $p->waktu_datang ?? '-');
            $sheet->setCellValue('I' . $rowNum, $p->hadir_pulang ?? '-');
            $sheet->setCellValue('J' . $rowNum, $p->waktu_pulang ?? '-');
            $sheet->setCellValue('K' . $rowNum, $p->catatan_hadir_datang ?: ($p->catatan_hadir_pulang ?: '-'));

            $sheet->getStyle('A' . $rowNum . ':B' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . $rowNum . ':J' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $rowNum++;
        }

        $lastRow = max(5, $rowNum - 1);
        $tableRange = 'A4:K' . $lastRow;
        $sheet->getStyle($tableRange)->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
            ->getColor()->setRGB('CCCCCC');

        foreach (range('A', 'K') as $col) {
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

    /**
     * Display dedicated attendance page for a session (Data Absensi).
     */
    public function showSesi($id)
    {
        $authUser = Auth::user();
        if (!$authUser || (!$authUser->isAdmin() && !$authUser->isSuperAdmin())) {
            Alert::error('Akses Ditolak', 'Hanya administrator yang dapat mengakses data absensi.');
            return redirect()->route('dashboard');
        }

        $sesi = KepanitiaanSesiAbsensi::with('kepanitiaan')->findOrFail($id);

        $totalPresensi = KepanitiaanPresensi::where('kepanitiaan_sesi_absensi_id', $id)->count();
        $totalHadirDatang = KepanitiaanPresensi::where('kepanitiaan_sesi_absensi_id', $id)
            ->where('hadir_datang', 'Hadir')->count();
        $totalHadirPulang = KepanitiaanPresensi::where('kepanitiaan_sesi_absensi_id', $id)
            ->where('hadir_pulang', 'Hadir')->count();
        $totalIzinSakit = KepanitiaanPresensi::where('kepanitiaan_sesi_absensi_id', $id)
            ->where(function ($q) {
                $q->whereIn('hadir_datang', ['Izin', 'Sakit', 'Alpha'])
                  ->orWhereIn('hadir_pulang', ['Izin', 'Sakit', 'Alpha']);
            })->count();

        $users = User::orderBy('name', 'asc')->get();
        $allSesis = KepanitiaanSesiAbsensi::orderBy('tanggal', 'desc')->orderBy('id', 'asc')->get();

        return view('pages.presensi.show', compact(
            'sesi',
            'totalPresensi',
            'totalHadirDatang',
            'totalHadirPulang',
            'totalIzinSakit',
            'users',
            'allSesis'
        ));
    }

    /**
     * Get DataTables JSON data for the session attendance list.
     */
    public function getSesiData($id)
    {
        $authUser = Auth::user();
        if (!$authUser || (!$authUser->isAdmin() && !$authUser->isSuperAdmin())) {
            return response()->json(['error' => 'Akses ditolak.'], 403);
        }

        $query = KepanitiaanPresensi::with('user')
            ->where('kepanitiaan_sesi_absensi_id', $id)
            ->latest();

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('tanggal', function ($p) {
                if (!$p->created_at) {
                    return '<span class="text-muted">-</span>';
                }
                $tgl = $p->created_at->format('d/m/Y');
                $hari = $p->created_at->translatedFormat('l');
                return '<div class="fw-bold text-dark text-nowrap"><i class="bi bi-calendar3 text-primary me-1"></i>' . $tgl . '</div><small class="text-muted text-nowrap">' . $hari . '</small>';
            })
            ->filterColumn('user_info', function ($query, $keyword) {
                $query->whereHas('user', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                      ->orWhere('jabatan', 'like', "%{$keyword}%")
                      ->orWhere('unit', 'like', "%{$keyword}%");
                });
            })
            ->addColumn('user_info', function ($p) {
                $u = $p->user;
                if (!$u) return '<span class="text-muted fst-italic">Pengguna tidak ditemukan</span>';
                
                $initial = strtoupper(substr($u->name, 0, 1));
                $html = '<div class="d-flex align-items-center gap-2">';
                $html .= '<div class="rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; font-size: 0.85rem;">' . $initial . '</div>';
                $html .= '<div>';
                $html .= '<div class="fw-bold text-dark" style="font-size: 0.9rem;">' . e($u->name) . '</div>';
                $html .= '<small class="text-muted" style="font-size: 0.76rem;">' . e($u->jabatan ?? '-') . ' &bull; ' . e($u->unit ?? '-') . '</small>';
                $html .= '</div></div>';
                return $html;
            })
            ->addColumn('status_datang', function ($p) {
                $status = $p->hadir_datang ?: 'Belum Absen';
                $color = match ($status) {
                    'Hadir' => 'bg-success',
                    'Izin' => 'bg-warning text-dark',
                    'Sakit' => 'bg-info text-dark',
                    'Alpha' => 'bg-danger',
                    default => 'bg-secondary',
                };
                $time = $p->waktu_datang ? '<div class="small text-muted mt-0.5"><i class="bi bi-clock me-1"></i>' . substr($p->waktu_datang, 0, 5) . ' WIB</div>' : '';
                return '<span class="badge ' . $color . '">' . $status . '</span>' . $time;
            })
            ->addColumn('status_pulang', function ($p) {
                $status = $p->hadir_pulang ?: 'Belum Absen';
                $color = match ($status) {
                    'Hadir' => 'bg-success',
                    'Izin' => 'bg-warning text-dark',
                    'Sakit' => 'bg-info text-dark',
                    'Alpha' => 'bg-danger',
                    default => 'bg-secondary',
                };
                $time = $p->waktu_pulang ? '<div class="small text-muted mt-0.5"><i class="bi bi-clock me-1"></i>' . substr($p->waktu_pulang, 0, 5) . ' WIB</div>' : '';
                return '<span class="badge ' . $color . '">' . $status . '</span>' . $time;
            })
            ->addColumn('catatan_col', function ($p) {
                $txt = $p->catatan_hadir_datang ?: ($p->catatan_hadir_pulang ?: '-');
                $bukti = '';
                if (!empty($p->bukti_izin)) {
                    $url = asset('storage/' . $p->bukti_izin);
                    $bukti = '<br><a href="' . $url . '" target="_blank" class="badge bg-light text-primary border mt-1"><i class="bi bi-paperclip me-1"></i>Bukti Izin</a>';
                }
                return '<small class="text-secondary">' . e($txt) . '</small>' . $bukti;
            })
            ->addColumn('actions', function ($p) {
                $editData = htmlspecialchars(json_encode([
                    'id' => $p->id,
                    'user_id' => $p->user_id,
                    'hadir_datang' => $p->hadir_datang,
                    'waktu_datang' => $p->waktu_datang ? substr($p->waktu_datang, 0, 5) : '',
                    'hadir_pulang' => $p->hadir_pulang,
                    'waktu_pulang' => $p->waktu_pulang ? substr($p->waktu_pulang, 0, 5) : '',
                    'catatan' => $p->catatan_hadir_datang ?: $p->catatan_hadir_pulang,
                ]), ENT_QUOTES, 'UTF-8');

                $btn = '<div class="d-inline-flex gap-1">';
                $btn .= '<button type="button" class="btn btn-sm btn-outline-primary" style="padding: 4px 8px;" onclick="window.editPresensiRow(' . $editData . ')" title="Ubah Data Presensi"><i class="bi bi-pencil"></i></button>';
                $btn .= '<button type="button" class="btn btn-sm btn-outline-danger" style="padding: 4px 8px;" onclick="window.deletePresensiRow(' . $p->id . ')" title="Hapus Data"><i class="bi bi-trash"></i></button>';
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['tanggal', 'user_info', 'status_datang', 'status_pulang', 'catatan_col', 'actions'])
            ->make(true);
    }

    /**
     * Store or update manual attendance record for an employee.
     */
    public function storeManualPresensi(Request $request, $id)
    {
        $authUser = Auth::user();
        if (!$authUser || (!$authUser->isAdmin() && !$authUser->isSuperAdmin())) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'hadir_datang' => 'nullable|string|in:Hadir,Izin,Sakit,Alpha',
            'waktu_datang' => 'nullable',
            'hadir_pulang' => 'nullable|string|in:Hadir,Izin,Sakit,Alpha',
            'waktu_pulang' => 'nullable',
            'catatan' => 'nullable|string|max:500',
        ]);

        $sesi = KepanitiaanSesiAbsensi::findOrFail($id);

        $presensi = KepanitiaanPresensi::firstOrNew([
            'kepanitiaan_sesi_absensi_id' => $sesi->id,
            'user_id' => $request->user_id,
        ]);

        if ($request->filled('hadir_datang')) {
            $presensi->hadir_datang = $request->hadir_datang;
            $presensi->waktu_datang = $request->waktu_datang ?: now()->format('H:i');
        }
        if ($request->filled('hadir_pulang')) {
            $presensi->hadir_pulang = $request->hadir_pulang;
            $presensi->waktu_pulang = $request->waktu_pulang ?: now()->format('H:i');
        }
        if ($request->filled('catatan')) {
            $presensi->catatan_hadir_datang = $request->catatan;
        }

        $presensi->save();

        return response()->json([
            'success' => true,
            'message' => 'Data presensi berhasil disimpan.',
        ]);
    }

    /**
     * Delete a single attendance record.
     */
    public function destroyPresensi($id)
    {
        $authUser = Auth::user();
        if (!$authUser || (!$authUser->isAdmin() && !$authUser->isSuperAdmin())) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $presensi = KepanitiaanPresensi::findOrFail($id);
        if ($presensi->bukti_izin && Storage::disk('public')->exists($presensi->bukti_izin)) {
            Storage::disk('public')->delete($presensi->bukti_izin);
        }
        $presensi->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data presensi berhasil dihapus.',
        ]);
    }
}
