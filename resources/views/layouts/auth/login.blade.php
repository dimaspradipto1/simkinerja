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

    <!-- Google Fonts: Playfair Display (Serif Elegant) & Plus Jakarta Sans (Modern Sans) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">

    <style>
        :root {
            --uis-dark: #0f172a;
            --uis-slate: #1e293b;
            --uis-gray: #475569;
            --uis-green: #046B26;
            --uis-yellow: #FED802;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html,
        body {
            height: 100vh;
            max-height: 100vh;
            width: 100vw;
            font-family: 'Plus Jakarta Sans', sans-serif;
            overflow: hidden !important;
            background-color: #0f172a;
        }

        /* Fullscreen Scenic Background with Campus Image - Strictly 1 Screen No Scroll */
        .login-wrapper {
            height: 100vh;
            max-height: 100vh;
            width: 100%;
            position: relative;
            background-image: url("{{ asset('assets/img/gedung.png') }}");
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
            display: flex;
            align-items: center;
            overflow: hidden;
        }

        /* Ambient Lighting / Vignette Overlay */
        .login-bg-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 75% 50%, rgba(255, 255, 255, 0.08) 0%, rgba(15, 23, 42, 0.42) 100%),
                linear-gradient(180deg, rgba(15, 23, 42, 0.28) 0%, rgba(15, 23, 42, 0.55) 100%);
            pointer-events: none;
            z-index: 1;
        }

        .login-content-container {
            position: relative;
            z-index: 2;
            width: 100%;
            height: 100vh;
            max-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 1.5rem 3.5rem;
            overflow: hidden;
        }

        /* Left Side Floating Quote / Vision Card */
        .hero-quote-card {
            background: rgba(30, 41, 59, 0.58);
            backdrop-filter: blur(18px) saturate(180%);
            -webkit-backdrop-filter: blur(18px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 20px 24px;
            max-width: 420px;
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.3);
            color: #ffffff;
            transition: all 0.3s ease;
        }

        .hero-quote-card:hover {
            background: rgba(30, 41, 59, 0.7);
            border-color: rgba(255, 255, 255, 0.35);
        }

        .hero-quote-title {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 1.35rem;
            font-weight: 600;
            line-height: 1.35;
            color: #ffffff;
            margin-bottom: 8px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
        }

        .hero-quote-desc {
            font-size: 0.84rem;
            line-height: 1.55;
            color: rgba(255, 255, 255, 0.88);
            margin-bottom: 0;
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.4);
        }

        /* Pagination Dots Under Quote */
        .hero-pagination {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 1rem;
        }

        .page-pill {
            width: 36px;
            height: 5px;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.4);
            display: inline-block;
        }

        .page-dot {
            width: 6px;
            height: 6px;
            background: rgba(255, 255, 255, 0.65);
            border-radius: 50%;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.4);
            display: inline-block;
        }

        /* Right Side: Clean & Elegant Frosted Glass Login Card */
        .glass-login-card {
            background: rgba(255, 255, 255, 0.52);
            backdrop-filter: blur(28px) saturate(190%);
            -webkit-backdrop-filter: blur(28px) saturate(190%);
            border: 1.5px solid rgba(255, 255, 255, 0.75);
            border-radius: 30px;
            padding: 32px 38px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.22), inset 0 1px 0 rgba(255, 255, 255, 0.85);
            max-width: 460px;
            width: 100%;
            margin-left: auto;
            position: relative;
        }

        /* Top Brand Pill Badge */
        .brand-pill {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            background: #0f172a;
            color: #ffffff;
            padding: 6px 16px;
            border-radius: 50px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25);
        }

        .brand-pill-img {
            height: 18px;
            width: auto;
            object-fit: contain;
        }

        .brand-pill-text {
            font-family: 'Playfair Display', Georgia, serif;
            font-style: italic;
            font-size: 0.92rem;
            letter-spacing: 0.4px;
            font-weight: 500;
            color: #ffffff;
        }

        /* Typography */
        .login-title {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 1.85rem;
            font-weight: 600;
            color: #0f172a;
            letter-spacing: -0.5px;
            line-height: 1.25;
            margin-top: 14px;
            margin-bottom: 4px;
        }

        .login-subtitle {
            font-size: 0.85rem;
            color: #475569;
            font-weight: 400;
            margin-bottom: 18px;
        }

        /* Form Custom Elements */
        .form-group-custom {
            margin-bottom: 14px;
        }

        .form-label-custom {
            display: block;
            font-size: 0.82rem;
            font-weight: 500;
            color: #1e293b;
            margin-bottom: 5px;
        }

        .form-control-custom {
            width: 100%;
            background: rgba(255, 255, 255, 0.40);
            border: 1.2px solid #334155;
            border-radius: 11px;
            padding: 10px 14px;
            font-size: 0.88rem;
            color: #0f172a;
            transition: all 0.2s ease;
            outline: none;
        }

        .form-control-custom::placeholder {
            color: #64748b;
            font-size: 0.84rem;
        }

        .form-control-custom:focus {
            background: rgba(255, 255, 255, 0.88);
            border-color: #0f172a;
            box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.1);
        }

        .password-input-wrapper {
            position: relative;
        }

        .password-input-wrapper .form-control-custom {
            padding-right: 42px;
        }

        .password-toggle-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: #475569;
            cursor: pointer;
            font-size: 1.1rem;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.15s ease;
        }

        .password-toggle-btn:hover {
            color: #0f172a;
        }

        /* Custom Checkbox */
        .custom-checkbox {
            width: 15px;
            height: 15px;
            accent-color: #0f172a;
            cursor: pointer;
            border-radius: 4px;
        }

        /* Sleek Dark Login Button */
        .btn-login-dark {
            background: #111827;
            color: #ffffff;
            border: none;
            border-radius: 11px;
            padding: 11px 18px;
            font-size: 0.92rem;
            font-weight: 600;
            width: 100%;
            cursor: pointer;
            transition: all 0.25s ease;
            box-shadow: 0 4px 14px rgba(17, 24, 39, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-login-dark:hover {
            background: #1f2937;
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(17, 24, 39, 0.35);
        }

        .btn-login-dark:active {
            transform: translateY(0);
        }

        /* Responsive Styles */
        @media (max-width: 991.98px) {
            html,
            body {
                overflow-y: auto !important;
                height: auto;
                min-height: 100vh;
            }

            .login-wrapper {
                height: auto;
                min-height: 100vh;
                overflow-y: auto;
            }

            .login-content-container {
                height: auto;
                min-height: 100vh;
                padding: 1.5rem 1.25rem;
                justify-content: center;
                overflow: visible;
            }

            .glass-login-card {
                margin: 0 auto;
                max-width: 100%;
                padding: 28px 24px;
                border-radius: 24px;
            }

            .hero-quote-card {
                max-width: 100%;
                margin-bottom: 1.5rem;
            }
        }

        @media (max-width: 575.98px) {
            .glass-login-card {
                padding: 24px 18px;
                border-radius: 22px;
            }

            .login-title {
                font-size: 1.6rem;
            }
        }
    </style>
</head>

<body>
    @include('sweetalert::alert')

    <div class="login-wrapper">
        <div class="login-bg-overlay"></div>

        <div class="login-content-container">

            <!-- Top Row Branding Spacer -->
            <div class="d-flex justify-content-between align-items-center pt-1">
                <span class="text-white-50 small d-none d-md-inline" style="text-shadow: 0 1px 4px rgba(0,0,0,0.6);">
                    <i class="bi bi-shield-check me-1"></i> Portal Resmi Universitas Ibnu Sina Batam
                </span>
                <span class="text-white-50 small ms-auto" style="text-shadow: 0 1px 4px rgba(0,0,0,0.6);">
                    <i class="bi bi-calendar3 me-1"></i> {{ date('d M Y') }}
                </span>
            </div>

            <!-- Main Row (Vertically Centered in 1 Page) -->
            <div class="row align-items-center justify-content-between g-4 my-auto w-100 mx-0">

                <!-- Left Column: Quote / Motto Card -->
                <div class="col-lg-6 col-xl-5 d-none d-lg-block ps-0">
                    <div class="hero-quote-card">
                        <h2 class="hero-quote-title">Mewujudkan Profesionalisme &amp; Kinerja Staff Unggul</h2>
                        <p class="hero-quote-desc">
                            Sistem Informasi Manajemen Kinerja Terpadu untuk monitoring rencana kerja, pencatatan kegiatan harian,
                            absensi kinerja, serta evaluasi akuntabilitas seluruh staff dan tenaga kependidikan Universitas Ibnu Sina.
                        </p>
                    </div>

                    <!-- Indicator Pagination Dots -->
                    <div class="hero-pagination">
                        <span class="page-pill"></span>
                        <span class="page-dot"></span>
                        <span class="page-dot"></span>
                    </div>
                </div>

                <!-- Right Column: Clean & Elegant Glassmorphism Card -->
                <div class="col-12 col-md-9 col-lg-6 col-xl-5 mx-auto ms-lg-auto me-lg-0 pe-0">
                    <div class="glass-login-card">

                        <!-- Brand Pill Badge at the Top -->
                        <div class="brand-pill">
                            <img src="{{ asset('assets/img/logouis.png') }}" alt="Logo UIS" class="brand-pill-img">
                            <span class="brand-pill-text">SIM Kinerja</span>
                        </div>

                        <!-- Title & Subtitle -->
                        <h1 class="login-title">Welcome Back to SIM Kinerja</h1>
                        <p class="login-subtitle">Sign in your account</p>

                        <!-- Flash Alerts -->
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center py-2 px-3 mb-3 border-0 rounded-3"
                                style="background: rgba(220, 252, 231, 0.9); color: #166534;" role="alert">
                                <i class="bi bi-check-circle-fill me-2 fs-6"></i>
                                <div class="small">{{ session('success') }}</div>
                                <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center py-2 px-3 mb-3 border-0 rounded-3"
                                style="background: rgba(254, 226, 226, 0.9); color: #991b1b;" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2 fs-6"></i>
                                <div class="small">{{ session('error') }}</div>
                                <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        @if ($errors->has('login_error'))
                            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center py-2 px-3 mb-3 border-0 rounded-3"
                                style="background: rgba(254, 226, 226, 0.9); color: #991b1b;" role="alert">
                                <i class="bi bi-exclamation-octagon-fill me-2 fs-6"></i>
                                <div class="small">{{ $errors->first('login_error') }}</div>
                                <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        <!-- Login Form -->
                        <form action="{{ route('login.proses') }}" method="POST" class="needs-validation" novalidate>
                            @csrf

                            <!-- Email / NUP -->
                            <div class="form-group-custom">
                                <label for="yourUsername" class="form-label-custom">Your Email</label>
                                <input type="text" name="username"
                                    class="form-control-custom @error('username') is-invalid @enderror" id="yourUsername"
                                    value="{{ old('username') }}" placeholder="Enter your email id here" required
                                    autocomplete="username" autofocus>
                                @error('username')
                                    <div class="text-danger small mt-1" style="font-size: 0.78rem;">
                                        <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Password -->
                            <div class="form-group-custom">
                                <label for="yourPassword" class="form-label-custom">Password</label>
                                <div class="password-input-wrapper">
                                    <input type="password" name="password"
                                        class="form-control-custom @error('password') is-invalid @enderror"
                                        id="yourPassword" placeholder="Enter your password here" required
                                        autocomplete="current-password">
                                    <button class="password-toggle-btn" type="button" id="togglePassword"
                                        title="Tampilkan / Sembunyikan Password" tabindex="-1">
                                        <i class="bi bi-eye" id="togglePasswordIcon"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <div class="text-danger small mt-1" style="font-size: 0.78rem;">
                                        <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Remember Me & Forgot Password -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <input type="checkbox" name="remember" id="remember" class="custom-checkbox">
                                    <label for="remember" class="small text-secondary mb-0"
                                        style="cursor: pointer; font-size: 0.82rem;">Remember me</label>
                                </div>
                                <a href="#" id="forgotPasswordLink" class="small text-secondary text-decoration-none"
                                    style="font-size: 0.82rem;">Forget Password?</a>
                            </div>

                            <!-- Submit Button -->
                            <button class="btn-login-dark" type="submit">
                                <span>Login</span>
                            </button>
                        </form>

                        <!-- Footer Registration Link -->
                        <div class="text-center mt-3 pt-1">
                            <span class="text-secondary small" style="font-size: 0.84rem;">Don’t have an account? </span>
                            <a href="{{ route('register') }}" class="small fw-semibold text-decoration-none"
                                style="color: #2563eb; font-size: 0.84rem;">Register</a>
                        </div>

                    </div>
                </div>

            </div>

            <!-- Bottom University Copyright -->
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pb-1 text-white-50 small"
                style="text-shadow: 0 1px 4px rgba(0,0,0,0.6); font-size: 0.78rem;">
                <span>&copy; {{ date('Y') }} Universitas Ibnu Sina &bull; SIM Kinerja</span>
                <span>Batam, Kepulauan Riau</span>
            </div>

        </div>
    </div>

    <!-- Vendor JS Files -->
    <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Password Visibility Toggle
            const toggleBtn = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('yourPassword');
            const icon = document.getElementById('togglePasswordIcon');

            if (toggleBtn && passwordInput && icon) {
                toggleBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const isPassword = passwordInput.getAttribute('type') === 'password';
                    passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                    icon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
                });
            }

            // Forgot Password Modal / Info
            document.getElementById('forgotPasswordLink')?.addEventListener('click', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Lupa Password?',
                    html: '<p class="text-muted small mb-3">Untuk reset password akun SIM KINERJA, silakan hubungi tim Administrator BAAK / LPTI Universitas Ibnu Sina.</p>' +
                        '<div class="p-3 bg-light rounded-3 text-start small border">' +
                        '<div class="mb-2"><strong><i class="bi bi-envelope-fill text-primary me-2"></i>Email Layanan:</strong><br><span class="text-dark">lpti@uis.ac.id</span></div>' +
                        '<div><strong><i class="bi bi-building-fill text-success me-2"></i>Unit Pengelola:</strong><br><span class="text-dark">LPTI & BAAK Universitas Ibnu Sina</span></div>' +
                        '</div>',
                    icon: 'info',
                    confirmButtonText: 'Tutup',
                    confirmButtonColor: '#111827',
                    customClass: {
                        popup: 'rounded-4'
                    }
                });
            });
        });
    </script>
</body>

</html>
