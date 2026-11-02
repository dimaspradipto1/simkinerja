@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h1 class="fw-bold mb-1" style="color: #15432D; font-size: 1.65rem;">
            <i class="bi bi-diagram-3-fill me-2"></i>Pemantauan Staff &amp; Tim (Struktur OTK)
        </h1>
        <nav>
            <ol class="breadcrumb mb-0" style="font-size: 0.85rem;">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item">Monitoring &amp; Evaluasi</li>
                <li class="breadcrumb-item active">Pemantauan Staff (OTK)</li>
            </ol>
        </nav>
    </div>

    <!-- Export Actions -->
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('pemantauan-staff.export-excel', request()->all()) }}" class="btn btn-sm btn-success fw-bold d-inline-flex align-items-center gap-2 shadow-sm px-3 py-2" style="background-color: #15432D; border-color: #15432D; border-radius: 8px;">
            <i class="bi bi-file-earmark-excel"></i>
            <span>Export Excel</span>
        </a>
        <a href="{{ route('pemantauan-staff.export-pdf', request()->all()) }}" class="btn btn-sm btn-outline-danger fw-bold d-inline-flex align-items-center gap-2 shadow-sm px-3 py-2 bg-white" style="border-radius: 8px;">
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
                    <a href="{{ route('pemantauan-staff.index') }}" class="btn btn-sm btn-outline-secondary bg-white py-1 px-2.5" style="font-size: 0.75rem;">
                        <i class="bi bi-arrow-left me-1"></i>Kembali ke Tim Saya
                    </a>
                @elseif(request()->has('supervisor_id'))
                    <a href="{{ route('pemantauan-staff.index', ['supervisor_id' => 'all']) }}" class="btn btn-sm btn-outline-secondary bg-white py-1 px-2.5" style="font-size: 0.75rem;">
                        Lihat Seluruh Personel OTK
                    </a>
                @endif
            </div>
        </div>
    @endif

    <!-- KPI Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 card-kpi" style="border-left: 4px solid #15432D !important;">
                <div class="card-body p-3.5 d-flex align-items-center gap-3">
                    <div class="icon-circle bg-success-subtle text-success rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; min-width: 48px;">
                        <i class="bi bi-diagram-3-fill fs-4" style="color: #15432D !important;"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem;">Anggota Tim (Sesuai OTK)</div>
                        <div class="fs-4 fw-bold text-dark mb-0">{{ number_format($kpi['total_staff']) }} <span class="fs-6 fw-normal text-muted">Orang</span></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 card-kpi" style="border-left: 4px solid #0d6efd !important;">
                <div class="card-body p-3.5 d-flex align-items-center gap-3">
                    <div class="icon-circle bg-primary-subtle text-primary rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; min-width: 48px;">
                        <i class="bi bi-person-check fs-4"></i>
                    </div>
                    <div class="w-100">
                        <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem;">Aktif Mencatat ({{ $namaBulanIndonesia }})</div>
                        <div class="d-flex align-items-baseline gap-2">
                            <span class="fs-4 fw-bold text-dark">{{ $kpi['total_aktif'] }}</span>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.75rem;">{{ $kpi['aktif_percentage'] }}% keaktifan</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 card-kpi" style="border-left: 4px solid #f59e0b !important;">
                <div class="card-body p-3.5 d-flex align-items-center gap-3">
                    <div class="icon-circle bg-warning-subtle text-warning rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; min-width: 48px;">
                        <i class="bi bi-journal-bookmark-fill fs-4" style="color: #b45309 !important;"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem;">Kegiatan Bulan Ini</div>
                        <div class="fs-4 fw-bold text-dark mb-0">{{ number_format($kpi['total_kegiatan_bulan']) }} <span class="fs-6 fw-normal text-muted">Kegiatan</span></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 card-kpi" style="border-left: 4px solid #10b981 !important;">
                <div class="card-body p-3.5 d-flex align-items-center gap-3">
                    <div class="icon-circle bg-emerald-subtle rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; min-width: 48px; background: #e6f9f0; color: #047857;">
                        <i class="bi bi-clock-history fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem;">Akumulasi Jam Kerja</div>
                        <div class="fs-4 fw-bold text-dark mb-0">{{ number_format($kpi['total_jam_kerja_bulan'], 1, ',', '.') }} <span class="fs-6 fw-normal text-muted">Jam</span></div>
                        <small class="text-muted" style="font-size: 0.72rem;">Rata-rata: {{ number_format($kpi['avg_jam_kerja'], 1, ',', '.') }} jam/staf</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3 p-md-4">
            <form action="{{ route('pemantauan-staff.index') }}" method="GET" id="formFilterPemantauan">
                <div class="row g-2.5 align-items-end">

                    <!-- Search Box -->
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label small fw-bold text-secondary mb-1">
                            <i class="bi bi-search me-1"></i>Cari Anggota Tim / Staf
                        </label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-start-0" placeholder="Ketik nama, jabatan, NIDN..." value="{{ request('search') }}">
                        </div>
                    </div>

                    <!-- Periode Bulan & Tahun Navigator -->
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label small fw-bold text-secondary mb-1">
                            <i class="bi bi-calendar-event me-1"></i>Periode Bulan
                        </label>
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('pemantauan-staff.index', array_merge(request()->except(['bulan', 'tahun']), ['bulan' => $prevDate->month, 'tahun' => $prevDate->year])) }}" class="btn btn-sm btn-outline-secondary px-2" title="Bulan Sebelumnya">
                                <i class="bi bi-chevron-left"></i>
                            </a>

                            <select name="bulan" class="form-select form-select-sm" onchange="this.form.submit()">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::createFromDate(2026, $m, 1)->translatedFormat('F') }}
                                    </option>
                                @endfor
                            </select>

                            <select name="tahun" class="form-select form-select-sm" style="max-width: 90px;" onchange="this.form.submit()">
                                @for($y = now()->year - 2; $y <= now()->year + 2; $y++)
                                    <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>

                            <a href="{{ route('pemantauan-staff.index', array_merge(request()->except(['bulan', 'tahun']), ['bulan' => $nextDate->month, 'tahun' => $nextDate->year])) }}" class="btn btn-sm btn-outline-secondary px-2" title="Bulan Berikutnya">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Filter Atasan / Pimpinan OTK -->
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label small fw-bold text-secondary mb-1">
                            <i class="bi bi-diagram-2 me-1"></i>Pimpinan / Atasan OTK
                        </label>
                        <select name="supervisor_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="all" {{ empty($selectedSupervisorId) ? 'selected' : '' }}>-- Seluruh Personel OTK UIS --</option>
                            @foreach($supervisorsList as $sup)
                                <option value="{{ $sup->id }}" {{ $selectedSupervisorId == $sup->id ? 'selected' : '' }}>
                                    {{ $sup->name }} &mdash; {{ $sup->jabatan ?: $sup->unit }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Bidang Koordinasi OTK -->
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label small fw-bold text-secondary mb-1">
                            <i class="bi bi-building me-1"></i>Bidang Koordinasi OTK
                        </label>
                        <select name="bidang_otk" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">-- Semua Bidang OTK --</option>
                            <option value="pimpinan_uis" {{ $selectedBidangOtk == 'pimpinan_uis' ? 'selected' : '' }}>Pimpinan Universitas (Rektor &amp; Warek)</option>
                            <option value="warek1" {{ $selectedBidangOtk == 'warek1' ? 'selected' : '' }}>Wakil Rektor I (Akademik &amp; BAAK)</option>
                            <option value="warek2" {{ $selectedBidangOtk == 'warek2' ? 'selected' : '' }}>Wakil Rektor II (Umum, Keuangan, SDM &amp; Sarpras)</option>
                            <option value="warek3" {{ $selectedBidangOtk == 'warek3' ? 'selected' : '' }}>Wakil Rektor III (Kemahasiswaan &amp; Kerjasama)</option>
                            <option value="feb" {{ $selectedBidangOtk == 'feb' ? 'selected' : '' }}>Fakultas Ekonomi dan Bisnis (FEB)</option>
                            <option value="fst" {{ $selectedBidangOtk == 'fst' ? 'selected' : '' }}>Fakultas Sains dan Teknologi (FST)</option>
                            <option value="fikes" {{ $selectedBidangOtk == 'fikes' ? 'selected' : '' }}>Fakultas Ilmu Kesehatan (FIKES)</option>
                            <option value="lembaga" {{ $selectedBidangOtk == 'lembaga' ? 'selected' : '' }}>Lembaga &amp; Perpustakaan (LPPM, LPMI, Perpustakaan)</option>
                        </select>
                    </div>

                </div>

                @if(request()->hasAny(['search', 'unit', 'supervisor_id', 'bidang_otk', 'status_pencatatan']))
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
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
        <div class="card-header bg-white py-3 px-4 border-0 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h5 class="fw-bold mb-0 text-dark" style="font-size: 1.05rem;">
                    Daftar Kinerja Anggota Tim Penempatan OTK (2024 - 2029)
                </h5>
                <small class="text-muted">Periode Pemantauan: <strong class="text-dark">{{ $namaBulanIndonesia }} {{ $tahun }}</strong> &bull; Diurutkan Berdasarkan Penempatan Resmi OTK</small>
            </div>
            <span class="badge bg-light text-dark border px-3 py-1.5 rounded-pill fw-semibold" style="font-size: 0.8rem;">
                {{ count($pegawaiData) }} Anggota Terdaftar
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 pemantauan-table" style="font-size: 0.84rem;">
                <thead>
                    <!-- Row 1: Header Grup Sesuai Standar OTK -->
                    <tr class="text-white text-center align-middle" style="background: linear-gradient(135deg, #15432D 0%, #1e5c3e 100%);">
                        <th rowspan="2" class="text-center py-3" style="width: 55px; border-bottom: none;">No</th>
                        <th rowspan="2" class="text-start py-3" style="min-width: 250px; border-bottom: none;">Pegawai</th>
                        <th colspan="2" class="text-center py-2" style="border-left: 1px solid rgba(255,255,255,0.15); border-right: 1px solid rgba(255,255,255,0.15); background: rgba(0,0,0,0.1);">
                            Bulan {{ $namaBulanIndonesia }} {{ $tahun }}
                        </th>
                        <th colspan="4" class="text-center py-2" style="border-right: 1px solid rgba(255,255,255,0.15); background: rgba(0,0,0,0.05);">
                            Tahun Kinerja Berjalan ({{ $tahun }})
                        </th>
                        <th rowspan="2" class="text-center py-3" style="width: 140px; border-bottom: none;">Aksi</th>
                    </tr>
                    <!-- Row 2: Sub-Header Kolom -->
                    <tr class="text-white text-center align-middle" style="background: #15432D; font-size: 0.77rem;">
                        <th class="py-2" style="border-left: 1px solid rgba(255,255,255,0.15); min-width: 105px;">Jum. Kegiatan</th>
                        <th class="py-2" style="border-right: 1px solid rgba(255,255,255,0.15); min-width: 110px;">Jum. Jam Kerja</th>
                        <th class="py-2" style="min-width: 75px;">Total</th>
                        <th class="py-2" style="min-width: 85px;">Rutin</th>
                        <th class="py-2" style="min-width: 95px;">Kepanitiaan</th>
                        <th class="py-2" style="border-right: 1px solid rgba(255,255,255,0.15); min-width: 85px;">Insidental</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pegawaiData as $index => $row)
                        @php
                            $pegawai = $row['pegawai'];
                            $hasActivities = $row['jum_kegiatan_bulan'] > 0;
                        @endphp
                        <tr>
                            <!-- No Urut OTK -->
                            <td class="text-center text-muted fw-bold">
                                {{ $loop->iteration }}
                            </td>

                            <!-- Profil Pegawai & Jabatan OTK -->
                            <td>
                                <div class="d-flex align-items-center">
                                    <!-- Avatar Circle -->
                                    <div class="avatar-circle rounded-circle text-white d-flex align-items-center justify-content-center fw-bold shadow-xs flex-shrink-0 me-3" 
                                         style="width: 38px; height: 38px; min-width: 38px; font-size: 12.5px; background-color: {{ $row['avatar_color'] }}; margin-right: 14px !important;">
                                        {{ $row['avatar_initial'] }}
                                    </div>
                                    <div class="lh-sm">
                                        <div class="fw-bold text-dark text-truncate" style="max-width: 250px;">
                                            {{ $pegawai->name }}
                                        </div>
                                        <div class="text-dark small fw-semibold text-truncate" style="max-width: 250px; font-size: 0.77rem; color: #15432D !important;">
                                            {{ $pegawai->jabatan ?: 'Staf' }}
                                        </div>
                                        <div class="text-muted text-truncate" style="font-size: 0.70rem;">
                                            {{ $pegawai->unit ?: 'Universitas Ibnu Sina' }}
                                            @if($pegawai->nidn) &bull; NIDN: {{ $pegawai->nidn }} @endif
                                        </div>
                                    </div>
                                </div>
                            </td>



                            <!-- Bulan Ini: Jum Kegiatan -->
                            <td class="text-center">
                                @if($hasActivities)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1.5 fw-bold" style="font-size: 0.82rem;">
                                        {{ $row['jum_kegiatan_bulan'] }}
                                    </span>
                                @else
                                    <span class="text-muted fw-medium small">0</span>
                                @endif
                            </td>

                            <!-- Bulan Ini: Jum Jam Kerja -->
                            <td class="text-center">
                                @if($row['jam_kerja_bulan'] > 0)
                                    <span class="badge bg-light text-dark border px-2.5 py-1.5 fw-bold" style="font-size: 0.82rem;">
                                        <i class="bi bi-clock me-1 text-primary"></i>{{ number_format($row['jam_kerja_bulan'], 1, ',', '.') }}
                                    </span>
                                @else
                                    <span class="text-muted small">0</span>
                                @endif
                            </td>

                            <!-- Tahun Berjalan: Total -->
                            <td class="text-center fw-bold text-dark">
                                {{ $row['total_tahunan'] }}
                            </td>

                            <!-- Tahun Berjalan: Rutin / Utama -->
                            <td class="text-center text-secondary fw-semibold">
                                {{ $row['rutin_tahunan'] }}
                            </td>

                            <!-- Tahun Berjalan: Kepanitiaan -->
                            <td class="text-center text-secondary fw-semibold">
                                {{ $row['kepanitiaan_tahunan'] }}
                            </td>

                            <!-- Tahun Berjalan: Insidental -->
                            <td class="text-center text-secondary fw-semibold">
                                {{ $row['insidentil_tahunan'] }}
                            </td>

                            <!-- Aksi -->
                            <td class="text-center">
                                <div class="d-inline-flex align-items-center gap-2">
                                    <!-- Tombol Pantau -->
                                    <button type="button" 
                                            class="btn btn-sm btn-dark d-inline-flex align-items-center gap-1 btn-pantau px-2.5 py-1.5 rounded-2 shadow-xs" 
                                            style="font-size: 0.78rem; background-color: #15432D; border-color: #15432D;"
                                            data-id="{{ $pegawai->id }}"
                                            data-name="{{ $pegawai->name }}"
                                            data-bulan="{{ $bulan }}"
                                            data-tahun="{{ $tahun }}"
                                            title="Pantau rincian kegiatan kinerja pegawai ini">
                                        <i class="bi bi-eye"></i>
                                        <span>Pantau</span>
                                    </button>

                                    <!-- Tombol WhatsApp -->
                                    @if(!empty($pegawai->nomor_wa))
                                        @php
                                            $cleanWa = preg_replace('/[^0-9]/', '', $pegawai->nomor_wa);
                                            if (str_starts_with($cleanWa, '0')) {
                                                $cleanWa = '62' . substr($cleanWa, 1);
                                            }
                                            $waMsg = urlencode("Halo {$pegawai->name}, saya melihat catatan pemantauan kinerja Anda untuk periode {$namaBulanIndonesia} {$tahun}.");
                                        @endphp
                                        <a href="https://wa.me/{{ $cleanWa }}?text={{ $waMsg }}" 
                                           target="_blank" 
                                           class="btn btn-sm btn-outline-success px-2 py-1.5 rounded-2 shadow-xs" 
                                           title="Kirim pesan WhatsApp ke {{ $pegawai->name }}">
                                            <i class="bi bi-whatsapp"></i>
                                        </a>
                                    @else
                                        <button type="button" class="btn btn-sm btn-light text-muted px-2 py-1.5 rounded-2" disabled title="Nomor WhatsApp belum didaftarkan">
                                            <i class="bi bi-chat-dots"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-diagram-3 display-4 d-block mb-3 text-secondary"></i>
                                    <h6 class="fw-bold">Tidak ada staf dalam penempatan OTK ini</h6>
                                    <p class="small mb-0">Coba ubah filter atasan, bidang koordinasi, atau kata kunci pencarian.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
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
                    <div>Total Tugas: <strong class="text-dark" id="modalTotalTugas">0</strong></div>
                    <div>Total Jam: <strong class="text-success" id="modalTotalJam">0 Jam</strong></div>
                    <div>Selesai: <strong class="text-primary" id="modalSelesai">0</strong></div>
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
    const modalEl = document.getElementById('modalPantauStaff');
    const modal = new bootstrap.Modal(modalEl);

    const spinner = document.getElementById('modalLoadingSpinner');
    const container = document.getElementById('modalContentContainer');
    const listBody = document.getElementById('modalTaskListBody');
    const emptyNotice = document.getElementById('modalEmptyNotice');

    document.querySelectorAll('.btn-pantau').forEach(button => {
        button.addEventListener('click', function () {
            const userId = this.getAttribute('data-id');
            const userName = this.getAttribute('data-name');
            const bulan = this.getAttribute('data-bulan');
            const tahun = this.getAttribute('data-tahun');

            // Reset modal state
            document.getElementById('modalStaffName').textContent = userName;
            document.getElementById('modalStaffSub').textContent = 'Memuat informasi...';
            document.getElementById('modalStaffAtasan').textContent = '';
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

                    // WhatsApp button in modal footer
                    const waContainer = document.getElementById('modalWaContainer');
                    if (p.nomor_wa) {
                        let cleanWa = p.nomor_wa.replace(/\D/g, '');
                        if (cleanWa.startsWith('0')) cleanWa = '62' + cleanWa.substring(1);
                        const msg = encodeURIComponent(`Halo ${p.name}, saya melihat catatan kegiatan Anda untuk periode {{ $namaBulanIndonesia }} {{ $tahun }}.`);
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

                            let row = `
                                <tr>
                                    <td class="text-muted">${idx + 1}</td>
                                    <td>
                                        <div class="fw-semibold text-dark">${t.uraian_tugas}</div>
                                        <small class="text-muted">${t.tanggal_mulai}</small>
                                    </td>
                                    <td><span class="badge bg-light text-dark border">${t.kategori}</span></td>
                                    <td class="text-center fw-bold text-dark">${t.durasi_jam} Jam</td>
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
});
</script>
@endsection
