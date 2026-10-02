@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div>
        <h1 class="fs-3 fw-bold text-dark mb-1">
            <i class="bi bi-calendar2-check text-primary me-2"></i>Data Absensi: {{ $sesi->nama_sesi }}
        </h1>
        <nav>
            <ol class="breadcrumb mb-0" style="font-size: 0.88rem;">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('kepanitiaan.index') }}">Rencana Kerja Kepanitiaan</a></li>
                <li class="breadcrumb-item active">{{ $sesi->nama_sesi }}</li>
            </ol>
        </nav>
    </div>

    <div class="d-flex align-items-center gap-2 flex-wrap">
        @if(isset($allSesis) && $allSesis->count() > 1)
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle d-inline-flex align-items-center gap-1 shadow-sm font-medium" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius: 8px; font-size: 0.86rem; padding: 7px 14px;">
                <i class="bi bi-arrow-left-right text-primary"></i> Ganti Sesi
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="min-width: 240px; border-radius: 10px;">
                <li class="dropdown-header text-uppercase fw-bold text-muted" style="font-size: 0.75rem;">Daftar Sesi Presensi Aktif</li>
                @foreach($allSesis as $otherSess)
                <li>
                    <a class="dropdown-item d-flex align-items-center justify-content-between py-2 {{ $otherSess->id === $sesi->id ? 'active fw-bold' : '' }}" href="{{ route('presensi.sesi.show', $otherSess->id) }}">
                        <span class="text-truncate me-2">{{ $otherSess->nama_sesi }}</span>
                        @if($otherSess->id === $sesi->id)
                            <i class="bi bi-check2"></i>
                        @endif
                    </a>
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        <a href="{{ route('kepanitiaan.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1 shadow-sm font-medium" style="border-radius: 8px; font-size: 0.86rem; padding: 7px 14px;">
            <i class="bi bi-arrow-left"></i> Rencana Kerja
        </a>
    </div>
</div>

<section class="section">
    <!-- Header Activity Banner Card -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #ffffff;">
        <div class="card-body p-4">
            <div class="row align-items-center g-3">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        @if($sesi->is_active)
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 font-semibold" style="font-size: 0.78rem;">
                                <i class="bi bi-broadcast me-1"></i>Sesi Presensi Aktif (Buka)
                            </span>
                        @else
                            <span class="badge bg-secondary-subtle text-white border border-secondary rounded-pill px-3 py-1 font-semibold" style="font-size: 0.78rem;">
                                <i class="bi bi-lock-fill me-1"></i>Sesi Presensi Ditutup (Terkunci)
                            </span>
                        @endif
                        @if($sesi->tanggal)
                            <span class="badge bg-white text-dark rounded-pill px-3 py-1 fw-bold shadow-sm" style="font-size: 0.78rem; color: #0f172a !important;">
                                <i class="bi bi-calendar3 me-1 text-primary"></i>{{ date('d M Y', strtotime($sesi->tanggal)) }}
                            </span>
                        @endif
                    </div>
                    <h3 class="fw-bold mb-1 text-white">{{ $sesi->nama_sesi }}</h3>
                    <p class="text-white-50 mb-0" style="font-size: 0.92rem;">
                        <i class="bi bi-bookmark-check me-1 text-primary-subtle"></i>Kegiatan: <strong>{{ $sesi->kepanitiaan->uraian_tugas ?? 'Kepanitiaan' }}</strong>
                        @if(!empty($sesi->kepanitiaan->unit))
                            <span class="ms-2 badge bg-white text-dark font-monospace fw-bold px-2.5 py-1 rounded-pill shadow-sm" style="font-size: 0.76rem; color: #0f172a !important;">{{ $sesi->kepanitiaan->unit }}</span>
                        @endif
                    </p>
                </div>

                <div class="col-lg-4 text-lg-end">
                    <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                        <button type="button" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1.5 shadow-sm fw-semibold rounded-2 px-3 py-1.5 btn-scan-barcode-modal"
                                data-slug="{{ $sesi->legacy_slug ?? 'sesi-' . $sesi->id }}"
                                data-token="{{ $sesi->token }}"
                                data-scan-url="{{ route('presensi.scan-proses', ['token' => $sesi->token]) }}"
                                data-title="{{ $sesi->nama_sesi }}"
                                onclick="window.openScanBarcodeModal(this)">
                            <i class="bi bi-qr-code-scan"></i> Scan Barcode
                        </button>

                        <button type="button" class="btn btn-light btn-sm text-dark border d-inline-flex align-items-center gap-1.5 shadow-sm fw-semibold rounded-2 px-3 py-1.5 btn-barcode-absensi"
                                data-slug="{{ $sesi->legacy_slug ?? 'sesi-' . $sesi->id }}"
                                data-token="{{ $sesi->token }}"
                                data-scan-url="{{ route('presensi.scan-proses', ['token' => $sesi->token]) }}"
                                data-title="{{ $sesi->nama_sesi }}"
                                data-url="{{ route('presensi.scan-proses', ['token' => $sesi->token]) }}"
                                onclick="window.openBarcodeModal(this)">
                            <i class="bi bi-qr-code"></i> Barcode Absensi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Metric Summary Cards -->
    <div class="row g-3 mb-4">
        @if($isSekretarisOrAdmin)
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid #0d6efd !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem;">Total Kehadiran</div>
                        <div class="fs-4 fw-bold text-dark mt-1" id="stat-total-presensi">{{ $totalPresensi }}</div>
                    </div>
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-people-fill fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid #198754 !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem;">Hadir Datang</div>
                        <div class="fs-4 fw-bold text-success mt-1" id="stat-hadir-datang">{{ $totalHadirDatang }}</div>
                    </div>
                    <div class="rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-box-arrow-in-right fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid #0dcaf0 !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem;">Hadir Pulang</div>
                        <div class="fs-4 fw-bold text-info mt-1" id="stat-hadir-pulang">{{ $totalHadirPulang }}</div>
                    </div>
                    <div class="rounded-circle bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-box-arrow-right fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid #ffc107 !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem;">Izin / Sakit / Alpha</div>
                        <div class="fs-4 fw-bold text-warning mt-1" id="stat-izin-sakit">{{ $totalIzinSakit }}</div>
                    </div>
                    <div class="rounded-circle bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-file-earmark-medical fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
        @else
        <!-- Kartu Kehadiran Khusus Pengguna Sendiri -->
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid {{ $totalSudahAbsen ? '#198754' : '#dc3545' }} !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem;">Status Kehadiran</div>
                        <div class="fs-5 fw-bold {{ $totalSudahAbsen ? 'text-success' : 'text-danger' }} mt-1">
                            <i class="bi {{ $totalSudahAbsen ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }} me-1"></i>
                            {{ $totalSudahAbsen ? 'Sudah Hadir' : 'Belum Absen' }}
                        </div>
                    </div>
                    <div class="rounded-circle {{ $totalSudahAbsen ? 'bg-success text-success' : 'bg-danger text-danger' }} bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-person-check-fill fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid #198754 !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem;">Presensi Datang</div>
                        <div class="fs-5 fw-bold {{ $totalHadirDatang ? 'text-success' : 'text-secondary' }} mt-1">
                            {{ $totalHadirDatang ? 'Tercatat Hadir' : 'Belum Absen' }}
                        </div>
                    </div>
                    <div class="rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-box-arrow-in-right fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid {{ $totalHadirPulang ? '#0dcaf0' : ($totalHadirDatang ? '#ffc107' : '#dc3545') }} !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem;">Presensi Pulang</div>
                        <div class="fs-5 fw-bold {{ $totalHadirPulang ? 'text-info' : 'text-secondary' }} mt-1">
                            {{ $totalHadirPulang ? 'Tercatat Pulang' : ($totalHadirDatang ? 'Belum Pulang' : 'Belum Absen') }}
                        </div>
                    </div>
                    <div class="rounded-circle bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-box-arrow-right fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid #ffc107 !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem;">Kelengkapan</div>
                        <div class="fs-6 fw-bold text-dark mt-1">
                            {{ $totalLengkap ? 'Lengkap (Datang & Pulang)' : ($totalBelumPulang ? 'Belum Presensi Pulang' : ($totalSudahAbsen ? 'Izin / Sakit' : 'Belum Absen')) }}
                        </div>
                    </div>
                    <div class="rounded-circle bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-clock-history fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Attendance Table Card -->
    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
        <div class="card-header bg-white py-3 px-4 d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 border-bottom">
            <div>
                <h5 class="card-title m-0 p-0 fs-5 fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-card-checklist text-primary"></i>
                    @if($isSekretarisOrAdmin)
                        Daftar Kehadiran Pegawai
                    @else
                        Data Presensi Saya &mdash; {{ auth()->user()->name }}
                    @endif
                </h5>
                <p class="text-muted small mb-0 mt-0.5">
                    @if($isSekretarisOrAdmin)
                        Semua data absensi yang tercatat pada sesi ini secara real-time.
                    @else
                        Rekaman kehadiran Anda untuk sesi ini.
                    @endif
                </p>
            </div>
            @if($isSekretarisOrAdmin)
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('presensi.sesi.export', $sesi->id) }}" class="btn btn-success btn-sm d-inline-flex align-items-center gap-1.5 shadow-sm fw-semibold rounded-2 px-3 py-1.5">
                    <i class="bi bi-file-earmark-excel"></i> Export Excel
                </a>
                <button type="button" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1.5 shadow-sm fw-semibold rounded-2 px-3 py-1.5" onclick="window.openManualPresensiModal()">
                    <i class="bi bi-plus-circle"></i> Input Presensi Manual
                </button>
            </div>
            @endif
        </div>

        <div class="card-body p-4">
            @if($isSekretarisOrAdmin)
            <!-- Filter & Bulk Action Toolbar -->
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3 p-3 bg-light rounded-3 border">
                <!-- Filter Buttons Group -->
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="text-muted small fw-bold me-1"><i class="bi bi-funnel text-primary me-1"></i>Filter Presensi:</span>
                    <div class="btn-group shadow-sm" role="group" aria-label="Filter Presensi">
                        <button type="button" class="btn btn-sm btn-primary active btn-filter-status" data-filter="all">
                            <i class="bi bi-people me-1"></i>Semua (<span id="filter-count-all">{{ $totalUsers ?? 0 }}</span>)
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success btn-filter-status" data-filter="sudah">
                            <i class="bi bi-check-circle me-1"></i>Sudah Absen (<span id="filter-count-sudah">{{ $totalSudahAbsen ?? 0 }}</span>)
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-filter-status" data-filter="belum">
                            <i class="bi bi-x-circle me-1"></i>Belum Absen (<span id="filter-count-belum">{{ $totalBelumAbsen ?? 0 }}</span>)
                        </button>
                    </div>

                    <!-- Dropdown Filter Lanjutan -->
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle d-inline-flex align-items-center gap-1 shadow-sm font-medium" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="btnFilterLanjutan">
                            <i class="bi bi-sliders"></i> Filter Khusus: <span id="label-filter-khusus" class="fw-bold">Pilih</span>
                        </button>
                        <ul class="dropdown-menu shadow border-0" style="font-size: 0.84rem; min-width: 260px; border-radius: 10px;">
                            <li class="dropdown-header text-uppercase fw-bold text-muted" style="font-size: 0.72rem;">Filter Detail Kehadiran</li>
                            <li><a class="dropdown-item py-2 filter-detail-item" href="javascript:void(0)" data-filter="sudah_lengkap"><i class="bi bi-check2-all text-success me-2"></i>Sudah Lengkap (Datang & Pulang) <span class="badge bg-success-subtle text-success float-end rounded-pill" id="filter-count-lengkap">{{ $totalLengkap ?? 0 }}</span></a></li>
                            <li><a class="dropdown-item py-2 filter-detail-item" href="javascript:void(0)" data-filter="sudah_datang"><i class="bi bi-box-arrow-in-right text-success me-2"></i>Sudah Absen Datang <span class="badge bg-light text-dark border float-end rounded-pill" id="filter-count-hadir-datang">{{ $totalHadirDatang ?? 0 }}</span></a></li>
                            <li><a class="dropdown-item py-2 filter-detail-item" href="javascript:void(0)" data-filter="sudah_pulang"><i class="bi bi-box-arrow-right text-info me-2"></i>Sudah Absen Pulang <span class="badge bg-light text-dark border float-end rounded-pill" id="filter-count-hadir-pulang">{{ $totalHadirPulang ?? 0 }}</span></a></li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li><a class="dropdown-item py-2 filter-detail-item" href="javascript:void(0)" data-filter="belum_pulang"><i class="bi bi-clock-history text-warning me-2"></i>Belum Absen Pulang <span class="badge bg-warning-subtle text-warning float-end rounded-pill" id="filter-count-belum-pulang">{{ $totalBelumPulang ?? 0 }}</span></a></li>
                            <li><a class="dropdown-item py-2 filter-detail-item" href="javascript:void(0)" data-filter="belum_datang"><i class="bi bi-exclamation-circle text-warning me-2"></i>Belum Absen Datang <span class="badge bg-warning-subtle text-warning float-end rounded-pill" id="filter-count-belum-datang">{{ $totalBelumDatang ?? 0 }}</span></a></li>
                            <li><a class="dropdown-item py-2 filter-detail-item" href="javascript:void(0)" data-filter="belum_semua"><i class="bi bi-person-x text-danger me-2"></i>Belum Absen Sama Sekali <span class="badge bg-danger-subtle text-danger float-end rounded-pill" id="filter-count-belum-semua">{{ $totalBelumAbsen ?? 0 }}</span></a></li>
                        </ul>
                    </div>
                </div>

                <!-- Multi-Select Bulk Action Controls -->
                <div id="bulk-action-toolbar" class="d-flex align-items-center gap-2 flex-wrap" style="display: none !important;">
                    <span class="badge bg-primary px-3 py-2 fs-6 fw-normal rounded-pill shadow-sm text-white">
                        <i class="bi bi-check-square me-1"></i><span id="selected-rows-count" class="fw-bold">0</span> pegawai dipilih
                    </span>
                    <button type="button" class="btn btn-outline-primary btn-sm fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm" id="btn-select-all-dataset" style="border-radius: 6px; min-height: 34px;">
                        <i class="bi bi-check2-all"></i> <span id="label-btn-select-all">Pilih Semua Data (<span id="toolbar-total-count">{{ $totalUsers ?? 0 }}</span>)</span>
                    </button>
                    <button type="button" class="btn btn-warning btn-sm fw-bold d-inline-flex align-items-center gap-1.5 shadow-sm text-dark" onclick="window.openBulkPresensiModal()" style="border-radius: 6px; min-height: 34px;">
                        <i class="bi bi-pencil-square"></i> Ubah Presensi Terpilih
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1" onclick="window.clearSelectedRows()" title="Batal Pilih Semua" style="border-radius: 6px; min-height: 34px;">
                        <i class="bi bi-x-lg me-1"></i>Batal
                    </button>
                </div>
            </div>

            <!-- Banner Notifikasi Pilih Semua Data Lintas Halaman -->
            <div id="select-all-data-banner" class="alert alert-primary py-2.5 px-3 mb-3 d-none align-items-center justify-content-between flex-wrap gap-2 shadow-xs" style="border-radius: 8px; border-left: 4px solid #0d6efd;">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle-fill text-primary fs-5"></i>
                    <span id="select-banner-text" class="small text-dark">
                        Semua <strong><span id="count-page-selected">0</span></strong> pegawai di halaman ini telah dipilih.
                    </span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-primary fw-semibold px-3 py-1 shadow-xs" id="btn-select-all-pages" style="border-radius: 6px; font-size: 0.82rem;">
                        <i class="bi bi-check-all me-1"></i> Pilih Seluruh <span id="banner-total-dataset-count">{{ $totalUsers ?? 0 }}</span> Pegawai
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary px-2.5 py-1 d-none" id="btn-undo-select-all-pages" style="border-radius: 6px; font-size: 0.82rem;">
                        Batal Pilih Semua Data
                    </button>
                </div>
            </div>
            @endif

            <div class="table-responsive">
                <table id="presensi-session-table" class="table table-hover align-middle w-100" style="font-size: 0.88rem;">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 40px;">
                                @if(!empty($canManage) || !empty($isSekretarisOrAdmin))
                                <div class="form-check d-flex justify-content-center m-0">
                                    <input type="checkbox" id="check-all-rows" class="form-check-input" style="cursor: pointer; width: 17px; height: 17px;" title="Pilih Semua Pegawai di Halaman Ini">
                                </div>
                                @else
                                <span class="text-muted small">&bull;</span>
                                @endif
                            </th>
                            <th class="text-center" style="width: 45px;">No</th>
                            <th class="text-center" style="width: 120px;">Tanggal</th>
                            <th>Pegawai</th>
                            <th class="text-center" style="width: 150px;">Presensi Datang</th>
                            <th class="text-center" style="width: 150px;">Presensi Pulang</th>
                            <th>Keterangan / Bukti</th>
                            <th class="text-center" style="width: 85px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<!-- Modal Input / Edit Presensi Manual (Single) -->
<div class="modal fade" id="modalManualPresensi" tabindex="-1" aria-labelledby="modalManualPresensiLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 12px;">
            <div class="modal-header bg-light border-bottom py-3">
                <h5 class="modal-title fw-bold text-dark fs-6" id="modalManualPresensiLabel">
                    <i class="bi bi-person-check text-primary me-2"></i>Input Presensi Manual
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formManualPresensi">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="manual_user_id" class="form-label fw-semibold" style="font-size: 0.85rem;">Pilih Pegawai / Panitia <span class="text-danger">*</span></label>
                        <select class="form-select" id="manual_user_id" name="user_id" required style="border-radius: 8px;">
                            <option value="">-- Cari Nama Pegawai --</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->jabatan ?? '-' }} - {{ $u->unit ?? '-' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="manual_hadir_datang" class="form-label fw-semibold" style="font-size: 0.85rem;">Status Datang</label>
                            <select class="form-select" id="manual_hadir_datang" name="hadir_datang" style="border-radius: 8px;">
                                <option value="">-- Pilih Status --</option>
                                <option value="Hadir">Hadir</option>
                                <option value="Izin">Izin</option>
                                <option value="Sakit">Sakit</option>
                                <option value="Alpha">Alpha</option>
                                <option value="reset">Belum Absen (Reset)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="manual_waktu_datang" class="form-label fw-semibold" style="font-size: 0.85rem;">Waktu Datang (WIB)</label>
                            <input type="time" class="form-control" id="manual_waktu_datang" name="waktu_datang" style="border-radius: 8px;">
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="manual_hadir_pulang" class="form-label fw-semibold" style="font-size: 0.85rem;">Status Pulang</label>
                            <select class="form-select" id="manual_hadir_pulang" name="hadir_pulang" style="border-radius: 8px;">
                                <option value="">-- Pilih Status --</option>
                                <option value="Hadir">Hadir</option>
                                <option value="Izin">Izin</option>
                                <option value="Sakit">Sakit</option>
                                <option value="Alpha">Alpha</option>
                                <option value="reset">Belum Absen (Reset)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="manual_waktu_pulang" class="form-label fw-semibold" style="font-size: 0.85rem;">Waktu Pulang (WIB)</label>
                            <input type="time" class="form-control" id="manual_waktu_pulang" name="waktu_pulang" style="border-radius: 8px;">
                        </div>
                    </div>

                    <div class="mb-2">
                        <label for="manual_catatan" class="form-label fw-semibold" style="font-size: 0.85rem;">Catatan / Keterangan (Opsional)</label>
                        <textarea class="form-control" id="manual_catatan" name="catatan" rows="2" placeholder="Catatan kehadiran atau alasan izin/sakit..." style="border-radius: 8px;"></textarea>
                    </div>
                </div>

                <div class="modal-footer bg-light border-top py-2 px-4">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" style="border-radius: 6px;">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold" id="btnSubmitManualPresensi" style="border-radius: 6px;">
                        <i class="bi bi-check-circle me-1"></i> Simpan Presensi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Bulk Presensi (Multi Select Datang & Pulang) -->
<div class="modal fade" id="modalBulkPresensi" tabindex="-1" aria-labelledby="modalBulkPresensiLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow" style="border-radius: 14px;">
            <div class="modal-header bg-light border-bottom py-3 px-4">
                <div>
                    <h5 class="modal-title fw-bold text-dark fs-6 mb-0" id="modalBulkPresensiLabel">
                        <i class="bi bi-pencil-square text-primary me-2"></i>Ubah Presensi Terpilih (<span id="bulk-modal-count">0</span> Pegawai)
                    </h5>
                    <small class="text-muted">Perbarui status absensi datang dan absensi pulang pegawai secara massal.</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formBulkPresensi">
                @csrf
                <div class="modal-body p-4">
                    <!-- Selected Employees Preview -->
                    <div class="alert alert-light border mb-4 py-2 px-3 rounded-3 shadow-none">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="small fw-bold text-dark"><i class="bi bi-people-fill text-primary me-1"></i>Daftar Pegawai Terpilih:</span>
                            <span class="badge bg-primary-subtle text-primary rounded-pill"><span id="bulk-names-count">0</span> Pegawai</span>
                        </div>
                        <div id="bulk-names-preview" class="small text-muted" style="max-height: 55px; overflow-y: auto;">
                            -
                        </div>
                    </div>

                    <div class="row g-3">
                        <!-- Section Presensi Datang -->
                        <div class="col-md-6">
                            <div class="card h-100 border shadow-none" style="border-radius: 10px; background-color: #f8fafc;">
                                <div class="card-header bg-white border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
                                    <div class="form-check form-switch m-0 d-flex align-items-center gap-2">
                                        <input class="form-check-input" type="checkbox" role="switch" id="switch_update_datang" name="update_datang" value="1" checked style="cursor: pointer; width: 34px; height: 17px;">
                                        <label class="form-check-label fw-bold text-dark small" for="switch_update_datang" style="cursor: pointer;">
                                            <i class="bi bi-box-arrow-in-right text-success me-1"></i>Ubah Absensi Datang
                                        </label>
                                    </div>
                                    <span class="badge bg-success-subtle text-success small" id="badge-datang-status">Diubah</span>
                                </div>
                                <div class="card-body p-3" id="wrapper_fields_datang">
                                    <div class="mb-3">
                                        <label for="bulk_hadir_datang" class="form-label fw-semibold small">Status Kehadiran Datang</label>
                                        <select class="form-select form-select-sm" id="bulk_hadir_datang" name="hadir_datang" style="border-radius: 6px;">
                                            <option value="Hadir" selected>🟢 Hadir</option>
                                            <option value="Izin">🟡 Izin</option>
                                            <option value="Sakit">🔵 Sakit</option>
                                            <option value="Alpha">🔴 Alpha</option>
                                            <option value="reset">⚪ Belum Absen (Kosongkan/Reset)</option>
                                        </select>
                                    </div>
                                    <div class="mb-1">
                                        <label for="bulk_waktu_datang" class="form-label fw-semibold small">Waktu Datang (WIB)</label>
                                        <input type="time" class="form-control form-control-sm" id="bulk_waktu_datang" name="waktu_datang" style="border-radius: 6px;">
                                        <div class="form-text" style="font-size: 0.74rem;">Kosongkan bila ingin otomatis sesuai jam saat ini.</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section Presensi Pulang -->
                        <div class="col-md-6">
                            <div class="card h-100 border shadow-none" style="border-radius: 10px; background-color: #f8fafc;">
                                <div class="card-header bg-white border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
                                    <div class="form-check form-switch m-0 d-flex align-items-center gap-2">
                                        <input class="form-check-input" type="checkbox" role="switch" id="switch_update_pulang" name="update_pulang" value="1" checked style="cursor: pointer; width: 34px; height: 17px;">
                                        <label class="form-check-label fw-bold text-dark small" for="switch_update_pulang" style="cursor: pointer;">
                                            <i class="bi bi-box-arrow-right text-info me-1"></i>Ubah Absensi Pulang
                                        </label>
                                    </div>
                                    <span class="badge bg-info-subtle text-info small" id="badge-pulang-status">Diubah</span>
                                </div>
                                <div class="card-body p-3" id="wrapper_fields_pulang">
                                    <div class="mb-3">
                                        <label for="bulk_hadir_pulang" class="form-label fw-semibold small">Status Kehadiran Pulang</label>
                                        <select class="form-select form-select-sm" id="bulk_hadir_pulang" name="hadir_pulang" style="border-radius: 6px;">
                                            <option value="Hadir" selected>🟢 Hadir</option>
                                            <option value="Izin">🟡 Izin</option>
                                            <option value="Sakit">🔵 Sakit</option>
                                            <option value="Alpha">🔴 Alpha</option>
                                            <option value="reset">⚪ Belum Absen (Kosongkan/Reset)</option>
                                        </select>
                                    </div>
                                    <div class="mb-1">
                                        <label for="bulk_waktu_pulang" class="form-label fw-semibold small">Waktu Pulang (WIB)</label>
                                        <input type="time" class="form-control form-control-sm" id="bulk_waktu_pulang" name="waktu_pulang" style="border-radius: 6px;">
                                        <div class="form-text" style="font-size: 0.74rem;">Kosongkan bila ingin otomatis sesuai jam saat ini.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Catatan / Keterangan -->
                    <div class="card border shadow-none mt-3" style="border-radius: 10px; background-color: #f8fafc;">
                        <div class="card-header bg-white border-bottom py-2 px-3">
                            <div class="form-check form-switch m-0 d-flex align-items-center gap-2">
                                <input class="form-check-input" type="checkbox" role="switch" id="switch_update_catatan" name="update_catatan" value="1" style="cursor: pointer; width: 34px; height: 17px;">
                                <label class="form-check-label fw-bold text-dark small" for="switch_update_catatan" style="cursor: pointer;">
                                    <i class="bi bi-chat-left-text text-secondary me-1"></i>Sertakan Catatan / Keterangan Massal
                                </label>
                            </div>
                        </div>
                        <div class="card-body p-3" id="wrapper_fields_catatan" style="display: none;">
                            <textarea class="form-control form-control-sm" id="bulk_catatan" name="catatan" rows="2" placeholder="Catatan massal (misal: Hadir tugas kepanitiaan, izin tugas dinas luar, dll)..." style="border-radius: 6px;"></textarea>
                        </div>
                    </div>

                    <!-- Section Terapkan ke Semua Absensi Aktif -->
                    <div class="card border shadow-none mt-3" style="border-radius: 10px; background-color: #f0fdf4; border: 1px solid #86efac !important;">
                        <div class="card-body p-3">
                            <div class="form-check form-switch m-0 d-flex align-items-center gap-2">
                                <input class="form-check-input" type="checkbox" role="switch" id="switch_apply_all_active" name="apply_to_all_active" value="1" style="cursor: pointer; width: 34px; height: 18px;">
                                <label class="form-check-label fw-bold text-dark small" for="switch_apply_all_active" style="cursor: pointer;">
                                    <i class="bi bi-broadcast text-success me-1"></i> Terapkan Perubahan ke Seluruh Sesi Absensi yang Sedang Aktif
                                </label>
                            </div>
                            <div class="form-text text-muted mt-1.5" style="font-size: 0.77rem; line-height: 1.4;">
                                Jika diaktifkan, status kehadiran datang dan pulang ini akan disimpan serentak ke <strong>semua sesi absensi yang berstatus Aktif</strong>, bukan hanya sesi ini saja.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-top py-2.5 px-4 d-flex justify-content-between align-items-center">
                    <span class="small text-muted"><i class="bi bi-info-circle me-1"></i>Data presensi akan disimpan secara serentak.</span>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" style="border-radius: 6px;">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm fw-bold shadow-sm" id="btnSubmitBulkPresensi" style="border-radius: 6px;">
                            <i class="bi bi-check2-all me-1"></i> Terapkan Perubahan (<span id="bulk-btn-count">0</span> Pegawai)
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-open Barcode modal if show_qr=1 is passed in URL
    try {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('show_qr') === '1') {
            setTimeout(function() {
                const barcodeBtn = document.querySelector('.btn-barcode-absensi');
                if (barcodeBtn) {
                    window.openBarcodeModal(barcodeBtn);
                }
            }, 300);
        }
    } catch (e) {}

    // State Filter Aktif & Map Multi-Select Pegawai Terpilih
    let currentFilter = 'all';
    let isAllDataSelected = false;
    const selectedUsersMap = new Map(); // userId => userName

    function getTotalDatasetCount() {
        if (presensiTable && presensiTable.page.info()) {
            return presensiTable.page.info().recordsDisplay;
        }
        return {{ $totalUsers ?? 0 }};
    }

    function updateBulkToolbarUI() {
        const totalDataset = getTotalDatasetCount();
        const count = isAllDataSelected ? totalDataset : selectedUsersMap.size;

        $('#selected-rows-count').text(count);
        $('#bulk-modal-count').text(count);
        $('#bulk-names-count').text(count);
        $('#bulk-btn-count').text(count);
        $('#toolbar-total-count').text(totalDataset);
        $('#banner-total-dataset-count').text(totalDataset);

        if (count > 0 || isAllDataSelected) {
            $('#bulk-action-toolbar').attr('style', 'display: flex !important;');
        } else {
            $('#bulk-action-toolbar').attr('style', 'display: none !important;');
            $('#select-all-data-banner').addClass('d-none').removeClass('d-flex');
        }

        if (isAllDataSelected) {
            $('#bulk-names-preview').html('<span class="badge bg-primary text-white py-1 px-2.5 rounded-pill"><i class="bi bi-people-fill me-1"></i> Seluruh data pegawai (' + totalDataset + ' Pegawai) terpilih</span>');
            $('#btn-select-all-dataset').addClass('active btn-primary text-white').removeClass('btn-outline-primary');
            $('#label-btn-select-all').text('Semua Data Terpilih (' + totalDataset + ')');
        } else {
            $('#btn-select-all-dataset').removeClass('active btn-primary text-white').addClass('btn-outline-primary');
            $('#label-btn-select-all').text('Pilih Semua Data (' + totalDataset + ')');
            if (count > 0) {
                const names = Array.from(selectedUsersMap.values()).map(n => `<span class="badge bg-white text-dark border me-1 mb-1 shadow-xs">${n}</span>`).join(' ');
                $('#bulk-names-preview').html(names);
            } else {
                $('#bulk-names-preview').html('<span class="text-muted fst-italic">Tidak ada pegawai yang dipilih.</span>');
            }
        }
    }

    window.clearSelectedRows = function() {
        isAllDataSelected = false;
        selectedUsersMap.clear();
        $('#check-all-rows').prop('checked', false);
        $('.check-row-presensi').prop('checked', false);
        $('#select-all-data-banner').addClass('d-none').removeClass('d-flex');
        $('#btn-select-all-pages').removeClass('d-none');
        $('#btn-undo-select-all-pages').addClass('d-none');
        updateBulkToolbarUI();
    };

    // Inisialisasi DataTables Presensi Sesi
    const presensiTable = $('#presensi-session-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('presensi.sesi.data', $sesi->id) }}",
            data: function(d) {
                d.filter = currentFilter;
            }
        },
        columns: [
            { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false, className: 'text-center' },
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center fw-semibold text-muted' },
            { data: 'tanggal', name: 'tanggal', className: 'text-center', searchable: false },
            { data: 'user_info', name: 'user_info', searchable: true },
            { data: 'status_datang', name: 'status_datang', className: 'text-center', searchable: false },
            { data: 'status_pulang', name: 'status_pulang', className: 'text-center', searchable: false },
            { data: 'catatan_col', name: 'catatan_col', orderable: false, searchable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' },
        ],
        order: [[3, 'asc']],
        pageLength: 25,
        drawCallback: function(settings) {
            let countInPage = 0;
            let checkedInPage = 0;

            $('.check-row-presensi').each(function() {
                countInPage++;
                const uId = $(this).val();
                if (isAllDataSelected || selectedUsersMap.has(uId)) {
                    $(this).prop('checked', true);
                    checkedInPage++;
                } else {
                    $(this).prop('checked', false);
                }
            });

            const totalDisplay = settings.fnRecordsDisplay ? settings.fnRecordsDisplay() : getTotalDatasetCount();
            $('#toolbar-total-count').text(totalDisplay);
            $('#banner-total-dataset-count').text(totalDisplay);

            if (countInPage > 0 && checkedInPage === countInPage) {
                $('#check-all-rows').prop('checked', true);
                // Jika total data lebih besar dari data per halaman saat ini dan belum select all
                if (totalDisplay > countInPage && !isAllDataSelected) {
                    $('#count-page-selected').text(countInPage);
                    $('#select-banner-text').html('Semua <strong>' + countInPage + '</strong> pegawai di halaman ini telah dipilih.');
                    $('#btn-select-all-pages').removeClass('d-none');
                    $('#btn-undo-select-all-pages').addClass('d-none');
                    $('#select-all-data-banner').removeClass('d-none').addClass('d-flex');
                } else if (isAllDataSelected) {
                    $('#select-banner-text').html('<strong>Seluruh ' + totalDisplay + ' data pegawai</strong> dalam sesi ini telah dipilih.');
                    $('#btn-select-all-pages').addClass('d-none');
                    $('#btn-undo-select-all-pages').removeClass('d-none');
                    $('#select-all-data-banner').removeClass('d-none').addClass('d-flex');
                } else {
                    $('#select-all-data-banner').addClass('d-none').removeClass('d-flex');
                }
            } else {
                $('#check-all-rows').prop('checked', false);
                if (!isAllDataSelected) {
                    $('#select-all-data-banner').addClass('d-none').removeClass('d-flex');
                }
            }

            updateBulkToolbarUI();
        },
        language: {
            processing: '<div class="spinner-border spinner-border-sm text-primary me-2"></div> Memuat data presensi...',
            zeroRecords: '<div class="text-center py-4 text-muted"><i class="bi bi-people fs-3 d-block mb-1"></i>Tidak ada data pegawai yang sesuai dengan filter.</div>',
            info: "Menampilkan <strong>_START_-_END_</strong> dari <strong>_TOTAL_</strong> pegawai",
            infoEmpty: "Menampilkan 0 data",
            infoFiltered: "(disaring dari _MAX_ pegawai)",
            search: "Cari Pegawai:",
            paginate: {
                previous: '<i class="bi bi-chevron-left"></i>',
                next: '<i class="bi bi-chevron-right"></i>'
            }
        }
    });

    // Check-all checkbox di header tabel
    $('#check-all-rows').on('change', function() {
        const isChecked = $(this).is(':checked');
        if (!isChecked) {
            isAllDataSelected = false;
        }
        $('.check-row-presensi').each(function() {
            const uId = $(this).val();
            const uName = $(this).data('user-name') || ('Pegawai #' + uId);
            $(this).prop('checked', isChecked);
            if (isChecked) {
                selectedUsersMap.set(uId, uName);
            } else {
                selectedUsersMap.delete(uId);
            }
        });

        const totalDisplay = getTotalDatasetCount();
        const countInPage = $('.check-row-presensi').length;
        if (isChecked && totalDisplay > countInPage) {
            $('#count-page-selected').text(countInPage);
            $('#banner-total-dataset-count').text(totalDisplay);
            $('#select-banner-text').html('Semua <strong>' + countInPage + '</strong> pegawai di halaman ini telah dipilih.');
            $('#btn-select-all-pages').removeClass('d-none');
            $('#btn-undo-select-all-pages').addClass('d-none');
            $('#select-all-data-banner').removeClass('d-none').addClass('d-flex');
        } else {
            $('#select-all-data-banner').addClass('d-none').removeClass('d-flex');
        }

        updateBulkToolbarUI();
    });

    // Tombol Pilih Seluruh Data Pegawai Lintas Halaman
    $('#btn-select-all-pages, #btn-select-all-dataset').on('click', function() {
        isAllDataSelected = true;
        const totalDisplay = getTotalDatasetCount();
        $('.check-row-presensi').prop('checked', true);
        $('#check-all-rows').prop('checked', true);

        $('#select-banner-text').html('<strong>Seluruh ' + totalDisplay + ' data pegawai</strong> dalam sesi ini telah dipilih.');
        $('#btn-select-all-pages').addClass('d-none');
        $('#btn-undo-select-all-pages').removeClass('d-none');
        $('#select-all-data-banner').removeClass('d-none').addClass('d-flex');

        updateBulkToolbarUI();
    });

    // Tombol Batal Pilih Semua Data (Kembali ke pilihan per halaman saja)
    $('#btn-undo-select-all-pages').on('click', function() {
        isAllDataSelected = false;
        const countInPage = $('.check-row-presensi').length;
        $('#select-banner-text').html('Semua <strong>' + countInPage + '</strong> pegawai di halaman ini telah dipilih.');
        $('#btn-select-all-pages').removeClass('d-none');
        $('#btn-undo-select-all-pages').addClass('d-none');
        updateBulkToolbarUI();
    });

    // Individual row checkbox
    $('#presensi-session-table').on('change', '.check-row-presensi', function() {
        if (isAllDataSelected) {
            isAllDataSelected = false;
            $('#btn-select-all-pages').removeClass('d-none');
            $('#btn-undo-select-all-pages').addClass('d-none');
        }

        const uId = $(this).val();
        const uName = $(this).data('user-name') || ('Pegawai #' + uId);
        if ($(this).is(':checked')) {
            selectedUsersMap.set(uId, uName);
        } else {
            selectedUsersMap.delete(uId);
        }

        // Cek apakah seluruh baris di halaman terpilih
        let allChecked = true;
        $('.check-row-presensi').each(function() {
            if (!$(this).is(':checked')) allChecked = false;
        });
        $('#check-all-rows').prop('checked', allChecked);

        if (!allChecked) {
            $('#select-all-data-banner').addClass('d-none').removeClass('d-flex');
        }

        updateBulkToolbarUI();
    });

    // Filter Buttons (Semua, Sudah Absen, Belum Absen)
    $('.btn-filter-status').on('click', function() {
        $('.btn-filter-status').removeClass('btn-primary btn-success btn-danger active').addClass('btn-outline-secondary');
        $(this).removeClass('btn-outline-secondary').addClass('active');

        const filterVal = $(this).data('filter');
        if (filterVal === 'all') {
            $(this).addClass('btn-primary');
            $('#label-filter-khusus').text('Pilih');
        } else if (filterVal === 'sudah') {
            $(this).addClass('btn-success');
            $('#label-filter-khusus').text('Sudah Absen');
        } else if (filterVal === 'belum') {
            $(this).addClass('btn-danger');
            $('#label-filter-khusus').text('Belum Absen');
        }

        currentFilter = filterVal;
        window.clearSelectedRows();
        presensiTable.ajax.reload();
    });

    // Filter Detail Dropdown Items
    $('.filter-detail-item').on('click', function() {
        const filterVal = $(this).data('filter');
        const filterLabel = $(this).text().trim().split(' ')[0] + ' ' + ($(this).text().trim().split(' ')[1] || '');
        
        $('.btn-filter-status').removeClass('btn-primary btn-success btn-danger active').addClass('btn-outline-secondary');
        $('#label-filter-khusus').text($(this).text().trim());

        currentFilter = filterVal;
        window.clearSelectedRows();
        presensiTable.ajax.reload();
    });

    // Switch Handlers di Modal Bulk
    $('#switch_update_datang').on('change', function() {
        if ($(this).is(':checked')) {
            $('#wrapper_fields_datang').slideDown(150);
            $('#badge-datang-status').removeClass('bg-secondary-subtle text-secondary').addClass('bg-success-subtle text-success').text('Diubah');
        } else {
            $('#wrapper_fields_datang').slideUp(150);
            $('#badge-datang-status').removeClass('bg-success-subtle text-success').addClass('bg-secondary-subtle text-secondary').text('Tidak Diubah');
        }
    });

    $('#switch_update_pulang').on('change', function() {
        if ($(this).is(':checked')) {
            $('#wrapper_fields_pulang').slideDown(150);
            $('#badge-pulang-status').removeClass('bg-secondary-subtle text-secondary').addClass('bg-info-subtle text-info').text('Diubah');
        } else {
            $('#wrapper_fields_pulang').slideUp(150);
            $('#badge-pulang-status').removeClass('bg-info-subtle text-info').addClass('bg-secondary-subtle text-secondary').text('Tidak Diubah');
        }
    });

    $('#switch_update_catatan').on('change', function() {
        if ($(this).is(':checked')) {
            $('#wrapper_fields_catatan').slideDown(150);
        } else {
            $('#wrapper_fields_catatan').slideUp(150);
        }
    });

    // Buka Modal Bulk Presensi
    window.openBulkPresensiModal = function() {
        const count = isAllDataSelected ? getTotalDatasetCount() : selectedUsersMap.size;
        if (count === 0) {
            Swal.fire('Info', 'Silakan pilih setidaknya satu pegawai terlebih dahulu melalui kotak centang tabel.', 'info');
            return;
        }

        // Set waktu default sekarang
        const now = new Date();
        const currentTime = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
        $('#bulk_waktu_datang').val(currentTime);
        $('#bulk_waktu_pulang').val(currentTime);

        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalBulkPresensi'));
        modal.show();
    };

    // Submit Bulk Presensi
    $('#formBulkPresensi').on('submit', function(e) {
        e.preventDefault();
        const userIds = isAllDataSelected ? [] : Array.from(selectedUsersMap.keys());
        if (!isAllDataSelected && userIds.length === 0) {
            Swal.fire('Peringatan', 'Tidak ada pegawai yang dipilih.', 'warning');
            return;
        }

        const isDatang = $('#switch_update_datang').is(':checked');
        const isPulang = $('#switch_update_pulang').is(':checked');
        const isCatatan = $('#switch_update_catatan').is(':checked');
        const applyToAllActive = $('#switch_apply_all_active').is(':checked') ? 1 : 0;

        if (!isDatang && !isPulang && !isCatatan) {
            Swal.fire('Peringatan', 'Silakan aktifkan setidaknya satu opsi perubahan (Datang, Pulang, atau Catatan).', 'warning');
            return;
        }

        const btn = $('#btnSubmitBulkPresensi');
        const countLabel = isAllDataSelected ? getTotalDatasetCount() : userIds.length;
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menerapkan Perubahan...');

        const payload = {
            _token: '{{ csrf_token() }}',
            select_all: isAllDataSelected ? 1 : 0,
            filter: currentFilter,
            user_ids: userIds,
            apply_to_all_active: applyToAllActive,
            update_datang: isDatang ? 1 : 0,
            hadir_datang: $('#bulk_hadir_datang').val(),
            waktu_datang: $('#bulk_waktu_datang').val(),
            update_pulang: isPulang ? 1 : 0,
            hadir_pulang: $('#bulk_hadir_pulang').val(),
            waktu_pulang: $('#bulk_waktu_pulang').val(),
            update_catatan: isCatatan ? 1 : 0,
            catatan: $('#bulk_catatan').val(),
        };

        $.ajax({
            url: "{{ route('presensi.sesi.bulk-update', $sesi->id) }}",
            type: 'POST',
            data: payload,
            dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false).html('<i class="bi bi-check2-all me-1"></i> Terapkan Perubahan (' + countLabel + ' Pegawai)');
                if (res.success) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalBulkPresensi'));
                    if (modal) modal.hide();

                    // Update live KPI cards
                    if (res.stats) {
                        $('#stat-total-presensi').text(res.stats.total_presensi);
                        $('#stat-hadir-datang').text(res.stats.hadir_datang);
                        $('#stat-hadir-pulang').text(res.stats.hadir_pulang);
                        $('#stat-izin-sakit').text(res.stats.izin_sakit);

                        // Update filter button counters
                        $('#filter-count-all').text(res.stats.total_users);
                        $('#filter-count-sudah').text(res.stats.sudah_absen);
                        $('#filter-count-belum').text(res.stats.belum_absen);
                        $('#filter-count-lengkap').text(res.stats.lengkap);
                        $('#filter-count-hadir-datang').text(res.stats.hadir_datang);
                        $('#filter-count-hadir-pulang').text(res.stats.hadir_pulang);
                        $('#filter-count-belum-pulang').text(res.stats.belum_pulang);
                        $('#filter-count-belum-datang').text(res.stats.belum_datang);
                        $('#filter-count-belum-semua').text(res.stats.belum_absen);
                    }

                    // Clear selection & reload DataTables
                    window.clearSelectedRows();
                    presensiTable.ajax.reload(null, false);

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil Diperbarui!',
                        text: res.message,
                        timer: 2500,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire('Gagal', res.message || 'Gagal memperbarui data presensi.', 'error');
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="bi bi-check2-all me-1"></i> Terapkan Perubahan (' + countLabel + ' Pegawai)');
                const errMsg = xhr.responseJSON?.message || 'Terjadi kesalahan sistem saat memperbarui presensi massal.';
                Swal.fire('Error', errMsg, 'error');
            }
        });
    });

    // Open Manual Presensi Modal (New)
    window.openManualPresensiModal = function() {
        $('#formManualPresensi')[0].reset();
        $('#manual_user_id').prop('disabled', false).val('');
        $('#modalManualPresensiLabel').html('<i class="bi bi-person-check text-primary me-2"></i>Input Presensi Manual');
        $('#btnSubmitManualPresensi').html('<i class="bi bi-check-circle me-1"></i> Simpan Presensi');

        // Set default time to current time
        const now = new Date();
        const currentTime = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
        $('#manual_waktu_datang').val(currentTime);
        $('#manual_hadir_datang').val('Hadir');

        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalManualPresensi'));
        modal.show();
    };

    // Open Manual Presensi Modal (Edit)
    window.editPresensiRow = function(data) {
        $('#formManualPresensi')[0].reset();
        $('#manual_user_id').val(data.user_id).prop('disabled', true);
        $('#manual_hadir_datang').val(data.hadir_datang || '');
        $('#manual_waktu_datang').val(data.waktu_datang || '');
        $('#manual_hadir_pulang').val(data.hadir_pulang || '');
        $('#manual_waktu_pulang').val(data.waktu_pulang || '');
        $('#manual_catatan').val(data.catatan || '');

        $('#modalManualPresensiLabel').html('<i class="bi bi-pencil-square text-primary me-2"></i>Ubah Data Presensi Pegawai');
        $('#btnSubmitManualPresensi').html('<i class="bi bi-check-circle me-1"></i> Perbarui Presensi');

        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalManualPresensi'));
        modal.show();
    };

    // Submit Manual Presensi
    $('#formManualPresensi').on('submit', function(e) {
        e.preventDefault();
        $('#manual_user_id').prop('disabled', false);
        const formData = $(this).serialize();

        const btn = $('#btnSubmitManualPresensi');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

        $.ajax({
            url: "{{ route('presensi.sesi.manual', $sesi->id) }}",
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> Simpan Presensi');
                if (res.success) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalManualPresensi'));
                    if (modal) modal.hide();
                    presensiTable.ajax.reload(null, false);

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: res.message,
                        timer: 1800,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire('Gagal', res.message || 'Gagal menyimpan presensi.', 'error');
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> Simpan Presensi');
                const errMsg = xhr.responseJSON?.message || 'Terjadi kesalahan sistem.';
                Swal.fire('Error', errMsg, 'error');
            }
        });
    });

    // Delete Presensi Row
    window.deletePresensiRow = function(id) {
        Swal.fire({
            title: 'Hapus data presensi?',
            text: 'Catatan kehadiran pegawai ini pada sesi ini akan dihapus permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-trash"></i> Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('presensi/presensi') }}/" + id,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (res.success) {
                            presensiTable.ajax.reload(null, false);
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus!',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire('Gagal', res.message || 'Gagal menghapus data.', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Terjadi kesalahan sistem saat menghapus.', 'error');
                    }
                });
            }
        });
    };
});
</script>
@endpush
