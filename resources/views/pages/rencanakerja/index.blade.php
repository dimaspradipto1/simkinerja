@extends('layouts.dashboard.template')

@section('page-style-class', 'menu-page-rencana-kerja')
@section('content')


<div class="pagetitle">
    <h1>Rencana Kerja</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Rencana Kerja</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    @if(auth()->check() && (auth()->user()->isRektorOrSuperAdmin() || auth()->user()->isPimpinanUnit()))
    <!-- Top Section: Kriteria Checklist Jabatan (Khusus Pimpinan & Admin) -->
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-header-green d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                    <span class="header-title-text">Kriteria Checklist Jabatan</span>
                </div>
                <div class="card-body pt-3">
                    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                        <label class="form-label mb-0 fw-semibold text-secondary small text-nowrap">Kriteria Checklist untuk jabatan:</label>
                        <div style="min-width: 250px; max-width: 650px;" class="flex-grow-1">
                            <select class="form-select" id="filter-jabatan">
                                <option value=""></option>
                                <option value="__own__">-- Data Saya Sendiri ({{ auth()->user()->name }}) --</option>
                                <option value="__all__">-- Semua Jabatan (Semua Pegawai) --</option>
                                @foreach($usersWithJabatan as $u)
                                    <option value="{{ $u->jabatan }}" data-user-id="{{ $u->id }}" {{ request('jabatan') == $u->jabatan ? 'selected' : '' }}>
                                        {{ $u->jabatan }} - {{ $u->name }}
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

    @if(isset($pendingRequests) && $pendingRequests->isNotEmpty())
    <!-- Section: Permohonan Perpanjangan Waktu Masuk -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="p-3 p-sm-3.5 rounded-3 shadow-xs" style="background-color: #fffdf5; border: 1px solid #fde68a; border-left: 5px solid #f59e0b; width: 100%; max-width: 100%; box-sizing: border-box;">
                <!-- Header Banner -->
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2.5 pb-2 border-bottom" style="border-color: #fef08a !important;">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge bg-danger rounded-pill px-2.5 py-1 fw-bold shadow-2xs" style="font-size: 0.78rem;">
                            <i class="bi bi-bell-fill me-1"></i> {{ $pendingRequests->count() }} Permohonan Masuk
                        </span>
                        <span class="text-dark small fw-semibold">Tugas melewati estimasi dan membutuhkan persetujuan perpanjangan waktu:</span>
                    </div>
                </div>

                <!-- List of Items -->
                <div class="d-flex flex-column gap-3">
                    @foreach($pendingRequests as $pr)
                    @php
                        $rReason = $pr->estimasi_unlock_reason;
                        $tujuanPimpinan = null;
                        if (preg_match('/^\[Tujuan:\s*([^\]]+)\]\s*(.*)$/s', $rReason, $mMatches)) {
                            $tujuanPimpinan = $mMatches[1];
                            $rReason = $mMatches[2];
                        }
                    @endphp
                    <div class="p-3 bg-white rounded-3 border shadow-2xs" style="border-color: #fed7aa !important; width: 100%; max-width: 100%; box-sizing: border-box;">
                        <!-- 1. Header: Pegawai & Timestamp -->
                        <div class="d-flex align-items-start justify-content-between gap-2 mb-2 pb-2 border-bottom border-light-subtle">
                            <div class="d-flex align-items-center gap-2">
                                <div class="d-flex align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary flex-shrink-0" style="width: 34px; height: 34px; font-size: 1rem;">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                                <div>
                                    <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                        <span class="fw-bold text-dark" style="font-size: 0.9rem;">{{ $pr->user->name ?? '-' }}</span>
                                        @if(!empty($pr->user->jabatan))
                                            <span class="badge bg-light text-secondary border fw-normal" style="font-size: 0.68rem; padding: 2px 6px;">{{ $pr->user->jabatan }}</span>
                                        @endif
                                    </div>
                                    <div class="text-muted d-flex align-items-center gap-1 mt-0.5" style="font-size: 0.72rem;">
                                        <i class="bi bi-clock"></i>
                                        <span>{{ \Carbon\Carbon::parse($pr->estimasi_unlock_requested_at)->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Uraian Tugas -->
                        <div class="mb-2.5" style="word-break: break-word;">
                            <div class="d-flex align-items-baseline gap-1.5">
                                <span class="badge bg-primary text-white flex-shrink-0" style="font-size: 0.68rem; padding: 3px 6px; border-radius: 4px;">Tugas</span>
                                <span class="fw-bold text-dark" style="font-size: 0.88rem; line-height: 1.45;">{{ $pr->uraian_tugas }}</span>
                            </div>
                        </div>

                        <!-- 3. Tujuan & Kendala -->
                        <div class="d-flex flex-column gap-1.5 mb-2.5" style="font-size: 0.78rem;">
                            @if($tujuanPimpinan)
                                <div class="d-flex align-items-center gap-2 p-1.5 px-2.5 rounded-2 bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.76rem;">
                                    <i class="bi bi-person-badge-fill flex-shrink-0"></i>
                                    <span class="text-truncate">Tujuan: <strong>{{ $tujuanPimpinan }}</strong></span>
                                </div>
                            @endif
                            <div class="d-flex align-items-start gap-2 p-1.5 px-2.5 rounded-2 bg-danger-subtle text-danger border border-danger-subtle" style="font-size: 0.76rem; word-break: break-word;">
                                <i class="bi bi-exclamation-circle-fill flex-shrink-0 mt-0.5"></i>
                                <div>
                                    <span class="fw-bold me-1">Kendala:</span>
                                    <span class="text-dark fw-medium">{{ strip_tags($rReason) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- 4. Tombol di Bawah -->
                        <div class="pt-2 border-top" style="border-color: #f1f5f9 !important;">
                            @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isPimpinanRektorat() || auth()->user()->isPimpinanUnit()))
                            <button type="button" 
                                    class="btn btn-warning btn-sm w-100 fw-bold d-flex align-items-center justify-content-center text-dark shadow-2xs" 
                                    style="background-color: #ffc107; border-color: #ffc107; padding: 8px 14px; font-size: 0.85rem; border-radius: 7px;"
                                    data-id="{{ $pr->id }}" 
                                    data-uraian="{{ e($pr->uraian_tugas) }}" 
                                    data-pegawai="{{ e($pr->user->name ?? '-') }}" 
                                    data-reason="{{ e($pr->estimasi_unlock_reason) }}" 
                                    data-tgl-selesai="{{ e($pr->estimasi_tanggal_selesai ?? date('Y-m-d')) }}" 
                                    data-jam-selesai="{{ e($pr->estimasi_jam_selesai ? substr($pr->estimasi_jam_selesai, 0, 5) : '23:59') }}" 
                                    data-route="{{ route('rencana-kerja.approve-extension', $pr->id) }}" 
                                    onclick="window.openApproveExtensionModal(this)">
                                <i class="bi bi-clock-history me-2 fs-6"></i>
                                <span>Beri Perpanjangan Waktu</span>
                            </button>
                            @else
                            <div class="text-center text-muted small py-1" style="font-size: 0.75rem;">
                                <i class="bi bi-hourglass-split me-1 text-warning"></i> Menunggu Persetujuan Pimpinan
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Bottom Section: Checklist Tugas -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-header-green d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
                    <div class="header-title-text d-flex align-items-center gap-2 flex-wrap text-white py-1">
                        <i class="bi bi-kanban-fill text-warning me-1" style="font-size: 1.15rem;"></i>
                        <span class="fw-bold text-white fs-6 mb-0">Rencana Kerja</span>
                        <span class="text-white d-none d-sm-inline">:</span>
                        <span id="judul-jabatan" class="fw-semibold" style="color: #f1f5f9; font-size: 0.95rem;">
                            {{ auth()->user()->jabatan ? auth()->user()->jabatan . ' - ' . auth()->user()->name : auth()->user()->name }}
                        </span>
                    </div>
                    <div class="header-action-group d-flex align-items-center gap-2 flex-wrap flex-sm-nowrap me-auto me-xl-0">
                        <div class="filter-status-modern" title="Filter checklist berdasarkan status">
                            <i class="bi bi-funnel-fill text-success" style="font-size: 0.85rem;"></i>
                            <span class="small fw-semibold text-secondary text-nowrap d-none d-xxl-inline" style="font-size: 0.78rem;">Status:</span>
                            <select id="filter-status" class="form-select form-select-sm border-0 bg-transparent fw-semibold text-dark p-0 pe-3 shadow-none">
                                <option value="">Semua Status</option>
                                <option value="Selesai">Selesai</option>
                                <option value="Proses">Proses / Berjalan</option>
                                <option value="Belum Dimulai">Belum Dimulai</option>
                            </select>
                        </div>
                        <button type="button" id="btn-toggle-all-cards" class="btn btn-header-action btn-light-modern" title="Buka atau tutup semua rincian">
                            <i class="bi bi-arrows-expand me-1.5 text-secondary"></i>
                            <span id="toggle-all-text">Buka Semua</span>
                        </button>
                        <button type="button" id="btn-voice-rencanakerja" class="btn btn-header-action btn-voice-modern" title="Dengarkan / Perintah Suara">
                            <i class="bi bi-volume-up-fill me-1.5"></i>
                            <span>Suara</span>
                        </button>
                        <button type="button" class="btn btn-header-action btn-light-modern" data-bs-toggle="modal" data-bs-target="#modalImportExcel" title="Import Rencana Kerja dari Excel">
                            <i class="bi bi-file-earmark-excel-fill text-success me-1.5"></i>
                            <span>Import Excel</span>
                        </button>
                    </div>
                </div>
                <div class="card-body p-3">
                    <!-- Quick Status Filter Pills -->
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 pb-2 border-bottom">
                        <div class="d-flex flex-wrap align-items-center gap-2" id="filter-status-pills">
                            <button type="button" class="btn btn-sm btn-filter-pill active fw-semibold shadow-2xs" data-status="">
                                <i class="bi bi-grid-fill me-1"></i> Semua (<span id="count-status-all">0</span>)
                            </button>
                            <button type="button" class="btn btn-sm btn-filter-pill fw-semibold shadow-2xs bg-white border text-success" data-status="Selesai">
                                <i class="bi bi-check-circle-fill me-1 text-success"></i> Selesai (<span id="count-status-selesai">0</span>)
                            </button>
                            <button type="button" class="btn btn-sm btn-filter-pill fw-semibold shadow-2xs bg-white border text-warning" data-status="Proses">
                                <i class="bi bi-hourglass-split me-1 text-warning"></i> Proses / Berjalan (<span id="count-status-proses">0</span>)
                            </button>
                            <button type="button" class="btn btn-sm btn-filter-pill fw-semibold shadow-2xs bg-white border text-secondary" data-status="Belum Dimulai">
                                <i class="bi bi-dash-circle me-1 text-secondary"></i> Belum Dimulai (<span id="count-status-belum">0</span>)
                            </button>
                        </div>
                    </div>

                    <!-- Toolbar Pilih Semua (Select All) & Hapus Terpilih -->
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 p-2 px-3 mb-3 rounded-3 border border-light-subtle shadow-2xs" id="selectAllToolbarRencanaKerja" style="background: #f8fafc; border-left: 4px solid #15432d !important;">
                        <div class="d-flex align-items-center gap-2">
                            <input type="checkbox" id="check-all" class="form-check-input my-0" style="cursor: pointer; width: 19px; height: 19px;" title="Pilih Semua">
                            <label for="check-all" class="form-check-label fw-bold text-dark user-select-none mb-0 cursor-pointer" style="cursor: pointer; font-size: 0.88rem;">
                                Pilih Semua (Select All)
                            </label>
                            <span id="selected-badge" class="badge bg-primary-subtle text-primary border border-primary-subtle d-none ms-1" style="font-size: 0.78rem;">
                                <span id="selected-count">0</span> tugas dipilih
                            </span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" id="btn-bulk-delete" class="btn btn-danger btn-sm text-white fw-bold d-inline-flex align-items-center gap-1 d-none shadow-xs" style="border-radius: 6px; font-size: 0.82rem; padding: 6px 14px;">
                                <i class="bi bi-trash-fill me-1"></i> Hapus Terpilih (<span class="selected-count-num">0</span>)
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover w-100" id="rencanakerja-table">
                            <thead>
                                <tr>
                                    <th width="3%" class="text-center align-middle">
                                        <input type="checkbox" class="form-check-input" style="cursor: pointer; width: 18px; height: 18px;" title="Pilih Semua">
                                    </th>
                                    <th width="4%">No</th>
                                    <th>Uraian Tugas & Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Import Excel -->
<div class="modal fade" id="modalImportExcel" tabindex="-1" aria-labelledby="modalImportExcelLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white" style="background-color: #2d6a4f;">
                <h5 class="modal-title fw-bold" id="modalImportExcelLabel">
                    <i class="bi bi-file-earmark-excel me-2"></i> Import Rencana Kerja Excel
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('rencana-kerja.import-excel') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="p-3 mb-3 rounded" style="background-color: #f0fdf4; border: 1px solid #bbf7d0;">
                        <div class="d-flex align-items-start gap-2 mb-2">
                            <i class="bi bi-check-circle-fill text-success fs-5 mt-0.5"></i>
                            <div style="font-size: 0.88rem;" class="text-dark">
                                <span class="fw-bold text-success d-block mb-1">Sistem Dinamis Multi-Format:</span>
                                Anda dapat mengunggah <strong>File Hasil "Export Laporan"</strong> secara langsung, atau menggunakan <strong>Template Standar Excel</strong>. Sistem akan mendeteksi formatnya secara otomatis.
                            </div>
                        </div>
                        <div class="pt-2 border-top border-success-subtle d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <span class="text-muted small"><i class="bi bi-info-circle me-1"></i>Belum punya format?</span>
                            <a href="{{ route('rencana-kerja.download-template') }}" class="btn btn-sm btn-outline-success fw-bold py-1 px-2.5" style="font-size: 0.82rem; border-radius: 6px;">
                                <i class="bi bi-download me-1"></i> Download Template Standar
                            </a>
                        </div>
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

<!-- Modal Quick Tag Rekan Kerja -->
<div class="modal fade" id="modalQuickTag" tabindex="-1" aria-labelledby="modalQuickTagLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white" style="background-color: #15432d;">
                <h5 class="modal-title fw-bold" id="modalQuickTagLabel">
                    <i class="bi bi-person-plus-fill me-2"></i> Tag Rekan Kerja
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-quick-tag" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary mb-1">Uraian Tugas</label>
                        <div id="quick-tag-uraian" class="fw-semibold text-dark p-2 bg-light border rounded" style="font-size: 0.95rem; word-break: break-word;"></div>
                    </div>
                    <div class="mb-3">
                        <label for="quick_tagged_users" class="form-label fw-bold text-dark mb-1">
                            Pilih Rekan Kerja <span class="text-danger">*</span>
                        </label>
                        <select name="tagged_users[]" id="quick_tagged_users" class="form-select select2-multiple" multiple="multiple" style="width: 100%;">
                            @foreach($usersWithJabatan as $u)
                                <option value="{{ $u->id }}">{{ $u->jabatan ?? '-' }} - {{ $u->name }}</option>
                            @endforeach
                        </select>
                        <div class="form-text text-muted small mt-1">Pilih satu atau beberapa rekan kerja untuk pengerjaan tugas yang sama.</div>
                    </div>
                </div>
                <div class="modal-footer bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success px-4 text-white fw-bold btn-save-quick-tag" style="background-color: #15432d; border-color: #15432d; height: 38px;">
                        <i class="bi bi-save me-1"></i> Simpan
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
        $('#btn-voice-rencanakerja').on('click', function() {
            let jabatanVal = $('#filter-jabatan').length ? $('#filter-jabatan').val() : '';
            let voiceText = "Halaman Laporan Rencana Kerja untuk " + (jabatanVal ? "jabatan " + jabatanVal : "{{ auth()->user()->name }}") + ". ";

            let taskItems = [];
            if (typeof table !== 'undefined' && table.rows) {
                let rowsData = table.rows({ search: 'applied' }).data();
                for (let i = 0; i < rowsData.length; i++) {
                    let rData = rowsData[i];
                    if (rData && rData.voice_narration) {
                        let parts = rData.voice_narration.split('|||');
                        let uraianTitle = parts[0] || '';
                        let detailsStr = parts[1] || '';
                        taskItems.push("Uraian Kerja " + (i + 1) + ": " + uraianTitle + ". " + detailsStr);
                    }
                }
            }

            // Fallback to DOM parsing if DataTables rows are empty or initializing
            if (taskItems.length === 0) {
                $('#rencanakerja-table tbody tr').each(function(idx) {
                    let row = $(this);
                    let taskTitle = row.find('.text-dark.fw-semibold, .fw-semibold.text-dark, .lh-sm').first().text().trim();
                    if (taskTitle) {
                        let textContent = row.text();
                        let no = idx + 1;
                        let uraianStr = "Uraian Kerja " + no + ": " + taskTitle + ". ";

                        // Estimasi
                        let estStr = "Estimasi: ";
                        let estMatch = textContent.match(/Estimasi Pelaksanaan:\s*([^\n\r\t]+)/i);
                        if (estMatch && estMatch[1] && estMatch[1].trim() !== '-') {
                            estStr += estMatch[1].trim() + ". ";
                        } else {
                            estStr += "Belum ada. ";
                        }

                        // Waktu Mulai
                        let mulaiStr = "Waktu mulai: ";
                        let mulaiMatch = textContent.match(/Waktu Mulai:\s*([^\n\r\t]+)/i);
                        if (mulaiMatch && mulaiMatch[1] && mulaiMatch[1].trim() !== '-') {
                            mulaiStr += mulaiMatch[1].trim() + ". ";
                        } else {
                            mulaiStr += "Belum dimulai. ";
                        }

                        // Waktu Selesai
                        let selesaiStr = "Waktu selesai: ";
                        let selesaiMatch = textContent.match(/Waktu Selesai:\s*([^\n\r\t]+)/i);
                        if (selesaiMatch && selesaiMatch[1] && selesaiMatch[1].trim() !== '-') {
                            selesaiStr += selesaiMatch[1].trim() + ". ";
                        } else {
                            selesaiStr += "Belum selesai. ";
                        }

                        // Durasi
                        let durasiStr = "Durasi: ";
                        let durasiMatch = textContent.match(/Total Durasi:\s*([^\n\r\t]+)/i);
                        if (durasiMatch && durasiMatch[1] && durasiMatch[1].trim() !== '-') {
                            durasiStr += durasiMatch[1].trim() + ". ";
                        } else {
                            durasiStr += "Belum ada. ";
                        }

                        // Analisis
                        let statusText = "Belum Dimulai";
                        if (textContent.includes("Selesai")) statusText = "Selesai";
                        else if (textContent.includes("Proses")) statusText = "Proses";

                        let analisisStr = "Analisis: ";
                        if (statusText === "Selesai") {
                            analisisStr += "Selesai tepat waktu. ";
                        } else if (statusText === "Proses") {
                            analisisStr += "Masih berproses. ";
                        } else {
                            analisisStr += "Belum dikerjakan. ";
                        }

                        // Link Eksternal Only
                        let hasLink = row.find('.bi-link-45deg, .bi-box-arrow-up-right').length > 0 || textContent.includes('Link External') || textContent.includes('http');
                        let linkStr = "Link eksternal: " + (hasLink ? "Ada link eksternal." : "Tidak ada link eksternal.") + " ";

                        taskItems.push(uraianStr + estStr + mulaiStr + selesaiStr + durasiStr + analisisStr + linkStr);
                    }
                });
            }

            if (taskItems.length > 0) {
                voiceText += "Menampilkan " + taskItems.length + " uraian kerja: " + taskItems.join(" ");

                // Combined Rekapitulasi Capaian Kinerja across ALL tasks in dataset
                let totalCount = taskItems.length;
                let selesaiCount = 0;
                let prosesCount = 0;
                let belumCount = 0;

                if (typeof window.latestOverallRekap !== 'undefined' && window.latestOverallRekap) {
                    totalCount = window.latestOverallRekap.total || taskItems.length;
                    selesaiCount = window.latestOverallRekap.selesai || 0;
                    prosesCount = window.latestOverallRekap.proses || 0;
                    belumCount = window.latestOverallRekap.belum || 0;
                } else if (typeof table !== 'undefined' && table.rows) {
                    let rowsData = table.rows().data();
                    totalCount = rowsData.length;
                    for (let i = 0; i < rowsData.length; i++) {
                        let rData = rowsData[i];
                        let txt = rData ? (rData.task_details || '') + (rData.voice_narration || '') : '';
                        if (txt.includes('Selesai')) selesaiCount++;
                        else if (txt.includes('Proses')) prosesCount++;
                        else belumCount++;
                    }
                }

                let percent = totalCount > 0 ? Math.round((selesaiCount / totalCount) * 100) : 0;
                let rekapStr = " Rekapitulasi keseluruhan capaian kinerja: Dari gabungan " + totalCount + " rencana kerja, sebanyak " + selesaiCount + " tugas telah selesai, " + prosesCount + " tugas sedang berproses, dan " + belumCount + " tugas belum dimulai. ";
                rekapStr += "Tingkat capaian kinerja Anda secara keseluruhan mencapai " + percent + " persen. ";

                if (percent >= 80) {
                    rekapStr += "Capaian kinerja Anda sangat bagus dan sangat memuaskan! Pertahankan prestasi kinerja luar biasa ini!";
                } else if (percent >= 50) {
                    rekapStr += "Capaian kinerja Anda sudah baik, mari tingkatkan penyelesaian tugas-tugas yang masih berproses!";
                } else {
                    rekapStr += "Capaian kinerja Anda perlu peningkatan dan percepatan agar seluruh target tugas dapat rampung tepat waktu!";
                }

                voiceText += rekapStr;
            } else {
                voiceText += "Belum ada rincian uraian kerja yang ditampilkan pada tabel.";
            }

            toggleSpeech(voiceText, 'btn-voice-rencanakerja', 'Suara');
        });

        function toggleSpeech(text, btnId, defaultText) {
            if (!('speechSynthesis' in window)) {
                alert('Peramban Anda belum mendukung fitur narasi suara.');
                return;
            }

            const btn = $('#' + btnId);

            if (window.speechSynthesis.speaking && !window.speechSynthesis.paused) {
                window.speechSynthesis.cancel();
                btn.removeClass('btn-danger').addClass('btn-warning').html('<i class="bi bi-volume-up-fill me-1"></i> ' + defaultText);
                return;
            }

            window.speechSynthesis.cancel();

            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = 'id-ID';
            utterance.rate = 0.95;

            const voices = window.speechSynthesis.getVoices();
            const idVoice = voices.find(v => v.lang.includes('id') || v.lang.includes('ID'));
            if (idVoice) utterance.voice = idVoice;

            utterance.onstart = function() {
                btn.removeClass('btn-warning').addClass('btn-danger').html('<i class="bi bi-stop-fill me-1"></i> Stop');
            };

            utterance.onend = function() {
                btn.removeClass('btn-danger').addClass('btn-warning').html('<i class="bi bi-volume-up-fill me-1"></i> ' + defaultText);
            };

            utterance.onerror = function() {
                btn.removeClass('btn-danger').addClass('btn-warning').html('<i class="bi bi-volume-up-fill me-1"></i> ' + defaultText);
            };

            window.speechSynthesis.speak(utterance);
        }

        // Prevent Bootstrap 5 Modal focus trap from blocking Select2 search field input
        $(document).on('focusin', function(e) {
            if ($(e.target).closest('.select2-container').length) {
                e.stopImmediatePropagation();
            }
        });

        if ($('#filter-jabatan').length) {
            $('#filter-jabatan').select2({
                theme: 'bootstrap-5',
                placeholder: "Cari & pilih jabatan - nama...",
                allowClear: true
            });
        }

        var table = $('#rencanakerja-table').DataTable({
            processing: true,
            serverSide: true,
            stateSave: true,
            ordering: false,
            autoWidth: false,
            ajax: {
                url: "{{ app()->environment('production') ? str_replace('http:', 'https:', route('rencana-kerja.index')) : route('rencana-kerja.index') }}",
                data: function(d) {
                    if ($('#filter-jabatan').length) {
                        d.jabatan = $('#filter-jabatan').val();
                    }
                    d.status = $('#filter-status').val();
                }
            },
            columns: [
                { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false, className: 'text-center align-top pt-2', width: '28px' },
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-muted fw-bold text-center align-top pt-2 small', width: '32px' },
                { data: 'task_details', name: 'uraian_tugas', searchable: true, className: 'align-top p-0' },
            ],
            language: {
                search: "Cari Tugas:",
                lengthMenu: "Tampilkan _MENU_ data",
                zeroRecords: "Belum ada rencana kerja / checklist tugas untuk filter ini",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ tugas",
                infoEmpty: "Tidak ada data"
            },
            initComplete: function() {
                $('#selectAllToolbarRencanaKerja').insertBefore('#rencanakerja-table');
            }
        });

        table.on('xhr.dt', function(e, settings, json, xhr) {
            if (json && json.overall_rekap) {
                window.latestOverallRekap = json.overall_rekap;
                $('#count-status-all').text(json.overall_rekap.total || 0);
                $('#count-status-selesai').text(json.overall_rekap.selesai || 0);
                $('#count-status-proses').text(json.overall_rekap.proses || 0);
                $('#count-status-belum').text(json.overall_rekap.belum || 0);
            }
        });

        // Filter Status Dropdown Change
        $('#filter-status').on('change', function() {
            var val = $(this).val();
            $('.btn-filter-pill').removeClass('active');
            $('.btn-filter-pill[data-status="' + val + '"]').addClass('active');
            table.ajax.reload();
        });

        // Filter Status Pills Click
        $('.btn-filter-pill').on('click', function() {
            var status = $(this).data('status');
            $('.btn-filter-pill').removeClass('active');
            $(this).addClass('active');
            $('#filter-status').val(status);
            table.ajax.reload();
        });

        if ($('#filter-jabatan').length) {
            $('#filter-jabatan').on('change', function() {
                var selectedText = $(this).find('option:selected').text();
                var val = $(this).val();
                let ownTitle = "{{ auth()->user()->jabatan ? auth()->user()->jabatan . ' - ' . auth()->user()->name : auth()->user()->name }}";

                if (!val || val === '__own__') {
                    $('#judul-jabatan').text(ownTitle);
                } else if (val === '__all__' || val === 'all') {
                    $('#judul-jabatan').text('Semua Jabatan');
                } else {
                    $('#judul-jabatan').text(selectedText.trim());
                }

                if ($('#export_jabatan').length) {
                    $('#export_jabatan').val(val).trigger('change');
                }

                table.ajax.reload();
            });
        }

        // Multi Select & Bulk Delete State Management
        let selectedIds = new Set();

        function updateBulkDeleteButton() {
            let count = selectedIds.size;
            $('#selected-count').text(count);
            $('.selected-count-num').text(count);
            if (count > 0) {
                $('#btn-bulk-delete').removeClass('d-none');
                $('#selected-badge').removeClass('d-none');
            } else {
                $('#btn-bulk-delete').addClass('d-none');
                $('#selected-badge').addClass('d-none');
            }
        }

        // Toggle check-all
        $(document).on('change', '#check-all', function() {
            let isChecked = $(this).is(':checked');
            $('.select-row-checkbox').prop('checked', isChecked);
            $('.select-row-checkbox').each(function() {
                let val = parseInt($(this).val());
                if (isChecked) {
                    selectedIds.add(val);
                } else {
                    selectedIds.delete(val);
                }
            });
            updateBulkDeleteButton();
        });

        // Individual row checkbox change
        $(document).on('change', '.select-row-checkbox', function() {
            let val = parseInt($(this).val());
            if ($(this).is(':checked')) {
                selectedIds.add(val);
            } else {
                selectedIds.delete(val);
            }

            let totalOnPage = $('.select-row-checkbox').length;
            let checkedOnPage = $('.select-row-checkbox:checked').length;
            $('#check-all').prop('checked', totalOnPage > 0 && totalOnPage === checkedOnPage);
            $('#check-all').prop('indeterminate', checkedOnPage > 0 && checkedOnPage < totalOnPage);

            updateBulkDeleteButton();
        });

    function initTinyMCEEditors() {
        if (typeof tinymce !== 'undefined') {
            tinymce.remove('.tinymce-editor');
            tinymce.init({
                selector: '.tinymce-editor',
                height: 150,
                menubar: false,
                plugins: 'lists link table',
                toolbar: 'undo redo | bold italic underline | bullist numlist | removeformat',
                content_style: 'body { font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; font-size: 13px; }',
                setup: function (editor) {
                    editor.on('change blur', function () {
                        editor.save();
                    });
                }
            });
        }
    }

    // Sync checkbox status on table redraw (pagination, search, reload)
    table.on('draw', function() {
        let totalOnPage = $('.select-row-checkbox').length;
        let checkedOnPage = 0;

        $('.select-row-checkbox').each(function() {
            let val = parseInt($(this).val());
            if (selectedIds.has(val)) {
                $(this).prop('checked', true);
                checkedOnPage++;
            } else {
                $(this).prop('checked', false);
            }
        });

        $('#check-all').prop('checked', totalOnPage > 0 && totalOnPage === checkedOnPage);
        $('#check-all').prop('indeterminate', checkedOnPage > 0 && checkedOnPage < totalOnPage);

        if ($('#selectAllToolbarRencanaKerja').length && $('#rencanakerja-table').length) {
            $('#selectAllToolbarRencanaKerja').insertBefore('#rencanakerja-table');
        }

        updateBulkDeleteButton();
        setTimeout(initTinyMCEEditors, 150);
    });

    // Re-init TinyMCE when a task collapse is shown to ensure correct rendering dimensions
    $(document).on('shown.bs.collapse', function (e) {
        if (typeof tinymce !== 'undefined') {
            $(e.target).find('.tinymce-editor').each(function() {
                let id = $(this).attr('id');
                if (id) {
                    tinymce.remove('#' + id);
                    tinymce.init({
                        selector: '#' + id,
                        height: 150,
                        menubar: false,
                        plugins: 'lists link table',
                        toolbar: 'undo redo | bold italic underline | bullist numlist | removeformat',
                        content_style: 'body { font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; font-size: 13px; }',
                        setup: function (editor) {
                            editor.on('change blur', function () {
                                editor.save();
                            });
                        }
                    });
                }
            });
        }
    });

    // Toggle All Task Details (Buka Semua / Tutup Semua)
    let allTaskCardsExpanded = false;
    $('#btn-toggle-all-cards').on('click', function() {
        allTaskCardsExpanded = !allTaskCardsExpanded;
        if (allTaskCardsExpanded) {
            $('.collapse[id^="collapseTask_"]').collapse('show');
            $('#toggle-all-text').text('Tutup Semua');
            $(this).find('i').removeClass('bi-arrows-expand').addClass('bi-arrows-collapse');
        } else {
            $('.collapse[id^="collapseTask_"]').collapse('hide');
            $('#toggle-all-text').text('Buka Semua');
            $(this).find('i').removeClass('bi-arrows-collapse').addClass('bi-arrows-expand');
        }
    });

        // Quick Tag Modal Handlers
        let currentQuickTagId = null;

        if ($('#quick_tagged_users').length) {
            $('#quick_tagged_users').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#modalQuickTag'),
                placeholder: "Cari & pilih rekan kerja...",
                allowClear: true
            });
        }

        $(document).on('click', '.btn-quick-tag', function() {
            let btn = $(this);
            currentQuickTagId = btn.data('id');
            let uraian = btn.data('uraian');
            let tags = btn.data('tags'); // Array of user IDs

            $('#quick-tag-uraian').text(uraian);
            
            // Set select values
            $('#quick_tagged_users').val(tags).trigger('change');

            $('#modalQuickTag').modal('show');
        });

        $('#form-quick-tag').on('submit', function(e) {
            e.preventDefault();
            if (!currentQuickTagId) return;

            let form = $(this);
            let btn = form.find('.btn-save-quick-tag');
            let formData = form.serialize();

            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

            $.ajax({
                url: "{{ url('rencana-kerja') }}/" + currentQuickTagId + "/update-tags",
                type: 'POST',
                data: formData,
                success: function(response) {
                    btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan');
                    if (response.success) {
                        $('#modalQuickTag').modal('hide');
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: response.message,
                            showConfirmButton: false,
                            timer: 4000,
                            timerProgressBar: true
                        });
                        table.ajax.reload(null, false);
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan');
                    let msg = 'Terjadi kesalahan saat memperbarui rekan kerja.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: msg
                    });
                }
            });
        });

        // Handle Bulk Delete Click
        $(document).on('click', '#btn-bulk-delete', function() {
            let ids = Array.from(selectedIds);
            if (ids.length === 0) return;

            Swal.fire({
                title: 'Hapus Rencana Kerja Terpilih?',
                text: 'Apakah Anda yakin ingin menghapus ' + ids.length + ' data rencana kerja terpilih ini secara permanen?',
                icon: 'warning',
                showCancelButton: true,
                confirmColor: '#d33',
                cancelColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-trash-fill me-1"></i> Ya, Hapus ' + ids.length + ' Data',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('rencana-kerja.bulk-delete') }}",
                        type: 'POST',
                        data: {
                            "_token": "{{ csrf_token() }}",
                            "ids": ids
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'success',
                                    title: response.message,
                                    showConfirmButton: false,
                                    timer: 4000,
                                    timerProgressBar: true
                                });
                                selectedIds.clear();
                                updateBulkDeleteButton();
                                $('#check-all').prop('checked', false).prop('indeterminate', false);
                                $('#rencanakerja-table').DataTable().ajax.reload(null, false);
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Terjadi kesalahan saat menghapus data terpilih.'
                            });
                        }
                    });
                }
            });
        });
    });

    $(document).on('click', '.btn-start-timer', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        if (id) window.startTimer(id);
    });

    $(document).on('click', '.btn-stop-timer', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        if (id) window.stopTimer(id);
    });

    $(document).on('click', '.btn-delete-timer', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        if (id) window.deleteRencanaKerja(id);
    });

    // Handle inline file and url_external upload form submit
    $(document).on('submit', '.form-inline-upload', function(e) {
        e.preventDefault();
        if (typeof tinymce !== 'undefined') {
            tinymce.triggerSave();
        }
        var form = $(this);
        var id = form.data('id');
        var btn = form.find('.btn-simpan-inline');
        var formData = new FormData(this);
        formData.append('_token', "{{ csrf_token() }}");

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>');

        $.ajax({
            url: "{{ url('rencana-kerja') }}/" + id + "/upload-attachment",
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                btn.prop('disabled', false).html('<i class="bi bi-cloud-arrow-up-fill me-1"></i> Simpan');
                if (response.success) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: response.message,
                        showConfirmButton: false,
                        timer: 4000,
                        timerProgressBar: true
                    });
                    $('#rencanakerja-table').DataTable().ajax.reload(null, false);
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="bi bi-cloud-arrow-up-fill me-1"></i> Simpan');
                var msg = 'Terjadi kesalahan saat menyimpan berkas/link.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Menyimpan',
                    text: msg
                });
            }
        });
    });

    // Get current laptop time formatted as HH:mm:ss and date YYYY-MM-DD
    window.getLaptopDateTime = function() {
        var now = new Date();
        var year = now.getFullYear();
        var month = String(now.getMonth() + 1).padStart(2, '0');
        var day = String(now.getDate()).padStart(2, '0');
        var hours = String(now.getHours()).padStart(2, '0');
        var minutes = String(now.getMinutes()).padStart(2, '0');
        var seconds = String(now.getSeconds()).padStart(2, '0');

        return {
            date: year + '-' + month + '-' + day,
            time: hours + ':' + minutes + ':' + seconds
        };
    };

    window.startTimer = function(id) {
        var dt = window.getLaptopDateTime();
        
        Swal.fire({
            title: 'Konfirmasi Pelaksanaan Tugas',
            text: 'Waktu mulai pelaksanaan tugas akan dicatat secara otomatis sesuai waktu sistem Anda (' + dt.time + ')',
            icon: 'question',
            showCancelButton: true,
            confirmColor: '#15432d',
            cancelColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-play-fill"></i> Ya, Mulai Pelaksanaan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('rencana-kerja') }}/" + id + "/start",
                    type: 'POST',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "client_time": dt.time,
                        "client_date": dt.date
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: response.message,
                                showConfirmButton: false,
                                timer: 4000,
                                timerProgressBar: true
                            });
                            $('#rencanakerja-table').DataTable().ajax.reload(null, false);
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Terjadi kesalahan saat mencatat waktu mulai.'
                        });
                    }
                });
            }
        });
    };

    window.stopTimer = function(id) {
        var dt = window.getLaptopDateTime();

        Swal.fire({
            title: 'Konfirmasi Penyelesaian Tugas',
            text: 'Waktu penyelesaian tugas akan dicatat secara otomatis sesuai waktu sistem Anda (' + dt.time + ')',
            icon: 'question',
            showCancelButton: true,
            confirmColor: '#8b0000',
            cancelColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-stop-fill"></i> Ya, Selesaikan Tugas',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('rencana-kerja') }}/" + id + "/stop",
                    type: 'POST',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "client_time": dt.time,
                        "client_date": dt.date
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: response.message,
                                showConfirmButton: false,
                                timer: 4000,
                                timerProgressBar: true
                            });
                            $('#rencanakerja-table').DataTable().ajax.reload(null, false);
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Terjadi kesalahan saat mencatat waktu selesai.'
                        });
                    }
                });
            }
        });
    };

    window.deleteRencanaKerja = function(id) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data rencana kerja ini akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmColor: '#d33',
            cancelColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('rencana-kerja') }}/" + id,
                    type: 'DELETE',
                    data: {
                        "_token": "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: response.message,
                                showConfirmButton: false,
                                timer: 4000,
                                timerProgressBar: true
                            });
                            $('#rencanakerja-table').DataTable().ajax.reload(null, false);
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Terjadi kesalahan saat menghapus data.'
                        });
                    }
                });
            }
        });
    };
</script>
@if(isset($dataTable))
    @if(app()->environment('production'))
        {!! str_replace('http:', 'https:', $dataTable->scripts()) !!}
    @else
        {!! $dataTable->scripts() !!}
    @endif
@endif
@endpush
