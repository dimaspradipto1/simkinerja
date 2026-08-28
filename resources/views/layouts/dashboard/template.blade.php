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
            .table-responsive table,
            table.dataTable {
                min-width: 750px !important;
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

        /* Mobile Edge-to-Edge Fullwidth Layout */
        @media (max-width: 767.98px) {
            #main.main {
                padding: 10px 0 !important;
            }
            .pagetitle {
                padding: 0 6px !important;
                margin-bottom: 10px !important;
            }
            .section {
                padding: 0 !important;
            }
            .card {
                border-radius: 0 !important;
                margin-left: 0 !important;
                margin-right: 0 !important;
                box-shadow: none !important;
                width: 100% !important;
            }
            .card-body {
                padding: 10px 6px !important;
            }
            .card-header-green {
                border-radius: 0 !important;
                padding: 12px 10px !important;
            }
            .absensi-mobile-banner {
                width: 100vw !important;
                max-width: 100vw !important;
                margin-left: calc(50% - 50vw) !important;
                margin-right: calc(50% - 50vw) !important;
                border-radius: 0 !important;
            }
            .absensi-mobile-banner .card-body {
                padding: 16px 12px !important;
            }
            .absensi-mobile-banner .btn {
                width: 100% !important;
                justify-content: center !important;
            }
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

    @stack('scripts')

</body>

</html>
