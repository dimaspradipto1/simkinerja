@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
    <h1>Tambah User</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('user.index') }}">User</a></li>
            <li class="breadcrumb-item active">Tambah</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Form Tambah User</h5>

                    <form action="{{ route('user.store') }}" method="POST" class="row g-3">
                        @csrf

                        <div class="col-md-6">
                            <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="Masukkan nama lengkap">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="Masukkan alamat email">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Masukkan password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="roles" class="form-label">Role Akses <span class="text-danger">*</span></label>
                            <select class="form-select select2 @error('roles') is-invalid @enderror" id="roles" name="roles">
                                <option value="" {{ old('roles') ? '' : 'selected' }}>-- Pilih Role Akses --</option>
                                @php
                                    $rolesList = [
                                        'super admin',
                                        'rektor',
                                        'wakil rektor i',
                                        'wakil rektor ii',
                                        'wakil rektor iii',
                                        'kepala biro',
                                        'kepala lpti',
                                        'staff lpti',
                                        'admin akademik universitas',
                                        'staff akademik fakultas',
                                        'admin kemahasiswaan',
                                        'admin ijazah',
                                        'admin perpustakaan',
                                        'admin keuangan universitas',
                                        'staff keuangan fakultas',
                                        'admin kepegawaian',
                                        'admin sarana prasarana',
                                        'admin humas dan publikasi',
                                        'admin kerjasama',
                                        'admin karir dan alumni',
                                        'admin perencanaan dan pengembangan',
                                        'kepala lppm',
                                        'staff lppm',
                                        'kepala lpmi',
                                        'staff lpmi',
                                        'dekan',
                                        'wakil dekan i',
                                        'wakil dekan ii',
                                        'ketua program studi',
                                        'sekretaris program studi',
                                        'kepala upmi',
                                        'kepala uppm',
                                        'staff uppm',
                                        'gkm',
                                        'kepala laboratorium',
                                        'staff laboratorium',
                                        'tata usaha fakultas',
                                    ];
                                @endphp
                                @foreach($rolesList as $roleItem)
                                    <option value="{{ $roleItem }}" {{ strtolower(old('roles', '')) == $roleItem ? 'selected' : '' }}>{{ $roleItem }}</option>
                                @endforeach
                            </select>
                            @error('roles')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="nidn" class="form-label">NIDN</label>
                            <input type="text" class="form-control @error('nidn') is-invalid @enderror" id="nidn" name="nidn" value="{{ old('nidn') }}" placeholder="Masukkan NIDN">
                            @error('nidn')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="unit" class="form-label">Unit</label>
                            <input type="text" class="form-control @error('unit') is-invalid @enderror" id="unit" name="unit" value="{{ old('unit') }}" placeholder="Masukkan Unit">
                            @error('unit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="jabatan" class="form-label">Jabatan</label>
                            <input type="text" class="form-control @error('jabatan') is-invalid @enderror" id="jabatan" name="jabatan" value="{{ old('jabatan') }}" placeholder="Masukkan Jabatan">
                            @error('jabatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="jabatan_pkkmb" class="form-label">Jabatan Kepanitiaan PKKMB</label>
                            <input type="text" class="form-control @error('jabatan_pkkmb') is-invalid @enderror" id="jabatan_pkkmb" name="jabatan_pkkmb" value="{{ old('jabatan_pkkmb') }}" placeholder="Masukkan Jabatan PKKMB">
                            @error('jabatan_pkkmb')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="jabatan_esq" class="form-label">Jabatan Kepanitiaan ESQ</label>
                            <input type="text" class="form-control @error('jabatan_esq') is-invalid @enderror" id="jabatan_esq" name="jabatan_esq" value="{{ old('jabatan_esq') }}" placeholder="Masukkan Jabatan ESQ">
                            @error('jabatan_esq')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="jabatan_milad" class="form-label">Jabatan Kepanitiaan MILAD</label>
                            <input type="text" class="form-control @error('jabatan_milad') is-invalid @enderror" id="jabatan_milad" name="jabatan_milad" value="{{ old('jabatan_milad') }}" placeholder="Masukkan Jabatan MILAD">
                            @error('jabatan_milad')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="jabatan_kuliah_umum" class="form-label">Jabatan Kepanitiaan Kuliah Umum</label>
                            <input type="text" class="form-control @error('jabatan_kuliah_umum') is-invalid @enderror" id="jabatan_kuliah_umum" name="jabatan_kuliah_umum" value="{{ old('jabatan_kuliah_umum') }}" placeholder="Masukkan Jabatan Kuliah Umum">
                            @error('jabatan_kuliah_umum')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="status" class="form-label">Status Pegawai <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('status') is-invalid @enderror" id="status" name="status" value="{{ old('status', 'Aktif') }}" placeholder="Contoh: Aktif">
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="is_active" class="form-label">Status Akun <span class="text-danger">*</span></label>
                            <select class="form-select @error('is_active') is-invalid @enderror" id="is_active" name="is_active">
                                <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                            @error('is_active')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan</button>
                            <a href="{{ route('user.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        if ($.fn.select2) {
            $('#roles').select2({
                theme: 'bootstrap-5',
                placeholder: '-- Pilih Role Akses --',
                allowClear: true,
                width: '100%'
            });
        }
    });
</script>
@endpush
