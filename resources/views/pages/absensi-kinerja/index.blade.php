@extends('layouts.dashboard.template')

@section('content')
<style>
    .matrix-table {
        border-collapse: separate !important;
        border-spacing: 0 !important;
    }
    .matrix-col-staff {
        min-width: 380px !important;
        width: 380px !important;
        position: sticky !important;
        left: 0 !important;
        white-space: nowrap !important;
    }
    .matrix-col-staff * {
        white-space: nowrap !important;
    }
</style>
<div class="pagetitle">
    <h1>Rekapitulasi Absensi</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item">Manajemen</li>
            <li class="breadcrumb-item active">Rekapitulasi Absensi</li>
        </ol>
    </nav>
</div>

<section class="section">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-octagon me-1"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Tab Switcher Navigation -->
    <ul class="nav nav-tabs nav-tabs-bordered mb-3" id="tabAbsensi" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-bold text-dark px-3.5 py-2.5 d-inline-flex align-items-center gap-2" id="monitoring-harian-tab" data-bs-toggle="tab" data-bs-target="#monitoring-harian" type="button" role="tab" aria-controls="monitoring-harian" aria-selected="true">
                <i class="bi bi-grid-3x3-gap-fill text-primary" style="font-size: 1.05rem; margin-right: 8px;"></i>
                <span>Monitoring Harian</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold text-secondary px-3.5 py-2.5 d-inline-flex align-items-center gap-2" id="rekap-karyawan-tab" data-bs-toggle="tab" data-bs-target="#rekap-karyawan" type="button" role="tab" aria-controls="rekap-karyawan" aria-selected="false">
                <i class="bi bi-person-lines-fill text-success" style="font-size: 1.05rem; margin-right: 8px;"></i>
                <span>Rekap Per Karyawan</span>
            </button>
        </li>
    </ul>

    <div class="tab-content" id="tabAbsensiContent">

        <!-- ======================================================== -->
        <!-- TAB 1: MONITORING HARIAN (MATRIX VIEW SESUAI GAMBAR) -->
        <!-- ======================================================== -->
        <div class="tab-pane fade show active" id="monitoring-harian" role="tabpanel" aria-labelledby="monitoring-harian-tab">
            <div class="card shadow-sm border-0 mb-4 rounded-3">
                <div class="card-body p-3.5">
                    <h5 class="fw-bold mb-3 text-dark fs-6 d-flex align-items-center gap-2">
                        <i class="bi bi-calendar3-range text-primary"></i>
                        <span>Monitoring Kehadiran (Matrix)</span>
                    </h5>

                    <!-- Toolbar Filter & Tombol Isi Absen Manual -->
                    <form id="form-filter-matrix" action="{{ route('absensi-kinerja.index') }}" method="GET" class="row g-2.5 align-items-end mb-4">
                        <div class="col-6 col-md-3 col-lg-2">
                            <label class="form-label small fw-bold text-secondary mb-1">Bulan</label>
                            <select name="bulan" id="matrix-select-bulan" class="form-select form-select-sm">
                                @php
                                    $bulanMap = [
                                        1 => 'januari', 2 => 'februari', 3 => 'maret', 4 => 'april',
                                        5 => 'mei', 6 => 'juni', 7 => 'juli', 8 => 'agustus',
                                        9 => 'september', 10 => 'oktober', 11 => 'november', 12 => 'desember'
                                    ];
                                @endphp
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" data-slug="{{ $bulanMap[$m] }}" {{ $bulan == $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::createFromDate(2026, $m, 1)->translatedFormat('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-6 col-md-2 col-lg-2">
                            <label class="form-label small fw-bold text-secondary mb-1">Tahun</label>
                            <select name="tahun" id="matrix-select-tahun" class="form-select form-select-sm">
                                @for($y = 2024; $y <= 2030; $y++)
                                    <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>

                        @if($isAdmin || $isPimpinan)
                            <div class="col-12 col-md-4 col-lg-3">
                                <label class="form-label small fw-bold text-secondary mb-1">Filter Pegawai</label>
                                <select name="filter_user_matrix" id="matrix-select-user" class="form-select form-select-sm">
                                    <option value="" data-slug="">Semua Pegawai Terkait</option>
                                    @foreach($users as $u)
                                        <option value="{{ $u->id }}" data-slug="{{ \Illuminate\Support\Str::slug($u->name) }}" {{ (request('filter_user_matrix') == $u->id || (isset($selectedUserSlug) && $selectedUserSlug == \Illuminate\Support\Str::slug($u->name))) ? 'selected' : '' }}>
                                            {{ $u->name }} ({{ $u->jabatan ?? '-' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="col-auto">
                            <button type="submit" class="btn btn-sm btn-primary px-3 fw-semibold d-inline-flex align-items-center gap-1 shadow-sm">
                                <i class="bi bi-search"></i>
                                <span>Tampilkan</span>
                            </button>
                        </div>

                        <div class="col-auto ms-auto d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-success fw-bold px-3 shadow-sm d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#modalIsiAbsenManual" style="background-color: #15432D; border-color: #15432D;">
                                <i class="bi bi-pencil-square"></i>
                                <span>Isi Absen Manual</span>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary px-3 d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#modalExportAbsensi">
                                <i class="bi bi-download"></i>
                                <span>Export</span>
                            </button>
                        </div>
                    </form>

                    <!-- Table Matrix View (Tanggal 01 s/d Akhir Bulan) -->
                    <div class="table-responsive rounded-2 border shadow-xs mb-4 w-100" style="overflow-x: auto;">
                        <table class="table table-bordered table-sm align-middle mb-0 matrix-table w-100" style="font-size: 0.77rem; min-width: 100%;">
                            <thead>
                                <!-- Baris 1: Header Grup Periode -->
                                <tr class="text-white text-center fw-bold" style="background-color: #0d6efd;">
                                    <th rowspan="2" class="text-white align-middle px-3 text-start text-nowrap matrix-col-staff" style="min-width: 380px; width: 380px; background-color: #0b5ed7; position: sticky; left: 0; z-index: 10;">
                                        Nama Staff
                                    </th>
                                    <th colspan="{{ count($matrixDays) }}" class="text-center py-1.5 text-white" style="background-color: #0d6efd; letter-spacing: 0.5px;">
                                        Monitoring Kehadiran - Periode {{ $namaBulanIndonesia }} {{ $tahun }}
                                    </th>
                                    <th rowspan="2" class="text-white align-middle text-center px-2 text-nowrap" style="width: 48px; background-color: #0b5ed7;">Sakit</th>
                                    <th rowspan="2" class="text-white align-middle text-center px-2 text-nowrap" style="width: 48px; background-color: #0b5ed7;">Izin</th>
                                    <th rowspan="2" class="text-white align-middle text-center px-2 text-nowrap" style="width: 48px; background-color: #0b5ed7;">Alfa</th>
                                    <th rowspan="2" class="text-white align-middle text-center px-2 text-nowrap" style="width: 48px; background-color: #0b5ed7;">Hadir</th>
                                    <th rowspan="2" class="text-white align-middle text-center px-2 text-nowrap" style="width: 65px; background-color: #0b5ed7;">Persentase</th>
                                </tr>
                                <!-- Baris 2: Nama Hari & Angka Tanggal (01, 02, ... 31) -->
                                <tr class="text-center align-middle" style="background-color: #f1f5f9; font-size: 0.69rem;">
                                    @foreach($matrixDays as $d)
                                        <th class="p-1 {{ $d['is_sunday'] ? 'text-danger bg-danger-subtle bg-opacity-50' : 'text-primary' }}" style="min-width: 32px; font-weight: 600;">
                                            <div>{{ substr($d['day_name'], 0, 3) }}</div>
                                            <div class="fw-bold fs-7 {{ $d['is_sunday'] ? 'text-danger' : 'text-dark' }}">{{ $d['day_padded'] }}</div>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($matrixData as $row)
                                    <tr>
                                        <!-- Identitas Pegawai (Nama, Jabatan OTK, NUPTK/NUP) -->
                                        <td class="px-3 py-2 text-start bg-white text-nowrap matrix-col-staff" style="position: sticky; left: 0; z-index: 5; box-shadow: 2px 0 5px rgba(0,0,0,0.06); min-width: 380px; width: 380px; white-space: nowrap;">
                                            <div class="fw-bold text-dark lh-sm text-nowrap" style="font-size: 0.85rem; white-space: nowrap;">
                                                {{ $row['user']->name }}
                                            </div>
                                            <div class="text-dark fw-semibold mt-1 text-uppercase text-nowrap" style="font-size: 0.73rem; color: #15432D !important; line-height: 1.25; white-space: nowrap;">
                                                {{ $row['user']->jabatan ?: 'Staff' }}
                                            </div>
                                            <div class="text-muted mt-0.5 text-nowrap" style="font-size: 0.70rem; white-space: nowrap;">
                                                NUPTK/NUP: {{ $row['user']->nidn ?: '-' }}
                                            </div>
                                        </td>

                                        <!-- Kolom Tanggal 01 s/d Akhir Bulan -->
                                        @foreach($matrixDays as $d)
                                            @php
                                                $cell = $row['days'][$d['day']];
                                                $st = $cell['status'];
                                            @endphp
                                            <td class="text-center p-1 {{ $d['is_sunday'] ? 'bg-danger-subtle bg-opacity-25' : '' }}">
                                                @if($st === 'H')
                                                    <span class="badge bg-success text-white fw-bold d-inline-flex align-items-center justify-content-center shadow-xs" 
                                                          style="width: 22px; height: 22px; font-size: 0.72rem; border-radius: 4px;"
                                                          data-bs-toggle="tooltip" title="{{ $cell['tooltip'] ?: 'Hadir' }}">
                                                        H
                                                    </span>
                                                @elseif($st === 'S')
                                                    <span class="badge bg-primary text-white fw-bold d-inline-flex align-items-center justify-content-center shadow-xs" 
                                                          style="width: 22px; height: 22px; font-size: 0.72rem; border-radius: 4px;"
                                                          data-bs-toggle="tooltip" title="{{ $cell['tooltip'] ?: 'Sakit' }}">
                                                        S
                                                    </span>
                                                @elseif($st === 'I')
                                                    <span class="badge bg-warning text-dark fw-bold d-inline-flex align-items-center justify-content-center shadow-xs" 
                                                          style="width: 22px; height: 22px; font-size: 0.72rem; border-radius: 4px;"
                                                          data-bs-toggle="tooltip" title="{{ $cell['tooltip'] ?: 'Izin' }}">
                                                        I
                                                    </span>
                                                @elseif($st === 'A')
                                                    <span class="badge bg-danger text-white fw-bold d-inline-flex align-items-center justify-content-center shadow-xs" 
                                                          style="width: 22px; height: 22px; font-size: 0.72rem; border-radius: 4px;"
                                                          data-bs-toggle="tooltip" title="{{ $cell['tooltip'] ?: 'Alfa (Tanpa Keterangan)' }}">
                                                        A
                                                    </span>
                                                @else
                                                    <span class="text-muted" style="font-size: 0.75rem;">-</span>
                                                @endif
                                            </td>
                                        @endforeach

                                        <!-- Summary Counts -->
                                        <td class="text-center fw-semibold text-secondary">{{ $row['sakit'] > 0 ? $row['sakit'] : '-' }}</td>
                                        <td class="text-center fw-semibold text-secondary">{{ $row['izin'] > 0 ? $row['izin'] : '-' }}</td>
                                        <td class="text-center fw-semibold text-secondary">{{ $row['alfa'] > 0 ? $row['alfa'] : '-' }}</td>
                                        <td class="text-center fw-bold text-success">{{ $row['hadir'] }}</td>
                                        <td class="text-center fw-bold text-dark">{{ $row['persentase'] }}%</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ count($matrixDays) + 6 }}" class="text-center py-4 text-muted">
                                            <i class="bi bi-info-circle me-1"></i>Belum ada data pegawai yang tersedia pada filter ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Status Keterangan (Legend Card Sesuai Gambar) -->
                    <div class="card border-0 shadow-none rounded-3 p-3" style="background-color: #fffdf0; border: 1px solid #fef08a !important;">
                        <h6 class="fw-bold mb-2 text-dark" style="font-size: 0.88rem;">Status Keterangan</h6>
                        <div class="d-flex flex-wrap align-items-center gap-4 text-dark" style="font-size: 0.82rem;">
                            <div class="d-flex align-items-center gap-1.5">
                                <span class="badge bg-success fw-bold px-1.5 py-0.5" style="border-radius: 3px;">H</span>
                                <span class="fw-semibold">: Hadir</span>
                            </div>
                            <div class="d-flex align-items-center gap-1.5">
                                <span class="badge bg-primary fw-bold px-1.5 py-0.5" style="border-radius: 3px;">S</span>
                                <span class="fw-semibold">: Sakit</span>
                            </div>
                            <div class="d-flex align-items-center gap-1.5">
                                <span class="badge bg-warning text-dark fw-bold px-1.5 py-0.5" style="border-radius: 3px;">I</span>
                                <span class="fw-semibold">: Izin</span>
                            </div>
                            <div class="d-flex align-items-center gap-1.5">
                                <span class="badge bg-danger fw-bold px-1.5 py-0.5" style="border-radius: 3px;">A</span>
                                <span class="fw-semibold">: Alfa (Tanpa Keterangan)</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- TAB 2: REKAP PER KARYAWAN (DETAIL ABSENSI HARIAN) -->
        <!-- ======================================================== -->
        <div class="tab-pane fade" id="rekap-karyawan" role="tabpanel" aria-labelledby="rekap-karyawan-tab">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                    <h5 class="mb-0 fw-semibold">Daftar Absensi Kinerja Harian</h5>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <small class="text-muted">Target harian: 7 jam kerja + 1 jam istirahat (total 8 jam).</small>
                        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalExportAbsensi">
                            <i class="bi bi-file-earmark-arrow-down me-1"></i>Export
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3 my-1">
                        <div class="col-sm-6 col-lg-3">
                            <label for="filter-dari" class="form-label">Tanggal dari</label>
                            <input type="date" id="filter-dari" class="form-control">
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <label for="filter-sampai" class="form-label">Tanggal sampai</label>
                            <input type="date" id="filter-sampai" class="form-control">
                        </div>
                        @if($hasPimpinanScope)
                            <div class="col-sm-6 col-lg-3">
                                <label for="filter-cakupan" class="form-label">Tampilkan absensi</label>
                                <select id="filter-cakupan" class="form-select">
                                    <option value="semua">Absensi saya dan bawahan</option>
                                    <option value="saya">Absensi saya</option>
                                    <option value="bawahan">Absensi bawahan</option>
                                </select>
                            </div>
                        @endif
                        @if($isAdmin || $isPimpinan)
                            <div class="col-sm-8 col-lg-{{ $hasPimpinanScope ? '3' : '4' }}">
                                <label for="filter-user" class="form-label">Pegawai</label>
                                <select id="filter-user" class="form-select">
                                    <option value="">{{ $hasPimpinanScope ? 'Semua pegawai terkait' : 'Semua pegawai' }}</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->jabatan ?? '-' }} - {{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="col-sm-4 col-lg-2 d-flex align-items-end">
                            <button type="button" id="reset-filter" class="btn btn-outline-secondary w-100">Reset filter</button>
                        </div>
                    </div>

                    <div class="table-responsive mt-3">
                        <table id="absensi-kinerja-table" class="table table-striped table-bordered align-middle w-100">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    @if($isAdmin || $isPimpinan)
                                        <th>Nama Pegawai</th>
                                        <th>Jabatan</th>
                                    @endif
                                    <th class="text-nowrap" style="min-width: 155px;">Tanggal</th>
                                    <th class="text-nowrap" style="min-width: 105px;">Datang</th>
                                    <th class="text-nowrap" style="min-width: 105px;">Pulang</th>
                                    <th>Total Durasi Kerja</th>
                                    <th>Kategori</th>
                                    <th>Keterangan Kerja</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- ======================================================== -->
<!-- MODAL: ISI ABSEN MANUAL (SAKIT, IZIN, ALFA, HADIR) -->
<!-- ======================================================== -->
<div class="modal fade" id="modalIsiAbsenManual" tabindex="-1" aria-labelledby="modalIsiAbsenManualLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <form action="{{ route('absensi-kinerja.manual') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header text-white px-4 py-3" style="background: linear-gradient(135deg, #15432D 0%, #1e5c3e 100%);">
                    <h5 class="modal-title fw-bold fs-6 d-flex align-items-center gap-2" id="modalIsiAbsenManualLabel">
                        <i class="bi bi-pencil-square"></i>
                        <span>Isi Absen Manual (Sakit / Izin / Alfa / Hadir)</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small mb-1">Pilih Pegawai <span class="text-danger">*</span></label>
                        <select name="user_id" class="form-select form-select-sm" required>
                            @if(!$isAdmin && !$isPimpinan)
                                <option value="{{ auth()->user()->id }}" selected>{{ auth()->user()->name }} ({{ auth()->user()->jabatan ?? 'Staff' }})</option>
                            @else
                                <option value="">-- Pilih Pegawai --</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" {{ $u->id === auth()->user()->id ? 'selected' : '' }}>
                                        {{ $u->name }} ({{ $u->jabatan ?? '-' }})
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold text-dark small mb-1">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_dari" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold text-dark small mb-1">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_sampai" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small mb-1">Status Kehadiran <span class="text-danger">*</span></label>
                        <select name="status" id="manual_status" class="form-select form-select-sm" required onchange="toggleManualJam(this.value)">
                            <option value="Sakit">Sakit (S)</option>
                            <option value="Izin">Izin (I)</option>
                            <option value="Alfa">Alfa / Tanpa Keterangan (A)</option>
                            <option value="Hadir">Hadir Manual (H)</option>
                        </select>
                    </div>

                    <div class="row g-2 mb-3 d-none" id="wrapper_manual_jam">
                        <div class="col-6">
                            <label class="form-label fw-bold text-dark small mb-1">Jam Datang</label>
                            <input type="time" name="waktu_datang" class="form-control form-control-sm" value="08:00">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold text-dark small mb-1">Jam Pulang</label>
                            <input type="time" name="waktu_pulang" class="form-control form-control-sm" value="16:00">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small mb-1">Keterangan / Catatan</label>
                        <textarea name="catatan" class="form-control form-control-sm" rows="2" placeholder="Contoh: Sakit demam, Izin urusan dinas luar, dll."></textarea>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-bold text-dark small mb-1">Bukti Surat / Dokumen (Opsional)</label>
                        <input type="file" name="bukti_izin" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png">
                        <small class="text-muted" style="font-size: 0.72rem;">Format: PDF, JPG, PNG (Maks 5MB)</small>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-success fw-bold px-3" style="background-color: #15432D; border-color: #15432D;">
                        <i class="bi bi-save me-1"></i>Simpan Absensi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL: EXPORT ABSENSI -->
<!-- ======================================================== -->
<div class="modal fade" id="modalExportAbsensi" tabindex="-1" aria-labelledby="modalExportAbsensiLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header text-white px-4 py-3" style="background-color: #15432d;">
                <h5 class="modal-title fw-bold fs-6 d-flex align-items-center gap-2" id="modalExportAbsensiLabel">
                    <i class="bi bi-file-earmark-arrow-down-fill"></i>
                    <span>Export Absensi Kinerja</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold text-dark small mb-1">Pilihan Filter Waktu</label>
                    <div class="d-flex flex-wrap gap-3 p-2 bg-light rounded border">
                        <div class="form-check mb-0">
                            <input class="form-check-input export-absensi-filter-choice" type="radio" name="export-absensi-filter-type" id="export-absensi-filter-month-year" value="month_year" checked>
                            <label class="form-check-label fw-semibold text-dark" for="export-absensi-filter-month-year">
                                <i class="bi bi-calendar-month me-1 text-primary"></i>Bulan & Tahun
                            </label>
                        </div>
                        <div class="form-check mb-0">
                            <input class="form-check-input export-absensi-filter-choice" type="radio" name="export-absensi-filter-type" id="export-absensi-filter-range" value="range">
                            <label class="form-check-label fw-semibold text-dark" for="export-absensi-filter-range">
                                <i class="bi bi-calendar-range me-1 text-info"></i>Rentang Tanggal
                            </label>
                        </div>
                        @if($periods->isNotEmpty())
                            <div class="form-check mb-0">
                                <input class="form-check-input export-absensi-filter-choice" type="radio" name="export-absensi-filter-type" id="export-absensi-filter-periode" value="periode">
                                <label class="form-check-label fw-semibold text-dark" for="export-absensi-filter-periode">
                                    <i class="bi bi-mortarboard me-1 text-success"></i>Periode Akademik
                                </label>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="mb-3" id="wrapper-export-absensi-month-year">
                    <label class="form-label fw-bold text-dark small mb-1">Bulan & Tahun</label>
                    <div class="row g-2">
                        <div class="col-6">
                            <label for="export-absensi-bulan" class="form-label small text-muted mb-1">Bulan</label>
                            <select id="export-absensi-bulan" class="form-select">
                                <option value="all">Semua Bulan (1 Tahun Penuh)</option>
                                @foreach([
                                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                ] as $mNum => $mName)
                                    <option value="{{ $mNum }}" {{ $mNum == ($bulan ?? now()->month) ? 'selected' : '' }}>{{ $mName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="export-absensi-tahun" class="form-label small text-muted mb-1">Tahun</label>
                            <select id="export-absensi-tahun" class="form-select">
                                @php
                                    $curYear = (int) ($tahun ?? now()->year);
                                    $startYear = min($curYear - 2, 2024);
                                    $endYear = max($curYear + 4, 2030);
                                @endphp
                                @for($y = $startYear; $y <= $endYear; $y++)
                                    <option value="{{ $y }}" {{ $y == $curYear ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>
                <div class="mb-3 d-none" id="wrapper-export-absensi-range">
                    <label class="form-label fw-bold text-dark small mb-1">Rentang Tanggal</label>
                    <div class="row g-2">
                        <div class="col-6">
                            <label for="export-absensi-dari" class="form-label small text-muted mb-1">Tanggal dari</label>
                            <input type="date" id="export-absensi-dari" class="form-control">
                        </div>
                        <div class="col-6">
                            <label for="export-absensi-sampai" class="form-label small text-muted mb-1">Tanggal sampai</label>
                            <input type="date" id="export-absensi-sampai" class="form-control">
                        </div>
                    </div>
                </div>
                @if($periods->isNotEmpty())
                    <div class="mb-3 d-none" id="wrapper-export-absensi-periode">
                        <label for="export-absensi-periode" class="form-label fw-bold text-dark small mb-1">Periode Akademik</label>
                        <select id="export-absensi-periode" class="form-select">
                            <option value="">Pilih periode akademik</option>
                            @foreach($periods as $period)
                                <option value="{{ $period->id }}">{{ $period->nama_periode }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                @if($isAdmin || $isPimpinan)
                    <div class="mb-3">
                        <label for="export-absensi-user" class="form-label fw-bold text-dark small mb-1">Pegawai / Jabatan</label>
                        <select id="export-absensi-user" class="form-select">
                            <option value="">{{ $hasPimpinanScope ? 'Semua pegawai terkait' : 'Semua pegawai' }}</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->jabatan ?? '-' }} - {{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="p-2 rounded bg-light border text-muted small d-flex align-items-start gap-2">
                    <i class="bi bi-info-circle-fill text-success"></i>
                    <span>Data mengikuti periode atau rentang tanggal, pegawai/jabatan, dan pencarian pada tabel.</span>
                </div>
            </div>
            <div class="modal-footer bg-light px-4 py-3 d-flex justify-content-between">
                <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Batal</button>
                <div class="d-flex gap-2">
                    <button type="button" id="btn-export-absensi-excel" class="btn btn-success px-3 fw-bold">
                        <i class="bi bi-file-earmark-excel-fill me-1"></i>Unduh Excel
                    </button>
                    <button type="button" id="btn-export-absensi-pdf" class="btn btn-danger px-3 fw-bold">
                        <i class="bi bi-file-earmark-pdf-fill me-1"></i>Unduh PDF
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleManualJam(status) {
        if (status === 'Hadir') {
            $('#wrapper_manual_jam').removeClass('d-none');
        } else {
            $('#wrapper_manual_jam').addClass('d-none');
        }
    }

    $(function () {
        // Inisialisasi tooltip bootstrap
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        const canFilterPegawai = @json($isAdmin || $isPimpinan);
        const canFilterCakupan = @json($hasPimpinanScope);
        if (canFilterPegawai && $.fn.select2) {
            $('#filter-user').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: canFilterCakupan ? '-- Cari nama pimpinan / bawahan --' : '-- Cari nama pegawai --',
                allowClear: true
            });
        }
        const columns = [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false }
        ];

        if (canFilterPegawai) {
            columns.push(
                { data: 'nama_pegawai', name: 'user.name' },
                { data: 'jabatan', name: 'user.jabatan' }
            );
        }

        columns.push(
            { data: 'tanggal_fmt', name: 'tanggal', width: '155px', className: 'text-nowrap' },
            { data: 'datang_col', name: 'waktu_datang', orderable: false, searchable: false, width: '105px', className: 'text-nowrap' },
            { data: 'pulang_col', name: 'waktu_pulang', orderable: false, searchable: false, width: '105px', className: 'text-nowrap' },
            { data: 'total_durasi_col', name: 'total_durasi_col', orderable: false, searchable: false },
            { data: 'kategori_col', name: 'kategori_col', orderable: false, searchable: false, className: 'align-top' },
            { data: 'keterangan_col', name: 'keterangan_kerja', orderable: false, searchable: false }
        );

        const table = $('#absensi-kinerja-table').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: @json(route('absensi-kinerja.index')),
                data: function (data) {
                    data.dari = $('#filter-dari').val();
                    data.sampai = $('#filter-sampai').val();
                    if (canFilterPegawai) {
                        data.user_id = $('#filter-user').val();
                    }
                    if (canFilterCakupan) {
                        data.cakupan = $('#filter-cakupan').val();
                    }
                }
            },
            columns: columns,
            order: [[canFilterPegawai ? 3 : 1, 'desc']],
            language: {
                processing: 'Memuat data...',
                emptyTable: 'Belum ada data absensi kinerja.',
                zeroRecords: 'Data tidak ditemukan.',
                search: 'Cari:',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                infoEmpty: 'Tidak ada data',
                paginate: { previous: 'Sebelumnya', next: 'Berikutnya' }
            }
        });

        $('#filter-dari, #filter-sampai, #filter-user, #filter-cakupan').on('change', function () {
            if (this.id === 'filter-cakupan') {
                $('#filter-user').val('').trigger('change.select2');
            }
            table.ajax.reload();
        });

        $('#reset-filter').on('click', function () {
            $('#filter-dari, #filter-sampai').val('');
            $('#filter-user').val('').trigger('change.select2');
            $('#filter-cakupan').val('semua');
            table.ajax.reload();
        });

        $('#modalExportAbsensi').on('show.bs.modal', function () {
            const hasDateFilter = Boolean($('#filter-dari').val() || $('#filter-sampai').val());
            const defaultFilterType = hasDateFilter ? 'range' : 'month_year';
            $('input[name="export-absensi-filter-type"][value="' + defaultFilterType + '"]').prop('checked', true).trigger('change');
            $('#export-absensi-dari').val($('#filter-dari').val());
            $('#export-absensi-sampai').val($('#filter-sampai').val());
            if (defaultFilterType === 'periode' && !$('#export-absensi-periode').val()) {
                $('#export-absensi-periode').val($('#export-absensi-periode option:eq(1)').val());
            }
            if (canFilterPegawai) {
                $('#export-absensi-user').val($('#filter-user').val() || '');
            }
        });

        $('.export-absensi-filter-choice').on('change', function () {
            const filterType = $(this).val();
            $('#wrapper-export-absensi-month-year').toggleClass('d-none', filterType !== 'month_year');
            $('#wrapper-export-absensi-periode').toggleClass('d-none', filterType !== 'periode');
            $('#wrapper-export-absensi-range').toggleClass('d-none', filterType !== 'range');
        });

        if (canFilterPegawai && $.fn.select2) {
            $('#export-absensi-user').select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: $('#modalExportAbsensi'),
                placeholder: '-- Cari nama pegawai / jabatan --',
                allowClear: true,
                minimumResultsForSearch: 0
            });
        }
        if ($('#export-absensi-periode').length && $.fn.select2) {
            $('#export-absensi-periode').select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: $('#modalExportAbsensi'),
                placeholder: 'Pilih periode akademik',
                allowClear: true,
                minimumResultsForSearch: 0
            });
        }

        function exportAbsensi(format) {
            const params = new URLSearchParams();
            const filterType = $('input[name="export-absensi-filter-type"]:checked').val() || 'month_year';
            const search = table.search();
            if (filterType === 'month_year') {
                const bVal = $('#export-absensi-bulan').val();
                const yVal = $('#export-absensi-tahun').val();
                if (bVal && bVal !== 'all') {
                    params.set('bulan', bVal);
                }
                if (yVal) {
                    params.set('tahun', yVal);
                }
            } else if (filterType === 'periode') {
                const periodId = $('#export-absensi-periode').val();
                if (!periodId) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Periode belum dipilih',
                            text: 'Pilih periode akademik untuk melanjutkan export.',
                            confirmButtonColor: '#15432d'
                        });
                    }
                    return;
                }
                params.set('periode_akademik_id', periodId);
            } else {
                const dari = $('#export-absensi-dari').val();
                const sampai = $('#export-absensi-sampai').val();

                if (dari && sampai && dari > sampai) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Rentang tanggal tidak valid',
                            text: 'Tanggal dari tidak boleh melewati tanggal sampai.',
                            confirmButtonColor: '#15432d'
                        });
                    }
                    return;
                }
                if (dari) params.set('dari', dari);
                if (sampai) params.set('sampai', sampai);
            }
            if (canFilterPegawai && $('#export-absensi-user').val()) {
                params.set('user_id', $('#export-absensi-user').val());
            }
            if (search) params.set('search', search);

            const query = params.toString();
            const url = format === 'pdf'
                ? @json(route('absensi-kinerja.export-pdf'))
                : @json(route('absensi-kinerja.export'));
            const exportUrl = url + (query ? '?' + query : '');
            if (format === 'pdf') {
                window.open(exportUrl, '_blank', 'noopener');
            } else {
                window.location.href = exportUrl;
            }
        }

        $('#btn-export-absensi-excel').on('click', function () {
            exportAbsensi('excel');
        });
        $('#btn-export-absensi-pdf').on('click', function () {
            exportAbsensi('pdf');
        });

        // Intercept form filter matrix agar selalu menggunakan Slug URL di browser (tanpa query string ?bulan=X&tahun=Y)
        $('#form-filter-matrix').on('submit', function (e) {
            e.preventDefault();
            const bSlug = $('#matrix-select-bulan option:selected').data('slug') || $('#matrix-select-bulan').val();
            const yVal = $('#matrix-select-tahun').val();
            const uSlug = $('#matrix-select-user option:selected').data('slug');

            let targetUrl = "{{ url('absensi-kinerja') }}/" + bSlug + "-" + yVal;
            if (uSlug) {
                targetUrl += "/" + uSlug;
            }
            window.location.href = targetUrl;
        });

        window.setInterval(function () {
            table.ajax.reload(null, false);
        }, 60000);
    });
</script>
@endpush
