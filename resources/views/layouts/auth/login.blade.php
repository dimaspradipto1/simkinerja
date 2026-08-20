<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Login SIM KINERJA - Universitas Ibnu Sina</title>
  <meta content="Sistem Informasi Manajemen Kinerja Terpadu Universitas Ibnu Sina" name="description">

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
      --uis-green: #046B26;
      --uis-green-dark: #024718;
      --uis-green-light: #069033;
      --uis-yellow: #FED802;
      --uis-yellow-dark: #d9b800;
      --uis-yellow-light: #ffeb60;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    html, body {
      height: 100%;
      width: 100%;
      font-family: 'Plus Jakarta Sans', sans-serif;
      overflow-x: hidden;
      background-color: #f8fafc;
    }

    /* Full Page Container */
    .fullpage-container {
      min-height: 100vh;
      width: 100vw;
      display: flex;
    }

    /* Left Side - Realistic University Campus Showcase */
    .hero-side {
      flex: 1.2;
      background: linear-gradient(180deg, rgba(15, 23, 42, 0.65) 0%, rgba(15, 23, 42, 0.30) 45%, rgba(15, 23, 42, 0.82) 100%),
                  url("{{ asset('assets/img/gedung.png') }}");
      background-size: cover;
      background-position: center;
      color: #ffffff;
      position: relative;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      padding: 4rem 4.5rem;
      overflow: hidden;
    }

    .hero-logo-box {
      width: 92px;
      height: 92px;
      background: #ffffff;
      border-radius: 1.5rem;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 10px;
      box-shadow: 0 16px 36px rgba(0, 0, 0, 0.4), 0 0 0 3px var(--uis-yellow);
    }

    .hero-logo-box img {
      max-width: 100%;
      max-height: 100%;
      object-fit: contain;
    }

    .hero-text-shadow {
      text-shadow: 0 2px 10px rgba(0, 0, 0, 0.85);
    }

    .badge-univ-gold {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      background: rgba(0, 0, 0, 0.6);
      backdrop-filter: blur(8px);
      border: 1.5px solid var(--uis-yellow);
      color: var(--uis-yellow);
      padding: 0.4rem 1rem;
      border-radius: 50rem;
      font-size: 0.75rem;
      font-weight: 700;
      letter-spacing: 1px;
      text-transform: uppercase;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
    }

    .feature-pill {
      background: rgba(15, 23, 42, 0.65);
      backdrop-filter: blur(12px);
      border: 1px solid rgba(255, 255, 255, 0.3);
      color: #ffffff;
      padding: 0.65rem 1.25rem;
      border-radius: 50rem;
      font-size: 0.875rem;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      gap: 0.6rem;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.35);
      transition: all 0.25s ease;
    }

    .feature-pill:hover {
      background: rgba(15, 23, 42, 0.85);
      border-color: var(--uis-yellow);
      color: var(--uis-yellow);
      transform: translateY(-2px);
    }

    /* Right Side - Login Form */
    .form-side {
      flex: 0.8;
      background: #ffffff;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      padding: 3.5rem 4rem;
      overflow-y: auto;
    }

    .form-header-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      background: rgba(4, 107, 38, 0.08);
      color: var(--uis-green);
      border: 1px solid rgba(4, 107, 38, 0.2);
      font-size: 0.75rem;
      font-weight: 700;
      letter-spacing: 0.5px;
      padding: 0.35rem 0.85rem;
      border-radius: 50rem;
    }

    .form-control {
      border-radius: 0.85rem;
      padding: 0.85rem 1.15rem;
      font-size: 0.95rem;
      border: 1.5px solid #e2e8f0;
      color: #1e293b;
      background-color: #f8fafc;
      transition: all 0.2s ease;
    }

    .form-control:focus {
      background-color: #ffffff;
      border-color: var(--uis-green);
      box-shadow: 0 0 0 4px rgba(4, 107, 38, 0.12);
    }

    .input-group-text {
      border-radius: 0.85rem 0 0 0.85rem;
      border: 1.5px solid #e2e8f0;
      border-right: none;
      background-color: #f8fafc;
      color: #64748b;
      padding-left: 1.15rem;
      padding-right: 0.9rem;
    }

    .input-group:focus-within .input-group-text {
      border-color: var(--uis-green);
      color: var(--uis-green);
      background-color: #ffffff;
    }

    .input-group .form-control {
      border-radius: 0 0.85rem 0.85rem 0;
    }

    .input-group .form-control.has-toggle {
      border-radius: 0;
    }

    .btn-toggle-pwd {
      border-radius: 0 0.85rem 0.85rem 0;
      border: 1.5px solid #e2e8f0;
      border-left: none;
      background-color: #f8fafc;
      color: #64748b;
      cursor: pointer;
      padding-right: 1.15rem;
      transition: all 0.2s ease;
    }

    .input-group:focus-within .btn-toggle-pwd {
      border-color: var(--uis-green);
      background-color: #ffffff;
    }

    .btn-toggle-pwd:hover {
      color: var(--uis-green);
    }

    .btn-submit-uis {
      background: linear-gradient(135deg, var(--uis-green) 0%, var(--uis-green-light) 100%);
      border: none;
      color: #ffffff;
      font-weight: 700;
      font-size: 1rem;
      padding: 0.95rem 1.5rem;
      border-radius: 0.85rem;
      box-shadow: 0 12px 25px -5px rgba(4, 107, 38, 0.35);
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.6rem;
      position: relative;
      overflow: hidden;
    }

    .btn-submit-uis::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 4px;
      background: var(--uis-yellow);
    }

    .btn-submit-uis:hover {
      background: linear-gradient(135deg, var(--uis-green-dark) 0%, var(--uis-green) 100%);
      color: #ffffff;
      transform: translateY(-2px);
      box-shadow: 0 16px 30px -5px rgba(4, 107, 38, 0.45);
    }

    .btn-submit-uis:active {
      transform: translateY(0);
    }

    .system-info-card {
      background: #f8fafc;
      border-left: 4px solid var(--uis-green);
      border-radius: 0.75rem;
      padding: 0.9rem 1.1rem;
      font-size: 0.825rem;
      color: #475569;
    }

    .form-check-input:checked {
      background-color: var(--uis-green);
      border-color: var(--uis-green);
    }

    /* Responsive */
    @media (max-width: 991.98px) {
      .fullpage-container {
        flex-direction: column;
      }
      .hero-side {
        padding: 3rem 2rem;
        min-height: 440px;
      }
      .form-side {
        padding: 3rem 2rem;
      }
    }
  </style>
</head>

<body>
  @include('sweetalert::alert')

  <div class="fullpage-container">

    <!-- Left Side: Realistic Campus Building Showcase -->
    <div class="hero-side">

      <!-- Top Branding -->
      <div class="position-relative z-1">
        <div class="d-flex align-items-center gap-3 mb-4">
          <div class="hero-logo-box">
            <img src="{{ asset('assets/img/logouis.png') }}" alt="Logo UIS">
          </div>
          <div>
            <span class="badge-univ-gold mb-1">
              <i class="bi bi-mortarboard-fill"></i> Portal Resmi
            </span>
            <h4 class="fw-bold text-white hero-text-shadow mb-0" style="letter-spacing: -0.2px;">UNIVERSITAS IBNU SINA</h4>
            <span class="text-white-50 hero-text-shadow small">Batam, Kepulauan Riau</span>
          </div>
        </div>

        <div class="mt-4 pt-2">
          <h1 class="display-5 text-white hero-text-shadow fw-bold mb-2" style="letter-spacing: -0.5px;">
            SIM KINERJA
          </h1>
          <div style="width: 70px; height: 5px; background: var(--uis-yellow); border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.5);" class="mb-3"></div>
          <p class="text-white hero-text-shadow fs-6" style="max-width: 540px; line-height: 1.7; color: rgba(255, 255, 255, 0.95) !important;">
            Sistem Informasi Manajemen Kinerja Terpadu Universitas Ibnu Sina untuk monitoring rencana kerja, tridharma perguruan tinggi, absensi kepanitiaan, serta evaluasi kinerja dosen dan tenaga kependidikan.
          </p>
        </div>
      </div>

      <!-- Middle Badges -->
      <div class="my-4 position-relative z-1">
        <div class="d-flex flex-wrap gap-2">
          <span class="feature-pill">
            <i class="bi bi-patch-check-fill" style="color: var(--uis-yellow);"></i> Unggul & Terpercaya
          </span>
          <span class="feature-pill">
            <i class="bi bi-speedometer2 text-info"></i> Real-time Monitoring
          </span>
          <span class="feature-pill">
            <i class="bi bi-shield-lock-fill" style="color: var(--uis-yellow);"></i> Single Sign-On Aman
          </span>
        </div>
      </div>

      <!-- Bottom University Motto & Copyright -->
      <div class="pt-3 border-top border-white-25 small position-relative z-1 d-flex justify-content-between align-items-center flex-wrap gap-2 hero-text-shadow">
        <span class="text-white">&copy; {{ date('Y') }} Universitas Ibnu Sina Batam</span>
        <span style="color: var(--uis-yellow);" class="fw-bold">
          <i class="bi bi-gem me-1"></i> UIS Batam
        </span>
      </div>
    </div>

    <!-- Right Side: Login Form Section -->
    <div class="form-side">
      <!-- Top header spacing / mobile logo -->
      <div class="d-flex justify-content-between align-items-center mb-4">
        <span class="form-header-badge">
          <i class="bi bi-shield-check"></i> AUTENTIKASI PENGGUNA
        </span>
        <span class="text-muted small d-none d-sm-inline">
          <i class="bi bi-calendar3 me-1"></i> {{ date('d M Y') }}
        </span>
      </div>

      <!-- Main Form Area -->
      <div class="my-auto py-2">
        <div class="mb-4">
          <h2 class="fw-bold text-dark mb-1" style="letter-spacing: -0.5px;">Selamat Datang! 👋</h2>
          <p class="text-muted small mb-0">Silakan masukkan username/email dan password akun SIM KINERJA Anda.</p>
        </div>

        @if (session('success'))
          <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-3" role="alert">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
            <div class="small">{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        @endif

        @if (session('error'))
          <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
            <div class="small">{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        @endif

        @if ($errors->has('login_error'))
          <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-3" role="alert">
            <i class="bi bi-exclamation-octagon-fill me-2 fs-5"></i>
            <div class="small">{{ $errors->first('login_error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        @endif

        <form action="{{ route('login.proses') }}" method="POST" class="needs-validation" novalidate>
          @csrf

          <!-- Username / Email Field -->
          <div class="mb-3">
            <label for="yourUsername" class="form-label fw-semibold text-dark small mb-1">
              Email / NUP / Username <span class="text-danger">*</span>
            </label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-person-badge-fill"></i></span>
              <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" id="yourUsername" value="{{ old('username') }}" placeholder="Masukkan email resmi atau NUP" required autocomplete="username" autofocus>
            </div>
            @error('username')
              <div class="text-danger small mt-1"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
            @enderror
          </div>

          <!-- Password Field -->
          <div class="mb-3">
            <label for="yourPassword" class="form-label fw-semibold text-dark small mb-1">
              Password <span class="text-danger">*</span>
            </label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
              <input type="password" name="password" class="form-control has-toggle @error('password') is-invalid @enderror" id="yourPassword" placeholder="Masukkan password Anda" required autocomplete="current-password">
              <button class="btn btn-toggle-pwd" type="button" id="togglePassword" title="Lihat/Sembunyikan Password">
                <i class="bi bi-eye-slash-fill" id="togglePasswordIcon"></i>
              </button>
            </div>
            @error('password')
              <div class="text-danger small mt-1"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
            @enderror
          </div>

          <!-- Remember Me -->
          <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="form-check mb-0">
              <input class="form-check-input" type="checkbox" name="remember" value="true" id="rememberMe">
              <label class="form-check-label text-muted small" for="rememberMe">
                Ingat sesi saya
              </label>
            </div>
          </div>

          <!-- Submit Button -->
          <button class="btn btn-submit-uis w-100 mb-4" type="submit">
            <i class="bi bi-box-arrow-in-right fs-5"></i>
            <span>Masuk ke Sistem</span>
          </button>

          <!-- System Note -->
          <div class="system-info-card d-flex align-items-start gap-2">
            <i class="bi bi-info-circle-fill text-success fs-6 mt-1"></i>
            <div>
              Portal ini khusus diperuntukkan bagi <strong>Dosen & Tenaga Kependidikan</strong> Universitas Ibnu Sina. Jika mengalami kendala login, silakan hubungi <strong>LPTI UIS</strong>.
            </div>
          </div>
        </form>
      </div>

      <!-- Bottom Info -->
      <div class="text-center pt-3 text-muted small border-top">
        SIM Kinerja &bull; Lembaga Pengembangan Teknologi & Informasi (LPTI) UIS
      </div>
    </div>

  </div>

  <!-- Vendor JS Files -->
  <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const toggleBtn = document.getElementById('togglePassword');
      const passwordInput = document.getElementById('yourPassword');
      const icon = document.getElementById('togglePasswordIcon');

      if (toggleBtn && passwordInput && icon) {
        toggleBtn.addEventListener('click', function () {
          const isPassword = passwordInput.getAttribute('type') === 'password';
          passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
          icon.className = isPassword ? 'bi bi-eye-fill text-success' : 'bi bi-eye-slash-fill';
        });
      }
    });
  </script>
</body>

</html>