@extends('layouts.dashboard.template')

@section('content')
@php
    $mhs = $laporan->data_mahasiswa ?? [];
    $prodiList = $laporan->data_prodi ?? [];
    $prestasi = $laporan->data_prestasi ?? [];
    $sdm = $laporan->data_sdm ?? [];
    $riset = $laporan->data_penelitian_pkm ?? [];
    $kerjasama = $laporan->data_kerjasama ?? [];
    $sarpras = $laporan->data_sarpras ?? [];
@endphp

<div class="pagetitle mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h1 class="mb-1 text-dark fw-bold fs-4">{{ $laporan->judul }}</h1>
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('laporan-pengembangan.index') }}">Laporan Pengembangan</a></li>
                    <li class="breadcrumb-item active text-success fw-semibold">Detail Dokumen</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('laporan-pengembangan.export-pdf', $laporan->id) }}" class="btn btn-danger d-inline-flex align-items-center gap-2 px-3 py-2 rounded-3 shadow-sm fw-semibold text-sm">
                <i class="bi bi-file-earmark-pdf-fill"></i>
                <span>Cetak PDF Resmi</span>
            </a>
            <a href="{{ route('laporan-pengembangan.export-excel', $laporan->id) }}" class="btn btn-success d-inline-flex align-items-center gap-2 px-3 py-2 rounded-3 shadow-sm fw-semibold text-sm">
                <i class="bi bi-file-earmark-excel-fill"></i>
                <span>Export Excel</span>
            </a>
            @if(Auth::user()->isAdmin() || Auth::user()->isSuperAdmin())
                <a href="{{ route('laporan-pengembangan.edit', $laporan->id) }}" class="btn btn-warning d-inline-flex align-items-center gap-2 px-3 py-2 rounded-3 shadow-sm fw-semibold text-sm text-dark">
                    <i class="bi bi-pencil-fill"></i>
                    <span>Edit Data</span>
                </a>
            @endif
            <a href="{{ route('laporan-pengembangan.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1.5 px-3 py-2 rounded-3 text-sm">
                <i class="bi bi-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Document Meta Banner -->
<div class="card border-0 shadow-sm rounded-4 mb-4" style="border-left: 5px solid #15432D !important; background: #ffffff;">
    <div class="card-body p-3">
        <div class="row g-2 align-items-center text-sm">
            <div class="col-md-3">
                <span class="text-muted d-block small">Institusi:</span>
                <strong class="text-success">Universitas Ibnu Sina (UIS)</strong>
            </div>
            <div class="col-md-3">
                <span class="text-muted d-block small">Yayasan Pengelola:</span>
                <strong class="text-dark">Yayasan Pendidikan Ibnu Sina Batam (YAPISTA)</strong>
            </div>
            <div class="col-md-3">
                <span class="text-muted d-block small">Tahun / Periode:</span>
                <strong class="text-primary">{{ $laporan->tahun_akademik ?: '-' }} ({{ $laporan->periodeAkademik ? $laporan->periodeAkademik->nama_periode : 'Semua Periode' }})</strong>
            </div>
            <div class="col-md-3">
                <span class="text-muted d-block small">Terakhir Diperbarui:</span>
                <strong class="text-muted">{{ $laporan->updated_at ? $laporan->updated_at->format('d F Y, H:i') : '-' }}</strong>
            </div>
        </div>
    </div>
</div>

<!-- BAB I: CAPAIAN PROGRAM KERJA STRATEGIS -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-bottom p-3">
        <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
            <span class="badge bg-success rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">I</span>
            Capaian Program Kerja & Target Pengembangan Universitas
        </h5>
    </div>
    <div class="card-body p-4">
        @if(!empty($laporan->capaian_program_kerja))
            <p class="text-dark mb-0" style="line-height: 1.8; text-align: justify; white-space: pre-line;">{{ $laporan->capaian_program_kerja }}</p>
        @else
            <p class="text-muted fst-italic mb-0">Belum ada uraian narasi program kerja strategis.</p>
        @endif
    </div>
</div>

<!-- BAB II: PERKEMBANGAN MAHASISWA -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-bottom p-3">
        <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
            <span class="badge bg-success rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">II</span>
            Perkembangan Jumlah Mahasiswa, PMB, Retensi, dan Kelulusan
        </h5>
    </div>
    <div class="card-body p-4">
        <div class="row g-3 mb-3">
            <div class="col-md-3 col-6">
                <div class="border rounded-3 p-3 bg-light text-center h-100">
                    <span class="text-muted small d-block mb-1">Target Mahasiswa Baru</span>
                    <h3 class="fw-bold text-secondary mb-0">{{ number_format((int)($mhs['target_pmb'] ?? 0)) }}</h3>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="border rounded-3 p-3 bg-light text-center h-100">
                    <span class="text-muted small d-block mb-1">Realisasi Penerimaan (PMB)</span>
                    <h3 class="fw-bold text-success mb-0">{{ number_format((int)($mhs['realisasi_pmb'] ?? 0)) }}</h3>
                    @php
                        $targetPmb = (int)($mhs['target_pmb'] ?? 0);
                        $realPmb = (int)($mhs['realisasi_pmb'] ?? 0);
                        $pctPmb = $targetPmb > 0 ? round(($realPmb / $targetPmb) * 100, 1) : 0;
                    @endphp
                    <small class="text-success fw-semibold">{{ $pctPmb }}% dari target</small>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="border rounded-3 p-3 bg-light text-center h-100">
                    <span class="text-muted small d-block mb-1">Total Mahasiswa Aktif</span>
                    <h3 class="fw-bold text-primary mb-0">{{ number_format((int)($mhs['total_aktif'] ?? 0)) }}</h3>
                    <small class="text-muted">Seluruh jenjang prodi</small>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="border rounded-3 p-3 bg-light text-center h-100">
                    <span class="text-muted small d-block mb-1">Tingkat Retensi Mahasiswa</span>
                    <h3 class="fw-bold text-dark mb-0">{{ $mhs['tingkat_retensi'] ?? 0 }}%</h3>
                    <small class="text-muted">Keberlanjutan studi</small>
                </div>
            </div>
            <div class="col-md-4 col-6">
                <div class="border rounded-3 p-3 bg-light text-center h-100">
                    <span class="text-muted small d-block mb-1">Jumlah Lulusan / Wisudawan</span>
                    <h4 class="fw-bold text-dark mb-0">{{ number_format((int)($mhs['jumlah_lulusan'] ?? 0)) }} Orang</h4>
                </div>
            </div>
            <div class="col-md-4 col-6">
                <div class="border rounded-3 p-3 bg-light text-center h-100">
                    <span class="text-muted small d-block mb-1">Rata-Rata IPK Kelulusan</span>
                    <h4 class="fw-bold text-success mb-0">{{ $mhs['ipk_rata_rata'] ?? '3.40' }}</h4>
                </div>
            </div>
            <div class="col-md-4 col-12">
                <div class="border rounded-3 p-3 bg-light h-100">
                    <span class="text-muted small d-block mb-1">Catatan Kemahasiswaan</span>
                    <span class="small text-dark">{{ $mhs['catatan'] ?? 'Penyelenggaraan beasiswa dan kegiatan pembinaan karakter berjalan sesuai target.' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- BAB III: PERKEMBANGAN PROGRAM STUDI & AKREDITASI -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-bottom p-3">
        <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
            <span class="badge bg-success rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">III</span>
            Perkembangan Program Studi, Akreditasi & Capaian Prestasi
        </h5>
    </div>
    <div class="card-body p-4">
        <div class="table-responsive mb-4">
            <table class="table table-hover table-bordered align-middle text-sm">
                <thead class="table-light">
                    <tr class="text-center text-uppercase" style="font-size: 0.78rem;">
                        <th style="width: 45px;">No</th>
                        <th class="text-start">Program Studi</th>
                        <th style="width: 80px;">Jenjang</th>
                        <th style="width: 140px;">Peringkat Akreditasi</th>
                        <th style="width: 130px;">Lembaga Akreditasi</th>
                        <th style="width: 120px;">Masa Berlaku</th>
                        <th class="text-start">Status Pengembangan / Mutu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($prodiList as $idx => $p)
                        <tr>
                            <td class="text-center text-muted">{{ $idx + 1 }}</td>
                            <td class="fw-bold text-dark">{{ $p['nama'] ?? '-' }}</td>
                            <td class="text-center"><span class="badge bg-light text-dark border">{{ $p['jenjang'] ?? 'S1' }}</span></td>
                            <td class="text-center">
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fw-bold">
                                    {{ $p['akreditasi'] ?? 'Baik Sekali' }}
                                </span>
                            </td>
                            <td class="text-center small">{{ $p['lembaga'] ?? 'BAN-PT' }}</td>
                            <td class="text-center small">{{ !empty($p['kadaluarsa']) ? date('d/m/Y', strtotime($p['kadaluarsa'])) : '-' }}</td>
                            <td class="small">{{ $p['status'] ?? 'Aktif' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-3 text-muted">Belum ada data program studi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card border rounded-3 p-3 bg-light">
            <h6 class="fw-bold text-dark mb-2"><i class="bi bi-trophy-fill text-warning me-1"></i> Rekapitulasi Capaian Prestasi Akademik & Non-Akademik</h6>
            <div class="row g-3 mb-2">
                <div class="col-md-4 text-center">
                    <div class="p-2 bg-white rounded border">
                        <small class="text-muted d-block">Tingkat Internasional</small>
                        <h4 class="fw-bold text-primary mb-0">{{ $prestasi['internasional'] ?? 0 }} Prestasi</h4>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <div class="p-2 bg-white rounded border">
                        <small class="text-muted d-block">Tingkat Nasional</small>
                        <h4 class="fw-bold text-success mb-0">{{ $prestasi['nasional'] ?? 0 }} Prestasi</h4>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <div class="p-2 bg-white rounded border">
                        <small class="text-muted d-block">Tingkat Wilayah / Lokal</small>
                        <h4 class="fw-bold text-secondary mb-0">{{ $prestasi['wilayah'] ?? 0 }} Prestasi</h4>
                    </div>
                </div>
            </div>
            @if(!empty($prestasi['keterangan']))
                <p class="small text-muted mb-0 mt-2"><strong>Rincian Prestasi:</strong> {{ $prestasi['keterangan'] }}</p>
            @endif
        </div>
    </div>
</div>

<!-- BAB IV: PERKEMBANGAN SUMBER DAYA MANUSIA (SDM) -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-bottom p-3">
        <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
            <span class="badge bg-success rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">IV</span>
            Perkembangan Sumber Daya Manusia (Kualifikasi & Kompetensi)
        </h5>
    </div>
    <div class="card-body p-4">
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="border rounded-3 p-3 bg-light h-100">
                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-mortarboard text-primary me-1"></i> Kualifikasi Akademik Dosen</h6>
                    <ul class="list-group list-group-flush bg-transparent small">
                        <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0">
                            <span>Dosen Berpendidikan S2 (Magister)</span>
                            <span class="badge bg-primary rounded-pill">{{ $sdm['dosen_s2'] ?? 0 }} Orang</span>
                        </li>
                        <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0">
                            <span>Dosen Berpendidikan S3 (Doktor)</span>
                            <span class="badge bg-success rounded-pill">{{ $sdm['dosen_s3'] ?? 0 }} Orang</span>
                        </li>
                        <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0">
                            <span>Dosen Sedang Studi Lanjut S3</span>
                            <span class="badge bg-info text-dark rounded-pill">{{ $sdm['dosen_studi_s3'] ?? 0 }} Orang</span>
                        </li>
                        <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0">
                            <span>Dosen Tersertifikasi Pendidik (Serdos)</span>
                            <span class="badge bg-warning text-dark rounded-pill">{{ $sdm['dosen_serdos'] ?? 0 }} Orang</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="col-md-6">
                <div class="border rounded-3 p-3 bg-light h-100">
                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-person-badge text-success me-1"></i> Jabatan Fungsional Akademik (JFA)</h6>
                    <ul class="list-group list-group-flush bg-transparent small">
                        <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0">
                            <span>Guru Besar (Profesor)</span>
                            <span class="badge bg-success rounded-pill">{{ $sdm['dosen_guru_besar'] ?? 0 }} Orang</span>
                        </li>
                        <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0">
                            <span>Lektor Kepala</span>
                            <span class="badge bg-primary rounded-pill">{{ $sdm['dosen_lektor_kepala'] ?? 0 }} Orang</span>
                        </li>
                        <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0">
                            <span>Lektor</span>
                            <span class="badge bg-info text-dark rounded-pill">{{ $sdm['dosen_lektor'] ?? 0 }} Orang</span>
                        </li>
                        <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0">
                            <span>Asisten Ahli</span>
                            <span class="badge bg-secondary rounded-pill">{{ $sdm['dosen_asisten_ahli'] ?? 0 }} Orang</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="card border rounded-3 p-3 bg-light">
            <div class="row g-2 align-items-center">
                <div class="col-md-4">
                    <small class="text-muted d-block">Tenaga Kependidikan Tetap</small>
                    <h5 class="fw-bold text-dark mb-0">{{ $sdm['tendik_tetap'] ?? 0 }} Orang</h5>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">Tenaga Kependidikan Kontrak/Honorer</small>
                    <h5 class="fw-bold text-dark mb-0">{{ $sdm['tendik_kontrak'] ?? 0 }} Orang</h5>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">Tendik Bersertifikasi Profesi</small>
                    <h5 class="fw-bold text-success mb-0">{{ $sdm['tendik_sertifikasi'] ?? 0 }} Orang</h5>
                </div>
                @if(!empty($sdm['pelatihan']))
                    <div class="col-12 mt-3 pt-2 border-top">
                        <small class="text-muted d-block fw-semibold">Program Peningkatan Kompetensi & Pelatihan:</small>
                        <p class="small text-dark mb-0">{{ $sdm['pelatihan'] }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- BAB V & VI: PENELITIAN, PKM & KERJASAMA -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-bottom p-3">
        <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
            <span class="badge bg-success rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">V</span>
            Capaian Penelitian, Pengabdian kepada Masyarakat & Kerjasama
        </h5>
    </div>
    <div class="card-body p-4">
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-6">
                <div class="border rounded-3 p-3 bg-light text-center h-100">
                    <span class="text-muted small d-block mb-1">Publikasi Scopus / Internasional</span>
                    <h3 class="fw-bold text-primary mb-0">{{ $riset['publikasi_scopus'] ?? 0 }}</h3>
                    <small class="text-muted">Artikel terindeks reputasi</small>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="border rounded-3 p-3 bg-light text-center h-100">
                    <span class="text-muted small d-block mb-1">Publikasi SINTA (Nasional)</span>
                    <h3 class="fw-bold text-success mb-0">{{ $riset['publikasi_sinta'] ?? 0 }}</h3>
                    <small class="text-muted">Jurnal terakreditasi</small>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="border rounded-3 p-3 bg-light text-center h-100">
                    <span class="text-muted small d-block mb-1">Hak Kekayaan Intelektual (HKI)</span>
                    <h3 class="fw-bold text-warning mb-0">{{ $riset['hki_paten'] ?? 0 }}</h3>
                    <small class="text-muted">Paten & Hak Cipta</small>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="border rounded-3 p-3 bg-light text-center h-100">
                    <span class="text-muted small d-block mb-1">Buku Ajar / Monograf</span>
                    <h3 class="fw-bold text-dark mb-0">{{ $riset['buku'] ?? 0 }}</h3>
                    <small class="text-muted">Ber-ISBN resmi</small>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <div class="p-3 border rounded-3 bg-light">
                    <h6 class="fw-bold text-dark mb-2">Pendanaan Riset & Pengabdian Masyarakat</h6>
                    <div class="d-flex justify-content-between mb-2 small">
                        <span>Total Dana Hibah Riset / Penelitian:</span>
                        <strong class="text-success">{{ $riset['dana_penelitian'] ?: 'Rp 0' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between small">
                        <span>Total Dana Pengabdian Masyarakat (PkM):</span>
                        <strong class="text-primary">{{ $riset['dana_pkm'] ?: 'Rp 0' }}</strong>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="p-3 border rounded-3 bg-light">
                    <h6 class="fw-bold text-dark mb-2">Jejaring Kerjasama Aktif (MoU / MoA)</h6>
                    <div class="d-flex justify-content-between mb-1 small">
                        <span>Pemerintah & BUMN:</span>
                        <strong>{{ $kerjasama['mitra_pemerintah'] ?? 0 }} Mitra</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1 small">
                        <span>Dunia Usaha & Dunia Industri (DUDI):</span>
                        <strong>{{ $kerjasama['mitra_dudi'] ?? 0 }} Mitra</strong>
                    </div>
                    <div class="d-flex justify-content-between small">
                        <span>Perguruan Tinggi Nasional & Internasional:</span>
                        <strong>{{ ((int)($kerjasama['mitra_pt'] ?? 0)) + ((int)($kerjasama['mitra_internasional'] ?? 0)) }} Mitra</strong>
                    </div>
                </div>
            </div>

            @if(!empty($kerjasama['realisasi']))
                <div class="col-12">
                    <div class="p-3 border rounded-3 bg-light">
                        <small class="text-muted d-block fw-semibold">Realisasi & Implementasi Kerjasama:</small>
                        <p class="small text-dark mb-0">{{ $kerjasama['realisasi'] }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- BAB VII: SARANA & PRASARANA -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-bottom p-3">
        <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
            <span class="badge bg-success rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">VI</span>
            Perkembangan Sarana dan Prasarana Pendukung Pendidikan
        </h5>
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="border rounded-3 p-3 bg-light h-100">
                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-building text-primary me-1"></i> Sarana Fisik, Gedung & Laboratorium</h6>
                    <p class="small text-muted mb-0" style="line-height: 1.7;">
                        {{ $sarpras['sarana_fisik'] ?: 'Pemeliharaan fasilitas ruang kuliah, penambahan alat laboratorium teknik dan kesehatan, serta renovasi ruang layanan mahasiswa berjalan sesuai jadwal.' }}
                    </p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="border rounded-3 p-3 bg-light h-100">
                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-hdd-network text-success me-1"></i> Infrastruktur IT & Digital Kampus</h6>
                    <p class="small text-muted mb-0" style="line-height: 1.7;">
                        {{ $sarpras['it_digital'] ?: 'Peningkatan kapasitas bandwidth internet kampus, integrasi SIM Kinerja, pengembangan portal e-learning, dan sistem presensi digital.' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- BAB VIII & IX: KENDALA, SOLUSI & RENCANA TINDAK LANJUT -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-bottom p-3">
        <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
            <span class="badge bg-success rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">VII</span>
            Kendala, Permasalahan & Rencana Tindak Lanjut Pengembangan
        </h5>
    </div>
    <div class="card-body p-4">
        <div class="mb-4">
            <h6 class="fw-bold text-danger mb-2"><i class="bi bi-exclamation-octagon-fill me-1"></i> Kendala dalam Pelaksanaan Program Pengembangan & Solusinya</h6>
            <div class="p-3 border rounded-3 bg-light">
                <p class="text-dark small mb-0" style="line-height: 1.7; white-space: pre-line;">
                    {{ $laporan->kendala_solusi ?: 'Tantangan percepatan akreditasi internasional dan peningkatan persentase dosen bergelar doktor terus dimitigasi dengan skema beasiswa yayasan dan pendampingan publikasi ilmiah berkala.' }}
                </p>
            </div>
        </div>

        <div>
            <h6 class="fw-bold text-primary mb-2"><i class="bi bi-compass-fill me-1"></i> Rencana Tindak Lanjut Periode Mendatang</h6>
            <div class="p-3 border rounded-3 bg-light">
                <p class="text-dark small mb-0" style="line-height: 1.7; white-space: pre-line;">
                    {{ $laporan->tindak_lanjut ?: 'Memperkuat hilirisasi riset dosen dengan dunia industri di Batam, percepatan pembukaan program studi magister baru, dan peningkatan daya saing lulusan.' }}
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Lembar Tanda Tangan Ringkas -->
<div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
    <div class="row text-center text-dark">
        <div class="col-md-6 mb-3">
            <span class="small text-muted d-block mb-1">Mengetahui,</span>
            <strong class="d-block mb-4">Ketua Yayasan Pendidikan Ibnu Sina Batam</strong>
            <div class="mt-5">
                <strong class="text-decoration-underline">( H. Andi Ibrahim, B.A. )</strong>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <span class="small text-muted d-block mb-1">Batam, {{ date('d F Y') }}</span>
            <strong class="d-block mb-4">Rektor Universitas Ibnu Sina</strong>
            <div class="mt-5">
                <strong class="text-decoration-underline">( Assoc. Prof. Dr. Ir. Larisang, M.T., IPU )</strong>
                <div class="small text-muted">NIDN. 1010046901</div>
            </div>
        </div>
    </div>
</div>
@endsection
