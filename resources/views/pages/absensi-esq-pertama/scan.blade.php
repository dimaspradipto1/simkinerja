@extends('layouts.dashboard.template')

@section('content')
<style>
    body, html {
        margin: 0;
        padding: 0;
    }
    .scan-page-container {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        padding: 1.5rem;
        background: #f8f9fa;
        justify-content: center;
        align-items: center;
        width: 100%;
        margin: 0 auto;
        overflow-x: hidden;
    }
    .scan-header {
        text-align: center;
        margin-bottom: 1.5rem;
        padding-top: 0;
    }
    .scan-title {
        font-size: 1.5rem;
        font-weight: 900;
        color: #1f2d3d;
        margin-bottom: 0.5rem;
        line-height: 1.2;
    }
    .scan-subtitle {
        font-size: 0.85rem;
        color: #666;
        font-weight: 400;
    }
    .time-toggle-group {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.5rem;
        justify-content: center;
        width: 100%;
        max-width: 500px;
    }
    .time-toggle-btn {
        flex: 1;
        max-width: 180px;
        padding: 0.7rem 1.25rem;
        border: 2px solid #ddd;
        background: white;
        border-radius: 12px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        font-size: 0.9rem;
        color: #1f2d3d;
    }
    .time-toggle-btn.active {
        background: #198754;
        border-color: #198754;
        color: white;
        box-shadow: 0 4px 12px rgba(25, 135, 84, 0.3);
    }
    .time-toggle-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(25, 135, 84, 0.2);
    }
    .scanner-card {
        background: white;
        border-radius: 1.5rem;
        box-shadow: 0 8px 24px rgba(0,0,0,0.08);
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 1.5rem;
        width: 100%;
        max-width: 400px;
    }
    .scan-inner {
        width: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 1.5rem;
    }
    #scanner-wrapper {
        width: 100%;
        aspect-ratio: 1;
        background-color: #000;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 4px 16px rgba(0,0,0,0.2);
        flex-shrink: 0;
    }
    #reader {
        width: 100% !important;
        height: 100% !important;
    }
    .scan-progress {
        width: 100%;
    }
    .progress {
        height: 4px;
        background: #e9ecef !important;
        border-radius: 2px;
    }
    .progress-bar {
        background: linear-gradient(90deg, #198754 0%, #20c997 100%) !important;
    }
    .scan-status-text {
        font-size: 0.85rem;
        color: #ff9800;
        font-weight: 600;
        margin-top: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }
    .button-group {
        width: 100%;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }
    .btn-scan {
        padding: 0.85rem 1.5rem;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.95rem;
        width: 100%;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }
    #btn-start-scan {
        background: #198754;
        color: white;
        box-shadow: 0 4px 12px rgba(25, 135, 84, 0.3);
    }
    #btn-start-scan:hover {
        background: #157347;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(25, 135, 84, 0.4);
    }
    #btn-stop-scan {
        background: #dc3545;
        color: white;
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
    }
    #btn-stop-scan:hover {
        background: #c82333;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(220, 53, 69, 0.4);
    }
    @media (max-width: 768px) {
        .scan-page-container {
            padding: 1rem;
            min-height: 100vh;
        }
        .scan-header {
            margin-bottom: 1.25rem;
            padding-top: 0;
        }
        .scan-title {
            font-size: 1.25rem;
            margin-bottom: 0.25rem;
        }
        .time-toggle-group {
            gap: 0.75rem;
            margin-bottom: 1.25rem;
            width: 100%;
            max-width: 100%;
        }
        .time-toggle-btn {
            max-width: 100%;
            padding: 0.6rem 1rem;
            font-size: 0.85rem;
            flex: 1;
        }
        .scanner-card {
            padding: 1.25rem;
            gap: 1.25rem;
            max-width: 100%;
            border-radius: 1.25rem;
        }
        .scan-inner {
            gap: 1.25rem;
        }
        #scanner-wrapper {
            width: 100%;
            max-width: 100%;
        }
    }
</style>

<div class="scan-page-container">
    <div class="scan-header">
        <div class="scan-title">QR Absensi Hari 1</div>
        <div class="scan-subtitle">(Waktu Datang)</div>
    </div>

    <div class="time-toggle-group">
        <button class="time-toggle-btn active" data-time="datang"><i class="bi bi-sun"></i> Waktu Datang</button>
        <button class="time-toggle-btn" data-time="pulang"><i class="bi bi-moon"></i> Waktu Pulang</button>
    </div>

    <div class="scanner-card">
        <div class="scan-inner">
            <!-- Camera Scanner Container -->
            <div id="scanner-wrapper" style="display: none;"></div>
            <div id="reader" style="display: none;"></div>

            <!-- Scan Progress Indicator -->
            <div id="scan-progress" class="scan-progress" style="display: none;">
                <div class="progress">
                    <div class="progress-bar" role="progressbar" style="width: 33%;"></div>
                </div>
                <div class="scan-status-text">
                    <i class="bi bi-lightning-fill"></i>Sedang Aktif — berganti dalam 52 detik
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="button-group">
                <button type="button" id="btn-start-scan" class="btn-scan">
                    <i class="bi bi-camera-fill"></i> Mulai Scan
                </button>

                <button type="button" id="btn-stop-scan" class="btn-scan" style="display: none;">
                    <i class="bi bi-stop-circle-fill"></i> Berhenti
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Load HTML5-QRCode library via CDN -->
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    $(document).ready(function() {
        let currentTimeMode = 'datang';
        
        // Time toggle buttons
        $('.time-toggle-btn').on('click', function() {
            $('.time-toggle-btn').removeClass('active');
            $(this).addClass('active');
            currentTimeMode = $(this).data('time');
            
            // Update title
            const titleMap = {
                'datang': '(Waktu Datang)',
                'pulang': '(Waktu Pulang)'
            };
            $('.scan-subtitle').text(titleMap[currentTimeMode]);
        });
        
        // Initialize HTML5 QrCode - use body element for camera
        const html5QrCode = new Html5Qrcode("scanner-wrapper");
        const config = {
            fps: 15,
            qrbox: { width: 280, height: 280 }
        };

        const qrCodeSuccessCallback = (decodedText, decodedResult) => {
            // Stop scanning and redirect
            html5QrCode.stop().then((ignore) => {
                window.location.href = decodedText;
            }).catch((err) => {
                console.warn("Failed to stop scanner", err);
                window.location.href = decodedText; // Fallback redirect anyway
            });
        };

        // Start button click handler
        $('#btn-start-scan').on('click', function() {
            // Camera access requires a secure context (HTTPS or localhost).
            if (!window.isSecureContext) {
                Swal.fire({
                    title: 'Koneksi Tidak Aman!',
                    html: 'Akses kamera browser hanya diizinkan melalui koneksi <strong>HTTPS</strong> (atau <code>localhost</code>).<br><br>Anda mengakses melalui:<br><code>' + window.location.protocol + '//' + window.location.host + '</code><br><br>Silakan hubungi admin untuk mengaktifkan HTTPS pada server.',
                    icon: 'error',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }

            $('#scanner-wrapper').show();
            $('#scan-progress').show();
            $('#btn-start-scan').hide();
            $('#btn-stop-scan').show();

            // Request camera permissions and start scanning
            html5QrCode.start(
                { facingMode: "environment" },
                config,
                qrCodeSuccessCallback
            ).catch((err) => {
                console.error("Camera access failed", err);

                let errorMsg = 'Pastikan Anda telah memberikan izin akses kamera ke browser Anda.';
                const errName = err && err.name;
                if (errName === 'NotAllowedError' || errName === 'PermissionDeniedError') {
                    errorMsg = 'Izin akses kamera ditolak. Aktifkan izin kamera untuk situs ini melalui pengaturan browser Anda, lalu coba lagi.';
                } else if (errName === 'NotFoundError' || errName === 'DevicesNotFoundError') {
                    errorMsg = 'Kamera tidak ditemukan pada perangkat ini.';
                } else if (errName === 'NotReadableError' || errName === 'TrackStartError') {
                    errorMsg = 'Kamera sedang digunakan oleh aplikasi lain. Tutup aplikasi lain yang menggunakan kamera lalu coba lagi.';
                }

                Swal.fire({
                    title: 'Gagal Mengakses Kamera!',
                    text: errorMsg,
                    icon: 'error',
                    confirmButtonColor: '#3085d6'
                });
                $('#scanner-wrapper').hide();
                $('#scan-progress').hide();
                $('#btn-start-scan').show();
                $('#btn-stop-scan').hide();
            });
        });

        // Stop button click handler
        $('#btn-stop-scan').on('click', function() {
            html5QrCode.stop().then((ignore) => {
                $('#scanner-wrapper').hide();
                $('#scan-progress').hide();
                $('#btn-start-scan').show();
                $('#btn-stop-scan').hide();
            }).catch((err) => {
                console.error("Unable to stop scanning", err);
            });
        });

        // Auto-start if parameter is present
        try {
            const params = new URLSearchParams(window.location.search);
            if (params.get('start') === '1') {
                $('#btn-start-scan').trigger('click');
            }
        } catch (e) {
            // URLSearchParams may not be available in very old browsers; ignore
        }
    });
</script>
@endpush
