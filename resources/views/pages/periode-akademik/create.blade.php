@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
    <h1>Tambah Periode Akademik</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('periode-akademik.index') }}">Periode Akademik</a></li>
            <li class="breadcrumb-item active">Tambah</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Form Tambah Periode Akademik</h5>

                    <form action="{{ route('periode-akademik.store') }}" method="POST" class="row g-3">
                        @csrf

                        <div class="col-md-12">
                            <label for="nama_periode" class="form-label">Nama Periode Akademik <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nama_periode') is-invalid @enderror" id="nama_periode" name="nama_periode" value="{{ old('nama_periode') }}" placeholder="Contoh: 2026/2027 Gasal">
                            @error('nama_periode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active') ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold text-dark" for="is_active">
                                    Jadikan sebagai Periode Akademik Aktif Utama
                                </label>
                                <div class="form-text text-muted small">Jika diaktifkan, periode ini akan terpilih secara otomatis pada setiap formulir kegiatan / rencana kerja.</div>
                            </div>
                        </div>

                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan</button>
                            <a href="{{ route('periode-akademik.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</section>
@endsection
