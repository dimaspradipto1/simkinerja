@extends('layouts.dashboard.template')

@section('title', 'Analisis Kendala & Keterlambatan Kinerja')

@section('content')
<div class="pagetitle">
    <h1><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>Analisis Kendala & Keterlambatan Kinerja</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item">Rekapitulasi & Laporan</li>
            <li class="breadcrumb-item active">Analisis Keterlambatan</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    <!-- Filter Card -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body py-3">
            @if(!$isStaffOnly)
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom gap-2">
                <ul class="nav nav-pills nav-fill gap-2 flex-grow-1" id="filterTab" role="tablist" style="max-width: 600px;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active py-1 px-3 small fw-bold" id="mode-hirarki-tab" data-bs-toggle="pill" data-bs-target="#mode-hirarki" type="button" role="tab">
                            <i class="bi bi-diagram-3-fill me-1"></i> Mode 1: Hirarki Bertingkat
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link py-1 px-3 small fw-bold text-secondary" id="mode-cepat-tab" data-bs-toggle="pill" data-bs-target="#mode-cepat" type="button" role="tab">
                            <i class="bi bi-funnel-fill me-1"></i> Mode 2: Filter Unit & Level
                        </button>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary shadow-sm rounded-pill px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalTreeHirarki">
                        <i class="bi bi-diagram-3-fill me-1 text-primary"></i> 🌳 Pohon Organisasi UIS
                    </button>
                    <span id="active_hirarki_badge" class="badge bg-success-subtle text-success border border-success-subtle py-2 px-3 d-none align-items-center">
                        <i class="bi bi-funnel-fill me-1"></i><span id="active_hirarki_text">Filter Hirarki Aktif</span>
                        <button type="button" class="btn-close ms-2" id="btn-clear-hirarki" style="font-size: 0.6rem;" title="Reset Filter"></button>
                    </span>
                </div>
            </div>
            @endif

            <form id="filterForm">
                <div class="tab-content" id="filterTabContent">
                    <!-- Tab Mode 1: Hirarki Bertingkat -->
                    <div class="tab-pane fade show active" id="mode-hirarki" role="tabpanel">
                        <div class="row g-3 align-items-center mb-3">
                            <div class="col-md-3">
                                <label for="filter_periode" class="form-label small fw-bold text-muted mb-1"><i class="bi bi-calendar3 me-1"></i> Periode Akademik</label>
                                <select id="filter_periode" name="periode_akademik_id" class="form-select form-select-sm">
                                    <option value="">-- Semua Periode --</option>
                                    @foreach($periodeAkademiks as $periode)
                                        <option value="{{ $periode->id }}" {{ $defaultPeriodeId == $periode->id ? 'selected' : '' }}>
                                            {{ $periode->nama_periode }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            @if(!$isStaffOnly)
                            <div class="col-md-3">
                                <label for="filter_tingkat_1" class="form-label small fw-bold text-primary mb-1">
                                    <i class="bi bi-diagram-3-fill me-1"></i> Tingkat 1 (Struktur Utama)
                                </label>
                                <select id="filter_tingkat_1" name="tingkat_1" class="form-select form-select-sm border-primary-subtle bg-primary-subtle text-primary fw-semibold">
                                    <option value="">-- Semua Struktur Utama --</option>
                                    <option value="Rektor">1. Rektor (UIS)</option>
                                    <option value="Wakil Rektor I">2. Wakil Rektor I</option>
                                    <option value="Wakil Rektor II">3. Wakil Rektor II</option>
                                    <option value="Wakil Rektor III">4. Wakil Rektor III</option>
                                    <option value="LPPM">5. LPPM</option>
                                    <option value="LPMI">6. LPMI</option>
                                    <option value="Fakultas Ekonomi dan Bisnis">7. Fakultas Ekonomi dan Bisnis</option>
                                    <option value="Fakultas Sains dan Teknologi">8. Fakultas Sains dan Teknologi</option>
                                    <option value="Fakultas Ilmu Kesehatan">9. Fakultas Ilmu Kesehatan</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="filter_tingkat_2" class="form-label small fw-bold text-info mb-1">
                                    <i class="bi bi-diagram-2-fill me-1"></i> Tingkat 2 (Biro / Sub-Unit)
                                </label>
                                <select id="filter_tingkat_2" name="tingkat_2" class="form-select form-select-sm border-info-subtle bg-info-subtle text-info-emphasis fw-semibold" disabled>
                                    <option value="">-- Pilih Tingkat 1 Dulu --</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="filter_tingkat_3" class="form-label small fw-bold text-success mb-1">
                                    <i class="bi bi-person-badge-fill me-1"></i> Tingkat 3 (Jabatan / Staf)
                                </label>
                                <select id="filter_tingkat_3" name="tingkat_3" class="form-select form-select-sm border-success-subtle bg-success-subtle text-success-emphasis fw-semibold" disabled>
                                    <option value="">-- Pilih Tingkat 2 Dulu --</option>
                                </select>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Tab Mode 2: Filter Unit & Level -->
                    <div class="tab-pane fade" id="mode-cepat" role="tabpanel">
                        <div class="row g-3 align-items-center mb-3">
                            @if(!$isStaffOnly)
                            <div class="col-md-6">
                                <label for="filter_level" class="form-label small fw-bold text-muted mb-1"><i class="bi bi-layers me-1"></i> Level Kepemimpinan</label>
                                <select id="filter_level" name="leadership_level" class="form-select form-select-sm">
                                    <option value="">-- Semua Level --</option>
                                    <option value="1">Level 1: Rektorat</option>
                                    <option value="2">Level 2: Dekanat & Ka. Unit</option>
                                    <option value="3">Level 3: Kaprodi & Kabid</option>
                                    <option value="4">Level 4: Staf Pelaksana</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="filter_unit" class="form-label small fw-bold text-muted mb-1"><i class="bi bi-building me-1"></i> Unit / Fakultas</label>
                                <select id="filter_unit" name="unit" class="form-select form-select-sm">
                                    <option value="">-- Semua Unit & Fakultas --</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit }}">{{ $unit }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Shared Row: Pegawai Spesifik & Kategori Kendala -->
                <div class="row g-3 align-items-center border-top pt-3 mt-1">
                {{-- Filter pegawai: tampil untuk Kepala/Pimpinan, tersembunyi untuk staf biasa --}}
                    @if(!$isStaffOnly)
                    <div class="col-md-8">
                        <label for="filter_user" class="form-label small fw-bold text-muted mb-1"><i class="bi bi-person me-1"></i> Cari / Pilih Pegawai Spesifik</label>
                        <select id="filter_user" name="user_id" class="form-select form-select-sm">
                            <option value="">-- Semua Pegawai --</option>
                            @foreach($groupedUsers as $levelTitle => $users)
                                @if($users->count() > 0)
                                    <optgroup label="{{ $levelTitle }}">
                                        @foreach($users as $u)
                                            <option value="{{ $u->id }}" data-level="{{ $u->level }}" data-unit="{{ $u->unit }}" data-jabatan="{{ $u->jabatan }}">
                                                {{ $u->name }} ({{ $u->jabatan }})
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    @else
                    <input type="hidden" id="filter_user" name="user_id" value="{{ auth()->user()->id }}">
                    @endif

                    <div class="{{ $isStaffOnly ? 'col-md-12' : 'col-md-4' }}">
                        <label for="filter_kendala" class="form-label small fw-bold text-muted mb-1"><i class="bi bi-funnel me-1"></i> Kategori Kendala</label>
                        <select id="filter_kendala" name="kategori_kendala" class="form-select form-select-sm">
                            <option value="semua">-- Semua Kendala --</option>
                            <option value="insidentil">🔴 Insidentil</option>
                            <option value="kepanitiaan">🟠 Kepanitiaan</option>
                            <option value="beban_ganda">🟡 Beban Ganda</option>
                            <option value="murni">⚪ Murni Staff</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Stat Cards Summary -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm border-start border-danger border-4 h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-danger bg-opacity-10 p-3 me-3 text-danger fs-3">
                            <i class="bi bi-exclamation-octagon-fill"></i>
                        </div>
                        <div>
                            <span class="text-muted small fw-bold text-uppercase d-block">Total Pekerjaan Terlambat</span>
                            <h3 id="stat-total" class="fw-bold mb-0 text-danger">0</h3>
                            <span class="small text-muted">Rencana Kerja Utama</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm border-start border-danger border-4 h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-danger bg-opacity-10 p-3 me-3 text-danger fs-3">
                            <i class="bi bi-lightning-charge-fill"></i>
                        </div>
                        <div>
                            <span class="text-muted small fw-bold text-uppercase d-block">Disebabkan Insidentil</span>
                            <h3 id="stat-insidentil" class="fw-bold mb-0 text-dark">0</h3>
                            <span id="stat-percent-ins" class="small badge bg-danger text-white">0% dari keterlambatan</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm border-start border-warning border-4 h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3 text-warning fs-3">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div>
                            <span class="text-muted small fw-bold text-uppercase d-block">Disebabkan Kepanitiaan</span>
                            <h3 id="stat-kepanitiaan" class="fw-bold mb-0 text-dark">0</h3>
                            <span id="stat-percent-pan" class="small badge bg-warning text-dark">0% dari keterlambatan</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm border-start border-secondary border-4 h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-secondary bg-opacity-10 p-3 me-3 text-secondary fs-3">
                            <i class="bi bi-person-x-fill"></i>
                        </div>
                        <div>
                            <span class="text-muted small fw-bold text-uppercase d-block">Keterlambatan Murni</span>
                            <h3 id="stat-murni" class="fw-bold mb-0 text-dark">0</h3>
                            <span id="stat-percent-murni" class="small badge bg-secondary text-white">0% dari keterlambatan</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-3 mb-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h5 class="card-title m-0 fw-bold text-dark fs-6"><i class="bi bi-pie-chart-fill me-2 text-danger"></i>Distribusi Penyebab Keterlambatan</h5>
                    <div class="btn-group btn-group-sm" role="group" id="chartTypeToggle">
                        <button type="button" class="btn btn-outline-danger btn-chart-type active py-1 px-2 small" data-type="doughnut" title="Grafik Donut">
                            <i class="bi bi-pie-chart-fill me-1"></i> Donut
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-chart-type py-1 px-2 small" data-type="radar" title="Grafik Spider / Radar">
                            <i class="bi bi-diagram-2-fill me-1"></i> Spider (Radar)
                        </button>
                    </div>
                </div>
                <div class="card-body p-3 d-flex align-items-center justify-content-center" style="min-height: 260px;">
                    <canvas id="chartPenyebab" style="max-height: 240px;"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h5 class="card-title m-0 fw-bold text-dark fs-6"><i class="bi bi-bar-chart-line-fill me-2 text-primary"></i>Ringkasan Kendala Beban Kerja Per Kategori</h5>
                </div>
                <div class="card-body p-3 d-flex align-items-center justify-content-center" style="min-height: 260px;">
                    <canvas id="chartBarKendala" style="max-height: 240px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Audit Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2 border-0">
            <h5 class="card-title m-0 fw-bold text-dark fs-6"><i class="bi bi-list-task me-2 text-danger"></i>Audit Detail Keterlambatan Pekerjaan & Interupsi Task</h5>
            <div class="d-flex gap-2">
                <button type="button" id="btn-export-excel" class="btn btn-sm btn-outline-success px-3"><i class="bi bi-file-earmark-excel me-1"></i> Export Excel</button>
                <button type="button" id="btn-export-pdf" class="btn btn-sm btn-outline-danger px-3"><i class="bi bi-file-earmark-pdf me-1"></i> Export PDF</button>
            </div>
        </div>
        <div class="card-body p-3">
            <div class="table-responsive">
                <table id="analisis-table" class="table table-hover align-middle w-100" style="font-size: 0.85rem;">
                    <thead class="table-light border-top">
                        <tr>
                            <th style="width: 4%;">NO</th>
                            <th style="width: 16%;">STAFF & JABATAN</th>
                            <th style="width: 22%;">TUGAS UTAMA (TERLAMBAT)</th>
                            <th style="width: 14%;">DIAGNOSTIK KENDALA</th>
                            <th style="width: 21%;">PEKERJAAN BENTROK (OVERLAP)</th>
                            <th style="width: 16%;">REKOMENDASI EVALUASI</th>
                            <th style="width: 7%; text-align:center;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<!-- Modal Edit Saran Pimpinan / Rektor -->
<div class="modal fade" id="modalSaranPimpinan" tabindex="-1" aria-labelledby="modalSaranPimpinanLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formSaranPimpinan">
                @csrf
                <input type="hidden" id="saran_task_id" name="task_id">
                <div class="modal-header bg-primary text-white py-2">
                    <h5 class="modal-title fs-6 fw-bold" id="modalSaranPimpinanLabel">
                        <i class="bi bi-pencil-square me-2"></i>Input / Edit Rekomendasi Evaluasi Pimpinan
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label text-muted small mb-0">Nama Staff:</label>
                        <div class="fw-bold text-dark" id="modal_staff_name">-</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-0">Uraian Tugas:</label>
                        <div class="small text-secondary" id="modal_task_name">-</div>
                    </div>
                    <div class="mb-3">
                        <label for="saran_pimpinan_input" class="form-label fw-bold small">Catatan & Rekomendasi Evaluasi Pimpinan:</label>
                        <textarea class="form-control" id="saran_pimpinan_input" name="saran_pimpinan" rows="4" placeholder="Tuliskan catatan evaluasi, instruksi, atau rekomendasi arahan pimpinan untuk staff ini..."></textarea>
                        <div class="form-text small text-muted">Kosongkan jika ingin mengembalikan ke rekomendasi saran otomatis sistem.</div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-light border btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm" id="btn-save-saran">
                        <i class="bi bi-save me-1"></i>Simpan Saran
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Tree Hirarki Organisasi UIS -->
<div class="modal fade" id="modalTreeHirarki" tabindex="-1" aria-labelledby="modalTreeHirarkiLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold fs-6" id="modalTreeHirarkiLabel">
                    <i class="bi bi-diagram-3-fill me-2"></i>Pohon Hirarki Jabatan & Organisasi Universitas Ibnu Sina
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 bg-light">
                <div class="alert alert-info py-2 px-3 small border-0 shadow-sm mb-3">
                    <i class="bi bi-cursor-fill me-1"></i> Klik <strong>"Filter Sub-Unit Ini"</strong> atau <strong>"Pilih Staf"</strong> untuk menyaring data keterlambatan secara interaktif.
                </div>

                @if(isset($hierarchyTree) && count($hierarchyTree) > 0)
                <div class="accordion shadow-sm rounded border-0" id="accordionHirarkiUIS">
                    @foreach($hierarchyTree as $divisiName => $divData)
                        @php $divId = 'div_' . Str::slug($divisiName); @endphp
                        <div class="accordion-item border-0 border-bottom">
                            <h2 class="accordion-header" id="heading_{{ $divId }}">
                                <button class="accordion-button collapsed py-2 px-3 fw-bold small text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_{{ $divId }}" aria-expanded="false" aria-controls="collapse_{{ $divId }}">
                                    <i class="{{ $divData['icon'] }} me-2 fs-5"></i> {{ $divisiName }}
                                </button>
                            </h2>
                            <div id="collapse_{{ $divId }}" class="accordion-collapse collapse" aria-labelledby="heading_{{ $divId }}" data-bs-parent="#accordionHirarkiUIS">
                                <div class="accordion-body p-2 bg-white">
                                    @foreach($divData['sub'] as $subUnitName => $usersList)
                                        <div class="mb-2 p-2 rounded bg-light border">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="fw-bold text-primary small"><i class="bi bi-folder-fill text-warning me-1"></i>{{ $subUnitName }}</span>
                                                <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2 btn-select-node" data-type="unit" data-value="{{ $subUnitName }}">
                                                    <i class="bi bi-funnel me-1"></i>Filter Sub-Unit Ini
                                                </button>
                                            </div>
                                            @if(count($usersList) > 0)
                                                <ul class="list-unstyled ps-3 mb-0 small">
                                                    @foreach($usersList as $usr)
                                                        <li class="py-1 border-bottom border-light d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <i class="bi bi-person-circle text-secondary me-1"></i>
                                                                <strong class="text-dark">{{ $usr['name'] }}</strong>
                                                                <span class="text-muted ms-1" style="font-size: 0.78rem;">({{ $usr['jabatan'] }})</span>
                                                            </div>
                                                            <button type="button" class="btn btn-xs btn-primary py-0 px-2 btn-select-node" data-type="user" data-value="{{ $usr['id'] }}" data-name="{{ $usr['name'] }} ({{ $usr['jabatan'] }})">
                                                                Pilih Staf
                                                            </button>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <div class="small text-muted fst-italic ps-2" style="font-size: 0.75rem;">Tidak ada staf terdaftar pada unit ini.</div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                @endif
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script>
$(document).ready(function() {
    let chartPenyebabInst = null;
    let chartBarKendalaInst = null;
    let currentChartType = 'doughnut';

    // Chart Type Switcher (Donut vs Spider/Radar)
    $('.btn-chart-type').on('click', function() {
        $('.btn-chart-type').removeClass('active btn-danger').addClass('btn-outline-danger');
        $(this).addClass('active btn-danger').removeClass('btn-outline-danger');
        currentChartType = $(this).data('type');
        let json = table.ajax.json();
        if (json && json.stats) {
            updateCharts(json.stats);
        }
    });

    // Initialize Select2 Searchable Dropdowns
    if ($.fn.select2) {
        @if(!$isStaffOnly)
        $('#filter_user').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: '-- Cari / Pilih Nama Pegawai --',
            allowClear: true
        });
        @endif

        $('#filter_tingkat_1, #filter_tingkat_2, #filter_tingkat_3, #filter_level, #filter_unit, #filter_periode, #filter_kendala').select2({
            theme: 'bootstrap-5',
            width: '100%',
            allowClear: true
        });
    }

    let table = $('#analisis-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('analisis-keterlambatan.data') }}",
            data: function(d) {
                d.periode_akademik_id = $('#filter_periode').val();
                d.tingkat_1 = $('#filter_tingkat_1').val();
                d.tingkat_2 = $('#filter_tingkat_2').val();
                d.tingkat_3 = $('#filter_tingkat_3').val();
                d.leadership_level = $('#filter_level').val();
                d.unit = $('#filter_unit').val();
                d.user_id = $('#filter_user').val();
                d.kategori_kendala = $('#filter_kendala').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'staff_info', name: 'staff_info' },
            { data: 'task_details', name: 'task_details' },
            { data: 'diagnostik_kendala', name: 'diagnostik_kendala' },
            { data: 'rincian_bentrokan', name: 'rincian_bentrokan' },
            { data: 'rekomendasi_evaluasi', name: 'rekomendasi_evaluasi' },
            { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-center' }
        ],
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            zeroRecords: "Tidak ada data keterlambatan pekerjaan yang ditemukan",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 data",
            paginate: { first: "Awal", last: "Akhir", next: "►", previous: "◄" }
        },
        drawCallback: function(settings) {
            let json = settings.json;
            if (json && json.stats) {
                let st = json.stats;
                $('#stat-total').text(st.total);
                $('#stat-insidentil').text(st.insidentil);
                $('#stat-kepanitiaan').text(st.kepanitiaan);
                $('#stat-murni').text(st.murni);

                $('#stat-percent-ins').text(st.percent_ins + '% dari keterlambatan');
                $('#stat-percent-pan').text(st.percent_pan + '% dari keterlambatan');
                $('#stat-percent-murni').text(st.percent_murni + '% dari keterlambatan');

                updateCharts(st);
            }
        }
    });

    // Cascading Dropdown Structure Mapping based on UIS Mind Maps
    const tingkatStructure = {
        "Rektor": {
            "Rektorat Utama": ["Rektor", "Wakil Rektor I", "Wakil Rektor II", "Wakil Rektor III"]
        },
        "Wakil Rektor I": {
            "Biro Administrasi Akademik Kemahasiswaan (BAAK)": ["Ka. Biro Administrasi Akademik Kemahasiswaan (BAAK)"],
            "Kabid Akademik": [
                "Staff Akademik Fakultas Ekonomi dan Bisnis",
                "Staff Akademik Fakultas Sains dan Teknologi",
                "Staff Akademik Fakultas Ilmu Kesehatan"
            ],
            "Kabid Administrasi dan Layanan Kemahasiswaan": [
                "Staff Support Layanan Nilai dan Perkuliahan",
                "Staff Layanan Ijazah"
            ],
            "Kepala Pustaka": ["Pustakawan"]
        },
        "Wakil Rektor II": {
            "Biro Administrasi Umum dan Keuangan": ["Ka. Biro Administrasi Umum dan Keuangan"],
            "Kabid Keuangan": [
                "Kasir Rektorat",
                "Staff Keuangan Fakultas Ekonomi dan Bisnis",
                "Staff Keuangan Fakultas Sains dan Teknologi",
                "Staff Keuangan Fakultas Ilmu Kesehatan"
            ],
            "Kabid SDM dan Umum": [
                "Operator SDM",
                "Staff Kepegawaian"
            ],
            "Kabid Sarana dan Prasarana": [
                "Admin Umum Sarpras",
                "Staff Sarpras"
            ],
            "Tata Usaha": ["Tata Usaha BAUK"],
            "Kepala LPTI": [
                "Staff Divisi Pengembangan Informasi dan Aplikasi",
                "Staf Divisi Infrastruktur dan Jaringan",
                "Staff Programmer",
                "Staff Layanan Troubleshooting"
            ]
        },
        "Wakil Rektor III": {
            "Biro Kemahasiswaan, Alumni, Kerjasama, Perencanaan dan Pengembangan": [
                "Ka Biro Kemahasiswaan, Alumni, Kerjasama, Perencanaan dan Pengembangan"
            ],
            "Kabid Humas dan Publikasi": [
                "Staff Dokumentasi",
                "Staff Humas",
                "Staff Website"
            ],
            "Kabid Kerjasama": [
                "Staff Kerjasama Internasional",
                "Staff Kerjasama Nasional"
            ],
            "Kabid Kemahasiswaan": [
                "Staff KIP",
                "Staff Prestasi Olah Raga",
                "Staff Prestasi Seni"
            ],
            "Kabid Pusat Karir, Alumni dan Kewirausahaan": [
                "Staff Pusat Karir, Alumni dan Kewirausahaan"
            ],
            "Kabid Perencanaan dan Pengembangan": [
                "Staff Perencanaan dan Pengembangan"
            ]
        },
        "LPPM": {
            "LPPM UIS": ["Ka LPPM", "Kabid Penelitian", "Kabid HAKI dan Publikasi"],
            "Kabid Pengabdian Kepada Masyarakat": [
                "Staff Administrasi Penelitian dan Pengabdian Kepada Masyarakat"
            ]
        },
        "LPMI": {
            "LPMI UIS": [
                "Ka LPMI",
                "Kabid Pengembangan SPMI dan SDM SPMI",
                "Kabid Sosialisasi SPMI dan Kerjasama SPMI",
                "Kabid Akreditasi dan Dokumentasi",
                "Kabid Evaluasi dan Audit Mutu"
            ]
        },
        "Fakultas Ekonomi dan Bisnis": {
            "Dekanat FEB": ["Dekan FEB", "Wakil Dekan I FEB", "Wakil Dekan II FEB"],
            "Prodi S1 Manajemen": ["Ketua Program Studi S1 Manajemen", "Sekretaris Prodi S1 Manajemen"],
            "Prodi S1 Akuntansi": ["Ketua Program Studi S1 Akuntansi", "Sekretaris Prodi S1 Akuntansi"],
            "Prodi Pascasarjana S2 Magister Manajemen": ["Ketua Program Studi Pascasarjana Magister Manajemen", "Sekretaris Prodi Pascasarjana Magister Manajemen"],
            "Support Unit FEB": ["UPMI FEB", "UPPM FEB", "Tata Usaha Sarjana FEB", "Tata Usaha Pascasarjana FEB", "Humas dan Publikasi Web FEB"]
        },
        "Fakultas Sains dan Teknologi": {
            "Dekanat FST": ["Dekan FST", "Wakil Dekan I FST", "Wakil Dekan II FST"],
            "Prodi Teknik Industri": ["Ketua Program Studi Teknik Industri", "Sekretaris Program Studi Teknik Industri"],
            "Prodi Teknik Informatika & Sistem Informasi": ["Ketua Program Studi Teknik Informatika dan Sistem Informasi", "Sekretaris Program Studi Teknik Informatika dan Sistem Informasi"],
            "Prodi Teknik Logistik & Perkapalan": ["Ketua Program Studi Teknik Logistik dan Perkapalan", "Sekretaris Program Studi Teknik Logistik dan Perkapalan"],
            "Laboratorium FST": ["Ka. Laboratorium FST", "Staff Labor Teknik Industri", "Staff Labor Teknik Komputer", "Staff Labor Teknik Proses Produksi"],
            "Support Unit FST": ["Tata Usaha FST", "UPMI FST", "Ka. UPPM FST", "Staff UPPM FST", "Ka. Humas FST", "Staff Humas FST"]
        },
        "Fakultas Ilmu Kesehatan": {
            "Dekanat FIKES": ["Dekan FIKES", "Wakil Dekan I FIKES", "Wakil Dekan II FIKES"],
            "Prodi K3": ["Ketua Program Studi K3", "Sekretaris Prodi K3"],
            "Prodi Kesling": ["Ketua Program Studi Kesling", "Sekretaris Prodi Kesling"],
            "Support Unit FIKES": ["UPMI FIKES", "GKM FIKES", "UPPM FIKES", "Laboran FIKES", "Tata Usaha FIKES", "Humas dan Publikasi Web FIKES"]
        }
    };

    // On Tingkat 1 Change
    $('#filter_tingkat_1').on('change', function() {
        let t1 = $(this).val();
        let t2Select = $('#filter_tingkat_2');
        let t3Select = $('#filter_tingkat_3');

        t2Select.empty().append('<option value="">-- Semua Tingkat 2 --</option>');
        t3Select.empty().append('<option value="">-- Pilih Tingkat 2 Dulu --</option>').prop('disabled', true);

        if (t1 && tingkatStructure[t1]) {
            t2Select.prop('disabled', false);
            $.each(tingkatStructure[t1], function(subName, arr) {
                t2Select.append('<option value="' + subName + '">' + subName + '</option>');
            });
        } else {
            t2Select.prop('disabled', true);
        }

        if ($.fn.select2) {
            t2Select.trigger('change.select2');
            t3Select.trigger('change.select2');
        }

        table.ajax.reload();
    });

    // On Tingkat 2 Change
    $('#filter_tingkat_2').on('change', function() {
        let t1 = $('#filter_tingkat_1').val();
        let t2 = $(this).val();
        let t3Select = $('#filter_tingkat_3');

        t3Select.empty().append('<option value="">-- Semua Tingkat 3 --</option>');

        if (t1 && t2 && tingkatStructure[t1] && tingkatStructure[t1][t2]) {
            t3Select.prop('disabled', false);
            let items = tingkatStructure[t1][t2];
            $.each(items, function(idx, jabName) {
                t3Select.append('<option value="' + jabName + '">' + jabName + '</option>');
            });
        } else {
            t3Select.prop('disabled', true);
        }

        if ($.fn.select2) {
            t3Select.trigger('change.select2');
        }

        table.ajax.reload();
    });

    // On Tingkat 3 Change
    $('#filter_tingkat_3').on('change', function() {
        table.ajax.reload();
    });

    // Filter for Leadership Level & Unit
    $('#filter_level, #filter_unit').on('change', function() {
        table.ajax.reload();
    });

    // Handle Tree Node Selection inside Modal
    $(document).on('click', '.btn-select-node', function() {
        let type = $(this).data('type');
        let val = $(this).data('value');
        let name = $(this).data('name');

        if (type === 'unit') {
            $('#filter_level').val('').trigger('change.select2');
            $('#filter_unit').val(val).trigger('change');
            if ($.fn.select2) $('#filter_unit').trigger('change.select2');
            $('#filter_user').val('');
            if ($.fn.select2) $('#filter_user').trigger('change.select2');
            $('#active_hirarki_text').text('Sub-Unit: ' + val);
            $('#active_hirarki_badge').removeClass('d-none').addClass('d-inline-flex');
        } else if (type === 'user') {
            $('#filter_level').val('');
            $('#filter_unit').val('');
            if ($.fn.select2) {
                $('#filter_level').trigger('change.select2');
                $('#filter_unit').trigger('change.select2');
            }
            $('#filter_user').val(val).trigger('change');
            if ($.fn.select2) $('#filter_user').trigger('change.select2');
            $('#active_hirarki_text').text('Pegawai: ' + name);
            $('#active_hirarki_badge').removeClass('d-none').addClass('d-inline-flex');
        }

        $('#modalTreeHirarki').modal('hide');
    });

    $('#btn-clear-hirarki').on('click', function(e) {
        e.preventDefault();
        $('#filter_level').val('');
        $('#filter_unit').val('');
        $('#filter_user').val('');
        $('#filter_tingkat_1').val('');
        $('#filter_tingkat_2').val('');
        $('#filter_tingkat_3').val('');
        if ($.fn.select2) {
            $('#filter_level, #filter_unit, #filter_user, #filter_tingkat_1, #filter_tingkat_2, #filter_tingkat_3').trigger('change.select2');
        }
        $('#active_hirarki_badge').addClass('d-none').removeClass('d-inline-flex');
        
        // Reset options visibility
        $('#filter_user option, #filter_user optgroup').show().prop('disabled', false);
        if ($.fn.select2) $('#filter_user').trigger('change.select2');
        table.ajax.reload();
    });

    $('#filter_periode, #filter_user, #filter_kendala').on('change', function() {
        table.ajax.reload();
    });

    function updateCharts(st) {
        let ctxPenyebab = document.getElementById('chartPenyebab').getContext('2d');
        if (chartPenyebabInst) chartPenyebabInst.destroy();

        let labels = ['Insidentil', 'Kepanitiaan', 'Beban Ganda', 'Murni Staff'];
        let dataValues = [st.insidentil, st.kepanitiaan, st.beban_ganda, st.murni];
        let bgColors = ['#dc3545', '#ffc107', '#fd7e14', '#6c757d'];

        let datasetConfig = {
            label: 'Jumlah Keterlambatan',
            data: dataValues,
            backgroundColor: currentChartType === 'radar' ? 'rgba(220, 53, 69, 0.25)' : bgColors,
            borderColor: currentChartType === 'radar' ? '#dc3545' : '#ffffff',
            borderWidth: 2,
            pointBackgroundColor: bgColors,
            pointBorderColor: '#fff',
            pointHoverBackgroundColor: '#fff',
            pointHoverBorderColor: bgColors
        };

        chartPenyebabInst = new Chart(ctxPenyebab, {
            type: currentChartType,
            data: {
                labels: labels,
                datasets: [datasetConfig]
            },
            plugins: typeof ChartDataLabels !== 'undefined' ? [ChartDataLabels] : [],
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        display: currentChartType !== 'radar',
                        labels: { boxWidth: 12, font: { size: 11 } }
                    },
                    datalabels: {
                        color: currentChartType === 'radar' ? '#111' : '#ffffff',
                        font: { weight: 'bold', size: 12 },
                        formatter: function(value, ctx) {
                            if (value === 0) return '';
                            let sum = ctx.dataset.data.reduce((a, b) => a + b, 0);
                            let percentage = (sum > 0 ? (value * 100 / sum) : 0).toFixed(0) + "%";
                            return currentChartType === 'radar' ? value + ' Tugas' : value + ' (' + percentage + ')';
                        }
                    }
                },
                scales: currentChartType === 'radar' ? {
                    r: {
                        angleLines: { display: true },
                        suggestedMin: 0,
                        ticks: { stepSize: 1, precision: 0 }
                    }
                } : {}
            }
        });

        // Render Bar Chart
        let ctxBar = document.getElementById('chartBarKendala').getContext('2d');
        if (chartBarKendalaInst) chartBarKendalaInst.destroy();

        chartBarKendalaInst = new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: ['Insidentil', 'Kepanitiaan', 'Beban Ganda', 'Murni Staff'],
                datasets: [{
                    label: 'Jumlah Tugas Terlambat',
                    data: [st.insidentil, st.kepanitiaan, st.beban_ganda, st.murni],
                    backgroundColor: bgColors,
                    borderRadius: 4
                }]
            },
            plugins: typeof ChartDataLabels !== 'undefined' ? [ChartDataLabels] : [],
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    datalabels: {
                        anchor: 'end',
                        align: 'top',
                        color: '#333',
                        font: { weight: 'bold', size: 11 },
                        formatter: function(val) {
                            return val > 0 ? val + ' Tugas' : '';
                        }
                    }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });
    }

    // Export Handlers
    $('#btn-export-excel').on('click', function() {
        let params = $('#filterForm').serialize();
        window.location.href = "{{ route('analisis-keterlambatan.export-excel') }}?" + params;
    });

    $('#btn-export-pdf').on('click', function() {
        let params = $('#filterForm').serialize();
        window.location.href = "{{ route('analisis-keterlambatan.export-pdf') }}?" + params;
    });

    // Handle Edit Saran Button Click
    $(document).on('click', '.btn-edit-saran', function() {
        let id = $(this).data('id');
        let staff = $(this).data('staff');
        let task = $(this).data('task');
        let saran = $(this).data('saran');

        $('#saran_task_id').val(id);
        $('#modal_staff_name').text(staff);
        $('#modal_task_name').text(task);
        $('#saran_pimpinan_input').val(saran);

        $('#modalSaranPimpinan').modal('show');
    });

    // Handle Form Submit for Saran Pimpinan
    $('#formSaranPimpinan').on('submit', function(e) {
        e.preventDefault();
        let id = $('#saran_task_id').val();
        let btn = $('#btn-save-saran');
        let originalText = btn.html();

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...');

        $.ajax({
            url: "{{ url('analisis-keterlambatan') }}/" + id + "/saran-pimpinan",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                saran_pimpinan: $('#saran_pimpinan_input').val()
            },
            success: function(response) {
                btn.prop('disabled', false).html(originalText);
                if (response.success) {
                    $('#modalSaranPimpinan').modal('hide');
                    table.ajax.reload(null, false);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        alert(response.message);
                    }
                } else {
                    alert(response.message || 'Gagal menyimpan saran.');
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(originalText);
                let msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Terjadi kesalahan saat menyimpan saran.';
                alert(msg);
            }
        });
    });
});
</script>
@endpush
