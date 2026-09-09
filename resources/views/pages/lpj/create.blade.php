@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
    <h1>Tambah LPJ</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item">Rekapitulasi & Laporan</li>
            <li class="breadcrumb-item"><a href="{{ route('lpj.index') }}">LPJ</a></li>
            <li class="breadcrumb-item active">Tambah</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-primary bg-primary-subtle" style="width: 36px; height: 36px;">
                            <i class="bi bi-file-earmark-plus-fill fs-5"></i>
                        </div>
                        <div>
                            <h5 class="card-title p-0 m-0 fw-bold text-dark fs-6">Form Tambah Laporan Pertanggungjawaban (LPJ)</h5>
                            <small class="text-muted">Isi formulir berikut untuk mengarsipkan berkas dan tautan LPJ kepanitiaan</small>
                        </div>
                    </div>

                    <form action="{{ route('lpj.store') }}" method="POST" enctype="multipart/form-data" class="row g-3" id="lpjForm">
                        @csrf
                        <input type="hidden" name="kepanitiaan_id" id="kepanitiaan_id" value="{{ old('kepanitiaan_id', $prefillKepanitiaanId ?? '') }}">

                        <!-- Nama / Judul LPJ -->
                        <div class="col-md-7">
                            <label for="nama_lpj" class="form-label fw-semibold">Nama / Judul LPJ <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('nama_lpj') is-invalid @enderror" 
                                   id="nama_lpj" 
                                   name="nama_lpj" 
                                   value="{{ old('nama_lpj', $prefillNamaLpj ?? '') }}" 
                                   placeholder="Contoh: LPJ Kegiatan Workshop Digitalisasi Kampus 2026" 
                                   required>
                            @error('nama_lpj')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Nama Kepanitiaan -->
                        <div class="col-md-5">
                            <label for="kepanitiaan_selector" class="form-label fw-semibold">Nama Kepanitiaan <span class="text-danger">*</span></label>
                            <select class="form-select @error('nama_kepanitiaan') is-invalid @enderror" id="kepanitiaan_selector">
                                <option value="" {{ (!old('nama_kepanitiaan') && empty($prefillKepanitiaan)) ? 'selected' : '' }}>-- Pilih Nama Kepanitiaan --</option>
                                @php
                                    $currentVal = old('nama_kepanitiaan', $prefillKepanitiaan ?? '');
                                    $isMatched = false;
                                @endphp
                                @foreach($kepanitiaans as $kp)
                                    @php
                                        $selected = false;
                                        if (($currentVal == $kp->uraian_tugas) || (!empty($prefillKepanitiaanId) && $prefillKepanitiaanId == $kp->id)) {
                                            $selected = true;
                                            $isMatched = true;
                                        }
                                    @endphp
                                    <option value="{{ $kp->uraian_tugas }}" 
                                            data-id="{{ $kp->id }}" 
                                            data-tanggal="{{ $kp->tanggal_selesai ?: $kp->tanggal_mulai }}"
                                            {{ $selected ? 'selected' : '' }}>
                                        {{ $kp->uraian_tugas }}
                                    </option>
                                @endforeach
                                <option value="__CUSTOM__" {{ ($currentVal && !$isMatched) ? 'selected' : '' }}>-- Ketik Kepanitiaan Lainnya --</option>
                            </select>

                            <input type="text" 
                                   class="form-control mt-2 @error('nama_kepanitiaan') is-invalid @enderror" 
                                   id="nama_kepanitiaan" 
                                   name="nama_kepanitiaan" 
                                   value="{{ old('nama_kepanitiaan', $prefillKepanitiaan ?? '') }}" 
                                   placeholder="Ketik nama kepanitiaan..."
                                   style="{{ ($currentVal && !$isMatched) ? '' : 'display: none;' }}"
                                   required>

                            @error('nama_kepanitiaan')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Tanggal Pelaksanaan -->
                        <div class="col-md-4">
                            <label for="tanggal_kegiatan" class="form-label fw-semibold">Tanggal Pelaksanaan</label>
                            <input type="date" 
                                   class="form-control @error('tanggal_kegiatan') is-invalid @enderror" 
                                   id="tanggal_kegiatan" 
                                   name="tanggal_kegiatan" 
                                   value="{{ old('tanggal_kegiatan', $prefillTanggal ?? '') }}">
                            @error('tanggal_kegiatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Upload Berkas File LPJ (PDF / Word, Bebas Ukuran) -->
                        <div class="col-md-4">
                            <label for="file_lpj" class="form-label fw-semibold">Unggah Berkas File (PDF / Word)</label>
                            <input type="file" 
                                   class="form-control @error('file_lpj') is-invalid @enderror" 
                                   id="file_lpj" 
                                   name="file_lpj" 
                                   accept=".pdf,.doc,.docx">
                            <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">Mendukung dokumen PDF atau Word (.pdf, .doc, .docx) tanpa batasan ukuran file</small>
                            @error('file_lpj')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Link Google Drive / Cloud LPJ -->
                        <div class="col-md-4">
                            <label for="link_lpj" class="form-label fw-semibold">Tautan / Link LPJ (Opsional)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
                                <input type="url" 
                                       class="form-control @error('link_lpj') is-invalid @enderror" 
                                       id="link_lpj" 
                                       name="link_lpj" 
                                       value="{{ old('link_lpj') }}" 
                                       placeholder="https://drive.google.com/...">
                            </div>
                            <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">Link Google Drive, OneDrive, atau Dokumen Cloud</small>
                            @error('link_lpj')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Keterangan / Ringkasan LPJ -->
                        <div class="col-md-12">
                            <label for="keterangan" class="form-label fw-semibold">Keterangan / Ringkasan LPJ</label>
                            <textarea class="form-control @error('keterangan') is-invalid @enderror" 
                                      id="keterangan" 
                                      name="keterangan" 
                                      rows="4" 
                                      placeholder="Tambahkan ringkasan atau rincian penting terkait laporan pertanggungjawaban ini...">{{ old('keterangan') }}</textarea>
                            @error('keterangan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 mt-4 pt-3 border-top d-flex gap-2">
                            <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1.5 px-4">
                                <i class="bi bi-save"></i> Simpan LPJ
                            </button>
                            <a href="{{ route('lpj.index') }}" class="btn btn-secondary d-inline-flex align-items-center gap-1.5 px-3">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selector = document.getElementById('kepanitiaan_selector');
    const inputNama = document.getElementById('nama_kepanitiaan');
    const inputId = document.getElementById('kepanitiaan_id');
    const inputTanggal = document.getElementById('tanggal_kegiatan');
    const inputJudul = document.getElementById('nama_lpj');

    selector.addEventListener('change', function() {
        const val = this.value;
        const selectedOpt = this.options[this.selectedIndex];

        if (val === '__CUSTOM__') {
            inputNama.style.display = 'block';
            inputNama.value = '';
            inputNama.focus();
            inputId.value = '';
        } else if (val) {
            inputNama.style.display = 'none';
            inputNama.value = val;
            inputId.value = selectedOpt.getAttribute('data-id') || '';

            // Jika tanggal kegiatan belum diisi, otomatis isi dari kepanitiaan
            const tgl = selectedOpt.getAttribute('data-tanggal');
            if (tgl && !inputTanggal.value) {
                inputTanggal.value = tgl;
            }

            // Jika judul LPJ masih kosong, buat otomatis default
            if (!inputJudul.value) {
                inputJudul.value = 'LPJ ' + val;
            }
        } else {
            inputNama.style.display = 'none';
            inputNama.value = '';
            inputId.value = '';
        }
    });

    // Inisialisasi awal saat halaman termuat
    if (selector.value && selector.value !== '__CUSTOM__') {
        inputNama.value = selector.value;
    }
});
</script>
@endpush
