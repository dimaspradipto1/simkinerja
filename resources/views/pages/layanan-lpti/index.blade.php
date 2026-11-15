@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
    <h1>{{ $title }}</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item">Modul Layanan</li>
            <li class="breadcrumb-item">Layanan LPTI</li>
            <li class="breadcrumb-item active">{{ $title }}</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    <div class="row">
        <!-- Main Information Card -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="p-2 rounded-3 bg-light border {{ $iconColor }}">
                            <i class="bi {{ $icon }} fs-4"></i>
                        </span>
                        <div>
                            <h5 class="card-title m-0 fw-bold text-dark fs-6">{{ $title }}</h5>
                            <span class="text-muted small">{{ $category }}</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge bg-{{ $roleBadgeColor }}-subtle text-{{ $roleBadgeColor }} border border-{{ $roleBadgeColor }}-subtle px-2.5 py-1.5 rounded-pill small">
                            <i class="bi bi-shield-lock me-1"></i>Role: {{ $roleBadge }}
                        </span>
                        @if($hasAccess)
                            <span class="badge bg-success text-white px-2.5 py-1.5 rounded-pill small">
                                <i class="bi bi-check-circle-fill me-1"></i>Akses Anda Aktif
                            </span>
                        @endif
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1.5 rounded-pill small">
                            <i class="bi bi-check-circle-fill me-1"></i>Layanan Aktif
                        </span>
                    </div>
                </div>
                <div class="card-body pt-3">
                    <p class="text-secondary mb-4" style="line-height: 1.6;">
                        {{ $description }}
                    </p>

                    <h6 class="fw-bold text-dark mb-3">
                        <i class="bi bi-list-check me-2 text-primary"></i>Ruang Lingkup &amp; Layanan Utama:
                    </h6>
                    <div class="list-group list-group-flush border rounded-3 mb-4">
                        @foreach($services as $idx => $service)
                            <div class="list-group-item d-flex align-items-center gap-2 py-2.5">
                                <span class="badge bg-primary-subtle text-primary rounded-circle" style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 11px;">
                                    {{ $idx + 1 }}
                                </span>
                                <span class="text-dark small fw-medium">{{ $service }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="alert alert-light border d-flex align-items-start gap-3 p-3 rounded-3" role="alert">
                        <i class="bi bi-info-circle-fill fs-5 text-primary mt-0.5"></i>
                        <div class="small text-secondary">
                            <strong>Informasi Pengajuan Tiket Layanan:</strong><br>
                            Untuk mengajukan permohonan atau pelaporan kendala terkait <em>{{ $title }}</em>, silakan buat tiket pengajuan atau hubungi PIC divisi terkait.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar / Contact & Quick Actions Card -->
        <div class="col-lg-4">
            <!-- PIC & Contact Card -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="card-title m-0 fw-bold text-dark fs-6">
                        <i class="bi bi-person-badge-fill me-2 text-success"></i>Pengelola Layanan (PIC)
                    </h6>
                </div>
                <div class="card-body pt-3">
                    <div class="d-flex align-items-center gap-3 mb-3 p-2 rounded-3 bg-light">
                        <div class="rounded-circle bg-white shadow-sm p-2 text-center" style="width: 44px; height: 44px;">
                            <i class="bi bi-building-gear fs-4 text-success"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark small">{{ $pic }}</div>
                            <div class="text-muted" style="font-size: 11px;">LPTI Universitas Ibnu Sina</div>
                        </div>
                    </div>

                    <ul class="list-unstyled small text-secondary mb-0">
                        <li class="mb-2 d-flex align-items-center gap-2">
                            <i class="bi bi-envelope text-primary"></i>
                            <span>Email: <a href="mailto:lpti@uis.ac.id" class="text-decoration-none">lpti@uis.ac.id</a></span>
                        </li>
                        <li class="mb-2 d-flex align-items-center gap-2">
                            <i class="bi bi-geo-alt text-danger"></i>
                            <span>Ruang LPTI Gedung Rektorat Lt. 2</span>
                        </li>
                        <li class="d-flex align-items-center gap-2">
                            <i class="bi bi-clock-history text-warning"></i>
                            <span>Senin - Jumat (08.00 - 16.00 WIB)</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Quick Navigation Card -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="card-title m-0 fw-bold text-dark fs-6">
                        <i class="bi bi-grid-fill me-2 text-primary"></i>Menu Layanan LPTI Lainnya
                    </h6>
                </div>
                <div class="list-group list-group-flush small">
                    <a href="{{ route('layanan.lpti.manajemen') }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between py-2.5 {{ $key === 'manajemen' ? 'active' : '' }}">
                        <span class="d-flex align-items-center gap-2"><i class="bi bi-briefcase"></i>Manajemen LPTI</span>
                        <span class="badge {{ $key === 'manajemen' ? 'bg-white text-primary' : 'bg-primary-subtle text-primary border border-primary-subtle' }} rounded-pill" style="font-size: 10px;">Kepala LPTI</span>
                    </a>
                    <a href="{{ route('layanan.lpti.pengembangan-si') }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between py-2.5 {{ $key === 'pengembangan-si' ? 'active' : '' }}">
                        <span class="d-flex align-items-center gap-2"><i class="bi bi-window-stack"></i>Pengembangan Sistem Informasi</span>
                        <span class="badge {{ $key === 'pengembangan-si' ? 'bg-white text-info' : 'bg-info-subtle text-info border border-info-subtle' }} rounded-pill" style="font-size: 10px;">Divisi SI</span>
                    </a>
                    <a href="{{ route('layanan.lpti.infrastruktur') }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between py-2.5 {{ $key === 'infrastruktur' ? 'active' : '' }}">
                        <span class="d-flex align-items-center gap-2"><i class="bi bi-diagram-3"></i>Pengelolaan Infrastruktur &amp; Jaringan</span>
                        <span class="badge {{ $key === 'infrastruktur' ? 'bg-white text-warning' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' }} rounded-pill" style="font-size: 10px;">Divisi Jaringan</span>
                    </a>
                    <a href="{{ route('layanan.lpti.pemeliharaan') }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between py-2.5 {{ $key === 'pemeliharaan' ? 'active' : '' }}">
                        <span class="d-flex align-items-center gap-2"><i class="bi bi-code-slash"></i>Pengembangan &amp; Pemeliharaan Aplikasi</span>
                        <span class="badge {{ $key === 'pemeliharaan' ? 'bg-white text-danger' : 'bg-danger-subtle text-danger border border-danger-subtle' }} rounded-pill" style="font-size: 10px;">Programmer</span>
                    </a>
                    <a href="{{ route('layanan.lpti.dukungan-teknis') }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between py-2.5 {{ $key === 'dukungan-teknis' ? 'active' : '' }}">
                        <span class="d-flex align-items-center gap-2"><i class="bi bi-headset"></i>Layanan Dukungan Teknis</span>
                        <span class="badge {{ $key === 'dukungan-teknis' ? 'bg-white text-success' : 'bg-success-subtle text-success border border-success-subtle' }} rounded-pill" style="font-size: 10px;">Troubleshooting</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
