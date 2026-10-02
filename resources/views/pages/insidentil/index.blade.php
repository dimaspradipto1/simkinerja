@extends('layouts.dashboard.template')

@section('content')
<style>
    .card-header-green {
        background: linear-gradient(135deg, #15432d 0%, #1e5a3c 100%);
        color: #ffffff;
        padding: 12px 20px;
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.06);
    }
    .btn-header-action {
        height: 35px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 13px;
        font-size: 0.82rem;
        font-weight: 600;
        border-radius: 8px;
        transition: all 0.15s ease-in-out;
        white-space: nowrap;
        text-decoration: none;
    }
    .btn-header-action:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.12);
    }
    .btn-header-action:active {
        transform: translateY(0);
    }
    .btn-light-modern {
        background: #ffffff;
        color: #1e293b !important;
        border: 1px solid #cbd5e1;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }
    .btn-light-modern:hover {
        background: #f8fafc;
        border-color: #94a3b8;
        color: #0f172a !important;
    }
    .btn-voice-modern {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: #ffffff !important;
        border: none;
        box-shadow: 0 1px 3px rgba(217, 119, 6, 0.35);
    }
    .btn-voice-modern:hover {
        background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
        color: #ffffff !important;
    }
    .btn-export-modern {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: #ffffff !important;
        border: none;
        box-shadow: 0 1px 3px rgba(5, 150, 105, 0.35);
    }
    .btn-export-modern:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        color: #ffffff !important;
    }
    .filter-status-modern {
        height: 35px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 0 8px 0 10px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }
    .filter-status-modern select {
        border: none !important;
        outline: none !important;
        box-shadow: none !important;
        font-size: 0.82rem;
        font-weight: 600;
        color: #1e293b;
        background-color: transparent;
        padding: 0 20px 0 2px;
        cursor: pointer;
        height: 100%;
    }
    .task-action-container {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .task-action-label {
        font-size: 0.82rem;
        font-weight: 700;
        color: #475569;
        margin-bottom: 2px;
    }
    .action-buttons-wrap {
        display: flex !important;
        align-items: center !important;
        flex-wrap: wrap !important;
        gap: 8px !important;
    }
    .action-buttons-wrap .timer-status-group,
    .action-buttons-wrap .badge-status-group,
    .action-buttons-wrap .owner-actions-group {
        display: inline-flex !important;
        align-items: center !important;
        gap: 8px !important;
    }
    .action-buttons-wrap .btn,
    .action-buttons-wrap .badge {
        margin: 0 !important;
    }
    .btn-green-add {
        background-color: #2d6a4f;
        color: #ffffff !important;
        border: none;
        padding: 4px 12px;
        font-size: 0.85rem;
        border-radius: 4px;
    }
    .btn-green-add:hover {
        background-color: #1b4332;
    }
    .table-responsive, .dataTables_wrapper, #insidentil-table {
        overflow-x: hidden !important;
        width: 100% !important;
    }
    #insidentil-table > thead {
        display: none;
    }
    #insidentil-table {
        margin-bottom: 24px !important;
    }
    #insidentil-table > tbody > tr > td {
        vertical-align: top !important;
        padding: 10px 4px 16px 4px !important;
        border-bottom: 1px solid #edf2f7;
    }
    #insidentil-table > tbody > tr:last-child > td {
        padding-bottom: 28px !important;
        border-bottom: none !important;
    }
    .dataTables_wrapper .dataTables_paginate {
        margin-top: 24px !important;
        margin-bottom: 14px !important;
        padding-top: 8px !important;
    }
    .dataTables_wrapper .dataTables_info {
        margin-top: 24px !important;
        margin-bottom: 14px !important;
        padding-top: 8px !important;
    }
    .task-card-item {
        margin-bottom: 14px !important;
        transition: all 0.2s ease-in-out;
    }
    .task-card-item:hover {
        border-color: #cbd5e1 !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05) !important;
    }
    .btn-filter-pill {
        border-radius: 20px;
        padding: 5px 14px;
        font-size: 0.8rem;
        transition: all 0.2s ease-in-out;
    }
    .btn-filter-pill.active {
        background: #15432d !important;
        color: #ffffff !important;
        border-color: #15432d !important;
        box-shadow: 0 2px 4px rgba(21, 67, 45, 0.3) !important;
    }
    .toggle-icon {
        transition: transform 0.25s ease-in-out;
        display: inline-block;
    }
    .collapsed .toggle-icon {
        transform: rotate(0deg);
    }
    [aria-expanded="true"] .toggle-icon {
        transform: rotate(180deg);
    }
    #insidentil-table > tbody > tr > td:nth-child(1) {
        width: 38px !important;
        max-width: 38px !important;
        padding-left: 2px !important;
        padding-right: 2px !important;
        vertical-align: top !important;
        padding-top: 14px !important;
        text-align: center;
    }
    #insidentil-table > tbody > tr > td:nth-child(2) {
        width: 30px !important;
        max-width: 30px !important;
        padding-left: 0 !important;
        padding-right: 6px !important;
        vertical-align: top !important;
        padding-top: 16px !important;
        font-weight: 700;
        color: #475569;
        font-size: 0.88rem;
        text-align: center;
    }
    #insidentil-table > tbody > tr > td:nth-child(3) {
        padding-left: 4px !important;
        padding-right: 0 !important;
        width: auto !important;
    }
    @media (max-width: 768px) {
        #insidentil-table > tbody > tr > td:nth-child(1) {
            width: 32px !important;
            max-width: 32px !important;
        }
        #insidentil-table > tbody > tr > td:nth-child(2) {
            width: 24px !important;
            max-width: 24px !important;
            font-size: 0.8rem;
        }
    }
</style>

<div class="pagetitle">
    <h1>Rencana Kerja Insidentil</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Rencana Kerja Insidentil</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isPimpinanRektorat() || auth()->user()->isPimpinanUnit()))
    <!-- Top Section: Kriteria Checklist Jabatan -->
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-header-green d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                    <span class="header-title-text"><i class="bi bi-person-badge-fill me-2"></i>Kriteria Checklist Jabatan Insidentil</span>
                </div>
                <div class="card-body pt-3">
                    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                        <label class="form-label mb-0 fw-semibold text-secondary small text-nowrap">Kriteria Checklist untuk jabatan:</label>
                        <div style="min-width: 250px; max-width: 650px;" class="flex-grow-1">
                            <select class="form-select" id="filter-jabatan" style="width: 100%;">
                                <option value=""></option>
                                <option value="__own__">-- Data Saya Sendiri ({{ auth()->user()->name }}) --</option>
                                <option value="__all__">-- Semua Jabatan (Semua Pegawai) --</option>
                                @foreach($usersWithJabatan as $u)
                                    <option value="{{ $u->jabatan }}" data-user-id="{{ $u->id }}" {{ request('jabatan') == $u->jabatan ? 'selected' : '' }}>
                                        {{ $u->jabatan }} - {{ $u->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(isset($pendingRequests) && $pendingRequests->isNotEmpty())
    <!-- Section: Permohonan Perpanjangan Waktu Masuk -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="p-3 p-sm-3.5 rounded-3 shadow-xs" style="background-color: #fffdf5; border: 1px solid #fde68a; border-left: 5px solid #f59e0b; width: 100%; max-width: 100%; box-sizing: border-box;">
                <!-- Header Banner -->
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2.5 pb-2 border-bottom" style="border-color: #fef08a !important;">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge bg-danger rounded-pill px-2.5 py-1 fw-bold shadow-2xs" style="font-size: 0.78rem;">
                            <i class="bi bi-bell-fill me-1"></i> {{ $pendingRequests->count() }} Permohonan Masuk
                        </span>
                        <span class="text-dark small fw-semibold">Tugas insidentil melewati estimasi dan membutuhkan persetujuan perpanjangan waktu:</span>
                    </div>
                </div>

                <!-- List of Items -->
                <div class="d-flex flex-column gap-3">
                    @foreach($pendingRequests as $pr)
                    @php
                        $rReason = $pr->estimasi_unlock_reason;
                        $tujuanPimpinan = null;
                        if (preg_match('/^\[Tujuan:\s*([^\]]+)\]\s*(.*)$/s', $rReason, $mMatches)) {
                            $tujuanPimpinan = $mMatches[1];
                            $rReason = $mMatches[2];
                        }
                    @endphp
                    <div class="p-3 bg-white rounded-3 border shadow-2xs" style="border-color: #fed7aa !important; width: 100%; max-width: 100%; box-sizing: border-box;">
                        <!-- 1. Header: Pegawai & Timestamp -->
                        <div class="d-flex align-items-start justify-content-between gap-2 mb-2 pb-2 border-bottom border-light-subtle">
                            <div class="d-flex align-items-center gap-2">
                                <div class="d-flex align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary flex-shrink-0" style="width: 34px; height: 34px; font-size: 1rem;">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                                <div>
                                    <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                        <span class="fw-bold text-dark" style="font-size: 0.9rem;">{{ $pr->user->name ?? '-' }}</span>
                                        @if(!empty($pr->user->jabatan))
                                            <span class="badge bg-light text-secondary border fw-normal" style="font-size: 0.68rem; padding: 2px 6px;">{{ $pr->user->jabatan }}</span>
                                        @endif
                                    </div>
                                    <div class="text-muted d-flex align-items-center gap-1 mt-0.5" style="font-size: 0.72rem;">
                                        <i class="bi bi-clock"></i>
                                        <span>{{ \Carbon\Carbon::parse($pr->estimasi_unlock_requested_at)->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Uraian Tugas -->
                        <div class="mb-2.5" style="word-break: break-word;">
                            <div class="d-flex align-items-baseline gap-1.5">
                                <span class="badge bg-primary text-white flex-shrink-0" style="font-size: 0.68rem; padding: 3px 6px; border-radius: 4px;">Tugas</span>
                                <span class="fw-bold text-dark" style="font-size: 0.88rem; line-height: 1.45;">{{ $pr->uraian_tugas }}</span>
                            </div>
                        </div>

                        <!-- 3. Tujuan & Kendala -->
                        <div class="d-flex flex-column gap-1.5 mb-2.5" style="font-size: 0.78rem;">
                            @if($tujuanPimpinan)
                                <div class="d-flex align-items-center gap-2 p-1.5 px-2.5 rounded-2 bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.76rem;">
                                    <i class="bi bi-person-badge-fill flex-shrink-0"></i>
                                    <span class="text-truncate">Tujuan: <strong>{{ $tujuanPimpinan }}</strong></span>
                                </div>
                            @endif
                            <div class="d-flex align-items-start gap-2 p-1.5 px-2.5 rounded-2 bg-danger-subtle text-danger border border-danger-subtle" style="font-size: 0.76rem; word-break: break-word;">
                                <i class="bi bi-exclamation-circle-fill flex-shrink-0 mt-0.5"></i>
                                <div>
                                    <span class="fw-bold me-1">Kendala:</span>
                                    <span class="text-dark fw-medium">{{ strip_tags($rReason) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- 4. Tombol di Bawah -->
                        <div class="pt-2 border-top" style="border-color: #f1f5f9 !important;">
                            @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isPimpinanRektorat() || auth()->user()->isPimpinanUnit()))
                            <button type="button" 
                                    class="btn btn-warning btn-sm w-100 fw-bold d-flex align-items-center justify-content-center text-dark shadow-2xs" 
                                    style="background-color: #ffc107; border-color: #ffc107; padding: 8px 14px; font-size: 0.85rem; border-radius: 7px;"
                                    data-id="{{ $pr->id }}" 
                                    data-uraian="{{ e($pr->uraian_tugas) }}" 
                                    data-pegawai="{{ e($pr->user->name ?? '-') }}" 
                                    data-reason="{{ e($pr->estimasi_unlock_reason) }}" 
                                    data-tgl-selesai="{{ e($pr->estimasi_tanggal_selesai ?? date('Y-m-d')) }}" 
                                    data-jam-selesai="{{ e($pr->estimasi_jam_selesai ? substr($pr->estimasi_jam_selesai, 0, 5) : '23:59') }}" 
                                    data-route="{{ route('insidentil.approve-extension', $pr->id) }}" 
                                    onclick="window.openApproveExtensionModal(this)">
                                <i class="bi bi-clock-history me-2 fs-6"></i>
                                <span>Beri Perpanjangan Waktu</span>
                            </button>
                            @else
                            <div class="text-center text-muted small py-1" style="font-size: 0.75rem;">
                                <i class="bi bi-hourglass-split me-1 text-warning"></i> Menunggu Persetujuan Pimpinan
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Bottom Section: Checklist Tugas Insidentil -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-header-green d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
                    <div class="header-title-text d-flex align-items-center gap-2 flex-wrap text-white py-1">
                        <i class="bi bi-lightning-charge-fill text-warning me-1" style="font-size: 1.15rem;"></i>
                        <span class="fw-bold text-white fs-6 mb-0">Rencana Kerja Insidentil</span>
                        <span class="text-white d-none d-sm-inline">:</span>
                        <span id="judul-jabatan" class="fw-semibold" style="color: #f1f5f9; font-size: 0.95rem;">
                            {{ auth()->user()->jabatan ? auth()->user()->jabatan . ' - ' . auth()->user()->name : auth()->user()->name }}
                        </span>
                    </div>
                    <div class="header-action-group d-flex align-items-center gap-2 flex-wrap flex-sm-nowrap me-auto me-xl-0">
                        <div class="filter-status-modern" title="Filter checklist berdasarkan status">
                            <i class="bi bi-funnel-fill text-success" style="font-size: 0.85rem;"></i>
                            <span class="small fw-semibold text-secondary text-nowrap d-none d-xxl-inline" style="font-size: 0.78rem;">Status:</span>
                            <select id="filter-status" class="form-select form-select-sm border-0 bg-transparent fw-semibold text-dark p-0 pe-3 shadow-none">
                                <option value="">Semua Status</option>
                                <option value="Selesai">Selesai</option>
                                <option value="Proses">Proses / Berjalan</option>
                                <option value="Belum Dimulai">Belum Dimulai</option>
                            </select>
                        </div>
                        <button type="button" id="btn-toggle-all-cards" class="btn btn-header-action btn-light-modern" title="Buka atau tutup semua rincian">
                            <i class="bi bi-arrows-expand me-1.5 text-secondary"></i>
                            <span id="toggle-all-text">Buka Semua</span>
                        </button>
                        <button type="button" id="btn-voice-insidentil" class="btn btn-header-action btn-voice-modern" title="Dengarkan / Perintah Suara">
                            <i class="bi bi-volume-up-fill me-1.5"></i>
                            <span>Suara</span>
                        </button>
                        <button type="button" class="btn btn-header-action btn-export-modern" data-bs-toggle="modal" data-bs-target="#modalExportExcel" title="Export Laporan Insidentil ke Excel">
                            <i class="bi bi-file-earmark-arrow-down-fill me-1.5"></i>
                            <span>Export Laporan</span>
                        </button>
                        <button type="button" class="btn btn-header-action btn-light-modern" data-bs-toggle="modal" data-bs-target="#modalImportExcel" title="Import Rencana Kerja Insidentil dari Excel">
                            <i class="bi bi-file-earmark-excel-fill text-success me-1.5"></i>
                            <span>Import Excel</span>
                        </button>
                    </div>
                </div>
                <div class="card-body p-3">
                    <!-- Quick Status Filter Pills -->
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 pb-2 border-bottom">
                        <div class="d-flex flex-wrap align-items-center gap-2" id="filter-status-pills">
                            <button type="button" class="btn btn-sm btn-filter-pill active fw-semibold shadow-2xs" data-status="">
                                <i class="bi bi-grid-fill me-1"></i> Semua (<span id="count-status-all">0</span>)
                            </button>
                            <button type="button" class="btn btn-sm btn-filter-pill fw-semibold shadow-2xs bg-white border text-success" data-status="Selesai">
                                <i class="bi bi-check-circle-fill me-1 text-success"></i> Selesai (<span id="count-status-selesai">0</span>)
                            </button>
                            <button type="button" class="btn btn-sm btn-filter-pill fw-semibold shadow-2xs bg-white border text-warning" data-status="Proses">
                                <i class="bi bi-hourglass-split me-1 text-warning"></i> Proses / Berjalan (<span id="count-status-proses">0</span>)
                            </button>
                            <button type="button" class="btn btn-sm btn-filter-pill fw-semibold shadow-2xs bg-white border text-secondary" data-status="Belum Dimulai">
                                <i class="bi bi-dash-circle me-1 text-secondary"></i> Belum Dimulai (<span id="count-status-belum">0</span>)
                            </button>
                        </div>
                    </div>

                    <!-- Toolbar Pilih Semua (Select All) & Hapus Terpilih -->
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 p-2 px-3 mb-3 rounded-3 border border-light-subtle shadow-2xs" id="selectAllToolbarInsidentil" style="background: #f8fafc; border-left: 4px solid #15432d !important;">
                        <div class="d-flex align-items-center gap-2">
                            <input type="checkbox" id="check-all" class="form-check-input my-0" style="cursor: pointer; width: 19px; height: 19px;" title="Pilih Semua">
                            <label for="check-all" class="form-check-label fw-bold text-dark user-select-none mb-0 cursor-pointer" style="cursor: pointer; font-size: 0.88rem;">
                                Pilih Semua (Select All)
                            </label>
                            <span id="selected-badge" class="badge bg-primary-subtle text-primary border border-primary-subtle d-none ms-1" style="font-size: 0.78rem;">
                                <span id="selected-count">0</span> tugas dipilih
                            </span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" id="btn-bulk-delete" class="btn btn-danger btn-sm text-white fw-bold d-inline-flex align-items-center gap-1 d-none shadow-xs" style="border-radius: 6px; font-size: 0.82rem; padding: 6px 14px;">
                                <i class="bi bi-trash-fill me-1"></i> Hapus Terpilih (<span class="selected-count-num">0</span>)
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover w-100" id="insidentil-table">
                            <thead>
                                <tr>
                                    <th width="3%" class="text-center align-middle">
                                        <input type="checkbox" class="form-check-input" style="cursor: pointer; width: 18px; height: 18px;" title="Pilih Semua">
                                    </th>
                                    <th width="4%">No</th>
                                    <th>Uraian Tugas & Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Export Excel & PDF -->
<div class="modal fade" id="modalExportExcel" tabindex="-1" aria-labelledby="modalExportExcelLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white" style="background-color: #15432d;">
                <h5 class="modal-title fw-bold" id="modalExportExcelLabel">
                    <i class="bi bi-file-earmark-arrow-down-fill me-2"></i> Export Laporan Insidentil
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('insidentil.export-excel') }}" method="GET" id="formExportExcel">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark mb-1">Periode Akademik</label>
                        <select name="periode_akademik_id" id="export_periode" class="form-select bg-white">
                            <option value="">-- Semua Periode --</option>
                            @if(isset($periodeAkademiks))
                                @foreach($periodeAkademiks as $p)
                                    <option value="{{ $p->id }}" {{ (isset($defaultPeriodeId) && $defaultPeriodeId == $p->id) ? 'selected' : '' }}>
                                        {{ $p->formatted_ta ?? $p->nama_periode }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isPimpinanRektorat() || auth()->user()->isPimpinanUnit()))
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark mb-1">Pilih Jabatan Pegawai</label>
                        <select name="jabatan" id="export_jabatan" class="form-select bg-white">
                            <option value="">-- Data Saya Sendiri ({{ auth()->user()->name }}) --</option>
                            <option value="__all__">-- Semua Jabatan (Semua Pegawai) --</option>
                            @if(isset($usersWithJabatan))
                                @foreach($usersWithJabatan as $u)
                                    <option value="{{ $u->jabatan }}">{{ $u->jabatan }} - {{ $u->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    @endif

                    <div class="mb-2">
                        <label class="form-label fw-bold text-dark mb-1">Pilih Format Unduhan</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="format_export" id="formatExcel" value="excel" checked>
                                <label class="form-check-label fw-semibold text-dark" for="formatExcel">
                                    <i class="bi bi-file-earmark-excel-fill text-success me-1"></i> Microsoft Excel (.xlsx)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="format_export" id="formatPdf" value="pdf">
                                <label class="form-check-label fw-semibold text-dark" for="formatPdf">
                                    <i class="bi bi-file-earmark-pdf-fill text-danger me-1"></i> Dokumen PDF (.pdf)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success px-4 text-white fw-bold" style="background-color: #15432d; border-color: #15432d;">
                        <i class="bi bi-download me-1"></i> Download Laporan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Import Excel -->
<div class="modal fade" id="modalImportExcel" tabindex="-1" aria-labelledby="modalImportExcelLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white" style="background-color: #2d6a4f;">
                <h5 class="modal-title fw-bold" id="modalImportExcelLabel">
                    <i class="bi bi-file-earmark-excel me-2"></i> Import Rencana Kerja Insidentil Excel
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('insidentil.import-excel') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="p-3 mb-3 rounded" style="background-color: #f0fdf4; border: 1px solid #bbf7d0;">
                        <div class="d-flex align-items-start gap-2 mb-2">
                            <i class="bi bi-check-circle-fill text-success fs-5 mt-0.5"></i>
                            <div style="font-size: 0.88rem;" class="text-dark">
                                <span class="fw-bold text-success d-block mb-1">Import Tugas Insidentil:</span>
                                Anda dapat mengunggah file hasil export atau gunakan <strong>Template Standar Excel</strong>.
                            </div>
                        </div>
                        <div class="pt-2 border-top border-success-subtle d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <span class="text-muted small"><i class="bi bi-info-circle me-1"></i>Belum punya template?</span>
                            <a href="{{ route('insidentil.download-template') }}" class="btn btn-sm btn-outline-success fw-bold py-1 px-2.5" style="font-size: 0.82rem; border-radius: 6px;">
                                <i class="bi bi-download me-1"></i> Download Template Standar
                            </a>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark mb-2">Periode Akademik Default <span class="text-muted fw-normal" style="font-size: 0.85rem;">(Opsional, jika di Excel kosong)</span></label>
                        <select name="periode_akademik_id" class="form-select bg-white">
                            <option value="">-- Otomatis Sesuai Data Excel --</option>
                            @if(isset($periodeAkademiks))
                                @foreach($periodeAkademiks as $p)
                                    <option value="{{ $p->id }}">{{ $p->formatted_ta ?? $p->nama_periode }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark mb-2">Pilih File Excel (.xlsx, .xls, .csv)</label>
                        <input type="file" name="file_excel" class="form-control bg-white" accept=".xlsx,.xls,.csv" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success px-4 text-white fw-bold" style="background-color: #15432d; border-color: #15432d;">
                        <i class="bi bi-cloud-arrow-up-fill me-1"></i> Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Quick Tag Rekan Kerja -->
<div class="modal fade" id="modalQuickTag" tabindex="-1" aria-labelledby="modalQuickTagLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white" style="background-color: #15432d;">
                <h5 class="modal-title fw-bold" id="modalQuickTagLabel">
                    <i class="bi bi-person-plus-fill me-2"></i> Tag Rekan Kerja
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-quick-tag" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary mb-1">Uraian Tugas Insidentil</label>
                        <div id="quick-tag-uraian" class="fw-semibold text-dark p-2 bg-light border rounded" style="font-size: 0.95rem; word-break: break-word;"></div>
                    </div>
                    <div class="mb-3">
                        <label for="quick_tagged_users" class="form-label fw-bold text-dark mb-1">
                            Pilih Rekan Kerja <span class="text-danger">*</span>
                        </label>
                        <select name="tagged_users[]" id="quick_tagged_users" class="form-select select2-multiple" multiple="multiple" style="width: 100%;">
                            @if(isset($usersWithJabatan))
                                @foreach($usersWithJabatan as $u)
                                    <option value="{{ $u->id }}">{{ $u->jabatan ?? '-' }} - {{ $u->name }}</option>
                                @endforeach
                            @endif
                        </select>
                        <div class="form-text text-muted small mt-1">Pilih satu atau beberapa rekan kerja untuk pengerjaan tugas ini.</div>
                    </div>
                </div>
                <div class="modal-footer bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success px-4 text-white fw-bold btn-save-quick-tag" style="background-color: #15432d; border-color: #15432d; height: 38px;">
                        <i class="bi bi-save me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Permohonan Perpanjangan Waktu (Pegawai) -->
<div class="modal fade" id="modalExtensionRequest" tabindex="-1" aria-labelledby="modalExtensionRequestLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white" style="background-color: #dc3545;">
                <h5 class="modal-title fw-bold" id="modalExtensionRequestLabel">
                    <i class="bi bi-clock-history me-2"></i> Permohonan Perpanjangan Waktu
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formExtensionRequest" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-danger d-flex align-items-start gap-2 mb-3">
                        <i class="bi bi-exclamation-triangle-fill fs-5 mt-0.5"></i>
                        <div class="small">
                            Tugas ini telah melewati batas estimasi waktu (Tugas Gagal). Silakan isi kendala dan ajukan permohonan ke pimpinan untuk mendapatkan tambahan waktu.
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary mb-1">Uraian Tugas:</label>
                        <div id="extension-task-uraian" class="p-2 bg-light rounded border fw-semibold text-dark small"></div>
                    </div>
                    @if(auth()->check() && !auth()->user()->isPimpinanUnit() && !auth()->user()->isAdmin())
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark mb-1">Tujukan ke Pimpinan:</label>
                        <select name="pimpinan_id" id="extension_pimpinan_id" class="form-select bg-white" required style="width: 100%;">
                            <option value="">-- Pilih Pimpinan Unit / Penanggung Jawab --</option>
                            @foreach($extensionSupervisors as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->jabatan ?? 'Pimpinan' }})</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="mb-2">
                        <label class="form-label fw-bold text-dark mb-1">Alasan / Kendala Keterlambatan: <span class="text-danger">*</span></label>
                        <textarea name="estimasi_unlock_reason" id="estimasi_unlock_reason" class="form-control" rows="3" placeholder="Jelaskan secara detail kendala yang dialami sehingga membutuhkan perpanjangan waktu..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger px-4 text-white fw-bold">
                        <i class="bi bi-send-fill me-1"></i> Kirim Permohonan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Persetujuan Perpanjangan Waktu (Pimpinan) -->
<div class="modal fade" id="modalApproveExtension" tabindex="-1" aria-labelledby="modalApproveExtensionLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white" style="background-color: #ffc107;">
                <h5 class="modal-title fw-bold text-dark" id="modalApproveExtensionLabel">
                    <i class="bi bi-clock-history me-2"></i> Beri Perpanjangan Waktu Tugas
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formApproveExtension" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary mb-1">Pegawai:</label>
                        <div id="approve-pegawai-name" class="fw-bold text-dark"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary mb-1">Uraian Tugas:</label>
                        <div id="approve-task-uraian" class="p-2 bg-light rounded border fw-semibold text-dark small"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-danger mb-1">Kendala yang Diajukan:</label>
                        <div id="approve-kendala-text" class="p-2 rounded border border-danger-subtle bg-danger-subtle text-danger small fw-medium"></div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark mb-1">Batas Tanggal Baru: <span class="text-danger">*</span></label>
                            <input type="date" name="estimasi_tanggal_selesai" id="approve_tgl_selesai" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark mb-1">Batas Jam Baru: <span class="text-danger">*</span></label>
                            <input type="time" name="estimasi_jam_selesai" id="approve_jam_selesai" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold text-dark mb-1">Catatan / Arahan Pimpinan (Opsional):</label>
                        <textarea name="catatan_pimpinan" class="form-control" rows="2" placeholder="Tuliskan arahan perpanjangan waktu..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning text-dark px-4 fw-bold">
                        <i class="bi bi-check2-circle me-1"></i> Setujui & Berikan Waktu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Reopen Task (Buka Kunci Tugas Selesai) -->
<div class="modal fade" id="modalReopenTask" tabindex="-1" aria-labelledby="modalReopenTaskLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white" style="background-color: #ffc107;">
                <h5 class="modal-title fw-bold text-dark" id="modalReopenTaskLabel">
                    <i class="bi bi-unlock-fill me-2"></i> Buka Kunci Tugas Selesai
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formReopenTask" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-warning d-flex align-items-start gap-2 mb-3">
                        <i class="bi bi-info-circle-fill fs-5 mt-0.5"></i>
                        <div class="small">
                            Membuka kunci akan mengembalikan status tugas menjadi <strong>Berjalan / Proses</strong> secara otomatis agar pekerjaan dapat dilanjutkan kembali.
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary mb-1">Uraian Tugas:</label>
                        <div id="reopen-task-uraian" class="p-2 bg-light rounded border fw-semibold text-dark small"></div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold text-dark mb-1">Alasan Pembukaan Kunci: <span class="text-danger">*</span></label>
                        <textarea name="reason" id="reopen_reason" class="form-control" rows="2" placeholder="Misal: Salah klik tombol selesai, perbaikan laporan, dll..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning text-dark px-4 fw-bold">
                        <i class="bi bi-unlock-fill me-1"></i> Buka Kunci Tugas
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('partials.milestone-scripts')
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $(document).on('focusin', function(e) {
            if ($(e.target).closest('.select2-container').length) {
                e.stopImmediatePropagation();
            }
        });

        if ($('#filter-jabatan').length) {
            $('#filter-jabatan').select2({
                theme: 'bootstrap-5',
                placeholder: 'Cari & pilih jabatan - nama...',
                allowClear: true,
                width: '100%'
            });
        }

        $('#modalExtensionRequest').on('shown.bs.modal', function() {
            const $supervisor = $('#extension_pimpinan_id');
            if ($supervisor.length && !$supervisor.hasClass('select2-hidden-accessible')) {
                $supervisor.select2({
                    theme: 'bootstrap-5',
                    dropdownParent: $('#modalExtensionRequest'),
                    placeholder: 'Cari & pilih pimpinan tujuan...',
                    allowClear: true,
                    width: '100%'
                });
            }
        });

        // Voice Assistant logic
        $('#btn-voice-insidentil').on('click', function() {
            let jabatanVal = $('#filter-jabatan').length ? $('#filter-jabatan').val() : '';
            let voiceText = "Halaman Laporan Rencana Kerja Insidentil untuk " + (jabatanVal ? "jabatan " + jabatanVal : "{{ auth()->user()->name }}") + ". ";

            let taskItems = [];
            if (typeof table !== 'undefined' && table.rows) {
                let rowsData = table.rows({ search: 'applied' }).data();
                for (let i = 0; i < rowsData.length; i++) {
                    let rData = rowsData[i];
                    if (rData && rData.voice_narration) {
                        let parts = rData.voice_narration.split('|||');
                        let uraianTitle = parts[0] || '';
                        let detailsStr = parts[1] || '';
                        taskItems.push("Uraian Kerja " + (i + 1) + ": " + uraianTitle + ". " + detailsStr);
                    }
                }
            }

            if (taskItems.length > 0) {
                voiceText += "Menampilkan " + taskItems.length + " uraian tugas: " + taskItems.join(" ");

                let totalCount = taskItems.length;
                let selesaiCount = 0;
                let prosesCount = 0;
                let belumCount = 0;

                if (typeof window.latestOverallRekap !== 'undefined' && window.latestOverallRekap) {
                    totalCount = window.latestOverallRekap.total || taskItems.length;
                    selesaiCount = window.latestOverallRekap.selesai || 0;
                    prosesCount = window.latestOverallRekap.proses || 0;
                    belumCount = window.latestOverallRekap.belum || 0;
                }

                let percent = totalCount > 0 ? Math.round((selesaiCount / totalCount) * 100) : 0;
                let rekapStr = " Rekapitulasi keseluruhan capaian kinerja: Dari gabungan " + totalCount + " rencana kerja insidentil, sebanyak " + selesaiCount + " tugas telah selesai, " + prosesCount + " tugas sedang berproses, dan " + belumCount + " tugas belum dimulai. ";
                rekapStr += "Tingkat capaian kinerja Anda secara keseluruhan mencapai " + percent + " persen. ";

                if (percent >= 80) {
                    rekapStr += "Capaian kinerja Anda sangat bagus dan sangat memuaskan! Pertahankan prestasi kinerja luar biasa ini!";
                } else if (percent >= 50) {
                    rekapStr += "Capaian kinerja Anda sudah baik, mari tingkatkan penyelesaian tugas-tugas yang masih berproses!";
                } else {
                    rekapStr += "Capaian kinerja Anda perlu peningkatan dan percepatan agar seluruh target tugas dapat rampung tepat waktu!";
                }

                voiceText += rekapStr;
            } else {
                voiceText += "Belum ada rincian uraian kerja insidentil yang ditampilkan pada tabel.";
            }

            toggleSpeech(voiceText, 'btn-voice-insidentil', 'Suara');
        });

        function toggleSpeech(text, btnId, defaultText) {
            if (!('speechSynthesis' in window)) {
                alert('Peramban Anda belum mendukung fitur narasi suara.');
                return;
            }

            const btn = $('#' + btnId);

            if (window.speechSynthesis.speaking && !window.speechSynthesis.paused) {
                window.speechSynthesis.cancel();
                btn.removeClass('btn-danger').addClass('btn-voice-modern').html('<i class="bi bi-volume-up-fill me-1.5"></i> ' + defaultText);
                return;
            }

            window.speechSynthesis.cancel();

            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = 'id-ID';
            utterance.rate = 1.0;
            utterance.pitch = 1.0;

            utterance.onstart = function() {
                btn.removeClass('btn-voice-modern').addClass('btn-danger').html('<i class="bi bi-stop-circle-fill me-1.5"></i> Berhenti');
            };

            utterance.onend = function() {
                btn.removeClass('btn-danger').addClass('btn-voice-modern').html('<i class="bi bi-volume-up-fill me-1.5"></i> ' + defaultText);
            };

            utterance.onerror = function() {
                btn.removeClass('btn-danger').addClass('btn-voice-modern').html('<i class="bi bi-volume-up-fill me-1.5"></i> ' + defaultText);
            };

            window.speechSynthesis.speak(utterance);
        }

        // Initialize DataTable
        var table = $('#insidentil-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('insidentil.index') }}",
                data: function (d) {
                    d.periode_akademik_id = $('#filter_periode').val();
                    d.jabatan = $('#filter-jabatan').val();
                    d.status = $('#filter-status').val();
                }
            },
            columns: [
                { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'task_details', name: 'task_details' }
            ],
            order: [],
            pageLength: 10,
            language: {
                search: "Cari Tugas:",
                lengthMenu: "Tampilkan _MENU_ data",
                zeroRecords: "Belum ada rencana kerja insidentil untuk filter ini",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ tugas",
                infoEmpty: "Tidak ada data"
            },
            initComplete: function() {
                $('#selectAllToolbarInsidentil').insertBefore('#insidentil-table');
            }
        });

        table.on('xhr.dt', function(e, settings, json, xhr) {
            if (json && json.overall_rekap) {
                window.latestOverallRekap = json.overall_rekap;
                $('#count-status-all').text(json.overall_rekap.total || 0);
                $('#count-status-selesai').text(json.overall_rekap.selesai || 0);
                $('#count-status-proses').text(json.overall_rekap.proses || 0);
                $('#count-status-belum').text(json.overall_rekap.belum || 0);
            }
        });

        // Filter Status Dropdown Change
        $('#filter-status').on('change', function() {
            var val = $(this).val();
            $('.btn-filter-pill').removeClass('active');
            $('.btn-filter-pill[data-status="' + val + '"]').addClass('active');
            table.ajax.reload();
        });

        // Filter Status Pills Click
        $('.btn-filter-pill').on('click', function() {
            var status = $(this).data('status');
            $('.btn-filter-pill').removeClass('active');
            $(this).addClass('active');
            $('#filter-status').val(status);
            table.ajax.reload();
        });

        if ($('#filter-jabatan').length) {
            $('#filter-jabatan').on('change', function() {
                var selectedText = $(this).find('option:selected').text();
                var val = $(this).val();
                let ownTitle = "{{ auth()->user()->jabatan ? auth()->user()->jabatan . ' - ' . auth()->user()->name : auth()->user()->name }}";

                if (!val || val === '__own__') {
                    $('#judul-jabatan').text(ownTitle);
                } else if (val === '__all__' || val === 'all') {
                    $('#judul-jabatan').text('Semua Jabatan');
                } else {
                    $('#judul-jabatan').text(selectedText.trim());
                }

                if ($('#export_jabatan').length) {
                    $('#export_jabatan').val(val).trigger('change');
                }

                table.ajax.reload();
            });
        }

        $('#filter_periode').on('change', function() {
            table.ajax.reload();
        });

        // Multi Select & Bulk Delete State Management
        let selectedIds = new Set();

        function updateBulkDeleteButton() {
            let count = selectedIds.size;
            $('#selected-count').text(count);
            $('.selected-count-num').text(count);
            if (count > 0) {
                $('#btn-bulk-delete').removeClass('d-none');
                $('#selected-badge').removeClass('d-none');
            } else {
                $('#btn-bulk-delete').addClass('d-none');
                $('#selected-badge').addClass('d-none');
            }
        }

        // Toggle check-all
        $(document).on('change', '#check-all', function() {
            let isChecked = $(this).is(':checked');
            $('.select-row-checkbox').prop('checked', isChecked);
            $('.select-row-checkbox').each(function() {
                let val = parseInt($(this).val());
                if (isChecked) {
                    selectedIds.add(val);
                } else {
                    selectedIds.delete(val);
                }
            });
            updateBulkDeleteButton();
        });

        // Individual row checkbox change
        $(document).on('change', '.select-row-checkbox', function() {
            let val = parseInt($(this).val());
            if ($(this).is(':checked')) {
                selectedIds.add(val);
            } else {
                selectedIds.delete(val);
            }

            let totalCheckboxes = $('.select-row-checkbox').length;
            let checkedCheckboxes = $('.select-row-checkbox:checked').length;

            if (checkedCheckboxes === 0) {
                $('#check-all').prop('checked', false).prop('indeterminate', false);
            } else if (checkedCheckboxes === totalCheckboxes) {
                $('#check-all').prop('checked', true).prop('indeterminate', false);
            } else {
                $('#check-all').prop('checked', false).prop('indeterminate', true);
            }

            updateBulkDeleteButton();
        });

        table.on('draw', function() {
            $('.select-row-checkbox').each(function() {
                let val = parseInt($(this).val());
                if (selectedIds.has(val)) {
                    $(this).prop('checked', true);
                }
            });

            let totalCheckboxes = $('.select-row-checkbox').length;
            let checkedCheckboxes = $('.select-row-checkbox:checked').length;
            if (totalCheckboxes > 0 && checkedCheckboxes === totalCheckboxes) {
                $('#check-all').prop('checked', true).prop('indeterminate', false);
            } else if (checkedCheckboxes > 0) {
                $('#check-all').prop('checked', false).prop('indeterminate', true);
            } else {
                $('#check-all').prop('checked', false).prop('indeterminate', false);
            }

            initTinyMCEEditors();
        });

        function initTinyMCEEditors() {
            if (typeof tinymce !== 'undefined') {
                tinymce.remove('.tinymce-editor');
                tinymce.init({
                    selector: '.tinymce-editor',
                    menubar: false,
                    statusbar: false,
                    plugins: 'lists link code autolink',
                    toolbar: 'undo redo | bold italic underline | bullist numlist | link',
                    branding: false,
                    height: 120,
                    content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; font-size: 13px; line-height: 1.4; color: #1e293b; }'
                });
            }
        }

        // Toggle all cards
        let allExpanded = false;
        $('#btn-toggle-all-cards').on('click', function() {
            allExpanded = !allExpanded;
            if (allExpanded) {
                $('.collapse').collapse('show');
                $('#toggle-all-text').text('Tutup Semua');
            } else {
                $('.collapse').collapse('hide');
                $('#toggle-all-text').text('Buka Semua');
            }
        });

        // Quick Tag Modal Handlers
        let currentQuickTagId = null;

        if ($('#quick_tagged_users').length) {
            $('#quick_tagged_users').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#modalQuickTag'),
                placeholder: "Cari & pilih rekan kerja...",
                allowClear: true
            });
        }

        $(document).on('click', '.btn-quick-tag', function() {
            let btn = $(this);
            currentQuickTagId = btn.data('id');
            let uraian = btn.data('uraian');
            let tags = btn.data('tags');

            $('#quick-tag-uraian').text(uraian);
            $('#quick_tagged_users').val(tags).trigger('change');
            $('#modalQuickTag').modal('show');
        });

        $('#form-quick-tag').on('submit', function(e) {
            e.preventDefault();
            if (!currentQuickTagId) return;

            let form = $(this);
            let btn = form.find('.btn-save-quick-tag');
            let formData = form.serialize();

            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

            $.ajax({
                url: "{{ url('insidentil') }}/" + currentQuickTagId + "/update-tags",
                type: 'POST',
                data: formData,
                success: function(response) {
                    btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan');
                    if (response.success || response.status === 'success') {
                        $('#modalQuickTag').modal('hide');
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: response.message || 'Rekan kerja berhasil disimpan.',
                            showConfirmButton: false,
                            timer: 4000,
                            timerProgressBar: true
                        });
                        table.ajax.reload(null, false);
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan');
                    let msg = 'Terjadi kesalahan saat memperbarui rekan kerja.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: msg
                    });
                }
            });
        });

        // Handle Bulk Delete Click
        $(document).on('click', '#btn-bulk-delete', function() {
            let ids = Array.from(selectedIds);
            if (ids.length === 0) return;

            Swal.fire({
                title: 'Hapus Rencana Kerja Insidentil Terpilih?',
                text: 'Apakah Anda yakin ingin menghapus ' + ids.length + ' data tugas insidentil terpilih ini secara permanen?',
                icon: 'warning',
                showCancelButton: true,
                confirmColor: '#d33',
                cancelColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-trash-fill me-1"></i> Ya, Hapus ' + ids.length + ' Data',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('insidentil.bulk-delete') }}",
                        type: 'POST',
                        data: {
                            "_token": "{{ csrf_token() }}",
                            "ids": ids
                        },
                        success: function(response) {
                            if (response.success || response.status === 'success') {
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'success',
                                    title: response.message || 'Data berhasil dihapus.',
                                    showConfirmButton: false,
                                    timer: 4000,
                                    timerProgressBar: true
                                });
                                selectedIds.clear();
                                updateBulkDeleteButton();
                                $('#check-all').prop('checked', false).prop('indeterminate', false);
                                $('#insidentil-table').DataTable().ajax.reload(null, false);
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Terjadi kesalahan saat menghapus data terpilih.'
                            });
                        }
                    });
                }
            });
        });

        // Handle Export Form submit
        $('#formExportExcel').on('submit', function(e) {
            let format = $('input[name="format_export"]:checked').val();
            if (format === 'pdf') {
                $(this).attr('action', "{{ route('insidentil.export-pdf') }}");
            } else {
                $(this).attr('action', "{{ route('insidentil.export-excel') }}");
            }
        });
    });

    $(document).on('click', '.btn-start-timer', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        if (id) window.startTimer(id);
    });

    $(document).on('click', '.btn-stop-timer', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        if (id) window.stopTimer(id);
    });

    $(document).on('click', '.btn-delete-timer', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        if (id) window.deleteInsidentil(id);
    });

    // Handle inline file and url_external upload form submit
    $(document).on('submit', '.form-inline-upload', function(e) {
        e.preventDefault();
        if (typeof tinymce !== 'undefined') {
            tinymce.triggerSave();
        }
        var form = $(this);
        var id = form.data('id');
        var btn = form.find('.btn-simpan-inline');
        var formData = new FormData(this);
        formData.append('_token', "{{ csrf_token() }}");

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>');

        $.ajax({
            url: "{{ url('insidentil') }}/" + id + "/upload-attachment",
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                btn.prop('disabled', false).html('<i class="bi bi-cloud-arrow-up-fill me-1"></i> Simpan');
                if (response.success || response.status === 'success') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: response.message || 'Hasil kerja berhasil disimpan.',
                        showConfirmButton: false,
                        timer: 4000,
                        timerProgressBar: true
                    });
                    $('#insidentil-table').DataTable().ajax.reload(null, false);
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="bi bi-cloud-arrow-up-fill me-1"></i> Simpan');
                var msg = 'Terjadi kesalahan saat menyimpan berkas/link.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Menyimpan',
                    text: msg
                });
            }
        });
    });

    // Handle Simpan Alasan Pause
    $(document).on('click', '.btn-simpan-alasan-pause', function(e) {
        e.preventDefault();
        var btn = $(this);
        var id = btn.data('id');
        var card = btn.closest('.task-card-item').length ? btn.closest('.task-card-item') : btn.closest('.collapse');
        var alasanText = card.find('.field-alasan-pause').val();

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

        $.ajax({
            url: "{{ url('insidentil') }}/" + id + "/upload-attachment",
            type: 'POST',
            data: {
                "_token": "{{ csrf_token() }}",
                "alasan_pause": alasanText
            },
            success: function(response) {
                btn.prop('disabled', false).html('<i class="bi bi-check2-circle me-1"></i> Simpan Penyebab Pause');
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Penyebab pause berhasil disimpan.',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="bi bi-check2-circle me-1"></i> Simpan Penyebab Pause');
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Menyimpan',
                    text: 'Terjadi kesalahan saat menyimpan penyebab pause.'
                });
            }
        });
    });

    // Global Timer functions
    window.startTimer = function(id) {
        $.ajax({
            url: "{{ url('insidentil') }}/" + id + "/start",
            type: 'POST',
            data: {
                "_token": "{{ csrf_token() }}"
            },
            success: function(response) {
                if (response.success || response.status === 'success') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: response.message || 'Pekerjaan dimulai.',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                    $('#insidentil-table').DataTable().ajax.reload(null, false);
                }
            },
            error: function(xhr) {
                let msg = 'Terjadi kesalahan saat memulai tugas.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Memulai',
                    text: msg
                });
            }
        });
    };

    window.pauseTaskTimer = function(id, routePrefix) {
        Swal.fire({
            title: 'Jeda Pekerjaan (Pause)',
            text: 'Tuliskan penyebab atau kendala pekerjaan di-pause (opsional):',
            input: 'textarea',
            inputPlaceholder: 'Tuliskan penyebab pekerjaan di-pause...',
            showCancelButton: true,
            confirmColor: '#ffc107',
            cancelColor: '#6c757d',
            confirmButtonText: '<span class="text-dark fw-bold">Jeda Sekarang</span>',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('insidentil') }}/" + id + "/pause",
                    type: 'POST',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "alasan_pause": result.value || ''
                    },
                    success: function(response) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'warning',
                            title: response.message || 'Pekerjaan di-pause.',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true
                        });
                        $('#insidentil-table').DataTable().ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Terjadi kesalahan saat menjeda pekerjaan.'
                        });
                    }
                });
            }
        });
    };

    window.stopTimer = function(id) {
        Swal.fire({
            title: 'Selesaikan Pekerjaan?',
            text: 'Status tugas akan diubah menjadi Selesai.',
            icon: 'question',
            showCancelButton: true,
            confirmColor: '#2d6a4f',
            cancelColor: '#6c757d',
            confirmButtonText: 'Ya, Selesaikan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('insidentil') }}/" + id + "/stop",
                    type: 'POST',
                    data: {
                        "_token": "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.success || response.status === 'success') {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: response.message || 'Pekerjaan selesai.',
                                showConfirmButton: false,
                                timer: 4000,
                                timerProgressBar: true
                            });
                            $('#insidentil-table').DataTable().ajax.reload(null, false);
                        }
                    },
                    error: function(xhr) {
                        let msg = 'Terjadi kesalahan saat menyelesaikan pekerjaan.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: msg
                        });
                    }
                });
            }
        });
    };

    window.deleteInsidentil = function(id) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data rencana kerja insidentil ini akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmColor: '#d33',
            cancelColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('insidentil') }}/" + id,
                    type: 'DELETE',
                    data: {
                        "_token": "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.success || response.status === 'success') {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: response.message || 'Data berhasil dihapus.',
                                showConfirmButton: false,
                                timer: 4000,
                                timerProgressBar: true
                            });
                            $('#insidentil-table').DataTable().ajax.reload(null, false);
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Terjadi kesalahan saat menghapus data.'
                        });
                    }
                });
            }
        });
    };

    // Modal Extension Request & Approval
    window.openExtensionModal = function(btn) {
        let el = $(btn);
        let id = el.data('id');
        let uraian = el.data('uraian');
        let route = el.data('route');

        $('#extension-task-uraian').text(uraian);
        $('#formExtensionRequest').attr('action', route);
        $('#modalExtensionRequest').modal('show');
    };

    window.openApproveExtensionModal = function(btn) {
        let el = $(btn);
        let id = el.data('id');
        let uraian = el.data('uraian');
        let pegawai = el.data('pegawai');
        let reason = el.data('reason');
        let tglSelesai = el.data('tgl-selesai');
        let jamSelesai = el.data('jam-selesai');
        let route = el.data('route');

        $('#approve-pegawai-name').text(pegawai);
        $('#approve-task-uraian').text(uraian);
        $('#approve-kendala-text').text(reason);
        $('#approve_tgl_selesai').val(tglSelesai);
        $('#approve_jam_selesai').val(jamSelesai);
        $('#formApproveExtension').attr('action', route);
        $('#modalApproveExtension').modal('show');
    };

    window.reopenTask = function(btn) {
        let el = $(btn);
        let id = el.data('id');
        let uraian = el.data('uraian');

        $('#reopen-task-uraian').text(uraian);
        $('#formReopenTask').attr('action', "{{ url('insidentil') }}/" + id + "/reopen");
        $('#modalReopenTask').modal('show');
    };

    $('#formReopenTask').on('submit', function(e) {
        e.preventDefault();
        let form = $(this);
        let actionUrl = form.attr('action');
        let reason = $('#reopen_reason').val();

        $.ajax({
            url: actionUrl,
            type: 'POST',
            data: {
                "_token": "{{ csrf_token() }}",
                "reason": reason
            },
            success: function(response) {
                $('#modalReopenTask').modal('hide');
                if (response.success) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: response.message,
                        showConfirmButton: false,
                        timer: 4000,
                        timerProgressBar: true
                    });
                    $('#insidentil-table').DataTable().ajax.reload(null, false);
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Terjadi kesalahan saat membuka kunci tugas.'
                });
            }
        });
    });
</script>
@endpush
