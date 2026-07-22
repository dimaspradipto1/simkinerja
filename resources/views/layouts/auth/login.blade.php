<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Login - SIM KINERJA | Universitas Ibnu Sina</title>
  <meta content="Sistem Informasi Manajemen Kinerja Universitas Ibnu Sina" name="description">

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

    .input-group .form-control {
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
      transition: all 0.25 ease;
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

  <main class="d-flex align-items-center justify-content-center min-vh-100 py-4 py-md-5">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-xl-10 col-lg-11">
          <div class="card auth-card">
            <div class="row g-0">
              
              <!-- Left Side: Hero Section -->
              <div class="col-lg-6 auth-hero p-4 p-md-5 d-flex flex-column justify-content-between">
                <div>
                  <div class="logo-box mb-4">
                    <img src="{{ asset('assets/img/logouis.png') }}" alt="Logo UIS">
                  </div>
                  <h6 class="text-uppercase tracking-wider fw-bold text-warning mb-1" style="letter-spacing: 1.5px; font-size: 0.8rem;">Universitas Ibnu Sina</h6>
                  <h2 class="fw-extrabold display-6 mb-3">SIM KINERJA</h2>
                  <p class="lead fs-6 text-white-50 mb-4" style="line-height: 1.6;">
                    Sistem Informasi Manajemen Kinerja Terpadu untuk mewujudkan tata kelola akademik dan pelayanan unggul, terukur, dan transparan.
                  </p>
                </div>

                <div class="my-4">
                  <div class="d-flex flex-wrap gap-2">
                    <span class="badge-feature"><i class="bi bi-lightning-charge-fill text-warning"></i> Transparan & Realtime</span>
                    <span class="badge-feature"><i class="bi bi-graph-up-arrow text-info"></i> Evaluasi Terukur</span>
                    <span class="badge-feature"><i class="bi bi-shield-check text-success"></i> Akuntabel</span>
                  </div>
                </div>

                <div class="pt-3 border-top border-white-10 text-white-50 small">
                  &copy; {{ date('Y') }} Universitas Ibnu Sina Batam. All Rights Reserved.
                </div>
              </div>

              <!-- Right Side: Form Section -->
              <div class="col-lg-6 p-4 p-md-5 d-flex flex-column justify-content-center bg-white">
                <div class="mb-4">
                  <h3 class="fw-bold text-dark mb-1">Selamat Datang! 👋</h3>
                  <p class="text-muted small">Silakan masuk menggunakan akun SIM KINERJA Anda.</p>
                </div>

                @if (session('success'))
                  <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>
                @endif

                @if (session('error'))
                  <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>
                @endif

                @if ($errors->has('login_error'))
                  <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
                    <i class="bi bi-exclamation-octagon-fill me-2 fs-5"></i>
                    <div>{{ $errors->first('login_error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>
                @endif

                <form action="{{ route('login.proses') }}" method="POST" class="needs-validation" novalidate>
                  @csrf

                  <div class="mb-3">
                    <label for="yourUsername" class="form-label fw-semibold text-secondary small">Username / Email</label>
                    <div class="input-group">
                      <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                      <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" id="yourUsername" value="{{ old('username') }}" placeholder="Masukkan email atau username" required>
                    </div>
                    @error('username')
                      <div class="text-danger small mt-1"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                      <label for="yourPassword" class="form-label fw-semibold text-secondary small mb-0">Password</label>
                    </div>
                    <div class="input-group">
                      <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                      <input type="password" name="password" class="form-control has-toggle @error('password') is-invalid @enderror" id="yourPassword" placeholder="Masukkan password Anda" required>
                      <button class="btn btn-toggle-password px-3" type="button" id="togglePassword">
                        <i class="bi bi-eye-slash-fill" id="togglePasswordIcon"></i>
                      </button>
                    </div>
                    @error('password')
                      <div class="text-danger small mt-1"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check mb-0">
                      <input class="form-check-input" type="checkbox" name="remember" value="true" id="rememberMe">
                      <label class="form-check-label text-secondary small" for="rememberMe">
                        Ingat Saya
                      </label>
                    </div>
                  </div>

                  <button class="btn btn-uis w-100 mb-3" type="submit">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Masuk Sekarang
                  </button>

                  <div class="text-center">
                    <p class="small text-muted mb-0">
                      Belum memiliki akun? 
                      <a href="{{ route('register') }}" class="fw-bold text-uis-teal text-decoration-none">Daftar Akun Baru</a>
                    </p>
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