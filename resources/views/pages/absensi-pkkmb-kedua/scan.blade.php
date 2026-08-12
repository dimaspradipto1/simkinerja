@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
    <h1>Scan Absensi Kehadiran</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('absensi-pkkmb-kedua.index') }}">Absensi Kedua</a></li>
            <li class="breadcrumb-item active">Scan</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    <div class="row">
        <div class="col-lg-6 mx-auto">
            <div class="card shadow-sm border-0" style="border-radius: 1rem;">
                <div class="card-body p-4">
                    <!-- Card Header with Back Button -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold m-0 text-primary" style="font-size: 1.25rem;">Portal Scan Kehadiran</h5>
                        <a href="{{ route('absensi-pkkmb-kedua.index') }}" class="btn btn-secondary btn-sm d-inline-flex align-items-center gap-1 py-1 px-3 rounded shadow-sm">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>

                    <!-- Center Subtitle -->
                    <div class="text-center my-4">
                        <h6 class="fw-bold text-dark m-0" style="font-size: 1.1rem; color: #1e3a8a !important;">Silakan Scan Terhadap QR Admin</h6>
                    </div>

                    <!-- Green Solid Divider Bar -->
                    <div class="my-4" style="height: 6px; background-color: #198754; border-radius: 1rem;"></div>

                    <!-- Camera Scanner Container with Custom CSS Overlay Spotlight -->
                    <div id="scanner-wrapper" class="position-relative overflow-hidden border border-2 border-success rounded-3 mx-auto shadow-sm mb-4" style="max-width: 500px; display: none;">
                        <div id="reader" style="width: 100%; aspect-ratio: 4/3; background-color: #000;"></div>
                        <!-- Spotlight Mask Overlay -->
                        <div class="scanner-overlay position-absolute top-0 start-0 end-0 bottom-0 d-flex align-items-center justify-content-center" style="background: rgba(0,0,0,0.4); pointer-events: none;">
                            <div class="position-relative" style="width: 200px; height: 200px; box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.4);">
                                <!-- White Frame Corners -->
                                <div class="position-absolute border-top border-start border-4 border-white" style="width: 30px; height: 30px; top: 0; left: 0;"></div>
                                <div class="position-absolute border-top border-end border-4 border-white" style="width: 30px; height: 30px; top: 0; right: 0;"></div>
                                <div class="position-absolute border-bottom border-start border-4 border-white" style="width: 30px; height: 30px; bottom: 0; left: 0;"></div>
                                <div class="position-absolute border-bottom border-end border-4 border-white" style="width: 30px; height: 30px; bottom: 0; right: 0;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-4 d-flex flex-column gap-3">
                        <button type="button" id="btn-start-scan" class="btn btn-primary btn-lg w-100 py-3 rounded-3 font-semibold shadow-sm d-flex align-items-center justify-content-center gap-2 fs-6">
                            <i class="bi bi-camera-fill"></i> Mulai Scan Sekarang
                        </button>

                        <button type="button" id="btn-stop-scan" class="btn btn-danger btn-lg w-100 py-3 rounded-3 font-semibold shadow-sm d-flex align-items-center justify-content-center gap-2 fs-6" style="display: none;">
                            <i class="bi bi-stop-circle-fill"></i> Berhenti
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<!-- Load HTML5-QRCode library via CDN -->
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    $(document).ready(function() {
        // Initialize HTML5 QrCode
        const html5QrCode = new Html5Qrcode("reader");
        const config = { 
            fps: 15, 
            qrbox: { width: 200, height: 200 } 
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
            $('#scanner-wrapper').show();
            $('#btn-start-scan').hide();
            $('#btn-stop-scan').show();

            // Request camera permissions and start scanning
            html5QrCode.start(
                { facingMode: "environment" }, 
                config, 
                qrCodeSuccessCallback
            ).catch((err) => {
                console.error("Camera access failed", err);
                Swal.fire({
                    title: 'Gagal Mengakses Kamera!',
                    text: 'Pastikan Anda telah memberikan izin akses kamera ke browser Anda dan menggunakan HTTPS.',
                    icon: 'error',
                    confirmButtonColor: '#3085d6'
                });
                $('#scanner-wrapper').hide();
                $('#btn-start-scan').show();
                $('#btn-stop-scan').hide();
            });
        });

        // Stop button click handler
        $('#btn-stop-scan').on('click', function() {
            html5QrCode.stop().then((ignore) => {
                $('#scanner-wrapper').hide();
                $('#btn-start-scan').show();
                $('#btn-stop-scan').hide();
            }).catch((err) => {
                console.error("Unable to stop scanning", err);
            });
        });
    });
</script>
@endpush
