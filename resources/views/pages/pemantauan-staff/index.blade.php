@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h1 class="fw-bold mb-1" style="color: #15432D; font-size: 1.65rem;">
            <i class="bi bi-diagram-3-fill me-2"></i>Pemantauan Staff &amp; Tim
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
        <a id="btnExportExcel" href="{{ route('pemantauan-staff.export-excel', request()->all()) }}" class="btn btn-sm btn-success fw-bold d-inline-flex align-items-center gap-2 shadow-sm px-3 py-2 text-white" style="background-color: #15432D; border-color: #15432D; border-radius: 8px;">
            <i class="bi bi-file-earmark-excel"></i>
            <span>Export Excel</span>
        </a>
        <a id="btnExportPdf" href="{{ route('pemantauan-staff.export-pdf', request()->all()) }}" class="btn btn-sm btn-outline-danger fw-bold d-inline-flex align-items-center gap-2 shadow-sm px-3 py-2" style="border-radius: 8px; border-color: #dc3545;">
            <i class="bi bi-file-earmark-pdf"></i>
            <span>Export PDF</span>
        </a>
    </div>
</div>

<section class="section">

    <!-- Banner Info Scope OTK -->
    @if($pimpinanTerpilih)
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4 p-3 d-flex align-items-center justify-content-between flex-wrap gap-2" style="background: linear-gradient(135deg, #e8f5ec 0%, #d1f0dd 100%); border-left: 5px solid #15432D !important;">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-white text-success d-flex align-items-center justify-content-center shadow-xs" style="width: 38px; height: 38px;">
                    <i class="bi bi-person-lines-fill fs-5" style="color: #15432D !important;"></i>
                </div>
                <div>
                    <div class="small text-uppercase fw-bold text-muted" style="letter-spacing: 0.5px; font-size: 0.72rem;">Lingkup Pemantauan Tim Sesuai Hierarki OTK</div>
                    <div class="fw-bold text-dark" style="font-size: 0.95rem;">
                        {{ $pimpinanTerpilih->name }} <span class="text-secondary fw-normal">({{ $pimpinanTerpilih->jabatan ?: 'Pimpinan' }})</span>
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-success px-3 py-1.5 rounded-pill fw-semibold" style="font-size: 0.8rem; background-color: #15432D !important;">
                    <i class="bi bi-people me-1"></i>{{ count($pegawaiData) }} Anggota Tim Terhubung
                </span>
                @if(request()->has('supervisor_id') && Auth::user()->subordinates()->exists())
                    <a href="{{ route('pemantauan-staff.index') }}" class="btn btn-sm btn-outline-secondary py-1 px-2.5" style="font-size: 0.75rem;">
                        <i class="bi bi-arrow-left me-1"></i>Kembali ke Tim Saya
                    </a>
                @elseif(request()->has('supervisor_id'))
                    <a href="{{ route('pemantauan-staff.index', ['supervisor_id' => 'all']) }}" class="btn btn-sm btn-outline-secondary py-1 px-2.5" style="font-size: 0.75rem;">
                        Lihat Seluruh Personel OTK
                    </a>
                @endif
            </div>
        </div>
    @elseif(!($isLeader ?? false))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4 p-3 d-flex align-items-center justify-content-between flex-wrap gap-2" style="background: linear-gradient(135deg, #e8f5ec 0%, #d1f0dd 100%); border-left: 5px solid #15432D !important;">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-white text-success d-flex align-items-center justify-content-center shadow-xs" style="width: 38px; height: 38px;">
                    <i class="bi bi-person-badge fs-5" style="color: #15432D !important;"></i>
                </div>
                <div>
                    <div class="small text-uppercase fw-bold text-muted" style="letter-spacing: 0.5px; font-size: 0.72rem;">Pemantauan Kinerja Individu Sesuai Penempatan OTK</div>
                    <div class="fw-bold text-dark" style="font-size: 0.95rem;">
                        {{ Auth::user()->name }} <span class="text-secondary fw-normal">({{ Auth::user()->jabatan ?: 'Pegawai' }} - {{ Auth::user()->unit ?: 'Universitas Ibnu Sina' }})</span>
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-success px-3 py-1.5 rounded-pill fw-semibold" style="font-size: 0.8rem; background-color: #15432D !important;">
                    <i class="bi bi-person-check me-1"></i>Kinerja Pribadi
                </span>
            </div>
        </div>
    @endif

    <!-- KPI Summary Cards -->
    <div id="kpiContainerWrapper" class="position-relative">
        @include('pages.pemantauan-staff.partials.kpi_cards')
    </div>

    <!-- Filter Toolbar -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3 p-md-4">
            <form action="{{ route('pemantauan-staff.index') }}" method="GET" id="formFilterPemantauan">
                <div class="row g-2.5 align-items-end">

                    <!-- Search Box -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary mb-1">
                            <i class="bi bi-search me-1"></i>Cari Anggota Tim / Staf
                        </label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" name="search" id="inputSearchPemantauan" class="form-control border-start-0" placeholder="Ketik nama, jabatan, NUPTK/NUP..." value="{{ request('search') }}">
                        </div>
                    </div>

                    <!-- Periode Bulan & Tahun Navigator -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary mb-1">
                            <i class="bi bi-calendar-event me-1"></i>Periode Bulan
                        </label>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" id="btnPrevMonth" class="btn btn-sm btn-outline-secondary px-2" title="Bulan Sebelumnya" data-month="{{ $prevDate->month }}" data-year="{{ $prevDate->year }}">
                                <i class="bi bi-chevron-left"></i>
                            </button>

                            <select name="bulan" id="selectBulan" class="form-select form-select-sm">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::createFromDate(2026, $m, 1)->translatedFormat('F') }}
                                    </option>
                                @endfor
                            </select>

                            <select name="tahun" id="selectTahun" class="form-select form-select-sm" style="max-width: 90px;">
                                @for($y = now()->year - 2; $y <= now()->year + 2; $y++)
                                    <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>

                            <button type="button" id="btnNextMonth" class="btn btn-sm btn-outline-secondary px-2" title="Bulan Berikutnya" data-month="{{ $nextDate->month }}" data-year="{{ $nextDate->year }}">
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
    /* Styling Pemantauan Staff */
    .card-kpi {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .card-kpi:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0,0,0,0.06) !important;
    }
    .pemantauan-table tbody tr:hover {
        background-color: #f1f8f4 !important;
    }
    .pemantauan-table th {
        font-weight: 600;
        letter-spacing: 0.3px;
    }
    .shadow-xs {
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let currentBulan = {{ $bulan }};
    let currentTahun = {{ $tahun }};
    let currentNamaBulan = "{{ $namaBulanIndonesia }}";

    const modalEl = document.getElementById('modalPantauStaff');
    const modal = new bootstrap.Modal(modalEl);

    const spinner = document.getElementById('modalLoadingSpinner');
    const container = document.getElementById('modalContentContainer');
    const listBody = document.getElementById('modalTaskListBody');
    const emptyNotice = document.getElementById('modalEmptyNotice');

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
    // Fungsi Bind Tombol Pantau (Detail Modal)
    // ─────────────────────────────────────────────────────────────────────────
    function bindPantauButtons() {
        document.querySelectorAll('.btn-pantau').forEach(button => {
            // Hindari duplikasi listener
            button.replaceWith(button.cloneNode(true));
        });

        document.querySelectorAll('.btn-pantau').forEach(button => {
            button.addEventListener('click', function () {
                const userId = this.getAttribute('data-id');
                const userName = this.getAttribute('data-name');
                const bulan = this.getAttribute('data-bulan') || currentBulan;
                const tahun = this.getAttribute('data-tahun') || currentTahun;

                // Reset modal state
                document.getElementById('modalStaffName').textContent = userName;
                document.getElementById('modalStaffSub').textContent = 'Memuat informasi...';
                document.getElementById('modalStaffAtasan').textContent = '';
                document.getElementById('modalPeriodeText').textContent = `${currentNamaBulan} ${tahun}`;
                spinner.style.display = 'block';
                container.style.display = 'none';
                listBody.innerHTML = '';
                emptyNotice.classList.add('d-none');

                modal.show();

                // Fetch detail via AJAX
                fetch(`{{ url('pemantauan-staff/detail') }}/${userId}?bulan=${bulan}&tahun=${tahun}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    spinner.style.display = 'none';
                    container.style.display = 'block';

                    if (data.success) {
                        const p = data.pegawai;
                        document.getElementById('modalStaffName').textContent = p.name;
                        document.getElementById('modalStaffSub').textContent = `${p.jabatan} • ${p.unit}`;
                        document.getElementById('modalStaffAtasan').textContent = `Atasan: ${p.atasan}`;
                        
                        const avatar = document.getElementById('modalStaffAvatar');
                        avatar.textContent = p.avatar_initial;
                        avatar.style.backgroundColor = p.avatar_color;

                        document.getElementById('modalTotalTugas').textContent = data.total_tasks;
                        document.getElementById('modalTotalJam').textContent = `${data.total_jam} Jam`;
                        document.getElementById('modalSelesai').textContent = data.selesai_count;

                        const taggedContainer = document.getElementById('modalTaggedContainer');
                        const taggedCount = document.getElementById('modalTaggedCount');
                        if (taggedContainer && taggedCount) {
                            if (data.tagged_count > 0) {
                                taggedCount.textContent = data.tagged_count;
                                taggedContainer.classList.remove('d-none');
                            } else {
                                taggedContainer.classList.add('d-none');
                            }
                        }

                        // WhatsApp button in modal footer
                        const waContainer = document.getElementById('modalWaContainer');
                        if (p.nomor_wa) {
                            let cleanWa = p.nomor_wa.replace(/\D/g, '');
                            if (cleanWa.startsWith('0')) cleanWa = '62' + cleanWa.substring(1);
                            const msg = encodeURIComponent(`Halo ${p.name}, saya melihat catatan kegiatan Anda untuk periode ${currentNamaBulan} ${tahun}.`);
                            waContainer.innerHTML = `
                                <a href="https://wa.me/${cleanWa}?text=${msg}" target="_blank" class="btn btn-sm btn-success d-inline-flex align-items-center gap-2 px-3">
                                    <i class="bi bi-whatsapp"></i> Hubungi via WhatsApp
                                </a>
                            `;
                        } else {
                            waContainer.innerHTML = '';
                        }

                        // Render task rows
                        if (data.tasks.length === 0) {
                            emptyNotice.classList.remove('d-none');
                        } else {
                            emptyNotice.classList.add('d-none');
                            data.tasks.forEach((t, idx) => {
                                let badgeClass = 'bg-secondary';
                                if (t.status === 'Selesai') badgeClass = 'bg-success';
                                else if (t.status === 'Proses' || t.status === 'Berjalan') badgeClass = 'bg-primary';
                                else if (t.status === 'Tertunda') badgeClass = 'bg-warning text-dark';

                                let tagBadge = '';
                                let durasiHtml = `<span class="fw-bold text-dark">${t.durasi_jam} Jam</span>`;

                                if (!t.is_owner) {
                                    tagBadge = `<span class="badge bg-info-subtle text-info border ms-2" style="font-size:0.72rem;" title="Hanya sekadar mengetahui, bukan penanggung jawab utama"><i class="bi bi-info-circle me-1"></i>Ditandai (Sekadar Mengetahui)</span>`;
                                    durasiHtml = `<span class="text-muted">0 Jam</span><br><small class="text-secondary" style="font-size:0.7rem;">(Info Rekan)</small>`;
                                }

                                let ownerInfo = !t.is_owner ? `<small class="text-muted d-block mt-1"><i class="bi bi-person me-1"></i>Penanggung Jawab: <span class="fw-medium text-dark">${t.pemilik_nama}</span></small>` : '';

                                let row = `
                                    <tr class="${!t.is_owner ? 'bg-light bg-opacity-75' : ''}">
                                        <td class="text-muted">${idx + 1}</td>
                                        <td>
                                            <div class="d-flex align-items-center flex-wrap gap-1">
                                                <span class="fw-semibold text-dark">${t.uraian_tugas}</span>
                                                ${tagBadge}
                                            </div>
                                            <small class="text-muted">${t.tanggal_mulai}</small>
                                            ${ownerInfo}
                                        </td>
                                        <td><span class="badge bg-light text-dark border">${t.kategori}</span></td>
                                        <td class="text-center">${durasiHtml}</td>
                                        <td class="text-center">
                                            <span class="badge ${badgeClass} px-2 py-1">${t.status}</span>
                                        </td>
                                    </tr>
                                `;
                                listBody.innerHTML += row;
                            });
                        }
                    }
                })
                .catch(err => {
                    spinner.style.display = 'none';
                    container.style.display = 'block';
                    listBody.innerHTML = `<tr><td colspan="5" class="text-center text-danger py-4">Gagal memuat rincian data kegiatan.</td></tr>`;
                });
            });
        });
    }

    // Inisialisasi awal tombol modal
    bindPantauButtons();

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

                // Hubungkan kembali event listener tombol Pantau
                bindPantauButtons();
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
