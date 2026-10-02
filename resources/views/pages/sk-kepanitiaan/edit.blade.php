@extends('layouts.dashboard.template')

@section('page-style-class', 'menu-page-sk-kepanitiaan-edit')
@section('content')
    <div class="pagetitle">
        <h1>Edit SK Kepanitiaan</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Pengaturan Sistem</li>
                <li class="breadcrumb-item"><a href="{{ route('sk-kepanitiaan.index') }}">SK Kepanitiaan</a></li>
                <li class="breadcrumb-item active">Edit SK</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm border-0 rounded-3">
                    <div
                        class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="card-title m-0 fw-bold text-dark fs-6 d-flex align-items-center gap-2">
                                <i class="bi bi-pencil-square text-warning"></i> Edit Data SK Kepanitiaan
                            </h5>
                            <p class="text-muted small mb-0 mt-0.5">Perbarui rincian metadata atau ganti berkas dokumen SK.
                            </p>
                        </div>
                        <a href="{{ route('sk-kepanitiaan.index') }}"
                            class="btn btn-outline-secondary btn-sm rounded-2 px-3 py-1.5">
                            <i class="bi bi-arrow-left me-1"></i> Kembali
                        </a>
                    </div>

                    <div class="card-body p-4">
                        <form action="{{ route('sk-kepanitiaan.update', $sk->id) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row g-3 mb-3">
                                <div class="col-md-8">
                                    <label for="nama_kepanitiaan" class="form-label fw-bold text-dark">
                                        Nama Kepanitiaan <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                        class="form-control @error('nama_kepanitiaan') is-invalid @enderror"
                                        id="nama_kepanitiaan" name="nama_kepanitiaan"
                                        value="{{ old('nama_kepanitiaan', $sk->nama_kepanitiaan) }}" required
                                        style="border-radius: 8px;">
                                    @error('nama_kepanitiaan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="status" class="form-label fw-bold text-dark">
                                        Status Kepanitiaan <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('status') is-invalid @enderror" id="status"
                                        name="status" style="border-radius: 8px;">
                                        <option value="Aktif"
                                            {{ old('status', $sk->status) === 'Aktif' ? 'selected' : '' }}>🟢 Aktif Berjalan
                                        </option>
                                        <option value="Selesai"
                                            {{ old('status', $sk->status) === 'Selesai' ? 'selected' : '' }}>⚪ Selesai Masa
                                            Tugas</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="nomor_sk" class="form-label fw-semibold text-dark">Nomor SK</label>
                                    <input type="text" class="form-control @error('nomor_sk') is-invalid @enderror"
                                        id="nomor_sk" name="nomor_sk" value="{{ old('nomor_sk', $sk->nomor_sk) }}"
                                        style="border-radius: 8px;">
                                    @error('nomor_sk')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3">
                                    <label for="tahun_akademik" class="form-label fw-semibold text-dark">Tahun
                                        Akademik</label>
                                    <input type="text" class="form-control @error('tahun_akademik') is-invalid @enderror"
                                        id="tahun_akademik" name="tahun_akademik"
                                        value="{{ old('tahun_akademik', $sk->tahun_akademik) }}"
                                        style="border-radius: 8px;">
                                    @error('tahun_akademik')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3">
                                    <label for="tanggal_sk" class="form-label fw-semibold text-dark">Tanggal SK
                                        Ditetapkan</label>
                                    <input type="date" class="form-control @error('tanggal_sk') is-invalid @enderror"
                                        id="tanggal_sk" name="tanggal_sk"
                                        value="{{ old('tanggal_sk', $sk->tanggal_sk ? $sk->tanggal_sk->format('Y-m-d') : '') }}"
                                        style="border-radius: 8px;">
                                    @error('tanggal_sk')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label
                                        class="form-label fw-bold text-dark d-flex align-items-center justify-content-between">
                                        <span>Ganti Berkas Dokumen SK (PDF) <small class="text-muted fw-normal">(Kosongkan
                                                bila tidak ingin mengganti berkas)</small></span>
                                        <span class="badge bg-light text-muted border fw-normal"
                                            style="font-size: 0.75rem;">Maksimal 30 MB</span>
                                    </label>

                                    @if ($sk->file_sk)
                                        <div
                                            class="p-2.5 px-3 bg-light border rounded-3 mb-2 d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center gap-2 small">
                                                <i class="bi bi-file-earmark-check-fill text-danger fs-5"></i>
                                                <span class="text-secondary">Berkas SK tersimpan saat ini:</span>
                                                <span class="fw-semibold text-dark">{{ basename($sk->file_sk) }}</span>
                                            </div>
                                            <a href="{{ asset('storage/' . $sk->file_sk) }}" target="_blank"
                                                class="btn btn-outline-danger btn-sm text-nowrap d-inline-flex align-items-center gap-1 rounded-2 px-2.5 py-1"
                                                style="font-size: 0.78rem;">
                                                <i class="bi bi-box-arrow-up-right"></i> Buka Berkas
                                            </a>
                                        </div>
                                    @endif

                                    <div id="dropzone"
                                        class="sk-dropzone position-relative rounded-3 p-3 text-center border-2 border-dashed"
                                        style="border-color: #cbd5e1; background-color: #f8fafc; cursor: pointer; transition: all 0.2s ease-in-out;">

                                        <!-- Hidden Native File Input -->
                                        <input type="file" class="@error('file_sk') is-invalid @enderror d-none"
                                            id="file_sk" name="file_sk" accept="application/pdf,.pdf">

                                        <!-- Idle State -->
                                        <div id="dropzone-idle" class="py-2">
                                            <div class="dropzone-icon-wrapper mb-2 mx-auto rounded-circle d-flex align-items-center justify-content-center"
                                                style="width: 48px; height: 48px; background: rgba(220, 53, 69, 0.1); color: #dc3545;">
                                                <i class="bi bi-file-earmark-pdf fs-2"></i>
                                            </div>
                                            <h6 class="fw-bold text-dark mb-1" style="font-size: 0.9rem;">
                                                Tarik &amp; Lepaskan berkas PDF baru ke sini
                                            </h6>
                                            <p class="text-muted small mb-0">
                                                atau <span class="text-primary fw-semibold text-decoration-underline">Klik
                                                    untuk memilih berkas dari komputer</span>
                                            </p>
                                        </div>

                                        <!-- Drag Active State -->
                                        <div id="dropzone-dragover" class="py-2 d-none">
                                            <div class="dropzone-icon-wrapper mb-2 mx-auto rounded-circle d-flex align-items-center justify-content-center bg-primary text-white"
                                                style="width: 48px; height: 48px;">
                                                <i class="bi bi-cloud-arrow-up-fill fs-2"></i>
                                            </div>
                                            <h6 class="fw-bold text-primary mb-1" style="font-size: 0.95rem;">
                                                Lepaskan berkas PDF di sini!
                                            </h6>
                                            <p class="text-muted small mb-0">Berkas baru akan diproses menggantikan berkas
                                                lama</p>
                                        </div>

                                        <!-- File Selected Preview State -->
                                        <div id="dropzone-preview" class="d-none py-1 text-start">
                                            <div
                                                class="p-2.5 px-3 bg-white border rounded-3 d-flex align-items-center justify-content-between shadow-sm">
                                                <div class="d-flex align-items-center gap-3 overflow-hidden">
                                                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                                                        style="width: 42px; height: 42px; background: rgba(220, 53, 69, 0.12); color: #dc3545;">
                                                        <i class="bi bi-file-earmark-pdf-fill fs-3"></i>
                                                    </div>
                                                    <div class="overflow-hidden">
                                                        <div class="fw-bold text-dark text-truncate" id="preview-filename"
                                                            style="max-width: 400px;">-</div>
                                                        <div
                                                            class="text-muted small d-flex align-items-center gap-2 mt-0.5">
                                                            <span id="preview-filesize" class="fw-medium">-</span>
                                                            <span>&bull;</span>
                                                            <span
                                                                class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-0.5"
                                                                style="font-size: 0.72rem;">
                                                                <i class="bi bi-arrow-repeat me-1"></i> Berkas Pengganti
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center gap-2 flex-shrink-0 ms-3">
                                                    <button type="button"
                                                        class="btn btn-outline-primary btn-sm rounded-2 px-2.5 py-1"
                                                        id="btn-change-file">
                                                        <i class="bi bi-arrow-repeat"></i> Ganti
                                                    </button>
                                                    <button type="button"
                                                        class="btn btn-outline-danger btn-sm rounded-2 px-2 py-1"
                                                        id="btn-remove-file" title="Batalkan pilihan berkas">
                                                        <i class="bi bi-trash3"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        @error('file_sk')
                                            <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-text text-muted mt-1.5" style="font-size: 0.76rem;">
                                        Catatan: Mengunggah berkas baru akan secara otomatis memindai ulang susunan personel
                                        dan memperbarui penugasan pegawai.
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label for="keterangan" class="form-label fw-semibold text-dark">Catatan / Keterangan
                                        Tambahan</label>
                                    <textarea class="form-control @error('keterangan') is-invalid @enderror" id="keterangan" name="keterangan"
                                        rows="2" style="border-radius: 8px;">{{ old('keterangan', $sk->keterangan) }}</textarea>
                                    @error('keterangan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-end gap-2 pt-3 border-top mt-4">
                                <a href="{{ route('sk-kepanitiaan.index') }}"
                                    class="btn btn-secondary px-3 py-2 rounded-2" style="min-height: 40px;">
                                    Batal
                                </a>
                                <button type="submit"
                                    class="btn btn-primary fw-bold px-4 py-2 rounded-2 shadow-sm d-inline-flex align-items-center gap-2"
                                    style="min-height: 40px;">
                                    <i class="bi bi-save me-1"></i> Simpan Perubahan
                                </button>
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
        $(document).ready(function() {
            const dropzone = $('#dropzone');
            const fileInput = document.getElementById('file_sk');
            const dropzoneIdle = $('#dropzone-idle');
            const dropzoneDragover = $('#dropzone-dragover');
            const dropzonePreview = $('#dropzone-preview');
            const previewFilename = $('#preview-filename');
            const previewFilesize = $('#preview-filesize');
            const btnChangeFile = $('#btn-change-file');
            const btnRemoveFile = $('#btn-remove-file');

            function formatBytes(bytes, decimals = 2) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const dm = decimals < 0 ? 0 : decimals;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
            }

            function setFile(file) {
                if (!file) return;

                const isPdf = file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');
                if (!isPdf) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Format Berkas Salah',
                        text: 'Hanya berkas berformat PDF (.pdf) yang diperbolehkan!',
                        confirmButtonColor: '#3085d6'
                    });
                    clearFile();
                    return;
                }

                const maxSize = 30 * 1024 * 1024;
                if (file.size > maxSize) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Ukuran Berkas Terlalu Besar',
                        text: 'Ukuran berkas melebihi batas maksimum 30 MB.',
                        confirmButtonColor: '#3085d6'
                    });
                    clearFile();
                    return;
                }

                previewFilename.text(file.name);
                previewFilesize.text(formatBytes(file.size));
                dropzoneIdle.addClass('d-none');
                dropzoneDragover.addClass('d-none');
                dropzonePreview.removeClass('d-none');
            }

            function clearFile() {
                fileInput.value = '';
                dropzonePreview.addClass('d-none');
                dropzoneDragover.addClass('d-none');
                dropzoneIdle.removeClass('d-none');
            }

            dropzone.on('click', function(e) {
                if ($(e.target).closest('#btn-remove-file').length) return;
                if ($(e.target).closest('#btn-change-file').length) {
                    fileInput.click();
                    return;
                }
                if (dropzonePreview.is(':visible')) return;
                fileInput.click();
            });

            btnChangeFile.on('click', function(e) {
                e.stopPropagation();
                fileInput.click();
            });

            btnRemoveFile.on('click', function(e) {
                e.stopPropagation();
                clearFile();
            });

            $(fileInput).on('change', function() {
                if (this.files && this.files.length > 0) {
                    setFile(this.files[0]);
                }
            });

            const dropzoneEl = dropzone[0];

            ['dragenter', 'dragover'].forEach(eventName => {
                dropzoneEl.addEventListener(eventName, function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.addClass('drag-active');
                    dropzoneIdle.addClass('d-none');
                    dropzonePreview.addClass('d-none');
                    dropzoneDragover.removeClass('d-none');
                }, false);
            });

            ['dragleave', 'dragend'].forEach(eventName => {
                dropzoneEl.addEventListener(eventName, function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.removeClass('drag-active');
                    dropzoneDragover.addClass('d-none');
                    if (fileInput.files && fileInput.files.length > 0) {
                        dropzonePreview.removeClass('d-none');
                    } else {
                        dropzoneIdle.removeClass('d-none');
                    }
                }, false);
            });

            dropzoneEl.addEventListener('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                dropzone.removeClass('drag-active');
                dropzoneDragover.addClass('d-none');

                const dt = e.dataTransfer;
                if (dt && dt.files && dt.files.length > 0) {
                    const droppedFile = dt.files[0];
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(droppedFile);
                    fileInput.files = dataTransfer.files;
                    setFile(droppedFile);
                } else {
                    if (fileInput.files && fileInput.files.length > 0) {
                        dropzonePreview.removeClass('d-none');
                    } else {
                        dropzoneIdle.removeClass('d-none');
                    }
                }
            }, false);
        });
    </script>
@endpush
