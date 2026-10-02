@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
    <h1>Upload SK Kepanitiaan</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item">Pengaturan Sistem</li>
            <li class="breadcrumb-item"><a href="{{ route('sk-kepanitiaan.index') }}">SK Kepanitiaan</a></li>
            <li class="breadcrumb-item active">Upload SK</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="card-title m-0 fw-bold text-dark fs-6 d-flex align-items-center gap-2">
                            <i class="bi bi-cloud-arrow-up-fill text-primary"></i> Formulir Upload SK Kepanitiaan
                        </h5>
                        <p class="text-muted small mb-0 mt-0.5">Unggah berkas PDF Surat Keputusan untuk dianalisis dan diekstraksi secara otomatis.</p>
                    </div>
                    <a href="{{ route('sk-kepanitiaan.index') }}" class="btn btn-outline-secondary btn-sm rounded-2 px-3 py-1.5">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>
                </div>

                <div class="card-body p-4">
                    <!-- Petunjuk Otomatisasi -->
                    <div class="alert alert-primary bg-primary-subtle border-0 py-3 px-3 mb-4 rounded-3 d-flex align-items-start gap-3">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px;">
                            <i class="bi bi-magic fs-5"></i>
                        </div>
                        <div class="small">
                            <div class="fw-bold text-dark mb-1" style="font-size: 0.9rem;">Pengecekan Otomatis &amp; Sinkronisasi Dinamis:</div>
                            <span class="text-secondary" style="line-height: 1.5;">
                                Setelah berkas PDF SK diunggah, sistem SIMKINERJA akan langsung memindai isi dokumen, mengenali nama-nama personel serta jabatannya dalam SK (Pelindung, Penanggung Jawab, Pengarah, Ketua, Sekretaris, Bendahara, dsb).
                                Sistem kemudian mencocokkannya dengan akun pegawai dan <strong>menambahkan peran kepanitiaan ke dalam akun pegawai secara dinamis</strong> tanpa menimpa kepanitiaan lain yang telah dipegang.
                            </span>
                        </div>
                    </div>

                    <form action="{{ route('sk-kepanitiaan.store') }}" method="POST" enctype="multipart/form-data" id="formUploadSk">
                        @csrf

                        <!-- Upload File Input (Drag & Drop Zone) -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark d-flex align-items-center justify-content-between">
                                <span>Berkas Dokumen SK (Format PDF) <span class="text-danger">*</span></span>
                                <span class="badge bg-light text-muted border fw-normal" style="font-size: 0.75rem;">Maksimal 30 MB</span>
                            </label>

                            <div id="dropzone" class="sk-dropzone position-relative rounded-3 p-4 text-center border-2 border-dashed"
                                 style="border-color: #cbd5e1; background-color: #f8fafc; cursor: pointer; transition: all 0.2s ease-in-out;">
                                
                                <!-- Hidden Native File Input -->
                                <input type="file" class="@error('file_sk') is-invalid @enderror d-none" id="file_sk" name="file_sk" accept="application/pdf,.pdf" required>

                                <!-- Idle State -->
                                <div id="dropzone-idle" class="py-2">
                                    <div class="dropzone-icon-wrapper mb-3 mx-auto rounded-circle d-flex align-items-center justify-content-center shadow-xs"
                                         style="width: 64px; height: 64px; background: rgba(220, 53, 69, 0.1); color: #dc3545;">
                                        <i class="bi bi-file-earmark-pdf fs-1"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1" style="font-size: 0.95rem;">
                                        Tarik &amp; Lepaskan berkas PDF ke sini
                                    </h6>
                                    <p class="text-muted small mb-3">
                                        atau <span class="text-primary fw-semibold text-decoration-underline">Klik untuk memilih berkas dari komputer</span>
                                    </p>
                                    <div class="d-inline-flex align-items-center gap-2 px-3 py-1.5 bg-white border rounded-pill text-muted" style="font-size: 0.78rem;">
                                        <i class="bi bi-shield-check text-success"></i>
                                        Format: <strong>.PDF</strong> &bull; Ukuran maksimal: <strong>30 MB</strong>
                                    </div>
                                </div>

                                <!-- Drag Active State -->
                                <div id="dropzone-dragover" class="py-3 d-none">
                                    <div class="dropzone-icon-wrapper mb-3 mx-auto rounded-circle d-flex align-items-center justify-content-center bg-primary text-white shadow"
                                         style="width: 64px; height: 64px;">
                                        <i class="bi bi-cloud-arrow-up-fill fs-1 animate__animated animate__bounceIn"></i>
                                    </div>
                                    <h6 class="fw-bold text-primary mb-1" style="font-size: 1rem;">
                                        Lepaskan berkas PDF di sini!
                                    </h6>
                                    <p class="text-muted small mb-0">Berkas akan langsung disiapkan untuk dianalisis sistem</p>
                                </div>

                                <!-- File Preview State -->
                                <div id="dropzone-preview" class="d-none py-1 text-start">
                                    <div class="p-3 bg-white border rounded-3 d-flex align-items-center justify-content-between shadow-sm">
                                        <div class="d-flex align-items-center gap-3 overflow-hidden">
                                            <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                                                 style="width: 50px; height: 50px; background: rgba(220, 53, 69, 0.12); color: #dc3545;">
                                                <i class="bi bi-file-earmark-pdf-fill fs-2"></i>
                                            </div>
                                            <div class="overflow-hidden">
                                                <div class="fw-bold text-dark text-truncate" id="preview-filename" style="max-width: 420px;">-</div>
                                                <div class="text-muted small d-flex align-items-center gap-2 mt-1">
                                                    <span id="preview-filesize" class="fw-medium">-</span>
                                                    <span>&bull;</span>
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5" style="font-size: 0.72rem;">
                                                        <i class="bi bi-check-circle-fill me-1"></i> Siap Diproses
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center gap-2 flex-shrink-0 ms-3">
                                            <button type="button" class="btn btn-outline-primary btn-sm rounded-2 px-3 py-1.5 d-inline-flex align-items-center gap-1" id="btn-change-file">
                                                <i class="bi bi-arrow-repeat"></i> Ganti
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm rounded-2 px-2.5 py-1.5" id="btn-remove-file" title="Hapus berkas terpilih">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                @error('file_sk')
                                    <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="nama_kepanitiaan" class="form-label fw-semibold text-dark">
                                    Nama Kepanitiaan <small class="text-muted">(Opsional, otomatis terdeteksi jika kosong)</small>
                                </label>
                                <input type="text" class="form-control @error('nama_kepanitiaan') is-invalid @enderror" id="nama_kepanitiaan" name="nama_kepanitiaan" value="{{ old('nama_kepanitiaan') }}" placeholder="Contoh: Panitia Penerimaan Mahasiswa Baru (PMB) 2026/2027" style="border-radius: 8px;">
                                @error('nama_kepanitiaan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="nomor_sk" class="form-label fw-semibold text-dark">
                                    Nomor SK <small class="text-muted">(Opsional, otomatis terdeteksi jika kosong)</small>
                                </label>
                                <input type="text" class="form-control @error('nomor_sk') is-invalid @enderror" id="nomor_sk" name="nomor_sk" value="{{ old('nomor_sk') }}" placeholder="Contoh: 926/UIS.R/SK/X/2025" style="border-radius: 8px;">
                                @error('nomor_sk')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="tahun_akademik" class="form-label fw-semibold text-dark">
                                    Tahun Akademik <small class="text-muted">(Opsional)</small>
                                </label>
                                <input type="text" class="form-control @error('tahun_akademik') is-invalid @enderror" id="tahun_akademik" name="tahun_akademik" value="{{ old('tahun_akademik', date('Y') . '/' . (date('Y') + 1)) }}" placeholder="Contoh: 2026/2027" style="border-radius: 8px;">
                                @error('tahun_akademik')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="tanggal_sk" class="form-label fw-semibold text-dark">
                                    Tanggal SK Ditetapkan <small class="text-muted">(Opsional)</small>
                                </label>
                                <input type="date" class="form-control @error('tanggal_sk') is-invalid @enderror" id="tanggal_sk" name="tanggal_sk" value="{{ old('tanggal_sk', date('Y-m-d')) }}" style="border-radius: 8px;">
                                @error('tanggal_sk')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="keterangan" class="form-label fw-semibold text-dark">Catatan / Keterangan Tambahan (Opsional)</label>
                                <textarea class="form-control @error('keterangan') is-invalid @enderror" id="keterangan" name="keterangan" rows="2" placeholder="Tuliskan catatan khusus atau rujukan SK jika diperlukan..." style="border-radius: 8px;">{{ old('keterangan') }}</textarea>
                                @error('keterangan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-end gap-2 pt-3 border-top mt-4">
                            <a href="{{ route('sk-kepanitiaan.index') }}" class="btn btn-secondary px-3 py-2 rounded-2" style="min-height: 40px;">
                                Batal
                            </a>
                            <button type="submit" class="btn btn-primary fw-bold px-4 py-2 rounded-2 shadow-sm d-inline-flex align-items-center gap-2" id="btnSubmitUpload" style="min-height: 40px;">
                                <i class="bi bi-cloud-arrow-up-fill"></i> Upload &amp; Proses Otomatis
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    .sk-dropzone {
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .sk-dropzone:hover {
        border-color: #3b82f6 !important;
        background-color: #f0f7ff !important;
    }
    .sk-dropzone.drag-active {
        border-color: #2563eb !important;
        background-color: #ebf5ff !important;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15);
        transform: scale(1.005);
    }
</style>
@endpush

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

            // Check if file is PDF
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

            // Max size 30 MB
            const maxSize = 30 * 1024 * 1024;
            if (file.size > maxSize) {
                Swal.fire({
                    icon: 'error',
                    title: 'Ukuran Berkas Terlalu Besar',
                    text: 'Ukuran berkas melebihi batas maksimum 30 MB. Silakan kompresi berkas terlebih dahulu.',
                    confirmButtonColor: '#3085d6'
                });
                clearFile();
                return;
            }

            // Update UI
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

        // Click on dropzone opens file dialog unless clicking buttons
        dropzone.on('click', function(e) {
            if ($(e.target).closest('#btn-remove-file').length) {
                return;
            }
            if ($(e.target).closest('#btn-change-file').length) {
                fileInput.click();
                return;
            }
            if (dropzonePreview.is(':visible')) {
                return; // Do nothing if preview is already visible and not clicking change
            }
            fileInput.click();
        });

        // Change button
        btnChangeFile.on('click', function(e) {
            e.stopPropagation();
            fileInput.click();
        });

        // Remove button
        btnRemoveFile.on('click', function(e) {
            e.stopPropagation();
            clearFile();
        });

        // File input changed via dialog
        $(fileInput).on('change', function() {
            if (this.files && this.files.length > 0) {
                setFile(this.files[0]);
            }
        });

        // Drag & Drop Events on dropzone
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
                
                // Assign to input file element via DataTransfer
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

        // Prevent browser default drop on window
        window.addEventListener('dragover', function(e) {
            e.preventDefault();
        }, false);
        window.addEventListener('drop', function(e) {
            e.preventDefault();
        }, false);

        // Form Submit
        $('#formUploadSk').on('submit', function() {
            if (!fileInput.files || !fileInput.files.length) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Dokumen Belum Dipilih',
                    text: 'Silakan pilih atau tarik berkas PDF SK terlebih dahulu.',
                    confirmButtonColor: '#3085d6'
                });
                return false;
            }

            const btn = $('#btnSubmitUpload');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Memproses &amp; Mengekstrak Dokumen...');
            return true;
        });
    });
</script>
@endpush
