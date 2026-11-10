<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EvaluasiController;
use App\Http\Controllers\EvaluasiPengenalanWawasanIbnuSinaController;
use App\Http\Controllers\PeriodeAkademikController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RencanaKerjaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RekapitulasiController;
use App\Http\Controllers\KepanitiaanController;
use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\RekapitulasiKepanitiaanController;
use App\Http\Controllers\InsidentilController;
use App\Http\Controllers\RekapitulasiInsidentilController;
use App\Http\Controllers\AnalisisKeterlambatanController;
use App\Http\Controllers\PemantauanStaffController;
use App\Http\Controllers\HierarchyController;
use App\Http\Controllers\MilestoneController;
use App\Http\Controllers\RekapitulasiAbsensiController;
use App\Http\Controllers\ScanAbsensiController;
use App\Http\Controllers\AbsensiSettingController;
use App\Http\Controllers\LpjController;
use App\Http\Controllers\LaporanKinerjaPegawaiController;
use App\Http\Controllers\LaporanPengembanganController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SkKepanitiaanController;
use App\Http\Controllers\AbsensiKinerjaController;
use App\Http\Controllers\PengaturanPesanWhatsappController;
use Illuminate\Support\Facades\Route;



Route::controller(AuthController::class)->group(function () {
    Route::get('/', 'login')->name('login');
    Route::get('/login', 'login');
    Route::post('/login', 'loginproses')->name('login.proses');
    Route::get('/loginproses', 'loginproses');
    Route::post('/loginproses', 'loginproses')->name('loginproses');

    Route::get('/register', 'register')->name('register');
    Route::post('/register', 'registerproses')->name('register.proses');
    Route::get('/registerproses', 'registerproses');
    Route::post('/registerproses', 'registerproses')->name('registerproses');

    Route::post('/logout', 'logout')->name('logout');
    Route::get('/logout', 'logout');
});

Route::middleware(['auth', 'checkrole'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');
    Route::get('/dashboard/attendance-data', [DashboardController::class, 'getAttendanceData'])
        ->name('dashboard.attendance-data');
    Route::get('/notifications/data', [NotificationController::class, 'getNotifications'])
        ->name('notifications.data');
    Route::get('profile', [ProfileController::class, 'index'])->name('profile');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('profile/password', [ProfileController::class, 'editPassword'])->name('profile.password');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::get('scan-absensi', [ScanAbsensiController::class, 'index'])->name('scan-absensi');
    Route::get('user/download-template', [UserController::class, 'downloadTemplate'])->name('user.download-template');
    Route::post('user/import-excel', [UserController::class, 'importExcel'])->name('user.import-excel');
    Route::post('user/bulk-delete', [UserController::class, 'bulkDelete'])->name('user.bulk-delete');
    Route::put('user/{user}/password', [UserController::class, 'updatePassword'])->name('user.update-password');
    Route::resource('user', UserController::class);
    Route::resource('periode-akademik', PeriodeAkademikController::class);
    Route::resource('kategori', KategoriController::class);
    Route::get('pengaturan-pesan-whatsapp', [PengaturanPesanWhatsappController::class, 'create'])
        ->name('pengaturan-pesan-whatsapp.create');
    Route::post('pengaturan-pesan-whatsapp', [PengaturanPesanWhatsappController::class, 'store'])
        ->name('pengaturan-pesan-whatsapp.store');
    Route::get('pengaturan-pesan-whatsapp/index', fn () => redirect()->route('pengaturan-pesan-whatsapp.create'))
        ->name('pengaturan-pesan-whatsapp.index');
    Route::get('database-backup', [\App\Http\Controllers\DatabaseBackupController::class, 'index'])
        ->name('database-backup.index');
    Route::get('database-backup/export', [\App\Http\Controllers\DatabaseBackupController::class, 'export'])
        ->name('database-backup.export');
    Route::post('database-backup/import', [\App\Http\Controllers\DatabaseBackupController::class, 'import'])
        ->name('database-backup.import');
    Route::get('database-backup/storage-public', [\App\Http\Controllers\DatabaseBackupController::class, 'exportPublicStorage'])
        ->name('database-backup.storage-public');
    Route::post('database-backup/storage-public/import', [\App\Http\Controllers\DatabaseBackupController::class, 'importPublicStorage'])
        ->name('database-backup.storage-public.import');

    // SK Kepanitiaan (Upload & Ekstraksi Personel - Superadmin & Admin)
    Route::post('sk-kepanitiaan/{id}/link-user', [SkKepanitiaanController::class, 'linkUser'])->name('sk-kepanitiaan.link-user');
    Route::post('sk-kepanitiaan/{id}/unlink-user', [SkKepanitiaanController::class, 'unlinkUser'])->name('sk-kepanitiaan.unlink-user');
    Route::post('sk-kepanitiaan/{id}/re-extract', [SkKepanitiaanController::class, 'reExtract'])->name('sk-kepanitiaan.re-extract');
    Route::resource('sk-kepanitiaan', SkKepanitiaanController::class);

    // Hirarki Pimpinan - Bawahan
    Route::get('hirarki', [HierarchyController::class, 'index'])->name('hirarki.index');
    Route::put('hirarki/{user}/update', [HierarchyController::class, 'update'])->name('hirarki.update');
    Route::post('hirarki/bulk-assign', [HierarchyController::class, 'bulkAssign'])->name('hirarki.bulk-assign');
    Route::post('hirarki/sync-otk', [HierarchyController::class, 'syncOtk'])->name('hirarki.sync-otk');
    Route::post('hirarki/upload-otk', [HierarchyController::class, 'uploadOtk'])->name('hirarki.upload-otk');
    Route::post('hirarki/add-user', [HierarchyController::class, 'addExistingUserToOtk'])->name('hirarki.add-user');
    Route::post('hirarki/insert-row', [HierarchyController::class, 'insertOtkRow'])->name('hirarki.insert-row');
    Route::post('hirarki/move-row', [HierarchyController::class, 'moveOtkRow'])->name('hirarki.move-row');
    Route::post('hirarki/reorder', [HierarchyController::class, 'reorderOtkRows'])->name('hirarki.reorder');
    Route::post('hirarki/delete-row', [HierarchyController::class, 'deleteOtkRow'])->name('hirarki.delete-row');
    Route::put('hirarki/update-titles', [HierarchyController::class, 'updateOtkTitles'])->name('hirarki.update-titles');
    Route::post('hirarki/{user}/reset', [HierarchyController::class, 'reset'])->name('hirarki.reset');
    Route::post('hirarki/reset-all', [HierarchyController::class, 'resetAll'])->name('hirarki.reset-all');

    Route::get('rencana-kerja/download-template', [RencanaKerjaController::class, 'downloadTemplate'])->name('rencana-kerja.download-template');
    Route::get('rencana-kerja/export-excel', [RencanaKerjaController::class, 'exportExcel'])->name('rencana-kerja.export-excel');
    Route::get('rencana-kerja/export-pdf', [RencanaKerjaController::class, 'exportPdf'])->name('rencana-kerja.export-pdf');
    Route::post('rencana-kerja/import-excel', [RencanaKerjaController::class, 'importExcel'])->name('rencana-kerja.import-excel');
    Route::post('rencana-kerja/{rencana_kerja}/start', [RencanaKerjaController::class, 'start'])->name('rencana-kerja.start');
    Route::post('rencana-kerja/{rencana_kerja}/pause', [RencanaKerjaController::class, 'pause'])->name('rencana-kerja.pause');
    Route::post('rencana-kerja/{rencana_kerja}/stop', [RencanaKerjaController::class, 'stop'])->name('rencana-kerja.stop');
    Route::post('rencana-kerja/{rencana_kerja}/upload-attachment', [RencanaKerjaController::class, 'uploadAttachment'])->name('rencana-kerja.upload-attachment');
    Route::post('rencana-kerja/bulk-delete', [RencanaKerjaController::class, 'bulkDelete'])->name('rencana-kerja.bulk-delete');
    Route::post('rencana-kerja/{rencana_kerja}/update-tags', [RencanaKerjaController::class, 'updateTags'])->name('rencana-kerja.update-tags');
    Route::post('rencana-kerja/{rencana_kerja}/request-unlock-estimasi', [RencanaKerjaController::class, 'requestUnlockEstimasi'])->name('rencana-kerja.request-unlock-estimasi');
    Route::post('rencana-kerja/{rencana_kerja}/approve-extension', [RencanaKerjaController::class, 'approveExtension'])->name('rencana-kerja.approve-extension');
    Route::post('rencana-kerja/{rencana_kerja}/reopen', [RencanaKerjaController::class, 'reopen'])->name('rencana-kerja.reopen');
    Route::post('rencana-kerja/{rencana_kerja}/request-reopen', [RencanaKerjaController::class, 'requestReopen'])->name('rencana-kerja.request-reopen');
    Route::resource('rencana-kerja', RencanaKerjaController::class);

    Route::get('rekapitulasi/data', [RekapitulasiController::class, 'getData'])->name('rekapitulasi.data');
    Route::get('rekapitulasi/export-excel', [RekapitulasiController::class, 'exportExcel'])->name('rekapitulasi.export-excel');
    Route::get('rekapitulasi/export-pdf', [RekapitulasiController::class, 'exportPdf'])->name('rekapitulasi.export-pdf');
    Route::resource('rekapitulasi', RekapitulasiController::class)->only(['index']);

    // Kepanitiaan Routes
    Route::get('kepanitiaan/download-template', [KepanitiaanController::class, 'downloadTemplate'])->name('kepanitiaan.download-template');
    Route::get('kepanitiaan/export-excel', [KepanitiaanController::class, 'exportExcel'])->name('kepanitiaan.export-excel');
    Route::get('kepanitiaan/export-pdf', [KepanitiaanController::class, 'exportPdf'])->name('kepanitiaan.export-pdf');
    Route::post('kepanitiaan/import-excel', [KepanitiaanController::class, 'importExcel'])->name('kepanitiaan.import-excel');
    Route::post('kepanitiaan/{kepanitiaan}/start', [KepanitiaanController::class, 'start'])->name('kepanitiaan.start');
    Route::post('kepanitiaan/{kepanitiaan}/pause', [KepanitiaanController::class, 'pause'])->name('kepanitiaan.pause');
    Route::post('kepanitiaan/{kepanitiaan}/stop', [KepanitiaanController::class, 'stop'])->name('kepanitiaan.stop');
    Route::post('kepanitiaan/{kepanitiaan}/upload-attachment', [KepanitiaanController::class, 'uploadAttachment'])->name('kepanitiaan.upload-attachment');
    Route::post('kepanitiaan/bulk-delete', [KepanitiaanController::class, 'bulkDelete'])->name('kepanitiaan.bulk-delete');
    Route::post('kepanitiaan/{kepanitiaan}/update-tags', [KepanitiaanController::class, 'updateTags'])->name('kepanitiaan.update-tags');
    Route::post('kepanitiaan/{kepanitiaan}/request-unlock-estimasi', [KepanitiaanController::class, 'requestUnlockEstimasi'])->name('kepanitiaan.request-unlock-estimasi');
    Route::post('kepanitiaan/{kepanitiaan}/approve-extension', [KepanitiaanController::class, 'approveExtension'])->name('kepanitiaan.approve-extension');
    Route::post('kepanitiaan/{kepanitiaan}/reopen', [KepanitiaanController::class, 'reopen'])->name('kepanitiaan.reopen');
    Route::post('kepanitiaan/{kepanitiaan}/request-reopen', [KepanitiaanController::class, 'requestReopen'])->name('kepanitiaan.request-reopen');
    Route::resource('kepanitiaan', KepanitiaanController::class);

    // Kegiatan Routes
    Route::get('kegiatan/download-template', [KegiatanController::class, 'downloadTemplate'])->name('kegiatan.download-template');
    Route::get('kegiatan/export-excel', [KegiatanController::class, 'exportExcel'])->name('kegiatan.export-excel');
    Route::get('kegiatan/export-pdf', [KegiatanController::class, 'exportPdf'])->name('kegiatan.export-pdf');
    Route::post('kegiatan/import-excel', [KegiatanController::class, 'importExcel'])->name('kegiatan.import-excel');
    Route::post('kegiatan/{kegiatan}/start', [KegiatanController::class, 'start'])->name('kegiatan.start');
    Route::post('kegiatan/{kegiatan}/pause', [KegiatanController::class, 'pause'])->name('kegiatan.pause');
    Route::post('kegiatan/{kegiatan}/stop', [KegiatanController::class, 'stop'])->name('kegiatan.stop');
    Route::post('kegiatan/{kegiatan}/upload-attachment', [KegiatanController::class, 'uploadAttachment'])->name('kegiatan.upload-attachment');
    Route::post('kegiatan/bulk-delete', [KegiatanController::class, 'bulkDelete'])->name('kegiatan.bulk-delete');
    Route::post('kegiatan/{kegiatan}/update-tags', [KegiatanController::class, 'updateTags'])->name('kegiatan.update-tags');
    Route::post('kegiatan/{kegiatan}/request-unlock-estimasi', [KegiatanController::class, 'requestUnlockEstimasi'])->name('kegiatan.request-unlock-estimasi');
    Route::post('kegiatan/{kegiatan}/approve-extension', [KegiatanController::class, 'approveExtension'])->name('kegiatan.approve-extension');
    Route::post('kegiatan/{kegiatan}/reopen', [KegiatanController::class, 'reopen'])->name('kegiatan.reopen');
    Route::post('kegiatan/{kegiatan}/request-reopen', [KegiatanController::class, 'requestReopen'])->name('kegiatan.request-reopen');
    Route::get('rekapitulasi-kegiatan/data', [KegiatanController::class, 'getRekapData'])->name('rekapitulasi-kegiatan.data');
    Route::resource('kegiatan', KegiatanController::class);

    // Rekapitulasi Kepanitiaan Routes
    Route::get('rekapitulasi-kepanitiaan/data', [RekapitulasiKepanitiaanController::class, 'getData'])->name('rekapitulasi-kepanitiaan.data');
    Route::resource('rekapitulasi-kepanitiaan', RekapitulasiKepanitiaanController::class)->only(['index']);

    // Insidentil Routes
    Route::get('insidentil/download-template', [InsidentilController::class, 'downloadTemplate'])->name('insidentil.download-template');
    Route::get('insidentil/export-excel', [InsidentilController::class, 'exportExcel'])->name('insidentil.export-excel');
    Route::get('insidentil/export-pdf', [InsidentilController::class, 'exportPdf'])->name('insidentil.export-pdf');
    Route::post('insidentil/import-excel', [InsidentilController::class, 'importExcel'])->name('insidentil.import-excel');
    Route::post('insidentil/{insidentil}/start', [InsidentilController::class, 'start'])->name('insidentil.start');
    Route::post('insidentil/{insidentil}/pause', [InsidentilController::class, 'pause'])->name('insidentil.pause');
    Route::post('insidentil/{insidentil}/stop', [InsidentilController::class, 'stop'])->name('insidentil.stop');
    Route::post('insidentil/{insidentil}/upload-attachment', [InsidentilController::class, 'uploadAttachment'])->name('insidentil.upload-attachment');
    Route::post('insidentil/bulk-delete', [InsidentilController::class, 'bulkDelete'])->name('insidentil.bulk-delete');
    Route::post('insidentil/{insidentil}/update-tags', [InsidentilController::class, 'updateTags'])->name('insidentil.update-tags');
    Route::post('insidentil/{insidentil}/request-unlock-estimasi', [InsidentilController::class, 'requestUnlockEstimasi'])->name('insidentil.request-unlock-estimasi');
    Route::post('insidentil/{insidentil}/approve-extension', [InsidentilController::class, 'approveExtension'])->name('insidentil.approve-extension');
    Route::post('insidentil/{insidentil}/reopen', [InsidentilController::class, 'reopen'])->name('insidentil.reopen');
    Route::post('insidentil/{insidentil}/request-reopen', [InsidentilController::class, 'requestReopen'])->name('insidentil.request-reopen');
    Route::resource('insidentil', InsidentilController::class);

    // Rekapitulasi Insidentil Routes
    Route::get('rekapitulasi-insidentil/data', [RekapitulasiInsidentilController::class, 'getData'])->name('rekapitulasi-insidentil.data');
    Route::resource('rekapitulasi-insidentil', RekapitulasiInsidentilController::class)->only(['index']);

    // Analisis Keterlambatan Routes
    Route::get('analisis-keterlambatan/data', [AnalisisKeterlambatanController::class, 'getData'])->name('analisis-keterlambatan.data');
    Route::get('analisis-keterlambatan/export-excel', [AnalisisKeterlambatanController::class, 'exportExcel'])->name('analisis-keterlambatan.export-excel');
    Route::get('analisis-keterlambatan/export-pdf', [AnalisisKeterlambatanController::class, 'exportPdf'])->name('analisis-keterlambatan.export-pdf');
    Route::post('analisis-keterlambatan/{id}/saran-pimpinan', [AnalisisKeterlambatanController::class, 'updateSaranPimpinan'])->name('analisis-keterlambatan.update-saran');
    Route::resource('analisis-keterlambatan', AnalisisKeterlambatanController::class)->only(['index']);

    // Pemantauan Staff Routes
    Route::get('pemantauan-staff/detail/{id}', [PemantauanStaffController::class, 'detail'])->name('pemantauan-staff.detail');
    Route::get('pemantauan-staff/export-excel', [PemantauanStaffController::class, 'exportExcel'])->name('pemantauan-staff.export-excel');
    Route::get('pemantauan-staff/export-pdf', [PemantauanStaffController::class, 'exportPdf'])->name('pemantauan-staff.export-pdf');
    Route::get('pemantauan-staff/{slug?}', [PemantauanStaffController::class, 'index'])->name('pemantauan-staff.index');

    // Laporan Eksekutif Yayasan & Universitas Ibnu Sina
    Route::get('laporan-kinerja-pegawai', [LaporanKinerjaPegawaiController::class, 'index'])->name('laporan-kinerja-pegawai.index');
    Route::get('laporan-kinerja-pegawai/export-pdf', [LaporanKinerjaPegawaiController::class, 'exportPdf'])->name('laporan-kinerja-pegawai.export-pdf');
    Route::get('laporan-kinerja-pegawai/export-excel', [LaporanKinerjaPegawaiController::class, 'exportExcel'])->name('laporan-kinerja-pegawai.export-excel');

    Route::get('laporan-pengembangan/export-pdf/{id}', [LaporanPengembanganController::class, 'exportPdf'])->name('laporan-pengembangan.export-pdf');
    Route::get('laporan-pengembangan/export-excel/{id}', [LaporanPengembanganController::class, 'exportExcel'])->name('laporan-pengembangan.export-excel');
    Route::resource('laporan-pengembangan', LaporanPengembanganController::class);

    // LPJ (Laporan Pertanggungjawaban) Routes
    Route::get('lpj/template', [LpjController::class, 'template'])->name('lpj.template');
    Route::post('lpj/template', [LpjController::class, 'updateTemplate'])->name('lpj.template.update');
    Route::get('lpj/template/download', [LpjController::class, 'downloadTemplate'])->name('lpj.template.download');
    Route::get('lpj/{lpj}/download', [LpjController::class, 'download'])->name('lpj.download');
    Route::resource('lpj', LpjController::class);

    // Milestone Routes
    Route::post('milestone', [MilestoneController::class, 'store'])->name('milestone.store');
    Route::post('milestone/{milestone}/start', [MilestoneController::class, 'start'])->name('milestone.start');
    Route::post('milestone/{milestone}/pause', [MilestoneController::class, 'pause'])->name('milestone.pause');
    Route::post('milestone/{milestone}/stop', [MilestoneController::class, 'stop'])->name('milestone.stop');
    Route::delete('milestone/{milestone}', [MilestoneController::class, 'destroy'])->name('milestone.destroy');
    Route::get('milestone/data', [MilestoneController::class, 'index'])->name('milestone.index');

    Route::resource('evaluasi', EvaluasiController::class)->except(['edit', 'update']);



    Route::resource('evaluasi-pengenalan-wawasan-ibnu-sina', EvaluasiPengenalanWawasanIbnuSinaController::class)
        ->names('evaluasipengenalanwawasanibnusina')
        ->parameters([
            'evaluasi-pengenalan-wawasan-ibnu-sina' => 'wawasan'
        ]);
    // Rekapitulasi Absensi Kepanitiaan (Sudah & Belum Absen)
    Route::get('rekapitulasi-absensi', [RekapitulasiAbsensiController::class, 'index'])->name('rekapitulasi-absensi.index');
    Route::get('rekapitulasi-absensi/data-sudah', [RekapitulasiAbsensiController::class, 'getDataSudah'])->name('rekapitulasi-absensi.data-sudah');
    Route::get('rekapitulasi-absensi/data-belum', [RekapitulasiAbsensiController::class, 'getDataBelum'])->name('rekapitulasi-absensi.data-belum');
    Route::get('rekapitulasi-absensi/stats', [RekapitulasiAbsensiController::class, 'getStats'])->name('rekapitulasi-absensi.stats');

    // Absensi Kinerja Harian
    Route::post('absensi-kinerja/manual', [AbsensiKinerjaController::class, 'storeManual'])->name('absensi-kinerja.manual');
    Route::delete('absensi-kinerja/manual/{id}', [AbsensiKinerjaController::class, 'destroyManual'])->name('absensi-kinerja.manual.destroy');
    Route::get('absensi-kinerja/export', [AbsensiKinerjaController::class, 'export'])->name('absensi-kinerja.export');
    Route::get('absensi-kinerja/export-pdf', [AbsensiKinerjaController::class, 'exportPdf'])->name('absensi-kinerja.export-pdf');
    Route::get('absensi-kinerja/{slug?}/{user_slug?}', [AbsensiKinerjaController::class, 'index'])->name('absensi-kinerja.index');

    // Pengaturan Status Sesi Absensi (is_active)
    Route::get('pengaturan-absensi', [AbsensiSettingController::class, 'index'])->name('pengaturan-absensi.index');
    Route::post('pengaturan-absensi/{slug}/toggle', [AbsensiSettingController::class, 'toggle'])->name('pengaturan-absensi.toggle');

    // Universal Presensi Kepanitiaan (Dynamic Attendance Engine)
    Route::get('presensi/scan-proses/{token}', [\App\Http\Controllers\UniversalPresensiController::class, 'scanProses'])->name('presensi.scan-proses');
    Route::get('presensi/sesi/{id}', [\App\Http\Controllers\UniversalPresensiController::class, 'showSesi'])->name('presensi.sesi.show');
    Route::get('presensi/sesi/{id}/data', [\App\Http\Controllers\UniversalPresensiController::class, 'getSesiData'])->name('presensi.sesi.data');
    Route::post('presensi/sesi/{id}/manual', [\App\Http\Controllers\UniversalPresensiController::class, 'storeManualPresensi'])->name('presensi.sesi.manual');
    Route::post('presensi/sesi/{id}/bulk-update', [\App\Http\Controllers\UniversalPresensiController::class, 'bulkUpdatePresensi'])->name('presensi.sesi.bulk-update');
    Route::delete('presensi/presensi/{id}', [\App\Http\Controllers\UniversalPresensiController::class, 'destroyPresensi'])->name('presensi.presensi.destroy');
    Route::get('kepanitiaan/{id}/sesi', [\App\Http\Controllers\UniversalPresensiController::class, 'getSesiByKepanitiaan'])->name('presensi.sesi.list');
    Route::post('presensi/sesi/{id}/toggle', [\App\Http\Controllers\UniversalPresensiController::class, 'toggleStatus'])->name('presensi.sesi.toggle');
    Route::post('kepanitiaan/{id}/sesi', [\App\Http\Controllers\UniversalPresensiController::class, 'storeSesi'])->name('presensi.sesi.store');
    Route::put('presensi/sesi/{id}', [\App\Http\Controllers\UniversalPresensiController::class, 'updateSesi'])->name('presensi.sesi.update');
    Route::delete('presensi/sesi/{id}', [\App\Http\Controllers\UniversalPresensiController::class, 'destroySesi'])->name('presensi.sesi.destroy');
    Route::get('presensi/sesi/{id}/export', [\App\Http\Controllers\UniversalPresensiController::class, 'exportExcel'])->name('presensi.sesi.export');
});