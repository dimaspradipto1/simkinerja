@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
    <h1>Edit Periode Akademik</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('periode-akademik.index') }}">Periode Akademik</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Form Edit Periode Akademik</h5>

                    <form action="{{ route('periode-akademik.update', $periodeAkademik->id) }}" method="POST" class="row g-3">
                        @csrf
                        @method('PUT')

                        <div class="col-md-12">
                            <label for="nama_periode" class="form-label">Nama Periode Akademik <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nama_periode') is-invalid @enderror" id="nama_periode" name="nama_periode" value="{{ old('nama_periode', $periodeAkademik->nama_periode) }}" placeholder="Contoh: 2026/2027 Gasal">
                            @error('nama_periode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Perbarui</button>
                            <a href="{{ route('periode-akademik.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</section>
@endsection
