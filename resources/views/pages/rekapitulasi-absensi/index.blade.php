@extends('layouts.dashboard.template')

@section('title', 'Rekapitulasi Absensi Kepanitiaan')

@push('css')
<style>
    /* Filter Bar Styles */
    .filter-card {
        background: #ffffff;
        border-radius: 1rem;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
        border: 1px solid #f1f5f9;
    }

    .kepanitiaan-btn, .day-btn {
        transition: all 0.2s ease-in-out;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        padding: 0.45rem 1rem !important;
        font-size: 0.85rem !important;
        margin-bottom: 0.25rem;
    }
    .kepanitiaan-btn:hover, .day-btn:hover {
        transform: translateY(-1px);
    }

    /* Stat Cards */
    .stat-kpi-card {
        border-radius: 1rem;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        padding: 1rem 1.15rem !important;
    }
    .stat-kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.07);
    }
    .stat-icon-wrapper {
        width: 44px;
        height: 44px;
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }

    /* Table Custom Styles */
    .table-card {
        border-radius: 1rem;
        border: 1px solid #f1f5f9;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }
    .table-rekap thead th {
        background-color: #f8fafc !important;
        color: #475569 !important;
        font-size: 0.82rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e2e8f0 !important;
        padding: 0.75rem 0.85rem !important;
        white-space: nowrap;
    }
    .table-rekap tbody td {
        padding: 0.75rem 0.85rem !important;
        vertical-align: middle;
        font-size: 0.88rem;
    }

    /* Mobile Specific Optimizations */
    @media (max-width: 767.98px) {
        .pagetitle h1 {
            font-size: 1.25rem !important;
        }
        .filter-card .card-body {
            padding: 1.15rem !important;
        }
        .kepanitiaan-btn, .day-btn {
            font-size: 0.82rem !important;
            padding: 0.45rem 0.75rem !important;
            flex-grow: 1;
            text-align: center;
        }
        .stat-kpi-card {
            padding: 0.95rem 0.85rem !important;
            border-radius: 0.85rem !important;
        }
        .stat-icon-wrapper {
            width: 38px !important;
            height: 38px !important;
            font-size: 1.1rem !important;
        }
        .stat-kpi-card h4 {
            font-size: 1.25rem !important;
        }
        .stat-kpi-card .stat-label {
            font-size: 0.75rem !important;
            margin-bottom: 0.15rem;
        }
        .table-card .card-header {
            padding: 0.85rem 1rem !important;
        }
        .table-card .card-body {
            padding: 0.75rem !important;
        }
        .dataTables_wrapper .dataTables_filter {
            margin-top: 0.5rem;
            text-align: left !important;
        }
        .dataTables_wrapper .dataTables_filter input {
            width: 100% !important;
            margin-left: 0 !important;
            margin-top: 0.25rem;
        }
    }
</style>
@endpush

@section('content')
<div class="pagetitle">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <h1 class="d-flex align-items-center gap-2 mb-1" style="font-weight: 700;">
                <i class="bi bi-clipboard-data-fill text-primary"></i> Rekapitulasi Presensi Kepanitiaan
            </h1>
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item">Absensi</li>
                    <li class="breadcrumb-item active">Rekapitulasi</li>
                </ol>
            </nav>
        </div>
    </div>
</div><!-- End Page Title -->

<section class="section mt-3">
    <!-- Filter Control Bar Card -->
    <div class="card filter-card mb-3 mb-md-4">
        <div class="card-body p-3 p-md-4">
            <div class="row g-3 g-lg-4 align-items-center justify-content-between">
                <!-- Kepanitiaan Selector -->
                <div class="col-12 col-xl-6">
                    <div class="text-secondary small fw-bold text-uppercase mb-2" style="letter-spacing: 0.5px;">
                        <i class="bi bi-tag-fill text-primary me-1"></i> Kepanitiaan:
                    </div>
                    <div class="row g-2" id="kepanitiaan-pills">
                        <div class="col-6 col-md-auto">
                            <button type="button" class="btn btn-sm w-100 {{ $kepanitiaan === 'pkkmb' ? 'btn-primary shadow-sm' : 'btn-outline-primary' }} rounded-pill fw-semibold kepanitiaan-btn" data-kepanitiaan="pkkmb">
                                <i class="bi bi-mortarboard me-1"></i> PKKMB
                            </button>
                        </div>
                        <div class="col-6 col-md-auto">
                            <button type="button" class="btn btn-sm w-100 {{ $kepanitiaan === 'esq' ? 'btn-primary shadow-sm' : 'btn-outline-primary' }} rounded-pill fw-semibold kepanitiaan-btn" data-kepanitiaan="esq">
                                <i class="bi bi-heart-pulse me-1"></i> ESQ
                            </button>
                        </div>
                        <div class="col-6 col-md-auto">
                            <button type="button" class="btn btn-sm w-100 {{ $kepanitiaan === 'milad' ? 'btn-primary shadow-sm' : 'btn-outline-primary' }} rounded-pill fw-semibold kepanitiaan-btn" data-kepanitiaan="milad">
                                <i class="bi bi-stars me-1"></i> MILAD
                            </button>
                        </div>
                        <div class="col-6 col-md-auto">
                            <button type="button" class="btn btn-sm w-100 {{ $kepanitiaan === 'kuliah_umum' ? 'btn-primary shadow-sm' : 'btn-outline-primary' }} rounded-pill fw-semibold kepanitiaan-btn" data-kepanitiaan="kuliah_umum">
                                <i class="bi bi-book me-1"></i> Kuliah Umum
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Day Selector -->
                <div class="col-12 col-xl-6">
                    <div class="text-secondary small fw-bold text-uppercase mb-2" style="letter-spacing: 0.5px;">
                        <i class="bi bi-calendar3 text-success me-1"></i> Hari Pelaksanaan:
                    </div>
                    <div class="row g-2" id="day-selector-container">
                        <div class="col-6 col-md-auto">
                            <button type="button" class="btn btn-sm w-100 {{ $day == 1 ? 'btn-success shadow-sm' : 'btn-outline-success' }} rounded-pill fw-semibold day-btn" data-day="1" id="btn-day-1">
                                <i class="bi bi-calendar-event me-1"></i> Hari Ke-1 (H-1)
                            </button>
                        </div>
                        <div class="col-6 col-md-auto">
                            <button type="button" class="btn btn-sm w-100 {{ $day == 2 ? 'btn-success shadow-sm' : 'btn-outline-success' }} rounded-pill fw-semibold day-btn" data-day="2" id="btn-day-2">
                                <i class="bi bi-calendar-event me-1"></i> Hari Ke-2 (H-2)
                            </button>
                        </div>
                        <div class="col-6 col-md-auto">
                            <button type="button" class="btn btn-sm w-100 {{ $day == 3 ? 'btn-success shadow-sm' : 'btn-outline-success' }} rounded-pill fw-semibold day-btn" data-day="3" id="btn-day-3">
                                <i class="bi bi-calendar-event me-1"></i> Hari Ke-3 (H-3)
                            </button>
                        </div>
                        <div class="col-6 col-md-auto">
                            <button type="button" class="btn btn-sm w-100 {{ $day == 0 ? 'btn-success shadow-sm' : 'btn-outline-success' }} rounded-pill fw-semibold day-btn" data-day="0" id="btn-day-all">
                                <i class="bi bi-calendar-week-fill me-1"></i> Rekap 3 Hari
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- STATISTIK RINGKASAN REKAPITULASI (KPI CARDS)                   -->
    <!-- ============================================================== -->
    <div class="row g-3 g-md-4 mb-4">
        <!-- Total Pegawai -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card stat-kpi-card h-100 p-2 p-md-3">
                <div class="d-flex align-items-center gap-2 gap-md-3">
                    <div class="stat-icon-wrapper bg-primary-subtle text-primary">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div class="overflow-hidden">
                        <div class="text-muted stat-label text-truncate fw-semibold">Total Pegawai</div>
                        <h4 class="fw-bold mb-0 text-dark" id="stat-total-pegawai">0</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sudah Absen Datang -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card stat-kpi-card h-100 p-2 p-md-3 border-start border-success border-3">
                <div class="d-flex align-items-center gap-2 gap-md-3">
                    <div class="stat-icon-wrapper bg-success-subtle text-success">
                        <i class="bi bi-box-arrow-in-right"></i>
                    </div>
                    <div class="overflow-hidden">
                        <div class="text-muted stat-label text-truncate fw-semibold" id="label-sudah-datang">Sudah Datang</div>
                        <h4 class="fw-bold mb-0 text-success" id="stat-sudah-datang">0</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Belum Absen Datang -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card stat-kpi-card h-100 p-2 p-md-3 border-start border-danger border-3">
                <div class="d-flex align-items-center gap-2 gap-md-3">
                    <div class="stat-icon-wrapper bg-danger-subtle text-danger">
                        <i class="bi bi-x-circle-fill"></i>
                    </div>
                    <div class="overflow-hidden">
                        <div class="text-muted stat-label text-truncate fw-semibold" id="label-belum-datang">Belum Datang</div>
                        <h4 class="fw-bold mb-0 text-danger" id="stat-belum-datang">0</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sudah Absen Pulang -->
        <div class="col-6 col-md-6 col-xl-3">
            <div class="card stat-kpi-card h-100 p-2 p-md-3 border-start border-info border-3">
                <div class="d-flex align-items-center gap-2 gap-md-3">
                    <div class="stat-icon-wrapper bg-info-subtle text-info">
                        <i class="bi bi-box-arrow-right"></i>
                    </div>
                    <div class="overflow-hidden">
                        <div class="text-muted stat-label text-truncate fw-semibold" id="label-sudah-pulang">Sudah Pulang</div>
                        <h4 class="fw-bold mb-0 text-info" id="stat-sudah-pulang">0</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Belum Absen Pulang -->
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card stat-kpi-card h-100 p-2 p-md-3 border-start border-warning border-3">
                <div class="d-flex align-items-center gap-2 gap-md-3">
                    <div class="stat-icon-wrapper bg-warning-subtle text-warning">
                        <i class="bi bi-dash-circle-fill"></i>
                    </div>
                    <div class="overflow-hidden">
                        <div class="text-muted stat-label text-truncate fw-semibold" id="label-belum-pulang">Belum Pulang</div>
                        <h4 class="fw-bold mb-0 text-warning" id="stat-belum-pulang">0</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- TABEL 1 (ATAS): USER YANG SUDAH ABSENSI                        -->
    <!-- ============================================================== -->
    <div class="card table-card mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2 gap-md-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-success text-white shadow-xs" style="width: 36px; height: 36px; flex-shrink: 0;">
                    <i class="bi bi-check2-circle fs-5"></i>
                </div>
                <div>
                    <h5 class="card-title m-0 p-0 fs-6 fw-bold text-success">
                        Pegawai SUDAH Absensi
                    </h5>
                    <p class="text-muted small m-0 d-none d-sm-block" id="desc-tabel-sudah">Pegawai yang telah mencatatkan kehadiran.</p>
                </div>
            </div>
            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 px-md-3 py-2 rounded-pill fw-bold" id="badge-total-sudah" style="font-size: 0.82rem;">
                <i class="bi bi-people-fill me-1"></i> Memuat...
            </span>
        </div>
        <div class="card-body p-2 p-md-4">
            <div class="table-responsive">
                <table class="table table-hover table-rekap align-middle w-100" id="table-sudah-absen">
                    <thead>
                        <tr>
                            <th style="width: 40px;">No</th>
                            <th>Nama Pegawai</th>
                            <th>Unit & Jabatan</th>
                            <th style="min-width: 140px;" id="th-absen-datang">Absen Datang</th>
                            <th style="min-width: 140px;" id="th-absen-pulang">Absen Pulang</th>
                            <th style="width: 100px;">Bukti Izin</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- TABEL 2 (BAWAH): USER YANG BELUM ABSENSI                       -->
    <!-- ============================================================== -->
    <div class="card table-card mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2 gap-md-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-danger text-white shadow-xs" style="width: 36px; height: 36px; flex-shrink: 0;">
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                </div>
                <div>
                    <h5 class="card-title m-0 p-0 fs-6 fw-bold text-danger">
                        Pegawai BELUM Absensi
                    </h5>
                    <p class="text-muted small m-0 d-none d-sm-block" id="desc-tabel-belum">Pegawai yang belum melakukan presensi pada sesi ini.</p>
                </div>
            </div>
            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 px-md-3 py-2 rounded-pill fw-bold" id="badge-total-belum" style="font-size: 0.82rem;">
                <i class="bi bi-person-x-fill me-1"></i> Memuat...
            </span>
        </div>
        <div class="card-body p-2 p-md-4">
            <div class="table-responsive">
                <table class="table table-hover table-rekap align-middle w-100" id="table-belum-absen">
                    <thead>
                        <tr>
                            <th style="width: 40px;">No</th>
                            <th>Nama Pegawai</th>
                            <th>NUP / NIDN</th>
                            <th>Unit Kerja</th>
                            <th>Jabatan</th>
                            <th style="width: 130px;" id="th-status-datang">Status Datang</th>
                            <th style="width: 130px;" id="th-status-pulang">Status Pulang</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let currentKepanitiaan = '{{ $kepanitiaan }}';
    let currentDay = {{ $day }};

    function updateHeaders() {
        if (currentDay === 0) {
            $('#th-absen-datang').text('Datang (3 Hari)');
            $('#th-absen-pulang').text('Pulang (3 Hari)');
            $('#desc-tabel-sudah').text('Rangkuman presensi pegawai di seluruh 3 hari pelaksanaan.');
            $('#desc-tabel-belum').text('Pegawai yang belum absen sama sekali pada seluruh 3 hari.');
            $('#label-sudah-datang').text('Presensi Datang');
            $('#label-belum-datang').text('Belum Datang');
            $('#label-sudah-pulang').text('Presensi Pulang');
            $('#label-belum-pulang').text('Belum Pulang');
        } else {
            $('#th-absen-datang').text('Datang (H-' + currentDay + ')');
            $('#th-absen-pulang').text('Pulang (H-' + currentDay + ')');
            $('#desc-tabel-sudah').text('Pegawai yang telah hadir di Hari Ke-' + currentDay + '.');
            $('#desc-tabel-belum').text('Pegawai yang belum hadir di Hari Ke-' + currentDay + '.');
            $('#label-sudah-datang').text('Sudah Datang');
            $('#label-belum-datang').text('Belum Datang');
            $('#label-sudah-pulang').text('Sudah Pulang');
            $('#label-belum-pulang').text('Belum Pulang');
        }
    }

    function loadStats() {
        updateHeaders();
        $.ajax({
            url: "{{ route('rekapitulasi-absensi.stats') }}",
            type: "GET",
            data: {
                kepanitiaan: currentKepanitiaan,
                day: currentDay
            },
            success: function(res) {
                $('#stat-total-pegawai').text(res.total_pegawai || 0);
                $('#stat-sudah-datang').text(res.sudah_datang || 0);
                $('#stat-belum-datang').text(res.belum_datang || 0);
                $('#stat-sudah-pulang').text(res.sudah_pulang || 0);
                $('#stat-belum-pulang').text(res.belum_pulang || 0);
                
                $('#badge-total-sudah').html('<i class="bi bi-check-circle-fill me-1"></i> ' + (res.sudah_absen || 0) + ' Sudah Absen');
                $('#badge-total-belum').html('<i class="bi bi-x-circle-fill me-1"></i> ' + (res.belum_absen || 0) + ' Belum Absen');
            }
        });
    }

    // Inisialisasi awal statistik
    loadStats();

    // 1. Inisialisasi DataTable SUDAH Absen
    let tableSudah = $('#table-sudah-absen').DataTable({
        processing: true,
        serverSide: true,
        responsive: false,
        scrollX: true,
        ajax: {
            url: "{{ route('rekapitulasi-absensi.data-sudah') }}",
            data: function(d) {
                d.kepanitiaan = currentKepanitiaan;
                d.day = currentDay;
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
            { data: 'name', name: 'name' },
            { data: 'unit_jabatan', name: 'unit_jabatan' },
            { data: 'absen_datang', name: 'absen_datang', orderable: false, searchable: false },
            { data: 'absen_pulang', name: 'absen_pulang', orderable: false, searchable: false },
            { data: 'bukti_izin', name: 'bukti_izin', orderable: false, searchable: false, className: 'text-center' },
        ],
        language: {
            processing: '<div class="spinner-border text-primary spinner-border-sm" role="status"></div> Memuat data...',
            search: "Cari:",
            lengthMenu: "_MENU_",
            info: "_START_ - _END_ dari _TOTAL_",
            infoEmpty: "0 pegawai",
            zeroRecords: "Tidak ada data",
            paginate: {
                first: "«",
                last: "»",
                next: "›",
                previous: "‹"
            }
        }
    });

    // 2. Inisialisasi DataTable BELUM Absen
    let tableBelum = $('#table-belum-absen').DataTable({
        processing: true,
        serverSide: true,
        responsive: false,
        scrollX: true,
        ajax: {
            url: "{{ route('rekapitulasi-absensi.data-belum') }}",
            data: function(d) {
                d.kepanitiaan = currentKepanitiaan;
                d.day = currentDay;
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
            { data: 'name', name: 'name' },
            { data: 'nidn', name: 'nidn' },
            { data: 'unit', name: 'unit' },
            { data: 'jabatan', name: 'jabatan' },
            { data: 'status_datang', name: 'status_datang', orderable: false, searchable: false },
            { data: 'status_pulang', name: 'status_pulang', orderable: false, searchable: false },
        ],
        language: {
            processing: '<div class="spinner-border text-danger spinner-border-sm" role="status"></div> Memuat data...',
            search: "Cari:",
            lengthMenu: "_MENU_",
            info: "_START_ - _END_ dari _TOTAL_",
            infoEmpty: "Semua sudah absen",
            zeroRecords: "Semua sudah absen",
            paginate: {
                first: "«",
                last: "»",
                next: "›",
                previous: "‹"
            }
        }
    });

    // Event Switch Kepanitiaan (Bootstrap button toggle)
    $('.kepanitiaan-btn').on('click', function() {
        $('.kepanitiaan-btn').removeClass('btn-primary shadow-sm').addClass('btn-outline-primary');
        $(this).removeClass('btn-outline-primary').addClass('btn-primary shadow-sm');
        currentKepanitiaan = $(this).data('kepanitiaan');
        loadStats();
        tableSudah.ajax.reload();
        tableBelum.ajax.reload();
    });

    // Event Switch Hari (Bootstrap button toggle)
    $('.day-btn').on('click', function() {
        $('.day-btn').removeClass('btn-success shadow-sm').addClass('btn-outline-success');
        $(this).removeClass('btn-outline-success').addClass('btn-success shadow-sm');
        currentDay = parseInt($(this).data('day'));
        loadStats();
        tableSudah.ajax.reload();
        tableBelum.ajax.reload();
    });
});
</script>
@endpush
