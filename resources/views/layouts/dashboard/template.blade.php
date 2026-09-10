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
            .owner-actions-group .btn span {
                display: none !important;
            }
            .owner-actions-group .btn i {
                margin: 0 !important;
                font-size: 1.1rem !important;
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
        let currentBarcodeToken = '';
        let currentBarcodeScanUrl = '';
        let currentBarcodeUrl = '';
        let barcodeCountdown = 60;
        let barcodeTimer = null;

        function refreshBarcodeQr() {
            if (!currentBarcodeSlug && !currentBarcodeToken && !currentBarcodeScanUrl) return;
            const timestamp = Math.floor(Date.now() / 60000);
            let scanUrl = '';
            if (currentBarcodeScanUrl) {
                scanUrl = currentBarcodeScanUrl + (currentBarcodeScanUrl.includes('?') ? '&' : '?') + "session=" + activeBarcodeSession + "&t=" + timestamp;
            } else if (currentBarcodeToken) {
                scanUrl = "{{ url('presensi/scan-proses') }}/" + currentBarcodeToken + "?session=" + activeBarcodeSession + "&t=" + timestamp;
            } else {
                scanUrl = "{{ url('') }}/" + currentBarcodeSlug + "/scan-proses?session=" + activeBarcodeSession + "&t=" + timestamp;
            }
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
            const sessionTitle = btn.data('title') || 'Barcode Absensi';
            const isActive = btn.attr('data-active');

            if (isActive === '0') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sesi Absensi Ditutup',
                    html: `Sesi presensi <strong>${sessionTitle}</strong> saat ini sedang <strong>Nonaktif (Tutup / Dikunci)</strong>.<br><small class="text-muted">Administrator atau Panitia dapat mengaktifkan sesi ini melalui menu Kelola Presensi.</small>`,
                    confirmButtonText: 'Tutup',
                    confirmButtonColor: '#0d6efd'
                });
                return;
            }

            currentBarcodeSlug = btn.data('slug') || '';
            currentBarcodeToken = btn.data('token') || '';
            currentBarcodeScanUrl = btn.data('scan-url') || '';
            currentBarcodeUrl = btn.data('url') || (currentBarcodeScanUrl || ("{{ url('') }}/" + currentBarcodeSlug));

            $('#modalBarcodeAbsensiLabel').text(sessionTitle);
            $('#btn-visit-absensi').attr('href', currentBarcodeScanUrl || currentBarcodeUrl);

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
            const isActive = btn.attr('data-active');

            if (isActive === '0') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sesi Absensi Ditutup',
                    html: `Sesi presensi <strong>${title}</strong> saat ini sedang <strong>Nonaktif (Tutup)</strong>.<br><small class="text-muted">Administrator dapat mengaktifkan sesi ini melalui menu <strong>Pengaturan &gt; Status Absensi</strong>.</small>`,
                    confirmButtonText: 'Tutup',
                    confirmButtonColor: '#0d6efd'
                });
                return;
            }

            $('#modalScanBarcodeAbsensiLabel').text('Scan Kehadiran: ' + title);
            $('#modal-scan-loading').addClass('d-none').removeClass('d-flex');

            const modalEl = document.getElementById('modalScanBarcodeAbsensi');
            if (modalEl) {
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            }
        };

        // Global Real-Time Sync for Active Sessions on Sidebar
        window.updateSidebarActiveSession = function(data) {
            const container = $('#sidebar-presensi-active-item');
            const badge = $('#sidebar-active-sessions-badge');
            const navList = $('#presensi-kepanitiaan-nav');

            if (!navList.length) return;

            if (data.is_active) {
                let existingItem = $('#sidebar-sesi-item-' + data.id);
                if (!existingItem.length) {
                    const newItemHtml = `
                        <li id="sidebar-sesi-item-${data.id}">
                            <a href="${data.url}" class="d-flex align-items-center gap-2 py-1.5 px-3" title="${data.nama}">
                                <i class="bi bi-circle-fill text-success flex-shrink-0" style="font-size: 6px;"></i>
                                <span class="text-truncate" style="font-size: 13px; font-weight: 500;">${data.nama}</span>
                            </a>
                        </li>
                    `;
                    navList.append(newItemHtml);
                } else {
                    existingItem.show();
                }

                container.show();
                if (!navList.hasClass('show')) {
                    navList.addClass('show');
                    container.find('a[data-bs-toggle="collapse"]').removeClass('collapsed');
                }
            } else {
                $('#sidebar-sesi-item-' + data.id).remove();
            }

            const activeItemsCount = data.active_count !== undefined ? data.active_count : navList.find('li').length;
            badge.text(activeItemsCount);

            if (activeItemsCount <= 0) {
                container.hide();
            } else {
                container.show();
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
                        } else if (typeof $ !== 'undefined' && $.fn.DataTable && $.fn.DataTable.isDataTable('#dashboard-attendance-table')) {
                            $('#dashboard-attendance-table').DataTable().ajax.reload(null, false);
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
            } else if (type === 'barcode') {
                $('#modalPilihSesiHeader').css('background-color', '#0f172a');
                $('#modalPilihSesiIcon').attr('class', 'bi bi-qr-code fs-4 text-white');
                $('#modalPilihSesiAbsensiLabel').text('Pilih Sesi Barcode Absensi');
                $('#modalPilihSesiSublabel').text('Pilih sesi untuk menampilkan barcode kehadiran');
            } else if (type === 'export') {
                $('#modalPilihSesiHeader').css('background-color', '#198754');
                $('#modalPilihSesiIcon').attr('class', 'bi bi-file-earmark-excel-fill fs-4 text-white');
                $('#modalPilihSesiAbsensiLabel').text('Pilih Sesi Export Excel');
                $('#modalPilihSesiSublabel').text('Pilih sesi untuk mengunduh rekap absensi Excel');
            } else if (type === 'multiselect') {
                $('#modalPilihSesiHeader').css('background-color', '#0284c7');
                $('#modalPilihSesiIcon').attr('class', 'bi bi-check2-square fs-4 text-white');
                $('#modalPilihSesiAbsensiLabel').text('Pilih Sesi Multi-Select Presensi');
                $('#modalPilihSesiSublabel').text('Pilih sesi untuk mengelola kehadiran & multi-select');
            }

            // Filter hanya sesi yang aktif (sesi yang dikunci tidak perlu ditampilkan)
            const activeSessionsList = sessions.filter(function(sess) {
                return (sess.is_active === true || sess.is_active === 1 || sess.is_active === "1" || sess.is_active === "true");
            });

            if (activeSessionsList.length === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'Sesi Ditutup',
                    text: 'Saat ini seluruh sesi absensi terkait sedang dinonaktifkan (dikunci) oleh Administrator.',
                    confirmButtonColor: '#0d6efd'
                });
                return;
            }

            let html = '';
            activeSessionsList.forEach(function(sess) {
                let iconClass = 'bi-qr-code text-dark';
                let actionLabel = 'Buka QR Barcode';
                if (type === 'scan') {
                    iconClass = 'bi-qr-code-scan text-primary';
                    actionLabel = 'Buka Scanner Kamera';
                } else if (type === 'export') {
                    iconClass = 'bi-file-earmark-excel-fill text-success';
                    actionLabel = 'Download Rekap Excel';
                } else if (type === 'multiselect') {
                    iconClass = 'bi-check2-square text-info';
                    actionLabel = 'Kelola Presensi & Multi-Select';
                }

                const token = sess.token || '';
                const scanUrl = sess.scan_url || '';
                const exportUrl = sess.export_url || '';
                const sessId = sess.id || '';

                html += `
                <div class="card p-3 shadow-2xs rounded-3 session-choice-item mb-2 border" 
                     style="cursor: pointer; background-color: #ffffff;" 
                     data-id="${sessId}"
                     data-slug="${sess.slug}" 
                     data-token="${token}"
                     data-scan-url="${scanUrl}"
                     data-title="${sess.nama}" 
                     data-url="${sess.url}" 
                     data-export-url="${exportUrl}"
                     data-type="${type}"
                     data-active="1">
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
                        <i class="bi bi-chevron-right text-primary fs-5"></i>
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
            const isActive = $(this).attr('data-active') === '1';
            const title = $(this).data('title');

            if (!isActive) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sesi Absensi Ditutup',
                    html: `Sesi presensi <strong>${title}</strong> saat ini sedang <strong>Nonaktif (Tutup / Dikunci)</strong>.`,
                    confirmButtonText: 'Tutup',
                    confirmButtonColor: '#0d6efd'
                });
                return;
            }

            const slug = $(this).data('slug');
            const token = $(this).data('token');
            const scanUrl = $(this).data('scan-url');
            const url = $(this).data('url');
            const type = $(this).data('type');
            const sessId = $(this).data('id');
            const exportUrl = $(this).data('export-url');

            const pickerModalEl = document.getElementById('modalPilihSesiAbsensi');
            if (pickerModalEl) {
                const pickerModal = bootstrap.Modal.getInstance(pickerModalEl);
                if (pickerModal) pickerModal.hide();
            }

            setTimeout(function() {
                if (type === 'scan') {
                    const dummyBtn = $('<button>')
                        .data('slug', slug)
                        .data('token', token)
                        .data('scan-url', scanUrl)
                        .data('title', title)
                        .data('url', url);
                    window.openScanBarcodeModal(dummyBtn);
                } else if (type === 'barcode') {
                    const dummyBtn = $('<button>')
                        .data('slug', slug)
                        .data('token', token)
                        .data('scan-url', scanUrl)
                        .data('title', title)
                        .data('url', url);
                    window.openBarcodeModal(dummyBtn);
                } else if (type === 'export') {
                    const targetExport = exportUrl || (sessId ? `{{ url('presensi/sesi') }}/${sessId}/export` : '');
                    if (targetExport) {
                        window.location.href = targetExport;
                    }
                } else if (type === 'multiselect') {
                    if (sessId) {
                        window.openPresensiMultiSelectModal(sessId, title);
                    }
                }
            }, 300);
        });

        // ==========================================
        // MULTI-SELECT & PRESENSI MODAL ENGINE
        // ==========================================
        let currentGmpSesiId = null;
        let currentGmpFilter = 'all';
        const gmpSelectedUsersMap = new Map();
        let gmpTable = null;

        function updateGmpBulkToolbarUI() {
            const count = gmpSelectedUsersMap.size;
            $('#gmp-selected-count').text(count);
            $('#gmp-bulk-modal-count').text(count);
            $('#gmp-bulk-names-count').text(count);
            $('#gmp-bulk-btn-count').text(count);

            if (count > 0) {
                $('#gmp-bulk-toolbar').attr('style', 'display: flex !important;');
            } else {
                $('#gmp-bulk-toolbar').attr('style', 'display: none !important;');
            }

            if (count > 0) {
                const names = Array.from(gmpSelectedUsersMap.values()).map(n => `<span class="badge bg-white text-dark border me-1 mb-1 shadow-xs">${n}</span>`).join(' ');
                $('#gmp-bulk-names-preview').html(names);
            } else {
                $('#gmp-bulk-names-preview').html('<span class="text-muted fst-italic">Tidak ada pegawai yang dipilih.</span>');
            }
        }

        window.clearGmpSelectedRows = function() {
            gmpSelectedUsersMap.clear();
            $('#gmp-check-all-rows').prop('checked', false);
            $('.gmp-check-row').prop('checked', false);
            updateGmpBulkToolbarUI();
        };

        window.openPresensiMultiSelectModal = function(sesiId, sesiTitle) {
            currentGmpSesiId = sesiId;
            currentGmpFilter = 'all';
            gmpSelectedUsersMap.clear();

            $('#modalPresensiMultiSelectTitle').text('Presensi: ' + (sesiTitle || 'Sesi Presensi'));
            $('#modalPresensiMultiSelectSubtitle').text('Kelola kehadiran pegawai & perbarui status absensi secara massal (' + (sesiTitle || '') + ')');
            $('#gmp-btn-export').attr('href', `{{ url('presensi/sesi') }}/${sesiId}/export`);
            $('#gmp-btn-fullpage').attr('href', `{{ url('presensi/sesi') }}/${sesiId}`);

            // Reset filter buttons
            $('.btn-gmp-filter').removeClass('btn-primary active').addClass('btn-outline-secondary');
            $('.btn-gmp-filter[data-filter="all"]').removeClass('btn-outline-secondary').addClass('btn-primary active');
            $('#label-gmp-filter-khusus').text('Pilih');
            updateGmpBulkToolbarUI();

            // Modal show
            const modalEl = document.getElementById('modalPresensiMultiSelect');
            if (modalEl) {
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            }

            // Init or reload table
            if ($.fn.DataTable.isDataTable('#gmp-table')) {
                $('#gmp-table').DataTable().destroy();
                $('#gmp-table tbody').empty();
            }

            gmpTable = $('#gmp-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: `{{ url('presensi/sesi') }}/${sesiId}/data`,
                    data: function(d) {
                        d.filter = currentGmpFilter;
                    }
                },
                columns: [
                    { 
                        data: 'checkbox', 
                        name: 'checkbox', 
                        orderable: false, 
                        searchable: false, 
                        className: 'text-center',
                        render: function(data, type, row) {
                            return `<div class="form-check d-flex justify-content-center m-0">
                                <input type="checkbox" class="form-check-input gmp-check-row" 
                                       value="${row.user_id}" 
                                       data-user-name="${row.user_name || ''}" 
                                       style="cursor: pointer; width: 17px; height: 17px;">
                            </div>`;
                        }
                    },
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center fw-semibold text-muted' },
                    { data: 'tanggal', name: 'created_at', className: 'text-center' },
                    { data: 'user_info', name: 'user_info' },
                    { data: 'status_datang', name: 'hadir_datang', className: 'text-center' },
                    { data: 'status_pulang', name: 'hadir_pulang', className: 'text-center' },
                    { data: 'catatan_col', name: 'catatan_hadir_datang', orderable: false },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' },
                ],
                order: [[3, 'asc']],
                pageLength: 15,
                drawCallback: function(settings) {
                    let allChecked = true;
                    let countInPage = 0;

                    $('.gmp-check-row').each(function() {
                        countInPage++;
                        const uId = $(this).val();
                        if (gmpSelectedUsersMap.has(uId)) {
                            $(this).prop('checked', true);
                        } else {
                            $(this).prop('checked', false);
                            allChecked = false;
                        }
                    });

                    if (countInPage === 0 || !allChecked) {
                        $('#gmp-check-all-rows').prop('checked', false);
                    } else {
                        $('#gmp-check-all-rows').prop('checked', true);
                    }

                    updateGmpBulkToolbarUI();
                },
                language: {
                    processing: '<div class="spinner-border spinner-border-sm text-primary me-2"></div> Memuat data presensi...',
                    zeroRecords: '<div class="text-center py-4 text-muted"><i class="bi bi-people fs-3 d-block mb-1"></i>Tidak ada data pegawai yang sesuai dengan filter.</div>',
                    info: "Menampilkan <strong>_START_-_END_</strong> dari <strong>_TOTAL_</strong> pegawai",
                    infoEmpty: "Menampilkan 0 data",
                    infoFiltered: "(disaring dari _MAX_ pegawai)",
                    search: "Cari Pegawai:",
                    paginate: {
                        previous: '<i class="bi bi-chevron-left"></i>',
                        next: '<i class="bi bi-chevron-right"></i>'
                    }
                }
            });
        };

        // Header check all
        $(document).on('change', '#gmp-check-all-rows', function() {
            const isChecked = $(this).is(':checked');
            $('.gmp-check-row').each(function() {
                const uId = $(this).val();
                const uName = $(this).data('user-name') || ('Pegawai #' + uId);
                $(this).prop('checked', isChecked);
                if (isChecked) {
                    gmpSelectedUsersMap.set(uId, uName);
                } else {
                    gmpSelectedUsersMap.delete(uId);
                }
            });
            updateGmpBulkToolbarUI();
        });

        // Individual row check
        $(document).on('change', '.gmp-check-row', function() {
            const uId = $(this).val();
            const uName = $(this).data('user-name') || ('Pegawai #' + uId);
            if ($(this).is(':checked')) {
                gmpSelectedUsersMap.set(uId, uName);
            } else {
                gmpSelectedUsersMap.delete(uId);
            }

            let allChecked = true;
            $('.gmp-check-row').each(function() {
                if (!$(this).is(':checked')) allChecked = false;
            });
            $('#gmp-check-all-rows').prop('checked', allChecked);

            updateGmpBulkToolbarUI();
        });

        // Filter buttons
        $(document).on('click', '.btn-gmp-filter', function() {
            $('.btn-gmp-filter').removeClass('btn-primary active').addClass('btn-outline-secondary');
            $(this).removeClass('btn-outline-secondary').addClass('btn-primary active');
            $('#label-gmp-filter-khusus').text('Pilih');
            currentGmpFilter = $(this).data('filter');
            if (gmpTable) gmpTable.ajax.reload();
        });

        $(document).on('click', '.gmp-filter-detail-item', function() {
            $('.btn-gmp-filter').removeClass('btn-primary active').addClass('btn-outline-secondary');
            const labelText = $(this).text().trim();
            $('#label-gmp-filter-khusus').text(labelText);
            currentGmpFilter = $(this).data('filter');
            if (gmpTable) gmpTable.ajax.reload();
        });

        // Open Bulk Update Modal
        window.openGmpBulkModal = function() {
            if (gmpSelectedUsersMap.size === 0) {
                Swal.fire('Peringatan', 'Silakan centang minimal satu pegawai untuk diubah presensinya.', 'warning');
                return;
            }

            const now = new Date();
            const currentTime = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
            $('#gmp_bulk_waktu_datang').val(currentTime);
            $('#gmp_bulk_waktu_pulang').val(currentTime);

            const modalEl = document.getElementById('modalGmpBulkPresensi');
            if (modalEl) {
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            }
        };

        // Switch listeners
        $(document).on('change', '#gmp_switch_update_datang', function() {
            const on = $(this).is(':checked');
            $('#gmp_wrapper_fields_datang').css('opacity', on ? '1' : '0.4').find('input, select').prop('disabled', !on);
            $('#gmp-badge-datang-status').text(on ? 'Diubah' : 'Tidak Diubah').attr('class', on ? 'badge bg-success-subtle text-success small' : 'badge bg-secondary-subtle text-secondary small');
        });

        $(document).on('change', '#gmp_switch_update_pulang', function() {
            const on = $(this).is(':checked');
            $('#gmp_wrapper_fields_pulang').css('opacity', on ? '1' : '0.4').find('input, select').prop('disabled', !on);
            $('#gmp-badge-pulang-status').text(on ? 'Diubah' : 'Tidak Diubah').attr('class', on ? 'badge bg-success-subtle text-success small' : 'badge bg-secondary-subtle text-secondary small');
        });

        $(document).on('change', '#gmp_switch_update_catatan', function() {
            const on = $(this).is(':checked');
            if (on) {
                $('#gmp_wrapper_field_catatan').slideDown(150);
            } else {
                $('#gmp_wrapper_field_catatan').slideUp(150);
            }
        });

        // Submit Bulk Presensi
        $(document).on('submit', '#formGmpBulkPresensi', function(e) {
            e.preventDefault();
            if (!currentGmpSesiId) return;

            const selectedIds = Array.from(gmpSelectedUsersMap.keys());
            if (selectedIds.length === 0) {
                Swal.fire('Peringatan', 'Tidak ada pegawai yang dipilih.', 'warning');
                return;
            }

            const btn = $('#gmp-btn-submit-bulk');
            const originalText = btn.html();
            btn.prop('disabled', true).html('<div class="spinner-border spinner-border-sm me-1"></div> Menyimpan...');

            $.ajax({
                url: `{{ url('presensi/sesi') }}/${currentGmpSesiId}/bulk-update`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    user_ids: selectedIds,
                    update_datang: $('#gmp_switch_update_datang').is(':checked') ? 1 : 0,
                    hadir_datang: $('#gmp_bulk_hadir_datang').val(),
                    waktu_datang: $('#gmp_bulk_waktu_datang').val(),
                    update_pulang: $('#gmp_switch_update_pulang').is(':checked') ? 1 : 0,
                    hadir_pulang: $('#gmp_bulk_hadir_pulang').val(),
                    waktu_pulang: $('#gmp_bulk_waktu_pulang').val(),
                    update_catatan: $('#gmp_switch_update_catatan').is(':checked') ? 1 : 0,
                    catatan: $('#gmp_bulk_catatan').val(),
                },
                dataType: 'json',
                success: function(res) {
                    btn.prop('disabled', false).html(originalText);
                    if (res.success) {
                        const modalEl = document.getElementById('modalGmpBulkPresensi');
                        if (modalEl) {
                            const modal = bootstrap.Modal.getInstance(modalEl);
                            if (modal) modal.hide();
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil Diperbarui!',
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false
                        });

                        window.clearGmpSelectedRows();
                        if (gmpTable) gmpTable.ajax.reload(null, false);
                        if (window.LaravelDataTables && window.LaravelDataTables["kepanitiaan-table"]) {
                            window.LaravelDataTables["kepanitiaan-table"].ajax.reload(null, false);
                        }
                    } else {
                        Swal.fire('Gagal', res.message || 'Terjadi kesalahan.', 'error');
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html(originalText);
                    let err = 'Terjadi kesalahan saat memperbarui presensi massal.';
                    if (xhr.responseJSON && xhr.responseJSON.message) err = xhr.responseJSON.message;
                    Swal.fire('Gagal', err, 'error');
                }
            });
        });

        // Handler Kelola Sesi Presensi Kepanitiaan (Universal Dynamic Attendance)
        let currentManageKepanitiaanId = null;

        window.openManageSesiModal = function(kepanitiaanId, kepanitiaanTitle) {
            currentManageKepanitiaanId = kepanitiaanId;
            $('#modalKelolaSesiTitle').text(kepanitiaanTitle || 'Kepanitiaan');
            $('#formTambahSesiContainer').hide();
            $('#btnToggleTambahSesi').html('<i class="bi bi-plus-circle me-1"></i> Tambah Sesi Baru');
            $('#input_nama_sesi').val('');
            $('#input_tanggal_sesi').val(new Date().toISOString().split('T')[0]);

            loadSesiPresensiList(kepanitiaanId);

            const modalEl = document.getElementById('modalKelolaSesiPresensi');
            if (modalEl) {
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            }
        };

        function loadSesiPresensiList(kepanitiaanId) {
            $('#tableSesiPresensiBody').html('<tr><td colspan="6" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2"></div>Memuat daftar sesi presensi...</td></tr>');

            $.ajax({
                url: "{{ url('kepanitiaan') }}/" + kepanitiaanId + "/sesi",
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res.success && res.sesis) {
                        renderSesiPresensiTable(res.sesis);
                    } else {
                        $('#tableSesiPresensiBody').html('<tr><td colspan="6" class="text-center py-3 text-muted">Gagal memuat sesi.</td></tr>');
                    }
                },
                error: function() {
                    $('#tableSesiPresensiBody').html('<tr><td colspan="6" class="text-center py-3 text-danger"><i class="bi bi-exclamation-triangle me-1"></i> Terjadi kesalahan saat memuat data.</td></tr>');
                }
            });
        }

        function renderSesiPresensiTable(sesis) {
            if (!sesis || sesis.length === 0) {
                $('#tableSesiPresensiBody').html('<tr><td colspan="6" class="text-center py-4 text-muted"><i class="bi bi-calendar-x fs-4 d-block mb-1 text-secondary"></i>Belum ada sesi presensi untuk kepanitiaan ini.<br><small class="text-primary">Klik "+ Tambah Sesi Baru" di atas untuk membuat sesi pertama.</small></td></tr>');
                return;
            }

            let html = '';
            sesis.forEach(function(s, idx) {
                const badgeClass = s.is_active ? 'bg-success' : 'bg-secondary';
                const badgeText = s.is_active ? 'Aktif' : 'Tutup (Kunci)';
                const checked = s.is_active ? 'checked' : '';

                html += `
                <tr id="row-manage-sesi-${s.id}">
                    <td class="text-center fw-semibold text-muted" style="vertical-align: middle;">${idx + 1}</td>
                    <td style="vertical-align: middle;">
                        <a href="${s.detail_url}" class="fw-bold text-primary text-decoration-none d-block">${s.nama_sesi}</a>
                    </td>
                    <td class="text-center" style="vertical-align: middle;">
                        <span class="badge bg-light text-dark border px-2 py-1" style="font-size: 0.78rem;">${s.tanggal || '-'}</span>
                    </td>
                    <td class="text-center" style="vertical-align: middle;">
                        <a href="${s.detail_url}" class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 fw-bold text-decoration-none" title="Buka Data Absensi">
                            <i class="bi bi-people-fill me-1"></i>${s.total_presensi} Hadir
                        </a>
                    </td>
                    <td class="text-center" style="vertical-align: middle;">
                        <div class="d-flex align-items-center justify-content-center gap-2">
                            <span id="badge-manage-sesi-${s.id}" class="badge ${badgeClass} px-2 py-1" style="font-size: 0.75rem;">${badgeText}</span>
                            <div class="form-check form-switch m-0 p-0" style="min-height: auto;">
                                <input class="form-check-input toggle-manage-sesi ms-0" type="checkbox" role="switch"
                                       data-id="${s.id}"
                                       data-nama="${s.nama_sesi}"
                                       style="width: 2.4em; height: 1.2em; cursor: pointer;"
                                       ${checked}>
                            </div>
                        </div>
                    </td>
                    <td class="text-center" style="vertical-align: middle;">
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-primary" onclick="window.openPresensiMultiSelectModal(${s.id}, '${s.nama_sesi.replace(/'/g, "\\'")}')" title="Kelola Presensi & Multi-Select Pegawai">
                                <i class="bi bi-check2-square me-1"></i>Multi-Select
                            </button>
                            <a href="${s.detail_url}" class="btn btn-outline-secondary" title="Buka Halaman Presensi Lengkap" target="_blank">
                                <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                            <a href="${s.export_url}" class="btn btn-outline-success" title="Download Rekap Presensi Excel" target="_blank">
                                <i class="bi bi-file-earmark-excel-fill"></i>
                            </a>
                            <button type="button" class="btn btn-outline-danger btn-delete-sesi" data-id="${s.id}" data-nama="${s.nama_sesi}" title="Hapus Sesi">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                `;
            });

            $('#tableSesiPresensiBody').html(html);
        }

        $(document).on('click', '#btnToggleTambahSesi', function() {
            const container = $('#formTambahSesiContainer');
            if (container.is(':visible')) {
                container.slideUp(200);
                $(this).html('<i class="bi bi-plus-circle me-1"></i> Tambah Sesi Baru');
            } else {
                container.slideDown(200);
                $(this).html('<i class="bi bi-dash-circle me-1"></i> Batal Tambah Sesi');
                $('#input_nama_sesi').focus();
            }
        });

        $(document).on('submit', '#formTambahSesiPresensi', function(e) {
            e.preventDefault();
            if (!currentManageKepanitiaanId) return;

            const submitBtn = $(this).find('button[type="submit"]');
            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...');

            $.ajax({
                url: "{{ url('kepanitiaan') }}/" + currentManageKepanitiaanId + "/sesi",
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(res) {
                    submitBtn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan Sesi');
                    if (res.success) {
                        $('#input_nama_sesi').val('');
                        $('#formTambahSesiContainer').slideUp(200);
                        $('#btnToggleTambahSesi').html('<i class="bi bi-plus-circle me-1"></i> Tambah Sesi Baru');
                        loadSesiPresensiList(currentManageKepanitiaanId);
                        if (window.LaravelDataTables && window.LaravelDataTables["kepanitiaan-table"]) {
                            window.LaravelDataTables["kepanitiaan-table"].ajax.reload(null, false);
                        }
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message,
                            timer: 1800,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire('Gagal', res.message || 'Gagal menambahkan sesi.', 'error');
                    }
                },
                error: function(xhr) {
                    submitBtn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan Sesi');
                    let msg = 'Terjadi kesalahan validasi atau server.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Swal.fire('Gagal', msg, 'error');
                }
            });
        });

        $(document).on('change', '.toggle-manage-sesi', function() {
            const sesiId = $(this).data('id');
            const sesiNama = $(this).data('nama');
            const isChecked = $(this).is(':checked');
            const switchEl = $(this);

            $.ajax({
                url: "{{ url('presensi/sesi') }}/" + sesiId + "/toggle",
                type: 'POST',
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(res) {
                    if (res.success) {
                        const badge = $('#badge-manage-sesi-' + sesiId);
                        if (res.is_active) {
                            badge.removeClass('bg-secondary').addClass('bg-success').text('Aktif');
                        } else {
                            badge.removeClass('bg-success').addClass('bg-secondary').text('Tutup (Kunci)');
                        }
                        if (window.LaravelDataTables && window.LaravelDataTables["kepanitiaan-table"]) {
                            window.LaravelDataTables["kepanitiaan-table"].ajax.reload(null, false);
                        }
                    } else {
                        switchEl.prop('checked', !isChecked);
                        Swal.fire('Gagal', res.message || 'Gagal mengubah status sesi.', 'error');
                    }
                },
                error: function() {
                    switchEl.prop('checked', !isChecked);
                    Swal.fire('Error', 'Gagal menghubungi server.', 'error');
                }
            });
        });

        $(document).on('click', '.btn-delete-sesi', function() {
            const sesiId = $(this).data('id');
            const sesiNama = $(this).data('nama');

            Swal.fire({
                title: 'Hapus Sesi Presensi?',
                html: `Apakah Anda yakin ingin menghapus sesi <strong>${sesiNama}</strong>?<br><small class="text-danger">Seluruh data presensi pada sesi ini juga akan terhapus.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('presensi/sesi') }}/" + sesiId,
                        type: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: '{{ csrf_token() }}'
                        },
                        dataType: 'json',
                        success: function(res) {
                            if (res.success) {
                                $('#row-manage-sesi-' + sesiId).fadeOut(300, function() { $(this).remove(); });
                                if (window.LaravelDataTables && window.LaravelDataTables["kepanitiaan-table"]) {
                                    window.LaravelDataTables["kepanitiaan-table"].ajax.reload(null, false);
                                }
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Terhapus!',
                                    text: res.message,
                                    timer: 1800,
                                    showConfirmButton: false
                                });
                            } else {
                                Swal.fire('Gagal', res.message, 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error', 'Terjadi kesalahan saat menghapus sesi.', 'error');
                        }
                    });
                }
            });
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

    <!-- Modal Kelola Sesi Presensi Kepanitiaan (Universal Dynamic Attendance) -->
    <div class="modal fade" id="modalKelolaSesiPresensi" tabindex="-1" aria-labelledby="modalKelolaSesiPresensiLabel" aria-hidden="true" style="z-index: 1065;">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem; overflow: hidden;">
                <div class="modal-header bg-dark text-white py-3 px-4 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-calendar2-range fs-5 text-primary"></i>
                        <div>
                            <h6 class="modal-title fw-bold mb-0 text-white" id="modalKelolaSesiPresensiLabel">Kelola Sesi Presensi</h6>
                            <small class="text-white-50" id="modalKelolaSesiTitle">Kepanitiaan</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <!-- Action Top Bar -->
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                        <span class="text-muted small"><i class="bi bi-info-circle me-1"></i>Sesi yang <strong>Tutup (Kunci)</strong> otomatis disembunyikan dari peserta.</span>
                        <button type="button" id="btnToggleTambahSesi" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm fw-semibold">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Sesi Baru
                        </button>
                    </div>

                    <!-- Inline Form Tambah Sesi Baru -->
                    <div id="formTambahSesiContainer" class="card border-0 shadow-sm rounded-3 mb-3 p-3 bg-white" style="display: none; border-left: 4px solid #0d6efd !important;">
                        <h6 class="fw-bold text-dark mb-2 small"><i class="bi bi-calendar-plus me-1 text-primary"></i> Form Tambah Sesi Presensi Baru</h6>
                        <form id="formTambahSesiPresensi">
                            <div class="row g-2 align-items-end">
                                <div class="col-12 col-md-7">
                                    <label class="form-label small fw-semibold text-secondary mb-1">Nama Sesi Presensi <span class="text-danger">*</span></label>
                                    <input type="text" name="nama_sesi" id="input_nama_sesi" class="form-control form-control-sm" placeholder="Contoh: Absensi Hari Ke-1, Rapat Panitia..." required>
                                </div>
                                <div class="col-12 col-md-3">
                                    <label class="form-label small fw-semibold text-secondary mb-1">Tanggal Pelaksanaan</label>
                                    <input type="date" name="tanggal" id="input_tanggal_sesi" class="form-control form-control-sm">
                                </div>
                                <div class="col-12 col-md-2">
                                    <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold">
                                        <i class="bi bi-save me-1"></i> Simpan
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Table Sesi Presensi Container -->
                    <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" style="width: 45px;">#</th>
                                        <th>Nama Sesi & Token</th>
                                        <th class="text-center" style="width: 110px;">Tanggal</th>
                                        <th class="text-center" style="width: 110px;">Kehadiran</th>
                                        <th class="text-center" style="width: 160px;">Status Sesi</th>
                                        <th class="text-center" style="width: 90px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tableSesiPresensiBody">
                                    <!-- Loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white border-top py-2 px-4 justify-content-between">
                    <span class="text-muted small">QR Code dan URL dibuat secara dinamis &amp; otomatis</span>
                    <button type="button" class="btn btn-secondary btn-sm px-4 rounded-pill fw-semibold" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Global Presensi & Multi-Select Pegawai -->
    <div class="modal fade" id="modalPresensiMultiSelect" tabindex="-1" aria-labelledby="modalPresensiMultiSelectLabel" aria-hidden="true" style="z-index: 1065;">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem; overflow: hidden;">
                <div class="modal-header bg-dark text-white py-3 px-4 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check2-square fs-4 text-primary"></i>
                        <div>
                            <h6 class="modal-title fw-bold mb-0 text-white" id="modalPresensiMultiSelectTitle">Presensi Pegawai &amp; Multi-Select</h6>
                            <small class="text-white-50" id="modalPresensiMultiSelectSubtitle">Kelola kehadiran pegawai &amp; perbarui status absensi secara massal</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a href="#" id="gmp-btn-export" class="btn btn-sm btn-success text-white fw-semibold d-inline-flex align-items-center gap-1 shadow-xs" title="Download Rekap Absensi Excel">
                            <i class="bi bi-file-earmark-excel-fill"></i> <span class="d-none d-sm-inline">Export Excel</span>
                        </a>
                        <a href="#" id="gmp-btn-fullpage" target="_blank" class="btn btn-sm btn-outline-light d-inline-flex align-items-center gap-1 shadow-xs" title="Buka Halaman Presensi Penuh di Tab Baru">
                            <i class="bi bi-box-arrow-up-right"></i> <span class="d-none d-md-inline">Halaman Penuh</span>
                        </a>
                        <button type="button" class="btn-close btn-close-white ms-1" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body p-4 bg-light">
                    <!-- Filter & Bulk Action Toolbar -->
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3 p-3 bg-white rounded-3 border shadow-2xs">
                        <!-- Filter Buttons Group -->
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <span class="text-muted small fw-bold me-1"><i class="bi bi-funnel text-primary me-1"></i>Filter Presensi:</span>
                            <div class="btn-group shadow-xs" role="group" aria-label="Filter Presensi">
                                <button type="button" class="btn btn-sm btn-primary active btn-gmp-filter" data-filter="all">
                                    <i class="bi bi-people me-1"></i>Semua
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-success btn-gmp-filter" data-filter="sudah">
                                    <i class="bi bi-check-circle me-1"></i>Sudah Absen
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger btn-gmp-filter" data-filter="belum">
                                    <i class="bi bi-x-circle me-1"></i>Belum Absen
                                </button>
                            </div>

                            <!-- Dropdown Filter Lanjutan -->
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle d-inline-flex align-items-center gap-1 shadow-xs font-medium" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="btnGmpFilterLanjutan">
                                    <i class="bi bi-sliders"></i> Filter Khusus: <span id="label-gmp-filter-khusus" class="fw-bold">Pilih</span>
                                </button>
                                <ul class="dropdown-menu shadow border-0" style="font-size: 0.84rem; min-width: 260px; border-radius: 10px;">
                                    <li class="dropdown-header text-uppercase fw-bold text-muted" style="font-size: 0.72rem;">Filter Detail Kehadiran</li>
                                    <li><a class="dropdown-item py-2 gmp-filter-detail-item" href="javascript:void(0)" data-filter="sudah_lengkap"><i class="bi bi-check2-all text-success me-2"></i>Sudah Lengkap (Datang & Pulang)</a></li>
                                    <li><a class="dropdown-item py-2 gmp-filter-detail-item" href="javascript:void(0)" data-filter="sudah_datang"><i class="bi bi-box-arrow-in-right text-success me-2"></i>Sudah Absen Datang</a></li>
                                    <li><a class="dropdown-item py-2 gmp-filter-detail-item" href="javascript:void(0)" data-filter="sudah_pulang"><i class="bi bi-box-arrow-right text-info me-2"></i>Sudah Absen Pulang</a></li>
                                    <li><hr class="dropdown-divider my-1"></li>
                                    <li><a class="dropdown-item py-2 gmp-filter-detail-item" href="javascript:void(0)" data-filter="belum_pulang"><i class="bi bi-clock-history text-warning me-2"></i>Belum Absen Pulang</a></li>
                                    <li><a class="dropdown-item py-2 gmp-filter-detail-item" href="javascript:void(0)" data-filter="belum_datang"><i class="bi bi-exclamation-circle text-warning me-2"></i>Belum Absen Datang</a></li>
                                    <li><a class="dropdown-item py-2 gmp-filter-detail-item" href="javascript:void(0)" data-filter="belum_semua"><i class="bi bi-person-x text-danger me-2"></i>Belum Absen Sama Sekali</a></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Multi-Select Bulk Action Controls -->
                        <div id="gmp-bulk-toolbar" class="d-flex align-items-center gap-2 flex-wrap" style="display: none !important;">
                            <span class="badge bg-primary px-3 py-2 fs-6 fw-normal rounded-pill shadow-sm text-white">
                                <i class="bi bi-check-square me-1"></i><span id="gmp-selected-count" class="fw-bold">0</span> pegawai dipilih
                            </span>
                            <button type="button" class="btn btn-warning btn-sm fw-bold d-inline-flex align-items-center gap-1 shadow-sm text-dark" onclick="window.openGmpBulkModal()" style="border-radius: 6px;">
                                <i class="bi bi-pencil-square"></i> Ubah Presensi Terpilih
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.clearGmpSelectedRows()" title="Batal Pilih Semua" style="border-radius: 6px;">
                                <i class="bi bi-x-lg me-1"></i>Batal
                            </button>
                        </div>
                    </div>

                    <!-- Table Container -->
                    <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white p-3">
                        <div class="table-responsive">
                            <table id="gmp-table" class="table table-hover align-middle w-100" style="font-size: 0.86rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" style="width: 40px;">
                                            <div class="form-check d-flex justify-content-center m-0">
                                                <input type="checkbox" id="gmp-check-all-rows" class="form-check-input" style="cursor: pointer; width: 17px; height: 17px;" title="Pilih Semua Pegawai di Halaman Ini">
                                            </div>
                                        </th>
                                        <th class="text-center" style="width: 45px;">No</th>
                                        <th class="text-center" style="width: 110px;">Tanggal</th>
                                        <th>Pegawai</th>
                                        <th class="text-center" style="width: 140px;">Presensi Datang</th>
                                        <th class="text-center" style="width: 140px;">Presensi Pulang</th>
                                        <th>Keterangan / Bukti</th>
                                        <th class="text-center" style="width: 80px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white border-top py-2 px-4 justify-content-between">
                    <span class="text-muted small"><i class="bi bi-shield-check me-1"></i>Perubahan presensi massal tersimpan otomatis secara real-time.</span>
                    <button type="button" class="btn btn-secondary btn-sm px-4 rounded-pill fw-semibold" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Sub-Modal Bulk Update Presensi -->
    <div class="modal fade" id="modalGmpBulkPresensi" tabindex="-1" aria-labelledby="modalGmpBulkPresensiLabel" aria-hidden="true" style="z-index: 1075;">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
                <div class="modal-header bg-light border-bottom py-3 px-4">
                    <div>
                        <h5 class="modal-title fw-bold text-dark fs-6 mb-0" id="modalGmpBulkPresensiLabel">
                            <i class="bi bi-pencil-square text-primary me-2"></i>Ubah Presensi Terpilih (<span id="gmp-bulk-modal-count">0</span> Pegawai)
                        </h5>
                        <small class="text-muted">Perbarui status absensi datang dan absensi pulang pegawai secara massal.</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formGmpBulkPresensi">
                    <div class="modal-body p-4">
                        <!-- Selected Employees Preview -->
                        <div class="alert alert-light border mb-4 py-2 px-3 rounded-3 shadow-none">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="small fw-bold text-dark"><i class="bi bi-people-fill text-primary me-1"></i>Daftar Pegawai Terpilih:</span>
                                <span class="badge bg-primary-subtle text-primary rounded-pill"><span id="gmp-bulk-names-count">0</span> Pegawai</span>
                            </div>
                            <div id="gmp-bulk-names-preview" class="small text-muted" style="max-height: 55px; overflow-y: auto;">
                                -
                            </div>
                        </div>

                        <div class="row g-3">
                            <!-- Section Presensi Datang -->
                            <div class="col-md-6">
                                <div class="card h-100 border shadow-none" style="border-radius: 10px; background-color: #f8fafc;">
                                    <div class="card-header bg-white border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
                                        <div class="form-check form-switch m-0 d-flex align-items-center gap-2">
                                            <input class="form-check-input" type="checkbox" role="switch" id="gmp_switch_update_datang" name="update_datang" value="1" checked style="cursor: pointer; width: 34px; height: 17px;">
                                            <label class="form-check-label fw-bold text-dark small" for="gmp_switch_update_datang" style="cursor: pointer;">
                                                <i class="bi bi-box-arrow-in-right text-success me-1"></i>Ubah Absensi Datang
                                            </label>
                                        </div>
                                        <span class="badge bg-success-subtle text-success small" id="gmp-badge-datang-status">Diubah</span>
                                    </div>
                                    <div class="card-body p-3" id="gmp_wrapper_fields_datang">
                                        <div class="mb-3">
                                            <label for="gmp_bulk_hadir_datang" class="form-label fw-semibold small">Status Kehadiran Datang</label>
                                            <select class="form-select form-select-sm" id="gmp_bulk_hadir_datang" name="hadir_datang" style="border-radius: 6px;">
                                                <option value="Hadir" selected>🟢 Hadir</option>
                                                <option value="Izin">🟡 Izin</option>
                                                <option value="Sakit">🔵 Sakit</option>
                                                <option value="Alpha">🔴 Alpha</option>
                                                <option value="reset">⚪ Belum Absen (Kosongkan/Reset)</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label for="gmp_bulk_waktu_datang" class="form-label fw-semibold small">Waktu Datang (WIB)</label>
                                            <input type="time" class="form-control form-control-sm" id="gmp_bulk_waktu_datang" name="waktu_datang" style="border-radius: 6px;">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section Presensi Pulang -->
                            <div class="col-md-6">
                                <div class="card h-100 border shadow-none" style="border-radius: 10px; background-color: #f8fafc;">
                                    <div class="card-header bg-white border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
                                        <div class="form-check form-switch m-0 d-flex align-items-center gap-2">
                                            <input class="form-check-input" type="checkbox" role="switch" id="gmp_switch_update_pulang" name="update_pulang" value="1" checked style="cursor: pointer; width: 34px; height: 17px;">
                                            <label class="form-check-label fw-bold text-dark small" for="gmp_switch_update_pulang" style="cursor: pointer;">
                                                <i class="bi bi-box-arrow-right text-info me-1"></i>Ubah Absensi Pulang
                                            </label>
                                        </div>
                                        <span class="badge bg-success-subtle text-success small" id="gmp-badge-pulang-status">Diubah</span>
                                    </div>
                                    <div class="card-body p-3" id="gmp_wrapper_fields_pulang">
                                        <div class="mb-3">
                                            <label for="gmp_bulk_hadir_pulang" class="form-label fw-semibold small">Status Kehadiran Pulang</label>
                                            <select class="form-select form-select-sm" id="gmp_bulk_hadir_pulang" name="hadir_pulang" style="border-radius: 6px;">
                                                <option value="Hadir" selected>🟢 Hadir (Selesai Pulang)</option>
                                                <option value="Izin">🟡 Izin</option>
                                                <option value="Sakit">🔵 Sakit</option>
                                                <option value="Alpha">🔴 Alpha</option>
                                                <option value="reset">⚪ Belum Pulang (Kosongkan/Reset)</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label for="gmp_bulk_waktu_pulang" class="form-label fw-semibold small">Waktu Pulang (WIB)</label>
                                            <input type="time" class="form-control form-control-sm" id="gmp_bulk_waktu_pulang" name="waktu_pulang" style="border-radius: 6px;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Catatan Massal -->
                        <div class="mt-3">
                            <div class="form-check form-switch mb-2 d-flex align-items-center gap-2">
                                <input class="form-check-input" type="checkbox" role="switch" id="gmp_switch_update_catatan" name="update_catatan" value="1" style="cursor: pointer; width: 34px; height: 17px;">
                                <label class="form-check-label fw-semibold text-dark small" for="gmp_switch_update_catatan" style="cursor: pointer;">
                                    Tambahkan Catatan Khusus Massal
                                </label>
                            </div>
                            <div id="gmp_wrapper_field_catatan" style="display: none;">
                                <textarea class="form-control form-control-sm" id="gmp_bulk_catatan" name="catatan" rows="2" placeholder="Catatan massal (misal: Hadir tugas kepanitiaan, izin tugas dinas luar, dll)..." style="border-radius: 6px;"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top py-3 px-4">
                        <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal" style="border-radius: 6px;">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold d-inline-flex align-items-center gap-1 shadow-sm" id="gmp-btn-submit-bulk" style="border-radius: 6px;">
                            <i class="bi bi-check2-all me-1"></i> Terapkan Perubahan (<span id="gmp-bulk-btn-count">0</span> Pegawai)
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @stack('scripts')

</body>

</html>
