@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
    <h1>Edit User</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('user.index') }}">User</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Form Edit User</h5>

                    <form action="{{ route('user.update', $user->id) }}" method="POST" class="row g-3">
                        @csrf
                        @method('PUT')

                        <div class="col-md-6">
                            <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" placeholder="Masukkan nama lengkap">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" placeholder="Masukkan alamat email">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="roles" class="form-label">Role Akses <span class="text-danger">*</span></label>
                            <select class="form-select @error('roles') is-invalid @enderror" id="roles" name="roles">
                                <option value="" disabled>-- Pilih Role Akses --</option>
                                @php
                                    $rolesList = [
                                        'SUPER ADMIN',
                                        'REKTOR',
                                        'WAKIL REKTOR I',
                                        'WAKIL REKTOR II',
                                        'WAKIL REKTOR III',
                                        'KEPALA BIRO',
                                        'KEPALA ICT',
                                        'ADMIN ICT',
                                        'ADMIN AKADEMIK UNIVERSITAS',
                                        'STAFF AKADEMIK FAKULTAS',
                                        'ADMIN KEMAHASISWAAN',
                                        'ADMIN IJAZAH',
                                        'ADMIN PERPUSTAKAAN',
                                        'ADMIN KEUANGAN',
                                        'KASIR',
                                        'ADMIN SDM',
                                        'ADMIN SARPRAS',
                                        'ADMIN HUMAS',
                                        'ADMIN KERJASAMA',
                                        'ADMIN KEMAHASISWAAN PRESTASI',
                                        'ADMIN KARIR ALUMNI',
                                        'ADMIN PERENCANAAN',
                                        'ADMIN LPPM',
                                        'ADMIN HAKI',
                                        'ADMIN LPMI',
                                        'DEKAN',
                                        'WAKIL DEKAN I',
                                        'WAKIL DEKAN II',
                                        'KAPRODI',
                                        'SEKRETARIS PRODI',
                                        'DOSEN',
                                        'DOSEN PEMBIMBING',
                                        'STAFF TU FAKULTAS',
                                        'ADMIN LABORATORIUM',
                                        'MAHASISWA',
                                        'ALUMNI',
                                    ];
                                @endphp
                                @foreach($rolesList as $roleItem)
                                    <option value="{{ $roleItem }}" {{ old('roles', $user->roles) == $roleItem ? 'selected' : '' }}>{{ $roleItem }}</option>
                                @endforeach
                            </select>
                            @error('roles')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="nidn" class="form-label">NIDN <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nidn') is-invalid @enderror" id="nidn" name="nidn" value="{{ old('nidn', $user->nidn) }}" placeholder="Masukkan NIDN">
                            @error('nidn')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="unit" class="form-label">Unit <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('unit') is-invalid @enderror" id="unit" name="unit" value="{{ old('unit', $user->unit) }}" placeholder="Masukkan Unit">
                            @error('unit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="jabatan" class="form-label">Jabatan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('jabatan') is-invalid @enderror" id="jabatan" name="jabatan" value="{{ old('jabatan', $user->jabatan) }}" placeholder="Masukkan Jabatan">
                            @error('jabatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="status" class="form-label">Status Pegawai <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('status') is-invalid @enderror" id="status" name="status" value="{{ old('status', $user->status) }}" placeholder="Contoh: Aktif">
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="is_active" class="form-label">Status Akun <span class="text-danger">*</span></label>
                            <select class="form-select @error('is_active') is-invalid @enderror" id="is_active" name="is_active">
                                <option value="1" {{ old('is_active', $user->is_active) == '1' ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ old('is_active', $user->is_active) == '0' ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                            @error('is_active')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Perbarui</button>
                            <a href="{{ route('user.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</section>
@endsection
