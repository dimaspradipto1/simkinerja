@extends('layouts.dashboard.template')

@section('content')
<style>
    .nav-pills-uis .nav-link {
        color: #475569;
        font-weight: 600;
        border-radius: 8px;
        padding: 8px 16px;
        transition: all 0.2s ease;
        border: 1px solid transparent;
    }
    .nav-pills-uis .nav-link:hover {
        background-color: #f1f5f9;
        color: #15432D;
    }
    .nav-pills-uis .nav-link.active {
        background-color: #15432D !important;
        color: #ffffff !important;
        box-shadow: 0 2px 5px rgba(21, 67, 45, 0.25);
    }
    .kpi-card-uis {
        transition: all 0.2s ease;
        border: 1px solid #e2e8f0;
    }
    .kpi-card-uis:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    }
</style>

<div class="pagetitle mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h1 class="mb-1 text-dark fw-bold fs-4">Laporan Kinerja Pegawai</h1>
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item text-muted">Laporan Eksekutif</li>
                    <li class="breadcrumb-item active text-success fw-semibold">Kinerja Pegawai UIS</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('laporan-kinerja-pegawai.export-pdf', request()->all()) }}" class="btn btn-danger d-inline-flex align-items-center gap-2 px-3 py-2 rounded-3 shadow-sm fw-semibold text-sm">
                <i class="bi bi-file-earmark-pdf-fill"></i>
                <span>Export PDF Resmi</span>
            </a>
            <a href="{{ route('laporan-kinerja-pegawai.export-excel', request()->all()) }}" class="btn btn-success d-inline-flex align-items-center gap-2 px-3 py-2 rounded-3 shadow-sm fw-semibold text-sm">
                <i class="bi bi-file-earmark-excel-fill"></i>
                <span>Export Excel</span>
            </a>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white" style="border-left: 5px solid #15432D !important;">
    <div class="card-body p-3.5">
        <form method="GET" action="{{ route('laporan-kinerja-pegawai.index') }}" class="row g-2.5 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Periode Akademik</label>
                <select name="periode_akademik_id" class="form-select form-select-sm rounded-2" onchange="this.form.submit()">
                    <option value="">-- Semua Periode --</option>
                    @foreach($periodeAkademiks as $p)
                        <option value="{{ $p->id }}" {{ (isset($selectedPeriode) && $selectedPeriode->id == $p->id) ? 'selected' : '' }}>
                            {{ $p->nama_periode }}
                        </option>
                    @endforeach
                </select>
            </div>

            @if(Auth::user()->isAdmin() || Auth::user()->isPimpinanRektorat())
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Unit Kerja</label>
                <select name="unit" class="form-select form-select-sm rounded-2" onchange="this.form.submit()">
                    <option value="">-- Semua Unit Kerja --</option>
                    @foreach($unitsList as $u)
                        <option value="{{ $u }}" {{ $selectedUnit == $u ? 'selected' : '' }}>{{ $u }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Cari Pegawai / Jabatan</label>
                <div class="input-group input-group-sm">
                    <input type="text" name="search" class="form-control rounded-start-2" placeholder="Nama / NIDN / Jabatan..." value="{{ request('search') }}">
                    <button class="btn btn-outline-success rounded-end-2" type="submit"><i class="bi bi-search"></i></button>
                </div>
            </div>

            <div class="col-md-3 d-flex align-items-center gap-2">
                <button type="submit" class="btn btn-sm btn-primary px-3 py-1.5 rounded-3 fw-semibold">
                    <i class="bi bi-funnel me-1"></i> Terapkan
                </button>
                @if(request()->hasAny(['periode_akademik_id', 'unit', 'search']))
                    <a href="{{ route('laporan-kinerja-pegawai.index') }}" class="btn btn-sm btn-outline-secondary rounded-3 px-3 py-1.5">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card kpi-card-uis border-0 shadow-sm rounded-4 h-100 bg-white">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Total Pegawai</span>
                    <h3 class="fw-bold mb-0 text-dark">{{ $totalPegawai }}</h3>
                    <small class="text-muted"><strong class="text-primary">{{ $totalDosen }}</strong> Dosen &bull; <strong class="text-secondary">{{ $totalTendik }}</strong> Tendik</small>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary-subtle text-primary" style="width: 48px; height: 48px; font-size: 1.3rem;">
                    <i class="bi bi-people-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card kpi-card-uis border-0 shadow-sm rounded-4 h-100 bg-white">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Total Tugas Ditugaskan</span>
                    <h3 class="fw-bold mb-0 text-dark">{{ $totalTugasSemua }}</h3>
                    <small class="text-muted">Rencana Kerja, Kegiatan, Panitia, Insidentil</small>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-info-subtle text-info" style="width: 48px; height: 48px; font-size: 1.3rem;">
                    <i class="bi bi-list-task"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card kpi-card-uis border-0 shadow-sm rounded-4 h-100 bg-white">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Tugas Tuntas / Selesai</span>
                    <h3 class="fw-bold mb-0 text-success">{{ $totalSelesaiSemua }}</h3>
                    <small class="text-muted">Dari total {{ $totalTugasSemua }} tugas terdaftar</small>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-success-subtle text-success" style="width: 48px; height: 48px; font-size: 1.3rem;">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card kpi-card-uis border-0 shadow-sm rounded-4 h-100 bg-white">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Rata-rata Ketercapaian</span>
                    <h3 class="fw-bold mb-0 {{ $persentaseRataRata >= 75 ? 'text-success' : ($persentaseRataRata >= 50 ? 'text-warning' : 'text-danger') }}">
                        {{ $persentaseRataRata }}%
                    </h3>
                    <small class="text-muted">Indeks Kinerja Pegawai Universitas</small>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-warning-subtle text-warning" style="width: 48px; height: 48px; font-size: 1.3rem;">
                    <i class="bi bi-speedometer2"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabs Laporan Kinerja -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-white">
    <div class="card-header bg-white border-bottom p-2.5">
        <ul class="nav nav-pills nav-pills-uis card-header-pills gap-1.5" id="kinerjaTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-semibold text-nowrap" id="rekap-pegawai-tab" data-bs-toggle="tab" data-bs-target="#rekap-pegawai" type="button" role="tab">
                    <i class="bi bi-building me-1"></i> 1. Rekapitulasi Pegawai & Unit
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-semibold text-nowrap" id="capaian-pegawai-tab" data-bs-toggle="tab" data-bs-target="#capaian-pegawai" type="button" role="tab">
                    <i class="bi bi-person-lines-fill me-1"></i> 2. Capaian Kinerja Pegawai
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-semibold text-nowrap" id="tri-dharma-tab" data-bs-toggle="tab" data-bs-target="#tri-dharma" type="button" role="tab">
                    <i class="bi bi-mortarboard-fill me-1"></i> 3. Evaluasi Tri Dharma Dosen
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-semibold text-nowrap" id="kendala-tab" data-bs-toggle="tab" data-bs-target="#kendala" type="button" role="tab">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> 4. Kendala & Langkah Solusi
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-semibold text-nowrap" id="tindak-lanjut-tab" data-bs-toggle="tab" data-bs-target="#tindak-lanjut" type="button" role="tab">
                    <i class="bi bi-journal-check me-1"></i> 5. Rencana Tindak Lanjut
                </button>
            </li>
        </ul>
    </div>

    <div class="card-body p-3">
        <div class="tab-content" id="kinerjaTabContent">

            <!-- TAB 1: REKAPITULASI PEGAWAI & UNIT -->
            <div class="tab-pane fade show active" id="rekap-pegawai" role="tabpanel">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-table me-1.5 text-success"></i> Rekapitulasi Jumlah Pegawai Berdasarkan Unit Kerja & Bidang Tugas</h6>
                    <span class="badge bg-light text-dark border px-2 py-1">Total: {{ count($rekapUnit) }} Unit Kerja Terdata</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle text-sm">
                        <thead class="table-light">
                            <tr class="text-center text-uppercase" style="font-size: 0.78rem;">
                                <th style="width: 50px;">No</th>
                                <th class="text-start">Unit Kerja</th>
                                <th>Bidang Tugas Utama</th>
                                <th style="width: 120px;">Dosen</th>
                                <th style="width: 120px;">Tendik</th>
                                <th style="width: 120px;">Total Pegawai</th>
                                <th style="width: 140px;">Rasio Dosen/Tendik</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rekapUnit as $idx => $r)
                                <tr>
                                    <td class="text-center">{{ $idx + 1 }}</td>
                                    <td class="fw-semibold text-dark">{{ $r['unit'] }}</td>
                                    <td><span class="badge bg-light text-secondary border">{{ $r['bidang'] }}</span></td>
                                    <td class="text-center fw-bold text-primary">{{ $r['dosen'] }}</td>
                                    <td class="text-center fw-bold text-secondary">{{ $r['tendik'] }}</td>
                                    <td class="text-center fw-bold text-dark">{{ $r['total'] }}</td>
                                    <td class="text-center small text-muted">
                                        {{ $r['tendik'] > 0 ? round($r['dosen'] / $r['tendik'], 1) . ' : 1' : $r['dosen'] . ' : 0' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Belum ada data pegawai pada unit ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr class="text-center">
                                <td colspan="3" class="text-center">TOTAL KESELURUHAN:</td>
                                <td class="text-primary">{{ $totalDosen }}</td>
                                <td class="text-secondary">{{ $totalTendik }}</td>
                                <td class="text-dark">{{ $totalPegawai }}</td>
                                <td>-</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- TAB 2: CAPAIAN KINERJA PEGAWAI -->
            <div class="tab-pane fade" id="capaian-pegawai" role="tabpanel">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-award-fill me-1.5 text-primary"></i> Capaian Kinerja Pegawai Berdasarkan Tugas & Tanggung Jawab</h6>
                    <span class="badge bg-light text-muted border">Periode: {{ $selectedPeriode ? $selectedPeriode->nama_periode : 'Semua' }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle text-sm" id="table-capaian-pegawai">
                        <thead class="table-light">
                            <tr class="text-center text-uppercase" style="font-size: 0.75rem;">
                                <th rowspan="2" class="align-middle" style="width: 45px;">No</th>
                                <th rowspan="2" class="align-middle text-start">Nama Pegawai & Jabatan</th>
                                <th rowspan="2" class="align-middle" style="width: 130px;">Unit Kerja</th>
                                <th rowspan="2" class="align-middle" style="width: 110px;">Bidang Tugas</th>
                                <th colspan="4" class="text-center bg-light border-bottom fw-bold" style="letter-spacing: 0.3px;">Realisasi Tugas (Selesai / Total)</th>
                                <th rowspan="2" class="align-middle" style="width: 100px;">Total Selesai</th>
                                <th rowspan="2" class="align-middle" style="width: 110px;">% Capaian</th>
                                <th rowspan="2" class="align-middle" style="width: 130px;">Predikat</th>
                            </tr>
                            <tr class="text-center text-uppercase" style="font-size: 0.72rem;">
                                <th style="min-width: 105px;" class="align-middle">Rencana Kerja</th>
                                <th style="min-width: 90px;" class="align-middle">Kegiatan</th>
                                <th style="min-width: 105px;" class="align-middle">Kepanitiaan</th>
                                <th style="min-width: 90px;" class="align-middle">Insidentil</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($capaianPegawai as $idx => $p)
                                <tr>
                                    <td class="text-center text-muted">{{ $idx + 1 }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $p['name'] }}</div>
                                        <div class="small text-muted">
                                            @if($p['nidn'] !== '-') <span class="badge bg-light text-primary border me-1">NUP/NIDN: {{ $p['nidn'] }}</span> @endif
                                            <span>{{ $p['jabatan'] }}</span>
                                        </div>
                                    </td>
                                    <td class="small">{{ $p['unit'] }}</td>
                                    <td><span class="badge bg-light text-dark border small">{{ $p['bidang'] }}</span></td>
                                    <td class="text-center small">{{ $p['tasks']['rk_selesai'] }} / {{ $p['tasks']['rk_total'] }}</td>
                                    <td class="text-center small">{{ $p['tasks']['kg_selesai'] }} / {{ $p['tasks']['kg_total'] }}</td>
                                    <td class="text-center small">{{ $p['tasks']['kp_selesai'] }} / {{ $p['tasks']['kp_total'] }}</td>
                                    <td class="text-center small">{{ $p['tasks']['in_selesai'] }} / {{ $p['tasks']['in_total'] }}</td>
                                    <td class="text-center fw-bold text-dark">
                                        {{ $p['tasks']['selesai'] }} <span class="text-muted fw-normal">/ {{ $p['tasks']['total'] }}</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center gap-1 justify-content-center">
                                            <div class="progress flex-grow-1" style="height: 6px; width: 50px;">
                                                <div class="progress-bar {{ $p['persentase'] >= 75 ? 'bg-success' : ($p['persentase'] >= 50 ? 'bg-warning' : 'bg-danger') }}" 
                                                     role="progressbar" style="width: {{ $p['persentase'] }}%"></div>
                                            </div>
                                            <span class="small fw-bold">{{ $p['persentase'] }}%</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $p['badgeClass'] }} px-2 py-1">{{ $p['predikat'] }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center py-4 text-muted">Tidak ada data pegawai yang ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 3: EVALUASI TRI DHARMA DOSEN -->
            <div class="tab-pane fade" id="tri-dharma" role="tabpanel">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-mortarboard-fill me-1.5 text-success"></i> Evaluasi Kinerja Dosen (Pendidikan, Penelitian, dan Pengabdian kepada Masyarakat)</h6>
                    <span class="badge bg-light text-primary border">Total Dosen Terdata: {{ count($evaluasiDosen) }} Dosen</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle text-sm">
                        <thead class="table-light">
                            <tr class="text-center text-uppercase" style="font-size: 0.75rem;">
                                <th style="width: 45px;">No</th>
                                <th class="text-start">Nama Dosen</th>
                                <th>Unit / Program Studi</th>
                                <th style="width: 160px;">Pendidikan & Pengajaran</th>
                                <th style="width: 160px;">Penelitian & Publikasi</th>
                                <th style="width: 160px;">Pengabdian Masyarakat (PkM)</th>
                                <th style="width: 110px;">% Capaian</th>
                                <th style="width: 150px;">Status Tri Dharma</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($evaluasiDosen as $idx => $ed)
                                <tr>
                                    <td class="text-center text-muted">{{ $idx + 1 }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $ed['name'] }}</div>
                                        <small class="text-muted">NIDN: {{ $ed['nidn'] }} &bull; {{ $ed['jabatan'] }}</small>
                                    </td>
                                    <td class="small">{{ $ed['unit'] }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                            {{ $ed['dharma']['pendidikan_selesai'] }} / {{ $ed['dharma']['pendidikan_total'] }} Selesai
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                            {{ $ed['dharma']['penelitian_selesai'] }} / {{ $ed['dharma']['penelitian_total'] }} Selesai
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1">
                                            {{ $ed['dharma']['pkm_selesai'] }} / {{ $ed['dharma']['pkm_total'] }} Selesai
                                        </span>
                                    </td>
                                    <td class="text-center fw-bold {{ $ed['persentase'] >= 75 ? 'text-success' : 'text-warning' }}">
                                        {{ $ed['persentase'] }}%
                                    </td>
                                    <td class="text-center">
                                        @if($ed['status_tri_dharma'] === 'Lengkap 3 Dharma')
                                            <span class="badge bg-success px-2 py-1"><i class="bi bi-check2-all me-1"></i> Lengkap 3 Dharma</span>
                                        @else
                                            <span class="badge bg-warning text-dark px-2 py-1"><i class="bi bi-hourglass-split me-1"></i> Sebagian Dharma</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">Belum ada data evaluasi dosen untuk filter ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 4: KENDALA & LANGKAH PENYELESAIAN -->
            <div class="tab-pane fade" id="kendala" role="tabpanel">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-shield-exclamation me-1.5 text-danger"></i> Permasalahan & Kendala yang Dihadapi Pegawai Beserta Solusinya</h6>
                    <span class="badge bg-light text-danger border">Total Kendala Terdeteksi: {{ count($kendalaList) }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle text-sm">
                        <thead class="table-light">
                            <tr class="text-center text-uppercase" style="font-size: 0.75rem;">
                                <th style="width: 45px;">No</th>
                                <th style="width: 180px;" class="text-start">Pegawai & Unit</th>
                                <th style="width: 120px;">Jenis Tugas</th>
                                <th class="text-start">Uraian Tugas</th>
                                <th class="text-start" style="width: 250px;">Kendala / Hambatan</th>
                                <th class="text-start" style="width: 250px;">Langkah Penyelesaian / Solusi</th>
                                <th style="width: 100px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kendalaList as $idx => $k)
                                <tr>
                                    <td class="text-center text-muted">{{ $idx + 1 }}</td>
                                    <td>
                                        <strong class="text-dark">{{ $k['user'] }}</strong>
                                        <div class="small text-muted">{{ $k['unit'] }}</div>
                                    </td>
                                    <td class="text-center"><span class="badge bg-secondary">{{ $k['jenis'] }}</span></td>
                                    <td class="small">{{ strip_tags($k['tugas']) }}</td>
                                    <td class="small text-danger"><i class="bi bi-exclamation-circle me-1"></i> {{ strip_tags($k['kendala']) }}</td>
                                    <td class="small text-success"><i class="bi bi-arrow-right-circle me-1"></i> {{ strip_tags($k['solusi']) }}</td>
                                    <td class="text-center">
                                        @if($k['status'] === 'Selesai')
                                            <span class="badge bg-success">Terselesaikan</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Dalam Penanganan</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        <i class="bi bi-check-circle text-success fs-4 d-block mb-1"></i>
                                        Tidak ada kendala kritis atau perpanjangan waktu pada periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 5: RENCANA TINDAK LANJUT -->
            <div class="tab-pane fade" id="tindak-lanjut" role="tabpanel">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-lightning-fill me-1.5 text-warning"></i> Rencana Tindak Lanjut Peningkatan Kinerja, Disiplin, Kompetensi & Produktivitas</h6>
                    <span class="badge bg-light text-primary border">Total Tindak Lanjut: {{ count($tindakLanjutList) }}</span>
                </div>
                
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card border rounded-3 p-3 bg-light h-100">
                            <h6 class="fw-bold text-dark mb-2"><i class="bi bi-mortarboard text-primary me-1"></i> Peningkatan Kompetensi</h6>
                            <p class="small text-muted mb-0">Mendorong keikutsertaan pelatihan bersertifikat, studi lanjut S3 dosen, sertifikasi kompetensi tendik, dan pendampingan penulisan jurnal internasional/SINTA.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border rounded-3 p-3 bg-light h-100">
                            <h6 class="fw-bold text-dark mb-2"><i class="bi bi-clock-history text-warning me-1"></i> Penegakan Disiplin</h6>
                            <p class="small text-muted mb-0">Pemantauan estimasi waktu pengerjaan tugas secara digital via SIM Kinerja dan integrasi presensi kepanitiaan/harian ke evaluasi tahunan pegawai.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border rounded-3 p-3 bg-light h-100">
                            <h6 class="fw-bold text-dark mb-2"><i class="bi bi-graph-up-arrow text-success me-1"></i> Optimalisasi Produktivitas</h6>
                            <p class="small text-muted mb-0">Penyelarasan Rencana Kerja Operasional Unit dengan Rencana Strategis (Renstra) Universitas serta apresiasi pegawai dengan capaian predikat Sangat Baik.</p>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle text-sm">
                        <thead class="table-light">
                            <tr class="text-center text-uppercase" style="font-size: 0.75rem;">
                                <th style="width: 45px;">No</th>
                                <th style="width: 200px;" class="text-start">Pegawai / Unit</th>
                                <th class="text-start">Uraian Tugas</th>
                                <th class="text-start" style="width: 240px;">Hasil Kerja yang Dicapai</th>
                                <th class="text-start" style="width: 240px;">Rencana Tindak Lanjut Pegawai</th>
                                <th class="text-start" style="width: 240px;">Arahan / Saran Pimpinan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tindakLanjutList as $idx => $tl)
                                <tr>
                                    <td class="text-center text-muted">{{ $idx + 1 }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $tl['user'] }}</div>
                                        <small class="text-muted">{{ $tl['unit'] }}</small>
                                    </td>
                                    <td class="small">{{ strip_tags($tl['tugas']) }}</td>
                                    <td class="small">{{ strip_tags($tl['hasil_kerja']) }}</td>
                                    <td class="small text-primary"><i class="bi bi-check2 me-1"></i> {{ strip_tags($tl['tindak_lanjut']) }}</td>
                                    <td class="small text-success"><i class="bi bi-chat-left-dots me-1"></i> {{ strip_tags($tl['saran_pimpinan']) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">Belum ada catatan tindak lanjut khusus yang dicatat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
