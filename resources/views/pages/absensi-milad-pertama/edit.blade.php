@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
    <h1>Edit Absensi MILAD Hari Pertama</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('absensi-milad-pertama.index') }}">Absensi Pertama</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title fw-bold text-primary mb-4">Form Edit Absensi MILAD Hari Pertama</h5>

                    <form action="{{ route('absensi-milad-pertama.update', $absensi->id) }}" method="POST" enctype="multipart/form-data" class="row g-4">
                        @csrf
                        @method('PUT')

                        <!-- User / Nama Pengguna Selection -->
                        <div class="col-md-12">
                            <label for="user_id" class="form-label fw-semibold">Nama Pengguna <span class="text-danger">*</span></label>
                            <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id" required>
                                <option value="" disabled>-- Pilih Nama Pengguna --</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" {{ old('user_id', $absensi->user_id) == $u->id ? 'selected' : '' }}>
                                        {{ $u->name }} ({{ $u->jabatan ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Divider -->
                        <div class="col-md-12">
                            <hr class="my-1 text-muted">
                        </div>

                        <!-- Arrival Section (Sesi Datang) -->
                        <div class="col-md-12">
                            <h6 class="fw-bold text-secondary mb-3"><i class="bi bi-box-arrow-in-right me-1"></i> Sesi Datang (Waktu Datang)</h6>

                            <!-- Hadir Datang Radios -->
                            <div class="row align-items-center mb-3">
                                <label class="col-md-2 col-form-label fw-semibold">Hadir Datang</label>
                                <div class="col-md-10 d-flex gap-4">
                                    <div class="form-check">
                                        <input class="form-check-input @error('hadir_datang') is-invalid @enderror" type="radio" name="hadir_datang" id="hadir_datang_hadir" value="Hadir" {{ old('hadir_datang', $absensi->hadir_datang) == 'Hadir' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="hadir_datang_hadir">Hadir</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input @error('hadir_datang') is-invalid @enderror" type="radio" name="hadir_datang" id="hadir_datang_izin" value="Izin" {{ old('hadir_datang', $absensi->hadir_datang) == 'Izin' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="hadir_datang_izin">Izin</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input @error('hadir_datang') is-invalid @enderror" type="radio" name="hadir_datang" id="hadir_datang_tidak" value="Tidak Hadir" {{ old('hadir_datang', $absensi->hadir_datang) == 'Tidak Hadir' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="hadir_datang_tidak">Tidak Hadir</label>
                                    </div>
                                </div>
                                @error('hadir_datang')
                                    <div class="text-danger mt-1 small d-block ms-md-3">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Hidden Waktu Datang & Visible Catatan -->
                            <input type="hidden" id="waktu_datang" name="waktu_datang" value="{{ old('waktu_datang', $absensi->waktu_datang ? substr($absensi->waktu_datang, 0, 5) : '') }}">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label for="catatan_hadir_datang" class="form-label fw-semibold">Catatan Datang</label>
                                    <textarea class="form-control @error('catatan_hadir_datang') is-invalid @enderror" id="catatan_hadir_datang" name="catatan_hadir_datang" rows="2" placeholder="Catatan kehadiran datang (opsional)">{{ old('catatan_hadir_datang', $absensi->catatan_hadir_datang) }}</textarea>
                                    @error('catatan_hadir_datang')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Divider -->
                        <div class="col-md-12">
                            <hr class="my-1 text-muted">
                        </div>

                        <!-- Departure Section (Sesi Pulang) -->
                        <div class="col-md-12">
                            <h6 class="fw-bold text-secondary mb-3"><i class="bi bi-box-arrow-right me-1"></i> Sesi Pulang (Waktu Pulang)</h6>

                            <!-- Hadir Pulang Radios -->
                            <div class="row align-items-center mb-3">
                                <label class="col-md-2 col-form-label fw-semibold">Hadir Pulang</label>
                                <div class="col-md-10 d-flex gap-4">
                                    <div class="form-check">
                                        <input class="form-check-input @error('hadir_pulang') is-invalid @enderror" type="radio" name="hadir_pulang" id="hadir_pulang_hadir" value="Hadir" {{ old('hadir_pulang', $absensi->hadir_pulang) == 'Hadir' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="hadir_pulang_hadir">Hadir</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input @error('hadir_pulang') is-invalid @enderror" type="radio" name="hadir_pulang" id="hadir_pulang_izin" value="Izin" {{ old('hadir_pulang', $absensi->hadir_pulang) == 'Izin' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="hadir_pulang_izin">Izin</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input @error('hadir_pulang') is-invalid @enderror" type="radio" name="hadir_pulang" id="hadir_pulang_tidak" value="Tidak Hadir" {{ old('hadir_pulang', $absensi->hadir_pulang) == 'Tidak Hadir' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="hadir_pulang_tidak">Tidak Hadir</label>
                                    </div>
                                </div>
                                @error('hadir_pulang')
                                    <div class="text-danger mt-1 small d-block ms-md-3">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Hidden Waktu Pulang & Visible Catatan -->
                            <input type="hidden" id="waktu_pulang" name="waktu_pulang" value="{{ old('waktu_pulang', $absensi->waktu_pulang ? substr($absensi->waktu_pulang, 0, 5) : '') }}">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label for="catatan_hadir_pulang" class="form-label fw-semibold">Catatan Pulang</label>
                                    <textarea class="form-control @error('catatan_hadir_pulang') is-invalid @enderror" id="catatan_hadir_pulang" name="catatan_hadir_pulang" rows="2" placeholder="Catatan kehadiran pulang (opsional)">{{ old('catatan_hadir_pulang', $absensi->catatan_hadir_pulang) }}</textarea>
                                    @error('catatan_hadir_pulang')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Divider -->
                        <div class="col-md-12">
                            <hr class="my-1 text-muted">
                        </div>

                        <!-- Bukti Izin Attachment -->
                        <div class="col-md-12">
                            <h6 class="fw-bold text-secondary mb-3"><i class="bi bi-file-earmark-check me-1"></i> Lampiran & Bukti Pendukung</h6>
                            <div class="row g-3 align-items-center">
                                <div class="col-md-8">
                                    <label for="bukti_izin" class="form-label fw-semibold">Ganti Bukti Izin (Opsional)</label>
                                    <input type="file" class="form-control @error('bukti_izin') is-invalid @enderror" id="bukti_izin" name="bukti_izin">
                                    <div class="form-text text-muted small mt-1">Format: JPG, JPEG, PNG, PDF, DOC, DOCX (Maksimal 5MB)</div>
                                    @error('bukti_izin')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold d-block">Bukti Izin Saat Ini</label>
                                    @if ($absensi->bukti_izin)
                                        <a href="{{ asset('storage/' . $absensi->bukti_izin) }}" target="_blank" class="btn btn-outline-info rounded w-100 d-inline-flex align-items-center justify-content-center gap-1 py-2">
                                            <i class="bi bi-file-earmark-text"></i> Lihat Berkas Saat Ini
                                        </a>
                                    @else
                                        <span class="badge bg-secondary p-2 d-block text-center rounded">Belum ada berkas terlampir</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="col-12 mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> Simpan Perubahan</button>
                            <a href="{{ route('absensi-milad-pertama.index') }}" class="btn btn-secondary px-4"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
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
        if ($.fn.select2) {
            $('#user_id').select2({
                placeholder: "-- Pilih Nama Pengguna --",
                allowClear: true,
                width: '100%'
            });
        }

        // Helper function to get current time in HH:MM format from device (realtime)
        function getCurrentTime() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            return `${hours}:${minutes}`;
        }

        // Allow toggling/unchecking radio buttons on click
        let lastChecked = {};
        $('input[type="radio"]').on('mousedown', function() {
            const name = $(this).attr('name');
            lastChecked[name] = $(this).is(':checked') ? this : null;
        }).on('click', function() {
            const name = $(this).attr('name');
            if (lastChecked[name] === this) {
                $(this).prop('checked', false);
                $(this).trigger('change');
                lastChecked[name] = null;
            }
        });

        // Automatically set/update waktu_datang when Hadir Datang status is changed
        $('input[name="hadir_datang"]').on('change', function() {
            const checkedRadio = $('input[name="hadir_datang"]:checked');
            if (checkedRadio.length > 0) {
                $('#waktu_datang').val(getCurrentTime());
            } else {
                $('#waktu_datang').val('');
            }
        });

        // Automatically set/update waktu_pulang when Hadir Pulang status is changed
        $('input[name="hadir_pulang"]').on('change', function() {
            const checkedRadio = $('input[name="hadir_pulang"]:checked');
            if (checkedRadio.length > 0) {
                $('#waktu_pulang').val(getCurrentTime());
            } else {
                $('#waktu_pulang').val('');
            }
        });
    });
</script>
@endpush
