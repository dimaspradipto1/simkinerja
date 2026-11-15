@extends('layouts.dashboard.template')

@section('page-style-class', 'menu-page-rencana-kerja')
@section('content')

<div class="pagetitle">
    <h1>Catatan Kegiatan &amp; Rencana Kerja</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0)">Kegiatan</a></li>
            <li class="breadcrumb-item active">Catatan Kegiatan</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    @if(auth()->check() && (auth()->user()->isRektorOrSuperAdmin() || auth()->user()->isPimpinanUnit()))
    <!-- Top Filter Section: Kriteria Checklist Jabatan (Khusus Pimpinan & Admin) -->
    <div class="row mb-3">
        <div class="col-lg-12">
            <div class="card shadow-2xs border-0 bg-white rounded-3">
                <div class="card-body py-2.5 px-3">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <i class="bi bi-person-lines-fill text-success fs-5"></i>
                        <label class="form-label mb-0 fw-semibold text-secondary small text-nowrap">Pantau Kinerja Jabatan / Personel:</label>
                        <div style="min-width: 250px; max-width: 650px;" class="flex-grow-1">
                            <select class="form-select form-select-sm" id="filter-jabatan">
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
    <div class="row mb-3" id="wrapper-pending-requests">
        <div class="col-12">
            <div class="p-3 rounded-3 shadow-xs" style="background-color: #fffdf5; border: 1px solid #fde68a; border-left: 5px solid #f59e0b; width: 100%;">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2 pb-2 border-bottom" style="border-color: #fef08a !important;">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge bg-danger rounded-pill px-2.5 py-1 fw-bold shadow-2xs" style="font-size: 0.78rem;">
                            <i class="bi bi-bell-fill me-1"></i> {{ $pendingRequests->count() }} Permohonan Masuk
                        </span>
                        <span class="text-dark small fw-semibold">Tugas melewati estimasi dan membutuhkan persetujuan perpanjangan waktu:</span>
                    </div>
                </div>
                <div class="d-flex flex-column gap-2">
                    @foreach($pendingRequests as $pr)
                    <div class="p-2.5 bg-white rounded-2 border d-flex align-items-center justify-content-between flex-wrap gap-2 shadow-2xs">
                        <div>
                            <span class="fw-bold text-dark small">{{ $pr->user->name ?? '-' }}</span>: 
                            <span class="text-secondary small">{{ $pr->uraian_tugas }}</span>
                        </div>
                        @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isPimpinanRektorat() || auth()->user()->isPimpinanUnit()))
                        <button type="button" class="btn btn-warning btn-sm text-dark fw-bold py-1 px-2.5" style="font-size: 0.8rem;"
                                data-id="{{ $pr->id }}" 
                                data-uraian="{{ e($pr->uraian_tugas) }}" 
                                data-pegawai="{{ e($pr->user->name ?? '-') }}" 
                                data-reason="{{ e($pr->estimasi_unlock_reason) }}" 
                                data-tgl-selesai="{{ e($pr->estimasi_tanggal_selesai ?? date('Y-m-d')) }}" 
                                data-jam-selesai="{{ e($pr->estimasi_jam_selesai ? substr($pr->estimasi_jam_selesai, 0, 5) : '23:59') }}" 
                                data-route="{{ route('rencana-kerja.approve-extension', $pr->id) }}" 
                                onclick="window.openApproveExtensionModal(this)">
                            <i class="bi bi-clock-history me-1"></i> Beri Perpanjangan Waktu
                        </button>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- 1. TOP TABS (Sesuai Referensi Gambar) -->
    <div class="d-flex align-items-center gap-4 mb-3 border-bottom px-2 pt-1 flex-wrap" style="background: transparent;">
        @if(auth()->check() && (auth()->user()->isRektorOrSuperAdmin() || auth()->user()->isPimpinanUnit() || auth()->user()->subordinates()->exists()))
            <a href="{{ route('pemantauan-staff.index') }}" class="tab-item-link text-decoration-none py-2 fw-semibold d-inline-flex align-items-center gap-2 text-secondary" style="font-size: 0.95rem; border-bottom: 2px solid transparent;">
                <i class="bi bi-people"></i>
                <span>Tim saya</span>
                <span class="badge bg-light text-secondary border rounded-pill px-2 py-0.5" id="nav-team-count">0</span>
            </a>
            <a href="javascript:void(0)" id="tab-btn-menunggu-verif" class="tab-item-link text-decoration-none py-2 fw-semibold d-inline-flex align-items-center gap-2 text-secondary" style="font-size: 0.95rem; border-bottom: 2px solid transparent;">
                <i class="bi bi-file-earmark-check"></i>
                <span>Menunggu verifikasi</span>
                <span class="badge bg-light text-secondary border rounded-pill px-2 py-0.5" id="nav-pending-count">{{ isset($pendingRequests) ? $pendingRequests->count() : 0 }}</span>
            </a>
        @endif
        <a href="javascript:void(0)" class="tab-item-link active text-decoration-none py-2 fw-bold d-inline-flex align-items-center gap-2 text-dark" style="font-size: 0.95rem; border-bottom: 3px solid #046B26;">
            <i class="bi bi-person"></i>
            <span id="tab-active-name">{{ auth()->user()->name }}</span>
        </a>
    </div>

    <!-- 2. SUB-HEADER CARD (Sesuai Referensi Gambar) -->
    <div class="card shadow-sm border-0 mb-3 rounded-3 bg-white">
        <div class="card-body p-3">
            <div class="d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3">
                <!-- Left Title & Metadata -->
                <div class="d-flex align-items-center gap-2.5">
                    <span class="p-2 rounded-3 bg-light border text-dark fs-5 flex-shrink-0 d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-clipboard2-check text-success"></i>
                    </span>
                    <div>
                        <h5 class="fw-bold text-dark mb-1 fs-6 d-flex align-items-center gap-2 flex-wrap">
                            <span>Catatan kegiatan - <span id="header-staff-name">{{ auth()->user()->name }}</span></span>
                        </h5>
                        <div class="text-secondary small fw-medium d-flex align-items-center gap-1.5 flex-wrap" style="font-size: 0.82rem;" id="header-staff-meta">
                            <span id="meta-jabatan">{{ auth()->user()->jabatan ?: 'Staff' }}</span>
                            <span>•</span>
                            <span id="meta-unit">{{ auth()->user()->unit ?: 'Universitas Ibnu Sina' }}</span>
                            <span>•</span>
                            <span><span id="meta-total-kegiatan">0</span> kegiatan (<span id="meta-selesai-kegiatan">0</span> terverifikasi)</span>
                            <span>•</span>
                            <span id="meta-total-jam" class="fw-semibold text-dark">0 jam</span>
                        </div>
                    </div>
                </div>

                <!-- Right Action Controls (Periode, Beri umpan balik, Evaluasi, Tambah, Import) -->
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <!-- Dropdown Periode -->
                    <div>
                        <select id="filter-periode" class="form-select form-select-sm bg-white border fw-semibold text-secondary shadow-none px-3 py-1.5" style="border-radius: 8px; font-size: 0.82rem; min-width: 160px; cursor: pointer;">
                            <option value="all" selected>Sepanjang periode</option>
                            <option value="today">Hari Ini</option>
                            <option value="week">Minggu Ini</option>
                            <option value="month">Bulan Ini</option>
                            <option value="year">Tahun Ini</option>
                        </select>
                    </div>

                    <!-- Filter Jenis Kegiatan (Rutin, Panitia, Insidental) -->
                    <div>
                        <select id="filter-jenis" class="form-select form-select-sm bg-white border fw-semibold text-secondary shadow-none px-2.5 py-1.5" style="border-radius: 8px; font-size: 0.82rem; min-width: 135px; cursor: pointer;">
                            <option value="all" selected>Semua Jenis</option>
                            <option value="rutin">Rutin</option>
                            <option value="panitia">Panitia</option>
                            <option value="insidentil">Insidental</option>
                        </select>
                    </div>

                    <!-- Tombol Beri Umpan Balik -->
                    <button type="button" class="btn btn-sm btn-outline-secondary fw-semibold d-inline-flex align-items-center gap-1.5 px-3 py-1.5 shadow-2xs" data-bs-toggle="modal" data-bs-target="#modalBeriUmpanBalik" style="border-radius: 8px; font-size: 0.82rem;">
                        <i class="bi bi-chat-left-text"></i>
                        <span>Beri umpan balik</span>
                    </button>

                    <!-- Tombol Evaluasi -->
                    <button type="button" class="btn btn-sm btn-outline-secondary fw-semibold d-inline-flex align-items-center gap-1.5 px-3 py-1.5 shadow-2xs" data-bs-toggle="modal" data-bs-target="#modalEvaluasiKinerja" style="border-radius: 8px; font-size: 0.82rem;">
                        <i class="bi bi-check2-square"></i>
                        <span>Evaluasi</span>
                    </button>

                    <!-- Tombol Tambah Kegiatan -->
                    <a href="{{ route('rencana-kerja.create') }}" class="btn btn-sm btn-success fw-bold d-inline-flex align-items-center gap-1 px-3 py-1.5 shadow-2xs text-white" style="background-color: #046B26; border-color: #046B26; border-radius: 8px; font-size: 0.82rem;">
                        <i class="bi bi-plus-lg"></i>
                        <span>Tambah</span>
                    </a>

                    <!-- Tombol Import Excel -->
                    <button type="button" class="btn btn-sm btn-light border fw-semibold d-inline-flex align-items-center gap-1.5 px-2.5 py-1.5 shadow-2xs" data-bs-toggle="modal" data-bs-target="#modalImportExcel" style="border-radius: 8px; font-size: 0.82rem;">
                        <i class="bi bi-file-earmark-excel text-success"></i>
                        <span>Import</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. TABLE SECTION (Dark Navy Header & Date Row Grouping) -->
    <div class="card shadow-sm border-0 rounded-3 mb-4 overflow-hidden">
        <div class="card-body p-0">
            <!-- Toolbar Pilih Semua (Select All) & Hapus Terpilih -->
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 p-2 px-3 bg-light border-bottom" id="selectAllToolbarRencanaKerja">
                <div class="d-flex align-items-center gap-2">
                    <input type="checkbox" id="check-all" class="form-check-input my-0" style="cursor: pointer; width: 17px; height: 17px;" title="Pilih Semua">
                    <label for="check-all" class="form-check-label fw-bold text-dark user-select-none mb-0 cursor-pointer" style="cursor: pointer; font-size: 0.84rem;">
                        Pilih Semua (Select All)
                    </label>
                    <span id="selected-badge" class="badge bg-primary-subtle text-primary border border-primary-subtle d-none ms-1" style="font-size: 0.78rem;">
                        <span id="selected-count">0</span> tugas dipilih
                    </span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" id="btn-bulk-delete" class="btn btn-danger btn-sm text-white fw-bold d-inline-flex align-items-center gap-1 d-none shadow-xs" style="border-radius: 6px; font-size: 0.8rem; padding: 4px 12px;">
                        <i class="bi bi-trash-fill me-1"></i> Hapus Terpilih (<span class="selected-count-num">0</span>)
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 w-100" id="rencanakerja-table">
                    <thead style="background-color: #0b1a30; color: #ffffff;">
                        <tr>
                            <th width="35px" class="text-center align-middle" style="background-color: #0b1a30; color: #ffffff; border: none;">
                                <input type="checkbox" class="form-check-input" style="cursor: pointer; width: 16px; height: 16px;" id="th-check-all">
                            </th>
                            <th width="115px" style="background-color: #0b1a30; color: #ffffff; border: none; font-weight: 600; font-size: 0.82rem;">Jam</th>
                            <th width="125px" style="background-color: #0b1a30; color: #ffffff; border: none; font-weight: 600; font-size: 0.82rem;">Total jam kerja</th>
                            <th style="background-color: #0b1a30; color: #ffffff; border: none; font-weight: 600; font-size: 0.82rem;">Kegiatan</th>
                            <th width="105px" style="background-color: #0b1a30; color: #ffffff; border: none; font-weight: 600; font-size: 0.82rem;">Jenis</th>
                            <th width="145px" style="background-color: #0b1a30; color: #ffffff; border: none; font-weight: 600; font-size: 0.82rem;">Status</th>
                            <th width="135px" class="text-center" style="background-color: #0b1a30; color: #ffffff; border: none; font-weight: 600; font-size: 0.82rem;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<!-- MODAL 1: Detail Lengkap Kegiatan -->
<div class="modal fade" id="modalDetailKegiatan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-white py-3 border-bottom">
                <h5 class="modal-title fs-6 fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-card-checklist text-success"></i>
                    <span>Rincian Catatan Kegiatan</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold mb-1">Uraian Tugas / Kegiatan:</label>
                    <div id="detailTaskTitle" class="fs-6 fw-bold text-dark p-2 bg-light rounded border"></div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold mb-1">Pegawai Penanggung Jawab:</label>
                        <div id="detailTaskPegawai" class="small text-dark fw-semibold p-2 bg-light rounded border"></div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold mb-1">Kategori / Jenis:</label>
                        <div id="detailTaskKategori" class="small text-dark fw-semibold p-2 bg-light rounded border"></div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold mb-1">Status Kegiatan:</label>
                        <div id="detailTaskStatus" class="p-1"></div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label text-muted small fw-bold mb-1"><i class="bi bi-calendar3 me-1"></i>Tanggal:</label>
                        <div id="detailTaskTanggal" class="small text-dark p-2 bg-light rounded border"></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small fw-bold mb-1"><i class="bi bi-clock me-1"></i>Jam Kegiatan:</label>
                        <div id="detailTaskJam" class="small text-dark p-2 bg-light rounded border"></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small fw-bold mb-1"><i class="bi bi-stopwatch me-1"></i>Total Jam Kerja:</label>
                        <div id="detailTaskTotalJam" class="small text-dark fw-bold p-2 bg-light rounded border"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold mb-1">Hasil Kerja / Output yang Dicapai:</label>
                    <div id="detailTaskHasil" class="small text-dark p-2 bg-light rounded border" style="min-height: 50px;"></div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold mb-1">Rencana Tindak Lanjut:</label>
                        <div id="detailTaskRTL" class="small text-secondary p-2 bg-light rounded border" style="min-height: 40px;"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold mb-1">Catatan / Arahan Pimpinan:</label>
                        <div id="detailTaskSaran" class="small text-secondary p-2 bg-light rounded border" style="min-height: 40px;"></div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold mb-1"><i class="bi bi-people-fill text-info me-1"></i>Rekan Kerja:</label>
                        <div id="detailTaskTagged" class="p-2 bg-light rounded border small"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold mb-1"><i class="bi bi-link-45deg text-primary me-1"></i>Dokumen / Tautan Bukti:</label>
                        <div id="detailTaskBukti" class="p-2 bg-light rounded border"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL 2: Beri Umpan Balik -->
<div class="modal fade" id="modalBeriUmpanBalik" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-white py-3 border-bottom">
                <h5 class="modal-title fs-6 fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-chat-left-quote text-success"></i>
                    <span>Beri Umpan Balik Kinerja</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-secondary small mb-3">
                    Kirim apresiasi, arahan, atau catatan evaluasi untuk staf yang sedang dipantau.
                </p>
                <div class="mb-3">
                    <label class="form-label fw-semibold small text-dark">Penerima Umpan Balik:</label>
                    <input type="text" class="form-control form-control-sm bg-light" id="feedback-receiver-name" readonly value="{{ auth()->user()->name }}">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small text-dark">Catatan / Umpan Balik:</label>
                    <textarea class="form-control form-control-sm" id="feedback-message" rows="4" placeholder="Tuliskan umpan balik Anda secara konstruktif..."></textarea>
                </div>
                <div class="alert alert-light border small text-muted p-2.5 mb-0">
                    <i class="bi bi-info-circle me-1 text-primary"></i> Anda juga dapat mengirimkan catatan ini langsung via WhatsApp ke staf bersangkutan.
                </div>
            </div>
            <div class="modal-footer bg-light py-2 d-flex justify-content-between">
                <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-success btn-sm px-3 fw-bold d-inline-flex align-items-center gap-1" id="btn-send-wa-feedback">
                        <i class="bi bi-whatsapp"></i> Kirim ke WhatsApp
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL 3: Evaluasi Kinerja -->
<div class="modal fade" id="modalEvaluasiKinerja" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-white py-3 border-bottom">
                <h5 class="modal-title fs-6 fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-check2-square text-success"></i>
                    <span>Evaluasi &amp; Rekap Kinerja</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="p-3 rounded-3 bg-light border text-center">
                            <div class="text-secondary small fw-semibold">Total Kegiatan</div>
                            <div class="fs-4 fw-bold text-dark" id="eval-total-kegiatan">0</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded-3 bg-success-subtle border border-success-subtle text-center">
                            <div class="text-success small fw-semibold">Terverifikasi / Selesai</div>
                            <div class="fs-4 fw-bold text-success" id="eval-selesai-kegiatan">0</div>
                        </div>
                    </div>
                </div>

                <div class="p-3 rounded-3 bg-light border mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small fw-semibold text-secondary">Rasio Kinerja Selesai:</span>
                        <span class="small fw-bold text-dark" id="eval-percent-text">0%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" id="eval-progress-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between p-2.5 rounded-3 bg-light border">
                    <span class="small fw-semibold text-secondary">Total Realisasi Jam Kerja:</span>
                    <span class="small fw-bold text-dark fs-6" id="eval-total-jam">0 jam</span>
                </div>
            </div>
            <div class="modal-footer bg-light py-2 d-flex justify-content-between">
                <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Tutup</button>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('rencana-kerja.export-excel') }}" class="btn btn-sm btn-outline-success fw-bold d-inline-flex align-items-center gap-1">
                        <i class="bi bi-file-earmark-excel"></i> Export Excel
                    </a>
                    <a href="{{ route('rencana-kerja.export-pdf') }}" class="btn btn-sm btn-outline-danger fw-bold d-inline-flex align-items-center gap-1">
                        <i class="bi bi-file-earmark-pdf"></i> Export PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Import Excel -->
<div class="modal fade" id="modalImportExcel" tabindex="-1" aria-labelledby="modalImportExcelLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white" style="background-color: #046B26;">
                <h5 class="modal-title fw-bold fs-6" id="modalImportExcelLabel">
                    <i class="bi bi-file-earmark-excel me-2"></i> Import Catatan Kegiatan Excel
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
                                <span class="fw-bold text-success d-block mb-1">Format Excel Kompatibel:</span>
                                Anda dapat mengunggah template kegiatan standar atau file laporan.
                            </div>
                        </div>
                        <div class="pt-2 border-top border-success-subtle d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <span class="text-muted small">Butuh template?</span>
                            <a href="{{ route('rencana-kerja.download-template') }}" class="btn btn-sm btn-outline-success fw-bold py-1 px-2.5" style="font-size: 0.82rem; border-radius: 6px;">
                                <i class="bi bi-download me-1"></i> Download Template
                            </a>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark mb-2 small">Pilih File Excel (.xlsx, .xls, .csv)</label>
                        <input type="file" name="file_excel" class="form-control form-control-sm bg-white" accept=".xlsx,.xls,.csv" required>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm px-4 text-white fw-bold" style="background-color: #046B26; border-color: #046B26;">
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
            <div class="modal-header text-white" style="background-color: #046B26;">
                <h5 class="modal-title fw-bold fs-6" id="modalQuickTagLabel">
                    <i class="bi bi-person-plus-fill me-2"></i> Tag Rekan Kerja
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-quick-tag" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary small mb-1">Uraian Tugas:</label>
                        <div id="quick-tag-uraian" class="fw-semibold text-dark p-2 bg-light border rounded small" style="word-break: break-word;"></div>
                    </div>
                    <div class="mb-3">
                        <label for="quick_tagged_users" class="form-label fw-bold text-dark small mb-1">
                            Pilih Rekan Kerja <span class="text-danger">*</span>
                        </label>
                        <select name="tagged_users[]" id="quick_tagged_users" class="form-select select2-multiple" multiple="multiple" style="width: 100%;">
                            @foreach($usersWithJabatan as $u)
                                <option value="{{ $u->id }}">{{ $u->jabatan ?? '-' }} - {{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm px-4 text-white fw-bold btn-save-quick-tag" style="background-color: #046B26; border-color: #046B26;">
                        <i class="bi bi-save me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* Styling Sesuai Referensi Gambar */
    .tab-item-link {
        color: #64748b;
        transition: all 0.2s ease;
    }
    .tab-item-link:hover {
        color: #046B26;
    }
    .btn-icon-table {
        width: 28px;
        height: 28px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        font-size: 13px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        color: #475569;
        transition: all 0.15s ease-in-out;
        text-decoration: none;
    }
    .btn-icon-table:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        color: #0f172a;
    }
    .btn-icon-table.btn-action-success:hover {
        background: #ecfdf5;
        border-color: #a7f3d0;
        color: #046B26;
    }
    .btn-icon-table.btn-action-warning:hover {
        background: #fffbeb;
        border-color: #fde68a;
        color: #d97706;
    }
    .group-date-header-row td {
        background-color: #f8fafc !important;
        border-top: 1px solid #e2e8f0 !important;
        border-bottom: 1px solid #e2e8f0 !important;
        padding: 8px 14px !important;
    }
    #rencanakerja-table tbody tr:hover {
        background-color: #f8fafc;
    }
    #rencanakerja-table td {
        padding: 9px 12px;
        font-size: 12.5px;
    }
</style>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        if ($('#filter-jabatan').length) {
            $('#filter-jabatan').select2({
                theme: 'bootstrap-5',
                placeholder: "Cari & pilih jabatan - nama...",
                allowClear: true
            });
        }

        if ($('#quick_tagged_users').length) {
            $('#quick_tagged_users').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#modalQuickTag'),
                placeholder: "Cari & pilih rekan kerja...",
                allowClear: true
            });
        }

        // Multi Select State
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

        // DataTables Init
        var table = $('#rencanakerja-table').DataTable({
            processing: true,
            serverSide: true,
            stateSave: true,
            ordering: false,
            autoWidth: false,
            pageLength: 25,
            ajax: {
                url: "{{ app()->environment('production') ? str_replace('http:', 'https:', route('rencana-kerja.index')) : route('rencana-kerja.index') }}",
                data: function(d) {
                    d.jabatan = $('#filter-jabatan').length ? $('#filter-jabatan').val() : '';
                    d.status = $('#filter-status').length ? $('#filter-status').val() : '';
                    d.periode = $('#filter-periode').val();
                    d.jenis = $('#filter-jenis').val();
                }
            },
            columns: [
                { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false, className: 'text-center align-middle', width: '35px' },
                { data: 'jam', name: 'jam', orderable: false, searchable: false, className: 'align-middle small text-secondary text-nowrap', width: '115px' },
                { data: 'total_jam', name: 'total_jam', orderable: false, searchable: false, className: 'align-middle small text-dark fw-medium text-nowrap', width: '125px' },
                { data: 'kegiatan_details', name: 'uraian_tugas', orderable: false, searchable: true, className: 'align-middle' },
                { data: 'jenis', name: 'kategori', orderable: false, searchable: false, className: 'align-middle', width: '105px' },
                { data: 'status_badge', name: 'status', orderable: false, searchable: false, className: 'align-middle', width: '145px' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center align-middle', width: '135px' }
            ],
            language: {
                search: "Cari Tugas:",
                lengthMenu: "Tampilkan _MENU_ data",
                zeroRecords: "Belum ada catatan kegiatan untuk filter ini",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ kegiatan",
                infoEmpty: "Tidak ada data"
            },
            drawCallback: function(settings) {
                var api = this.api();
                var rows = api.rows({ page: 'current' }).nodes();
                var data = api.rows({ page: 'current' }).data();
                var lastDate = null;

                // Group statistics accumulator
                var groupStats = {};
                data.each(function(row) {
                    var d = row.date_group_label || 'Tanpa Tanggal';
                    if (!groupStats[d]) {
                        groupStats[d] = { count: 0, seconds: 0, draft: 0 };
                    }
                    groupStats[d].count++;
                    groupStats[d].seconds += (row.durasi_detik || 0);
                    if (row.raw_status === 'Draft' || row.raw_status === 'Belum Dimulai') {
                        groupStats[d].draft++;
                    }
                });

                // Insert group banner rows
                data.each(function(row, i) {
                    var group = row.date_group_label || 'Tanpa Tanggal';
                    if (lastDate !== group) {
                        var stats = groupStats[group] || { count: 1, seconds: 0, draft: 0 };
                        var hours = stats.seconds > 0 ? (Math.round((stats.seconds / 3600) * 10) / 10).toLocaleString('id-ID') + ' jam' : '0 jam';
                        var metaParts = [stats.count + ' kegiatan', hours];
                        if (stats.draft > 0) {
                            metaParts.push(stats.draft + ' draft');
                        }

                        var groupRow = $('<tr class="group-date-header-row">' +
                            '<td colspan="7" class="py-2 px-3 border-top border-bottom" style="background-color: #f8fafc !important;">' +
                                '<div class="d-flex align-items-center justify-content-between flex-wrap gap-2">' +
                                    '<div class="d-flex align-items-center gap-2 fw-bold text-dark" style="font-size: 0.86rem;">' +
                                        '<i class="bi bi-calendar3 text-secondary"></i>' +
                                        '<span>' + group + '</span>' +
                                    '</div>' +
                                    '<div class="text-secondary small fw-medium" style="font-size: 0.8rem;">' +
                                        metaParts.join(' • ') +
                                    '</div>' +
                                '</div>' +
                            '</td>' +
                        '</tr>');

                        $(rows).eq(i).before(groupRow);
                        lastDate = group;
                    }
                });

                // Sync checkboxes
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
                $('#th-check-all').prop('checked', totalOnPage > 0 && totalOnPage === checkedOnPage);
                updateBulkDeleteButton();
            }
        });

        // XHR Response handler to update Header and Statistics
        table.on('xhr.dt', function(e, settings, json, xhr) {
            if (json && json.overall_rekap) {
                var r = json.overall_rekap;
                $('#meta-total-kegiatan').text(r.total || 0);
                $('#meta-selesai-kegiatan').text(r.selesai || 0);
                $('#meta-total-jam').text(r.total_jam_label || '0 jam');

                if (r.staff_name) {
                    $('#tab-active-name').text(r.staff_name);
                    $('#header-staff-name').text(r.staff_name);
                    $('#feedback-receiver-name').val(r.staff_name);
                }
                if (r.staff_jabatan) $('#meta-jabatan').text(r.staff_jabatan);
                if (r.staff_unit) $('#meta-unit').text(r.staff_unit);
                if (r.team_count !== undefined) $('#nav-team-count').text(r.team_count);
                if (r.pending_verif_count !== undefined) $('#nav-pending-count').text(r.pending_verif_count);

                // Update Evaluasi Modal
                $('#eval-total-kegiatan').text(r.total || 0);
                $('#eval-selesai-kegiatan').text(r.selesai || 0);
                $('#eval-total-jam').text(r.total_jam_label || '0 jam');
                $('#eval-percent-text').text((r.percent || 0) + '%');
                $('#eval-progress-bar').css('width', (r.percent || 0) + '%');
            }
        });

        // Filters Change
        $('#filter-periode, #filter-jenis').on('change', function() {
            table.ajax.reload();
        });

        if ($('#filter-jabatan').length) {
            $('#filter-jabatan').on('change', function() {
                table.ajax.reload();
            });
        }

        // Tab "Menunggu Verifikasi" Click Filter
        $('#tab-btn-menunggu-verif').on('click', function(e) {
            e.preventDefault();
            if ($('#wrapper-pending-requests').length) {
                $('html, body').animate({
                    scrollTop: $("#wrapper-pending-requests").offset().top - 120
                }, 400);
            }
        });

        // Toggle Check All
        $(document).on('change', '#check-all, #th-check-all', function() {
            let isChecked = $(this).is(':checked');
            $('#check-all, #th-check-all').prop('checked', isChecked);
            $('.select-row-checkbox').prop('checked', isChecked);
            $('.select-row-checkbox').each(function() {
                let val = parseInt($(this).val());
                if (isChecked) selectedIds.add(val);
                else selectedIds.delete(val);
            });
            updateBulkDeleteButton();
        });

        // Individual Checkbox Change
        $(document).on('change', '.select-row-checkbox', function() {
            let val = parseInt($(this).val());
            if ($(this).is(':checked')) selectedIds.add(val);
            else selectedIds.delete(val);

            let totalOnPage = $('.select-row-checkbox').length;
            let checkedOnPage = $('.select-row-checkbox:checked').length;
            $('#check-all, #th-check-all').prop('checked', totalOnPage > 0 && totalOnPage === checkedOnPage);
            updateBulkDeleteButton();
        });

        // Bulk Delete Handler
        $(document).on('click', '#btn-bulk-delete', function() {
            let ids = Array.from(selectedIds);
            if (ids.length === 0) return;

            Swal.fire({
                title: 'Hapus Kegiatan Terpilih?',
                text: 'Apakah Anda yakin ingin menghapus ' + ids.length + ' kegiatan terpilih secara permanen?',
                icon: 'warning',
                showCancelButton: true,
                confirmColor: '#d33',
                cancelColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus ' + ids.length + ' Data',
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
                                    timer: 3500
                                });
                                selectedIds.clear();
                                updateBulkDeleteButton();
                                $('#check-all, #th-check-all').prop('checked', false);
                                table.ajax.reload(null, false);
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Gagal', 'Terjadi kesalahan saat menghapus data terpilih.', 'error');
                        }
                    });
                }
            });
        });

        // Detail Modal Viewer (Eye Icon)
        $(document).on('click', '.btn-detail-task', function() {
            var rowData = table.row($(this).closest('tr')).data();
            if (rowData && rowData.task_json) {
                var d = rowData.task_json;
                $('#detailTaskTitle').text(d.uraian_tugas || '-');
                $('#detailTaskPegawai').text(d.user_name + ' (' + d.user_jabatan + ' - ' + d.user_unit + ')');
                $('#detailTaskTanggal').text(d.tanggal || '-');
                $('#detailTaskJam').text(d.jam || '-');
                $('#detailTaskTotalJam').text(d.total_jam || '-');
                $('#detailTaskKategori').text(d.kategori || '-');
                $('#detailTaskStatus').html('<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">' + (d.status || '-') + '</span>');
                $('#detailTaskHasil').text(d.hasil_kerja || '-');
                $('#detailTaskRTL').text(d.rencana_tindak_lanjut || '-');
                $('#detailTaskSaran').text(d.saran_pimpinan || '-');

                if (d.file_url) {
                    $('#detailTaskBukti').html('<a href="' + d.file_url + '" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-paperclip me-1"></i>Unduh Bukti Lampiran</a>');
                } else if (d.url_external) {
                    $('#detailTaskBukti').html('<a href="' + d.url_external + '" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-box-arrow-up-right me-1"></i>Buka Tautan Bukti</a>');
                } else {
                    $('#detailTaskBukti').html('<span class="text-muted small">Tidak ada bukti lampiran.</span>');
                }

                var taggedHtml = '';
                if (d.tagged_users && d.tagged_users.length > 0) {
                    taggedHtml = '<ul class="list-unstyled mb-0">';
                    d.tagged_users.forEach(function(tu) {
                        taggedHtml += '<li class="mb-1"><i class="bi bi-person me-1"></i>' + tu.name + ' <span class="text-muted">(' + tu.jabatan + ')</span></li>';
                    });
                    taggedHtml += '</ul>';
                } else {
                    taggedHtml = '<span class="text-muted small">Tidak ada rekan kerja yang di-tag.</span>';
                }
                $('#detailTaskTagged').html(taggedHtml);

                var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDetailKegiatan'));
                modal.show();
            }
        });

        // Quick Tag Modal Handlers
        let currentQuickTagId = null;
        window.openQuickTagModal = function(id, uraian) {
            currentQuickTagId = id;
            $('#quick-tag-uraian').text(uraian);
            var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalQuickTag'));
            modal.show();
        };

        $('#form-quick-tag').on('submit', function(e) {
            e.preventDefault();
            if (!currentQuickTagId) return;

            let form = $(this);
            let btn = form.find('.btn-save-quick-tag');
            let formData = form.serialize();

            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>');

            $.ajax({
                url: "{{ url('rencana-kerja') }}/" + currentQuickTagId + "/update-tags",
                type: 'POST',
                data: formData,
                success: function(response) {
                    btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan');
                    if (response.success) {
                        bootstrap.Modal.getInstance(document.getElementById('modalQuickTag')).hide();
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: response.message,
                            showConfirmButton: false,
                            timer: 3500
                        });
                        table.ajax.reload(null, false);
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan');
                    Swal.fire('Gagal', 'Terjadi kesalahan saat menyimpan rekan kerja.', 'error');
                }
            });
        });

        // WhatsApp Feedback Sender
        $('#btn-send-wa-feedback').on('click', function() {
            var msg = $('#feedback-message').val();
            if (!msg.trim()) {
                Swal.fire('Peringatan', 'Silakan tuliskan catatan umpan balik terlebih dahulu.', 'warning');
                return;
            }
            var targetName = $('#feedback-receiver-name').val();
            var text = encodeURIComponent("Halo " + targetName + ",\nBerikut umpan balik terkait kegiatan Anda:\n\n" + msg);
            window.open("https://wa.me/?text=" + text, "_blank");
        });
    });

    // Quick Complete Task Handler
    window.quickCompleteTask = function(id) {
        Swal.fire({
            title: 'Tandai Selesai?',
            text: 'Apakah kegiatan ini telah selesai dilaksanakan?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Selesai',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#046B26'
        }).then((result) => {
            if (result.isConfirmed) {
                var now = new Date();
                var time = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0') + ':' + String(now.getSeconds()).padStart(2, '0');
                var date = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0') + '-' + String(now.getDate()).padStart(2, '0');

                $.ajax({
                    url: "{{ url('rencana-kerja') }}/" + id + "/stop",
                    type: 'POST',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "client_time": time,
                        "client_date": date
                    },
                    success: function(res) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Kegiatan berhasil ditandai selesai.',
                            showConfirmButton: false,
                            timer: 3500
                        });
                        $('#rencanakerja-table').DataTable().ajax.reload(null, false);
                    },
                    error: function() {
                        Swal.fire('Gagal', 'Terjadi kesalahan saat menyelesaikan kegiatan.', 'error');
                    }
                });
            }
        });
    };

    // Reopen Task Handler
    window.reopenTask = function(btn) {
        var id = $(btn).data('id');
        var uraian = $(btn).data('uraian');

        Swal.fire({
            title: 'Buka Kunci Tugas?',
            text: 'Tugas "' + uraian + '" akan dibuka kembali agar dapat dikerjakan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Buka Kunci',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#ffc107'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('rencana-kerja') }}/" + id + "/reopen",
                    type: 'POST',
                    data: {
                        "_token": "{{ csrf_token() }}"
                    },
                    success: function(res) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Tugas berhasil dibuka kunci.',
                            showConfirmButton: false,
                            timer: 3500
                        });
                        $('#rencanakerja-table').DataTable().ajax.reload(null, false);
                    },
                    error: function() {
                        Swal.fire('Gagal', 'Terjadi kesalahan saat membuka kunci tugas.', 'error');
                    }
                });
            }
        });
    };

    // Delete Single Task Handler
    window.deleteRencanaKerja = function(id) {
        Swal.fire({
            title: 'Hapus Kegiatan?',
            text: "Kegiatan ini akan dihapus secara permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmColor: '#d33',
            cancelColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus',
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
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Kegiatan berhasil dihapus.',
                            showConfirmButton: false,
                            timer: 3500
                        });
                        $('#rencanakerja-table').DataTable().ajax.reload(null, false);
                    },
                    error: function() {
                        Swal.fire('Gagal', 'Terjadi kesalahan saat menghapus data.', 'error');
                    }
                });
            }
        });
    };
</script>
@endpush
