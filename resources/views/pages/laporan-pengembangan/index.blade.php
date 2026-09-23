@extends('layouts.dashboard.template')

@section('content')
<style>
    .banner-hero-uis {
        background: linear-gradient(135deg, #15432D 0%, #1c583b 50%, #15432D 100%);
        color: #ffffff;
        border: none;
        position: relative;
        overflow: hidden;
    }
    .banner-hero-uis::after {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 350px;
        height: 350px;
        background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, rgba(255,255,255,0) 70%);
        border-radius: 50%;
        pointer-events: none;
    }
    .glass-metric-card {
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(6px);
        border-radius: 12px;
        padding: 14px 16px;
        transition: all 0.2s ease;
    }
    .glass-metric-card:hover {
        background: rgba(255, 255, 255, 0.18);
        transform: translateY(-2px);
    }
    .btn-action-table {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        transition: all 0.15s ease-in-out;
        font-size: 0.85rem;
    }
    .btn-action-table:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 5px rgba(0,0,0,0.12);
    }
    .table-modern thead th {
        background-color: #f8fafc;
        color: #475569;
        font-size: 0.76rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e2e8f0;
        padding: 12px 14px;
        white-space: nowrap;
    }
    .table-modern tbody td {
        padding: 14px 14px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
    }
</style>

<!-- Page Header -->
<div class="pagetitle mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h1 class="mb-1 text-dark fw-bold fs-4">Laporan Pencapaian Pengembangan Universitas</h1>
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item text-muted">Laporan Eksekutif</li>
                    <li class="breadcrumb-item active text-success fw-semibold">Pengembangan Universitas</li>
                </ol>
            </nav>
        </div>
        @if(Auth::user()->isAdmin() || Auth::user()->isSuperAdmin() || Auth::user()->isPimpinanRektorat())
            <div>
                <a href="{{ route('laporan-pengembangan.create') }}" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1.5 shadow-sm fw-semibold rounded-2 px-3 py-1.5">
                    <i class="bi bi-plus-circle"></i> Buat Laporan Baru
                </a>
            </div>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4 d-flex align-items-center gap-2" role="alert">
        <i class="bi bi-check-circle-fill fs-5 text-success"></i>
        <div>{{ session('success') }}</div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Hero Banner (Laporan Terkini) -->
@if($latestLaporan)
    @php
        $mhs = $latestLaporan->data_mahasiswa ?? [];
        $sdm = $latestLaporan->data_sdm ?? [];
        $riset = $latestLaporan->data_penelitian_pkm ?? [];
        $prodiList = $latestLaporan->data_prodi ?? [];
    @endphp
    <div class="card banner-hero-uis shadow-sm rounded-4 mb-4">
        <div class="card-body p-4 position-relative" style="z-index: 2;">
            <!-- Top info & actions -->
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                <div>
                    <div class="mb-2">
                        <span class="badge bg-white text-success fw-bold px-3 py-1.5 rounded-pill shadow-xs d-inline-flex align-items-center gap-1.5" style="font-size: 0.78rem;">
                            <i class="bi bi-star-fill text-warning"></i>
                            <span>Laporan Periode Terkini</span>
                        </span>
                    </div>
                    <h2 class="fw-bold mb-1 text-white" style="font-size: 1.45rem; line-height: 1.35;">
                        {{ $latestLaporan->judul }}
                    </h2>
                    <div class="text-white-50 small mt-1 d-flex flex-wrap align-items-center gap-2">
                        <span><i class="bi bi-calendar3 me-1"></i> Tahun Akademik: <strong class="text-white">{{ $latestLaporan->tahun_akademik ?: '-' }}</strong></span>
                        <span>&bull;</span>
                        <span><i class="bi bi-bookmark-check me-1"></i> Periode: <strong class="text-white">{{ $latestLaporan->periodeAkademik ? $latestLaporan->periodeAkademik->nama_periode : 'Semua Periode' }}</strong></span>
                    </div>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2 pt-2 pt-lg-0">
                    <a href="{{ route('laporan-pengembangan.show', $latestLaporan->id) }}" class="btn btn-light px-3 py-2 rounded-3 fw-bold text-success shadow-sm d-inline-flex align-items-center gap-2 text-sm">
                        <i class="bi bi-eye-fill"></i>
                        <span>Buka Laporan Lengkap</span>
                    </a>
                    <a href="{{ route('laporan-pengembangan.export-pdf', $latestLaporan->id) }}" class="btn btn-outline-light px-3 py-2 rounded-3 fw-semibold shadow-sm d-inline-flex align-items-center gap-2 text-sm">
                        <i class="bi bi-file-earmark-pdf-fill text-danger"></i>
                        <span>Cetak PDF Resmi</span>
                    </a>
                    <a href="{{ route('laporan-pengembangan.export-excel', $latestLaporan->id) }}" class="btn btn-outline-light px-3 py-2 rounded-3 fw-semibold shadow-sm d-inline-flex align-items-center gap-2 text-sm">
                        <i class="bi bi-file-earmark-excel-fill text-success"></i>
                        <span>Export Excel</span>
                    </a>
                </div>
            </div>

            <!-- 4 Highlight Cards -->
            <div class="row g-3 pt-3 border-top border-white border-opacity-25">
                <div class="col-xl-3 col-md-6">
                    <div class="glass-metric-card h-100">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="text-white-50 small fw-medium">Mahasiswa Baru (PMB)</span>
                            <i class="bi bi-people-fill text-warning fs-5"></i>
                        </div>
                        <h4 class="fw-bold text-white mb-0" style="font-size: 1.35rem;">
                            {{ number_format((int)($mhs['realisasi_pmb'] ?? 0)) }} <span class="fs-6 fw-normal text-white-50">Mhs</span>
                        </h4>
                        <small class="text-white-50 mt-1 d-block" style="font-size: 0.78rem;">
                            Target: {{ number_format((int)($mhs['target_pmb'] ?? 0)) }} &bull; Retensi: {{ $mhs['tingkat_retensi'] ?? 0 }}%
                        </small>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="glass-metric-card h-100">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="text-white-50 small fw-medium">Status Program Studi</span>
                            <i class="bi bi-award-fill text-warning fs-5"></i>
                        </div>
                        <h4 class="fw-bold text-white mb-0" style="font-size: 1.35rem;">
                            {{ count($prodiList) }} <span class="fs-6 fw-normal text-white-50">Prodi</span>
                        </h4>
                        <small class="text-white-50 mt-1 d-block" style="font-size: 0.78rem;">
                            Akreditasi Baik Sekali & Unggul
                        </small>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="glass-metric-card h-100">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="text-white-50 small fw-medium">SDM Dosen Doktor (S3)</span>
                            <i class="bi bi-mortarboard-fill text-warning fs-5"></i>
                        </div>
                        <h4 class="fw-bold text-white mb-0" style="font-size: 1.35rem;">
                            {{ $sdm['dosen_s3'] ?? 0 }} <span class="fs-6 fw-normal text-white-50">Orang</span>
                        </h4>
                        <small class="text-white-50 mt-1 d-block" style="font-size: 0.78rem;">
                            S2: {{ $sdm['dosen_s2'] ?? 0 }} &bull; Guru Besar: {{ $sdm['dosen_guru_besar'] ?? 0 }}
                        </small>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="glass-metric-card h-100">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="text-white-50 small fw-medium">Publikasi & Riset</span>
                            <i class="bi bi-journal-richtext text-warning fs-5"></i>
                        </div>
                        <h4 class="fw-bold text-white mb-0" style="font-size: 1.35rem;">
                            {{ ((int)($riset['publikasi_scopus'] ?? 0)) + ((int)($riset['publikasi_sinta'] ?? 0)) }} <span class="fs-6 fw-normal text-white-50">Judul</span>
                        </h4>
                        <small class="text-white-50 mt-1 d-block" style="font-size: 0.78rem;">
                            HKI/Paten: {{ $riset['hki_paten'] ?? 0 }} &bull; Scopus: {{ $riset['publikasi_scopus'] ?? 0 }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

<!-- Table Archive Card -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-white">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2.5">
            <div class="rounded-3 d-flex align-items-center justify-content-center text-success bg-success-subtle" style="width: 38px; height: 38px;">
                <i class="bi bi-collection-fill fs-5"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Arsip Dokumen Laporan Pencapaian Pengembangan Universitas</h6>
                <small class="text-muted" style="font-size: 0.78rem;">Daftar rekapitulasi capaian strategis universitas per periode akademik</small>
            </div>
        </div>
        <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill fw-semibold" style="font-size: 0.78rem;">
            <i class="bi bi-folder2 me-1"></i> Total: {{ $laporans->total() }} Laporan
        </span>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-modern align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 45px;" class="text-center">No</th>
                        <th>Judul Dokumen Laporan</th>
                        <th style="width: 140px;" class="text-center">Tahun / Periode</th>
                        <th style="width: 150px;">Mahasiswa Aktif</th>
                        <th style="width: 140px;" class="text-center">Jumlah Prodi</th>
                        <th style="width: 120px;">Dibuat Oleh</th>
                        <th style="width: 150px;">Tanggal Pembaruan</th>
                        <th style="width: 160px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($laporans as $idx => $l)
                        @php
                            $dMhs = $l->data_mahasiswa ?? [];
                            $dProdi = $l->data_prodi ?? [];
                        @endphp
                        <tr>
                            <td class="text-center text-muted fw-semibold" style="font-size: 0.85rem;">
                                {{ $laporans->firstItem() + $idx }}
                            </td>
                            <td>
                                <a href="{{ route('laporan-pengembangan.show', $l->id) }}" class="fw-bold text-dark text-decoration-none d-block mb-1 hover-primary" style="font-size: 0.92rem;">
                                    {{ $l->judul }}
                                </a>
                                <div class="text-muted small d-flex align-items-center gap-1.5" style="font-size: 0.78rem;">
                                    <i class="bi bi-building text-success"></i>
                                    <span>Universitas Ibnu Sina Batam</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border px-2.5 py-1.5 rounded-pill fw-semibold" style="font-size: 0.78rem;">
                                    {{ $l->tahun_akademik ?: ($l->periodeAkademik ? $l->periodeAkademik->nama_periode : '-') }}
                                </span>
                            </td>
                            <td>
                                <strong class="text-dark d-block" style="font-size: 0.9rem;">{{ number_format((int)($dMhs['total_aktif'] ?? 0)) }} <span class="text-muted fw-normal">Mhs</span></strong>
                                <small class="text-muted" style="font-size: 0.76rem;">Retensi: <strong class="text-success">{{ $dMhs['tingkat_retensi'] ?? 0 }}%</strong></small>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1.5 rounded-pill fw-semibold" style="font-size: 0.78rem;">
                                    {{ count($dProdi) }} Program Studi
                                </span>
                            </td>
                            <td>
                                <span class="text-muted small d-flex align-items-center gap-1">
                                    <i class="bi bi-person-circle"></i>
                                    <span>{{ $l->creator ? $l->creator->name : '-' }}</span>
                                </span>
                            </td>
                            <td>
                                <span class="text-muted small d-flex align-items-center gap-1">
                                    <i class="bi bi-clock"></i>
                                    <span>{{ $l->updated_at ? $l->updated_at->format('d/m/Y H:i') : '-' }}</span>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center gap-1">
                                    <a href="{{ route('laporan-pengembangan.show', $l->id) }}" class="btn btn-action-table btn-light border text-primary" title="Buka Detail Laporan">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('laporan-pengembangan.export-pdf', $l->id) }}" class="btn btn-action-table btn-light border text-danger" title="Cetak PDF Resmi">
                                        <i class="bi bi-file-earmark-pdf"></i>
                                    </a>
                                    <a href="{{ route('laporan-pengembangan.export-excel', $l->id) }}" class="btn btn-action-table btn-light border text-success" title="Ekspor Excel">
                                        <i class="bi bi-file-earmark-excel"></i>
                                    </a>
                                    @if(Auth::user()->isAdmin() || Auth::user()->isSuperAdmin())
                                        <a href="{{ route('laporan-pengembangan.edit', $l->id) }}" class="btn btn-action-table btn-light border text-warning" title="Edit Data Laporan">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('laporan-pengembangan.destroy', $l->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus laporan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-action-table btn-light border text-danger" title="Hapus Laporan">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-folder2-open fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                <span class="fw-semibold">Belum ada dokumen Laporan Pencapaian Pengembangan Universitas yang dibuat.</span><br>
                                @if(Auth::user()->isAdmin() || Auth::user()->isSuperAdmin() || Auth::user()->isPimpinanRektorat())
                                    <a href="{{ route('laporan-pengembangan.create') }}" class="btn btn-sm btn-success rounded-3 mt-3 px-3 py-2 fw-semibold shadow-sm">
                                        <i class="bi bi-plus-circle me-1"></i> Buat Laporan Pertama
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($laporans->hasPages())
        <div class="card-footer bg-white border-top p-3 d-flex justify-content-end">
            {{ $laporans->links() }}
        </div>
    @endif
</div>
@endsection
