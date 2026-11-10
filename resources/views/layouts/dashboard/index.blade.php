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
        margin-top: 1rem;
    }
    .scan-accordion-item {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        overflow: hidden;
        background: #ffffff;
        margin-bottom: 0.85rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.03);
        transition: all 0.2s ease;
    }
    .scan-accordion-item:hover {
        border-color: #cbd5e1;
    }
    .scan-accordion-header {
        background: #f8fafc;
        color: #1e293b;
        padding: 0.95rem 1.25rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-weight: 700;
        font-size: 1rem;
        border-bottom: 1px solid transparent;
        transition: all 0.2s ease;
    }
    .scan-accordion-header:not(.collapsed) {
        background: #f1f5f9;
        border-bottom-color: #e2e8f0;
        color: #0f172a;
    }
    .scan-accordion-header .left {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .scan-accordion-header .bi-chevron-toggle {
        transition: transform 0.25s ease;
        color: #64748b;
    }
    .scan-accordion-header.collapsed .bi-chevron-toggle {
        transform: rotate(180deg);
    }
    .scan-accordion-header .kepanitiaan-badge {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: rgba(25, 135, 84, 0.12);
        color: #198754;
        font-size: 0.95rem;
    }
    .scan-accordion-body {
        background: #ffffff;
        padding: 0.85rem;
    }
    .scan-session-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.75rem 1rem;
        background: #f8fafc;
        border: 1px solid #f1f5f9;
        border-radius: 10px;
        margin-bottom: 0.5rem;
        transition: all 0.2s ease;
    }
    .scan-session-item:last-child {
        margin-bottom: 0;
    }
    .scan-session-item:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }
    .barcode-thumb-box {
        width: 44px;
        height: 44px;
        min-width: 44px;
        background: #eff6ff;
        border: 1.5px solid #bfdbfe;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        color: #1d4ed8;
    }
    .barcode-thumb-box:hover {
        background: #dbeafe;
        border-color: #3b82f6;
        color: #1e40af;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
        transform: translateY(-2px);
    }
    .barcode-thumb-box i {
        font-size: 1.45rem;
        line-height: 1;
    }
    .pulse-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: #198754;
        box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.7);
        animation: pulse-green 2s infinite;
        display: inline-block;
        flex-shrink: 0;
    }
    @keyframes pulse-green {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(25, 135, 84, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(25, 135, 84, 0); }
    }
    .btn-session-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.45rem 1rem;
        border-radius: 50rem;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #334155;
        font-weight: 600;
        font-size: 0.88rem;
        transition: all 0.2s ease;
        cursor: pointer;
        user-select: none;
    }
    .btn-session-pill:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: #0f172a;
    }
    .btn-session-pill.active {
        background: #198754;
        color: #ffffff;
        border-color: #198754;
        box-shadow: 0 3px 10px rgba(25, 135, 84, 0.25);
    }
    .btn-session-pill.active .badge-pill-sub {
        background: rgba(255, 255, 255, 0.25);
        color: #ffffff;
    }
    .btn-session-pill.active .pulse-dot {
        background-color: #ffffff;
        box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.7);
    }
    .badge-pill-sub {
        font-size: 0.72rem;
        font-weight: 500;
        padding: 0.12rem 0.45rem;
        border-radius: 8px;
        background: #f1f5f9;
        color: #64748b;
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
        overflow: hidden;
    }
    .edlink-header {
        padding: 1.15rem 1.25rem 0.6rem 1.25rem;
    }
    .edlink-calendar-strip {
        padding: 0.25rem 0.5rem 0.85rem 0.5rem;
        border-bottom: 1px solid #edf2f7;
        position: relative;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .edlink-day-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-decoration: none;
        padding: 4px 2px;
        border-radius: 10px;
        transition: all 0.2s ease;
        color: #495057;
        position: relative;
        cursor: pointer;
        user-select: none;
        flex: 1;
    }
    .edlink-day-btn:hover {
        background-color: #f8f9fa;
        color: #198754;
    }
    .edlink-day-name {
        font-size: 0.82rem;
        font-weight: 500;
        margin-bottom: 4px;
        color: #6c757d;
    }
    .edlink-day-btn.active .edlink-day-name {
        color: #198754;
        font-weight: 700;
    }
    .edlink-day-badge {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
        font-weight: 600;
        transition: all 0.2s ease;
        color: #212529;
    }
    .edlink-day-btn.active .edlink-day-badge {
        background-color: #198754;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(25, 135, 84, 0.35);
    }
    .edlink-day-dot {
        width: 5px;
        height: 5px;
        background-color: #198754;
        border-radius: 50%;
        margin-top: 5px;
        display: inline-block;
    }
    .edlink-day-dot-empty {
        width: 5px;
        height: 5px;
        margin-top: 5px;
        display: inline-block;
        visibility: hidden;
    }
    .edlink-active-arrow {
        position: absolute;
        bottom: -14px;
        left: 50%;
        transform: translateX(-50%);
        width: 0;
        height: 0;
        border-left: 6px solid transparent;
        border-right: 6px solid transparent;
        border-top: 6px solid #198754;
        display: none;
        z-index: 2;
    }
    .edlink-day-btn.active .edlink-active-arrow {
        display: block;
    }
    .edlink-task-card {
        border-radius: 12px;
        border: 1px solid #edf2f7;
        background: #ffffff;
        padding: 1rem;
        margin-bottom: 0.85rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .edlink-task-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .edlink-btn-subtle {
        background: #ffffff;
        border: 1px solid #dee2e6;
        color: #495057;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        padding: 6px 8px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        flex: 1;
        transition: all 0.2s;
    }
    button.edlink-btn-subtle {
        cursor: pointer;
        font-family: inherit;
    }
    button.edlink-btn-subtle:disabled {
        cursor: not-allowed;
    }
    .edlink-btn-subtle:hover {
        background: #f8f9fa;
        color: #212529;
        border-color: #ced4da;
    }
    .edlink-btn-start {
        background-color: #198754;
        border: 1px solid #198754;
        color: #ffffff;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 700;
        padding: 6px 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        text-decoration: none;
        flex: 1;
        transition: all 0.2s;
        box-shadow: 0 2px 6px rgba(25, 135, 84, 0.25);
    }
    .edlink-btn-start:hover {
        background-color: #157347;
        border-color: #146c43;
        color: #ffffff;
    }
    .edlink-btn-stop {
        background-color: #dc3545;
        border: 1px solid #dc3545;
        color: #ffffff;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 700;
        padding: 6px 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        text-decoration: none;
        flex: 1;
        transition: all 0.2s;
        box-shadow: 0 2px 6px rgba(220, 53, 69, 0.25);
    }
    .edlink-btn-stop:hover {
        background-color: #bb2d3b;
        border-color: #b02a37;
        color: #ffffff;
    }
    .edlink-empty-state {
        padding: 2.5rem 1rem;
        text-align: center;
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
<div class="card welcome-card mb-4 p-4 shadow-sm border-0">
    <div class="row align-items-center">
        <div class="col-lg-8 mb-3 mb-lg-0">
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge bg-white text-success fw-bold px-2.5 py-1 rounded-pill" style="font-size: 0.78rem;">
                    <i class="bi bi-person-badge-fill me-1"></i>{{ auth()->user()->jabatan ?: (auth()->user()->roles ?? 'Pegawai') }}
                </span>
                <span class="badge bg-white bg-opacity-25 text-white fw-normal px-2.5 py-1 rounded-pill" style="font-size: 0.78rem;">
                    {{ auth()->user()->unit ?? 'Universitas Ibnu Sina' }}
                </span>
            </div>
            <h2 class="fw-bold fs-4 mb-2 text-white">Selamat Datang, {{ auth()->user()->name }}! 👋</h2>
            <p class="mb-2 text-white-50" style="font-size: 0.95rem;">
                <strong>Jabatan:</strong> {{ auth()->user()->jabatan ?? 'Pegawai' }} &nbsp;&bull;&nbsp; 
                <strong>Unit Kerja:</strong> {{ auth()->user()->unit ?? 'Universitas Ibnu Sina' }}
            </p>
            <p class="mb-0 small text-white-50">
                @if($isAdminOrLeader)
                    Pantau kinerja seluruh pegawai unit, kelola sesi absensi aktif kepanitiaan, dan tinjau progres tugas universitas secara terpusat.
                @else
                    Kelola dan pantau seluruh rencana kerja utama, kepanitiaan, insidentil, serta pencatatan absensi Anda secara efisien.
                @endif
            </p>
        </div>
        <div class="col-lg-4 text-lg-end d-flex flex-column align-items-lg-end gap-2 mt-3 mt-lg-0">
            <button type="button" id="btn-voice-dashboard" class="btn btn-warning fw-bold text-dark px-3 py-2 shadow-sm btn-equal-width rounded-pill">
                <i class="bi bi-volume-up-fill me-1 fs-6"></i> Ringkasan Suara
            </button>
            <a href="{{ route('rencana-kerja.index') }}" class="btn btn-light fw-bold text-success px-3 py-2 shadow-sm btn-equal-width rounded-pill">
                <i class="bi bi-list-check me-1"></i> Rencana Kerja
            </a>
            @if(auth()->check() && (!auth()->user()->isPimpinanUnit() && !auth()->user()->isAdmin()))
                <a href="{{ route('rencana-kerja.create') }}" class="btn btn-outline-light fw-bold px-3 py-2 btn-equal-width rounded-pill">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Tugas
                </a>
            @endif
        </div>
    </div>
</div>

<!-- Main Content Grid: Sisi Kiri col-lg-4 (Jadwal Minggu Ini) & Sisi Kanan col-lg-8 (Rencana Kerja & Kinerja) -->
<div class="row g-3 mb-4">
    <!-- SISI KIRI: col-lg-4 (Jadwal Minggu Ini & Status Kinerja Sistem) -->
    <div class="col-lg-4">
        <!-- Widget: Jadwal Minggu Ini -->
        <div class="card edlink-card shadow-sm border-0 mb-4">
            <div class="edlink-header d-flex align-items-center justify-content-between">
                <h5 class="fw-bold text-dark m-0 fs-6">Jadwal Minggu Ini</h5>
                <span class="small text-muted" style="font-size: 0.8rem;">Hari ini: {{ $todayFormatted }}</span>
            </div>
            
            <!-- Day Selector Strip (Senin - Minggu) -->
            <div class="edlink-calendar-strip">
                @foreach($weekDays as $day)
                    <a href="{{ request()->fullUrlWithQuery(['selected_date' => $day['date']]) }}" 
                       class="edlink-day-btn {{ $day['is_selected'] ? 'active' : '' }}" 
                       data-date="{{ $day['date'] }}"
                       title="{{ $day['formatted_date'] }}">
                        <span class="edlink-day-name">{{ $day['day_name'] }}</span>
                        <div class="edlink-day-badge">
                            {{ $day['day_num'] }}
                        </div>
                        @if($day['has_tasks'])
                            <span class="edlink-day-dot"></span>
                        @else
                            <span class="edlink-day-dot-empty"></span>
                        @endif
                        @if($day['is_selected'])
                            <span class="edlink-active-arrow"></span>
                        @endif
                    </a>
                @endforeach
            </div>

            <!-- Card Body / Task List (Multi-Day Instant Panels) -->
            <div class="card-body p-3">
                @foreach($weekDays as $day)
                    <div class="edlink-day-panel" data-date="{{ $day['date'] }}" style="{{ $day['is_selected'] ? '' : 'display: none;' }}">
                        <!-- Status Banner -->
                        <div class="alert alert-success d-flex align-items-center gap-2 p-2.5 rounded-3 mb-3 border-0" style="background-color: #e8f8f2; color: #0f5132; font-size: 0.85rem;">
                            <i class="bi bi-check-circle-fill text-success fs-5"></i>
                            <div>
                                @if($day['tasks']->count() > 0)
                                    <strong>{{ $day['tasks']->count() }} kegiatan / sesi</strong> terjadwal pada hari ini
                                @else
                                    Semua sesi di hari ini sudah selesai atau belum ada jadwal
                                @endif
                            </div>
                        </div>

                        <!-- Scheduled Tasks List -->
                        @forelse($day['tasks'] as $task)
                            <div class="edlink-task-card">
                                <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
                                    <h6 class="fw-bold text-dark m-0 text-uppercase" style="font-size: 0.9rem; line-height: 1.35;">
                                        {{ $task->uraian_tugas }}
                                    </h6>
                                    <span class="badge {{ $task->badge_class }} rounded-pill text-nowrap" style="font-size: 0.7rem; padding: 4px 8px;">
                                        {{ $task->kategori_label }}
                                    </span>
                                </div>

                                <!-- Meta Chips Row (Kategori, Jam Estimasi, Lokasi/Pegawai) -->
                                <div class="d-flex flex-wrap align-items-center gap-2 my-2.5 text-muted" style="font-size: 0.78rem;">
                                    <span class="badge bg-light text-secondary border px-2 py-1 d-inline-flex align-items-center gap-1.5 fw-normal">
                                        <i class="bi bi-journal-bookmark-fill text-success"></i>
                                        <span>{{ $task->kategori_label }}</span>
                                    </span>
                                    <span class="badge bg-light text-secondary border px-2 py-1 d-inline-flex align-items-center gap-1.5 fw-normal">
                                        <i class="bi bi-clock-fill text-primary"></i>
                                        <span>
                                            @if($task->estimasi_jam_mulai)
                                                {{ date('H:i', strtotime($task->estimasi_jam_mulai)) }} - {{ $task->estimasi_jam_selesai ? date('H:i', strtotime($task->estimasi_jam_selesai)) : 'Selesai' }} WIB
                                            @else
                                                Fleksibel
                                            @endif
                                        </span>
                                    </span>
                                    <span class="badge bg-light text-secondary border px-2 py-1 d-inline-flex align-items-center gap-1.5 fw-normal">
                                        <i class="bi bi-person-fill text-dark"></i>
                                        <span>{{ Str::limit($task->user->name ?? 'Pegawai', 20) }}</span>
                                    </span>
                                </div>

                                <!-- Subtitle / Document indicator -->
                                <div class="d-flex align-items-center gap-2 mb-3 small" style="font-size: 0.82rem;">
                                    <i class="bi bi-file-earmark-check-fill text-success"></i>
                                    <span class="fw-medium text-dark text-truncate">
                                        @if($task->file)
                                            <a href="{{ asset('storage/' . $task->file) }}" target="_blank" class="text-success text-decoration-none fw-semibold" title="Buka / Unduh Berkas"><i class="bi bi-paperclip me-1"></i>{{ basename($task->file) }}</a>
                                        @elseif($task->url_external)
                                            <a href="{{ $task->url_external }}" target="_blank" class="text-success text-decoration-none fw-semibold" title="Buka Tautan Eksternal"><i class="bi bi-link-45deg me-1"></i>Tautan Berkas Terlampir</a>
                                        @else
                                            Rencana Tugas Terjadwal
                                        @endif
                                    </span>
                                </div>

                                <!-- Action Buttons Row -->
                                <div class="d-flex align-items-center gap-2">
                                    {{-- Button 1: Detail mengarah ke Kegiatan Rutin --}}
                                    <a href="{{ $task->kategori_code === 'rencana-kerja' ? route('rencana-kerja.index') : route($task->kategori_code . '.index') }}" class="edlink-btn-subtle" title="Lihat Kegiatan Rutin">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>

                                    {{-- Button 2: Pause (atau Presensi jika Kepanitiaan) --}}
                                    @if($task->kategori_code === 'kepanitiaan')
                                        <a href="{{ route('kepanitiaan.show', $task->id) }}" class="edlink-btn-subtle" title="Presensi Sesi Kepanitiaan">
                                            <i class="bi bi-qr-code-scan"></i> Presensi
                                        </a>
                                    @elseif($task->status === 'Di-pause')
                                        <button type="button" class="edlink-btn-subtle text-warning fw-semibold" disabled style="opacity: 0.85; cursor: default;" title="Kegiatan sedang di-jeda (Di-pause)">
                                            <i class="bi bi-pause-circle-fill text-warning"></i> Di-pause
                                        </button>
                                    @elseif($task->status === 'Selesai')
                                        <button type="button" class="edlink-btn-subtle text-muted" disabled style="opacity: 0.45; cursor: not-allowed;" title="Kegiatan telah selesai">
                                            <i class="bi bi-pause-fill"></i> Pause
                                        </button>
                                    @elseif($task->status === 'Proses' || $task->status === 'Berjalan')
                                        <button type="button" class="edlink-btn-subtle btn-action-dash text-warning fw-semibold text-nowrap" data-type="{{ $task->kategori_code }}" data-action="pause" data-id="{{ $task->id }}" title="Jeda / Pause Kegiatan">
                                            <i class="bi bi-pause-fill"></i> Pause
                                        </button>
                                    @else
                                        <button type="button" class="edlink-btn-subtle btn-action-dash text-nowrap" data-type="{{ $task->kategori_code }}" data-action="pause" data-id="{{ $task->id }}" title="Jeda / Pause Kegiatan">
                                            <i class="bi bi-pause-fill"></i> Pause
                                        </button>
                                    @endif

                                    {{-- Button 3: Mulai Kegiatan / Lanjut / Selesaikan / Selesai --}}
                                    @if($task->status == 'Selesai')
                                        <button type="button" class="btn btn-secondary btn-sm rounded-3 px-2 py-1.5 fw-semibold d-inline-flex align-items-center justify-content-center gap-1 flex-grow-1 text-nowrap" disabled style="font-size: 0.8rem; border-radius: 8px;">
                                            <i class="bi bi-check2-circle"></i> Selesai
                                        </button>
                                    @elseif($task->status == 'Proses' || $task->status == 'Berjalan')
                                        <button type="button" class="edlink-btn-stop btn-action-dash text-nowrap" data-type="{{ $task->kategori_code }}" data-action="stop" data-id="{{ $task->id }}">
                                            <i class="bi bi-stop-fill"></i> Selesaikan
                                        </button>
                                    @elseif($task->status == 'Di-pause')
                                        <button type="button" class="edlink-btn-start btn-action-dash text-nowrap" data-type="{{ $task->kategori_code }}" data-action="start" data-id="{{ $task->id }}">
                                            <i class="bi bi-play-fill"></i> Lanjut Kegiatan
                                        </button>
                                    @else
                                        <button type="button" class="edlink-btn-start btn-action-dash text-nowrap" data-type="{{ $task->kategori_code }}" data-action="start" data-id="{{ $task->id }}">
                                            <i class="bi bi-play-fill"></i> Mulai Kegiatan
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="edlink-empty-state">
                                <i class="bi bi-calendar-check text-muted d-block fs-1 mb-2 opacity-50"></i>
                                <h6 class="fw-bold text-dark mb-1">Tidak Ada Jadwal</h6>
                                <p class="text-muted small mb-0">Tidak ada sesi kegiatan yang terjadwal pada {{ $day['formatted_date'] }}.</p>
                            </div>
                        @endforelse
                    </div>
                @endforeach
            </div>
        </div>

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
                            @if(isset($isCampusWide) && $isCampusWide)
                                Total Pegawai Sistem
                            @else
                                Total Pegawai (OTK)
                            @endif
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

    <!-- SISI KANAN: col-lg-8 (KPI, Scan Hub, & Tabel Rencana Kerja Utama, Kepanitiaan, Insidentil) -->
    <div class="col-lg-8">
        <!-- KPI Summary Cards -->
        <div class="row g-2 mb-3">
            <div class="col-sm-6 col-md-3">
                <div class="card stat-card border-0 shadow-sm h-100 p-2.5" style="background: #ffffff; border-left: 4px solid #198754 !important;">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.7rem;">Rutinitas</span>
                            <h4 class="fw-bold text-dark mt-1 mb-0">{{ $totalTugas }}</h4>
                            <div class="text-muted" style="font-size: 0.72rem;">
                                <span class="text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i>{{ $tugasSelesai }}</span> ({{ $persentaseSelesai }}%)
                            </div>
                        </div>
                        <div class="stat-icon stat-icon-green shadow-sm" style="width: 38px; height: 38px; font-size: 1.1rem;">
                            <i class="bi bi-journal-check"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 4px; background-color: #f1f5f9;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $persentaseSelesai }}%;" aria-valuenow="{{ $persentaseSelesai }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
            
            <div class="col-sm-6 col-md-3">
                <div class="card stat-card border-0 shadow-sm h-100 p-2.5" style="background: #ffffff; border-left: 4px solid #0d6efd !important;">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.7rem;">Kepanitiaan</span>
                            <h4 class="fw-bold text-dark mt-1 mb-0">{{ $totalKepanitiaan }}</h4>
                            <div class="text-muted" style="font-size: 0.72rem;">
                                <span class="text-primary fw-bold"><i class="bi bi-check-circle-fill me-1"></i>{{ $kepanitiaanSelesai }} Selesai</span>
                            </div>
                        </div>
                        <div class="stat-icon stat-icon-blue shadow-sm" style="width: 38px; height: 38px; font-size: 1.1rem;">
                            <i class="bi bi-people-fill"></i>
                        </div>
                    </div>
                    @php
                        $kpPct = $totalKepanitiaan > 0 ? round(($kepanitiaanSelesai / $totalKepanitiaan) * 100) : 0;
                    @endphp
                    <div class="progress mt-2" style="height: 4px; background-color: #f1f5f9;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $kpPct }}%;" aria-valuenow="{{ $kpPct }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-md-3">
                <div class="card stat-card border-0 shadow-sm h-100 p-2.5" style="background: #ffffff; border-left: 4px solid #ffc107 !important;">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.7rem;">Insidentil</span>
                            <h4 class="fw-bold text-dark mt-1 mb-0">{{ $totalInsidentil }}</h4>
                            <div class="text-muted" style="font-size: 0.72rem;">
                                <span class="text-warning fw-bold text-dark"><i class="bi bi-check-circle-fill me-1"></i>{{ $insidentilSelesai }} Selesai</span>
                            </div>
                        </div>
                        <div class="stat-icon stat-icon-amber shadow-sm" style="width: 38px; height: 38px; font-size: 1.1rem;">
                            <i class="bi bi-lightning-charge-fill"></i>
                        </div>
                    </div>
                    @php
                        $insPct = $totalInsidentil > 0 ? round(($insidentilSelesai / $totalInsidentil) * 100) : 0;
                    @endphp
                    <div class="progress mt-2" style="height: 4px; background-color: #f1f5f9;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $insPct }}%;" aria-valuenow="{{ $insPct }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-md-3">
                <div class="card stat-card border-0 shadow-sm h-100 p-2.5" style="background: #ffffff; border-left: 4px solid #6f42c1 !important;">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.7rem;">
                                @if($isAdminOrLeader) Pegawai @else Presensi @endif
                            </span>
                            <h4 class="fw-bold text-dark mt-1 mb-0">
                                @if($isAdminOrLeader) {{ $totalPegawai }} @else {{ $activeSessions->count() }} @endif
                            </h4>
                            <div class="text-muted" style="font-size: 0.72rem;">
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill py-0.5 px-1.5" style="font-size: 0.68rem;">
                                    <i class="bi bi-broadcast me-1"></i>{{ $activeSessions->count() }} Sesi
                                </span>
                            </div>
                        </div>
                        <div class="stat-icon stat-icon-purple shadow-sm" style="width: 38px; height: 38px; font-size: 1.1rem;">
                            <i class="bi bi-person-badge-fill"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mt-2 text-muted" style="font-size: 0.68rem;">
                        <span class="text-truncate">{{ auth()->user()->unit ?? 'Universitas' }}</span>
                        <span>{{ date('d/m') }}</span>
                    </div>
                </div>
            </div>
        </div>

        @unless(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()))
        <!-- Quick Scan Absensi Card for Regular Users -->
        <div class="card shadow-sm border-0 mb-3" style="background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%); border: 1px solid #bbf7d0 !important;">
            <div class="card-body p-3">
                <div class="row align-items-center g-2">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center gap-2.5">
                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fs-5 shadow-sm" style="width: 42px; height: 42px; min-width: 42px; background: linear-gradient(135deg, #198754 0%, #20c997 100%);">
                                <i class="bi bi-qr-code-scan"></i>
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2">
                                    <h6 class="fw-bold m-0 fs-6 text-dark">Scan Absensi Kehadiran</h6>
                                    @if($defaultSession)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill" style="font-size: 0.68rem;">Sesi Aktif</span>
                                    @endif
                                </div>
                                <p class="m-0 text-muted small" style="font-size: 0.78rem;">
                                    @if($defaultSession)
                                        Sesi: <strong>{{ $defaultSession->nama_sesi }}</strong> ({{ $defaultSession->kepanitiaan->uraian_tugas ?? 'Kepanitiaan' }}).
                                    @else
                                        Scan QR Code untuk mencatat kehadiran Anda pada sesi kegiatan yang aktif.
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <button type="button" 
                                class="btn btn-success btn-sm rounded-pill shadow-sm btn-scan-barcode-modal d-inline-flex align-items-center justify-content-center px-3 py-1.5 fw-semibold"
                                @if($defaultSession)
                                    data-title="{{ $defaultSession->nama_sesi }}"
                                    data-slug="{{ $defaultSession->legacy_slug ?? 'sesi-' . $defaultSession->id }}"
                                    data-token="{{ $defaultSession->token }}"
                                @else
                                    data-title="Absensi Kehadiran"
                                @endif
                                data-active="1"
                                onclick="window.openScanBarcodeModal(this)">
                            <i class="bi bi-camera-fill me-1.5"></i>
                            <span>Scan Sekarang</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endunless

        <!-- ============================================================== -->
        <!-- Unified Table: Gabungan Rutinitas, Kepanitiaan & Insidentil     -->
        <!-- ============================================================== -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <h5 class="card-title m-0 fw-bold fs-6 text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-card-checklist text-success fs-5"></i>
                            <span>Daftar Kegiatan &amp; Tugas Kinerja</span>
                        </h5>
                        <p class="text-muted small mb-0 mt-1">Kegiatan rutinitas, kepanitiaan, dan insidentil milik Anda.</p>
                    </div>

                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <form action="{{ route('dashboard') }}" method="GET" class="d-flex flex-wrap align-items-center gap-2 m-0">
                            <!-- Dropdown Filter Kategori -->
                            <select name="filter_kategori" class="form-select form-select-sm" style="width: auto; min-width: 140px;" onchange="this.form.submit()">
                                <option value="all" {{ request('filter_kategori') == 'all' || !request('filter_kategori') ? 'selected' : '' }}>Semua Kategori</option>
                                <option value="rutinitas" {{ request('filter_kategori') == 'rutinitas' ? 'selected' : '' }}>Rutinitas</option>
                                <option value="kepanitiaan" {{ request('filter_kategori') == 'kepanitiaan' ? 'selected' : '' }}>Kepanitiaan</option>
                                <option value="insidentil" {{ request('filter_kategori') == 'insidentil' ? 'selected' : '' }}>Insidentil</option>
                            </select>

                            <!-- Dropdown Filter Status -->
                            <select name="filter_status" class="form-select form-select-sm" style="width: auto; min-width: 130px;" onchange="this.form.submit()">
                                <option value="all" {{ request('filter_status') == 'all' || !request('filter_status') ? 'selected' : '' }}>Semua Status</option>
                                <option value="Belum Dimulai" {{ request('filter_status') == 'Belum Dimulai' ? 'selected' : '' }}>Belum Dimulai</option>
                                <option value="Proses" {{ request('filter_status') == 'Proses' ? 'selected' : '' }}>Sedang Berjalan</option>
                                <option value="Selesai" {{ request('filter_status') == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                            </select>

                            <!-- Search Input -->
                            <div class="input-group input-group-sm" style="max-width: 220px;">
                                <input type="text" name="search_kegiatan" class="form-control" placeholder="Cari kegiatan..." value="{{ request('search_kegiatan', request('search_utama')) }}">
                                <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                            </div>

                            @if((request('filter_kategori') && request('filter_kategori') != 'all') || (request('filter_status') && request('filter_status') != 'all') || request('search_kegiatan'))
                                <a href="{{ route('dashboard') }}" class="btn btn-sm btn-light border text-secondary" title="Reset Filter">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </a>
                            @endif
                        </form>

                        <!-- Quick Dropdown Links ke Modul Lengkap -->
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle fw-semibold" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-box-arrow-up-right me-1"></i>Kelola Modul
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <li><a class="dropdown-item py-2 small" href="{{ route('rencana-kerja.index') }}"><i class="bi bi-journal-bookmark text-success me-2"></i>Kegiatan Rutinitas</a></li>
                                <li><a class="dropdown-item py-2 small" href="{{ route('kepanitiaan.index') }}"><i class="bi bi-people text-primary me-2"></i>Tugas Kepanitiaan</a></li>
                                <li><a class="dropdown-item py-2 small" href="{{ route('insidentil.index') }}"><i class="bi bi-lightning-charge text-warning me-2"></i>Tugas Insidentil</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="4%" class="ps-3">No</th>
                                <th width="32%">Uraian Tugas / Kegiatan</th>
                                <th width="12%">Kategori</th>
                                <th width="20%">Pegawai / Jabatan</th>
                                <th width="18%">Tanggal (Estimasi &amp; Realisasi)</th>
                                <th width="14%" class="pe-3 text-center">Status &amp; Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($allTasks as $index => $task)
                                <tr>
                                    <td class="ps-3 text-muted fw-semibold">{{ $allTasks->firstItem() + $index }}</td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $task->uraian_tugas }}</div>
                                        @if($task->file || $task->url_external)
                                            <div class="small text-muted mt-1">
                                                @if($task->file)
                                                    <a href="{{ asset('storage/' . $task->file) }}" target="_blank" class="badge bg-light text-secondary border text-decoration-none me-1">
                                                        <i class="bi bi-paperclip me-1"></i>Berkas
                                                    </a>
                                                @endif
                                                @if($task->url_external)
                                                    <a href="{{ $task->url_external }}" target="_blank" class="badge bg-light text-primary border text-decoration-none">
                                                        <i class="bi bi-link-45deg me-1"></i>Link
                                                    </a>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $task->kategori_badge_class ?? 'bg-secondary-subtle text-secondary' }} fw-semibold px-2 py-1" style="font-size: 0.74rem;">
                                            <i class="bi {{ $task->kategori_icon ?? 'bi-tag' }} me-1"></i>{{ $task->kategori_label ?? 'Kegiatan' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($task->user)
                                            <div class="text-dark fw-bold small">
                                                <i class="bi bi-person-fill text-muted me-1"></i>{{ $task->user->name ?? '-' }}
                                            </div>
                                            @if($task->user->jabatan)
                                                <div class="small mt-1">
                                                    <span class="badge bg-light text-dark border fw-semibold me-1">
                                                        <i class="bi bi-briefcase-fill text-secondary me-1"></i>{{ $task->user->jabatan }}
                                                    </span>
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="small">
                                            @if($task->estimasi_tanggal_mulai)
                                                <div class="text-secondary">
                                                    <i class="bi bi-calendar-event me-1"></i>{{ date('d/m/Y', strtotime($task->estimasi_tanggal_mulai)) }}
                                                    @if($task->estimasi_tanggal_selesai)
                                                        s/d {{ date('d/m/Y', strtotime($task->estimasi_tanggal_selesai)) }}
                                                    @endif
                                                </div>
                                            @endif
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
                                            <button type="button" class="btn btn-sm btn-danger px-3 py-1 fw-bold shadow-sm btn-action-dash text-nowrap" data-type="{{ $task->module_type }}" data-action="stop" data-id="{{ $task->id }}">
                                                <i class="bi bi-stop-fill me-1"></i>Selesaikan
                                            </button>
                                        @else
                                            @php
                                                $btnStartClass = $task->module_type == 'rencana-kerja' ? 'btn-success' : ($task->module_type == 'kepanitiaan' ? 'btn-primary' : 'btn-warning text-dark');
                                            @endphp
                                            <button type="button" class="btn btn-sm {{ $btnStartClass }} px-3 py-1 fw-bold shadow-sm btn-action-dash text-nowrap" data-type="{{ $task->module_type }}" data-action="start" data-id="{{ $task->id }}">
                                                <i class="bi bi-play-fill me-1"></i>Mulai
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                                        Belum ada data kegiatan atau tugas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($allTasks->hasPages() || $allTasks->total() > 0)
                <div class="card-footer bg-white border-top py-2 d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div class="small text-muted">
                        Menampilkan <strong>{{ $allTasks->firstItem() ?? 0 }}</strong> s/d <strong>{{ $allTasks->lastItem() ?? 0 }}</strong> dari <strong>{{ $allTasks->total() }}</strong> data
                    </div>
                    <div>
                        {{ $allTasks->links() }}
                    </div>
                </div>
            @endif
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // -------------------------------------------------------------
        // Seamless Instant Day Switcher for Jadwal Minggu Ini
        // -------------------------------------------------------------
        $(document).on('click', '.edlink-day-btn', function(e) {
            e.preventDefault();
            let targetDate = $(this).data('date');
            if (!targetDate) return;

            // 1. Immediately update active badge and arrow on clicked day
            $('.edlink-day-btn').removeClass('active');
            $('.edlink-day-btn .edlink-active-arrow').remove();
            $(this).addClass('active');
            $(this).append('<span class="edlink-active-arrow"></span>');

            // 2. Instantly switch day panel with zero page refresh
            $('.edlink-day-panel').hide();
            $('.edlink-day-panel[data-date="' + targetDate + '"]').stop(true, true).fadeIn(120);

            // 3. Update URL query parameter silently without reloading
            try {
                let currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('selected_date', targetDate);
                window.history.replaceState({ selected_date: targetDate }, '', currentUrl.toString());
            } catch (err) {}
        });

        // Handle Mulai, Pause, & Selesai button click on Dashboard
        $(document).on('click', '.btn-action-dash', function() {
            let btn = $(this);
            let type = btn.data('type');
            let action = btn.data('action');
            let id = btn.data('id');
            let actionText = action === 'start' ? 'memulai' : (action === 'pause' ? 'menjeda' : 'menyelesaikan');

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


        $('#btn-voice-dashboard').on('click', function() {
            let voiceText = "Halo {{ auth()->user()->name }}. Berikut ringkasan capaian kinerja untuk unit {{ auth()->user()->unit ?? 'Universitas Ibnu Sina' }}. Total rencana kerja utama terdaftar sebanyak {{ $totalTugas }} tugas dengan tingkat capaian {{ $persentaseSelesai }} persen. Tugas kepanitiaan terdaftar sebanyak {{ $totalKepanitiaan }} tugas, dan tugas insidentil terdaftar sebanyak {{ $totalInsidentil }} tugas. Saat ini terdapat {{ $activeSessions->count() }} sesi absensi yang aktif.";
            
            toggleSpeech(voiceText, 'btn-voice-dashboard', 'Ringkasan Suara');
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