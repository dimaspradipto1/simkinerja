@extends('layouts.dashboard.template')

@section('content')
<style>
    .nav-pills-uis .nav-link {
        color: #475569;
        font-weight: 600;
        border-radius: 8px;
        padding: 8px 16px;
        transition: all 0.2s ease;
        border: 1px solid transparent;
    }
    .nav-pills-uis .nav-link:hover {
        background-color: #f1f5f9;
        color: #15432D;
    }
    .nav-pills-uis .nav-link.active {
        background-color: #15432D !important;
        color: #ffffff !important;
        box-shadow: 0 2px 5px rgba(21, 67, 45, 0.25);
    }
</style>

<div class="pagetitle mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h1 class="mb-1 text-dark fw-bold fs-4">{{ $isEdit ? 'Edit Laporan Pencapaian Pengembangan' : 'Buat Laporan Pencapaian Pengembangan' }}</h1>
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('laporan-pengembangan.index') }}">Laporan Pengembangan</a></li>
                    <li class="breadcrumb-item active text-success fw-semibold">{{ $isEdit ? 'Edit Laporan' : 'Tambah Baru' }}</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('laporan-pengembangan.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1.5 px-3 py-2 rounded-3 text-sm">
            <i class="bi bi-arrow-left"></i>
            <span>Kembali ke Daftar</span>
        </a>
    </div>
</div>

@php
    $mhs = old('data_mahasiswa', $laporan->data_mahasiswa ?? []);
    $sdm = old('data_sdm', $laporan->data_sdm ?? []);
    $riset = old('data_penelitian_pkm', $laporan->data_penelitian_pkm ?? []);
    $kerjasama = old('data_kerjasama', $laporan->data_kerjasama ?? []);
    $sarpras = old('data_sarpras', $laporan->data_sarpras ?? []);
    $prestasi = old('data_prestasi', $laporan->data_prestasi ?? []);
    $prodiList = old('data_prodi', $laporan->data_prodi ?? $defaultProdi);
@endphp

<form action="{{ $isEdit ? route('laporan-pengembangan.update', $laporan->id) : route('laporan-pengembangan.store') }}" method="POST">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <!-- Card Info Dasar -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h5 class="card-title fw-bold text-dark mb-3 p-0"><i class="bi bi-info-circle-fill text-success me-1.5"></i> Identitas & Periode Dokumen</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-dark">Judul Laporan Resmi <span class="text-danger">*</span></label>
                    <input type="text" name="judul" class="form-control" value="{{ old('judul', $laporan->judul ?: 'Laporan Pencapaian Pengembangan Universitas Ibnu Sina') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-dark">Tahun Akademik</label>
                    <input type="text" name="tahun_akademik" class="form-control" placeholder="Contoh: 2025/2026" value="{{ old('tahun_akademik', $laporan->tahun_akademik) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-dark">Tautkan Periode Akademik</label>
                    <select name="periode_akademik_id" class="form-select">
                        <option value="">-- Pilih Periode (Opsional) --</option>
                        @foreach($periodeAkademiks as $p)
                            <option value="{{ $p->id }}" {{ old('periode_akademik_id', $laporan->periode_akademik_id) == $p->id ? 'selected' : '' }}>
                                {{ $p->nama_periode }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs Pengisian Metrik Pengembangan -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-white">
        <div class="card-header bg-white border-bottom p-2.5">
            <ul class="nav nav-pills nav-pills-uis card-header-pills gap-1.5" id="formPengembanganTab" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active fw-semibold" id="tab-program-tab" data-bs-toggle="tab" data-bs-target="#tab-program" type="button" role="tab">
                        <i class="bi bi-bullseye me-1"></i> 1. Program Kerja & Mahasiswa
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-semibold" id="tab-prodi-tab" data-bs-toggle="tab" data-bs-target="#tab-prodi" type="button" role="tab">
                        <i class="bi bi-mortarboard me-1"></i> 2. Program Studi & Mutu
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-semibold" id="tab-sdm-tab" data-bs-toggle="tab" data-bs-target="#tab-sdm" type="button" role="tab">
                        <i class="bi bi-people me-1"></i> 3. SDM (Dosen & Tendik)
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-semibold" id="tab-riset-tab" data-bs-toggle="tab" data-bs-target="#tab-riset" type="button" role="tab">
                        <i class="bi bi-journal-richtext me-1"></i> 4. Penelitian, PkM & Kerjasama
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-semibold" id="tab-sarpras-tab" data-bs-toggle="tab" data-bs-target="#tab-sarpras" type="button" role="tab">
                        <i class="bi bi-building-check me-1"></i> 5. Sarpras, Kendala & Solusi
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body p-4">
            <div class="tab-content" id="formPengembanganTabContent">

                <!-- TAB 1: PROGRAM KERJA & MAHASISWA -->
                <div class="tab-pane fade show active" id="tab-program" role="tabpanel">
                    <h6 class="fw-bold text-success mb-3"><i class="bi bi-check2-circle me-1"></i> Capaian Program Kerja & Target Pengembangan Universitas</h6>
                    <div class="mb-4">
                        <label class="form-label text-muted small fw-semibold">Uraian Narasi Capaian Program Kerja Strategis & Target Rektorat</label>
                        <textarea name="capaian_program_kerja" class="form-control" rows="5" placeholder="Tuliskan ringkasan capaian program kerja utama universitas yang telah ditetapkan pada periode ini...">{{ old('capaian_program_kerja', $laporan->capaian_program_kerja) }}</textarea>
                    </div>

                    <h6 class="fw-bold text-success mb-3"><i class="bi bi-person-lines-fill me-1"></i> Perkembangan Mahasiswa (PMB, Retensi, Kelulusan)</h6>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Target Mahasiswa Baru (PMB)</label>
                            <input type="number" name="data_mahasiswa[target_pmb]" class="form-control" value="{{ $mhs['target_pmb'] ?? '' }}" placeholder="Contoh: 1200">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Realisasi Penerimaan Mhs Baru</label>
                            <input type="number" name="data_mahasiswa[realisasi_pmb]" class="form-control" value="{{ $mhs['realisasi_pmb'] ?? '' }}" placeholder="Contoh: 1150">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Total Mahasiswa Aktif</label>
                            <input type="number" name="data_mahasiswa[total_aktif]" class="form-control" value="{{ $mhs['total_aktif'] ?? '' }}" placeholder="Contoh: 4500">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Tingkat Retensi Mahasiswa (%)</label>
                            <input type="text" name="data_mahasiswa[tingkat_retensi]" class="form-control" value="{{ $mhs['tingkat_retensi'] ?? '' }}" placeholder="Contoh: 92.5">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Jumlah Lulusan / Wisudawan</label>
                            <input type="number" name="data_mahasiswa[jumlah_lulusan]" class="form-control" value="{{ $mhs['jumlah_lulusan'] ?? '' }}" placeholder="Contoh: 650">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Rata-rata IPK Lulusan</label>
                            <input type="text" name="data_mahasiswa[ipk_rata_rata]" class="form-control" value="{{ $mhs['ipk_rata_rata'] ?? '' }}" placeholder="Contoh: 3.45">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Catatan Tambahan Bidang Kemahasiswaan</label>
                            <input type="text" name="data_mahasiswa[catatan]" class="form-control" value="{{ $mhs['catatan'] ?? '' }}" placeholder="Contoh: Penerima beasiswa KIPK meningkat 15%">
                        </div>
                    </div>
                </div>

                <!-- TAB 2: PROGRAM STUDI & AKREDITASI -->
                <div class="tab-pane fade" id="tab-prodi" role="tabpanel">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h6 class="fw-bold text-success mb-0"><i class="bi bi-award me-1"></i> Perkembangan Program Studi & Status Akreditasi</h6>
                            <small class="text-muted">Kelola status akreditasi dan pengembangan program studi Universitas Ibnu Sina</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" id="btn-add-prodi">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Program Studi
                        </button>
                    </div>

                    <div id="prodi-container" class="d-flex flex-column gap-3 mb-4">
                        @foreach($prodiList as $pIdx => $prodi)
                            <div class="prodi-row border rounded-3 p-3 bg-light position-relative">
                                <div class="row g-2 align-items-center">
                                    <div class="col-md-3">
                                        <label class="form-label small fw-semibold text-muted mb-0">Nama Program Studi</label>
                                        <input type="text" name="data_prodi[{{ $pIdx }}][nama]" class="form-control form-control-sm" value="{{ $prodi['nama'] ?? '' }}" required>
                                    </div>
                                    <div class="col-md-1">
                                        <label class="form-label small fw-semibold text-muted mb-0">Jenjang</label>
                                        <input type="text" name="data_prodi[{{ $pIdx }}][jenjang]" class="form-control form-control-sm" value="{{ $prodi['jenjang'] ?? 'S1' }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small fw-semibold text-muted mb-0">Status Akreditasi</label>
                                        <input type="text" name="data_prodi[{{ $pIdx }}][akreditasi]" class="form-control form-control-sm" value="{{ $prodi['akreditasi'] ?? 'Baik Sekali' }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small fw-semibold text-muted mb-0">Lembaga Akreditasi</label>
                                        <input type="text" name="data_prodi[{{ $pIdx }}][lembaga]" class="form-control form-control-sm" value="{{ $prodi['lembaga'] ?? 'BAN-PT' }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small fw-semibold text-muted mb-0">Masa Berlaku S.d.</label>
                                        <input type="date" name="data_prodi[{{ $pIdx }}][kadaluarsa]" class="form-control form-control-sm" value="{{ $prodi['kadaluarsa'] ?? '' }}">
                                    </div>
                                    <div class="col-md-2 d-flex align-items-center gap-1 pt-3">
                                        <input type="text" name="data_prodi[{{ $pIdx }}][status]" class="form-control form-control-sm" value="{{ $prodi['status'] ?? 'Aktif' }}" placeholder="Status pengembangan">
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-prodi px-2 py-1 rounded" title="Hapus Baris">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <h6 class="fw-bold text-success mb-2"><i class="bi bi-trophy me-1"></i> Capaian Prestasi Akademik & Non-Akademik</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Prestasi Tingkat Internasional</label>
                            <input type="number" name="data_prestasi[internasional]" class="form-control" value="{{ $prestasi['internasional'] ?? 0 }}" placeholder="Jumlah kejuaraan/prestasi">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Prestasi Tingkat Nasional</label>
                            <input type="number" name="data_prestasi[nasional]" class="form-control" value="{{ $prestasi['nasional'] ?? 0 }}" placeholder="Jumlah kejuaraan/prestasi">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Prestasi Tingkat Wilayah / Lokal</label>
                            <input type="number" name="data_prestasi[wilayah]" class="form-control" value="{{ $prestasi['wilayah'] ?? 0 }}" placeholder="Jumlah kejuaraan/prestasi">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Deskripsi Prestasi Menonjol</label>
                            <textarea name="data_prestasi[keterangan]" class="form-control" rows="3" placeholder="Sebutkan kejuaraan atau kompetisi penting yang diraih mahasiswa maupun dosen...">{{ $prestasi['keterangan'] ?? '' }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- TAB 3: PERKEMBANGAN SDM -->
                <div class="tab-pane fade" id="tab-sdm" role="tabpanel">
                    <h6 class="fw-bold text-success mb-3"><i class="bi bi-people-fill me-1"></i> Perkembangan SDM (Dosen & Tenaga Kependidikan)</h6>
                    
                    <div class="card border rounded-3 p-3 bg-light mb-3">
                        <span class="fw-bold text-dark d-block mb-2">A. Kualifikasi Pendidikan & Jabatan Fungsional Dosen</span>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Dosen Berpendidikan S2</label>
                                <input type="number" name="data_sdm[dosen_s2]" class="form-control" value="{{ $sdm['dosen_s2'] ?? '' }}" placeholder="Jumlah orang">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Dosen Berpendidikan S3 (Doktor)</label>
                                <input type="number" name="data_sdm[dosen_s3]" class="form-control" value="{{ $sdm['dosen_s3'] ?? '' }}" placeholder="Jumlah orang">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Dosen Sedang Studi Lanjut S3</label>
                                <input type="number" name="data_sdm[dosen_studi_s3]" class="form-control" value="{{ $sdm['dosen_studi_s3'] ?? '' }}" placeholder="Jumlah orang">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Dosen Tersertifikasi Pendidik (Serdos)</label>
                                <input type="number" name="data_sdm[dosen_serdos]" class="form-control" value="{{ $sdm['dosen_serdos'] ?? '' }}" placeholder="Jumlah orang">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Jabatan Asisten Ahli</label>
                                <input type="number" name="data_sdm[dosen_asisten_ahli]" class="form-control" value="{{ $sdm['dosen_asisten_ahli'] ?? '' }}" placeholder="Orang">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Jabatan Lektor</label>
                                <input type="number" name="data_sdm[dosen_lektor]" class="form-control" value="{{ $sdm['dosen_lektor'] ?? '' }}" placeholder="Orang">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Jabatan Lektor Kepala</label>
                                <input type="number" name="data_sdm[dosen_lektor_kepala]" class="form-control" value="{{ $sdm['dosen_lektor_kepala'] ?? '' }}" placeholder="Orang">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Jabatan Guru Besar / Profesor</label>
                                <input type="number" name="data_sdm[dosen_guru_besar]" class="form-control" value="{{ $sdm['dosen_guru_besar'] ?? '' }}" placeholder="Orang">
                            </div>
                        </div>
                    </div>

                    <div class="card border rounded-3 p-3 bg-light">
                        <span class="fw-bold text-dark d-block mb-2">B. Tenaga Kependidikan & Peningkatan Kompetensi</span>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Tendik Tetap</label>
                                <input type="number" name="data_sdm[tendik_tetap]" class="form-control" value="{{ $sdm['tendik_tetap'] ?? '' }}" placeholder="Jumlah orang">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Tendik Kontrak / Honorer</label>
                                <input type="number" name="data_sdm[tendik_kontrak]" class="form-control" value="{{ $sdm['tendik_kontrak'] ?? '' }}" placeholder="Jumlah orang">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Tendik Bersertifikasi Profesi</label>
                                <input type="number" name="data_sdm[tendik_sertifikasi]" class="form-control" value="{{ $sdm['tendik_sertifikasi'] ?? '' }}" placeholder="Jumlah orang">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Program Pelatihan / Peningkatan Kompetensi yang Dilaksanakan</label>
                                <textarea name="data_sdm[pelatihan]" class="form-control" rows="3" placeholder="Sebutkan workshop/diklat/bimtek yang diikuti dosen dan tendik pada periode ini...">{{ $sdm['pelatihan'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 4: RISET, PKM & KERJASAMA -->
                <div class="tab-pane fade" id="tab-riset" role="tabpanel">
                    <h6 class="fw-bold text-success mb-3"><i class="bi bi-journal-text me-1"></i> Capaian Penelitian & Pengabdian kepada Masyarakat (LPPM)</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Publikasi Internasional / Scopus</label>
                            <input type="number" name="data_penelitian_pkm[publikasi_scopus]" class="form-control" value="{{ $riset['publikasi_scopus'] ?? '' }}" placeholder="Judul artikel">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Publikasi Nasional Terakreditasi (SINTA)</label>
                            <input type="number" name="data_penelitian_pkm[publikasi_sinta]" class="form-control" value="{{ $riset['publikasi_sinta'] ?? '' }}" placeholder="Judul artikel">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Hak Kekayaan Intelektual (HKI / Paten)</label>
                            <input type="number" name="data_penelitian_pkm[hki_paten]" class="form-control" value="{{ $riset['hki_paten'] ?? '' }}" placeholder="Sertifikat HKI">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Buku Ajar / Monograf Ber-ISBN</label>
                            <input type="number" name="data_penelitian_pkm[buku]" class="form-control" value="{{ $riset['buku'] ?? '' }}" placeholder="Judul buku">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Total Dana Hibah Penelitian (Internal + Eksternal)</label>
                            <input type="text" name="data_penelitian_pkm[dana_penelitian]" class="form-control" value="{{ $riset['dana_penelitian'] ?? '' }}" placeholder="Contoh: Rp 450.000.000">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Total Dana Pengabdian Masyarakat (PkM)</label>
                            <input type="text" name="data_penelitian_pkm[dana_pkm]" class="form-control" value="{{ $riset['dana_pkm'] ?? '' }}" placeholder="Contoh: Rp 220.000.000">
                        </div>
                    </div>

                    <h6 class="fw-bold text-success mb-3"><i class="bi bi-handshake me-1"></i> Perkembangan Kerjasama (MoU & MoA)</h6>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Mitra Instansi Pemerintah</label>
                            <input type="number" name="data_kerjasama[mitra_pemerintah]" class="form-control" value="{{ $kerjasama['mitra_pemerintah'] ?? '' }}" placeholder="Jumlah MoU">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Mitra Industri & DUDI</label>
                            <input type="number" name="data_kerjasama[mitra_dudi]" class="form-control" value="{{ $kerjasama['mitra_dudi'] ?? '' }}" placeholder="Jumlah MoU">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Mitra Perguruan Tinggi Nasional</label>
                            <input type="number" name="data_kerjasama[mitra_pt]" class="form-control" value="{{ $kerjasama['mitra_pt'] ?? '' }}" placeholder="Jumlah MoU">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Mitra Perguruan Tinggi Internasional</label>
                            <input type="number" name="data_kerjasama[mitra_internasional]" class="form-control" value="{{ $kerjasama['mitra_internasional'] ?? '' }}" placeholder="Jumlah MoU">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Bentuk Realisasi Kerjasama</label>
                            <textarea name="data_kerjasama[realisasi]" class="form-control" rows="3" placeholder="Sebutkan kegiatan nyata implementasi kerjasama (magang mahasiswa, riset kolaborasi, kuliah pakar, dll)...">{{ $kerjasama['realisasi'] ?? '' }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- TAB 5: SARPRAS, KENDALA & SOLUSI -->
                <div class="tab-pane fade" id="tab-sarpras" role="tabpanel">
                    <h6 class="fw-bold text-success mb-3"><i class="bi bi-buildings me-1"></i> Perkembangan Sarana & Prasarana Pendukung Pendidikan</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Pengadaan & Pengembangan Sarana Fisik / Fasilitas</label>
                            <textarea name="data_sarpras[sarana_fisik]" class="form-control" rows="3" placeholder="Gedung baru, laboratorium, perpustakaan, ruang seminar, studio podcast, fasilitas olahraga, dll...">{{ $sarpras['sarana_fisik'] ?? '' }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Pengembangan Infrastruktur IT & Digital Kampus</label>
                            <textarea name="data_sarpras[it_digital]" class="form-control" rows="2" placeholder="Bandwidth internet, server kampus, SIM Kinerja, Smart Classroom, perangkat lab komputer, dll...">{{ $sarpras['it_digital'] ?? '' }}</textarea>
                        </div>
                    </div>

                    <h6 class="fw-bold text-danger mb-3"><i class="bi bi-exclamation-triangle-fill me-1"></i> Kendala dan Permasalahan dalam Pelaksanaan Pengembangan Beserta Langkah Penyelesaiannya</h6>
                    <div class="mb-4">
                        <textarea name="kendala_solusi" class="form-control" rows="4" placeholder="Uraikan kendala-kendala strategis yang dihadapi serta langkah mitigasi dan solusinya...">{{ old('kendala_solusi', $laporan->kendala_solusi) }}</textarea>
                    </div>

                    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-signpost-2-fill me-1"></i> Rencana Tindak Lanjut Pengembangan Universitas</h6>
                    <div>
                        <textarea name="tindak_lanjut" class="form-control" rows="3" placeholder="Rencana strategis perbaikan, target akselerasi, dan prioritas pengembangan periode mendatang...">{{ old('tindak_lanjut', $laporan->tindak_lanjut) }}</textarea>
                    </div>
                </div>

            </div>
        </div>

        <div class="card-footer bg-light p-3 d-flex align-items-center justify-content-between">
            <a href="{{ route('laporan-pengembangan.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Batal / Kembali
            </a>
            <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold shadow-sm">
                <i class="bi bi-save me-1.5"></i> Simpan Dokumen Laporan
            </button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        let prodiIdx = $('#prodi-container .prodi-row').length;

        $('#btn-add-prodi').on('click', function() {
            const html = `
                <div class="prodi-row border rounded-3 p-3 bg-light position-relative">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-muted mb-0">Nama Program Studi</label>
                            <input type="text" name="data_prodi[${prodiIdx}][nama]" class="form-control form-control-sm" placeholder="Contoh: Farmasi" required>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label small fw-semibold text-muted mb-0">Jenjang</label>
                            <input type="text" name="data_prodi[${prodiIdx}][jenjang]" class="form-control form-control-sm" value="S1">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold text-muted mb-0">Status Akreditasi</label>
                            <input type="text" name="data_prodi[${prodiIdx}][akreditasi]" class="form-control form-control-sm" value="Baik">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold text-muted mb-0">Lembaga Akreditasi</label>
                            <input type="text" name="data_prodi[${prodiIdx}][lembaga]" class="form-control form-control-sm" value="BAN-PT">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold text-muted mb-0">Masa Berlaku S.d.</label>
                            <input type="date" name="data_prodi[${prodiIdx}][kadaluarsa]" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2 d-flex align-items-center gap-1 pt-3">
                            <input type="text" name="data_prodi[${prodiIdx}][status]" class="form-control form-control-sm" value="Aktif" placeholder="Status pengembangan">
                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-prodi px-2 py-1 rounded" title="Hapus Baris">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            $('#prodi-container').append(html);
            prodiIdx++;
        });

        $(document).on('click', '.btn-remove-prodi', function() {
            if ($('#prodi-container .prodi-row').length > 1) {
                $(this).closest('.prodi-row').remove();
            } else {
                alert('Minimal harus ada 1 program studi.');
            }
        });
    });
</script>
@endpush
