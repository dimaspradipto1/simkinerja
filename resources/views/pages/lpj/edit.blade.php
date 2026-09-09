@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
    <h1>Edit LPJ</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item">Rekapitulasi & Laporan</li>
            <li class="breadcrumb-item"><a href="{{ route('lpj.index') }}">LPJ</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-warning bg-warning-subtle" style="width: 36px; height: 36px;">
                            <i class="bi bi-pencil-square fs-5"></i>
                        </div>
                        <div>
                            <h5 class="card-title p-0 m-0 fw-bold text-dark fs-6">Form Edit Laporan Pertanggungjawaban (LPJ)</h5>
                            <small class="text-muted">Perbarui data atau berkas laporan pertanggungjawaban</small>
                        </div>
                    </div>

                    <form action="{{ route('lpj.update', $lpj->id) }}" method="POST" enctype="multipart/form-data" class="row g-3" id="lpjEditForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="kepanitiaan_id" id="kepanitiaan_id" value="{{ old('kepanitiaan_id', $lpj->kepanitiaan_id) }}">

                        <!-- Nama / Judul LPJ -->
                        <div class="col-md-7">
                            <label for="nama_lpj" class="form-label fw-semibold">Nama / Judul LPJ <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('nama_lpj') is-invalid @enderror" 
                                   id="nama_lpj" 
                                   name="nama_lpj" 
                                   value="{{ old('nama_lpj', $lpj->nama_lpj) }}" 
                                   required>
                            @error('nama_lpj')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Nama Kepanitiaan -->
                        <div class="col-md-5">
                            <label for="kepanitiaan_selector" class="form-label fw-semibold">Nama Kepanitiaan <span class="text-danger">*</span></label>
                            <select class="form-select @error('nama_kepanitiaan') is-invalid @enderror" id="kepanitiaan_selector">
                                <option value="" disabled>-- Pilih Nama Kepanitiaan --</option>
                                @php
                                    $currentVal = old('nama_kepanitiaan', $lpj->nama_kepanitiaan);
                                    $isMatched = false;
                                @endphp
                                @foreach($kepanitiaans as $kp)
                                    @php
                                        $selected = false;
                                        if ($currentVal == $kp->uraian_tugas || old('kepanitiaan_id', $lpj->kepanitiaan_id) == $kp->id) {
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
                                   value="{{ old('nama_kepanitiaan', $lpj->nama_kepanitiaan) }}" 
                                   placeholder="Ketik nama kepanitiaan..."
                                   style="{{ ($currentVal && !$isMatched) ? '' : 'display: none;' }}"
                                   required>

                            @error('nama_kepanitiaan')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Tanggal Kegiatan -->
                        <div class="col-md-4">
                            <label for="tanggal_kegiatan" class="form-label fw-semibold">Tanggal Pelaksanaan</label>
                            <input type="date" 
                                   class="form-control @error('tanggal_kegiatan') is-invalid @enderror" 
                                   id="tanggal_kegiatan" 
                                   name="tanggal_kegiatan" 
                                   value="{{ old('tanggal_kegiatan', $lpj->tanggal_kegiatan ? $lpj->tanggal_kegiatan->format('Y-m-d') : '') }}">
                            @error('tanggal_kegiatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Upload Berkas File LPJ (PDF / Word) -->
                        <div class="col-md-4">
                            <label for="file_lpj" class="form-label fw-semibold">Ganti Berkas File (PDF / Word)</label>
                            <input type="file" 
                                   class="form-control @error('file_lpj') is-invalid @enderror" 
                                   id="file_lpj" 
                                   name="file_lpj" 
                                   accept=".pdf,.doc,.docx">
                            <div class="mt-1">
                                @if($lpj->file_lpj)
                                    <div class="d-flex align-items-center gap-1.5 small text-success mt-1">
                                        <i class="bi bi-check-circle-fill"></i>
                                        <span>Berkas saat ini:</span>
                                        <a href="{{ route('lpj.download', $lpj->id) }}" class="fw-semibold text-primary text-decoration-underline" target="_blank">Unduh Berkas</a>
                                    </div>
                                    <small class="text-muted d-block" style="font-size: 0.73rem;">Pilih file PDF/Word baru jika ingin memperbarui berkas tanpa batasan ukuran file.</small>
                                @else
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Mendukung dokumen PDF atau Word (.pdf, .doc, .docx) tanpa batasan ukuran file.</small>
                                @endif
                            </div>
                            @error('file_lpj')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Link Google Drive / Cloud LPJ -->
                        <div class="col-md-4">
                            <label for="link_lpj" class="form-label fw-semibold">Tautan / Link LPJ</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
                                <input type="url" 
                                       class="form-control @error('link_lpj') is-invalid @enderror" 
                                       id="link_lpj" 
                                       name="link_lpj" 
                                       value="{{ old('link_lpj', $lpj->link_lpj) }}" 
                                       placeholder="https://drive.google.com/...">
                            </div>
                            <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">Link Google Drive, OneDrive, atau Dokumen Cloud</small>
                            @error('link_lpj')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Keterangan / Catatan -->
                        <div class="col-md-12">
                            <label for="keterangan" class="form-label fw-semibold">Keterangan / Ringkasan LPJ</label>
                            <textarea class="form-control @error('keterangan') is-invalid @enderror" 
                                      id="keterangan" 
                                      name="keterangan" 
                                      rows="4" 
                                      placeholder="Tambahkan ringkasan atau rincian penting terkait laporan pertanggungjawaban ini...">{{ old('keterangan', $lpj->keterangan) }}</textarea>
                            @error('keterangan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 mt-4 pt-3 border-top d-flex gap-2">
                            <button type="submit" class="btn btn-warning text-white d-inline-flex align-items-center gap-1.5 px-4 fw-semibold">
                                <i class="bi bi-save"></i> Perbarui LPJ
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
        } else {
            inputNama.style.display = 'none';
            inputNama.value = '';
            inputId.value = '';
        }
    });

    if (selector.value && selector.value !== '__CUSTOM__') {
        inputNama.value = selector.value;
    }
});
</script>
@endpush
