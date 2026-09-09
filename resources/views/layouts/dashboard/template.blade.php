<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>SIM KINERJA - Dashboard</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="{{ asset('assets/img/logouis.png') }}" rel="icon">
    <link href="{{ asset('assets/img/logouis.png') }}" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"
        rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/boxicons/css/boxicons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/quill/quill.snow.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/quill/quill.bubble.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/remixicon/remixicon.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/simple-datatables/style.css') }}" rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <style>
        /* Table Symmetrical Alignment & Responsive Container */
        .card-body {
            overflow-x: auto;
        }

        .table-responsive,
        .dataTables_wrapper,
        .dataTables_scroll,
        .dataTables_scrollHead,
        .dataTables_scrollBody {
            display: block;
            width: 100% !important;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch !important;
            scrollbar-width: thin;
        }

        .table-responsive table {
            width: 100% !important;
            margin-bottom: 0;
        }

        /* Pending Extension Alert Styling */
        .btn-approve-ext {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
            color: #ffffff !important;
            border: none !important;
            border-radius: 8px !important;
            padding: 8px 18px !important;
            font-size: 0.83rem !important;
            white-space: nowrap !important;
            box-shadow: 0 2px 6px rgba(217, 119, 6, 0.22) !important;
            transition: all 0.2s ease-in-out !important;
            width: auto;
        }
        .btn-approve-ext:hover {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%) !important;
            color: #ffffff !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 4px 12px rgba(217, 119, 6, 0.35) !important;
        }
        @media (max-width: 991.98px) {
            .btn-approve-ext {
                width: 100% !important;
            }
        }

        .dataTables_scrollHeadInner,
        .dataTables_scrollHeadInner > table,
        .dataTables_scrollBody > table {
            width: 100% !important;
            min-width: 100%;
        }

        .dataTables_wrapper {
            max-width: 100% !important;
            clear: both;
        }

        .dataTables_wrapper .row {
            max-width: 100%;
            margin-left: 0;
            margin-right: 0;
        }

        /* Table cell styling */
        .table th, .table td {
            vertical-align: middle !important;
            box-sizing: border-box;
        }

        /* Task Dropdown/Collapse Toggle Animation & Button Styling */
        .transition-transform {
            transition: transform 0.2s ease-in-out;
        }
        .btn-task-toggle[aria-expanded="true"] .toggle-icon {
            transform: rotate(180deg);
        }
        .btn-task-toggle[aria-expanded="true"] {
            background-color: #15432d !important;
            color: #ffffff !important;
            border-color: #15432d !important;
        }
        .btn-task-toggle[aria-expanded="true"]:hover {
            background-color: #0f3121 !important;
            color: #ffffff !important;
        }

        /* Mobile specific horizontal scroll polish */
        @media (max-width: 991.98px) {
            .table-responsive table:not(#kepanitiaan-table):not(#kegiatan-table):not(#rencanakerja-table):not(#rencana-kerja-table),
            table.dataTable:not(#kepanitiaan-table):not(#kegiatan-table):not(#rencanakerja-table):not(#rencana-kerja-table) {
                min-width: 750px !important;
            }
            #kepanitiaan-table,
            #kegiatan-table,
            #rencanakerja-table,
            #rencana-kerja-table {
                min-width: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }
            .dataTables_scrollBody {
                overflow-x: auto !important;
                -webkit-overflow-scrolling: touch !important;
            }
        }

        /* Keep action buttons neatly aligned */
        .table td .btn-group,
        .table td .d-inline-flex {
            flex-wrap: nowrap;
        }

        .absensi-mobile-banner {
            width: 100%;
            max-width: none;
        }

        /* Mobile Spacing & Layout Polish */
        @media (max-width: 767.98px) {
            #main.main {
                padding: 12px 10px !important;
            }
            .pagetitle {
                padding: 0 4px !important;
                margin-bottom: 12px !important;
            }
            .section {
                padding: 0 !important;
            }
            .card {
                border-radius: 12px !important;
                margin-left: 0 !important;
                margin-right: 0 !important;
                margin-bottom: 14px !important;
                box-shadow: 0 2px 8px rgba(0,0,0,0.06) !important;
                width: 100% !important;
                max-width: 100% !important;
                overflow: hidden !important;
            }
            .card-body {
                padding: 12px 10px !important;
            }
            .card-header-green {
                border-radius: 12px 12px 0 0 !important;
                padding: 12px 14px !important;
            }
            .absensi-mobile-banner {
                width: 100% !important;
                max-width: 100% !important;
                margin-left: 0 !important;
                margin-right: 0 !important;
                border-radius: 12px !important;
            }
            .absensi-mobile-banner .card-body {
                padding: 14px 12px !important;
            }
            .absensi-mobile-banner .btn {
                width: 100% !important;
                justify-content: center !important;
            }
        }

        /* Modal QR Camera Scanner Styling */
        #modal-scanner-wrapper {
            position: relative;
            width: 100%;
            max-width: 320px;
            aspect-ratio: 1 / 1;
            margin: 0 auto;
            border-radius: 1rem;
            overflow: hidden;
            background: #000;
        }
        #modal-reader {
            width: 100% !important;
            height: 100% !important;
            border: none !important;
        }
        #modal-reader video {
            object-fit: cover !important;
            width: 100% !important;
            height: 100% !important;
            border-radius: 1rem !important;
        }
        #modal-reader img[alt="Info icon"] { 
            display: none !important; 
        }
        .session-choice-item {
            transition: all 0.18s ease-in-out;
            border: 1px solid #e2e8f0;
        }
        .session-choice-item:hover {
            border-color: #0d6efd !important;
            background-color: #f0f7ff !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.12) !important;
        }
    </style>
</head>

<body>

    @include('layouts.dashboard.header')
    @include('layouts.dashboard.sidebar')

    <main id="main" class="main">
        @include('sweetalert::alert')
        @yield('content')
    </main>

    @include('layouts.dashboard.footer')


    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>

    <!-- Vendor JS Files -->
    <script src="{{ asset('assets/vendor/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/chart.js/chart.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/echarts/echarts.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/quill/quill.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/simple-datatables/simple-datatables.js') }}"></script>
    <script src="{{ asset('assets/vendor/tinymce/tinymce.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/php-email-form/validate.js') }}"></script>

    <!-- Template Main JS File -->
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.js"></script>
    <script src="https://unpkg.com/html5-qrcode"></script>

    @php
        $authUserForModal = Auth::user();
        $pimpinanList = $authUserForModal ? $authUserForModal->getDirectSupervisors() : collect();
        $firstPimpinan = $pimpinanList->first();
    @endphp

    <!-- Modal Universal Permohonan Perpanjangan Waktu (Tugas Gagal / Lewat Estimasi) -->
    <div class="modal fade" id="modalUniversalExtension" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="formUniversalExtension" method="POST" action="">
                @csrf
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title fs-6 fw-bold"><i class="bi bi-exclamation-octagon-fill me-2"></i>Permohonan Perpanjangan Waktu (Tugas Gagal)</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert alert-danger bg-danger-subtle border-danger-subtle text-danger small mb-3">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Waktu pengerjaan tugas telah melewati estimasi yang ditetapkan sehingga tugas dianggap <strong>Gagal</strong>. Untuk dapat melanjutkan dan menyelesaikan tugas, Anda harus mengajukan permohonan perpanjangan waktu ke pimpinan di atas Anda.
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Uraian Tugas:</label>
                            <div id="extensionTaskUraian" class="p-2 bg-light rounded border text-dark fw-semibold small"></div>
                        </div>
                        <div class="mb-3">
                            <label for="ext_pimpinan" class="form-label fw-bold small text-dark"><i class="bi bi-person-badge-fill text-primary me-1"></i>Pimpinan Tujuan (Atasan Penanggung Jawab) <span class="text-danger">*</span></label>
                            <select name="pimpinan_id" id="ext_pimpinan" class="form-select form-select-sm" required>
                                <option value="" selected disabled>-- Pilih Pimpinan Tujuan --</option>
                                @if($pimpinanList->isNotEmpty())
                                    @foreach($pimpinanList as $pimpinan)
                                        <option value="{{ $pimpinan->id }}">
                                            {{ $pimpinan->name }} — {{ $pimpinan->jabatan ?: $pimpinan->roles }} (Unit: {{ $pimpinan->unit ?: 'Universitas' }})
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="form-text small text-muted">Permohonan perpanjangan waktu akan ditujukan kepada pimpinan yang dipilih di atas.</div>
                        </div>
                        <div class="mb-3">
                            <label for="ext_reason" class="form-label fw-bold small text-dark">Alasan Kendala / Mengapa Melewati Batas Waktu <span class="text-danger">*</span></label>
                            <textarea name="estimasi_unlock_reason" id="ext_reason" class="form-control" rows="3" required placeholder="Jelaskan alasan kendala teknis / koordinasi / kendala lapangan yang dialami..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger btn-sm px-3 fw-bold"><i class="bi bi-send-fill me-1"></i> Ajukan ke Pimpinan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        window.openExtensionModal = function(el) {
            const btn = $(el);
            const uraian = btn.data('uraian');
            const route = btn.data('route');
            $('#extensionTaskUraian').text(uraian || '-');
            $('#formUniversalExtension').attr('action', route);
            $('#ext_reason').val('');
            
            const modalEl = document.getElementById('modalUniversalExtension');
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();

            setTimeout(function() {
                if ($.fn.select2) {
                    $('#ext_pimpinan').select2({
                        theme: 'bootstrap-5',
                        dropdownParent: $('#modalUniversalExtension'),
                        placeholder: '-- Pilih Pimpinan Tujuan --',
                        allowClear: true,
                        width: '100%'
                    });
                }
                $('#ext_pimpinan').val('').trigger('change');
            }, 150);
        };

        window.openApproveExtensionModal = function(el) {
            const btn = $(el);
            const uraian = btn.data('uraian');
            const pegawai = btn.data('pegawai');
            let reason = btn.data('reason') || '-';
            const tglSelesai = btn.data('tgl-selesai');
            const jamSelesai = btn.data('jam-selesai') || '23:59';
            const route = btn.data('route');

            $('#approveTaskPegawai').text(pegawai || '-');
            $('#approveTaskUraian').text(uraian || '-');

            let tujuanHtml = '';
            let cleanReason = reason;
            const match = reason.match(/^\[Tujuan:\s*([^\]]+)\]\s*(.*)$/s);
            if (match) {
                const pimpinanName = match[1];
                cleanReason = match[2];
                tujuanHtml = `
                    <div class="mb-2 p-2 bg-primary-subtle text-primary border border-primary-subtle rounded d-flex align-items-center gap-2">
                        <i class="bi bi-person-badge-fill fs-5"></i>
                        <div>
                            <span class="d-block text-muted" style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Pimpinan Tujuan</span>
                            <strong class="text-dark" style="font-size: 0.85rem;">${pimpinanName}</strong>
                        </div>
                    </div>
                `;
            }

            const reasonCardHtml = `
                ${tujuanHtml}
                <div class="p-2.5 p-2 bg-danger-subtle text-danger rounded border border-danger-subtle d-flex align-items-start gap-2">
                    <i class="bi bi-chat-square-quote-fill fs-6 mt-1 text-danger"></i>
                    <div>
                        <span class="d-block fw-bold text-danger mb-1" style="font-size: 0.75rem; text-transform: uppercase;">Alasan / Kendala Pegawai:</span>
                        <div class="text-dark fw-medium small" style="font-size: 0.85rem; line-height: 1.4;">${cleanReason || '-'}</div>
                    </div>
                </div>
            `;

            $('#approveTaskReasonContainer').html(reasonCardHtml);
            $('#appr_tgl').val(tglSelesai || new Date().toISOString().split('T')[0]);
            $('#appr_jam').val(jamSelesai);
            $('#appr_catatan').val('');
            $('#formApproveExtension').attr('action', route);

            const modalEl = document.getElementById('modalApproveExtension');
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        };

        // Global Barcode Absensi Modal Handler
        let activeBarcodeSession = 'datang';
        let currentBarcodeSlug = '';
        let currentBarcodeUrl = '';
        let barcodeCountdown = 60;
        let barcodeTimer = null;

        function refreshBarcodeQr() {
            if (!currentBarcodeSlug) return;
            const timestamp = Math.floor(Date.now() / 60000);
            const scanUrl = "{{ url('') }}/" + currentBarcodeSlug + "/scan-proses?session=" + activeBarcodeSession + "&t=" + timestamp;
            const qrApi = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' + encodeURIComponent(scanUrl);
            $('#barcode-qr-img').attr('src', qrApi);
        }

        function startBarcodeTimer() {
            clearInterval(barcodeTimer);
            barcodeCountdown = 60;
            $('#barcode-countdown').text(barcodeCountdown);
            $('#barcode-qr-progress').css('width', '100%');

            barcodeTimer = setInterval(function() {
                barcodeCountdown--;
                $('#barcode-countdown').text(barcodeCountdown);
                const percent = (barcodeCountdown / 60) * 100;
                $('#barcode-qr-progress').css('width', percent + '%');

                if (barcodeCountdown <= 0) {
                    barcodeCountdown = 60;
                    refreshBarcodeQr();
                }
            }, 1000);
        }

        window.openBarcodeModal = function(el) {
            const btn = $(el);
            currentBarcodeSlug = btn.data('slug') || '';
            const sessionTitle = btn.data('title') || 'Barcode Absensi';
            currentBarcodeUrl = btn.data('url') || ("{{ url('') }}/" + currentBarcodeSlug);

            $('#modalBarcodeAbsensiLabel').text(sessionTitle);
            $('#btn-visit-absensi').attr('href', currentBarcodeUrl);

            activeBarcodeSession = 'datang';
            $('#btn-barcode-datang').removeClass('btn-outline-success').addClass('btn-success');
            $('#btn-barcode-pulang').removeClass('btn-primary').addClass('btn-outline-primary');
            $('#barcode-session-indicator').text('Sesi: Waktu Datang');

            refreshBarcodeQr();
            startBarcodeTimer();

            const modalEl = document.getElementById('modalBarcodeAbsensi');
            if (modalEl) {
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            }
        };

        $(document).on('click', '.btn-barcode-absensi', function(e) {
            e.preventDefault();
            window.openBarcodeModal(this);
        });

        $(document).on('click', '#btn-barcode-datang', function() {
            activeBarcodeSession = 'datang';
            $(this).removeClass('btn-outline-success').addClass('btn-success');
            $('#btn-barcode-pulang').removeClass('btn-primary').addClass('btn-outline-primary');
            $('#barcode-session-indicator').text('Sesi: Waktu Datang');
            refreshBarcodeQr();
        });

        $(document).on('click', '#btn-barcode-pulang', function() {
            activeBarcodeSession = 'pulang';
            $(this).removeClass('btn-outline-primary').addClass('btn-primary');
            $('#btn-barcode-datang').removeClass('btn-success').addClass('btn-outline-success');
            $('#barcode-session-indicator').text('Sesi: Waktu Pulang');
            refreshBarcodeQr();
        });

        $(document).on('hidden.bs.modal', '#modalBarcodeAbsensi', function() {
            clearInterval(barcodeTimer);
        });

        // Global Scan Barcode Modal Handler (Kamera Scanner Langsung dalam Modal)
        let modalHtml5QrCode = null;
        let isModalScanning = false;

        window.openScanBarcodeModal = function(el) {
            const btn = $(el);
            const title = btn.data('title') || 'Scan Barcode Kehadiran';
            const slug = btn.data('slug') || '';

            $('#modalScanBarcodeAbsensiLabel').text('Scan Kehadiran: ' + title);
            $('#modal-scan-loading').addClass('d-none').removeClass('d-flex');

            const modalEl = document.getElementById('modalScanBarcodeAbsensi');
            if (modalEl) {
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            }
        };

        $(document).on('click', '.btn-scan-barcode-modal', function(e) {
            e.preventDefault();
            window.openScanBarcodeModal(this);
        });

        $(document).on('shown.bs.modal', '#modalScanBarcodeAbsensi', function() {
            startModalScanner();
        });

        $(document).on('hidden.bs.modal', '#modalScanBarcodeAbsensi', function() {
            stopModalScanner();
        });

        function startModalScanner() {
            if (!window.isSecureContext) {
                Swal.fire({
                    title: 'Koneksi Tidak Aman!',
                    html: 'Akses kamera browser memerlukan koneksi <strong>HTTPS</strong> atau <code>localhost</code>.<br><br>Anda mengakses melalui:<br><code>' + window.location.protocol + '//' + window.location.host + '</code>',
                    icon: 'error',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }

            if (typeof Html5Qrcode === 'undefined') {
                Swal.fire('Error', 'Library scanner kamera belum termuat.', 'error');
                return;
            }

            $('#modal-scanner-wrapper').show();
            $('#modal-scan-loading').addClass('d-none').removeClass('d-flex');

            try {
                if (!modalHtml5QrCode) {
                    modalHtml5QrCode = new Html5Qrcode("modal-reader");
                }

                const config = {
                    fps: 15,
                    qrbox: { width: 220, height: 220 }
                };

                modalHtml5QrCode.start(
                    { facingMode: "environment" },
                    config,
                    onModalScanSuccess
                ).then(() => {
                    isModalScanning = true;
                }).catch((err) => {
                    console.error("Camera access failed", err);
                    let errorMsg = 'Gagal mengakses kamera. Pastikan izin kamera telah diberikan di browser Anda.';
                    const errName = err && err.name;
                    if (errName === 'NotAllowedError' || errName === 'PermissionDeniedError') {
                        errorMsg = 'Izin kamera ditolak. Aktifkan izin kamera untuk situs ini melalui pengaturan browser Anda.';
                    } else if (errName === 'NotFoundError' || errName === 'DevicesNotFoundError') {
                        errorMsg = 'Kamera tidak ditemukan pada perangkat ini.';
                    } else if (errName === 'NotReadableError' || errName === 'TrackStartError') {
                        errorMsg = 'Kamera sedang digunakan oleh aplikasi lain.';
                    }
                    Swal.fire({
                        title: 'Kamera Tidak Tersedia',
                        text: errorMsg,
                        icon: 'warning',
                        confirmButtonColor: '#3085d6'
                    });
                });
            } catch (ex) {
                console.error("Scanner init exception", ex);
            }
        }

        function stopModalScanner() {
            if (modalHtml5QrCode && isModalScanning) {
                modalHtml5QrCode.stop().then(() => {
                    isModalScanning = false;
                }).catch((err) => {
                    console.warn("Error stopping scanner", err);
                    isModalScanning = false;
                });
            }
        }

        function onModalScanSuccess(decodedText) {
            stopModalScanner();
            $('#modal-scan-loading').removeClass('d-none').addClass('d-flex');

            // Kirim request AJAX ke decodedText (URL endpoint scan-proses)
            $.ajax({
                url: decodedText,
                type: 'GET',
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function(response) {
                    const modalEl = document.getElementById('modalScanBarcodeAbsensi');
                    if (modalEl) {
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();
                    }

                    Swal.fire({
                        title: response.success ? 'Berhasil!' : 'Pemberitahuan',
                        text: response.message || 'Absensi berhasil diproses!',
                        icon: response.success ? 'success' : 'info',
                        confirmButtonColor: '#198754'
                    }).then(() => {
                        if (window.LaravelDataTables && window.LaravelDataTables["rencanakerja-table"]) {
                            window.LaravelDataTables["rencanakerja-table"].ajax.reload(null, false);
                        } else if (window.LaravelDataTables && window.LaravelDataTables["kepanitiaan-table"]) {
                            window.LaravelDataTables["kepanitiaan-table"].ajax.reload(null, false);
                        } else {
                            window.location.reload();
                        }
                    });
                },
                error: function(xhr) {
                    let errMessage = null;
                    try {
                        const res = xhr.responseJSON;
                        if (res && res.message) {
                            errMessage = res.message;
                        }
                    } catch (e) {}

                    if (errMessage) {
                        const modalEl = document.getElementById('modalScanBarcodeAbsensi');
                        if (modalEl) {
                            const modal = bootstrap.Modal.getInstance(modalEl);
                            if (modal) modal.hide();
                        }
                        Swal.fire({
                            title: 'Perhatian',
                            text: errMessage,
                            icon: 'warning',
                            confirmButtonColor: '#3085d6'
                        });
                    } else {
                        // Fallback redirect jika browser mengizinkan
                        window.location.href = decodedText;
                    }
                }
            });
        }

        // Multi Session Picker Modal Handler (Untuk Tugas dengan Banyak Sesi Absensi)
        window.openMultiSessionPicker = function(el, type) {
            const btn = $(el);
            let sessions = btn.attr('data-sessions');
            if (typeof sessions === 'string') {
                try {
                    sessions = JSON.parse(sessions);
                } catch(e) {
                    console.error("Invalid sessions JSON", e);
                    sessions = [];
                }
            }

            if (!sessions || !Array.isArray(sessions) || sessions.length === 0) {
                Swal.fire('Informasi', 'Tidak ada data sesi absensi yang terhubung.', 'info');
                return;
            }

            if (type === 'scan') {
                $('#modalPilihSesiHeader').css('background-color', '#0d6efd');
                $('#modalPilihSesiIcon').attr('class', 'bi bi-qr-code-scan fs-4 text-white');
                $('#modalPilihSesiAbsensiLabel').text('Pilih Sesi Scan Barcode');
                $('#modalPilihSesiSublabel').text('Pilih sesi untuk membuka scanner kamera');
            } else {
                $('#modalPilihSesiHeader').css('background-color', '#0f172a');
                $('#modalPilihSesiIcon').attr('class', 'bi bi-qr-code fs-4 text-white');
                $('#modalPilihSesiAbsensiLabel').text('Pilih Sesi Barcode Absensi');
                $('#modalPilihSesiSublabel').text('Pilih sesi untuk menampilkan barcode kehadiran');
            }

            let html = '';
            sessions.forEach(function(sess) {
                const iconClass = type === 'scan' ? 'bi-qr-code-scan text-primary' : 'bi-qr-code text-dark';
                const actionLabel = type === 'scan' ? 'Buka Scanner Kamera' : 'Buka QR Barcode';
                
                html += `
                <div class="card p-3 shadow-2xs rounded-3 session-choice-item mb-2" style="cursor: pointer; background-color: #ffffff;" 
                     data-slug="${sess.slug}" data-title="${sess.nama}" data-url="${sess.url}" data-type="${type}">
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle p-2 bg-light border d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px;">
                                <i class="bi ${iconClass} fs-5"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark" style="font-size: 0.92rem;">${sess.nama}</div>
                                <div class="text-muted small" style="font-size: 0.78rem;"><i class="bi bi-arrow-right-short"></i> ${actionLabel}</div>
                            </div>
                        </div>
                        <i class="bi bi-chevron-right text-secondary fs-5"></i>
                    </div>
                </div>
                `;
            });

            $('#modalPilihSesiContainer').html(html);

            const modalEl = document.getElementById('modalPilihSesiAbsensi');
            if (modalEl) {
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            }
        };

        $(document).on('click', '.session-choice-item', function() {
            const slug = $(this).data('slug');
            const title = $(this).data('title');
            const url = $(this).data('url');
            const type = $(this).data('type');

            const pickerModalEl = document.getElementById('modalPilihSesiAbsensi');
            if (pickerModalEl) {
                const pickerModal = bootstrap.Modal.getInstance(pickerModalEl);
                if (pickerModal) pickerModal.hide();
            }

            setTimeout(function() {
                if (type === 'scan') {
                    const dummyBtn = $('<button>')
                        .data('slug', slug)
                        .data('title', title)
                        .data('url', url);
                    window.openScanBarcodeModal(dummyBtn);
                } else {
                    const dummyBtn = $('<button>')
                        .data('slug', slug)
                        .data('title', title)
                        .data('url', url);
                    window.openBarcodeModal(dummyBtn);
                }
            }, 300);
        });
    </script>

    <!-- Modal Persetujuan Perpanjangan Waktu (Khusus Pimpinan / Admin) -->
    <div class="modal fade" id="modalApproveExtension" tabindex="-1" aria-labelledby="modalApproveExtensionLabel" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered">
            <form id="formApproveExtension" method="POST" action="" class="w-100">
                @csrf
                <div class="modal-content shadow-lg border-0">
                    <div class="modal-header bg-warning text-dark py-2 px-3">
                        <h6 class="modal-title fw-bold" id="modalApproveExtensionLabel"><i class="bi bi-clock-history me-1 text-dark"></i> Persetujuan Perpanjangan Waktu Tugas</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert alert-warning bg-warning-subtle border-warning-subtle text-dark small mb-3">
                            <i class="bi bi-info-circle-fill text-warning me-1"></i> Pegawai mengajukan permohonan perpanjangan waktu untuk tugas ini. Tentukan batas tanggal & jam selesai yang baru untuk membuka kembali kunci tugas.
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-bold small text-secondary">Pegawai Pemohon:</label>
                            <div id="approveTaskPegawai" class="p-2 bg-light rounded border text-dark fw-bold small"></div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-bold small text-secondary">Uraian Tugas:</label>
                            <div id="approveTaskUraian" class="p-2 bg-light rounded border text-dark fw-semibold small"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Rincian Permohonan & Kendala:</label>
                            <div id="approveTaskReasonContainer"></div>
                        </div>
                        <hr class="my-3">
                        <div class="row g-2 mb-3">
                            <div class="col-12 col-md-7">
                                <label for="appr_tgl" class="form-label fw-bold small text-dark"><i class="bi bi-calendar-event me-1 text-primary"></i>Tanggal Estimasi Baru <span class="text-danger">*</span></label>
                                <input type="date" name="estimasi_tanggal_selesai" id="appr_tgl" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-12 col-md-5">
                                <label for="appr_jam" class="form-label fw-bold small text-dark"><i class="bi bi-clock me-1 text-primary"></i>Jam Estimasi <span class="text-danger">*</span></label>
                                <input type="time" name="estimasi_jam_selesai" id="appr_jam" class="form-control form-control-sm" value="23:59" required>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label for="appr_catatan" class="form-label fw-bold small text-dark">Catatan / Arahan Pimpinan (Opsional):</label>
                            <textarea name="catatan_pimpinan" id="appr_catatan" class="form-control form-control-sm" rows="2" placeholder="Catatan arahan atau feedback untuk pegawai..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success btn-sm px-3 fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Setujui & Berikan Waktu Tambahan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Pemilihan Sesi Absensi (Untuk Multi-Select Absensi) -->
    <div class="modal fade" id="modalPilihSesiAbsensi" tabindex="-1" aria-labelledby="modalPilihSesiAbsensiLabel" aria-hidden="true" style="z-index: 1055;">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 460px;">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header text-white border-0 py-3 px-4" id="modalPilihSesiHeader" style="background-color: #0f172a;">
                    <div class="d-flex align-items-center gap-2">
                        <i id="modalPilihSesiIcon" class="bi bi-qr-code fs-4 text-white"></i>
                        <div>
                            <h5 class="modal-title fw-bold fs-6 mb-0 text-white" id="modalPilihSesiAbsensiLabel">Pilih Sesi Absensi</h5>
                            <small class="text-white-50" id="modalPilihSesiSublabel" style="font-size: 0.78rem;">Pilih sesi yang ingin Anda buka</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3 p-sm-4" style="background-color: #f8fafc;">
                    <div id="modalPilihSesiContainer" class="d-flex flex-column gap-1">
                        <!-- Dynamic items -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Barcode Absensi Universal -->
    <div class="modal fade" id="modalBarcodeAbsensi" tabindex="-1" aria-labelledby="modalBarcodeAbsensiLabel" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">
                <div class="modal-header border-0 pb-0 justify-content-between position-relative pt-4 px-4">
                    <div class="text-center w-100">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 mb-2 fw-semibold" style="font-size: 0.8rem;">
                            <i class="bi bi-qr-code-scan me-1"></i> Absensi Digital Kepanitiaan
                        </span>
                        <h5 class="modal-title font-bold text-center w-100 fw-bold text-dark fs-5" id="modalBarcodeAbsensiLabel">
                            Barcode Absensi
                        </h5>
                        <p class="text-muted small mb-0 mt-1" id="barcode-session-indicator">Sesi: Waktu Datang</p>
                    </div>
                    <button type="button" class="btn-close position-absolute end-0 top-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center px-4 pb-4 pt-3">
                    <!-- Session Toggle Buttons -->
                    <div class="d-flex justify-content-center gap-2 mb-3">
                        <button type="button" id="btn-barcode-datang" class="btn btn-sm btn-success d-inline-flex align-items-center gap-1 rounded-pill px-3 py-1 fw-bold shadow-sm">
                            <i class="bi bi-sun-fill"></i> Waktu Datang
                        </button>
                        <button type="button" id="btn-barcode-pulang" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 rounded-pill px-3 py-1 fw-bold shadow-sm">
                            <i class="bi bi-moon-stars-fill"></i> Waktu Pulang
                        </button>
                    </div>

                    <!-- QR Code Display Box -->
                    <div class="p-3 border rounded-3 shadow-sm bg-white mb-3 mx-auto" style="max-width: 300px;">
                        <img id="barcode-qr-img" src="" alt="Barcode Absensi" style="width: 100%; max-width: 270px; height: auto; aspect-ratio: 1 / 1; object-fit: contain; display: block; margin: 0 auto;">
                    </div>

                    <!-- Progress Bar -->
                    <div class="progress mb-2" style="height: 6px; width: 100%; max-width: 280px; margin: 0 auto; background-color: #e9ecef; border-radius: 1rem;">
                        <div id="barcode-qr-progress" class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%; border-radius: 1rem; transition: width 1s linear;"></div>
                    </div>

                    <!-- Countdown Text -->
                    <p class="text-muted small mb-3">
                        <i class="bi bi-lightning-fill text-warning"></i> Kode dinamis &mdash; berganti dalam <span id="barcode-countdown" class="fw-bold text-dark">60</span> detik
                    </p>

                    <div class="d-flex justify-content-center gap-2">
                        <a href="#" id="btn-visit-absensi" target="_blank" class="btn btn-sm btn-outline-dark d-inline-flex align-items-center gap-1 px-3 py-2 fw-semibold rounded-pill">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Buka Halaman Absensi
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Scan Barcode Universal (Kamera Scanner Langsung dalam Modal) -->
    <div class="modal fade" id="modalScanBarcodeAbsensi" tabindex="-1" aria-labelledby="modalScanBarcodeAbsensiLabel" aria-hidden="true" style="z-index: 1065;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem; overflow: hidden;">
                <div class="modal-header border-0 pb-0 justify-content-between position-relative pt-4 px-4">
                    <div class="text-center w-100">
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 mb-2 fw-semibold" style="font-size: 0.8rem;">
                            <i class="bi bi-camera-fill me-1"></i> Scanner Kamera
                        </span>
                        <h5 class="modal-title font-bold text-center w-100 fw-bold text-dark fs-5" id="modalScanBarcodeAbsensiLabel">
                            Scan Barcode Kehadiran
                        </h5>
                        <p class="text-muted small mb-0 mt-1">Arahkan kamera smartphone ke QR Code panitia</p>
                    </div>
                    <button type="button" class="btn-close position-absolute end-0 top-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center px-4 pb-4 pt-3">
                    <!-- Scanner Container -->
                    <div id="modal-scanner-wrapper" class="position-relative overflow-hidden border border-2 border-success rounded-3 mx-auto shadow-sm mb-3" style="max-width: 320px; aspect-ratio: 1; background: #000;">
                        <div id="modal-reader" style="width: 100%; height: 100%;"></div>
                        <div class="position-absolute top-0 start-0 end-0 bottom-0 d-flex align-items-center justify-content-center" style="pointer-events: none;">
                            <div class="position-relative" style="width: 220px; height: 220px; border: 2px dashed rgba(255,255,255,0.7); border-radius: 12px; box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.4);">
                                <div class="position-absolute border-top border-start border-4 border-success" style="width: 24px; height: 24px; top: -2px; left: -2px; border-top-left-radius: 8px;"></div>
                                <div class="position-absolute border-top border-end border-4 border-success" style="width: 24px; height: 24px; top: -2px; right: -2px; border-top-right-radius: 8px;"></div>
                                <div class="position-absolute border-bottom border-start border-4 border-success" style="width: 24px; height: 24px; bottom: -2px; left: -2px; border-bottom-left-radius: 8px;"></div>
                                <div class="position-absolute border-bottom border-end border-4 border-success" style="width: 24px; height: 24px; bottom: -2px; right: -2px; border-bottom-right-radius: 8px;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Loading Indicator -->
                    <div id="modal-scan-loading" class="alert alert-success py-2 small d-none align-items-center justify-content-center gap-2 mb-3">
                        <div class="spinner-border spinner-border-sm text-success" role="status"></div>
                        <span class="fw-semibold">QR Code terdeteksi! Menyimpan absensi...</span>
                    </div>

                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-secondary btn-sm px-4 py-2 rounded-pill fw-semibold" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i> Tutup Kamera
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @stack('scripts')

</body>

</html>
