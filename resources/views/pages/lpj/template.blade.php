@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
    <h1>Template LPJ</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item">Rekapitulasi & Laporan</li>
            <li class="breadcrumb-item"><a href="{{ route('lpj.index') }}">LPJ</a></li>
            <li class="breadcrumb-item active">Template LPJ</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    <div class="row">
        <!-- Kolom Kiri: Informasi & Unduh Template -->
        <div class="col-lg-{{ $canEdit ? '6' : '12' }}">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-primary bg-primary-subtle flex-shrink-0" style="width: 40px; height: 40px;">
                            <i class="bi bi-file-earmark-word-fill fs-5"></i>
                        </div>
                        <div>
                            <h5 class="card-title p-0 m-0 fw-bold text-dark fs-6">{{ $templateLpj->nama_template ?? 'Template Resmi LPJ' }}</h5>
                            <small class="text-muted">Dokumen acuan resmi penyusunan Laporan Pertanggungjawaban</small>
                        </div>
                    </div>

                    <div class="p-3 mb-3 rounded-3" style="background-color: #f8fafc; border: 1px solid #e2e8f0;">
                        <p class="text-secondary small mb-2 fw-medium">
                            {{ $templateLpj->keterangan ?? 'Silakan unduh atau buka template berikut sebagai pedoman standar dalam pembuatan dokumen LPJ kepanitiaan.' }}
                        </p>
                        @if($templateLpj->updated_at)
                            <small class="text-muted d-block" style="font-size: 0.75rem;">
                                <i class="bi bi-clock-history me-1"></i> Terakhir diperbarui: {{ $templateLpj->updated_at->translatedFormat('d F Y, H:i') }} WIB
                            </small>
                        @endif
                    </div>

                    <!-- Tombol Akses / Download -->
                    <div class="d-flex flex-column gap-2 mt-3">
                        @if(!empty($templateLpj->link_lpj))
                            <a href="{{ $templateLpj->link_lpj }}" target="_blank" rel="noopener noreferrer" 
                               class="btn btn-primary d-inline-flex align-items-center justify-content-center gap-2 py-2 fw-bold shadow-xs">
                                <i class="bi bi-box-arrow-up-right"></i> Buka Tautan Template LPJ (Google Docs / Drive)
                            </a>
                        @endif

                        @if(!empty($templateLpj->file_template))
                            <a href="{{ route('lpj.template.download') }}" 
                               class="btn btn-outline-success d-inline-flex align-items-center justify-content-center gap-2 py-2 fw-bold shadow-xs">
                                <i class="bi bi-download"></i> Unduh File Template Langsung ({{ basename($templateLpj->file_template) }})
                            </a>
                        @endif

                        @if(empty($templateLpj->link_lpj) && empty($templateLpj->file_template))
                            <div class="alert alert-warning d-flex align-items-center gap-2 m-0 p-3 rounded-3" style="font-size: 0.85rem;">
                                <i class="bi bi-exclamation-triangle-fill fs-5 text-warning flex-shrink-0"></i>
                                <span>Tautan maupun berkas template LPJ belum diatur oleh administrator.</span>
                            </div>
                        @endif

                        <a href="{{ route('lpj.create') }}" class="btn btn-light border d-inline-flex align-items-center justify-content-center gap-2 py-2 mt-2 fw-semibold text-secondary">
                            <i class="bi bi-plus-circle-fill text-success"></i> Buat / Upload LPJ Sekarang
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @if($canEdit)
        <!-- Kolom Kanan: Pengaturan Template LPJ (Admin, Pimpinan, Tim Evaluasi) -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-warning bg-warning-subtle flex-shrink-0" style="width: 40px; height: 40px;">
                            <i class="bi bi-sliders fs-5"></i>
                        </div>
                        <div>
                            <h5 class="card-title p-0 m-0 fw-bold text-dark fs-6">Kelola Tautan & File Template LPJ</h5>
                            <small class="text-muted">Atur tautan template LPJ yang akan tampil di halaman Tambah LPJ</small>
                        </div>
                    </div>

                    <form action="{{ route('lpj.template.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Nama Template -->
                        <div class="mb-3">
                            <label for="nama_template" class="form-label fw-semibold">Nama / Judul Template <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nama_template') is-invalid @enderror" 
                                   id="nama_template" name="nama_template" 
                                   value="{{ old('nama_template', $templateLpj->nama_template ?? 'Template Resmi Laporan Pertanggungjawaban (LPJ)') }}" required>
                            @error('nama_template')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Menu Field: Link LPJ (Google Drive / Docs / OneDrive) -->
                        <div class="mb-3">
                            <label for="link_lpj" class="form-label fw-semibold">
                                <i class="bi bi-link-45deg text-primary me-1"></i> Tautan / Link LPJ (Template)
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-globe"></i></span>
                                <input type="url" class="form-control @error('link_lpj') is-invalid @enderror" 
                                       id="link_lpj" name="link_lpj" 
                                       value="{{ old('link_lpj', $templateLpj->link_lpj ?? '') }}" 
                                       placeholder="Contoh: https://docs.google.com/document/d/.../edit">
                            </div>
                            <small class="text-muted d-block mt-1" style="font-size: 0.78rem;">
                                Tautan ini akan otomatis muncul sebagai tombol <strong>Download Template LPJ</strong> pada form Tambah LPJ.
                            </small>
                            @error('link_lpj')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Upload Berkas Template Alternatif (Opsional) -->
                        <div class="mb-3">
                            <label for="file_template" class="form-label fw-semibold">
                                <i class="bi bi-file-earmark-arrow-up text-success me-1"></i> Upload File Template (.docx, .doc, .pdf)
                            </label>
                            <input type="file" class="form-control @error('file_template') is-invalid @enderror" 
                                   id="file_template" name="file_template" accept=".doc,.docx,.pdf,.xls,.xlsx,.zip">
                            @if(!empty($templateLpj->file_template))
                                <small class="text-success d-block mt-1" style="font-size: 0.78rem;">
                                    <i class="bi bi-check-circle me-1"></i> File saat ini: <strong>{{ basename($templateLpj->file_template) }}</strong>
                                </small>
                            @endif
                            @error('file_template')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Keterangan / Catatan Template -->
                        <div class="mb-3">
                            <label for="keterangan" class="form-label fw-semibold">Keterangan / Panduan</label>
                            <textarea class="form-control @error('keterangan') is-invalid @enderror" 
                                      id="keterangan" name="keterangan" rows="3" 
                                      placeholder="Tambahkan catatan atau petunjuk pengisian template LPJ...">{{ old('keterangan', $templateLpj->keterangan ?? '') }}</textarea>
                            @error('keterangan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="pt-2 border-top d-flex justify-content-end">
                            <button type="submit" class="btn btn-success fw-bold px-4 shadow-xs">
                                <i class="bi bi-check-circle-fill me-1"></i> Simpan Pengaturan Template
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
    </div>
</section>
@endsection
