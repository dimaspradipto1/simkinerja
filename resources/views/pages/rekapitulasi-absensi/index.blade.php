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

    .form-select:focus {
        border-color: #198754;
        box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.15);
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
    .stat-label {
        font-size: 0.85rem !important;
        line-height: 1.25 !important;
        white-space: normal !important;
        word-break: normal !important;
        overflow: visible !important;
        text-overflow: clip !important;
        margin-bottom: 0.25rem;
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
@php
    $isAdmin = Auth::check() && (Auth::user()->isAdmin() || Auth::user()->isSuperAdmin() || Auth::user()->hasRole('REKTOR') || (trim(strtoupper(Auth::user()->jabatan ?? '')) === 'REKTOR'));
@endphp
<div class="pagetitle">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <h1 class="d-flex align-items-center gap-2 mb-1" style="font-weight: 700;">
                @if($isAdmin)
                    <i class="bi bi-clipboard-data-fill text-primary"></i> Rekapitulasi Presensi Kepanitiaan
                @else
                    <i class="bi bi-person-check-fill text-success"></i> Riwayat Presensi Kepanitiaan Saya
                @endif
            </h1>
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item">Presensi</li>
                    <li class="breadcrumb-item active">{{ $isAdmin ? 'Rekapitulasi' : 'Riwayat Presensi Saya' }}</li>
                </ol>
            </nav>
        </div>
    </div>
</div><!-- End Page Title -->

<section class="section mt-3">
    <!-- Filter Control Bar Card: Dropdown Form -->
    <div class="card filter-card mb-3 mb-md-4">
        <div class="card-body p-3 p-md-4">
            <div class="row g-3 align-items-end">
                <!-- Dropdown 1: Pilih Kepanitiaan -->
                <div class="col-12 col-md-6 col-lg-5">
                    <label for="select-kepanitiaan" class="form-label fw-bold text-secondary small text-uppercase mb-1" style="letter-spacing: 0.5px;">
                        <i class="bi bi-tag-fill text-primary me-1"></i> Pilih Kepanitiaan:
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-primary border-end-0">
                            <i class="bi bi-people-fill"></i>
                        </span>
                        <select class="form-select border-start-0 ps-1 fw-semibold shadow-none" id="select-kepanitiaan" style="border-color: #dee2e6; font-size: 0.9rem;">
                            @if($activeKepanitiaans->isNotEmpty())
                                <optgroup label="-- Sesi Aktif (Sedang Buka) --">
                                    @foreach($activeKepanitiaans as $k)
                                        @php
                                            $activeCount = $k->sesiAbsensis->where('is_active', true)->count();
                                        @endphp
                                        <option value="{{ $k->id }}" {{ $selectedKepanitiaanId == $k->id ? 'selected' : '' }}>
                                            {{ $k->uraian_tugas }} [{{ $activeCount }} Sesi Aktif]
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif

                            @php
                                $otherKepanitiaans = $allKepanitiaans->whereNotIn('id', $activeKepanitiaans->pluck('id'));
                            @endphp
                            @if($otherKepanitiaans->isNotEmpty())
                                <optgroup label="-- Kepanitiaan Lainnya --">
                                    @foreach($otherKepanitiaans as $k)
                                        <option value="{{ $k->id }}" {{ $selectedKepanitiaanId == $k->id ? 'selected' : '' }}>
                                            {{ $k->uraian_tugas }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif
                        </select>
                    </div>
                </div>

                <!-- Dropdown 2: Pilih Sesi / Hari Pelaksanaan -->
                <div class="col-12 col-md-6 col-lg-5">
                    <label for="select-sesi" class="form-label fw-bold text-secondary small text-uppercase mb-1" style="letter-spacing: 0.5px;">
                        <i class="bi bi-calendar3 text-success me-1"></i> Pilih Sesi / Hari Pelaksanaan:
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-success border-end-0">
                            <i class="bi bi-calendar2-check"></i>
                        </span>
                        <select class="form-select border-start-0 ps-1 fw-semibold shadow-none" id="select-sesi" style="border-color: #dee2e6; font-size: 0.9rem;">
                            @if($selectedKepanitiaan && $selectedKepanitiaan->sesiAbsensis->isNotEmpty())
                                @foreach($selectedKepanitiaan->sesiAbsensis as $s)
                                    <option value="{{ $s->id }}" {{ (!$isAllDays && $selectedSesiId == $s->id) ? 'selected' : '' }}>
                                        {{ $s->nama_sesi }} {{ $s->is_active ? ' [AKTIF / BUKA]' : '' }}
                                    </option>
                                @endforeach
                                @if($selectedKepanitiaan->sesiAbsensis->count() > 1)
                                    <option value="all" {{ $isAllDays ? 'selected' : '' }}>
                                        -- Rekap Semua Sesi --
                                    </option>
                                @endif
                            @else
                                <option value="">Tidak ada sesi absensi</option>
                            @endif
                        </select>
                    </div>
                </div>

                <!-- Status Badge -->
                <div class="col-12 col-lg-2 d-flex align-items-center justify-content-center justify-content-lg-start">
                    <div id="sesi-status-badge-container" class="w-100 text-center text-lg-start">
                        @if($isAllDays)
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-semibold" style="font-size: 0.8rem;">
                                <i class="bi bi-calendar-range me-1"></i> Rekap Semua Sesi
                            </span>
                        @elseif($selectedSesi && $selectedSesi->is_active)
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-semibold" style="font-size: 0.8rem;">
                                <i class="bi bi-broadcast me-1"></i> Sesi Aktif (Buka)
                            </span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary px-3 py-2 rounded-pill fw-semibold" style="font-size: 0.8rem;">
                                <i class="bi bi-lock-fill me-1"></i> Sesi Ditutup
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- STATISTIK RINGKASAN REKAPITULASI (KPI CARDS)                   -->
    <!-- ============================================================== -->
    <div class="row row-cols-2 row-cols-md-3 row-cols-xl-5 g-3 mb-4">
        <!-- Total Pegawai -->
        <div class="col">
            <div class="card stat-kpi-card h-100 p-2 p-md-3">
                <div class="d-flex align-items-center gap-2 gap-md-3">
                    <div class="stat-icon-wrapper bg-primary-subtle text-primary">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="text-muted stat-label fw-semibold">{{ $isAdmin ? 'Total Pegawai' : 'Data Pegawai' }}</div>
                        <h4 class="fw-bold mb-0 text-dark" id="stat-total-pegawai">0</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sudah Absen Datang -->
        <div class="col">
            <div class="card stat-kpi-card h-100 p-2 p-md-3 border-start border-success border-3">
                <div class="d-flex align-items-center gap-2 gap-md-3">
                    <div class="stat-icon-wrapper bg-success-subtle text-success">
                        <i class="bi bi-box-arrow-in-right"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="text-muted stat-label fw-semibold" id="label-sudah-datang">Sudah Datang</div>
                        <h4 class="fw-bold mb-0 text-success" id="stat-sudah-datang">0</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Belum Absen Datang -->
        <div class="col">
            <div class="card stat-kpi-card h-100 p-2 p-md-3 border-start border-danger border-3">
                <div class="d-flex align-items-center gap-2 gap-md-3">
                    <div class="stat-icon-wrapper bg-danger-subtle text-danger">
                        <i class="bi bi-x-circle-fill"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="text-muted stat-label fw-semibold" id="label-belum-datang">Belum Datang</div>
                        <h4 class="fw-bold mb-0 text-danger" id="stat-belum-datang">0</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sudah Absen Pulang -->
        <div class="col">
            <div class="card stat-kpi-card h-100 p-2 p-md-3 border-start border-info border-3">
                <div class="d-flex align-items-center gap-2 gap-md-3">
                    <div class="stat-icon-wrapper bg-info-subtle text-info">
                        <i class="bi bi-box-arrow-right"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="text-muted stat-label fw-semibold" id="label-sudah-pulang">Sudah Pulang</div>
                        <h4 class="fw-bold mb-0 text-info" id="stat-sudah-pulang">0</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Belum Absen Pulang -->
        <div class="col-12 col-md-4 col-xl">
            <div class="card stat-kpi-card h-100 p-2 p-md-3 border-start border-warning border-3">
                <div class="d-flex align-items-center gap-2 gap-md-3">
                    <div class="stat-icon-wrapper bg-warning-subtle text-warning">
                        <i class="bi bi-dash-circle-fill"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="text-muted stat-label fw-semibold" id="label-belum-pulang">Belum Pulang</div>
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
                        {{ $isAdmin ? 'Pegawai SUDAH Absensi' : 'Riwayat Presensi Hadir Anda' }}
                    </h5>
                    <p class="text-muted small m-0 d-none d-sm-block" id="desc-tabel-sudah">
                        {{ $isAdmin ? 'Pegawai yang telah mencatatkan kehadiran.' : 'Daftar sesi kepanitiaan yang telah Anda hadiri / tercatat kehadirannya.' }}
                    </p>
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
                        {{ $isAdmin ? 'Pegawai BELUM Absensi' : 'Sesi Belum Diikuti / Belum Hadir' }}
                    </h5>
                    <p class="text-muted small m-0 d-none d-sm-block" id="desc-tabel-belum">
                        {{ $isAdmin ? 'Pegawai yang belum melakukan presensi pada sesi ini.' : 'Status sesi kepanitiaan yang belum Anda hadiri / belum melakukan presensi.' }}
                    </p>
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
    const allKepanitiaansData = @json($allKepanitiaansJson);
    
    let currentKepanitiaanId = {{ $selectedKepanitiaanId ?? 'null' }};
    let currentSesiId = '{{ $selectedSesiId }}';

    function getKepanitiaanById(id) {
        return allKepanitiaansData.find(k => k.id == id) || allKepanitiaansData[0] || null;
    }

    // Populate opsi Sesi pada Dropdown kedua saat Kepanitiaan dipilih
    function populateSesiDropdown() {
        const k = getKepanitiaanById(currentKepanitiaanId);
        const select = $('#select-sesi');
        select.empty();

        if (!k || !k.sesis || k.sesis.length === 0) {
            select.append('<option value="">Tidak ada sesi absensi</option>');
            $('#sesi-status-badge-container').html('<span class="badge bg-secondary-subtle text-secondary border px-2.5 py-1.5 rounded-pill"><i class="bi bi-dash-circle me-1"></i>Tidak Ada Sesi</span>');
            return;
        }

        // Cek apakah currentSesiId masih valid di kepanitiaan ini
        const exists = (currentSesiId === 'all') || k.sesis.some(s => s.id == currentSesiId);
        if (!exists) {
            const activeSess = k.sesis.find(s => s.is_active);
            currentSesiId = activeSess ? activeSess.id : k.sesis[0].id;
        }

        k.sesis.forEach(s => {
            const activeLabel = s.is_active ? ' [AKTIF / BUKA]' : '';
            const isSelected = (currentSesiId !== 'all' && s.id == currentSesiId);
            const opt = $(`<option value="${s.id}" ${isSelected ? 'selected' : ''}>${s.name}${activeLabel}</option>`);
            select.append(opt);
        });

        if (k.sesis.length > 1) {
            const isAllSelected = (currentSesiId === 'all');
            const allOpt = $(`<option value="all" ${isAllSelected ? 'selected' : ''}>-- Rekap Semua Sesi --</option>`);
            select.append(allOpt);
        }

        updateStatusBadge(k);
    }

    function updateStatusBadge(k) {
        if (currentSesiId === 'all') {
            $('#sesi-status-badge-container').html(`
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-semibold" style="font-size: 0.8rem;">
                    <i class="bi bi-calendar-range me-1"></i> Rekap Semua Sesi
                </span>
            `);
        } else {
            const s = k?.sesis?.find(item => item.id == currentSesiId);
            if (s && s.is_active) {
                $('#sesi-status-badge-container').html(`
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-semibold" style="font-size: 0.8rem;">
                        <i class="bi bi-broadcast me-1"></i> Sesi Aktif (Buka)
                    </span>
                `);
            } else {
                $('#sesi-status-badge-container').html(`
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary px-3 py-2 rounded-pill fw-semibold" style="font-size: 0.8rem;">
                        <i class="bi bi-lock-fill me-1"></i> Sesi Ditutup
                    </span>
                `);
            }
        }
    }

    function updateHeaders(sesiTitle, isAll) {
        if (isAll) {
            $('#th-absen-datang').text('Datang (Semua Sesi)');
            $('#th-absen-pulang').text('Pulang (Semua Sesi)');
            $('#desc-tabel-sudah').html('Rangkuman presensi di seluruh sesi kepanitiaan ini.');
            $('#desc-tabel-belum').html('Pegawai yang belum absen sama sekali di seluruh sesi kepanitiaan ini.');
            $('#label-sudah-datang').text('Presensi Datang');
            $('#label-belum-datang').text('Belum Datang');
            $('#label-sudah-pulang').text('Presensi Pulang');
            $('#label-belum-pulang').text('Belum Pulang');
        } else {
            $('#th-absen-datang').text('Absen Datang');
            $('#th-absen-pulang').text('Absen Pulang');
            $('#desc-tabel-sudah').html('Pegawai yang telah hadir pada sesi: <strong class="text-dark">' + sesiTitle + '</strong>');
            $('#desc-tabel-belum').html('Pegawai yang belum hadir pada sesi: <strong class="text-dark">' + sesiTitle + '</strong>');
            $('#label-sudah-datang').text('Sudah Datang');
            $('#label-belum-datang').text('Belum Datang');
            $('#label-sudah-pulang').text('Sudah Pulang');
            $('#label-belum-pulang').text('Belum Pulang');
        }
    }

    function loadStats() {
        $.ajax({
            url: "{{ route('rekapitulasi-absensi.stats') }}",
            type: "GET",
            data: {
                kepanitiaan_id: currentKepanitiaanId,
                sesi_id: currentSesiId
            },
            success: function(res) {
                $('#stat-total-pegawai').text(res.total_pegawai || 0);
                $('#stat-sudah-datang').text(res.sudah_datang || 0);
                $('#stat-belum-datang').text(res.belum_datang || 0);
                $('#stat-sudah-pulang').text(res.sudah_pulang || 0);
                $('#stat-belum-pulang').text(res.belum_pulang || 0);
                
                $('#badge-total-sudah').html('<i class="bi bi-check-circle-fill me-1"></i> ' + (res.sudah_absen || 0) + ' Sudah Absen');
                $('#badge-total-belum').html('<i class="bi bi-x-circle-fill me-1"></i> ' + (res.belum_absen || 0) + ' Belum Absen');

                const title = res.sesi_nama || (currentSesiId === 'all' ? 'Semua Sesi' : 'Sesi Terpilih');
                updateHeaders(title, res.is_all_days);
            }
        });
    }

    // 1. Inisialisasi DataTable SUDAH Absen
    let tableSudah = $('#table-sudah-absen').DataTable({
        processing: true,
        serverSide: true,
        responsive: false,
        scrollX: true,
        ajax: {
            url: "{{ route('rekapitulasi-absensi.data-sudah') }}",
            data: function(d) {
                d.kepanitiaan_id = currentKepanitiaanId;
                d.sesi_id = currentSesiId;
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
            zeroRecords: "Tidak ada data hadir",
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
                d.kepanitiaan_id = currentKepanitiaanId;
                d.sesi_id = currentSesiId;
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

    // Event: Ganti Kepanitiaan via Dropdown
    $('#select-kepanitiaan').on('change', function() {
        currentKepanitiaanId = $(this).val();
        const k = getKepanitiaanById(currentKepanitiaanId);
        if (k && k.sesis && k.sesis.length > 0) {
            const activeSess = k.sesis.find(s => s.is_active);
            currentSesiId = activeSess ? activeSess.id : k.sesis[0].id;
        } else {
            currentSesiId = 'all';
        }
        populateSesiDropdown();
        loadStats();
        tableSudah.ajax.reload();
        tableBelum.ajax.reload();
    });

    // Event: Ganti Sesi via Dropdown
    $('#select-sesi').on('change', function() {
        currentSesiId = $(this).val();
        const k = getKepanitiaanById(currentKepanitiaanId);
        updateStatusBadge(k);
        loadStats();
        tableSudah.ajax.reload();
        tableBelum.ajax.reload();
    });

    // Inisialisasi awal
    loadStats();
});
</script>
@endpush
