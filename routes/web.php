<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EvaluasiController;
use App\Http\Controllers\EvaluasiPengenalanWawasanIbnuSinaController;
use App\Http\Controllers\RencanaKerjaController;
use App\Http\Controllers\UserController;
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

    Route::get('rencana-kerja/download-template', [RencanaKerjaController::class, 'downloadTemplate'])->name('rencana-kerja.download-template');
    Route::get('rencana-kerja/export-excel', [RencanaKerjaController::class, 'exportExcel'])->name('rencana-kerja.export-excel');
    Route::post('rencana-kerja/import-excel', [RencanaKerjaController::class, 'importExcel'])->name('rencana-kerja.import-excel');
    Route::post('rencana-kerja/{rencana_kerja}/start', [RencanaKerjaController::class, 'start'])->name('rencana-kerja.start');
    Route::post('rencana-kerja/{rencana_kerja}/stop', [RencanaKerjaController::class, 'stop'])->name('rencana-kerja.stop');
    Route::post('rencana-kerja/{rencana_kerja}/upload-attachment', [RencanaKerjaController::class, 'uploadAttachment'])->name('rencana-kerja.upload-attachment');
    Route::resource('rencana-kerja', RencanaKerjaController::class);

    Route::resource('evaluasi', EvaluasiController::class)->except(['edit', 'update']);

    Route::resource('evaluasi-pengenalan-wawasan-ibnu-sina', EvaluasiPengenalanWawasanIbnuSinaController::class)
        ->names('evaluasipengenalanwawasanibnusina');
});