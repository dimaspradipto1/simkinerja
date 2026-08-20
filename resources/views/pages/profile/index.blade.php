@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
    <h1>Profil Saya</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Profil</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section profile">
    <div class="row">
        <!-- Left Side: Profile Card Summary -->
        <div class="col-xl-4">
            <div class="card shadow-sm border-0">
                <div class="card-body profile-card pt-4 d-flex flex-column align-items-center text-center">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&size=128&background=15432d&color=ffffff&bold=true" alt="Profile" class="rounded-circle shadow-sm mb-3" style="width: 110px; height: 110px;">
                    <h5 class="fw-bold text-dark mb-1">{{ $user->name }}</h5>
                    <div class="d-flex flex-wrap justify-content-center gap-1 mb-2">
                        @php
                            $pRoles = array_filter(array_map('trim', explode(',', $user->roles ?? 'Staff')));
                        @endphp
                        @foreach($pRoles as $r)
                            <span class="badge bg-primary px-2 py-1 text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">{{ $r }}</span>
                        @endforeach
                    </div>
                    <p class="text-muted small mb-3"><i class="bi bi-envelope me-1"></i> {{ $user->email }}</p>

                    <div class="w-100 border-top pt-3 text-start small">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Unit / Bagian:</span>
                            <span class="fw-semibold text-dark">{{ $user->unit ?: '-' }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Jabatan Pokok:</span>
                            <span class="fw-semibold text-dark">{{ $user->jabatan ?: '-' }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">NIDN:</span>
                            <span class="fw-semibold text-dark">{{ $user->nidn ?: '-' }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Status Akun:</span>
                            <span>
                                {!! $user->is_active ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-danger">Nonaktif</span>' !!}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Details & Edit Tabs -->
        <div class="col-xl-8">
            <div class="card shadow-sm border-0">
                <div class="card-body pt-3">
                    <!-- Bordered Tabs -->
                    <ul class="nav nav-tabs nav-tabs-bordered" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-semibold" data-bs-toggle="tab" data-bs-target="#profile-overview" role="tab">
                                <i class="bi bi-person-lines-fill me-1"></i> Ringkasan Profil
                            </button>
                        </li>

                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#profile-edit" role="tab">
                                <i class="bi bi-pencil-square me-1"></i> Edit Profil
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content pt-4">

                        <!-- Tab 1: Ringkasan Profil -->
                        <div class="tab-pane fade show active profile-overview" id="profile-overview" role="tabpanel">
                            <h6 class="card-title fw-bold text-success mb-3" style="color: #15432d !important;">Informasi Kepegawaian</h6>

                            <div class="row mb-3">
                                <div class="col-lg-4 col-md-4 text-muted fw-semibold">Nama Lengkap</div>
                                <div class="col-lg-8 col-md-8 fw-bold text-dark">{{ $user->name }}</div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-lg-4 col-md-4 text-muted fw-semibold">Email Resmi</div>
                                <div class="col-lg-8 col-md-8 text-dark">{{ $user->email }}</div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-lg-4 col-md-4 text-muted fw-semibold">Role Sistem</div>
                                <div class="col-lg-8 col-md-8 d-flex flex-wrap gap-1">
                                    @foreach($pRoles as $r)
                                        <span class="badge bg-secondary text-uppercase">{{ $r }}</span>
                                    @endforeach
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-lg-4 col-md-4 text-muted fw-semibold">NUP</div>
                                <div class="col-lg-8 col-md-8 text-dark">{{ $user->nidn ?: '-' }}</div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-lg-4 col-md-4 text-muted fw-semibold">Unit / Divisi</div>
                                <div class="col-lg-8 col-md-8 text-dark">{{ $user->unit ?: '-' }}</div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-lg-4 col-md-4 text-muted fw-semibold">Jabatan Pokok</div>
                                <div class="col-lg-8 col-md-8 text-dark">{{ $user->jabatan ?: '-' }}</div>
                            </div>

                            <hr class="my-4">

                            <h6 class="card-title fw-bold text-success mb-3" style="color: #15432d !important;">Jabatan Kepanitiaan</h6>

                            <div class="row mb-3">
                                <div class="col-lg-4 col-md-4 text-muted fw-semibold">Jabatan PKKMB</div>
                                <div class="col-lg-8 col-md-8 text-dark">{{ $user->jabatan_pkkmb ?: '-' }}</div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-lg-4 col-md-4 text-muted fw-semibold">Jabatan ESQ</div>
                                <div class="col-lg-8 col-md-8 text-dark">{{ $user->jabatan_esq ?: '-' }}</div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-lg-4 col-md-4 text-muted fw-semibold">Jabatan MILAD</div>
                                <div class="col-lg-8 col-md-8 text-dark">{{ $user->jabatan_milad ?: '-' }}</div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-lg-4 col-md-4 text-muted fw-semibold">Jabatan Kuliah Umum</div>
                                <div class="col-lg-8 col-md-8 text-dark">{{ $user->jabatan_kuliah_umum ?: '-' }}</div>
                            </div>
                        </div>

                        <!-- Tab 2: Edit Profil Form -->
                        <div class="tab-pane fade profile-edit pt-2" id="profile-edit" role="tabpanel">
                            <form action="{{ route('profile.update') }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="row mb-3">
                                    <label for="name" class="col-md-4 col-lg-3 col-form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                                    <div class="col-md-8 col-lg-9">
                                        <input name="name" type="text" class="form-control @error('name') is-invalid @enderror" id="name" value="{{ old('name', $user->name) }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="email" class="col-md-4 col-lg-3 col-form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                    <div class="col-md-8 col-lg-9">
                                        <input name="email" type="email" class="form-control @error('email') is-invalid @enderror" id="email" value="{{ old('email', $user->email) }}" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="nidn" class="col-md-4 col-lg-3 col-form-label fw-semibold">NUP</label>
                                    <div class="col-md-8 col-lg-9">
                                        <input name="nidn" type="text" class="form-control @error('nidn') is-invalid @enderror" id="nidn" value="{{ old('nidn', $user->nidn) }}" placeholder="Nomor Urut Pegawai / Pendidik">
                                        @error('nidn')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="unit" class="col-md-4 col-lg-3 col-form-label fw-semibold">Unit / Divisi</label>
                                    <div class="col-md-8 col-lg-9">
                                        <input name="unit" type="text" class="form-control @error('unit') is-invalid @enderror" id="unit" value="{{ old('unit', $user->unit) }}" placeholder="Contoh: BAAK, REKTORAT, LPTI">
                                        @error('unit')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="jabatan" class="col-md-4 col-lg-3 col-form-label fw-semibold">Jabatan Pokok</label>
                                    <div class="col-md-8 col-lg-9">
                                        <input name="jabatan" type="text" class="form-control @error('jabatan') is-invalid @enderror" id="jabatan" value="{{ old('jabatan', $user->jabatan) }}" placeholder="Contoh: Staff Programmer, Dosen, Kepala Biro">
                                        @error('jabatan')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <hr class="my-4">
                                <h6 class="fw-bold text-success mb-3" style="color: #15432d !important;">Jabatan Kepanitiaan</h6>

                                <div class="row mb-3">
                                    <label for="jabatan_pkkmb" class="col-md-4 col-lg-3 col-form-label fw-semibold">Jabatan PKKMB</label>
                                    <div class="col-md-8 col-lg-9">
                                        <input name="jabatan_pkkmb" type="text" class="form-control" id="jabatan_pkkmb" value="{{ old('jabatan_pkkmb', $user->jabatan_pkkmb) }}" placeholder="Jabatan kepanitiaan PKKMB">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="jabatan_esq" class="col-md-4 col-lg-3 col-form-label fw-semibold">Jabatan ESQ</label>
                                    <div class="col-md-8 col-lg-9">
                                        <input name="jabatan_esq" type="text" class="form-control" id="jabatan_esq" value="{{ old('jabatan_esq', $user->jabatan_esq) }}" placeholder="Jabatan kepanitiaan ESQ">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="jabatan_milad" class="col-md-4 col-lg-3 col-form-label fw-semibold">Jabatan MILAD</label>
                                    <div class="col-md-8 col-lg-9">
                                        <input name="jabatan_milad" type="text" class="form-control" id="jabatan_milad" value="{{ old('jabatan_milad', $user->jabatan_milad) }}" placeholder="Jabatan kepanitiaan MILAD">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="jabatan_kuliah_umum" class="col-md-4 col-lg-3 col-form-label fw-semibold">Jabatan Kuliah Umum</label>
                                    <div class="col-md-8 col-lg-9">
                                        <input name="jabatan_kuliah_umum" type="text" class="form-control" id="jabatan_kuliah_umum" value="{{ old('jabatan_kuliah_umum', $user->jabatan_kuliah_umum) }}" placeholder="Jabatan kepanitiaan Kuliah Umum">
                                    </div>
                                </div>

                                @if($user->isAdmin() || $user->isSuperAdmin())
                                <div class="row mb-3">
                                    <label for="roles" class="col-md-4 col-lg-3 col-form-label fw-semibold">Role Akses</label>
                                    <div class="col-md-8 col-lg-9">
                                        <input name="roles" type="text" class="form-control" id="roles" value="{{ old('roles', $user->roles) }}">
                                    </div>
                                </div>
                                @endif

                                <div class="text-end mt-4">
                                    <button type="submit" class="btn btn-success px-4 text-white fw-bold" style="background-color: #15432d; border-color: #15432d;">
                                        <i class="bi bi-save me-1"></i> Simpan Perubahan
                                    </button>
                                </div>
                            </form>
                        </div>

                    </div><!-- End Bordered Tabs -->
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
