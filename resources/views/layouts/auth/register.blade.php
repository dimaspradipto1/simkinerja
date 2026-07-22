<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Register - SIM KINERJA | Universitas Ibnu Sina</title>
  <meta content="Registrasi Akun SIM KINERJA Universitas Ibnu Sina" name="description">

  <!-- Favicons -->
  <link href="{{ asset('assets/img/logouis.png') }}" rel="icon">
  <link href="{{ asset('assets/img/logouis.png') }}" rel="apple-touch-icon">

  <!-- Google Fonts: Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">

  <style>
    :root {
      --uis-green-dark: #0f5132;
      --uis-green: #198754;
      --uis-teal: #0d9488;
      --uis-teal-light: #14b8a6;
      --uis-gold: #f59e0b;
      --uis-gradient-hero: linear-gradient(135deg, rgba(15, 81, 50, 0.92) 0%, rgba(13, 148, 136, 0.88) 100%);
      --uis-gradient-btn: linear-gradient(135deg, #0d9488 0%, #15803d 100%);
      --uis-gradient-btn-hover: linear-gradient(135deg, #0f766e 0%, #166534 100%);
    }

    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background: linear-gradient(135deg, #f0fdf4 0%, #e0f2fe 50%, #f1f5f9 100%);
      min-vh-100: 100vh;
      color: #1e293b;
    }

    .auth-card {
      border: none;
      border-radius: 1.75rem;
      box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.12);
      overflow: hidden;
      background: #ffffff;
      transition: all 0.3s ease;
    }

    .auth-hero {
      background: var(--uis-gradient-hero), url("{{ asset('assets/img/gedung.png') }}");
      background-size: cover;
      background-position: center;
      color: #ffffff;
      position: relative;
      overflow: hidden;
    }

    .auth-hero::before {
      content: '';
      position: absolute;
      top: -50px;
      right: -50px;
      width: 200px;
      height: 200px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
      pointer-events: none;
    }

    .auth-hero::after {
      content: '';
      position: absolute;
      bottom: -80px;
      left: -80px;
      width: 260px;
      height: 260px;
      background: rgba(245, 158, 11, 0.15);
      border-radius: 50%;
      pointer-events: none;
    }

    .logo-box {
      width: 80px;
      height: 80px;
      background: rgba(255, 255, 255, 0.95);
      border-radius: 1.25rem;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
      backdrop-filter: blur(8px);
    }

    .logo-box img {
      max-height: 56px;
      object-fit: contain;
    }

    .badge-feature {
      background: rgba(255, 255, 255, 0.18);
      backdrop-filter: blur(6px);
      border: 1px solid rgba(255, 255, 255, 0.25);
      color: #ffffff;
      padding: 0.5rem 1rem;
      border-radius: 50rem;
      font-size: 0.85rem;
      font-weight: 500;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
    }

    .form-control, .form-select {
      border-radius: 0.75rem;
      padding: 0.75rem 1rem;
      font-size: 0.95rem;
      border: 1.5px solid #e2e8f0;
      transition: all 0.2s ease;
    }

    .form-control:focus, .form-select:focus {
      border-color: var(--uis-teal);
      box-shadow: 0 0 0 4px rgba(13, 148, 136, 0.15);
    }

    .input-group-text {
      border-radius: 0.75rem 0 0 0.75rem;
      border: 1.5px solid #e2e8f0;
      border-right: none;
      background-color: #f8fafc;
      color: #64748b;
    }

    .input-group .form-control, .input-group .form-select {
      border-radius: 0 0.75rem 0.75rem 0;
    }

    .input-group .form-control.has-toggle {
      border-radius: 0;
    }

    .btn-toggle-password {
      border-radius: 0 0.75rem 0.75rem 0;
      border: 1.5px solid #e2e8f0;
      border-left: none;
      background-color: #f8fafc;
      color: #64748b;
      cursor: pointer;
    }

    .btn-toggle-password:hover {
      background-color: #f1f5f9;
      color: #1e293b;
    }

    .btn-uis {
      background: var(--uis-gradient-btn);
      border: none;
      color: #ffffff;
      font-weight: 600;
      padding: 0.85rem 1.5rem;
      border-radius: 0.75rem;
      box-shadow: 0 10px 20px -5px rgba(13, 148, 136, 0.4);
      transition: all 0.25s ease;
    }

    .btn-uis:hover {
      background: var(--uis-gradient-btn-hover);
      color: #ffffff;
      transform: translateY(-2px);
      box-shadow: 0 14px 24px -5px rgba(13, 148, 136, 0.5);
    }

    .btn-uis:active {
      transform: translateY(0);
    }

    .text-uis-teal {
      color: var(--uis-teal);
    }

    .alert {
      border-radius: 0.75rem;
      font-size: 0.9rem;
    }
  </style>
</head>

<body>
  @include('sweetalert::alert')

  <main class="d-flex align-items-center justify-content-center min-vh-100 py-4 py-md-5">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-xl-11 col-lg-12">
          <div class="card auth-card">
            <div class="row g-0">
              
              <!-- Left Side: Hero Section -->
              <div class="col-lg-5 auth-hero p-4 p-md-5 d-flex flex-column justify-content-between">
                <div>
                  <div class="logo-box mb-4">
                    <img src="{{ asset('assets/img/logouis.png') }}" alt="Logo UIS">
                  </div>
                  <h6 class="text-uppercase tracking-wider fw-bold text-warning mb-1" style="letter-spacing: 1.5px; font-size: 0.8rem;">Universitas Ibnu Sina</h6>
                  <h2 class="fw-extrabold display-6 mb-3">Registrasi Akun</h2>
                  <p class="lead fs-6 text-white-50 mb-4" style="line-height: 1.6;">
                    Bergabunglah ke dalam ekosistem digital SIM KINERJA untuk kemudahan pengelolaan kinerja civitas akademika.
                  </p>
                </div>

                <div class="my-4">
                  <div class="d-flex flex-column gap-2">
                    <span class="badge-feature"><i class="bi bi-person-check-fill text-warning"></i> Registrasi Cepat & Mudah</span>
                    <span class="badge-feature"><i class="bi bi-layers-fill text-info"></i> Akses Terintegrasi Sesuai Role</span>
                    <span class="badge-feature"><i class="bi bi-shield-lock-fill text-success"></i> Keamanan Data Terjamin</span>
                  </div>
                </div>

                <div class="pt-3 border-top border-white-10 text-white-50 small">
                  &copy; {{ date('Y') }} Universitas Ibnu Sina Batam.
                </div>
              </div>

              <!-- Right Side: Form Section -->
              <div class="col-lg-7 p-4 p-md-5 d-flex flex-column justify-content-center bg-white">
                <div class="mb-4">
                  <h3 class="fw-bold text-dark mb-1">Buat Akun Baru ✨</h3>
                  <p class="text-muted small">Silakan lengkapi form di bawah ini untuk mendaftarkan akun SIM KINERJA Anda.</p>
                </div>

                @if ($errors->any())
                  <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <div class="d-flex align-items-center mb-1">
                      <i class="bi bi-exclamation-octagon-fill me-2 fs-5"></i>
                      <strong>Mohon perbaiki kesalahan berikut:</strong>
                    </div>
                    <ul class="mb-0 ps-4 small">
                      @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                      @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>
                @endif

                <form action="{{ route('register.proses') }}" method="POST" class="needs-validation" novalidate>
                  @csrf

                  <div class="row g-3">
                    
                    <!-- Nama Lengkap -->
                    <div class="col-md-6">
                      <label for="yourName" class="form-label fw-semibold text-secondary small">Nama Lengkap</label>
                      <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" id="yourName" value="{{ old('name') }}" placeholder="Nama lengkap & gelar" required>
                      </div>
                    </div>

                    <!-- Email -->
                    <div class="col-md-6">
                      <label for="yourEmail" class="form-label fw-semibold text-secondary small">Alamat Email</label>
                      <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" id="yourEmail" value="{{ old('email') }}" placeholder="nama@uis.ac.id" required>
                      </div>
                    </div>

                    <!-- NIP / NIDN -->
                    <div class="col-md-6">
                      <label for="yourNidn" class="form-label fw-semibold text-secondary small">NIP / NIDN</label>
                      <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-card-heading"></i></span>
                        <input type="text" name="nidn" class="form-control" id="yourNidn" value="{{ old('nidn') }}" placeholder="Contoh: 1028067804">
                      </div>
                    </div>

                    <!-- Unit Kerja -->
                    <div class="col-md-6">
                      <label for="yourUnit" class="form-label fw-semibold text-secondary small">Unit Kerja</label>
                      <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-building"></i></span>
                        <input type="text" name="unit" class="form-control" id="yourUnit" value="{{ old('unit') }}" placeholder="Contoh: Div. Sistem Informasi">
                      </div>
                    </div>

                    <!-- Jabatan -->
                    <div class="col-md-6">
                      <label for="yourJabatan" class="form-label fw-semibold text-secondary small">Jabatan</label>
                      <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-briefcase-fill"></i></span>
                        <input type="text" name="jabatan" class="form-control" id="yourJabatan" value="{{ old('jabatan') }}" placeholder="Contoh: Staff Pengembangan">
                      </div>
                    </div>

                    <!-- Level Akses -->
                    <div class="col-md-6">
                      <label for="yourRoles" class="form-label fw-semibold text-secondary small">Level Akses</label>
                      <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-shield-lock-fill"></i></span>
                        <select name="roles" class="form-select" id="yourRoles">
                          <option value="Standar" {{ old('roles') == 'Standar' ? 'selected' : '' }}>Standar</option>
                          <option value="Full Akses" {{ old('roles') == 'Full Akses' ? 'selected' : '' }}>Full Akses</option>
                          <option value="Admin" {{ old('roles') == 'Admin' ? 'selected' : '' }}>Admin</option>
                          <option value="Super Admin" {{ old('roles') == 'Super Admin' ? 'selected' : '' }}>Super Admin</option>
                        </select>
                      </div>
                    </div>

                    <!-- Password -->
                    <div class="col-12">
                      <label for="yourPassword" class="form-label fw-semibold text-secondary small">Password</label>
                      <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" name="password" class="form-control has-toggle @error('password') is-invalid @enderror" id="yourPassword" placeholder="Minimal 6 karakter" required>
                        <button class="btn btn-toggle-password px-3" type="button" id="togglePassword">
                          <i class="bi bi-eye-slash-fill" id="togglePasswordIcon"></i>
                        </button>
                      </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="col-12 mt-4">
                      <button class="btn btn-uis w-100" type="submit">
                        <i class="bi bi-person-plus-fill me-2"></i>Daftar Akun Sekarang
                      </button>
                    </div>

                    <div class="col-12 text-center mt-3">
                      <p class="small text-muted mb-0">
                        Sudah memiliki akun? 
                        <a href="{{ route('login') }}" class="fw-bold text-uis-teal text-decoration-none">Masuk di Sini</a>
                      </p>
                    </div>

                  </div>
                </form>

              </div>

            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Vendor JS Files -->
  <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const toggleBtn = document.getElementById('togglePassword');
      const passwordInput = document.getElementById('yourPassword');
      const icon = document.getElementById('togglePasswordIcon');

      if (toggleBtn && passwordInput && icon) {
        toggleBtn.addEventListener('click', function () {
          const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
          passwordInput.setAttribute('type', type);
          icon.classList.toggle('bi-eye-fill');
          icon.classList.toggle('bi-eye-slash-fill');
        });
      }
    });
  </script>
</body>

</html>
