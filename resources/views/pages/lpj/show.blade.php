@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
    <h1>Detail LPJ</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item">Rekapitulasi & Laporan</li>
            <li class="breadcrumb-item"><a href="{{ route('lpj.index') }}">LPJ</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    <div class="row">
        <div class="col-lg-10 col-xl-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-4 pb-3 border-bottom">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1" style="font-size: 0.8rem;">
                                    <i class="bi bi-people me-1"></i> {{ $lpj->nama_kepanitiaan }}
                                </span>
                                @if($lpj->tanggal_kegiatan)
                                    <span class="text-muted small">
                                        <i class="bi bi-calendar3 me-1"></i> {{ $lpj->tanggal_kegiatan->translatedFormat('d F Y') }}
                                    </span>
                                @endif
                            </div>
                            <h4 class="fw-bold text-dark mb-1">{{ $lpj->nama_lpj }}</h4>
                            <small class="text-muted">Diarsipkan pada {{ $lpj->created_at->translatedFormat('d F Y, H:i') }} WIB</small>
                        </div>
                        <a href="{{ route('lpj.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>

                    <!-- Author Info Card -->
                    <div class="p-3 bg-light rounded-3 mb-4 border d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-primary fw-bold" style="width: 44px; height: 44px; background-color: #e0f2fe; font-size: 1.1rem;">
                            {{ strtoupper(substr($lpj->user->name ?? 'U', 0, 1)) }}
                        </div>
                        <div>
                            <span class="d-block fw-bold text-dark">{{ $lpj->user->name ?? '-' }}</span>
                            <small class="text-muted">{{ $lpj->user->jabatan ?? 'Pegawai' }} &bull; {{ $lpj->user->email ?? '-' }}</small>
                        </div>
                    </div>

                    <!-- Keterangan / Deskripsi -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-dark mb-2"><i class="bi bi-text-paragraph text-primary me-1"></i> Keterangan / Ringkasan:</h6>
                        <div class="p-3 bg-white border rounded-3 text-secondary" style="line-height: 1.6; white-space: pre-line;">
                            {{ $lpj->keterangan ?: 'Tidak ada keterangan tambahan.' }}
                        </div>
                    </div>

                    <!-- Berkas & Link -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-dark mb-2"><i class="bi bi-paperclip text-primary me-1"></i> Berkas & Tautan LPJ:</h6>
                        <div class="row g-3">
                            <!-- File Card -->
                            <div class="col-md-6">
                                <div class="p-3 border rounded-3 h-100 bg-white d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i class="bi bi-file-earmark-arrow-down-fill fs-4 text-primary"></i>
                                            <span class="fw-bold text-dark">Berkas File LPJ</span>
                                        </div>
                                        @if($lpj->file_lpj)
                                            <p class="text-muted small mb-3 text-break">{{ basename($lpj->file_lpj) }}</p>
                                        @else
                                            <p class="text-muted small mb-3">Tidak ada berkas file diunggah.</p>
                                        @endif
                                    </div>
                                    @if($lpj->file_lpj)
                                        <a href="{{ route('lpj.download', $lpj->id) }}" class="btn btn-primary btn-sm d-inline-flex align-items-center justify-content-center gap-1.5 w-100">
                                            <i class="bi bi-download"></i> Unduh Berkas
                                        </a>
                                    @else
                                        <button class="btn btn-light btn-sm w-100" disabled>Tidak Tersedia</button>
                                    @endif
                                </div>
                            </div>

                            <!-- Link Cloud Card -->
                            <div class="col-md-6">
                                <div class="p-3 border rounded-3 h-100 bg-white d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i class="bi bi-cloud-arrow-up-fill fs-4 text-info"></i>
                                            <span class="fw-bold text-dark">Tautan Dokumen Cloud</span>
                                        </div>
                                        @if($lpj->link_lpj)
                                            <p class="text-muted small mb-3 text-truncate">{{ $lpj->link_lpj }}</p>
                                        @else
                                            <p class="text-muted small mb-3">Tidak ada tautan eksternal.</p>
                                        @endif
                                    </div>
                                    @if($lpj->link_lpj)
                                        <a href="{{ $lpj->link_lpj }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-info btn-sm d-inline-flex align-items-center justify-content-center gap-1.5 w-100">
                                            <i class="bi bi-box-arrow-up-right"></i> Buka Tautan Dokumen
                                        </a>
                                    @else
                                        <button class="btn btn-light btn-sm w-100" disabled>Tidak Tersedia</button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Bar -->
                    @php
                        $canManage = auth()->check() && (
                            auth()->user()->isAdmin() || 
                            auth()->user()->isSuperAdmin() || 
                            auth()->user()->isPimpinanRektorat() || 
                            auth()->user()->isPimpinanUnit() || 
                            auth()->id() === $lpj->user_id
                        );
                    @endphp

                    @if($canManage)
                    <div class="pt-3 border-top d-flex gap-2">
                        <a href="{{ route('lpj.edit', $lpj->id) }}" class="btn btn-warning text-white d-inline-flex align-items-center gap-1.5">
                            <i class="bi bi-pencil-square"></i> Edit Data
                        </a>
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</section>
@endsection
