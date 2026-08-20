@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
    <h1>Update Password</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('profile') }}">Profil</a></li>
            <li class="breadcrumb-item active">Update Password</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                    <i class="bi bi-shield-lock-fill text-success fs-5"></i>
                    <h5 class="card-title p-0 m-0 fw-bold text-dark">Ganti Password Akun</h5>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted small mb-4">
                        Perbarui password akun Anda untuk menjaga keamanan akses sistem. Masukkan password baru dengan minimal 6 karakter.
                    </p>

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0 small">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('profile.password.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold text-dark">
                                Password Baru <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-key-fill text-muted"></i></span>
                                <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required minlength="6" placeholder="Masukkan password baru (min. 6 karakter)">
                                <button class="btn btn-outline-secondary toggle-pwd" type="button" data-target="password" title="Lihat/Sembunyikan Password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label fw-semibold text-dark">
                                Konfirmasi Password Baru <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-check2-circle text-muted"></i></span>
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required minlength="6" placeholder="Ketik ulang password baru">
                                <button class="btn btn-outline-secondary toggle-pwd" type="button" data-target="password_confirmation" title="Lihat/Sembunyikan Password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <a href="{{ route('profile') }}" class="btn btn-secondary px-3">
                                <i class="bi bi-arrow-left me-1"></i> Kembali ke Profil
                            </a>
                            <button type="submit" class="btn btn-success px-4 text-white fw-bold shadow-sm" style="background-color: #15432d; border-color: #15432d;">
                                <i class="bi bi-save me-1"></i> Simpan Password Baru
                            </button>
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
        $('.toggle-pwd').on('click', function() {
            var targetId = $(this).data('target');
            var input = $('#' + targetId);
            var icon = $(this).find('i');

            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                icon.removeClass('bi-eye').addClass('bi-eye-slash');
            } else {
                input.attr('type', 'password');
                icon.removeClass('bi-eye-slash').addClass('bi-eye');
            }
        });
    });
</script>
@endpush
