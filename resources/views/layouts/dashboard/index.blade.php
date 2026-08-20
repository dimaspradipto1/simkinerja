@extends('layouts.dashboard.template')

@section('content')
<style>
    .welcome-card {
        background: linear-gradient(135deg, #15432D 0%, #2D6A4F 100%);
        border-radius: 12px;
        color: #ffffff;
        position: relative;
        overflow: hidden;
    }
    .welcome-card::after {
        content: "";
        position: absolute;
        right: -30px;
        bottom: -30px;
        width: 180px;
        height: 180px;
        background: rgba(255, 255, 255, 0.07);
        border-radius: 50%;
        pointer-events: none;
    }
    .stat-card {
        border-radius: 10px;
        border: none;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
    }
    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    .stat-icon-green { background: #e8f5e9; color: #2d6a4f; }
    .stat-icon-orange { background: #fff3e0; color: #e65100; }
    .stat-icon-blue { background: #e3f2fd; color: #1565c0; }
    .stat-icon-teal { background: #e0f2f1; color: #00695c; }
    .stat-icon-purple { background: #f3e5f5; color: #7b1fa2; }
    .stat-icon-amber { background: #fff8e1; color: #ff8f00; }
    .btn-equal-width {
        min-width: 185px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-align: center;
    }
    .scan-menu-card {
        background: linear-gradient(135deg, #f4f9f6 0%, #eaf7ef 100%);
        border: 1px solid #d9efe1;
        border-radius: 18px;
        box-shadow: 0 8px 24px rgba(25, 135, 84, 0.08);
    }
    .scan-category-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
    }
    .scan-category-tile {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 190px;
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.32);
        border: 1px solid rgba(25, 135, 84, 0.08);
        text-decoration: none;
        color: #1f2d3d;
        padding: 1rem 0.75rem;
        transition: all 0.2s ease;
        text-align: center;
    }
    .scan-category-tile:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 18px rgba(25, 135, 84, 0.12);
        text-decoration: none;
        color: #1f2d3d;
    }
    .scan-category-icon {
        width: 72px;
        height: 72px;
        border-radius: 18px;
        background: linear-gradient(135deg, #198754 0%, #9ec4b1 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        margin-bottom: 0.9rem;
    }
    .scan-category-name {
        font-size: 1.1rem;
        font-weight: 800;
        margin: 0;
    }
    .scan-category-count {
        font-size: 1rem;
        color: #4b5a63;
        margin: 0.35rem 0 0;
    }
    .scan-accordion {
        margin-top: 1.5rem;
    }
    .scan-accordion-item {
        border: 1px solid rgba(25, 135, 84, 0.15);
        border-radius: 14px;
        overflow: hidden;
        background: rgba(255,255,255,0.7);
        margin-bottom: 0.75rem;
    }
    .scan-accordion-header {
        background: #4d74ff;
        color: white;
        padding: 1rem 1.1rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-weight: 700;
        font-size: 1.05rem;
    }
    .scan-accordion-header .left {
        display: flex;
        align-items: center;
        gap: 0.8rem;
    }
    .scan-accordion-header .check {
        width: 22px;
        height: 22px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        background: rgba(255,255,255,0.18);
        border: 1px solid rgba(255,255,255,0.4);
        font-size: 0.8rem;
    }
    .scan-accordion-body {
        background: #fff;
        padding: 0.8rem 0.8rem 0.35rem;
    }
    .scan-session-link {
        display: flex;
        align-items: center;
        padding: 0.7rem 0.5rem;
        color: #1f2d3d;
        text-decoration: none;
        border-radius: 8px;
        font-size: 1.05rem;
        transition: background 0.2s ease;
    }
    .scan-session-link:hover {
        background: #f4f8f5;
        color: #198754;
        text-decoration: none;
    }
    .scan-session-link .dot {
        width: 12px;
        height: 12px;
        border: 2px solid #7d8796;
        border-radius: 50%;
        display: inline-block;
        margin-right: 0.85rem;
    }
    .scan-session-link.active .dot {
        background: #4d74ff;
        border-color: #4d74ff;
    }
    .scan-session-link.active {
        color: #1f2d3d;
        font-weight: 600;
    }
    @media (max-width: 991.98px) {
        .scan-category-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 575.98px) {
        .btn-equal-width {
            width: 100%;
        }
        .scan-category-grid {
            grid-template-columns: 1fr;
        }
        .scan-category-tile {
            min-height: 150px;
        }
    }

    /* EdLink Style Weekly Calendar Styling */
    .edlink-card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        border: 1px solid #e9ecef;
    }
    .edlink-header {
        padding: 1.25rem 1.5rem 0.75rem 1.5rem;
    }
    .edlink-calendar-strip {
        padding: 0.5rem 1rem 1rem 1rem;
        border-bottom: 1px solid #f0f2f5;
        position: relative;
    }
    .edlink-day-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-decoration: none;
        padding: 6px 12px;
        border-radius: 12px;
        transition: all 0.2s ease;
        color: #495057;
        position: relative;
        cursor: pointer;
        user-select: none;
    }
    .edlink-day-btn:hover {
        background-color: #f8f9fa;
        color: #198754;
    }
    .edlink-day-btn.active {
        color: #198754;
        font-weight: 700;
    }
    .edlink-day-name {
        font-size: 0.85rem;
        font-weight: 500;
        margin-bottom: 4px;
    }
    .edlink-day-badge {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
        font-weight: 600;
        transition: all 0.2s ease;
    }
    .edlink-day-btn.active .edlink-day-badge {
        background-color: #198754;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(25, 135, 84, 0.35);
    }
    .edlink-active-arrow {
        position: absolute;
        bottom: -16px;
        width: 0;
        height: 0;
        border-left: 7px solid transparent;
        border-right: 7px solid transparent;
        border-bottom: 7px solid #f0f2f5;
        display: none;
    }
    .edlink-day-btn.active .edlink-active-arrow {
        display: block;
    }
    .edlink-empty-state {
        padding: 3rem 1.5rem;
        text-align: center;
    }
    .edlink-empty-icon {
        width: 90px;
        height: 90px;
        margin: 0 auto 1.25rem auto;
        opacity: 0.85;
    }

    /* Attendance Realtime DataTables Styling */
    .day-pill-btn {
        border-radius: 8px;
        font-weight: 700;
        padding: 6px 18px;
        font-size: 0.95rem;
        border: none;
        background: transparent;
        color: #0d6efd;
        transition: all 0.2s ease;
        text-decoration: none;
    }
    .day-pill-btn.active {
        background: #0d6efd !important;
        color: #ffffff !important;
        box-shadow: 0 2px 8px rgba(13, 110, 253, 0.35);
    }
    .day-pill-btn:hover:not(.active) {
        background: #eff6ff;
        color: #0d6efd;
    }
    #dashboard-attendance-table th {
        font-weight: 800;
        color: #1e3a8a;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e2e8f0;
    }
    #dashboard-attendance-table td {
        padding: 0.75rem 0.5rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .dataTables_paginate .pagination {
        margin-bottom: 0;
    }
</style>

<div class="pagetitle mb-4">
    <h1 class="fw-bold text-dark fs-3">Dashboard Kinerja</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Dashboard</li>
        </ol>
    </nav>
</div>

<!-- Welcome Banner -->
<div class="card welcome-card mb-4 p-4 shadow-sm">
    <div class="row align-items-center">
        <div class="col-lg-8 mb-3 mb-lg-0">
            <h2 class="fw-bold fs-4 mb-2">Selamat Datang, {{ auth()->user()->name }}! 👋</h2>
            <p class="mb-2 text-white-50" style="font-size: 0.95rem;">
                <strong>Jabatan:</strong> {{ auth()->user()->jabatan ?? 'Pegawai' }} &nbsp;&bull;&nbsp; 
                <strong>Unit:</strong> {{ auth()->user()->unit ?? 'Universitas Ibnu Sina' }} &nbsp;&bull;&nbsp; 
                <strong>Role:</strong> <span class="badge bg-light text-dark fw-bold ms-1">{{ auth()->user()->roles }}</span>
            </p>
            <p class="mb-0 small text-white-50">Kelola dan pantau seluruh rencana kerja utama, kepanitiaan, insidentil, serta timer kegiatan Anda secara efisien.</p>
        </div>
        <div class="col-lg-4 text-lg-end d-flex flex-column align-items-lg-end gap-2 mt-3 mt-lg-0">
            <button type="button" id="btn-voice-dashboard" class="btn btn-warning fw-bold text-dark px-3 py-2 shadow-sm btn-equal-width">
                <i class="bi bi-volume-up-fill me-1 fs-6"></i> Dengarkan Suara
            </button>
            <a href="{{ route('rencana-kerja.index') }}" class="btn btn-light fw-bold text-success px-3 py-2 shadow-sm btn-equal-width">
                <i class="bi bi-list-check me-1"></i> Rencana Kerja
            </a>
            @if(auth()->check() && (!auth()->user()->isPimpinanUnit() && !auth()->user()->isAdmin()))
                <a href="{{ route('rencana-kerja.create') }}" class="btn btn-outline-light fw-bold px-3 py-2 btn-equal-width">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Tugas
                </a>
            @endif
        </div>
    </div>
</div>

@unless(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()))
<!-- Scan Absensi Barcode Card -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-4">
        <div class="row align-items-center g-3">
            <div class="col-md-8">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fs-4" style="width: 48px; height: 48px; min-width: 48px; background-color: #198754;">
                        <i class="bi bi-qr-code-scan"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold m-0 fs-5 mb-1 text-dark">Scan Absensi Kehadiran</h5>
                        <p class="m-0 text-muted small">Scan QR Code untuk mencatat kehadiran Anda pada kegiatan yang sedang berlangsung.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="{{ route('scan-absensi') }}?start=1" class="btn btn-success btn-lg text-nowrap btn-equal-width">
                    <i class="bi bi-camera-fill me-1"></i> Scan Sekarang
                </a>
            </div>
        </div>
    </div>
</div>
@endunless

@if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()))
<div class="card scan-menu-card mb-4 border-0">
    <div class="card-body p-4">
        <div class="d-flex align-items-center gap-3 mb-4">
            <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 52px; height: 52px; background: linear-gradient(135deg, #198754 0%, #a3cfbb 100%);">
                <i class="bi bi-qr-code-scan fs-4"></i>
            </div>
            <div>
                <h5 class="fw-bold m-0 text-dark">Menu Scan Barcode</h5>
                <p class="m-0 text-muted small">Navigasi cepat ke semua sesi absensi.</p>
            </div>
        </div>

        <div class="scan-accordion">
            <div class="scan-accordion-item">
                <div class="scan-accordion-header" data-bs-toggle="collapse" data-bs-target="#pkkmbSessions" aria-expanded="true">
                    <div class="left">
                        <span class="check"><i class="bi bi-check"></i></span>
                        <span>Kepanitiaan PKKMB</span>
                    </div>
                    <i class="bi bi-chevron-up"></i>
                </div>
                <div id="pkkmbSessions" class="collapse show scan-accordion-body">
                    <a href="{{ route('absensi-pkkmb-pertama.index') }}?show_qr=1" class="scan-session-link active"><span class="dot"></span> Absensi Hari Pertama</a>
                    <a href="{{ route('absensi-pkkmb-kedua.index') }}?show_qr=1" class="scan-session-link"><span class="dot"></span> Absensi Hari Kedua</a>
                    <a href="{{ route('absensi-pkkmb-ketiga.index') }}?show_qr=1" class="scan-session-link"><span class="dot"></span> Absensi Hari Ketiga</a>
                </div>
            </div>

            <div class="scan-accordion-item">
                <div class="scan-accordion-header" data-bs-toggle="collapse" data-bs-target="#esqSessions" aria-expanded="false">
                    <div class="left">
                        <span class="check"><i class="bi bi-check"></i></span>
                        <span>Kepanitiaan ESQ</span>
                    </div>
                    <i class="bi bi-chevron-down"></i>
                </div>
                <div id="esqSessions" class="collapse scan-accordion-body">
                    <a href="{{ route('absensi-esq-pertama.index') }}?show_qr=1" class="scan-session-link active"><span class="dot"></span> Absensi Hari Pertama</a>
                    <a href="{{ route('absensi-esq-kedua.index') }}?show_qr=1" class="scan-session-link"><span class="dot"></span> Absensi Hari Kedua</a>
                    <a href="{{ route('absensi-esq-ketiga.index') }}?show_qr=1" class="scan-session-link"><span class="dot"></span> Absensi Hari Ketiga</a>
                </div>
            </div>

            <div class="scan-accordion-item">
                <div class="scan-accordion-header" data-bs-toggle="collapse" data-bs-target="#miladSessions" aria-expanded="false">
                    <div class="left">
                        <span class="check"><i class="bi bi-check"></i></span>
                        <span>Kepanitiaan MILAD</span>
                    </div>
                    <i class="bi bi-chevron-down"></i>
                </div>
                <div id="miladSessions" class="collapse scan-accordion-body">
                    <a href="{{ route('absensi-milad-pertama.index') }}?show_qr=1" class="scan-session-link active"><span class="dot"></span> Absensi Hari Pertama</a>
                    <a href="{{ route('absensi-milad-kedua.index') }}?show_qr=1" class="scan-session-link"><span class="dot"></span> Absensi Hari Kedua</a>
                </div>
            </div>

            <div class="scan-accordion-item">
                <div class="scan-accordion-header" data-bs-toggle="collapse" data-bs-target="#kuliahUmumSessions" aria-expanded="false">
                    <div class="left">
                        <span class="check"><i class="bi bi-check"></i></span>
                        <span>Kepanitiaan Kuliah Umum</span>
                    </div>
                    <i class="bi bi-chevron-down"></i>
                </div>
                <div id="kuliahUmumSessions" class="collapse scan-accordion-body">
                    <a href="{{ route('absensi-kuliah-umum-pertama.index') }}?show_qr=1" class="scan-session-link active"><span class="dot"></span> Absensi</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- ============================================================== -->
<!-- DataTables Monitoring Presensi Real-Time (Datang & Pulang)     -->
<!-- ============================================================== -->
<div class="card shadow-sm border-0 mb-4 attendance-monitor-card" id="section-monitoring-absensi">
    <div class="card-body p-4">
        
        <!-- Header Subtitle -->
        <p class="text-muted small mb-3">Monitoring kehadiran pegawai real-time.</p>

        <!-- Manage Dropdown & Kepanitiaan Tabs -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <!-- Manage Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-outline-primary dropdown-toggle rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.9rem;">
                        <i class="bi bi-box-arrow-up-right"></i> Manage
                    </button>
                    <ul class="dropdown-menu shadow-sm border-0 rounded-3">
                        <li><h6 class="dropdown-header text-uppercase small fw-bold text-muted">Manajemen Presensi</h6></li>
                        <li><a class="dropdown-item py-2" href="{{ route('absensi-pkkmb-pertama.index') }}"><i class="bi bi-mortarboard me-2 text-success"></i>Absensi PKKMB</a></li>
                        <li><a class="dropdown-item py-2" href="{{ route('absensi-esq-pertama.index') }}"><i class="bi bi-heart-pulse me-2 text-primary"></i>Absensi ESQ</a></li>
                        <li><a class="dropdown-item py-2" href="{{ route('absensi-milad-pertama.index') }}"><i class="bi bi-stars me-2 text-warning"></i>Absensi MILAD</a></li>
                        <li><a class="dropdown-item py-2" href="{{ route('absensi-kuliah-umum-pertama.index') }}"><i class="bi bi-book me-2 text-info"></i>Absensi Kuliah Umum</a></li>
                    </ul>
                </div>

                <!-- Kepanitiaan Switcher Tabs -->
                <ul class="nav nav-pills gap-1 p-1 bg-light rounded-pill" id="attendanceKepanitiaanTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link rounded-pill py-1 px-3 fw-bold active btn-kepanitiaan-tab" data-kepanitiaan="pkkmb" type="button">PKKMB</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link rounded-pill py-1 px-3 fw-semibold text-secondary btn-kepanitiaan-tab" data-kepanitiaan="esq" type="button">ESQ</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link rounded-pill py-1 px-3 fw-semibold text-secondary btn-kepanitiaan-tab" data-kepanitiaan="milad" type="button">MILAD</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link rounded-pill py-1 px-3 fw-semibold text-secondary btn-kepanitiaan-tab" data-kepanitiaan="kuliah_umum" type="button">Kuliah Umum</button>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Pill Search Bar -->
        <div class="position-relative mb-3">
            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
            <input type="text" id="attendance-custom-search" class="form-control rounded-pill ps-5 py-2 shadow-none" placeholder="Cari..." style="background-color: #f8fafc; border: 1px solid #e2e8f0; font-size: 0.95rem;">
        </div>

        <!-- Day Selector Buttons (H-1, H-2, H-3) -->
        <div class="d-flex align-items-center gap-2 mb-3" id="attendance-day-selector">
            <button type="button" class="day-pill-btn active" data-day="1">H-1</button>
            <button type="button" class="day-pill-btn" data-day="2">H-2</button>
            <button type="button" class="day-pill-btn" data-day="3">H-3</button>
        </div>

        <!-- DataTables Container -->
        <div class="table-responsive border-0">
            <table class="table table-hover align-middle mb-0 w-100" id="dashboard-attendance-table">
                <thead>
                    <tr style="border-bottom: 2px solid #e2e8f0;">
                        <th width="8%" class="ps-4 text-uppercase fw-bold text-dark" style="font-size: 0.85rem; letter-spacing: 0.5px;">NO</th>
                        <th width="62%" class="text-uppercase fw-bold text-dark" style="font-size: 0.85rem; letter-spacing: 0.5px;">NAMA</th>
                        <th width="15%" class="text-center text-uppercase fw-bold text-dark" style="font-size: 0.85rem; letter-spacing: 0.5px;">DATANG</th>
                        <th width="15%" class="text-center pe-4 text-uppercase fw-bold text-dark" style="font-size: 0.85rem; letter-spacing: 0.5px;">PULANG</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Loaded via AJAX DataTables -->
                </tbody>
            </table>
        </div>

        <!-- Legend at the Bottom -->
        <div class="d-flex flex-wrap align-items-center justify-content-center gap-2 pt-4 mt-2 text-center" style="font-size: 0.85rem;">
            <span class="fw-bold text-secondary text-uppercase me-2" style="letter-spacing: 0.5px;">KETERANGAN:</span>
            <span class="badge px-3 py-2 fw-bold" style="background-color: #15803d; color: #ffffff; border-radius: 6px; font-size: 0.8rem;">H: Hadir</span>
            <span class="badge px-3 py-2 fw-bold" style="background-color: #eab308; color: #ffffff; border-radius: 6px; font-size: 0.8rem;">I: Izin</span>
            <span class="badge px-3 py-2 fw-bold" style="background-color: #06b6d4; color: #ffffff; border-radius: 6px; font-size: 0.8rem;">S: Sakit</span>
            <span class="badge px-3 py-2 fw-bold" style="background-color: #dc2626; color: #ffffff; border-radius: 6px; font-size: 0.8rem;">A: Alpa</span>
        </div>

    </div>
</div>

<!-- Main Content Grid -->
<div class="row g-3">
    <!-- Left Column: Recent Activity Tables -->
    <div class="col-lg-8">
        
        <!-- Table 1: Rencana Kerja Utama Terbaru -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <h5 class="card-title m-0 fw-bold fs-6 text-dark">
                        <i class="bi bi-journal-check text-success me-2"></i>Rencana Kerja Utama
                    </h5>
                    <div class="d-flex align-items-center gap-2">
                        <form action="{{ route('dashboard') }}" method="GET" class="d-flex align-items-center">
                            @if(request('page_kepanitiaan')) <input type="hidden" name="page_kepanitiaan" value="{{ request('page_kepanitiaan') }}"> @endif
                            @if(request('page_insidentil')) <input type="hidden" name="page_insidentil" value="{{ request('page_insidentil') }}"> @endif
                            <div class="input-group input-group-sm" style="max-width: 220px;">
                                <input type="text" name="search_utama" class="form-control" placeholder="Cari..." value="{{ request('search_utama') }}">
                                <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                            </div>
                        </form>
                        <a href="{{ route('rencana-kerja.index') }}" class="btn btn-sm btn-outline-success fw-semibold text-nowrap">
                            Lihat Semua <i class="bi bi-arrow-right me-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="4%" class="ps-3">No</th>
                                <th width="30%">Uraian Tugas</th>
                                <th width="26%">Pegawai / Jabatan</th>
                                <th width="22%">Tanggal (Estimasi & Realisasi)</th>
                                <th width="18%" class="pe-3 text-center">Status & Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTasks as $index => $task)
                                <tr>
                                    <td class="ps-3 text-muted fw-semibold">{{ $recentTasks->firstItem() + $index }}</td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $task->uraian_tugas }}</div>
                                        @if($task->file || $task->url_external)
                                            <div class="small text-muted mt-1">
                                                @if($task->file)
                                                    <span class="badge bg-light text-secondary border me-1"><i class="bi bi-paperclip me-1"></i>Berkas</span>
                                                @endif
                                                @if($task->url_external)
                                                    <span class="badge bg-light text-primary border"><i class="bi bi-link-45deg me-1"></i>Link</span>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($task->user && $task->user->jabatan)
                                            <div class="text-dark fw-bold small">
                                                <i class="bi bi-person-fill text-success me-1"></i>{{ $task->user->name ?? '-' }}
                                            </div>
                                            <div class="small mt-1">
                                                <span class="badge bg-light text-dark border fw-semibold me-1"><i class="bi bi-briefcase-fill text-secondary me-1"></i>{{ $task->user->jabatan ?? '-' }}</span>
                                            </div>
                                        @else
                                            <div class="fw-bold text-secondary small">{{ $task->user->name ?? '-' }}</div>
                                        @endif
                                        @if($task->taggedUsers && $task->taggedUsers->count() > 0)
                                            <div class="small text-muted mt-1" style="font-size: 0.75rem;">
                                                <strong>Rekan:</strong> {{ $task->taggedUsers->pluck('name')->implode(', ') }}
                                            </div>
                                        @endif
                                    </td>

                                    <td>
                                        <div class="small">
                                            <div class="text-secondary">
                                                <i class="bi bi-calendar-event me-1 text-secondary"></i>
                                                {{ $task->estimasi_tanggal_mulai ? date('d/m/Y', strtotime($task->estimasi_tanggal_mulai)) : '-' }} 
                                                s/d 
                                                {{ $task->estimasi_tanggal_selesai ? date('d/m/Y', strtotime($task->estimasi_tanggal_selesai)) : '-' }}
                                            </div>
                                            @if($task->tanggal_mulai)
                                                <div class="text-success mt-1">
                                                    <i class="bi bi-play-circle me-1"></i>Mulai: {{ date('d/m/Y', strtotime($task->tanggal_mulai)) }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="pe-3 text-center">
                                        @if($task->status == 'Selesai')
                                            <span class="badge bg-success px-3 py-2 fw-semibold text-nowrap"><i class="bi bi-check-circle-fill me-1"></i>Selesai</span>
                                        @elseif($task->status == 'Proses' || $task->status == 'Berjalan')
                                            <button type="button" class="btn btn-sm btn-danger px-3 py-1 fw-bold shadow-sm btn-action-dash text-nowrap" data-type="rencana-kerja" data-action="stop" data-id="{{ $task->id }}">
                                                <i class="bi bi-stop-fill me-1"></i>Selesaikan
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-sm btn-success px-3 py-1 fw-bold shadow-sm btn-action-dash text-nowrap" data-type="rencana-kerja" data-action="start" data-id="{{ $task->id }}">
                                                <i class="bi bi-play-fill me-1"></i>Mulai
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                                        Belum ada data Rencana Kerja Utama.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($recentTasks->hasPages() || $recentTasks->total() > 0)
                <div class="card-footer bg-white border-top py-2 d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div class="small text-muted">
                        Menampilkan <strong>{{ $recentTasks->firstItem() ?? 0 }}</strong> s/d <strong>{{ $recentTasks->lastItem() ?? 0 }}</strong> dari <strong>{{ $recentTasks->total() }}</strong> data
                    </div>
                    <div>
                        {{ $recentTasks->links() }}
                    </div>
                </div>
            @endif
        </div>

        <!-- Table 2: Kepanitiaan Terbaru -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <h5 class="card-title m-0 fw-bold fs-6 text-dark">
                        <i class="bi bi-people-fill text-primary me-2"></i>Tugas Kepanitiaan
                    </h5>
                    <div class="d-flex align-items-center gap-2">
                        <form action="{{ route('dashboard') }}" method="GET" class="d-flex align-items-center">
                            @if(request('page_utama')) <input type="hidden" name="page_utama" value="{{ request('page_utama') }}"> @endif
                            @if(request('page_insidentil')) <input type="hidden" name="page_insidentil" value="{{ request('page_insidentil') }}"> @endif
                            <div class="input-group input-group-sm" style="max-width: 220px;">
                                <input type="text" name="search_kepanitiaan" class="form-control" placeholder="Cari..." value="{{ request('search_kepanitiaan') }}">
                                <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                            </div>
                        </form>
                        <a href="{{ route('kepanitiaan.index') }}" class="btn btn-sm btn-outline-primary fw-semibold text-nowrap">
                            Lihat Semua <i class="bi bi-arrow-right me-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="4%" class="ps-3">No</th>
                                <th width="30%">Uraian Tugas Kepanitiaan</th>
                                <th width="26%">Pembuat & Rekan Kerja</th>
                                <th width="22%">Tanggal (Estimasi & Realisasi)</th>
                                <th width="18%" class="pe-3 text-center">Status & Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentKepanitiaan as $index => $kp)
                                <tr>
                                    <td class="ps-3 text-muted fw-semibold">{{ $recentKepanitiaan->firstItem() + $index }}</td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $kp->uraian_tugas }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark small"><i class="bi bi-person-circle me-1 text-primary"></i>{{ $kp->user ? $kp->user->name : '-' }}</div>
                                        @if($kp->taggedUsers->count() > 0)
                                            <div class="small text-muted mt-1" style="font-size: 0.75rem;">
                                                <strong>Rekan:</strong> {{ $kp->taggedUsers->pluck('name')->implode(', ') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="small">
                                            <div class="text-secondary">
                                                <i class="bi bi-calendar-event me-1 text-secondary"></i>
                                                {{ $kp->estimasi_tanggal_mulai ? date('d/m/Y', strtotime($kp->estimasi_tanggal_mulai)) : '-' }}
                                                s/d
                                                {{ $kp->estimasi_tanggal_selesai ? date('d/m/Y', strtotime($kp->estimasi_tanggal_selesai)) : '-' }}
                                            </div>
                                            @if($kp->tanggal_mulai)
                                                <div class="text-success mt-1">
                                                    <i class="bi bi-play-circle me-1"></i>Mulai: {{ date('d/m/Y', strtotime($kp->tanggal_mulai)) }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="pe-3 text-center">
                                        @if($kp->status == 'Selesai')
                                            <span class="badge bg-success px-3 py-2 fw-semibold text-nowrap"><i class="bi bi-check-circle-fill me-1"></i>Selesai</span>
                                        @elseif($kp->status == 'Proses' || $kp->status == 'Berjalan')
                                            <button type="button" class="btn btn-sm btn-danger px-3 py-1 fw-bold shadow-sm btn-action-dash text-nowrap" data-type="kepanitiaan" data-action="stop" data-id="{{ $kp->id }}">
                                                <i class="bi bi-stop-fill me-1"></i>Selesaikan
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-sm btn-success px-3 py-1 fw-bold shadow-sm btn-action-dash text-nowrap" data-type="kepanitiaan" data-action="start" data-id="{{ $kp->id }}">
                                                <i class="bi bi-play-fill me-1"></i>Mulai
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                                        Belum ada data Tugas Kepanitiaan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($recentKepanitiaan->hasPages() || $recentKepanitiaan->total() > 0)
                <div class="card-footer bg-white border-top py-2 d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div class="small text-muted">
                        Menampilkan <strong>{{ $recentKepanitiaan->firstItem() ?? 0 }}</strong> s/d <strong>{{ $recentKepanitiaan->lastItem() ?? 0 }}</strong> dari <strong>{{ $recentKepanitiaan->total() }}</strong> data
                    </div>
                    <div>
                        {{ $recentKepanitiaan->links() }}
                    </div>
                </div>
            @endif
        </div>

        <!-- Table 3: Insidentil Terbaru -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <h5 class="card-title m-0 fw-bold fs-6 text-dark">
                        <i class="bi bi-lightning-charge text-warning me-2"></i>Tugas Insidentil
                    </h5>
                    <div class="d-flex align-items-center gap-2">
                        <form action="{{ route('dashboard') }}" method="GET" class="d-flex align-items-center">
                            @if(request('page_utama')) <input type="hidden" name="page_utama" value="{{ request('page_utama') }}"> @endif
                            @if(request('page_kepanitiaan')) <input type="hidden" name="page_kepanitiaan" value="{{ request('page_kepanitiaan') }}"> @endif
                            <div class="input-group input-group-sm" style="max-width: 220px;">
                                <input type="text" name="search_insidentil" class="form-control" placeholder="Cari..." value="{{ request('search_insidentil') }}">
                                <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                            </div>
                        </form>
                        <a href="{{ route('insidentil.index') }}" class="btn btn-sm btn-outline-warning text-dark fw-semibold text-nowrap">
                            Lihat Semua <i class="bi bi-arrow-right me-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="4%" class="ps-3">No</th>
                                <th width="30%">Uraian Tugas Insidentil</th>
                                <th width="26%">Pembuat & Rekan Kerja</th>
                                <th width="22%">Tanggal (Estimasi & Realisasi)</th>
                                <th width="18%" class="pe-3 text-center">Status & Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentInsidentil as $index => $ins)
                                <tr>
                                    <td class="ps-3 text-muted fw-semibold">{{ $recentInsidentil->firstItem() + $index }}</td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $ins->uraian_tugas }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark small"><i class="bi bi-person-circle me-1 text-warning"></i>{{ $ins->user ? $ins->user->name : '-' }}</div>
                                        @if($ins->taggedUsers->count() > 0)
                                            <div class="small text-muted mt-1" style="font-size: 0.75rem;">
                                                <strong>Rekan:</strong> {{ $ins->taggedUsers->pluck('name')->implode(', ') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="small">
                                            <div class="text-secondary">
                                                <i class="bi bi-calendar-event me-1 text-secondary"></i>
                                                {{ $ins->estimasi_tanggal_mulai ? date('d/m/Y', strtotime($ins->estimasi_tanggal_mulai)) : '-' }}
                                                s/d
                                                {{ $ins->estimasi_tanggal_selesai ? date('d/m/Y', strtotime($ins->estimasi_tanggal_selesai)) : '-' }}
                                            </div>
                                            @if($ins->tanggal_mulai)
                                                <div class="text-success mt-1">
                                                    <i class="bi bi-play-circle me-1"></i>Mulai: {{ date('d/m/Y', strtotime($ins->tanggal_mulai)) }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="pe-3 text-center">
                                        @if($ins->status == 'Selesai')
                                            <span class="badge bg-success px-3 py-2 fw-semibold text-nowrap"><i class="bi bi-check-circle-fill me-1"></i>Selesai</span>
                                        @elseif($ins->status == 'Proses' || $ins->status == 'Berjalan')
                                            <button type="button" class="btn btn-sm btn-danger px-3 py-1 fw-bold shadow-sm btn-action-dash text-nowrap" data-type="insidentil" data-action="stop" data-id="{{ $ins->id }}">
                                                <i class="bi bi-stop-fill me-1"></i>Selesaikan
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-sm btn-success px-3 py-1 fw-bold shadow-sm btn-action-dash text-nowrap" data-type="insidentil" data-action="start" data-id="{{ $ins->id }}">
                                                <i class="bi bi-play-fill me-1"></i>Mulai
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                                        Belum ada data Tugas Insidentil.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($recentInsidentil->hasPages() || $recentInsidentil->total() > 0)
                <div class="card-footer bg-white border-top py-2 d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div class="small text-muted">
                        Menampilkan <strong>{{ $recentInsidentil->firstItem() ?? 0 }}</strong> s/d <strong>{{ $recentInsidentil->lastItem() ?? 0 }}</strong> dari <strong>{{ $recentInsidentil->total() }}</strong> data
                    </div>
                    <div>
                        {{ $recentInsidentil->links() }}
                    </div>
                </div>
            @endif
        </div>

    </div>

    <!-- Right Column: Monitoring & Quick Navigation -->
    <div class="col-lg-4">
        <!-- Monitoring Status Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="card-title m-0 fw-bold fs-6 text-dark">
                    <i class="bi bi-shield-check text-primary me-2"></i>Status Kinerja Sistem
                </h5>
            </div>
            <div class="card-body p-3">
                <div class="p-3 rounded mb-3" style="background-color: #f8fafc; border: 1px solid #e2e8f0;">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-secondary small fw-semibold">Unit Kerja Logged-in</span>
                        <span class="badge bg-success">{{ auth()->user()->unit ?? 'Sistem' }}</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-secondary small fw-semibold">
                            @if(auth()->user() && auth()->user()->isAdmin()) Total Pegawai Sistem @else Total Pegawai Unit @endif
                        </span>
                        <span class="fw-bold text-dark">{{ $totalPegawai }} Pegawai</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="text-secondary small fw-semibold">Tanggal Hari Ini</span>
                        <span class="fw-semibold text-muted small">{{ date('d M Y') }}</span>
                    </div>
                </div>

                <div class="p-3 rounded" style="background-color: #f0fdf4; border: 1px solid #bbf7d0;">
                    <h6 class="fw-bold text-success mb-2" style="font-size: 0.9rem;">
                        <i class="bi bi-lightbulb-fill me-1"></i> Petunjuk Pengisian
                    </h6>
                    <ul class="ps-3 mb-0 text-dark small" style="line-height: 1.6;">
                        <li>Klik <strong>Mulai</strong> saat Anda memulai pengerjaan tugas.</li>
                        <li>Klik <strong>Selesai</strong> setelah pekerjaan rampung.</li>
                        <li>Sertakan berkas atau link Drive jika diperlukan.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Handle Mulai & Selesai button click on Dashboard
        $(document).on('click', '.btn-action-dash', function() {
            let btn = $(this);
            let type = btn.data('type');
            let action = btn.data('action');
            let id = btn.data('id');
            let actionText = action === 'start' ? 'memulai' : 'menyelesaikan';

            btn.prop('disabled', true);

            $.ajax({
                url: "/" + type + "/" + id + "/" + action,
                type: "POST",
                data: { _token: "{{ csrf_token() }}" },
                success: function(res) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        location.reload();
                    }
                },
                error: function(err) {
                    btn.prop('disabled', false);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Gagal', err.responseJSON ? err.responseJSON.message : 'Terjadi kesalahan.', 'error');
                    } else {
                        alert('Terjadi kesalahan saat ' + actionText + ' tugas.');
                    }
                }
            });
        });

        // -------------------------------------------------------------
        // Real-time Kepanitiaan Attendance DataTables Initialization
        // -------------------------------------------------------------
        let currentKepanitiaan = 'pkkmb';
        let currentDay = 1;

        let attendanceDataTable = $('#dashboard-attendance-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('dashboard.attendance-data') }}",
                data: function (d) {
                    d.kepanitiaan = currentKepanitiaan;
                    d.day = currentDay;
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-muted fw-semibold ps-4' },
                { data: 'name', name: 'name', className: 'text-dark' },
                { data: 'datang', name: 'datang', orderable: false, searchable: false, className: 'text-center' },
                { data: 'pulang', name: 'pulang', orderable: false, searchable: false, className: 'text-center pe-4' },
            ],
            dom: 't<"d-flex flex-wrap align-items-center justify-content-between p-3 border-top gap-2"ip>',
            pageLength: 10,
            language: {
                processing: '<div class="spinner-border spinner-border-sm text-primary me-2"></div> Memuat data presensi...',
                zeroRecords: "Data tidak ditemukan",
                info: "Menampilkan <strong>_START_-_END_</strong> dari <strong>_TOTAL_</strong> data",
                infoEmpty: "Menampilkan 0 data",
                infoFiltered: "(disaring dari _MAX_ data)",
                paginate: {
                    previous: "<",
                    next: ">"
                }
            }
        });

        // Bind Custom Search Box
        $('#attendance-custom-search').on('keyup', function () {
            attendanceDataTable.search(this.value).draw();
        });

        // Day Selector Pill Click
        $(document).on('click', '.day-pill-btn', function () {
            $('.day-pill-btn').removeClass('active');
            $(this).addClass('active');
            currentDay = $(this).data('day');
            attendanceDataTable.ajax.reload();
        });

        // Kepanitiaan Tabs Switcher
        $('.btn-kepanitiaan-tab').on('click', function () {
            $('.btn-kepanitiaan-tab').removeClass('active fw-bold text-white bg-primary').addClass('text-secondary fw-semibold');
            $(this).removeClass('text-secondary fw-semibold').addClass('active fw-bold text-white bg-primary');
            currentKepanitiaan = $(this).data('kepanitiaan');
            currentDay = 1;

            // Update Day Pills based on kepanitiaan
            let daySelector = $('#attendance-day-selector');
            daySelector.empty();
            let totalDays = (currentKepanitiaan === 'milad') ? 2 : (currentKepanitiaan === 'kuliah_umum' ? 1 : 3);

            for (let d = 1; d <= totalDays; d++) {
                let label = (currentKepanitiaan === 'kuliah_umum') ? 'Sesi ' + d : 'H-' + d;
                let activeClass = (d === 1) ? 'active' : '';
                daySelector.append('<button type="button" class="day-pill-btn ' + activeClass + '" data-day="' + d + '">' + label + '</button>');
            }

            attendanceDataTable.ajax.reload();
        });

        $('#btn-voice-dashboard').on('click', function() {
            let voiceText = "Halo {{ auth()->user()->name }}. Berikut ringkasan capaian kinerja untuk unit {{ auth()->user()->unit ?? 'Universitas Ibnu Sina' }}. Total rencana kerja utama terdaftar sebanyak {{ $totalTugas }} tugas dengan tingkat capaian {{ $persentaseSelesai }} persen. Tugas kepanitiaan terdaftar sebanyak {{ $totalKepanitiaan }} tugas, dan tugas insidentil terdaftar sebanyak {{ $totalInsidentil }} tugas. ";
            
            toggleSpeech(voiceText, 'btn-voice-dashboard', 'Dengarkan Suara');
        });

        function toggleSpeech(text, btnId, defaultText) {
            if (!('speechSynthesis' in window)) {
                alert('Peramban Anda belum mendukung fitur narasi suara.');
                return;
            }

            const btn = $('#' + btnId);

            if (window.speechSynthesis.speaking && !window.speechSynthesis.paused) {
                window.speechSynthesis.cancel();
                btn.removeClass('btn-danger').addClass('btn-warning').html('<i class="bi bi-volume-up-fill me-1 fs-6"></i> ' + defaultText);
                return;
            }

            window.speechSynthesis.cancel();

            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = 'id-ID';
            utterance.rate = 0.95;

            const voices = window.speechSynthesis.getVoices();
            const idVoice = voices.find(v => v.lang.includes('id') || v.lang.includes('ID'));
            if (idVoice) utterance.voice = idVoice;

            utterance.onstart = function() {
                btn.removeClass('btn-warning').addClass('btn-danger').html('<i class="bi bi-stop-fill me-1 fs-6"></i> Hentikan Suara');
            };

            utterance.onend = function() {
                btn.removeClass('btn-danger').addClass('btn-warning').html('<i class="bi bi-volume-up-fill me-1 fs-6"></i> ' + defaultText);
            };

            utterance.onerror = function() {
                btn.removeClass('btn-danger').addClass('btn-warning').html('<i class="bi bi-volume-up-fill me-1 fs-6"></i> ' + defaultText);
            };

            window.speechSynthesis.speak(utterance);
        }
    });
</script>
@endpush