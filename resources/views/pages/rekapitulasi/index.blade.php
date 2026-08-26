@extends('layouts.dashboard.template')

@section('title', 'Rekapitulasi Capaian & Diagnosis Kendala')

@section('content')
<style>
    .card-header-green {
        background-color: #15432d;
        color: #ffffff;
        padding: 10px 16px;
        border-top-left-radius: 6px;
        border-top-right-radius: 6px;
        font-weight: 600;
        font-size: 0.95rem;
    }
    .rekap-card {
        transition: transform 0.2s;
    }
    .rekap-card:hover {
        transform: translateY(-2px);
    }
    .rekap-tab-nav .nav-link {
        border-radius: 10px 10px 0 0;
        font-weight: 600;
        font-size: 0.9rem;
        color: #6c757d;
        border: 2px solid transparent;
        padding: 10px 22px;
        transition: all 0.2s;
    }
    .rekap-tab-nav .nav-link.active {
        color: #15432d;
        border-color: #15432d #15432d #fff;
        background: #fff;
    }
    .rekap-tab-nav .nav-link:not(.active):hover {
        color: #15432d;
        background: #f0f8f4;
    }
    .tab-icon { font-size: 1rem; margin-right: 6px; }
</style>

<div class="pagetitle">
    <h1><i class="bi bi-pie-chart-fill text-success me-2"></i>Rekapitulasi Capaian & Analisis Kendala Kinerja</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Rekapitulasi & Analisis Kendala</li>
        </ol>
    </nav>
</div>

<section class="section">

    {{-- ===== SHARED FILTER ===== --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header-green">
            <i class="bi bi-filter-square me-1"></i> Filter & Diagnosis Kinerja
        </div>
        <div class="card-body pt-3">
            <form id="filter-form" class="row g-3 align-items-end">
                <div class="col-md-6 col-lg-3">
                    <label for="filter_periode" class="form-label fw-semibold text-secondary small">Periode Akademik</label>
                    <select name="periode_akademik_id" id="filter_periode" class="form-select select2-simple">
                        <option value="">Semua Periode</option>
                        @foreach($periodeAkademiks as $p)
                            <option value="{{ $p->id }}" {{ $p->id == $defaultPeriodeId ? 'selected' : '' }}>
                                {{ $p->nama_periode }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 col-lg-3">
                    <label for="filter_status" class="form-label fw-semibold text-secondary small">Filter Status / Kendala</label>
                    <select name="status_filter" id="filter_status" class="form-select select2-simple">
                        <option value="">Semua Status</option>
                        <option value="belum_selesai" class="fw-bold text-danger">⚠️ Belum Selesai (Semua Kendala)</option>
                        <option value="terlambat" class="fw-bold text-danger">🚨 Terlambat (Lewat Estimasi)</option>
                        <option value="proses" class="text-primary">▶️ Sedang Proses</option>
                        <option value="belum_dimulai" class="text-secondary">⏳ Belum Dimulai</option>
                        <option value="selesai" class="text-success">✅ Sudah Selesai</option>
                    </select>
                </div>

                <div class="col-md-6 col-lg-3" id="wrapper_filter_kategori">
                    <label for="filter_kategori" class="form-label fw-semibold text-secondary small">Kategori Kegiatan</label>
                    <select name="kategori" id="filter_kategori" class="form-select select2-simple">
                        <option value="">Semua Kategori</option>
                        <option value="Rencana Kerja">Rencana Kerja Utama</option>
                        <option value="Kepanitiaan">Kepanitiaan</option>
                        <option value="Insidentil">Insidentil</option>
                    </select>
                </div>

                @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isPimpinanRektorat()))
                <div class="col-md-6 col-lg-3">
                    <label for="filter_jabatan" class="form-label fw-semibold text-secondary small">Pegawai / Jabatan</label>
                    <select name="jabatan" id="filter_jabatan" class="form-select select2-simple">
                        <option value="">Semua Jabatan</option>
                        @foreach($usersWithJabatan as $u)
                            <option value="{{ $u->id }}" {{ ($u->id == auth()->user()->id && !auth()->user()->isPimpinanRektorat()) ? 'selected' : '' }}>
                                {{ $u->jabatan }} - {{ $u->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="col-12 col-lg-auto d-flex align-items-end">
                    <button type="button" id="btn-reset" class="btn btn-secondary btn-sm px-3">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== TAB NAVIGATION ===== --}}
    <ul class="nav rekap-tab-nav border-bottom mb-0" id="rekapTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-keg-btn" data-bs-toggle="tab" data-bs-target="#tab-keg" type="button" role="tab">
                <i class="bi bi-grid-fill tab-icon"></i> Semua Kegiatan (Diagnosis)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-rk-btn" data-bs-toggle="tab" data-bs-target="#tab-rk" type="button" role="tab">
                <i class="bi bi-journal-text tab-icon"></i> Rencana Kerja Utama
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-pan-btn" data-bs-toggle="tab" data-bs-target="#tab-pan" type="button" role="tab">
                <i class="bi bi-people-fill tab-icon"></i> Kepanitiaan
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-ins-btn" data-bs-toggle="tab" data-bs-target="#tab-ins" type="button" role="tab">
                <i class="bi bi-lightning-charge-fill tab-icon"></i> Insidentil
            </button>
        </li>
    </ul>

    <div class="tab-content bg-white border border-top-0 rounded-bottom shadow-sm p-4" id="rekapTabContent">

        {{-- ============================================================ --}}
        {{-- TAB 0: SEMUA KEGIATAN (MASTER DIAGNOSIS)                      --}}
        {{-- ============================================================ --}}
        <div class="tab-pane fade show active" id="tab-keg" role="tabpanel">
            {{-- Stat Cards --}}
            <div class="row row-cols-2 row-cols-md-3 row-cols-xl-5 g-3 mb-4 mt-1">
                <div class="col">
                    <div class="card rekap-card shadow-sm border-0 border-start border-4 border-info h-100 mb-0">
                        <div class="card-body p-3 d-flex align-items-center">
                            <div class="rounded-circle bg-info bg-opacity-10 p-2 me-2 text-info d-flex align-items-center justify-content-center" style="width:42px;height:42px;">
                                <i class="bi bi-grid-fill fs-5"></i>
                            </div>
                            <div>
                                <div class="text-secondary small fw-bold mb-0">TOTAL</div>
                                <h4 class="mb-0 fw-bold text-dark" id="keg-stat-total">0</h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card rekap-card shadow-sm border-0 border-start border-4 border-secondary h-100 mb-0">
                        <div class="card-body p-3 d-flex align-items-center">
                            <div class="rounded-circle bg-secondary bg-opacity-10 p-2 me-2 text-secondary d-flex align-items-center justify-content-center" style="width:42px;height:42px;">
                                <i class="bi bi-hourglass-split fs-5"></i>
                            </div>
                            <div>
                                <div class="text-secondary small fw-bold mb-0">BELUM MULAI</div>
                                <h4 class="mb-0 fw-bold text-secondary" id="keg-stat-belum">0</h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card rekap-card shadow-sm border-0 border-start border-4 border-primary h-100 mb-0">
                        <div class="card-body p-3 d-flex align-items-center">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2 text-primary d-flex align-items-center justify-content-center" style="width:42px;height:42px;">
                                <i class="bi bi-play-circle fs-5"></i>
                            </div>
                            <div>
                                <div class="text-secondary small fw-bold mb-0">PROSES</div>
                                <h4 class="mb-0 fw-bold text-primary" id="keg-stat-proses">0</h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card rekap-card shadow-sm border-0 border-start border-4 border-danger h-100 mb-0">
                        <div class="card-body p-3 d-flex align-items-center">
                            <div class="rounded-circle bg-danger bg-opacity-10 p-2 me-2 text-danger d-flex align-items-center justify-content-center" style="width:42px;height:42px;">
                                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                            </div>
                            <div>
                                <div class="text-danger small fw-bold mb-0">TERLAMBAT</div>
                                <h4 class="mb-0 fw-bold text-danger" id="keg-stat-terlambat">0</h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card rekap-card shadow-sm border-0 border-start border-4 border-success h-100 mb-0">
                        <div class="card-body p-3 d-flex align-items-center">
                            <div class="rounded-circle bg-success bg-opacity-10 p-2 me-2 text-success d-flex align-items-center justify-content-center" style="width:42px;height:42px;">
                                <i class="bi bi-check-circle fs-5"></i>
                            </div>
                            <div>
                                <div class="text-secondary small fw-bold mb-0">SELESAI</div>
                                <h4 class="mb-0 fw-bold text-success" id="keg-stat-selesai">0</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Chart + Progress --}}
            <div class="row mb-4">
                <div class="col-lg-5 mb-3 mb-lg-0">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header-green"><i class="bi bi-pie-chart-fill me-1"></i> Perbandingan Status & Kendala</div>
                        <div class="card-body pt-3 d-flex flex-column justify-content-center align-items-center" style="min-height:260px;">
                            <div style="width:200px;height:200px;"><canvas id="keg-chartStatus"></canvas></div>
                            <div class="mt-2 text-center text-muted small" id="keg-chart-no-data" style="display:none;"><i class="bi bi-info-circle me-1"></i> Tidak ada data</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header-green"><i class="bi bi-percent me-1"></i> Persentase Capaian Semua Kegiatan</div>
                        <div class="card-body pt-3 d-flex flex-column justify-content-between">
                            <div>
                                <h6 class="fw-bold text-secondary mb-3">Persentase Capaian Kerja (Selesai vs Total)</h6>
                                <div class="d-flex align-items-baseline mb-3">
                                    <span class="fs-1 fw-bold text-success" id="keg-big-percent">0%</span>
                                    <span class="ms-2 text-secondary">Tugas Terselesaikan</span>
                                </div>
                                <div class="progress mb-4" style="height:18px;border-radius:9px;">
                                    <div id="keg-bar-selesai" class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width:0%">0%</div>
                                    <div id="keg-bar-proses" class="progress-bar bg-primary progress-bar-striped progress-bar-animated" role="progressbar" style="width:0%">0%</div>
                                    <div id="keg-bar-belum" class="progress-bar bg-secondary progress-bar-striped progress-bar-animated" role="progressbar" style="width:0%">0%</div>
                                </div>
                            </div>
                            <div class="border rounded p-3 bg-light">
                                <div class="row text-center">
                                    <div class="col-4 border-end">
                                        <div class="text-secondary small fw-bold">BELUM MULAI</div>
                                        <div class="fw-semibold text-secondary" id="keg-percent-belum">0%</div>
                                    </div>
                                    <div class="col-4 border-end">
                                        <div class="text-primary small fw-bold">SEDANG PROSES</div>
                                        <div class="fw-semibold text-primary" id="keg-percent-proses">0%</div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-success small fw-bold">SELESAI</div>
                                        <div class="fw-semibold text-success" id="keg-percent-selesai">0%</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- DataTable --}}
            <div class="card shadow-sm border-0">
                <div class="card-header-green d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-list-check me-1"></i> Rincian Seluruh Kegiatan & Diagnosis Kendala</span>
                </div>
                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table id="keg-table" class="table table-hover w-100 align-middle" style="font-size:0.88rem;">
                            <thead class="table-light text-secondary fw-semibold">
                                <tr>
                                    <th style="width:40px;" class="text-center">No</th>
                                    <th style="width:110px;">Kategori Modul</th>
                                    <th style="min-width:200px;">Uraian Tugas</th>
                                    <th style="min-width:140px;">Pembuat & Rekan</th>
                                    <th style="min-width:130px;">Estimasi Waktu</th>
                                    <th style="width:90px;" class="text-center">Status</th>
                                    <th style="min-width:240px;">Analisis Penyebab / Kendala Belum Selesai</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>{{-- end tab-keg --}}

        {{-- ============================================================ --}}
        {{-- TAB 1: RENCANA KERJA UTAMA                                   --}}
        {{-- ============================================================ --}}
        <div class="tab-pane fade" id="tab-rk" role="tabpanel">
            {{-- Stat Cards --}}
            <div class="row g-3 mb-4 mt-1">
                <div class="col-6 col-lg-3">
                    <div class="card rekap-card shadow-sm border-0 border-start border-4 border-info h-100 mb-0">
                        <div class="card-body p-3 d-flex align-items-center">
                            <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3 text-info d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                                <i class="bi bi-journal-text fs-4"></i>
                            </div>
                            <div><div class="text-secondary small fw-bold mb-0">TOTAL TUGAS</div>
                            <h4 class="mb-0 fw-bold text-dark" id="rk-stat-total">0</h4></div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card rekap-card shadow-sm border-0 border-start border-4 border-secondary h-100 mb-0">
                        <div class="card-body p-3 d-flex align-items-center">
                            <div class="rounded-circle bg-secondary bg-opacity-10 p-3 me-3 text-secondary d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                                <i class="bi bi-x-circle fs-4"></i>
                            </div>
                            <div><div class="text-secondary small fw-bold mb-0">BELUM DIMULAI</div>
                            <h4 class="mb-0 fw-bold text-secondary" id="rk-stat-belum">0</h4></div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card rekap-card shadow-sm border-0 border-start border-4 border-primary h-100 mb-0">
                        <div class="card-body p-3 d-flex align-items-center">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3 text-primary d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                                <i class="bi bi-play-circle fs-4"></i>
                            </div>
                            <div><div class="text-secondary small fw-bold mb-0">SEDANG PROSES</div>
                            <h4 class="mb-0 fw-bold text-primary" id="rk-stat-proses">0</h4></div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card rekap-card shadow-sm border-0 border-start border-4 border-success h-100 mb-0">
                        <div class="card-body p-3 d-flex align-items-center">
                            <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3 text-success d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                                <i class="bi bi-check-circle fs-4"></i>
                            </div>
                            <div><div class="text-secondary small fw-bold mb-0">SUDAH SELESAI</div>
                            <h4 class="mb-0 fw-bold text-success" id="rk-stat-selesai">0</h4></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Chart + Progress --}}
            <div class="row mb-4">
                <div class="col-lg-5 mb-3 mb-lg-0">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header-green"><i class="bi bi-pie-chart-fill me-1"></i> Perbandingan Status</div>
                        <div class="card-body pt-3 d-flex flex-column justify-content-center align-items-center" style="min-height:260px;">
                            <div style="width:200px;height:200px;"><canvas id="rk-chartStatus"></canvas></div>
                            <div class="mt-2 text-center text-muted small" id="rk-chart-no-data" style="display:none;"><i class="bi bi-info-circle me-1"></i> Tidak ada data</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header-green"><i class="bi bi-percent me-1"></i> Persentase Capaian</div>
                        <div class="card-body pt-3 d-flex flex-column justify-content-between">
                            <div>
                                <h6 class="fw-bold text-secondary mb-3">Persentase Capaian Kerja (Selesai vs Total)</h6>
                                <div class="d-flex align-items-baseline mb-3">
                                    <span class="fs-1 fw-bold text-success" id="rk-big-percent">0%</span>
                                    <span class="ms-2 text-secondary">Tugas Terselesaikan</span>
                                </div>
                                <div class="progress mb-4" style="height:18px;border-radius:9px;">
                                    <div id="rk-bar-selesai" class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width:0%">0%</div>
                                    <div id="rk-bar-proses" class="progress-bar bg-primary progress-bar-striped progress-bar-animated" role="progressbar" style="width:0%">0%</div>
                                    <div id="rk-bar-belum" class="progress-bar bg-secondary progress-bar-striped progress-bar-animated" role="progressbar" style="width:0%">0%</div>
                                </div>
                            </div>
                            <div class="border rounded p-3 bg-light">
                                <div class="row text-center">
                                    <div class="col-4 border-end">
                                        <div class="text-secondary small fw-bold">BELUM MULAI</div>
                                        <div class="fw-semibold text-secondary" id="rk-percent-belum">0%</div>
                                    </div>
                                    <div class="col-4 border-end">
                                        <div class="text-primary small fw-bold">SEDANG PROSES</div>
                                        <div class="fw-semibold text-primary" id="rk-percent-proses">0%</div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-success small fw-bold">SELESAI</div>
                                        <div class="fw-semibold text-success" id="rk-percent-selesai">0%</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- DataTable --}}
            <div class="card shadow-sm border-0">
                <div class="card-header-green"><i class="bi bi-list-check me-1"></i> Rincian Rencana Kerja Utama</div>
                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table id="rk-table" class="table table-hover w-100" style="font-size:0.9rem;">
                            <thead class="table-light text-secondary fw-semibold">
                                <tr>
                                    <th style="width:50px;">No</th>
                                    <th>Uraian Tugas</th>
                                    <th>Pembuat</th>
                                    <th>Rekan Kerja (Tag)</th>
                                    <th style="width:120px;">Status</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>{{-- end tab-rk --}}

        {{-- ============================================================ --}}
        {{-- TAB 2: KEPANITIAAN                                           --}}
        {{-- ============================================================ --}}
        <div class="tab-pane fade" id="tab-pan" role="tabpanel">
            {{-- Stat Cards --}}
            <div class="row g-3 mb-4 mt-1">
                <div class="col-6 col-lg-3">
                    <div class="card rekap-card shadow-sm border-0 border-start border-4 border-info h-100 mb-0">
                        <div class="card-body p-3 d-flex align-items-center">
                            <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3 text-info d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                                <i class="bi bi-people-fill fs-4"></i>
                            </div>
                            <div><div class="text-secondary small fw-bold mb-0">TOTAL TUGAS</div>
                            <h4 class="mb-0 fw-bold text-dark" id="pan-stat-total">0</h4></div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card rekap-card shadow-sm border-0 border-start border-4 border-secondary h-100 mb-0">
                        <div class="card-body p-3 d-flex align-items-center">
                            <div class="rounded-circle bg-secondary bg-opacity-10 p-3 me-3 text-secondary d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                                <i class="bi bi-x-circle fs-4"></i>
                            </div>
                            <div><div class="text-secondary small fw-bold mb-0">BELUM DIMULAI</div>
                            <h4 class="mb-0 fw-bold text-secondary" id="pan-stat-belum">0</h4></div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card rekap-card shadow-sm border-0 border-start border-4 border-primary h-100 mb-0">
                        <div class="card-body p-3 d-flex align-items-center">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3 text-primary d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                                <i class="bi bi-play-circle fs-4"></i>
                            </div>
                            <div><div class="text-secondary small fw-bold mb-0">SEDANG PROSES</div>
                            <h4 class="mb-0 fw-bold text-primary" id="pan-stat-proses">0</h4></div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card rekap-card shadow-sm border-0 border-start border-4 border-success h-100 mb-0">
                        <div class="card-body p-3 d-flex align-items-center">
                            <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3 text-success d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                                <i class="bi bi-check-circle fs-4"></i>
                            </div>
                            <div><div class="text-secondary small fw-bold mb-0">SUDAH SELESAI</div>
                            <h4 class="mb-0 fw-bold text-success" id="pan-stat-selesai">0</h4></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Chart + Progress --}}
            <div class="row mb-4">
                <div class="col-lg-5 mb-3 mb-lg-0">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header-green"><i class="bi bi-pie-chart-fill me-1"></i> Perbandingan Status</div>
                        <div class="card-body pt-3 d-flex flex-column justify-content-center align-items-center" style="min-height:260px;">
                            <div style="width:200px;height:200px;"><canvas id="pan-chartStatus"></canvas></div>
                            <div class="mt-2 text-center text-muted small" id="pan-chart-no-data" style="display:none;"><i class="bi bi-info-circle me-1"></i> Tidak ada data</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header-green"><i class="bi bi-percent me-1"></i> Persentase Capaian</div>
                        <div class="card-body pt-3 d-flex flex-column justify-content-between">
                            <div>
                                <h6 class="fw-bold text-secondary mb-3">Persentase Capaian Kerja (Selesai vs Total)</h6>
                                <div class="d-flex align-items-baseline mb-3">
                                    <span class="fs-1 fw-bold text-success" id="pan-big-percent">0%</span>
                                    <span class="ms-2 text-secondary">Tugas Terselesaikan</span>
                                </div>
                                <div class="progress mb-4" style="height:18px;border-radius:9px;">
                                    <div id="pan-bar-selesai" class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width:0%">0%</div>
                                    <div id="pan-bar-proses" class="progress-bar bg-primary progress-bar-striped progress-bar-animated" role="progressbar" style="width:0%">0%</div>
                                    <div id="pan-bar-belum" class="progress-bar bg-secondary progress-bar-striped progress-bar-animated" role="progressbar" style="width:0%">0%</div>
                                </div>
                            </div>
                            <div class="border rounded p-3 bg-light">
                                <div class="row text-center">
                                    <div class="col-4 border-end">
                                        <div class="text-secondary small fw-bold">BELUM MULAI</div>
                                        <div class="fw-semibold text-secondary" id="pan-percent-belum">0%</div>
                                    </div>
                                    <div class="col-4 border-end">
                                        <div class="text-primary small fw-bold">SEDANG PROSES</div>
                                        <div class="fw-semibold text-primary" id="pan-percent-proses">0%</div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-success small fw-bold">SELESAI</div>
                                        <div class="fw-semibold text-success" id="pan-percent-selesai">0%</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- DataTable --}}
            <div class="card shadow-sm border-0">
                <div class="card-header-green"><i class="bi bi-list-check me-1"></i> Rincian Rencana Kerja Kepanitiaan</div>
                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table id="pan-table" class="table table-hover w-100" style="font-size:0.9rem;">
                            <thead class="table-light text-secondary fw-semibold">
                                <tr>
                                    <th style="width:50px;">No</th>
                                    <th>Uraian Tugas Kepanitiaan</th>
                                    <th>Pembuat</th>
                                    <th>Rekan Kerja</th>
                                    <th style="width:120px;">Status</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>{{-- end tab-pan --}}

        {{-- ============================================================ --}}
        {{-- TAB 3: INSIDENTIL                                            --}}
        {{-- ============================================================ --}}
        <div class="tab-pane fade" id="tab-ins" role="tabpanel">
            {{-- Stat Cards --}}
            <div class="row g-3 mb-4 mt-1">
                <div class="col-6 col-lg-3">
                    <div class="card rekap-card shadow-sm border-0 border-start border-4 border-info h-100 mb-0">
                        <div class="card-body p-3 d-flex align-items-center">
                            <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3 text-info d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                                <i class="bi bi-lightning-charge-fill fs-4"></i>
                            </div>
                            <div><div class="text-secondary small fw-bold mb-0">TOTAL TUGAS</div>
                            <h4 class="mb-0 fw-bold text-dark" id="ins-stat-total">0</h4></div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card rekap-card shadow-sm border-0 border-start border-4 border-secondary h-100 mb-0">
                        <div class="card-body p-3 d-flex align-items-center">
                            <div class="rounded-circle bg-secondary bg-opacity-10 p-3 me-3 text-secondary d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                                <i class="bi bi-x-circle fs-4"></i>
                            </div>
                            <div><div class="text-secondary small fw-bold mb-0">BELUM DIMULAI</div>
                            <h4 class="mb-0 fw-bold text-secondary" id="ins-stat-belum">0</h4></div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card rekap-card shadow-sm border-0 border-start border-4 border-primary h-100 mb-0">
                        <div class="card-body p-3 d-flex align-items-center">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3 text-primary d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                                <i class="bi bi-play-circle fs-4"></i>
                            </div>
                            <div><div class="text-secondary small fw-bold mb-0">SEDANG PROSES</div>
                            <h4 class="mb-0 fw-bold text-primary" id="ins-stat-proses">0</h4></div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card rekap-card shadow-sm border-0 border-start border-4 border-success h-100 mb-0">
                        <div class="card-body p-3 d-flex align-items-center">
                            <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3 text-success d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                                <i class="bi bi-check-circle fs-4"></i>
                            </div>
                            <div><div class="text-secondary small fw-bold mb-0">SUDAH SELESAI</div>
                            <h4 class="mb-0 fw-bold text-success" id="ins-stat-selesai">0</h4></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Chart + Progress --}}
            <div class="row mb-4">
                <div class="col-lg-5 mb-3 mb-lg-0">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header-green"><i class="bi bi-pie-chart-fill me-1"></i> Perbandingan Status Insidentil</div>
                        <div class="card-body pt-3 d-flex flex-column justify-content-center align-items-center" style="min-height:260px;">
                            <div style="width:200px;height:200px;"><canvas id="ins-chartStatus"></canvas></div>
                            <div class="mt-2 text-center text-muted small" id="ins-chart-no-data" style="display:none;"><i class="bi bi-info-circle me-1"></i> Tidak ada data</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header-green"><i class="bi bi-percent me-1"></i> Persentase Capaian Insidentil</div>
                        <div class="card-body pt-3 d-flex flex-column justify-content-between">
                            <div>
                                <h6 class="fw-bold text-secondary mb-3">Persentase Capaian Kerja (Selesai vs Total)</h6>
                                <div class="d-flex align-items-baseline mb-3">
                                    <span class="fs-1 fw-bold text-success" id="ins-percent-text">0%</span>
                                    <span class="ms-2 text-secondary">Tugas Terselesaikan</span>
                                </div>
                                <div class="progress mb-4" style="height:18px;border-radius:9px;">
                                    <div id="ins-progress-bar" class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width:0%">0%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- DataTable --}}
            <div class="card shadow-sm border-0">
                <div class="card-header-green"><i class="bi bi-list-check me-1"></i> Rincian Tugas Insidentil</div>
                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle w-100" id="ins-table">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%" class="text-center">No</th>
                                    <th width="25%">Pembuat</th>
                                    <th width="30%">Uraian Tugas Insidentil</th>
                                    <th width="20%">Rekan Kerja</th>
                                    <th width="10%" class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>{{-- end tab-ins --}}

    </div>{{-- end tab-content --}}
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {

    // ---- Select2 ----
    if ($.fn.select2) {
        $('.select2-simple').select2({ theme: 'bootstrap-5', width: '100%' });
    }

    // ---- Helper: build chart ----
    function makeChart(canvasId) {
        const el = document.getElementById(canvasId);
        if (!el) return null;
        const ctx = el.getContext('2d');
        return new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Belum Dimulai', 'Proses', 'Selesai'],
                datasets: [{ data: [0,0,0], backgroundColor: ['#6c757d','#0d6efd','#198754'], borderWidth: 2, hoverOffset: 4 }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 12, font: { size: 11 } } } },
                cutout: '65%'
            }
        });
    }

    // ---- Helper: update stats UI ----
    function updateStats(prefix, r) {
        $('#' + prefix + '-stat-total').text(r.total || 0);
        $('#' + prefix + '-stat-belum').text(r.belum || 0);
        $('#' + prefix + '-stat-proses').text(r.proses || 0);
        $('#' + prefix + '-stat-selesai').text(r.selesai || 0);
        if ($('#' + prefix + '-stat-terlambat').length) {
            $('#' + prefix + '-stat-terlambat').text(r.terlambat || 0);
        }
    }

    function updateProgress(prefix, r, chart, noDataId) {
        let pBelum  = r.total > 0 ? Math.round((r.belum  / r.total) * 100) : 0;
        let pProses = r.total > 0 ? Math.round((r.proses / r.total) * 100) : 0;
        let pSelesai = r.percent;
        if ($('#' + prefix + '-big-percent').length) {
            $('#' + prefix + '-big-percent').text(pSelesai + '%');
            $('#' + prefix + '-bar-selesai').css('width', pSelesai + '%').text(pSelesai >= 5 ? pSelesai + '%' : '');
            $('#' + prefix + '-bar-proses').css('width', pProses + '%').text(pProses >= 5 ? pProses + '%' : '');
            $('#' + prefix + '-bar-belum').css('width', pBelum + '%').text(pBelum >= 5 ? pBelum + '%' : '');
            $('#' + prefix + '-percent-belum').text(pBelum + '%');
            $('#' + prefix + '-percent-proses').text(pProses + '%');
            $('#' + prefix + '-percent-selesai').text(pSelesai + '%');
        }
        if (chart) {
            if (r.total > 0) {
                $('#' + prefix + '-chartStatus').show();
                $('#' + noDataId).hide();
                chart.data.datasets[0].data = [r.belum, r.proses, r.selesai];
                chart.update();
            } else {
                $('#' + prefix + '-chartStatus').hide();
                $('#' + noDataId).show();
            }
        }
    }

    // ---- Charts ----
    const kegChart = makeChart('keg-chartStatus');
    const rkChart  = makeChart('rk-chartStatus');
    const panChart = makeChart('pan-chartStatus');
    const insChart = makeChart('ins-chartStatus');

    // ---- Tab 0: Semua Kegiatan ----
    const kegTable = $('#keg-table').DataTable({
        processing: true, serverSide: true,
        ajax: {
            url: "{{ route('rekapitulasi-kegiatan.data') }}",
            data: function(d) {
                d.periode_akademik_id = $('#filter_periode').val();
                d.status_filter = $('#filter_status').val();
                d.kategori = $('#filter_kategori').val();
                d.jabatan = $('#filter_jabatan').val();
            }
        },
        columns: [
            { data: null, orderable: false, searchable: false, className: 'text-center',
              render: function(data, type, row, meta) { return meta.row + meta.settings._iDisplayStart + 1; } },
            { data: 'kategori_badge', name: 'kategori', className: 'text-center' },
            { data: 'uraian_tugas', name: 'uraian_tugas',
              render: function(data) { return '<div class="fw-bold text-dark text-wrap">' + (data || '-') + '</div>'; } },
            { data: 'pembuat', name: 'user.name' },
            { data: 'estimasi_waktu', name: 'estimasi_tanggal_selesai' },
            { data: 'status_badge', name: 'status', className: 'text-center' },
            { data: 'analisis_kendala', name: 'status', orderable: false, searchable: false }
        ],
        language: { url: "https://cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json" },
        order: [[0, 'desc']],
        drawCallback: function() {
            let json = this.api().ajax.json();
            if (json && json.rekap) {
                updateStats('keg', json.rekap);
                updateProgress('keg', json.rekap, kegChart, 'keg-chart-no-data');
            }
        }
    });

    // ---- Tab 1: Rencana Kerja ----
    let rkInitialized = false;
    let rkTable;

    // ---- Tab 2: Kepanitiaan ----
    let panInitialized = false;
    let panTable;

    // ---- Tab 3: Insidentil ----
    let insInitialized = false;
    let insTable;

    // Lazy-load tabs on first show
    $('#tab-rk-btn').on('shown.bs.tab', function() {
        if (!rkInitialized) {
            rkInitialized = true;
            rkTable = $('#rk-table').DataTable({
                processing: true, serverSide: true,
                ajax: {
                    url: "{{ route('rekapitulasi.data') }}",
                    data: function(d) {
                        d.periode_akademik_id = $('#filter_periode').val();
                        d.jabatan = $('#filter_jabatan').val();
                    }
                },
                columns: [
                    { data: null, orderable: false, searchable: false,
                      render: function(data, type, row, meta) { return meta.row + meta.settings._iDisplayStart + 1; } },
                    { data: 'uraian_tugas', name: 'uraian_tugas',
                      render: function(data) { return '<div class="fw-semibold text-dark text-wrap">' + (data || '-') + '</div>'; } },
                    { data: 'pembuat', name: 'user.name' },
                    { data: 'rekan_kerja', name: 'taggedUsers.name', orderable: false },
                    { data: 'status_badge', name: 'status', className: 'text-center' }
                ],
                language: { url: "https://cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json" },
                order: [[0, 'desc']],
                drawCallback: function() {
                    let json = this.api().ajax.json();
                    if (json && json.rekap) {
                        updateStats('rk', json.rekap);
                        updateProgress('rk', json.rekap, rkChart, 'rk-chart-no-data');
                    }
                }
            });
        } else {
            rkTable.ajax.reload();
        }
    });

    $('#tab-pan-btn').on('shown.bs.tab', function() {
        if (!panInitialized) {
            panInitialized = true;
            panTable = $('#pan-table').DataTable({
                processing: true, serverSide: true,
                ajax: {
                    url: "{{ route('rekapitulasi-kepanitiaan.data') }}",
                    data: function(d) {
                        d.periode_akademik_id = $('#filter_periode').val();
                        d.jabatan = $('#filter_jabatan').val();
                    }
                },
                columns: [
                    { data: null, orderable: false, searchable: false,
                      render: function(data, type, row, meta) { return meta.row + meta.settings._iDisplayStart + 1; } },
                    { data: 'uraian_tugas', name: 'uraian_tugas',
                      render: function(data) { return '<div class="fw-semibold text-dark text-wrap">' + (data || '-') + '</div>'; } },
                    { data: 'pembuat', name: 'user.name' },
                    { data: 'rekan_kerja', name: 'taggedUsers.name', orderable: false },
                    { data: 'status_badge', name: 'status', className: 'text-center' }
                ],
                language: { url: "https://cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json" },
                order: [[0, 'desc']],
                drawCallback: function() {
                    let json = this.api().ajax.json();
                    if (json && json.rekap) {
                        updateStats('pan', json.rekap);
                        updateProgress('pan', json.rekap, panChart, 'pan-chart-no-data');
                    }
                }
            });
        } else {
            panTable.ajax.reload();
        }
    });

    $('#tab-ins-btn').on('shown.bs.tab', function() {
        if (!insInitialized) {
            insInitialized = true;
            insTable = $('#ins-table').DataTable({
                processing: true, serverSide: true,
                ajax: {
                    url: "{{ route('rekapitulasi-insidentil.data') }}",
                    data: function(d) {
                        d.periode_akademik_id = $('#filter_periode').val();
                        d.jabatan = $('#filter_jabatan').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'pembuat', name: 'pembuat' },
                    { data: 'uraian_tugas', name: 'uraian_tugas' },
                    { data: 'rekan_kerja', name: 'rekan_kerja' },
                    { data: 'status_badge', name: 'status_badge', className: 'text-center' }
                ],
                language: {
                    search: "Cari:", lengthMenu: "Tampilkan _MENU_ data",
                    zeroRecords: "Data tidak ditemukan",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    infoEmpty: "Menampilkan 0 data",
                    paginate: { first: "Awal", last: "Akhir", next: "►", previous: "◄" }
                },
                order: [[0, 'desc']],
                drawCallback: function() {
                    let json = this.api().ajax.json();
                    if (json && json.rekap) {
                        updateStats('ins', json.rekap);
                        let pct = json.rekap.percent;
                        $('#ins-percent-text').text(pct + '%');
                        $('#ins-progress-bar').css('width', pct + '%').attr('aria-valuenow', pct);
                        if (insChart) {
                            if (json.rekap.total > 0) {
                                $('#ins-chartStatus').show();
                                $('#ins-chart-no-data').hide();
                                insChart.data.datasets[0].data = [json.rekap.belum, json.rekap.proses, json.rekap.selesai];
                                insChart.update();
                            } else {
                                $('#ins-chartStatus').hide();
                                $('#ins-chart-no-data').show();
                            }
                        }
                    }
                }
            });
        } else {
            insTable.ajax.reload();
        }
    });

    // ---- Filter changes reload active tab ----
    $('#filter_periode, #filter_status, #filter_kategori, #filter_jabatan').on('change', function() {
        let activeTab = $('#rekapTab .nav-link.active').attr('id');
        if (activeTab === 'tab-keg-btn') {
            kegTable.ajax.reload();
        } else if (activeTab === 'tab-rk-btn' && rkInitialized) {
            rkTable.ajax.reload();
        } else if (activeTab === 'tab-pan-btn' && panInitialized) {
            panTable.ajax.reload();
        } else if (activeTab === 'tab-ins-btn' && insInitialized) {
            insTable.ajax.reload();
        }
    });

    // ---- Reset ----
    $('#btn-reset').on('click', function() {
        $('#filter_periode').val('').trigger('change');
        $('#filter_status').val('').trigger('change');
        $('#filter_kategori').val('').trigger('change');
        $('#filter_jabatan').val('').trigger('change');
    });
});
</script>
@endpush
