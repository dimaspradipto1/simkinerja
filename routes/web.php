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
use App\Http\Controllers\MilestoneController;
use App\Http\Controllers\AbsensiPkkmbPertamaController;
use App\Http\Controllers\AbsensiPkkmbKeduaController;
use App\Http\Controllers\AbsensiPkkmbKetigaController;
use App\Http\Controllers\AbsensiEsqPertamaController;
use App\Http\Controllers\AbsensiEsqKeduaController;
use App\Http\Controllers\AbsensiEsqKetigaController;
use App\Http\Controllers\AbsensiEsqKeempatController;
use App\Http\Controllers\AbsensiMiladPertamaController;
use App\Http\Controllers\AbsensiMiladKeduaController;
use App\Http\Controllers\AbsensiKuliahUmumPertamaController;
use App\Http\Controllers\RekapitulasiAbsensiController;
use App\Http\Controllers\ScanAbsensiController;
use App\Http\Controllers\AbsensiSettingController;
use App\Http\Controllers\LpjController;
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
    Route::resource('rencana-kerja', RencanaKerjaController::class);

    Route::get('rekapitulasi/data', [RekapitulasiController::class, 'getData'])->name('rekapitulasi.data');
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
    Route::resource('kepanitiaan', KepanitiaanController::class);

    // Kegiatan Routes
    Route::get('kegiatan/export-pdf', [KegiatanController::class, 'exportPdf'])->name('kegiatan.export-pdf');
    Route::post('kegiatan/{kegiatan}/start', [KegiatanController::class, 'start'])->name('kegiatan.start');
    Route::post('kegiatan/{kegiatan}/pause', [KegiatanController::class, 'pause'])->name('kegiatan.pause');
    Route::post('kegiatan/{kegiatan}/stop', [KegiatanController::class, 'stop'])->name('kegiatan.stop');
    Route::post('kegiatan/{kegiatan}/upload-attachment', [KegiatanController::class, 'uploadAttachment'])->name('kegiatan.upload-attachment');
    Route::post('kegiatan/bulk-delete', [KegiatanController::class, 'bulkDelete'])->name('kegiatan.bulk-delete');
    Route::post('kegiatan/{kegiatan}/update-tags', [KegiatanController::class, 'updateTags'])->name('kegiatan.update-tags');
    Route::post('kegiatan/{kegiatan}/request-unlock-estimasi', [KegiatanController::class, 'requestUnlockEstimasi'])->name('kegiatan.request-unlock-estimasi');
    Route::post('kegiatan/{kegiatan}/approve-extension', [KegiatanController::class, 'approveExtension'])->name('kegiatan.approve-extension');
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

    // LPJ (Laporan Pertanggungjawaban) Routes
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

    Route::get('absensi-pkkmb-pertama/export-excel', [AbsensiPkkmbPertamaController::class, 'exportExcel'])->name('absensi-pkkmb-pertama.export-excel');
    Route::get('absensi-pkkmb-pertama/scan', [AbsensiPkkmbPertamaController::class, 'scan'])->name('absensi-pkkmb-pertama.scan');
    Route::get('absensi-pkkmb-pertama/scan-proses', [AbsensiPkkmbPertamaController::class, 'scanProses'])->name('absensi-pkkmb-pertama.scan-proses');
    Route::post('absensi-pkkmb-pertama/cleanup-orphaned', [AbsensiPkkmbPertamaController::class, 'cleanupOrphaned'])->name('absensi-pkkmb-pertama.cleanup-orphaned');
    Route::resource('absensi-pkkmb-pertama', AbsensiPkkmbPertamaController::class);

    Route::get('absensi-pkkmb-kedua/export-excel', [AbsensiPkkmbKeduaController::class, 'exportExcel'])->name('absensi-pkkmb-kedua.export-excel');
    Route::get('absensi-pkkmb-kedua/scan', [AbsensiPkkmbKeduaController::class, 'scan'])->name('absensi-pkkmb-kedua.scan');
    Route::get('absensi-pkkmb-kedua/scan-proses', [AbsensiPkkmbKeduaController::class, 'scanProses'])->name('absensi-pkkmb-kedua.scan-proses');
    Route::post('absensi-pkkmb-kedua/cleanup-orphaned', [AbsensiPkkmbKeduaController::class, 'cleanupOrphaned'])->name('absensi-pkkmb-kedua.cleanup-orphaned');
    Route::resource('absensi-pkkmb-kedua', AbsensiPkkmbKeduaController::class);

    Route::get('absensi-pkkmb-ketiga/export-excel', [AbsensiPkkmbKetigaController::class, 'exportExcel'])->name('absensi-pkkmb-ketiga.export-excel');
    Route::get('absensi-pkkmb-ketiga/scan', [AbsensiPkkmbKetigaController::class, 'scan'])->name('absensi-pkkmb-ketiga.scan');
    Route::get('absensi-pkkmb-ketiga/scan-proses', [AbsensiPkkmbKetigaController::class, 'scanProses'])->name('absensi-pkkmb-ketiga.scan-proses');
    Route::post('absensi-pkkmb-ketiga/cleanup-orphaned', [AbsensiPkkmbKetigaController::class, 'cleanupOrphaned'])->name('absensi-pkkmb-ketiga.cleanup-orphaned');
    Route::resource('absensi-pkkmb-ketiga', AbsensiPkkmbKetigaController::class);

    Route::get('absensi-esq-pertama/export-excel', [AbsensiEsqPertamaController::class, 'exportExcel'])->name('absensi-esq-pertama.export-excel');
    Route::get('absensi-esq-pertama/scan', [AbsensiEsqPertamaController::class, 'scan'])->name('absensi-esq-pertama.scan');
    Route::get('absensi-esq-pertama/scan-proses', [AbsensiEsqPertamaController::class, 'scanProses'])->name('absensi-esq-pertama.scan-proses');
    Route::resource('absensi-esq-pertama', AbsensiEsqPertamaController::class);

    Route::get('absensi-esq-kedua/export-excel', [AbsensiEsqKeduaController::class, 'exportExcel'])->name('absensi-esq-kedua.export-excel');
    Route::get('absensi-esq-kedua/scan', [AbsensiEsqKeduaController::class, 'scan'])->name('absensi-esq-kedua.scan');
    Route::get('absensi-esq-kedua/scan-proses', [AbsensiEsqKeduaController::class, 'scanProses'])->name('absensi-esq-kedua.scan-proses');
    Route::resource('absensi-esq-kedua', AbsensiEsqKeduaController::class);

    Route::get('absensi-esq-ketiga/export-excel', [AbsensiEsqKetigaController::class, 'exportExcel'])->name('absensi-esq-ketiga.export-excel');
    Route::get('absensi-esq-ketiga/scan', [AbsensiEsqKetigaController::class, 'scan'])->name('absensi-esq-ketiga.scan');
    Route::get('absensi-esq-ketiga/scan-proses', [AbsensiEsqKetigaController::class, 'scanProses'])->name('absensi-esq-ketiga.scan-proses');
    Route::resource('absensi-esq-ketiga', AbsensiEsqKetigaController::class);

    Route::get('absensi-esq-keempat/export-excel', [AbsensiEsqKeempatController::class, 'exportExcel'])->name('absensi-esq-keempat.export-excel');
    Route::get('absensi-esq-keempat/scan', [AbsensiEsqKeempatController::class, 'scan'])->name('absensi-esq-keempat.scan');
    Route::get('absensi-esq-keempat/scan-proses', [AbsensiEsqKeempatController::class, 'scanProses'])->name('absensi-esq-keempat.scan-proses');
    Route::resource('absensi-esq-keempat', AbsensiEsqKeempatController::class);

    Route::get('absensi-milad-pertama/export-excel', [AbsensiMiladPertamaController::class, 'exportExcel'])->name('absensi-milad-pertama.export-excel');
    Route::get('absensi-milad-pertama/scan', [AbsensiMiladPertamaController::class, 'scan'])->name('absensi-milad-pertama.scan');
    Route::get('absensi-milad-pertama/scan-proses', [AbsensiMiladPertamaController::class, 'scanProses'])->name('absensi-milad-pertama.scan-proses');
    Route::resource('absensi-milad-pertama', AbsensiMiladPertamaController::class);

    Route::get('absensi-milad-kedua/export-excel', [AbsensiMiladKeduaController::class, 'exportExcel'])->name('absensi-milad-kedua.export-excel');
    Route::get('absensi-milad-kedua/scan', [AbsensiMiladKeduaController::class, 'scan'])->name('absensi-milad-kedua.scan');
    Route::get('absensi-milad-kedua/scan-proses', [AbsensiMiladKeduaController::class, 'scanProses'])->name('absensi-milad-kedua.scan-proses');
    Route::resource('absensi-milad-kedua', AbsensiMiladKeduaController::class);

    Route::get('absensi-kuliah-umum-pertama/export-excel', [AbsensiKuliahUmumPertamaController::class, 'exportExcel'])->name('absensi-kuliah-umum-pertama.export-excel');
    Route::get('absensi-kuliah-umum-pertama/scan', [AbsensiKuliahUmumPertamaController::class, 'scan'])->name('absensi-kuliah-umum-pertama.scan');
    Route::get('absensi-kuliah-umum-pertama/scan-proses', [AbsensiKuliahUmumPertamaController::class, 'scanProses'])->name('absensi-kuliah-umum-pertama.scan-proses');
    Route::resource('absensi-kuliah-umum-pertama', AbsensiKuliahUmumPertamaController::class);

    // Rekapitulasi Absensi Kepanitiaan (Sudah & Belum Absen)
    Route::get('rekapitulasi-absensi', [RekapitulasiAbsensiController::class, 'index'])->name('rekapitulasi-absensi.index');
    Route::get('rekapitulasi-absensi/data-sudah', [RekapitulasiAbsensiController::class, 'getDataSudah'])->name('rekapitulasi-absensi.data-sudah');
    Route::get('rekapitulasi-absensi/data-belum', [RekapitulasiAbsensiController::class, 'getDataBelum'])->name('rekapitulasi-absensi.data-belum');
    Route::get('rekapitulasi-absensi/stats', [RekapitulasiAbsensiController::class, 'getStats'])->name('rekapitulasi-absensi.stats');

    // Pengaturan Status Sesi Absensi (is_active)
    Route::get('pengaturan-absensi', [AbsensiSettingController::class, 'index'])->name('pengaturan-absensi.index');
    Route::post('pengaturan-absensi/{slug}/toggle', [AbsensiSettingController::class, 'toggle'])->name('pengaturan-absensi.toggle');

    // Universal Presensi Kepanitiaan (Dynamic Attendance Engine)
    Route::get('presensi/scan-proses/{token}', [\App\Http\Controllers\UniversalPresensiController::class, 'scanProses'])->name('presensi.scan-proses');
    Route::get('kepanitiaan/{id}/sesi', [\App\Http\Controllers\UniversalPresensiController::class, 'getSesiByKepanitiaan'])->name('presensi.sesi.list');
    Route::post('presensi/sesi/{id}/toggle', [\App\Http\Controllers\UniversalPresensiController::class, 'toggleStatus'])->name('presensi.sesi.toggle');
    Route::post('kepanitiaan/{id}/sesi', [\App\Http\Controllers\UniversalPresensiController::class, 'storeSesi'])->name('presensi.sesi.store');
    Route::put('presensi/sesi/{id}', [\App\Http\Controllers\UniversalPresensiController::class, 'updateSesi'])->name('presensi.sesi.update');
    Route::delete('presensi/sesi/{id}', [\App\Http\Controllers\UniversalPresensiController::class, 'destroySesi'])->name('presensi.sesi.destroy');
    Route::get('presensi/sesi/{id}/export', [\App\Http\Controllers\UniversalPresensiController::class, 'exportExcel'])->name('presensi.sesi.export');
});