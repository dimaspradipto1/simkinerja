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
            height: 100%;
            width: 100%;
            font-family: 'Plus Jakarta Sans', sans-serif;
            overflow-x: hidden;
            background-color: #0f172a;
        }

        /* Fullscreen Scenic Background with Campus Image */
        .login-wrapper {
            min-height: 100vh;
            width: 100%;
            position: relative;
            background-image: url("{{ asset('assets/img/gedung.png') }}");
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            display: flex;
            align-items: center;
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
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 3rem 4rem;
        }

        /* Left Side Floating Quote / Vision Card */
        .hero-quote-card {
            background: rgba(30, 41, 59, 0.58);
            backdrop-filter: blur(18px) saturate(180%);
            -webkit-backdrop-filter: blur(18px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 22px;
            padding: 24px 28px;
            max-width: 440px;
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
            font-size: 1.45rem;
            font-weight: 600;
            line-height: 1.35;
            color: #ffffff;
            margin-bottom: 10px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
        }

        .hero-quote-desc {
            font-size: 0.88rem;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.88);
            margin-bottom: 0;
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.4);
        }

        /* Pagination Dots Under Quote */
        .hero-pagination {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 1.25rem;
        }

        .page-pill {
            width: 38px;
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
            background: rgba(255, 255, 255, 0.50);
            backdrop-filter: blur(28px) saturate(190%);
            -webkit-backdrop-filter: blur(28px) saturate(190%);
            border: 1.5px solid rgba(255, 255, 255, 0.75);
            border-radius: 34px;
            padding: 42px 46px;
            box-shadow: 0 30px 65px rgba(0, 0, 0, 0.22), inset 0 1px 0 rgba(255, 255, 255, 0.85);
            max-width: 485px;
            width: 100%;
            margin-left: auto;
            position: relative;
        }

        /* Top Brand Pill Badge */
        .brand-pill {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: #0f172a;
            color: #ffffff;
            padding: 7px 18px;
            border-radius: 50px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25);
        }

        .brand-pill-img {
            height: 20px;
            width: auto;
            object-fit: contain;
        }

        .brand-pill-text {
            font-family: 'Playfair Display', Georgia, serif;
            font-style: italic;
            font-size: 0.95rem;
            letter-spacing: 0.4px;
            font-weight: 500;
            color: #ffffff;
        }

        /* Typography */
        .login-title {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 2.15rem;
            font-weight: 600;
            color: #0f172a;
            letter-spacing: -0.5px;
            line-height: 1.25;
            margin-top: 20px;
            margin-bottom: 6px;
        }

        .login-subtitle {
            font-size: 0.9rem;
            color: #475569;
            font-weight: 400;
            margin-bottom: 24px;
        }

        /* Form Custom Elements */
        .form-group-custom {
            margin-bottom: 18px;
        }

        .form-label-custom {
            display: block;
            font-size: 0.83rem;
            font-weight: 500;
            color: #1e293b;
            margin-bottom: 6px;
        }

        .form-control-custom {
            width: 100%;
            background: rgba(255, 255, 255, 0.38);
            border: 1.2px solid #334155;
            border-radius: 12px;
            padding: 11px 16px;
            font-size: 0.9rem;
            color: #0f172a;
            transition: all 0.2s ease;
            outline: none;
        }

        .form-control-custom::placeholder {
            color: #64748b;
            font-size: 0.86rem;
        }

        .form-control-custom:focus {
            background: rgba(255, 255, 255, 0.85);
            border-color: #0f172a;
            box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.1);
        }

        .password-input-wrapper {
            position: relative;
        }

        .password-input-wrapper .form-control-custom {
            padding-right: 44px;
        }

        .password-toggle-btn {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: #475569;
            cursor: pointer;
            font-size: 1.15rem;
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
            width: 16px;
            height: 16px;
            accent-color: #0f172a;
            cursor: pointer;
            border-radius: 4px;
        }

        /* Sleek Dark Login Button */
        .btn-login-dark {
            background: #111827;
            color: #ffffff;
            border: none;
            border-radius: 12px;
            padding: 12px 20px;
            font-size: 0.95rem;
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

        /* SSO Options */
        .sso-actions {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            margin-top: 22px;
            flex-wrap: wrap;
        }

        .btn-sso {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: transparent;
            border: none;
            color: #1e293b;
            font-size: 0.85rem;
            font-weight: 500;
            padding: 6px 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            border-radius: 8px;
        }

        .btn-sso:hover {
            color: #0f172a;
            background: rgba(255, 255, 255, 0.45);
        }

        /* Responsive Styles */
        @media (max-width: 991.98px) {
            .login-content-container {
                padding: 2rem 1.5rem;
                justify-content: center;
            }

            .glass-login-card {
                margin: 0 auto;
                max-width: 100%;
                padding: 34px 28px;
                border-radius: 28px;
            }

            .hero-quote-card {
                max-width: 100%;
                margin-bottom: 2rem;
            }
        }

        @media (max-width: 575.98px) {
            .glass-login-card {
                padding: 28px 20px;
                border-radius: 24px;
            }

            .login-title {
                font-size: 1.75rem;
            }

            .sso-actions {
                gap: 10px;
                flex-direction: column;
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
            <div class="d-flex justify-content-between align-items-center mb-auto pt-2">
                <span class="text-white-50 small d-none d-md-inline" style="text-shadow: 0 1px 4px rgba(0,0,0,0.6);">
                    <i class="bi bi-shield-check me-1"></i> Portal Resmi Universitas Ibnu Sina Batam
                </span>
                <span class="text-white-50 small ms-auto" style="text-shadow: 0 1px 4px rgba(0,0,0,0.6);">
                    <i class="bi bi-calendar3 me-1"></i> {{ date('d M Y') }}
                </span>
            </div>

            <!-- Main Row -->
            <div class="row align-items-center justify-content-between g-4 my-auto w-100 mx-0">

                <!-- Left Column: Quote / Motto Card -->
                <div class="col-lg-6 col-xl-5 d-none d-lg-block ps-0">
                    <div class="hero-quote-card">
                        <h2 class="hero-quote-title">Because Stays Should Feel Like Destinations</h2>
                        <p class="hero-quote-desc">
                            Sistem Informasi Manajemen Kinerja Terpadu untuk monitoring rencana kerja, tridharma perguruan
                            tinggi, absensi kepanitiaan, serta evaluasi kinerja dosen dan tenaga kependidikan.
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
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="d-flex align-items-center gap-2">
                                    <input type="checkbox" name="remember" id="remember" class="custom-checkbox">
                                    <label for="remember" class="small text-secondary mb-0"
                                        style="cursor: pointer; font-size: 0.83rem;">Remember me</label>
                                </div>
                                <a href="#" id="forgotPasswordLink" class="small text-secondary text-decoration-none"
                                    style="font-size: 0.83rem;">Forget Password?</a>
                            </div>

                            <!-- Submit Button -->
                            <button class="btn-login-dark" type="submit">
                                <span>Login</span>
                            </button>
                        </form>

                        <!-- Single Sign-On / Alternate Logins -->
                        <div class="sso-actions">
                            <button type="button" class="btn-sso" id="googleSsoBtn">
                                <svg width="17" height="17" viewBox="0 0 24 24">
                                    <path fill="#4285F4"
                                        d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                                    <path fill="#34A853"
                                        d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                                    <path fill="#FBBC05"
                                        d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z" />
                                    <path fill="#EA4335"
                                        d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z" />
                                </svg>
                                <span>Continue with Google</span>
                            </button>

                            <button type="button" class="btn-sso" id="appleSsoBtn">
                                <i class="bi bi-apple fs-5"></i>
                                <span>Continue with Apple</span>
                            </button>
                        </div>

                        <!-- Footer Registration Link -->
                        <div class="text-center mt-4 pt-1">
                            <span class="text-secondary small" style="font-size: 0.85rem;">Don’t have an account? </span>
                            <a href="{{ route('register') }}" class="small fw-semibold text-decoration-none"
                                style="color: #2563eb; font-size: 0.85rem;">Register</a>
                        </div>

                    </div>
                </div>

            </div>

            <!-- Bottom University Copyright -->
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-2 text-white-50 small"
                style="text-shadow: 0 1px 4px rgba(0,0,0,0.6);">
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

            // Google SSO Handler
            document.getElementById('googleSsoBtn')?.addEventListener('click', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Single Sign-On Google',
                    text: 'Fitur SSO Google sedang dalam proses integrasi akun resmi Google Workspace @uis.ac.id. Silakan masuk menggunakan Email/NUP dan Password akun Anda.',
                    icon: 'info',
                    confirmButtonText: 'Mengerti',
                    confirmButtonColor: '#111827',
                    customClass: {
                        popup: 'rounded-4'
                    }
                });
            });

            // Apple SSO Handler
            document.getElementById('appleSsoBtn')?.addEventListener('click', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Sign in with Apple',
                    text: 'Fitur Apple ID sedang dalam tahap pengembangan. Silakan masuk menggunakan Email/NUP dan Password akun Anda.',
                    icon: 'info',
                    confirmButtonText: 'Mengerti',
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
