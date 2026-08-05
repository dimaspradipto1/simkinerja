<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EvaluasiController;
use App\Http\Controllers\EvaluasiPengenalanWawasanIbnuSinaController;
use App\Http\Controllers\PeriodeAkademikController;
use App\Http\Controllers\RencanaKerjaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RekapitulasiController;
use App\Http\Controllers\KepanitiaanController;
use App\Http\Controllers\RekapitulasiKepanitiaanController;
use App\Http\Controllers\InsidentilController;
use App\Http\Controllers\RekapitulasiInsidentilController;
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
    Route::put('user/{user}/password', [UserController::class, 'updatePassword'])->name('user.update-password');
    Route::resource('user', UserController::class);
    Route::resource('periode-akademik', PeriodeAkademikController::class);

    Route::get('rencana-kerja/download-template', [RencanaKerjaController::class, 'downloadTemplate'])->name('rencana-kerja.download-template');
    Route::get('rencana-kerja/export-excel', [RencanaKerjaController::class, 'exportExcel'])->name('rencana-kerja.export-excel');
    Route::get('rencana-kerja/export-pdf', [RencanaKerjaController::class, 'exportPdf'])->name('rencana-kerja.export-pdf');
    Route::post('rencana-kerja/import-excel', [RencanaKerjaController::class, 'importExcel'])->name('rencana-kerja.import-excel');
    Route::post('rencana-kerja/{rencana_kerja}/start', [RencanaKerjaController::class, 'start'])->name('rencana-kerja.start');
    Route::post('rencana-kerja/{rencana_kerja}/stop', [RencanaKerjaController::class, 'stop'])->name('rencana-kerja.stop');
    Route::post('rencana-kerja/{rencana_kerja}/upload-attachment', [RencanaKerjaController::class, 'uploadAttachment'])->name('rencana-kerja.upload-attachment');
    Route::post('rencana-kerja/bulk-delete', [RencanaKerjaController::class, 'bulkDelete'])->name('rencana-kerja.bulk-delete');
    Route::post('rencana-kerja/{rencana_kerja}/update-tags', [RencanaKerjaController::class, 'updateTags'])->name('rencana-kerja.update-tags');
    Route::resource('rencana-kerja', RencanaKerjaController::class);

    Route::get('rekapitulasi/data', [RekapitulasiController::class, 'getData'])->name('rekapitulasi.data');
    Route::resource('rekapitulasi', RekapitulasiController::class)->only(['index']);

    // Kepanitiaan Routes
    Route::get('kepanitiaan/download-template', [KepanitiaanController::class, 'downloadTemplate'])->name('kepanitiaan.download-template');
    Route::get('kepanitiaan/export-excel', [KepanitiaanController::class, 'exportExcel'])->name('kepanitiaan.export-excel');
    Route::get('kepanitiaan/export-pdf', [KepanitiaanController::class, 'exportPdf'])->name('kepanitiaan.export-pdf');
    Route::post('kepanitiaan/import-excel', [KepanitiaanController::class, 'importExcel'])->name('kepanitiaan.import-excel');
    Route::post('kepanitiaan/{kepanitiaan}/start', [KepanitiaanController::class, 'start'])->name('kepanitiaan.start');
    Route::post('kepanitiaan/{kepanitiaan}/stop', [KepanitiaanController::class, 'stop'])->name('kepanitiaan.stop');
    Route::post('kepanitiaan/{kepanitiaan}/upload-attachment', [KepanitiaanController::class, 'uploadAttachment'])->name('kepanitiaan.upload-attachment');
    Route::post('kepanitiaan/bulk-delete', [KepanitiaanController::class, 'bulkDelete'])->name('kepanitiaan.bulk-delete');
    Route::post('kepanitiaan/{kepanitiaan}/update-tags', [KepanitiaanController::class, 'updateTags'])->name('kepanitiaan.update-tags');
    Route::resource('kepanitiaan', KepanitiaanController::class);

    // Rekapitulasi Kepanitiaan Routes
    Route::get('rekapitulasi-kepanitiaan/data', [RekapitulasiKepanitiaanController::class, 'getData'])->name('rekapitulasi-kepanitiaan.data');
    Route::resource('rekapitulasi-kepanitiaan', RekapitulasiKepanitiaanController::class)->only(['index']);

    // Insidentil Routes
    Route::get('insidentil/download-template', [InsidentilController::class, 'downloadTemplate'])->name('insidentil.download-template');
    Route::get('insidentil/export-excel', [InsidentilController::class, 'exportExcel'])->name('insidentil.export-excel');
    Route::get('insidentil/export-pdf', [InsidentilController::class, 'exportPdf'])->name('insidentil.export-pdf');
    Route::post('insidentil/import-excel', [InsidentilController::class, 'importExcel'])->name('insidentil.import-excel');
    Route::post('insidentil/{insidentil}/start', [InsidentilController::class, 'start'])->name('insidentil.start');
    Route::post('insidentil/{insidentil}/stop', [InsidentilController::class, 'stop'])->name('insidentil.stop');
    Route::post('insidentil/{insidentil}/upload-attachment', [InsidentilController::class, 'uploadAttachment'])->name('insidentil.upload-attachment');
    Route::post('insidentil/bulk-delete', [InsidentilController::class, 'bulkDelete'])->name('insidentil.bulk-delete');
    Route::post('insidentil/{insidentil}/update-tags', [InsidentilController::class, 'updateTags'])->name('insidentil.update-tags');
    Route::resource('insidentil', InsidentilController::class);

    // Rekapitulasi Insidentil Routes
    Route::get('rekapitulasi-insidentil/data', [RekapitulasiInsidentilController::class, 'getData'])->name('rekapitulasi-insidentil.data');
    Route::resource('rekapitulasi-insidentil', RekapitulasiInsidentilController::class)->only(['index']);

    Route::resource('evaluasi', EvaluasiController::class)->except(['edit', 'update']);



    Route::resource('evaluasi-pengenalan-wawasan-ibnu-sina', EvaluasiPengenalanWawasanIbnuSinaController::class)
        ->names('evaluasipengenalanwawasanibnusina')
        ->parameters([
            'evaluasi-pengenalan-wawasan-ibnu-sina' => 'wawasan'
        ]);
});