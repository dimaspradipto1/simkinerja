@extends('layouts.dashboard.template')

@section('page-style-class', 'menu-page-kegiatan')
@section('content')


<div class="pagetitle">
    <h1>Kegiatan</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Kegiatan</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isPimpinanRektorat() || auth()->user()->isPimpinanUnit() || auth()->user()->isSuperAdmin()))
    <!-- Top Filter Section -->
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-header-green d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                    <span class="header-title-text"><i class="bi bi-filter me-2"></i>Filter Kegiatan Berdasarkan Jabatan / Pegawai</span>
                    <a href="{{ route('kegiatan.create') }}" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1.5 shadow-sm fw-semibold rounded-2 px-3 py-1.5 text-nowrap ms-auto ms-md-0">
                        <i class="bi bi-plus-circle"></i> Tambah Kegiatan
                    </a>
                </div>
                <div class="card-body pt-3">
                    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                        <label class="form-label mb-0 fw-semibold text-secondary small text-nowrap">Filter Jabatan:</label>
                        <div style="min-width: 250px; max-width: 650px;" class="flex-grow-1">
                            <select class="form-select" id="filter-jabatan">
                                <option value="">-- Data Saya Sendiri ({{ auth()->user()->name }}) --</option>
                                <option value="__all__" {{ request('jabatan') == '__all__' ? 'selected' : '' }}>-- Semua Jabatan (Semua Pegawai) --</option>
                                @foreach($jabatanList as $jbt)
                                    <option value="{{ $jbt }}" {{ request('jabatan') == $jbt ? 'selected' : '' }}>
                                        {{ $jbt }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Main Section: Table -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-header-green d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                    <div class="header-title-text fw-semibold text-break">
                        Daftar Kegiatan &mdash; <span id="judul-jabatan">{{ auth()->user()->jabatan ? auth()->user()->jabatan . ' - ' . auth()->user()->name : 'Semua Pegawai' }}</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap flex-sm-nowrap me-auto me-md-0">
                        <button type="button" id="btn-bulk-delete" class="btn btn-danger btn-sm d-inline-flex align-items-center gap-1.5 shadow-sm fw-semibold rounded-2 px-3 py-1.5 text-nowrap d-none">
                            <i class="bi bi-trash-fill"></i> Hapus Terpilih (<span id="selected-count">0</span>)
                        </button>
                        <button type="button" class="btn btn-success btn-sm d-inline-flex align-items-center gap-1.5 shadow-sm fw-semibold rounded-2 px-3 py-1.5 text-nowrap" data-bs-toggle="modal" data-bs-target="#modalExportKegiatan" style="background-color: #15432d; border-color: #15432d;">
                            <i class="bi bi-file-earmark-arrow-down-fill"></i> Export Laporan
                        </button>
                        <button type="button" class="btn btn-outline-light btn-sm d-inline-flex align-items-center gap-1.5 shadow-sm fw-semibold rounded-2 px-3 py-1.5 text-nowrap" data-bs-toggle="modal" data-bs-target="#modalImportKegiatan">
                            <i class="bi bi-file-earmark-excel"></i> Import Excel
                        </button>
                        <a href="{{ route('kegiatan.create') }}" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1.5 shadow-sm fw-semibold rounded-2 px-3 py-1.5 text-nowrap">
                            <i class="bi bi-plus-circle"></i> Tambah Kegiatan
                        </a>
                    </div>
                </div>
                <div class="card-body pt-3">
                    <!-- Progress Card -->
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 p-3 bg-light rounded border">
                        <div class="d-flex align-items-center gap-3">
                            <div class="fw-bold fs-5 text-dark" id="progress-percent">0%</div>
                            <div class="text-secondary small">
                                <span class="badge bg-success" id="count-selesai">0</span> Selesai &bull;
                                <span class="badge bg-warning text-dark" id="count-proses">0</span> Proses &bull;
                                <span class="badge bg-secondary" id="count-belum">0</span> Belum Dimulai
                            </div>
                        </div>
                        <div class="progress flex-grow-1 mx-3" style="height: 10px; min-width: 150px;">
                            <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" id="progress-bar" style="width: 0%"></div>
                        </div>
                        <div class="text-secondary small fw-semibold">
                            Total: <span id="count-total" class="fw-bold text-dark">0</span> Kegiatan
                        </div>
                    </div>

                    <!-- DataTable -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle w-100" id="kegiatan-table">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="select-all-checkbox" class="form-check-input" style="cursor: pointer; width: 18px; height: 18px;"></th>
                                    <th>No</th>
                                    <th>Detail Kegiatan</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Stop & Upload Bukti Kegiatan -->
<div class="modal fade" id="modalStopTask" tabindex="-1" aria-labelledby="modalStopTaskLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fs-6 fw-bold" id="modalStopTaskLabel"><i class="bi bi-stop-circle-fill me-2"></i>Selesaikan Kegiatan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formStopTask" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="stop-task-id" name="task_id">
                <div class="modal-body">
                    <p class="text-secondary small mb-3">Isi hasil kegiatan atau unggah dokumen bukti pelaksanaan (opsional):</p>
                    <div class="mb-3">
                        <label for="hasil_kerja" class="form-label fw-semibold small">Hasil Kegiatan</label>
                        <textarea class="form-control" id="stop-hasil-kerja" name="hasil_kerja" rows="2" placeholder="Jelaskan ringkasan hasil kegiatan..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="rencana_tindak_lanjut" class="form-label fw-semibold small">Rencana Tindak Lanjut</label>
                        <textarea class="form-control" id="stop-tindak-lanjut" name="rencana_tindak_lanjut" rows="2" placeholder="Rencana tindak lanjut (bila ada)..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="file" class="form-label fw-semibold small">Unggah Berkas / Bukti Kegiatan</label>
                        <input type="file" class="form-control" id="stop-file" name="file">
                    </div>
                    <div class="mb-2">
                        <label for="url_external" class="form-label fw-semibold small">Tautan Google Drive / Link Luar</label>
                        <input type="url" class="form-control" id="stop-url-external" name="url_external" placeholder="https://drive.google.com/...">
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger btn-sm fw-bold"><i class="bi bi-check-lg me-1"></i> Simpan & Selesai</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Quick Tag Rekan Kerja -->
<div class="modal fade" id="modalQuickTag" tabindex="-1" aria-labelledby="modalQuickTagLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title fs-6 fw-bold" id="modalQuickTagLabel"><i class="bi bi-people-fill me-2"></i>Tag Rekan Kerja</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formQuickTag">
                @csrf
                <input type="hidden" id="tag-kegiatan-id" name="kegiatan_id">
                <div class="modal-body">
                    <p class="text-secondary small mb-2" id="tag-uraian-preview"></p>
                    <div class="mb-3">
                        <label for="quick_tagged_users" class="form-label fw-semibold small">Pilih Rekan Kerja:</label>
                        <select class="form-select select2-modal" id="quick_tagged_users" name="tagged_users[]" multiple style="width: 100%;">
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->jabatan ?? '-' }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info text-white btn-sm fw-bold"><i class="bi bi-save me-1"></i> Simpan Rekan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Barcode Absensi Kegiatan -->
<div class="modal fade" id="modalBarcodeAbsensi" tabindex="-1" aria-labelledby="modalBarcodeAbsensiLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">
            <div class="modal-header border-0 pb-0 justify-content-between position-relative pt-4 px-4">
                <div class="text-center w-100">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 mb-2 fw-semibold" style="font-size: 0.8rem;">
                        <i class="bi bi-qr-code-scan me-1"></i> Absensi Digital
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
<!-- Modal Export Excel & PDF Kegiatan -->
<div class="modal fade" id="modalExportKegiatan" tabindex="-1" aria-labelledby="modalExportKegiatanLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white" style="background-color: #15432d;">
                <h5 class="modal-title fw-bold" id="modalExportKegiatanLabel">
                    <i class="bi bi-file-earmark-arrow-down-fill me-2"></i> Export Laporan Kegiatan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('kegiatan.export-excel') }}" method="GET" id="formExportExcelKegiatan">
                <div class="modal-body p-4">
                    <!-- Pilihan Opsi Filter Export -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark mb-2">
                            Pilihan Filter Laporan <span class="text-danger">*</span>
                        </label>
                        <div class="d-flex gap-3 p-2 bg-light rounded border">
                            <div class="form-check mb-0">
                                <input class="form-check-input export-filter-choice-kg" type="radio" name="filter_type" id="export_filter_kg_periode" value="periode" checked>
                                <label class="form-check-label fw-semibold text-dark cursor-pointer" for="export_filter_kg_periode">
                                    <i class="bi bi-mortarboard me-1 text-success"></i> Tahun Akademik
                                </label>
                            </div>
                            <div class="form-check mb-0">
                                <input class="form-check-input export-filter-choice-kg" type="radio" name="filter_type" id="export_filter_kg_range" value="range">
                                <label class="form-check-label fw-semibold text-dark cursor-pointer" for="export_filter_kg_range">
                                    <i class="bi bi-calendar-range me-1 text-primary"></i> Range Tanggal
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Opsi 1: Periode Akademik -->
                    <div class="mb-3" id="wrapper_export_periode_kg">
                        <label for="export_periode_akademik_id_kg" class="form-label fw-bold text-dark mb-1">
                            Pilih Periode Akademik <span class="text-danger">*</span>
                        </label>
                        <select name="periode_akademik_id" id="export_periode_akademik_id_kg" class="form-select bg-white" required>
                            <option value="">-- Pilih Periode Akademik --</option>
                            @if(isset($periodeAkademiks))
                                @foreach($periodeAkademiks as $p)
                                    <option value="{{ $p->id }}" {{ $p->id == ($defaultPeriodeId ?? null) ? 'selected' : '' }}>
                                        {{ $p->formatted_ta ?? $p->nama_periode }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <!-- Opsi 2: Range Tanggal -->
                    <div class="mb-3 d-none" id="wrapper_export_range_kg">
                        <label class="form-label fw-bold text-dark mb-1">
                            Pilih Rentang Tanggal <span class="text-danger">*</span>
                        </label>
                        <div class="row g-2">
                            <div class="col-6">
                                <label for="export_tanggal_mulai_kg" class="form-label small text-muted mb-1">Tanggal Mulai</label>
                                <input type="date" name="tanggal_mulai" id="export_tanggal_mulai_kg" class="form-control bg-white">
                            </div>
                            <div class="col-6">
                                <label for="export_tanggal_selesai_kg" class="form-label small text-muted mb-1">Tanggal Selesai</label>
                                <input type="date" name="tanggal_selesai" id="export_tanggal_selesai_kg" class="form-control bg-white">
                            </div>
                        </div>
                    </div>

                    <!-- Filter Kategori Kegiatan -->
                    <div class="mb-3">
                        <label for="export_kategori_kg" class="form-label fw-bold text-dark mb-1">
                            Kategori Kegiatan
                        </label>
                        <select name="kategori" id="export_kategori_kg" class="form-select bg-white">
                            <option value="">-- Semua Kategori Kegiatan --</option>
                            <option value="Rencana Kerja">Rencana Kerja</option>
                            <option value="Kepanitiaan">Kepanitiaan</option>
                            <option value="Insidentil">Insidentil</option>
                        </select>
                    </div>

                    @if(auth()->check() && (auth()->user()->isPimpinanUnit() || auth()->user()->isAdmin() || auth()->user()->isPimpinanRektorat()))
                    <div class="mb-3">
                        <label for="export_user_id_kg" class="form-label fw-bold text-dark mb-1">
                            Pilih Jabatan / Pegawai
                        </label>
                        <select name="user_id" id="export_user_id_kg" class="form-select bg-white">
                            <option value="">-- Semua Pegawai / Jabatan --</option>
                            @if(isset($usersWithJabatan))
                                @foreach($usersWithJabatan as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} &mdash; {{ $u->jabatan }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    @else
                        <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
                    @endif
                </div>
                <div class="modal-footer bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Batal</button>
                    <div class="d-flex gap-2">
                        <button type="submit" formaction="{{ route('kegiatan.export-excel') }}" class="btn btn-success px-3 text-white fw-bold" style="background-color: #15432d; border-color: #15432d;">
                            <i class="bi bi-file-earmark-excel-fill me-1"></i> Unduh Excel
                        </button>
                        <button type="submit" formaction="{{ route('kegiatan.export-pdf') }}" class="btn btn-danger px-3 text-white fw-bold">
                            <i class="bi bi-file-earmark-pdf-fill me-1"></i> Unduh PDF
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Import Excel Kegiatan -->
<div class="modal fade" id="modalImportKegiatan" tabindex="-1" aria-labelledby="modalImportKegiatanLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white" style="background-color: #2d6a4f;">
                <h5 class="modal-title fw-bold" id="modalImportKegiatanLabel">
                    <i class="bi bi-file-earmark-excel me-2"></i> Import Kegiatan Excel
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('kegiatan.import-excel') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="p-3 mb-3 rounded" style="background-color: #f0fdf4; border: 1px solid #bbf7d0;">
                        <div class="d-flex align-items-start gap-2 mb-2">
                            <i class="bi bi-check-circle-fill text-success fs-5 mt-0.5"></i>
                            <div style="font-size: 0.88rem;" class="text-dark">
                                <span class="fw-bold text-success d-block mb-1">Mendukung Format Terpadu:</span>
                                Anda dapat mengunggah file template terpadu yang memuat kolom kategori (<strong>Rencana Kerja</strong>, <strong>Kepanitiaan</strong>, atau <strong>Insidentil</strong>).
                            </div>
                        </div>
                        <div class="pt-2 border-top border-success-subtle d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <span class="text-muted small"><i class="bi bi-info-circle me-1"></i>Belum punya format?</span>
                            <a href="{{ route('kegiatan.download-template') }}" class="btn btn-sm btn-outline-success fw-bold py-1 px-2.5" style="font-size: 0.82rem; border-radius: 6px;">
                                <i class="bi bi-download me-1"></i> Download Template Terpadu
                            </a>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark mb-2">Kategori Default <span class="text-muted fw-normal" style="font-size: 0.85rem;">(Jika di Excel kolom kategori kosong)</span></label>
                        <select name="kategori" class="form-select bg-white">
                            <option value="Rencana Kerja">Rencana Kerja</option>
                            <option value="Kepanitiaan">Kepanitiaan</option>
                            <option value="Insidentil">Insidentil</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark mb-2">Periode Akademik Default <span class="text-muted fw-normal" style="font-size: 0.85rem;">(Opsional, jika di Excel kosong)</span></label>
                        <select name="periode_akademik_id" class="form-select bg-white">
                            <option value="">-- Otomatis Sesuai Data Excel --</option>
                            @if(isset($periodeAkademiks))
                                @foreach($periodeAkademiks as $p)
                                    <option value="{{ $p->id }}">{{ $p->formatted_ta ?? $p->nama_periode }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark mb-2">Pilih File Excel (.xlsx, .xls, .csv)</label>
                        <input type="file" name="file_excel" class="form-control bg-white" accept=".xlsx,.xls,.csv" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success px-4 text-white fw-bold" style="background-color: #15432d; border-color: #15432d;">
                        <i class="bi bi-cloud-arrow-up-fill me-1"></i> Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('partials.milestone-scripts')
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let table = $('#kegiatan-table').DataTable({
        processing: true,
        serverSide: true,
        stateSave: true,
        ajax: {
            url: "{{ route('kegiatan.index') }}",
            data: function(d) {
                d.jabatan = $('#filter-jabatan').val();
            }
        },
        columns: [
            { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'task_details', name: 'task_details', orderable: false, searchable: true },
        ],
        drawCallback: function(settings) {
            let json = settings.json;
            if (json) {
                $('#count-total').text(json.totalCount || 0);
                $('#count-selesai').text(json.selesaiCount || 0);
                $('#count-proses').text(json.prosesCount || 0);
                $('#count-belum').text(json.belumCount || 0);
                let percent = json.percent || 0;
                $('#progress-percent').text(percent + '%');
                $('#progress-bar').css('width', percent + '%');
            }
            updateBulkDeleteState();
        },
        language: {
            search: "Cari Kegiatan:",
            lengthMenu: "Tampilkan _MENU_ data",
            zeroRecords: "Tidak ada kegiatan ditemukan",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ kegiatan",
            infoEmpty: "Tidak ada kegiatan",
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Memuat...</span></div>'
        }
    });

    $('#filter-jabatan').on('change', function() {
        let val = $(this).val();
        let selectedText = $(this).find('option:selected').text().trim();
        if (!val || val === '__own__') {
            $('#judul-jabatan').text("{{ auth()->user()->jabatan ? auth()->user()->jabatan . ' - ' . auth()->user()->name : auth()->user()->name }}");
        } else if (val === '__all__') {
            $('#judul-jabatan').text('Semua Jabatan');
        } else {
            $('#judul-jabatan').text(selectedText || val);
        }
        table.ajax.reload();
    });

    // Select all checkboxes
    $('#select-all-checkbox').on('change', function() {
        $('.select-row-checkbox').prop('checked', $(this).prop('checked'));
        updateBulkDeleteState();
    });

    $(document).on('change', '.select-row-checkbox', function() {
        updateBulkDeleteState();
    });

    function updateBulkDeleteState() {
        let checked = $('.select-row-checkbox:checked').length;
        $('#selected-count').text(checked);
        if (checked > 0) {
            $('#btn-bulk-delete').removeClass('d-none');
        } else {
            $('#btn-bulk-delete').addClass('d-none');
            $('#select-all-checkbox').prop('checked', false);
        }
    }

    // Toggle Opsi Filter Export Kegiatan (Periode Akademik vs Range Tanggal)
    $('.export-filter-choice-kg').on('change', function() {
        if ($(this).val() === 'periode') {
            $('#wrapper_export_periode_kg').removeClass('d-none');
            $('#wrapper_export_range_kg').addClass('d-none');
            $('#export_periode_akademik_id_kg').prop('required', true);
            $('#export_tanggal_mulai_kg').prop('required', false).val('');
            $('#export_tanggal_selesai_kg').prop('required', false).val('');
        } else {
            $('#wrapper_export_periode_kg').addClass('d-none');
            $('#wrapper_export_range_kg').removeClass('d-none');
            $('#export_periode_akademik_id_kg').prop('required', false);
            $('#export_tanggal_mulai_kg').prop('required', true);
            $('#export_tanggal_selesai_kg').prop('required', true);
        }
    });

    // Bulk Delete
    $('#btn-bulk-delete').on('click', function() {
        let ids = [];
        $('.select-row-checkbox:checked').each(function() {
            ids.push($(this).val());
        });

        if (ids.length === 0) return;

        Swal.fire({
            title: 'Hapus ' + ids.length + ' kegiatan terpilih?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus Semua!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('kegiatan.bulk-delete') }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        ids: ids
                    },
                    success: function(res) {
                        if (res.success) {
                            Swal.fire({ title: 'Berhasil!', text: res.message, icon: 'success', timer: 1500, showConfirmButton: false });
                            table.ajax.reload();
                        }
                    }
                });
            }
        });
    });

    // Timer functions
    window.startTimer = function(id) {
        $.ajax({
            url: "{{ url('kegiatan') }}/" + id + "/start",
            type: 'POST',
            data: { _token: "{{ csrf_token() }}" },
            success: function(res) {
                if (res.success) {
                    Swal.fire({ title: 'Berhasil!', text: res.message, icon: 'success', timer: 1200, showConfirmButton: false });
                    table.ajax.reload(null, false);
                }
            }
        });
    };

    window.pauseTaskTimer = function(id, routePrefix) {
        Swal.fire({
            title: 'Jeda Kegiatan (Pause)',
            html: `
                <div class="text-start">
                    <label class="form-label fw-semibold small text-muted mb-1">
                        <i class="bi bi-info-circle-fill text-warning me-1"></i>Penyebab Di-pause:
                    </label>
                    <textarea id="swal-pause-kegiatan-reason" class="form-control" rows="3" placeholder="Masukkan alasan atau kendala mengapa kegiatan di-pause..."></textarea>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d97706',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-pause-fill me-1"></i> Pause Kegiatan',
            cancelButtonText: 'Batal',
            focusConfirm: false,
            preConfirm: () => {
                const el = document.getElementById('swal-pause-kegiatan-reason');
                return el ? el.value.trim() : '';
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('kegiatan') }}/" + id + "/pause",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        alasan_pause: result.value || ''
                    },
                    success: function(res) {
                        if (res.success) {
                            Swal.fire({ title: 'Dijeda', text: res.message, icon: 'info', timer: 1200, showConfirmButton: false });
                            table.ajax.reload(null, false);
                        }
                    }
                });
            }
        });
    };

    $(document).on('click', '.btn-simpan-alasan-pause', function(e) {
        e.preventDefault();
        const btn = $(this);
        const id = btn.data('id');
        const container = btn.closest('.border');
        const textarea = container.find('.field-alasan-pause');
        const alasan = textarea.val();

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>');

        $.ajax({
            url: "{{ url('kegiatan') }}/" + id + "/upload-attachment",
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                alasan_pause: alasan
            },
            success: function(res) {
                btn.prop('disabled', false).html('<i class="bi bi-check2-circle me-1"></i> Simpan Penyebab Pause');
                if (res.success) {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Penyebab pause berhasil disimpan.', showConfirmButton: false, timer: 2000 });
                    table.ajax.reload(null, false);
                }
            },
            error: function() {
                btn.prop('disabled', false).html('<i class="bi bi-check2-circle me-1"></i> Simpan Penyebab Pause');
                alert('Gagal menyimpan penyebab pause.');
            }
        });
    });

    window.stopTimer = function(id) {
        $('#stop-task-id').val(id);
        $('#modalStopTask').modal('show');
    };

    $('#formStopTask').on('submit', function(e) {
        e.preventDefault();
        let id = $('#stop-task-id').val();
        let formData = new FormData(this);

        $.ajax({
            url: "{{ url('kegiatan') }}/" + id + "/stop",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.success) {
                    $('#modalStopTask').modal('hide');
                    Swal.fire({ title: 'Selesai!', text: res.message, icon: 'success', timer: 1500, showConfirmButton: false });
                    table.ajax.reload(null, false);
                }
            }
        });
    });

    // Quick Tag Rekan Kerja
    $(document).on('click', '.btn-quick-tag', function() {
        let id = $(this).data('id');
        let uraian = $(this).data('uraian');
        let tags = $(this).data('tags') || [];

        $('#tag-kegiatan-id').val(id);
        $('#tag-uraian-preview').text('Kegiatan: ' + uraian);
        $('#quick_tagged_users').val(tags).trigger('change');
        $('#modalQuickTag').modal('show');
    });

    $('#formQuickTag').on('submit', function(e) {
        e.preventDefault();
        let id = $('#tag-kegiatan-id').val();
        $.ajax({
            url: "{{ url('kegiatan') }}/" + id + "/update-tags",
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if (res.success) {
                    $('#modalQuickTag').modal('hide');
                    Swal.fire({ title: 'Berhasil!', text: res.message, icon: 'success', timer: 1200, showConfirmButton: false });
                    table.ajax.reload(null, false);
                }
            }
        });
    });

    // Delete single task
    window.deleteKegiatan = function(id) {
        Swal.fire({
            title: 'Hapus kegiatan ini?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('kegiatan') }}/" + id,
                    type: 'DELETE',
                    data: { _token: "{{ csrf_token() }}" },
                    success: function(res) {
                        if (res.success) {
                            Swal.fire({ title: 'Berhasil!', text: res.message, icon: 'success', timer: 1200, showConfirmButton: false });
                            table.ajax.reload(null, false);
                        }
                    }
                });
            }
        });
    };

    // Barcode Absensi Modal Handler
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

    $(document).on('click', '.btn-barcode-absensi', function(e) {
        e.preventDefault();
        currentBarcodeSlug = $(this).data('slug');
        const sessionTitle = $(this).data('title') || 'Barcode Absensi';
        currentBarcodeUrl = $(this).data('url') || ("{{ url('') }}/" + currentBarcodeSlug);

        $('#modalBarcodeAbsensiLabel').text(sessionTitle);
        $('#btn-visit-absensi').attr('href', currentBarcodeUrl);

        activeBarcodeSession = 'datang';
        $('#btn-barcode-datang').removeClass('btn-outline-success').addClass('btn-success');
        $('#btn-barcode-pulang').removeClass('btn-primary').addClass('btn-outline-primary');
        $('#barcode-session-indicator').text('Sesi: Waktu Datang');

        refreshBarcodeQr();
        startBarcodeTimer();

        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            var elModal = document.getElementById('modalBarcodeAbsensi');
            var qrModal = bootstrap.Modal.getInstance(elModal) || new bootstrap.Modal(elModal);
            qrModal.show();
        } else {
            $('#modalBarcodeAbsensi').modal('show');
        }
    });

    $('#btn-barcode-datang').on('click', function() {
        activeBarcodeSession = 'datang';
        $(this).removeClass('btn-outline-success').addClass('btn-success');
        $('#btn-barcode-pulang').removeClass('btn-primary').addClass('btn-outline-primary');
        $('#barcode-session-indicator').text('Sesi: Waktu Datang');
        refreshBarcodeQr();
    });

    $('#btn-barcode-pulang').on('click', function() {
        activeBarcodeSession = 'pulang';
        $(this).removeClass('btn-outline-primary').addClass('btn-primary');
        $('#btn-barcode-datang').removeClass('btn-success').addClass('btn-outline-success');
        $('#barcode-session-indicator').text('Sesi: Waktu Pulang');
        refreshBarcodeQr();
    });

    $('#modalBarcodeAbsensi').on('hidden.bs.modal', function() {
        clearInterval(barcodeTimer);
    });
});
</script>
@endpush
