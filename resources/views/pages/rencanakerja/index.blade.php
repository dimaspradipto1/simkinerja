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
    .btn-mulai-green {
        background-color: #15432d;
        color: #ffffff !important;
        border: none;
        padding: 4px 14px;
        font-size: 0.85rem;
        font-weight: 600;
        border-radius: 4px;
    }
    .btn-mulai-green:hover {
        background-color: #0f3121;
    }
    .btn-selesai-red {
        background-color: #8b0000;
        color: #ffffff !important;
        border: none;
        padding: 4px 14px;
        font-size: 0.85rem;
        font-weight: 600;
        border-radius: 4px;
    }
    .btn-selesai-red:hover {
        background-color: #660000;
    }
    .table-responsive, .dataTables_wrapper, #rencanakerja-table {
        overflow-x: hidden !important;
        width: 100% !important;
    }
    #rencanakerja-table > thead {
        display: none;
    }
    #rencanakerja-table {
        margin-bottom: 24px !important;
    }
    #rencanakerja-table > tbody > tr > td {
        vertical-align: top !important;
        padding: 10px 4px 16px 4px !important;
        border-bottom: 1px solid #edf2f7;
    }
    #rencanakerja-table > tbody > tr:last-child > td {
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
    }
    .transition-transform {
        transition: transform 0.2s ease-in-out;
    }
    .btn-task-toggle[aria-expanded="true"] .toggle-icon {
        transform: rotate(180deg);
    }
    .btn-task-toggle[aria-expanded="true"] {
        background-color: #15432d !important;
        color: #ffffff !important;
        border-color: #15432d !important;
    }
    .btn-task-toggle[aria-expanded="true"]:hover {
        background-color: #0f3121 !important;
        color: #ffffff !important;
    }
    .btn-filter-pill {
        transition: all 0.2s ease-in-out;
        font-size: 0.8rem !important;
        padding: 5px 14px !important;
        border-radius: 20px !important;
    }
    .btn-filter-pill.active {
        background-color: #15432d !important;
        color: #ffffff !important;
        border-color: #15432d !important;
    }
    .btn-filter-pill:hover:not(.active) {
        background-color: #f1f5f9;
    }
    @media (max-width: 767.98px) {
        .card-header-green {
            padding: 12px 14px;
        }
        .card-header-green .header-title-text {
            font-size: 0.95rem !important;
            line-height: 1.4 !important;
            margin-bottom: 4px;
        }
        .header-action-group {
            width: 100% !important;
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 8px !important;
            margin-top: 8px !important;
        }
        .header-action-group .btn {
            width: 100% !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 0.82rem !important;
            padding: 8px 8px !important;
            min-height: 38px !important;
            border-radius: 6px !important;
            text-align: center !important;
            white-space: nowrap !important;
            box-sizing: border-box !important;
            margin: 0 !important;
        }
        .header-action-group #btn-bulk-delete:not(.d-none),
        .header-action-group .filter-status-wrapper {
            grid-column: 1 / -1 !important;
            width: 100% !important;
        }
        .header-action-group .filter-status-wrapper select {
            width: 100% !important;
        }
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            float: none !important;
            text-align: left !important;
            margin-bottom: 0.75rem;
            width: 100% !important;
        }
        .dataTables_wrapper .dataTables_filter label {
            display: flex !important;
            align-items: center !important;
            width: 100% !important;
            gap: 8px;
            font-weight: 600;
            color: #475569;
        }
        .dataTables_wrapper .dataTables_filter input {
            flex-grow: 1 !important;
            width: 100% !important;
            margin-left: 0 !important;
            border-radius: 6px !important;
            padding: 6px 12px !important;
        }

        .card-body {
            padding: 12px 10px !important;
        }
        .table-responsive {
            overflow-x: hidden !important;
            padding: 0 !important;
            margin: 0 !important;
            border: none !important;
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
        }
        #rencanakerja-table {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            display: block !important;
            border-collapse: separate !important;
            border: none !important;
            table-layout: fixed !important;
        }
        #rencanakerja-table > thead {
            display: none !important;
        }
        #rencanakerja-table > tbody {
            display: block !important;
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
        }
        #rencanakerja-table > tbody > tr {
            display: block !important;
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            background: transparent !important;
            border: none !important;
            padding: 0 !important;
            margin-bottom: 12px !important;
            box-shadow: none !important;
            box-sizing: border-box !important;
        }
        #rencanakerja-table > tbody > tr > td {
            display: block !important;
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            box-sizing: border-box !important;
            border: none !important;
            padding: 0 !important;
        }
        #rencanakerja-table > tbody > tr > td:first-child,
        #rencanakerja-table > tbody > tr > td:nth-child(2) {
            display: none !important;
        }
        #rencanakerja-table > tbody > tr > td:last-child {
            margin: 0 !important;
            padding: 0 !important;
            border-top: none !important;
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            box-sizing: border-box !important;
        }
        .task-card-item {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            box-sizing: border-box !important;
            padding: 14px 12px !important;
            margin: 0 0 12px 0 !important;
            border-radius: 10px !important;
            background-color: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05) !important;
            overflow: hidden !important;
        }
        .task-action-container {
            display: flex !important;
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 8px !important;
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            box-sizing: border-box !important;
        }
        .task-action-label {
            font-size: 0.82rem !important;
            font-weight: 700 !important;
            color: #475569 !important;
            margin-bottom: 2px !important;
            width: 100% !important;
        }
        .action-buttons-wrap {
            display: flex !important;
            flex-direction: column !important;
            align-items: stretch !important;
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            box-sizing: border-box !important;
            gap: 8px !important;
        }
        .action-buttons-wrap .badge-status-group,
        .action-buttons-wrap .timer-status-group {
            display: flex !important;
            flex-direction: row !important;
            flex-wrap: wrap !important;
            gap: 8px !important;
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
        }
        .action-buttons-wrap .badge-status-group .badge,
        .action-buttons-wrap .timer-status-group .btn {
            flex: 1 1 0 !important;
            min-width: 0 !important;
            justify-content: center !important;
            text-align: center !important;
            font-size: 0.82rem !important;
            min-height: 40px !important;
            display: inline-flex !important;
            align-items: center !important;
            padding: 8px 10px !important;
            border-radius: 8px !important;
            white-space: nowrap !important;
            box-sizing: border-box !important;
        }
        .action-buttons-wrap .btn-mobile-stack {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            box-sizing: border-box !important;
            justify-content: center !important;
            text-align: center !important;
            display: flex !important;
            align-items: center !important;
            font-size: 0.85rem !important;
            font-weight: 600 !important;
            min-height: 42px !important;
            padding: 10px 14px !important;
            border-radius: 8px !important;
        }
        .action-buttons-wrap .owner-actions-group {
            display: flex !important;
            flex-direction: row !important;
            gap: 6px !important;
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
        }
        .action-buttons-wrap .owner-actions-group .btn {
            flex: 1 1 0 !important;
            min-width: 0 !important;
            width: auto !important;
            height: 40px !important;
            min-height: 40px !important;
            padding: 6px 2px !important;
            border-radius: 8px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            box-sizing: border-box !important;
        }
        .action-buttons-wrap .owner-actions-group .btn span {
            display: none !important;
        }
        .action-buttons-wrap .owner-actions-group .btn i {
            margin: 0 !important;
            font-size: 1.15rem !important;
        }
        .inline-upload-left {
            border-right: none !important;
            border-bottom: 1px solid #dee2e6 !important;
            padding-bottom: 1rem !important;
            margin-bottom: 0.5rem !important;
            padding-right: 0 !important;
        }
        .inline-upload-right {
            padding-left: 0 !important;
        }
    }
</style>

<div class="pagetitle">
    <h1>Rencana Kerja</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Rencana Kerja</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    @if(auth()->check() && auth()->user()->isRektorOrSuperAdmin())
    <!-- Top Section: Kriteria Checklist Jabatan (Khusus Pimpinan & Admin) -->
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-header-green d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                    <span class="header-title-text">Kriteria Checklist Jabatan</span>
                </div>
                <div class="card-body pt-3">
                    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                        <label class="form-label mb-0 fw-semibold text-secondary small text-nowrap">Kriteria Checklist untuk jabatan:</label>
                        <div style="min-width: 250px; max-width: 650px;" class="flex-grow-1">
                            <select class="form-select" id="filter-jabatan">
                                <option value="">-- Semua Jabatan --</option>
                                @foreach($usersWithJabatan as $u)
                                    <option value="{{ $u->jabatan }}" data-user-id="{{ $u->id }}" {{ (request('jabatan') == $u->jabatan || (!request()->has('jabatan') && auth()->user()->jabatan == $u->jabatan)) ? 'selected' : '' }}>
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
            <div class="p-2.5 p-sm-3 rounded-3 shadow-xs" style="background-color: #fffdf5; border: 1px solid #fde68a; border-left: 5px solid #f59e0b; width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
                <!-- Header Banner -->
                <div class="d-flex align-items-center gap-2 flex-wrap mb-2 pb-2 border-bottom" style="border-color: #fef08a !important;">
                    <span class="badge bg-danger rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.78rem;">
                        <i class="bi bi-bell-fill me-1"></i> {{ $pendingRequests->count() }} Permohonan Masuk
                    </span>
                    <span class="text-dark small fw-semibold">Tugas melewati estimasi dan membutuhkan persetujuan perpanjangan waktu:</span>
                </div>

                <!-- List of Items -->
                <div class="d-flex flex-column gap-2">
                    @foreach($pendingRequests as $pr)
                    @php
                        $rReason = $pr->estimasi_unlock_reason;
                        $tujuanPimpinan = null;
                        if (preg_match('/^\[Tujuan:\s*([^\]]+)\]\s*(.*)$/s', $rReason, $mMatches)) {
                            $tujuanPimpinan = $mMatches[1];
                            $rReason = $mMatches[2];
                        }
                    @endphp
                    <div class="p-2.5 p-sm-3 bg-white rounded-2 border shadow-2xs" style="border-color: #fed7aa !important; width: 100%; max-width: 100%; box-sizing: border-box;">
                        <!-- 1. Header: Pegawai & Timestamp -->
                        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-1.5">
                            <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                <i class="bi bi-person-circle text-primary fs-6"></i>
                                <span class="fw-bold text-dark" style="font-size: 0.88rem;">{{ $pr->user->name ?? '-' }}</span>
                                @if(!empty($pr->user->jabatan))
                                    <span class="badge bg-light text-secondary border fw-normal" style="font-size: 0.7rem;">{{ $pr->user->jabatan }}</span>
                                @endif
                            </div>
                            <span class="text-muted small" style="font-size: 0.73rem;">
                                <i class="bi bi-clock me-1"></i>{{ \Carbon\Carbon::parse($pr->estimasi_unlock_requested_at)->diffForHumans() }}
                            </span>
                        </div>

                        <!-- 2. Uraian Tugas -->
                        <div class="mb-2" style="word-break: break-word;">
                            <span class="badge bg-primary text-white me-1" style="font-size: 0.68rem; padding: 2px 6px;">Tugas</span>
                            <span class="fw-bold text-dark" style="font-size: 0.86rem;">{{ $pr->uraian_tugas }}</span>
                        </div>

                        <!-- 3. Tujuan & Kendala -->
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2.5" style="font-size: 0.75rem;">
                            @if($tujuanPimpinan)
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                    <i class="bi bi-person-badge me-1"></i>Tujuan: <strong>{{ $tujuanPimpinan }}</strong>
                                </span>
                            @endif
                            <div class="d-inline-flex align-items-start gap-1 p-1 px-2 rounded bg-danger-subtle text-danger border border-danger-subtle" style="word-break: break-word; max-width: 100%;">
                                <span class="fw-bold flex-shrink-0"><i class="bi bi-exclamation-circle-fill me-1"></i>Kendala:</span>
                                <span class="text-dark fw-medium">{{ $rReason }}</span>
                            </div>
                        </div>

                        <!-- 4. Tombol di Bawah -->
                        <div class="pt-2 border-top" style="border-color: #f1f5f9 !important;">
                            @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isPimpinanRektorat() || auth()->user()->isPimpinanUnit()))
                            <button type="button" 
                                    class="btn btn-warning btn-sm w-100 fw-bold d-flex align-items-center justify-content-center gap-1.5 text-dark" 
                                    style="background-color: #ffc107; border-color: #ffc107; padding: 7px 12px; font-size: 0.83rem; border-radius: 6px;"
                                    data-id="{{ $pr->id }}" 
                                    data-uraian="{{ e($pr->uraian_tugas) }}" 
                                    data-pegawai="{{ e($pr->user->name ?? '-') }}" 
                                    data-reason="{{ e($pr->estimasi_unlock_reason) }}" 
                                    data-tgl-selesai="{{ e($pr->estimasi_tanggal_selesai ?? date('Y-m-d')) }}" 
                                    data-jam-selesai="{{ e($pr->estimasi_jam_selesai ? substr($pr->estimasi_jam_selesai, 0, 5) : '23:59') }}" 
                                    data-route="{{ route('rencana-kerja.approve-extension', $pr->id) }}" 
                                    onclick="window.openApproveExtensionModal(this)">
                                <i class="bi bi-clock-history"></i>
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

    <!-- Bottom Section: Checklist Tugas -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-header-green d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
                    <div class="header-title-text d-flex align-items-center gap-2 flex-wrap text-white py-1">
                        <i class="bi bi-kanban-fill text-warning me-1" style="font-size: 1.15rem;"></i>
                        <span class="fw-bold text-white fs-6 mb-0">Rencana Kerja</span>
                        <span class="text-white d-none d-sm-inline">:</span>
                        <span id="judul-jabatan" class="fw-semibold" style="color: #f1f5f9; font-size: 0.95rem;">
                            {{ auth()->user()->jabatan ? auth()->user()->jabatan . ' - ' . auth()->user()->name : 'Semua Jabatan' }}
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
                        <button type="button" id="btn-voice-rencanakerja" class="btn btn-header-action btn-voice-modern" title="Dengarkan / Perintah Suara">
                            <i class="bi bi-volume-up-fill me-1.5"></i>
                            <span>Suara</span>
                        </button>
                        <button type="button" class="btn btn-header-action btn-export-modern" data-bs-toggle="modal" data-bs-target="#modalExportExcel" title="Export Laporan Rencana Kerja ke Excel">
                            <i class="bi bi-file-earmark-arrow-down-fill me-1.5"></i>
                            <span>Export Laporan</span>
                        </button>
                        <button type="button" class="btn btn-header-action btn-light-modern" data-bs-toggle="modal" data-bs-target="#modalImportExcel" title="Import Rencana Kerja dari Excel">
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
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 p-2 px-3 mb-3 rounded-3 border border-light-subtle shadow-2xs" id="selectAllToolbarRencanaKerja" style="background: #f8fafc; border-left: 4px solid #15432d !important;">
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
                        <table class="table table-hover w-100" id="rencanakerja-table">
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
                    <i class="bi bi-file-earmark-arrow-down-fill me-2"></i> Export Laporan Rencana Kerja
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('rencana-kerja.export-excel') }}" method="GET">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="export_periode_akademik_id" class="form-label fw-bold text-dark mb-1">
                            Pilih Periode Akademik <span class="text-danger">*</span>
                        </label>
                        <select name="periode_akademik_id" id="export_periode_akademik_id" class="form-select bg-white" required>
                            <option value="">-- Pilih Periode Akademik --</option>
                            @foreach($periodeAkademiks as $p)
                                <option value="{{ $p->id }}" {{ $p->id == $defaultPeriodeId ? 'selected' : '' }}>
                                    {{ $p->nama_periode }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if(auth()->check() && (auth()->user()->isPimpinanUnit() || auth()->user()->isAdmin()))
                    <div class="mb-3">
                        <label for="export_user_id" class="form-label fw-bold text-dark mb-1">
                            Pilih Jabatan / Staff <span class="text-muted fw-normal">(Read Only / Sesuai Checklist)</span>
                        </label>
                        <input type="hidden" name="user_id" id="export_user_id_hidden">
                        <select id="export_user_id" class="form-select bg-light" disabled style="pointer-events: none;">
                            <option value="">-- Semua Staff / Jabatan --</option>
                            @foreach($usersWithJabatan as $u)
                                <option value="{{ $u->id }}" data-jabatan="{{ $u->jabatan }}">{{ $u->name }} &mdash; {{ $u->jabatan }}</option>
                            @endforeach
                        </select>
                    </div>
                    @else
                        <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
                    @endif
                </div>
                <div class="modal-footer bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Batal</button>
                    <div class="d-flex gap-2">
                        <button type="submit" formaction="{{ route('rencana-kerja.export-excel') }}" class="btn btn-success px-3 text-white fw-bold" style="background-color: #15432d; border-color: #15432d;">
                            <i class="bi bi-file-earmark-excel-fill me-1"></i> Unduh Excel
                        </button>
                        <button type="submit" formaction="{{ route('rencana-kerja.export-pdf') }}" class="btn btn-danger px-3 text-white fw-bold">
                            <i class="bi bi-file-earmark-pdf-fill me-1"></i> Unduh PDF
                        </button>
                    </div>
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
                    <i class="bi bi-file-earmark-excel me-2"></i> Import Rencana Kerja Excel
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('rencana-kerja.import-excel') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="p-3 mb-3 rounded" style="background-color: #e0f2fe; border: 1px solid #bae6fd;">
                        <p class="mb-1 text-dark" style="font-size: 0.9rem;">
                            Format header: <strong>uraian_tugas</strong>
                        </p>
                        <a href="{{ route('rencana-kerja.download-template') }}" class="btn btn-sm text-primary p-0 fw-bold border-0 bg-transparent" style="font-size: 0.95rem;">
                            <i class="bi bi-download me-1"></i> Download Template
                        </a>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark mb-2">Periode Akademik Default <span class="text-muted fw-normal" style="font-size: 0.85rem;">(Opsional, jika di Excel kosong)</span></label>
                        <select name="periode_akademik_id" class="form-select bg-white">
                            <option value="">-- Otomatis Sesuai Data Excel --</option>
                            @if(isset($periodeAkademiks))
                                @foreach($periodeAkademiks as $p)
                                    <option value="{{ $p->id }}">{{ $p->nama_periode }}</option>
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
                        <label class="form-label fw-bold text-secondary mb-1">Uraian Tugas</label>
                        <div id="quick-tag-uraian" class="fw-semibold text-dark p-2 bg-light border rounded" style="font-size: 0.95rem; word-break: break-word;"></div>
                    </div>
                    <div class="mb-3">
                        <label for="quick_tagged_users" class="form-label fw-bold text-dark mb-1">
                            Pilih Rekan Kerja <span class="text-danger">*</span>
                        </label>
                        <select name="tagged_users[]" id="quick_tagged_users" class="form-select select2-multiple" multiple="multiple" style="width: 100%;">
                            @foreach($usersWithJabatan as $u)
                                <option value="{{ $u->id }}">{{ $u->jabatan ?? '-' }} - {{ $u->name }}</option>
                            @endforeach
                        </select>
                        <div class="form-text text-muted small mt-1">Pilih satu atau beberapa rekan kerja untuk pengerjaan tugas yang sama.</div>
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
@include('partials.milestone-scripts')
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#btn-voice-rencanakerja').on('click', function() {
            let jabatanVal = $('#filter-jabatan').length ? $('#filter-jabatan').val() : '';
            let voiceText = "Halaman Laporan Rencana Kerja untuk " + (jabatanVal ? "jabatan " + jabatanVal : "{{ auth()->user()->name }}") + ". ";

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

            // Fallback to DOM parsing if DataTables rows are empty or initializing
            if (taskItems.length === 0) {
                $('#rencanakerja-table tbody tr').each(function(idx) {
                    let row = $(this);
                    let taskTitle = row.find('.text-dark.fw-semibold, .fw-semibold.text-dark, .lh-sm').first().text().trim();
                    if (taskTitle) {
                        let textContent = row.text();
                        let no = idx + 1;
                        let uraianStr = "Uraian Kerja " + no + ": " + taskTitle + ". ";

                        // Estimasi
                        let estStr = "Estimasi: ";
                        let estMatch = textContent.match(/Estimasi Pelaksanaan:\s*([^\n\r\t]+)/i);
                        if (estMatch && estMatch[1] && estMatch[1].trim() !== '-') {
                            estStr += estMatch[1].trim() + ". ";
                        } else {
                            estStr += "Belum ada. ";
                        }

                        // Waktu Mulai
                        let mulaiStr = "Waktu mulai: ";
                        let mulaiMatch = textContent.match(/Waktu Mulai:\s*([^\n\r\t]+)/i);
                        if (mulaiMatch && mulaiMatch[1] && mulaiMatch[1].trim() !== '-') {
                            mulaiStr += mulaiMatch[1].trim() + ". ";
                        } else {
                            mulaiStr += "Belum dimulai. ";
                        }

                        // Waktu Selesai
                        let selesaiStr = "Waktu selesai: ";
                        let selesaiMatch = textContent.match(/Waktu Selesai:\s*([^\n\r\t]+)/i);
                        if (selesaiMatch && selesaiMatch[1] && selesaiMatch[1].trim() !== '-') {
                            selesaiStr += selesaiMatch[1].trim() + ". ";
                        } else {
                            selesaiStr += "Belum selesai. ";
                        }

                        // Durasi
                        let durasiStr = "Durasi: ";
                        let durasiMatch = textContent.match(/Total Durasi:\s*([^\n\r\t]+)/i);
                        if (durasiMatch && durasiMatch[1] && durasiMatch[1].trim() !== '-') {
                            durasiStr += durasiMatch[1].trim() + ". ";
                        } else {
                            durasiStr += "Belum ada. ";
                        }

                        // Analisis
                        let statusText = "Belum Dimulai";
                        if (textContent.includes("Selesai")) statusText = "Selesai";
                        else if (textContent.includes("Proses")) statusText = "Proses";

                        let analisisStr = "Analisis: ";
                        if (statusText === "Selesai") {
                            analisisStr += "Selesai tepat waktu. ";
                        } else if (statusText === "Proses") {
                            analisisStr += "Masih berproses. ";
                        } else {
                            analisisStr += "Belum dikerjakan. ";
                        }

                        // Link Eksternal Only
                        let hasLink = row.find('.bi-link-45deg, .bi-box-arrow-up-right').length > 0 || textContent.includes('Link External') || textContent.includes('http');
                        let linkStr = "Link eksternal: " + (hasLink ? "Ada link eksternal." : "Tidak ada link eksternal.") + " ";

                        taskItems.push(uraianStr + estStr + mulaiStr + selesaiStr + durasiStr + analisisStr + linkStr);
                    }
                });
            }

            if (taskItems.length > 0) {
                voiceText += "Menampilkan " + taskItems.length + " uraian kerja: " + taskItems.join(" ");

                // Combined Rekapitulasi Capaian Kinerja across ALL tasks in dataset
                let totalCount = taskItems.length;
                let selesaiCount = 0;
                let prosesCount = 0;
                let belumCount = 0;

                if (typeof window.latestOverallRekap !== 'undefined' && window.latestOverallRekap) {
                    totalCount = window.latestOverallRekap.total || taskItems.length;
                    selesaiCount = window.latestOverallRekap.selesai || 0;
                    prosesCount = window.latestOverallRekap.proses || 0;
                    belumCount = window.latestOverallRekap.belum || 0;
                } else if (typeof table !== 'undefined' && table.rows) {
                    let rowsData = table.rows().data();
                    totalCount = rowsData.length;
                    for (let i = 0; i < rowsData.length; i++) {
                        let rData = rowsData[i];
                        let txt = rData ? (rData.task_details || '') + (rData.voice_narration || '') : '';
                        if (txt.includes('Selesai')) selesaiCount++;
                        else if (txt.includes('Proses')) prosesCount++;
                        else belumCount++;
                    }
                }

                let percent = totalCount > 0 ? Math.round((selesaiCount / totalCount) * 100) : 0;
                let rekapStr = " Rekapitulasi keseluruhan capaian kinerja: Dari gabungan " + totalCount + " rencana kerja, sebanyak " + selesaiCount + " tugas telah selesai, " + prosesCount + " tugas sedang berproses, dan " + belumCount + " tugas belum dimulai. ";
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
                voiceText += "Belum ada rincian uraian kerja yang ditampilkan pada tabel.";
            }

            toggleSpeech(voiceText, 'btn-voice-rencanakerja', 'Suara');
        });

        function toggleSpeech(text, btnId, defaultText) {
            if (!('speechSynthesis' in window)) {
                alert('Peramban Anda belum mendukung fitur narasi suara.');
                return;
            }

            const btn = $('#' + btnId);

            if (window.speechSynthesis.speaking && !window.speechSynthesis.paused) {
                window.speechSynthesis.cancel();
                btn.removeClass('btn-danger').addClass('btn-warning').html('<i class="bi bi-volume-up-fill me-1"></i> ' + defaultText);
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
                btn.removeClass('btn-warning').addClass('btn-danger').html('<i class="bi bi-stop-fill me-1"></i> Stop');
            };

            utterance.onend = function() {
                btn.removeClass('btn-danger').addClass('btn-warning').html('<i class="bi bi-volume-up-fill me-1"></i> ' + defaultText);
            };

            utterance.onerror = function() {
                btn.removeClass('btn-danger').addClass('btn-warning').html('<i class="bi bi-volume-up-fill me-1"></i> ' + defaultText);
            };

            window.speechSynthesis.speak(utterance);
        }

        // Prevent Bootstrap 5 Modal focus trap from blocking Select2 search field input
        $(document).on('focusin', function(e) {
            if ($(e.target).closest('.select2-container').length) {
                e.stopImmediatePropagation();
            }
        });

        if ($('#filter-jabatan').length) {
            $('#filter-jabatan').select2({
                theme: 'bootstrap-5',
                placeholder: "Cari & pilih jabatan - nama...",
                allowClear: true
            });
        }

        function initExportModalSelect2() {
            if ($('#export_periode_akademik_id').length) {
                $('#export_periode_akademik_id').select2({
                    theme: 'bootstrap-5',
                    dropdownParent: $('#modalExportExcel'),
                    placeholder: "Cari & pilih periode akademik...",
                    allowClear: true
                });
            }

            if ($('#export_user_id').length) {
                $('#export_user_id').select2({
                    theme: 'bootstrap-5',
                    dropdownParent: $('#modalExportExcel'),
                    placeholder: "Nama staff...",
                    allowClear: false,
                    disabled: true
                });
            }
        }

        function syncExportStaffWithChecklist() {
            if (!$('#export_user_id').length) return;

            let selectedOpt = $('#filter-jabatan option:selected');
            let userId = selectedOpt.attr('data-user-id');
            let selectedJabatan = $('#filter-jabatan').val();
            let targetId = '';

            if (userId && $('#export_user_id option[value="' + userId + '"]').length) {
                targetId = userId;
            } else if (selectedJabatan) {
                let matchOpt = $('#export_user_id option').filter(function() {
                    return $(this).text().indexOf(selectedJabatan) !== -1;
                });
                if (matchOpt.length) {
                    targetId = matchOpt.first().val();
                }
            } else {
                let authId = "{{ auth()->user()->id }}";
                if ($('#export_user_id option[value="' + authId + '"]').length) {
                    targetId = authId;
                }
            }

            if (targetId) {
                $('#export_user_id').val(targetId).trigger('change');
                $('#export_user_id_hidden').val(targetId);
            }
        }

        initExportModalSelect2();

        $('#modalExportExcel').on('show.bs.modal shown.bs.modal', function () {
            initExportModalSelect2();
            syncExportStaffWithChecklist();
        });

        var table = $('#rencanakerja-table').DataTable({
            processing: true,
            serverSide: true,
            stateSave: true,
            ordering: false,
            autoWidth: false,
            ajax: {
                url: "{{ app()->environment('production') ? str_replace('http:', 'https:', route('rencana-kerja.index')) : route('rencana-kerja.index') }}",
                data: function(d) {
                    if ($('#filter-jabatan').length) {
                        d.jabatan = $('#filter-jabatan').val();
                    }
                    d.status = $('#filter-status').val();
                }
            },
            columns: [
                { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false, className: 'text-center align-top pt-2', width: '28px' },
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-muted fw-bold text-center align-top pt-2 small', width: '32px' },
                { data: 'task_details', name: 'uraian_tugas', searchable: true, className: 'align-top p-0' },
            ],
            language: {
                search: "Cari Tugas:",
                lengthMenu: "Tampilkan _MENU_ data",
                zeroRecords: "Belum ada rencana kerja / checklist tugas untuk filter ini",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ tugas",
                infoEmpty: "Tidak ada data"
            },
            initComplete: function() {
                $('#selectAllToolbarRencanaKerja').insertBefore('#rencanakerja-table');
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
                $('#judul-jabatan').text(val ? selectedText.trim() : 'Semua Jabatan');

                if ($('#export_jabatan').length) {
                    $('#export_jabatan').val(val).trigger('change');
                }

                table.ajax.reload();
            });
        }

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

            let totalOnPage = $('.select-row-checkbox').length;
            let checkedOnPage = $('.select-row-checkbox:checked').length;
            $('#check-all').prop('checked', totalOnPage > 0 && totalOnPage === checkedOnPage);
            $('#check-all').prop('indeterminate', checkedOnPage > 0 && checkedOnPage < totalOnPage);

            updateBulkDeleteButton();
        });

    function initTinyMCEEditors() {
        if (typeof tinymce !== 'undefined') {
            tinymce.remove('.tinymce-editor');
            tinymce.init({
                selector: '.tinymce-editor',
                height: 150,
                menubar: false,
                plugins: 'lists link table',
                toolbar: 'undo redo | bold italic underline | bullist numlist | removeformat',
                content_style: 'body { font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; font-size: 13px; }',
                setup: function (editor) {
                    editor.on('change blur', function () {
                        editor.save();
                    });
                }
            });
        }
    }

    // Sync checkbox status on table redraw (pagination, search, reload)
    table.on('draw', function() {
        let totalOnPage = $('.select-row-checkbox').length;
        let checkedOnPage = 0;

        $('.select-row-checkbox').each(function() {
            let val = parseInt($(this).val());
            if (selectedIds.has(val)) {
                $(this).prop('checked', true);
                checkedOnPage++;
            } else {
                $(this).prop('checked', false);
            }
        });

        $('#check-all').prop('checked', totalOnPage > 0 && totalOnPage === checkedOnPage);
        $('#check-all').prop('indeterminate', checkedOnPage > 0 && checkedOnPage < totalOnPage);

        if ($('#selectAllToolbarRencanaKerja').length && $('#rencanakerja-table').length) {
            $('#selectAllToolbarRencanaKerja').insertBefore('#rencanakerja-table');
        }

        updateBulkDeleteButton();
        setTimeout(initTinyMCEEditors, 150);
    });

    // Re-init TinyMCE when a task collapse is shown to ensure correct rendering dimensions
    $(document).on('shown.bs.collapse', function (e) {
        if (typeof tinymce !== 'undefined') {
            $(e.target).find('.tinymce-editor').each(function() {
                let id = $(this).attr('id');
                if (id) {
                    tinymce.remove('#' + id);
                    tinymce.init({
                        selector: '#' + id,
                        height: 150,
                        menubar: false,
                        plugins: 'lists link table',
                        toolbar: 'undo redo | bold italic underline | bullist numlist | removeformat',
                        content_style: 'body { font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; font-size: 13px; }',
                        setup: function (editor) {
                            editor.on('change blur', function () {
                                editor.save();
                            });
                        }
                    });
                }
            });
        }
    });

    // Toggle All Task Details (Buka Semua / Tutup Semua)
    let allTaskCardsExpanded = false;
    $('#btn-toggle-all-cards').on('click', function() {
        allTaskCardsExpanded = !allTaskCardsExpanded;
        if (allTaskCardsExpanded) {
            $('.collapse[id^="collapseTask_"]').collapse('show');
            $('#toggle-all-text').text('Tutup Semua');
            $(this).find('i').removeClass('bi-arrows-expand').addClass('bi-arrows-collapse');
        } else {
            $('.collapse[id^="collapseTask_"]').collapse('hide');
            $('#toggle-all-text').text('Buka Semua');
            $(this).find('i').removeClass('bi-arrows-collapse').addClass('bi-arrows-expand');
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
            let tags = btn.data('tags'); // Array of user IDs

            $('#quick-tag-uraian').text(uraian);
            
            // Set select values
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
                url: "{{ url('rencana-kerja') }}/" + currentQuickTagId + "/update-tags",
                type: 'POST',
                data: formData,
                success: function(response) {
                    btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan');
                    if (response.success) {
                        $('#modalQuickTag').modal('hide');
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: response.message,
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
                title: 'Hapus Rencana Kerja Terpilih?',
                text: 'Apakah Anda yakin ingin menghapus ' + ids.length + ' data rencana kerja terpilih ini secara permanen?',
                icon: 'warning',
                showCancelButton: true,
                confirmColor: '#d33',
                cancelColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-trash-fill me-1"></i> Ya, Hapus ' + ids.length + ' Data',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('rencana-kerja.bulk-delete') }}",
                        type: 'POST',
                        data: {
                            "_token": "{{ csrf_token() }}",
                            "ids": ids
                        },
                        success: function(response) {
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
                                selectedIds.clear();
                                updateBulkDeleteButton();
                                $('#check-all').prop('checked', false).prop('indeterminate', false);
                                $('#rencanakerja-table').DataTable().ajax.reload(null, false);
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
        if (id) window.deleteRencanaKerja(id);
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
            url: "{{ url('rencana-kerja') }}/" + id + "/upload-attachment",
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                btn.prop('disabled', false).html('<i class="bi bi-cloud-arrow-up-fill me-1"></i> Simpan');
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
                    $('#rencanakerja-table').DataTable().ajax.reload(null, false);
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

    // Get current laptop time formatted as HH:mm:ss and date YYYY-MM-DD
    window.getLaptopDateTime = function() {
        var now = new Date();
        var year = now.getFullYear();
        var month = String(now.getMonth() + 1).padStart(2, '0');
        var day = String(now.getDate()).padStart(2, '0');
        var hours = String(now.getHours()).padStart(2, '0');
        var minutes = String(now.getMinutes()).padStart(2, '0');
        var seconds = String(now.getSeconds()).padStart(2, '0');

        return {
            date: year + '-' + month + '-' + day,
            time: hours + ':' + minutes + ':' + seconds
        };
    };

    window.startTimer = function(id) {
        var dt = window.getLaptopDateTime();
        
        Swal.fire({
            title: 'Konfirmasi Pelaksanaan Tugas',
            text: 'Waktu mulai pelaksanaan tugas akan dicatat secara otomatis sesuai waktu sistem Anda (' + dt.time + ')',
            icon: 'question',
            showCancelButton: true,
            confirmColor: '#15432d',
            cancelColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-play-fill"></i> Ya, Mulai Pelaksanaan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('rencana-kerja') }}/" + id + "/start",
                    type: 'POST',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "client_time": dt.time,
                        "client_date": dt.date
                    },
                    success: function(response) {
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
                            $('#rencanakerja-table').DataTable().ajax.reload(null, false);
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Terjadi kesalahan saat mencatat waktu mulai.'
                        });
                    }
                });
            }
        });
    };

    window.stopTimer = function(id) {
        var dt = window.getLaptopDateTime();

        Swal.fire({
            title: 'Konfirmasi Penyelesaian Tugas',
            text: 'Waktu penyelesaian tugas akan dicatat secara otomatis sesuai waktu sistem Anda (' + dt.time + ')',
            icon: 'question',
            showCancelButton: true,
            confirmColor: '#8b0000',
            cancelColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-stop-fill"></i> Ya, Selesaikan Tugas',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('rencana-kerja') }}/" + id + "/stop",
                    type: 'POST',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "client_time": dt.time,
                        "client_date": dt.date
                    },
                    success: function(response) {
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
                            $('#rencanakerja-table').DataTable().ajax.reload(null, false);
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Terjadi kesalahan saat mencatat waktu selesai.'
                        });
                    }
                });
            }
        });
    };

    window.deleteRencanaKerja = function(id) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data rencana kerja ini akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmColor: '#d33',
            cancelColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('rencana-kerja') }}/" + id,
                    type: 'DELETE',
                    data: {
                        "_token": "{{ csrf_token() }}"
                    },
                    success: function(response) {
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
                            $('#rencanakerja-table').DataTable().ajax.reload(null, false);
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
</script>
@if(isset($dataTable))
    @if(app()->environment('production'))
        {!! str_replace('http:', 'https:', $dataTable->scripts()) !!}
    @else
        {!! $dataTable->scripts() !!}
    @endif
@endif
@endpush
