@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h1 class="fw-bold mb-1" style="color: #046B26; font-size: 1.6rem; letter-spacing: -0.3px;">
            <i class="bi bi-people-fill me-2" style="color: #046B26;"></i>Pemantauan Staff &amp; Tim
        </h1>
        <nav>
            <ol class="breadcrumb mb-0" style="font-size: 0.85rem;">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item">Monitoring &amp; Evaluasi</li>
                <li class="breadcrumb-item active">Pemantauan Staff</li>
            </ol>
        </nav>
    </div>

    <!-- Export Actions -->
    <div class="d-flex align-items-center gap-2">
        <a id="btnExportExcel" href="{{ route('pemantauan-staff.export-excel', request()->all()) }}" 
           class="btn btn-sm btn-success fw-semibold d-inline-flex align-items-center gap-2 shadow-xs px-3 py-2 text-white rounded-2" 
           style="background-color: #046B26; border-color: #046B26; font-size: 0.84rem;">
            <i class="bi bi-file-earmark-excel-fill"></i>
            <span>Export Excel</span>
        </a>
        <a id="btnExportPdf" href="{{ route('pemantauan-staff.export-pdf', request()->all()) }}" 
           class="btn btn-sm btn-outline-secondary bg-white text-dark fw-semibold d-inline-flex align-items-center gap-2 shadow-xs px-3 py-2 rounded-2 border" 
           style="border-color: #cbd5e1; font-size: 0.84rem;">
            <i class="bi bi-file-earmark-pdf-fill text-danger"></i>
            <span>Export PDF</span>
        </a>
    </div>
</div>

<section class="section">

    <!-- Banner Info Scope OTK -->
    @if($pimpinanTerpilih)
        <div class="card card-scope mb-4 p-3.5">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center text-white shadow-2xs flex-shrink-0" 
                         style="width: 44px; height: 44px; background: linear-gradient(135deg, #046B26, #15432D); font-size: 1.2rem;">
                        <i class="bi bi-diagram-3-fill"></i>
                    </div>
                    <div>
                        <div class="text-uppercase fw-bold text-muted" style="letter-spacing: 0.6px; font-size: 0.70rem; color: #64748b !important;">
                            Lingkup Pemantauan Tim Sesuai Hierarki OTK
                        </div>
                        <div class="fw-bold text-dark mt-0.5" style="font-size: 0.98rem;">
                            {{ $pimpinanTerpilih->name }} <span class="text-secondary fw-normal">({{ $pimpinanTerpilih->jabatan ?: 'Pimpinan' }})</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light text-dark border border-success-subtle px-3 py-2 rounded-pill fw-semibold shadow-2xs d-inline-flex align-items-center gap-1.5" 
                          style="font-size: 0.80rem; color: #046B26 !important;">
                        <i class="bi bi-people-fill text-success"></i>
                        <span>{{ count($pegawaiData) }} Anggota Tim Terhubung</span>
                    </span>
                    @if(request()->has('supervisor_id') && Auth::user()->subordinates()->exists())
                        <a href="{{ route('pemantauan-staff.index') }}" class="btn btn-sm btn-outline-secondary py-1.5 px-3 rounded-2 fw-semibold bg-white shadow-2xs" style="font-size: 0.78rem;">
                            <i class="bi bi-arrow-left me-1"></i>Kembali ke Tim Saya
                        </a>
                    @elseif(request()->has('supervisor_id'))
                        <a href="{{ route('pemantauan-staff.index', ['supervisor_id' => 'all']) }}" class="btn btn-sm btn-outline-secondary py-1.5 px-3 rounded-2 fw-semibold bg-white shadow-2xs" style="font-size: 0.78rem;">
                            Lihat Seluruh Personel OTK
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @elseif(!($isLeader ?? false))
        <div class="card card-scope mb-4 p-3.5">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center text-white shadow-2xs flex-shrink-0" 
                         style="width: 44px; height: 44px; background: linear-gradient(135deg, #046B26, #15432D); font-size: 1.2rem;">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <div>
                        <div class="text-uppercase fw-bold text-muted" style="letter-spacing: 0.6px; font-size: 0.70rem; color: #64748b !important;">
                            Pemantauan Kinerja Individu Sesuai Penempatan OTK
                        </div>
                        <div class="fw-bold text-dark mt-0.5" style="font-size: 0.98rem;">
                            {{ Auth::user()->name }} <span class="text-secondary fw-normal">({{ Auth::user()->jabatan ?: 'Pegawai' }} - {{ Auth::user()->unit ?: 'Universitas Ibnu Sina' }})</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light text-dark border border-success-subtle px-3 py-2 rounded-pill fw-semibold shadow-2xs d-inline-flex align-items-center gap-1.5" 
                          style="font-size: 0.80rem; color: #046B26 !important;">
                        <i class="bi bi-person-check text-success"></i>
                        <span>Kinerja Pribadi</span>
                    </span>
                </div>
            </div>
        </div>
    @endif

    <!-- Filter Toolbar -->
    <div class="card card-filter mb-4">
        <div class="card-body p-3 p-md-3.5">
            <form action="{{ route('pemantauan-staff.index') }}" method="GET" id="formFilterPemantauan">
                <div class="row g-3 align-items-end">

                    <!-- Search Box -->
                    <div class="col-md-7">
                        <label class="form-label small fw-bold text-dark mb-1.5 d-flex align-items-center gap-1.5" style="font-size: 0.82rem;">
                            <i class="bi bi-search text-secondary"></i>
                            <span>Cari Anggota Tim / Staf</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted ps-3" style="border-color: #cbd5e1;">
                                <i class="bi bi-search text-secondary"></i>
                            </span>
                            <input type="text" name="search" id="inputSearchPemantauan" 
                                   class="form-control border-start-0 ps-1" 
                                   placeholder="Ketik nama pegawai, jabatan, NUPTK/NUP..." 
                                   value="{{ request('search') }}"
                                   style="font-size: 0.86rem; border-color: #cbd5e1;">
                        </div>
                    </div>

                    <!-- Periode Bulan & Tahun Navigator -->
                    <div class="col-md-5">
                        <label class="form-label small fw-bold text-dark mb-1.5 d-flex align-items-center gap-1.5" style="font-size: 0.82rem;">
                            <i class="bi bi-calendar3 text-secondary"></i>
                            <span>Periode Pemantauan</span>
                        </label>
                        @php
                            $daftarBulanNama = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
                        @endphp
                        <div class="input-group">
                            <button type="button" id="btnPrevMonth" class="btn btn-outline-secondary bg-white px-2.5 border" 
                                    style="border-color: #cbd5e1;" title="Bulan Sebelumnya" 
                                    data-month="{{ $prevDate->month }}" data-year="{{ $prevDate->year }}">
                                <i class="bi bi-chevron-left"></i>
                            </button>

                            <select name="bulan" id="selectBulan" class="form-select fw-semibold border-start-0 border-end-0 text-center" 
                                    style="font-size: 0.86rem; border-color: #cbd5e1; color: #0f172a;">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                                        {{ $daftarBulanNama[$m] }}
                                    </option>
                                @endfor
                            </select>

                            <select name="tahun" id="selectTahun" class="form-select fw-semibold border-end-0 text-center" 
                                    style="max-width: 95px; font-size: 0.86rem; border-color: #cbd5e1; color: #0f172a;">
                                @for($y = now()->year - 2; $y <= now()->year + 2; $y++)
                                    <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>

                            <button type="button" id="btnNextMonth" class="btn btn-outline-secondary bg-white px-2.5 border" 
                                    style="border-color: #cbd5e1;" title="Bulan Berikutnya" 
                                    data-month="{{ $nextDate->month }}" data-year="{{ $nextDate->year }}">
                                <i class="bi bi-chevron-right"></i>
                            </button>
                        </div>
                    </div>

                </div>

                @if(request()->hasAny(['search', 'unit', 'status_pencatatan']))
                    <div class="mt-2.5 pt-2 border-top d-flex align-items-center justify-content-between">
                        <span class="small text-muted">
                            <i class="bi bi-info-circle me-1"></i>Menampilkan hasil filter khusus
                        </span>
                        <a href="{{ route('pemantauan-staff.index', ['bulan' => $bulan, 'tahun' => $tahun]) }}" class="small text-danger text-decoration-none fw-semibold">
                            <i class="bi bi-x-circle me-1"></i>Reset Filter
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <!-- Main Table Card -->
    <div id="tableContainerWrapper" class="position-relative">
        <div id="tableLoadingOverlay" class="position-absolute top-0 start-0 w-100 h-100 d-none bg-white bg-opacity-75 d-flex align-items-center justify-content-center rounded-4" style="z-index: 10; backdrop-filter: blur(2px);">
            <div class="text-center py-4">
                <div class="spinner-border text-success" role="status" style="width: 2rem; height: 2rem;">
                    <span class="visually-hidden">Memuat data...</span>
                </div>
                <div class="small fw-semibold text-secondary mt-2">Memuat data kinerja...</div>
            </div>
        </div>
        @include('pages.pemantauan-staff.partials.table')
    </div>

</section>

<!-- Modal Pantau Detail Kegiatan Staff -->
<div class="modal fade" id="modalPantauStaff" tabindex="-1" aria-labelledby="modalPantauStaffLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <!-- Modal Header -->
            <div class="modal-header text-white px-4 py-3" style="background: linear-gradient(135deg, #15432D 0%, #1e5c3e 100%);">
                <div class="d-flex align-items-center gap-3">
                    <div id="modalStaffAvatar" class="avatar-circle rounded-circle text-white d-flex align-items-center justify-content-center fw-bold shadow-sm me-3" style="width: 44px; height: 44px; min-width: 44px; font-size: 14px; background: #047857; margin-right: 14px !important;">
                        ST
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold text-white mb-0" id="modalStaffName">Nama Pegawai</h6>
                        <small class="text-white-50" id="modalStaffSub">Jabatan &bull; Unit</small>
                        <div class="text-white-50" style="font-size: 0.72rem;" id="modalStaffAtasan">Atasan: -</div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Sub-Header Stats -->
            <div class="px-4 py-2.5 bg-light border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2" style="font-size: 0.82rem;">
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted">Periode:</span>
                    <strong class="text-dark" id="modalPeriodeText">{{ $namaBulanIndonesia }} {{ $tahun }}</strong>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div>Tugas Mandiri: <strong class="text-dark" id="modalTotalTugas">0</strong></div>
                    <div>Total Jam Kerja: <strong class="text-success" id="modalTotalJam">0 Jam</strong></div>
                    <div>Selesai: <strong class="text-primary" id="modalSelesai">0</strong></div>
                    <div id="modalTaggedContainer" class="d-none">Ditandai (CC): <strong class="text-info" id="modalTaggedCount">0</strong></div>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4">
                <div id="modalLoadingSpinner" class="text-center py-5">
                    <div class="spinner-border text-success" role="status" style="width: 2.5rem; height: 2.5rem;">
                        <span class="visually-hidden">Memuat data...</span>
                    </div>
                    <div class="small text-muted mt-2 fw-semibold">Memuat rincian kegiatan staf...</div>
                </div>

                <div id="modalContentContainer" style="display: none;">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0" style="font-size: 0.82rem;">
                            <thead class="table-light text-secondary">
                                <tr>
                                    <th style="width: 30px;">#</th>
                                    <th>Uraian Tugas / Kegiatan</th>
                                    <th style="width: 110px;">Kategori</th>
                                    <th style="width: 90px;" class="text-center">Durasi</th>
                                    <th style="width: 95px;" class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody id="modalTaskListBody">
                                <!-- Populated dynamically via JS -->
                            </tbody>
                        </table>
                    </div>

                    <div id="modalEmptyNotice" class="text-center py-5 d-none">
                        <i class="bi bi-journal-x fs-1 text-muted d-block mb-2"></i>
                        <h6 class="text-muted fw-semibold">Belum ada catatan kegiatan pada bulan ini.</h6>
                        <small class="text-muted">Pegawai belum menambahkan kegiatan untuk periode terpilih.</small>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer bg-light px-4 py-2.5">
                <div class="me-auto" id="modalWaContainer">
                    <!-- WhatsApp link inserted dynamically -->
                </div>
                <button type="button" class="btn btn-sm btn-secondary px-3" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Styling Pemantauan Staff: Border & Shadow Tegas & Jelas */
    .card-scope {
        border: 1px solid #c2e2cc !important;
        border-left: 5px solid #046B26 !important;
        border-radius: 12px !important;
        box-shadow: 0 4px 14px rgba(4, 107, 38, 0.08), 0 1px 3px rgba(0, 0, 0, 0.04) !important;
        background-color: #ffffff !important;
    }
    .card-filter {
        border: 1px solid #cbd5e1 !important;
        border-radius: 12px !important;
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.07), 0 1px 3px rgba(15, 23, 42, 0.04) !important;
        background-color: #ffffff !important;
    }
    .card-table {
        border: 1px solid #cbd5e1 !important;
        border-radius: 12px !important;
        box-shadow: 0 6px 20px rgba(15, 23, 42, 0.08), 0 2px 6px rgba(15, 23, 42, 0.04) !important;
        background-color: #ffffff !important;
        overflow: hidden !important;
    }
    .card-filter:hover, .card-table:hover {
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.10), 0 2px 8px rgba(15, 23, 42, 0.05) !important;
    }
    .pemantauan-table tbody tr.pegawai-main-row:hover {
        background-color: #f1f8f4 !important;
    }
    .pemantauan-table th {
        font-weight: 600;
        letter-spacing: 0.3px;
    }
    .shadow-xs {
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06) !important;
    }
    .shadow-2xs {
        box-shadow: 0 1px 4px rgba(15, 23, 42, 0.04) !important;
    }
    .form-control:focus, .form-select:focus {
        border-color: #046B26 !important;
        box-shadow: 0 0 0 0.2rem rgba(4, 107, 38, 0.15) !important;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let currentBulan = {{ $bulan }};
    let currentTahun = {{ $tahun }};
    let currentNamaBulan = "{{ $namaBulanIndonesia }}";
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    const modalEl = document.getElementById('modalPantauStaff');
    const modal = modalEl ? new bootstrap.Modal(modalEl) : null;

    const selectBulan = document.getElementById('selectBulan');
    const selectTahun = document.getElementById('selectTahun');
    const btnPrevMonth = document.getElementById('btnPrevMonth');
    const btnNextMonth = document.getElementById('btnNextMonth');
    const formFilter = document.getElementById('formFilterPemantauan');
    const inputSearch = document.getElementById('inputSearchPemantauan');
    const kpiWrapper = document.getElementById('kpiContainerWrapper');
    const tableWrapper = document.getElementById('tableContainerWrapper');
    const btnExportExcel = document.getElementById('btnExportExcel');
    const btnExportPdf = document.getElementById('btnExportPdf');

    // ─────────────────────────────────────────────────────────────────────────
    // Load & Render Sub-Tabel Rincian Pekerjaan di Dalam Baris Tabel
    // ─────────────────────────────────────────────────────────────────────────
    function loadStaffDetailRow(userId, forceReload = false) {
        const detailRow = document.getElementById(`detail-row-${userId}`);
        const contentWrapper = document.getElementById(`detail-content-${userId}`);
        if (!detailRow || !contentWrapper) return;

        if (!forceReload && contentWrapper.getAttribute('data-loaded') === 'true') {
            return;
        }

        contentWrapper.innerHTML = `
            <div class="text-center py-4 text-muted">
                <div class="spinner-border spinner-border-sm text-success me-2" role="status"></div>
                <span class="small fw-semibold">Memuat rincian pekerjaan dan berkas pendukung...</span>
            </div>
        `;

        const targetUrl = `{{ url('pemantauan-staff/detail') }}/${userId}?bulan=${currentBulan}&tahun=${currentTahun}`;

        fetch(targetUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                contentWrapper.innerHTML = `<div class="alert alert-danger py-2 small mb-0"><i class="bi bi-exclamation-circle me-1"></i>Gagal memuat rincian data staf.</div>`;
                return;
            }

            const p = data.pegawai;
            let waHtml = '';
            if (p.nomor_wa) {
                let cleanWa = p.nomor_wa.replace(/\D/g, '');
                if (cleanWa.startsWith('0')) cleanWa = '62' + cleanWa.substring(1);
                const msg = encodeURIComponent(`Halo ${p.name}, saya sedang meninjau rincian kinerja Anda untuk periode ${currentNamaBulan} ${currentTahun}.`);
                waHtml = `
                    <a href="https://wa.me/${cleanWa}?text=${msg}" target="_blank" class="btn btn-sm btn-outline-success d-inline-flex align-items-center gap-1.5 px-2.5 py-1" style="font-size: 0.76rem;" title="Hubungi Pegawai via WhatsApp">
                        <i class="bi bi-whatsapp text-success"></i> WhatsApp
                    </a>
                `;
            }

            let taskRowsHtml = '';
            if (!data.tasks || data.tasks.length === 0) {
                taskRowsHtml = `
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox display-6 d-block mb-2 text-secondary opacity-50"></i>
                            <span class="small fw-semibold">Belum ada catatan pekerjaan/tugas di periode ${currentNamaBulan} ${currentTahun}.</span>
                        </td>
                    </tr>
                `;
            } else {
                data.tasks.forEach((t, idx) => {
                    // Status Badge Styling
                    let statusBadge = '';
                    if (t.status === 'Disetujui') {
                        statusBadge = `<span class="badge bg-success text-white px-2 py-1"><i class="bi bi-check-circle-fill me-1"></i>Disetujui</span>`;
                    } else if (t.status === 'Perlu Revisi') {
                        statusBadge = `<span class="badge bg-danger text-white px-2 py-1"><i class="bi bi-exclamation-triangle-fill me-1"></i>Perlu Revisi</span>`;
                    } else if (t.status === 'Menunggu Validasi') {
                        statusBadge = `<span class="badge bg-warning text-dark px-2 py-1"><i class="bi bi-hourglass-split me-1"></i>Menunggu</span>`;
                    } else if (t.status === 'Selesai') {
                        statusBadge = `<span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="bi bi-check2 me-1"></i>Selesai</span>`;
                    } else {
                        statusBadge = `<span class="badge bg-primary-subtle text-primary border px-2 py-1">${t.status}</span>`;
                    }

                    // Bukti Dukung pills
                    let buktiHtml = '';
                    if (t.file_url) {
                        buktiHtml += `
                            <a href="${t.file_url}" target="_blank" class="badge bg-light text-primary border text-decoration-none d-inline-flex align-items-center gap-1 me-1 py-1 px-1.5" title="Unduh Bukti Lampiran">
                                <i class="bi bi-paperclip"></i> Dokumen
                            </a>
                        `;
                    }
                    if (t.url_external) {
                        buktiHtml += `
                            <a href="${t.url_external}" target="_blank" class="badge bg-light text-info border text-decoration-none d-inline-flex align-items-center gap-1 me-1 py-1 px-1.5" title="Buka Tautan Output">
                                <i class="bi bi-box-arrow-up-right"></i> Link
                            </a>
                        `;
                    }

                    // Hasil kerja note
                    let hasilKerjaHtml = '';
                    if (t.hasil_kerja && t.hasil_kerja !== '-') {
                        hasilKerjaHtml = `
                            <div class="small text-muted mt-1 fst-italic" style="font-size: 0.74rem;">
                                <i class="bi bi-arrow-return-right text-success me-1"></i>Output: ${t.hasil_kerja}
                            </div>
                        `;
                    }

                    // Tagged / CC badge
                    let tagBadge = '';
                    let durasiHtml = `<span class="badge bg-light text-dark border fw-bold"><i class="bi bi-clock me-1 text-primary"></i>${t.durasi_jam} Jam</span>`;
                    if (!t.is_owner) {
                        tagBadge = `<span class="badge bg-info-subtle text-info border ms-1" style="font-size: 0.70rem;"><i class="bi bi-info-circle me-1"></i>Ditandai (CC)</span>`;
                        durasiHtml = `<span class="text-muted small">0 Jam</span><br><small class="text-secondary" style="font-size: 0.68rem;">(Info Rekan)</small>`;
                    }

                    // Catatan validasi sebelumnya
                    let catatanHtml = '';
                    if (t.catatan_validasi) {
                        catatanHtml = `
                            <div class="small mt-1 text-danger bg-danger-subtle border border-danger-subtle p-1 px-2 rounded" style="font-size: 0.72rem;">
                                <i class="bi bi-chat-left-dots-fill me-1"></i><strong>Catatan Atasan:</strong> ${t.catatan_validasi}
                            </div>
                        `;
                    }

                    // Kolom Aksi Validasi Atasan
                    let actionHtml = '';
                    if (!t.is_owner) {
                        actionHtml = `<span class="text-muted small fst-italic" style="font-size: 0.72rem;">Info Rekan</span>`;
                    } else if (t.can_validate) {
                        if (t.status === 'Disetujui') {
                            actionHtml = `
                                <div class="d-flex align-items-center justify-content-center gap-1">
                                    <span class="text-success small fw-semibold" style="font-size: 0.74rem;" title="${t.validated_at ? 'Divalidasi pada ' + t.validated_at : 'Telah Divalidasi'}">
                                        <i class="bi bi-patch-check-fill me-1"></i>Valid
                                    </span>
                                    <button type="button" class="btn btn-xs btn-outline-danger py-0.5 px-1.5 rounded btn-task-revise" data-task-id="${t.id}" data-task-name="${encodeURIComponent(t.uraian_tugas)}" data-user-id="${p.id}" title="Minta Revisi Tugas Ini">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                </div>
                            `;
                        } else {
                            actionHtml = `
                                <div class="d-flex align-items-center justify-content-center gap-1">
                                    <button type="button" class="btn btn-sm btn-success fw-bold d-inline-flex align-items-center gap-1 py-1 px-2 rounded btn-task-approve shadow-2xs" data-task-id="${t.id}" data-user-id="${p.id}" style="background-color: #046B26; border-color: #046B26; font-size: 0.72rem;">
                                        <i class="bi bi-check-lg"></i> Setujui
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger fw-semibold d-inline-flex align-items-center gap-1 py-1 px-1.5 rounded btn-task-revise shadow-2xs" data-task-id="${t.id}" data-task-name="${encodeURIComponent(t.uraian_tugas)}" data-user-id="${p.id}" style="font-size: 0.72rem;">
                                        <i class="bi bi-pencil-square"></i> Revisi
                                    </button>
                                </div>
                            `;
                        }
                    } else {
                        actionHtml = `<span class="text-muted small">-</span>`;
                    }

                    // Checkbox enable/disable
                    const canCheck = t.is_owner && t.can_validate && t.status !== 'Disetujui';
                    const checkHtml = canCheck 
                        ? `<input type="checkbox" class="form-check-input task-check-item" data-user-id="${p.id}" value="${t.id}">`
                        : `<input type="checkbox" class="form-check-input" disabled>`;

                    taskRowsHtml += `
                        <tr id="task-row-${t.id}" class="${!t.is_owner ? 'bg-light bg-opacity-75' : ''}">
                            <td class="text-center">${checkHtml}</td>
                            <td class="text-center text-muted">${idx + 1}</td>
                            <td>
                                <div class="fw-semibold text-dark mb-0.5">${t.uraian_tugas} ${tagBadge}</div>
                                <div class="d-flex flex-wrap align-items-center gap-1 mt-0.5">
                                    ${buktiHtml}
                                </div>
                                ${hasilKerjaHtml}
                                ${catatanHtml}
                            </td>
                            <td><span class="badge bg-light text-dark border">${t.kategori}</span></td>
                            <td class="text-center">${durasiHtml}</td>
                            <td class="text-center small text-secondary">${t.tanggal_mulai}</td>
                            <td class="text-center" id="task-status-cell-${t.id}">${statusBadge}</td>
                            <td class="text-center" id="task-action-cell-${t.id}">${actionHtml}</td>
                        </tr>
                    `;
                });
            }

            contentWrapper.setAttribute('data-loaded', 'true');
            contentWrapper.innerHTML = `
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 p-3 bg-white rounded-3 border shadow-2xs">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar-circle rounded-circle text-white d-flex align-items-center justify-content-center fw-bold shadow-xs flex-shrink-0" style="width: 38px; height: 38px; background-color: ${p.avatar_color};">
                            ${p.avatar_initial}
                        </div>
                        <div>
                            <div class="fw-bold text-dark d-flex align-items-center flex-wrap gap-2" style="font-size: 0.95rem;">
                                <span>Rincian Pekerjaan: ${p.name}</span>
                                <span class="badge bg-light text-dark border" style="font-size: 0.72rem;">Periode: ${currentNamaBulan} ${currentTahun}</span>
                            </div>
                            <div class="small text-muted mt-0.5">
                                <span class="me-2">Tugas Mandiri: <strong class="text-dark">${data.total_tasks}</strong></span>
                                <span class="me-2">Total Jam: <strong class="text-success">${data.total_jam} Jam</strong></span>
                                <span class="me-2">Disetujui: <strong class="text-primary">${data.disetujui_count || 0}</strong></span>
                                ${data.revisi_count > 0 ? `<span class="me-2 text-danger">Revisi: <strong>${data.revisi_count}</strong></span>` : ''}
                                ${data.tagged_count > 0 ? `<span class="text-info"><i class="bi bi-info-circle me-1"></i>Ditandai (CC): <strong>${data.tagged_count}</strong></span>` : ''}
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <button type="button" class="btn btn-sm btn-success fw-semibold d-inline-flex align-items-center gap-1.5 shadow-2xs btn-batch-approve" data-user-id="${p.id}" style="background-color: #046B26; border-color: #046B26; font-size: 0.76rem;">
                            <i class="bi bi-check2-all"></i> Setujui Tugas Terpilih
                        </button>
                        ${waHtml}
                        <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 btn-refresh-detail" data-user-id="${p.id}" title="Muat Ulang Rincian Pekerjaan">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>
                </div>

                <div class="table-responsive bg-white rounded-3 border shadow-2xs">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.82rem;">
                        <thead class="table-light text-secondary" style="font-size: 0.78rem;">
                            <tr>
                                <th class="text-center" style="width: 38px;">
                                    <input type="checkbox" class="form-check-input check-all-tasks" data-user-id="${p.id}" title="Pilih Semua yang Belum Divalidasi">
                                </th>
                                <th class="text-center" style="width: 38px;">#</th>
                                <th style="min-width: 280px;">Uraian Tugas &amp; Bukti Pengerjaan</th>
                                <th style="width: 135px;">Kategori</th>
                                <th class="text-center" style="width: 105px;">Durasi Jam</th>
                                <th class="text-center" style="width: 105px;">Tanggal</th>
                                <th class="text-center" style="width: 125px;">Status</th>
                                <th class="text-center" style="width: 155px;">Aksi Validasi Atasan</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${taskRowsHtml}
                        </tbody>
                    </table>
                </div>
            `;

            // Bind Event Checkbox Select All
            const checkAll = contentWrapper.querySelector('.check-all-tasks');
            if (checkAll) {
                checkAll.addEventListener('change', function () {
                    const checkboxes = contentWrapper.querySelectorAll('.task-check-item:not(:disabled)');
                    checkboxes.forEach(cb => { cb.checked = this.checked; });
                });
            }

            // Bind Tombol Batch Approve
            const btnBatch = contentWrapper.querySelector('.btn-batch-approve');
            if (btnBatch) {
                btnBatch.addEventListener('click', function () {
                    const checkedItems = contentWrapper.querySelectorAll('.task-check-item:checked');
                    let taskIds = Array.from(checkedItems).map(cb => parseInt(cb.value));

                    if (taskIds.length === 0) {
                        const allUnapproved = contentWrapper.querySelectorAll('.task-check-item:not(:disabled)');
                        if (allUnapproved.length === 0) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire('Info', 'Semua tugas staf ini telah disetujui atau tidak ada tugas yang dapat divalidasi.', 'info');
                            } else {
                                alert('Semua tugas staf ini telah disetujui.');
                            }
                            return;
                        }
                        taskIds = Array.from(allUnapproved).map(cb => parseInt(cb.value));
                    }

                    const confirmFn = () => {
                        btnBatch.disabled = true;
                        btnBatch.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memproses...';

                        fetch("{{ route('pemantauan-staff.batch-validate') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                ids: taskIds,
                                action: 'approve'
                            })
                        })
                        .then(res => res.json())
                        .then(resData => {
                            if (resData.success) {
                                if (typeof Swal !== 'undefined') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil Divalidasi!',
                                        text: resData.message || `${taskIds.length} tugas berhasil disetujui.`,
                                        confirmButtonColor: '#046B26'
                                    });
                                }
                                loadStaffDetailRow(userId, true);
                            } else {
                                if (typeof Swal !== 'undefined') {
                                    Swal.fire('Gagal', resData.message || 'Gagal memvalidasi tugas.', 'error');
                                } else {
                                    alert(resData.message || 'Gagal.');
                                }
                                btnBatch.disabled = false;
                                btnBatch.innerHTML = '<i class="bi bi-check2-all"></i> Setujui Tugas Terpilih';
                            }
                        })
                        .catch(err => {
                            btnBatch.disabled = false;
                            btnBatch.innerHTML = '<i class="bi bi-check2-all"></i> Setujui Tugas Terpilih';
                            if (typeof Swal !== 'undefined') {
                                Swal.fire('Error', 'Terjadi kesalahan sistem saat memproses validasi.', 'error');
                            }
                        });
                    };

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: `Setujui ${taskIds.length} Tugas?`,
                            text: `Tugas terpilih dari ${p.name} akan divalidasi dan disetujui sekaligus.`,
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#046B26',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Ya, Setujui',
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                confirmFn();
                            }
                        });
                    } else if (confirm(`Setujui ${taskIds.length} tugas terpilih?`)) {
                        confirmFn();
                    }
                });
            }

            // Bind Tombol Single Approve
            contentWrapper.querySelectorAll('.btn-task-approve').forEach(btn => {
                btn.addEventListener('click', function () {
                    const taskId = this.getAttribute('data-task-id');
                    this.disabled = true;
                    this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

                    fetch(`{{ url('pemantauan-staff/validate-task') }}/${taskId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ action: 'approve' })
                    })
                    .then(res => res.json())
                    .then(resData => {
                        if (resData.success) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'success',
                                    title: 'Tugas berhasil disetujui!',
                                    showConfirmButton: false,
                                    timer: 2000
                                });
                            }
                            loadStaffDetailRow(userId, true);
                        } else {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire('Gagal', resData.message || 'Gagal menyetujui tugas.', 'error');
                            }
                            btn.disabled = false;
                            btn.innerHTML = '<i class="bi bi-check-lg"></i> Setujui';
                        }
                    })
                    .catch(err => {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="bi bi-check-lg"></i> Setujui';
                        if (typeof Swal !== 'undefined') {
                            Swal.fire('Error', 'Gagal memproses validasi tugas.', 'error');
                        }
                    });
                });
            });

            // Bind Tombol Single Revise (Minta Revisi)
            contentWrapper.querySelectorAll('.btn-task-revise').forEach(btn => {
                btn.addEventListener('click', function () {
                    const taskId = this.getAttribute('data-task-id');
                    const taskName = decodeURIComponent(this.getAttribute('data-task-name') || 'Tugas');

                    if (typeof Swal === 'undefined') {
                        const note = prompt(`Tuliskan alasan/catatan perbaikan untuk tugas:\n"${taskName}"`);
                        if (!note) return;
                        submitRevision(taskId, note, btn);
                    } else {
                        Swal.fire({
                            title: 'Minta Revisi Tugas',
                            html: `
                                <div class="text-start mb-2 small text-muted">
                                    Tugas: <strong class="text-dark">${taskName}</strong>
                                </div>
                                <div class="text-start mb-1 small fw-semibold text-secondary">Catatan Perbaikan untuk Pegawai:</div>
                                <textarea id="swal-revise-note" class="form-control form-control-sm" rows="3" placeholder="Contoh: Lampirkan file laporan pengujian atau revisi bagian flowchart..."></textarea>
                            `,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#dc3545',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Kirim Revisi',
                            cancelButtonText: 'Batal',
                            preConfirm: () => {
                                const note = document.getElementById('swal-revise-note').value.trim();
                                if (!note) {
                                    Swal.showValidationMessage('Catatan perbaikan wajib diisi!');
                                    return false;
                                }
                                return note;
                            }
                        }).then((result) => {
                            if (result.isConfirmed && result.value) {
                                submitRevision(taskId, result.value, btn);
                            }
                        });
                    }

                    function submitRevision(tId, noteVal, btnEl) {
                        btnEl.disabled = true;
                        fetch(`{{ url('pemantauan-staff/validate-task') }}/${tId}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                action: 'revise',
                                catatan: noteVal
                            })
                        })
                        .then(res => res.json())
                        .then(resData => {
                            if (resData.success) {
                                if (typeof Swal !== 'undefined') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Permintaan Revisi Terkirim',
                                        text: resData.message || 'Status tugas diubah menjadi Perlu Revisi.',
                                        confirmButtonColor: '#046B26'
                                    });
                                }
                                loadStaffDetailRow(userId, true);
                            } else {
                                if (typeof Swal !== 'undefined') {
                                    Swal.fire('Gagal', resData.message || 'Gagal mengirim revisi.', 'error');
                                }
                                btnEl.disabled = false;
                            }
                        })
                        .catch(err => {
                            btnEl.disabled = false;
                            if (typeof Swal !== 'undefined') {
                                Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
                            }
                        });
                    }
                });
            });

            // Bind Tombol Refresh Detail
            const btnRefresh = contentWrapper.querySelector('.btn-refresh-detail');
            if (btnRefresh) {
                btnRefresh.addEventListener('click', function () {
                    loadStaffDetailRow(userId, true);
                });
            }
        })
        .catch(err => {
            contentWrapper.innerHTML = `
                <div class="alert alert-danger py-2 small mb-0 d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-exclamation-triangle-fill me-1"></i>Terjadi kesalahan saat memuat rincian pekerjaan.</span>
                    <button type="button" class="btn btn-xs btn-outline-danger btn-refresh-detail" data-user-id="${userId}">Coba Lagi</button>
                </div>
            `;
            const retryBtn = contentWrapper.querySelector('.btn-refresh-detail');
            if (retryBtn) {
                retryBtn.addEventListener('click', function () { loadStaffDetailRow(userId, true); });
            }
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Fungsi Bind Tombol Expand Tabel & Modal Fallback
    // ─────────────────────────────────────────────────────────────────────────
    function bindTaskDetailEvents() {
        document.querySelectorAll('.btn-toggle-tasks').forEach(button => {
            button.replaceWith(button.cloneNode(true));
        });

        document.querySelectorAll('.btn-toggle-tasks').forEach(button => {
            button.addEventListener('click', function () {
                const userId = this.getAttribute('data-id');
                const detailRow = document.getElementById(`detail-row-${userId}`);
                if (!detailRow) return;

                const isExpanded = this.getAttribute('aria-expanded') === 'true';

                if (isExpanded) {
                    // Tutup baris
                    detailRow.classList.add('d-none');
                    this.setAttribute('aria-expanded', 'false');
                    this.classList.remove('btn-success');
                    this.classList.add('btn-dark');
                    const textSpan = this.querySelector('.btn-text');
                    if (textSpan) textSpan.innerHTML = 'Rincian &amp; Validasi';
                } else {
                    // Buka baris
                    detailRow.classList.remove('d-none');
                    this.setAttribute('aria-expanded', 'true');
                    this.classList.remove('btn-dark');
                    this.classList.add('btn-success');
                    const textSpan = this.querySelector('.btn-text');
                    if (textSpan) textSpan.innerHTML = 'Tutup Rincian';
                    loadStaffDetailRow(userId);
                }
            });
        });
    }

    // Inisialisasi awal tombol toggle
    bindTaskDetailEvents();

    // ─────────────────────────────────────────────────────────────────────────
    // Live AJAX Navigator & Filter (Tanpa Refresh Halaman)
    // ─────────────────────────────────────────────────────────────────────────
    let abortController = null;

    function loadPemantauanData(newBulan, newTahun, searchVal) {
        if (newBulan !== undefined) currentBulan = parseInt(newBulan);
        if (newTahun !== undefined) currentTahun = parseInt(newTahun);

        const params = new URLSearchParams();
        params.set('bulan', currentBulan);
        params.set('tahun', currentTahun);

        const search = (searchVal !== undefined) ? searchVal : (inputSearch ? inputSearch.value.trim() : '');
        if (search) {
            params.set('search', search);
        }

        // Pertahankan query params filter lainnya jika ada di URL saat ini
        const currentUrlParams = new URLSearchParams(window.location.search);
        ['unit', 'supervisor_id', 'status_mencatat', 'bidang_otk'].forEach(key => {
            if (currentUrlParams.has(key)) {
                params.set(key, currentUrlParams.get(key));
            }
        });

        const targetUrl = `{{ route('pemantauan-staff.index') }}?${params.toString()}`;

        // Loading state
        const tableOverlay = document.getElementById('tableLoadingOverlay');
        if (tableOverlay) {
            tableOverlay.classList.remove('d-none');
            tableOverlay.classList.add('d-flex');
        }
        if (kpiWrapper) {
            kpiWrapper.style.opacity = '0.5';
            kpiWrapper.style.transition = 'opacity 0.2s ease';
        }

        if (abortController) {
            abortController.abort();
        }
        abortController = new AbortController();

        fetch(targetUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            signal: abortController.signal
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Update KPI Cards HTML
                if (kpiWrapper && data.html_kpi) {
                    kpiWrapper.innerHTML = data.html_kpi;
                }

                // Update Table HTML
                if (tableWrapper && data.html_table) {
                    const overlayHtml = `
                        <div id="tableLoadingOverlay" class="position-absolute top-0 start-0 w-100 h-100 d-none bg-white bg-opacity-75 d-flex align-items-center justify-content-center rounded-4" style="z-index: 10; backdrop-filter: blur(2px);">
                            <div class="text-center py-4">
                                <div class="spinner-border text-success" role="status" style="width: 2rem; height: 2rem;">
                                    <span class="visually-hidden">Memuat data...</span>
                                </div>
                                <div class="small fw-semibold text-secondary mt-2">Memuat data kinerja...</div>
                            </div>
                        </div>
                    `;
                    tableWrapper.innerHTML = overlayHtml + data.html_table;
                }

                // Update Select Values
                if (selectBulan) selectBulan.value = data.bulan;
                if (selectTahun) selectTahun.value = data.tahun;

                // Update Prev / Next Buttons
                if (btnPrevMonth) {
                    btnPrevMonth.setAttribute('data-month', data.prev_month);
                    btnPrevMonth.setAttribute('data-year', data.prev_year);
                }
                if (btnNextMonth) {
                    btnNextMonth.setAttribute('data-month', data.next_month);
                    btnNextMonth.setAttribute('data-year', data.next_year);
                }

                // Update Export URLs
                if (btnExportExcel && data.export_excel_url) {
                    btnExportExcel.href = data.export_excel_url;
                }
                if (btnExportPdf && data.export_pdf_url) {
                    btnExportPdf.href = data.export_pdf_url;
                }

                currentNamaBulan = data.namaBulanIndonesia;
                currentBulan = data.bulan;
                currentTahun = data.tahun;

                // Update browser URL tanpa refresh
                window.history.pushState({ bulan: data.bulan, tahun: data.tahun }, '', data.clean_url || targetUrl);

                // Hubungkan kembali event listener tombol Rincian & Validasi
                bindTaskDetailEvents();
            }
        })
        .catch(err => {
            if (err.name !== 'AbortError') {
                console.error('Gagal memperbarui data pemantauan:', err);
            }
        })
        .finally(() => {
            const tableOverlayNow = document.getElementById('tableLoadingOverlay');
            if (tableOverlayNow) {
                tableOverlayNow.classList.remove('d-flex');
                tableOverlayNow.classList.add('d-none');
            }
            if (kpiWrapper) {
                kpiWrapper.style.opacity = '1';
            }
        });
    }

    // Event listener: Ubah dropdown bulan
    if (selectBulan) {
        selectBulan.addEventListener('change', function () {
            loadPemantauanData(this.value, selectTahun.value);
        });
    }

    // Event listener: Ubah dropdown tahun
    if (selectTahun) {
        selectTahun.addEventListener('change', function () {
            loadPemantauanData(selectBulan.value, this.value);
        });
    }

    // Event listener: Tombol bulan sebelumnya (<)
    if (btnPrevMonth) {
        btnPrevMonth.addEventListener('click', function () {
            const m = this.getAttribute('data-month');
            const y = this.getAttribute('data-year');
            loadPemantauanData(m, y);
        });
    }

    // Event listener: Tombol bulan berikutnya (>)
    if (btnNextMonth) {
        btnNextMonth.addEventListener('click', function () {
            const m = this.getAttribute('data-month');
            const y = this.getAttribute('data-year');
            loadPemantauanData(m, y);
        });
    }

    // Event listener: Submit form filter pencarian
    if (formFilter) {
        formFilter.addEventListener('submit', function (e) {
            e.preventDefault();
            loadPemantauanData(selectBulan.value, selectTahun.value);
        });
    }

    // Event listener: Live Search (debounce 350ms)
    let searchTimeout = null;
    if (inputSearch) {
        inputSearch.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                loadPemantauanData(selectBulan.value, selectTahun.value, this.value.trim());
            }, 350);
        });
    }

    // Event listener: Navigasi Back/Forward browser
    window.addEventListener('popstate', function (e) {
        const urlParams = new URLSearchParams(window.location.search);
        const b = urlParams.get('bulan') || currentBulan;
        const y = urlParams.get('tahun') || currentTahun;
        const s = urlParams.get('search') || '';
        if (inputSearch) inputSearch.value = s;
        loadPemantauanData(b, y, s);
    });
});
</script>
@endsection
