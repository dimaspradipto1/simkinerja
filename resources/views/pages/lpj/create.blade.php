@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
    <h1>Tambah LPJ</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item">Rekapitulasi & Laporan</li>
            <li class="breadcrumb-item"><a href="{{ route('lpj.index') }}">LPJ</a></li>
            <li class="breadcrumb-item active">Tambah</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-primary bg-primary-subtle" style="width: 36px; height: 36px;">
                            <i class="bi bi-file-earmark-plus-fill fs-5"></i>
                        </div>
                        <div>
                            <h5 class="card-title p-0 m-0 fw-bold text-dark fs-6">Form Tambah Laporan Pertanggungjawaban (LPJ)</h5>
                            <small class="text-muted">Isi formulir berikut untuk mengarsipkan berkas dan tautan LPJ</small>
                        </div>
                    </div>

                    <form action="{{ route('lpj.store') }}" method="POST" enctype="multipart/form-data" class="row g-3">
                        @csrf

                        <!-- Nama / Judul LPJ -->
                        <div class="col-md-8">
                            <label for="nama_lpj" class="form-label fw-semibold">Nama / Judul LPJ <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('nama_lpj') is-invalid @enderror" 
                                   id="nama_lpj" 
                                   name="nama_lpj" 
                                   value="{{ old('nama_lpj') }}" 
                                   placeholder="Contoh: LPJ Kegiatan Workshop Digitalisasi Kampus 2026" 
                                   required>
                            @error('nama_lpj')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Jenis Kegiatan -->
                        <div class="col-md-4">
                            <label for="jenis_kegiatan" class="form-label fw-semibold">Jenis Kegiatan <span class="text-danger">*</span></label>
                            <select class="form-select @error('jenis_kegiatan') is-invalid @enderror" id="jenis_kegiatan" name="jenis_kegiatan" required>
                                <option value="" disabled {{ old('jenis_kegiatan') ? '' : 'selected' }}>-- Pilih Jenis Kegiatan --</option>
                                @foreach($jenisKegiatanOptions as $opt)
                                    <option value="{{ $opt }}" {{ old('jenis_kegiatan') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                            </select>
                            @error('jenis_kegiatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Tanggal Kegiatan -->
                        <div class="col-md-4">
                            <label for="tanggal_kegiatan" class="form-label fw-semibold">Tanggal Pelaksanaan</label>
                            <input type="date" 
                                   class="form-control @error('tanggal_kegiatan') is-invalid @enderror" 
                                   id="tanggal_kegiatan" 
                                   name="tanggal_kegiatan" 
                                   value="{{ old('tanggal_kegiatan') }}">
                            @error('tanggal_kegiatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Upload Berkas File LPJ -->
                        <div class="col-md-4">
                            <label for="file_lpj" class="form-label fw-semibold">Unggah Berkas File (PDF / Word / ZIP)</label>
                            <input type="file" 
                                   class="form-control @error('file_lpj') is-invalid @enderror" 
                                   id="file_lpj" 
                                   name="file_lpj" 
                                   accept=".pdf,.doc,.docx,.xls,.xlsx,.zip,.rar,.png,.jpg,.jpeg">
                            <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">Maksimal 20 MB (.pdf, .docx, .zip, dll)</small>
                            @error('file_lpj')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Link Google Drive / Cloud LPJ -->
                        <div class="col-md-4">
                            <label for="link_lpj" class="form-label fw-semibold">Tautan / Link LPJ (Opsional)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
                                <input type="url" 
                                       class="form-control @error('link_lpj') is-invalid @enderror" 
                                       id="link_lpj" 
                                       name="link_lpj" 
                                       value="{{ old('link_lpj') }}" 
                                       placeholder="https://drive.google.com/...">
                            </div>
                            <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">Link Google Drive, OneDrive, atau Dokumen Cloud</small>
                            @error('link_lpj')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Keterangan / Catatan -->
                        <div class="col-md-12">
                            <label for="keterangan" class="form-label fw-semibold">Keterangan / Ringkasan LPJ</label>
                            <textarea class="form-control @error('keterangan') is-invalid @enderror" 
                                      id="keterangan" 
                                      name="keterangan" 
                                      rows="4" 
                                      placeholder="Tambahkan ringkasan atau rincian penting terkait laporan pertanggungjawaban ini...">{{ old('keterangan') }}</textarea>
                            @error('keterangan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 mt-4 pt-3 border-top d-flex gap-2">
                            <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1.5 px-4">
                                <i class="bi bi-save"></i> Simpan LPJ
                            </button>
                            <a href="{{ route('lpj.index') }}" class="btn btn-secondary d-inline-flex align-items-center gap-1.5 px-3">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</section>
@endsection
