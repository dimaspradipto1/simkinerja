@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
    <h1>Kegiatan</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Kegiatan</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    @if(isset($pendingRequests) && $pendingRequests->isNotEmpty())
    <!-- Section: Permohonan Perpanjangan Waktu Masuk -->
    <div class="row mb-3">
        <div class="col-lg-12">
            <div class="alert alert-warning border border-warning shadow-sm p-3 rounded-3 mb-0">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2 pb-2 border-bottom border-warning-subtle">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-danger fs-6 px-2 py-1"><i class="bi bi-bell-fill me-1"></i> {{ $pendingRequests->count() }} Permohonan Perpanjangan Waktu Masuk</span>
                        <span class="text-dark small fw-semibold">Tugas di bawah ini telah melewati estimasi dan membutuhkan persetujuan batas waktu baru:</span>
                    </div>
                </div>
                <div class="d-flex flex-column gap-2">
                    @foreach($pendingRequests as $pr)
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 bg-white p-2 px-3 rounded border border-warning shadow-2xs">
                        <div class="d-flex flex-column">
                            <div class="fw-bold text-dark small">
                                <i class="bi bi-person-fill text-primary me-1"></i>{{ $pr->user->name ?? '-' }} <span class="text-muted fw-normal">({{ $pr->user->jabatan ?? '-' }})</span> &mdash; <span class="text-success fw-bold">{{ $pr->uraian_tugas }}</span>
                            </div>
                            @php
                                $rReason = $pr->estimasi_unlock_reason;
                                $tujuanPimpinan = null;
                                if (preg_match('/^\[Tujuan:\s*([^\]]+)\]\s*(.*)$/s', $rReason, $mMatches)) {
                                    $tujuanPimpinan = $mMatches[1];
                                    $rReason = $mMatches[2];
                                }
                            @endphp
                            <div class="text-secondary small mt-1 d-flex flex-wrap align-items-center gap-1" style="font-size: 0.76rem;">
                                @if($tujuanPimpinan)
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><i class="bi bi-person-badge-fill me-1"></i>Tujuan: {{ $tujuanPimpinan }}</span>
                                @endif
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="bi bi-exclamation-circle me-1"></i>Kendala:</span>
                                <span class="text-dark fw-medium">{{ $rReason }}</span>
                                <span class="text-muted ms-1">&bull; <i class="bi bi-clock me-1"></i>Diajukan: {{ \Carbon\Carbon::parse($pr->estimasi_unlock_requested_at)->diffForHumans() }}</span>
                            </div>
                        </div>
                        <div>
                            @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isPimpinanRektorat() || auth()->user()->isPimpinanUnit()))
                            <button type="button" class="btn btn-sm btn-warning text-dark fw-bold" data-id="{{ $pr->id }}" data-uraian="{{ e($pr->uraian_tugas) }}" data-pegawai="{{ e($pr->user->name ?? '-') }}" data-reason="{{ e($pr->estimasi_unlock_reason) }}" data-tgl-selesai="{{ e($pr->estimasi_tanggal_selesai ?? date('Y-m-d')) }}" data-jam-selesai="{{ e($pr->estimasi_jam_selesai ? substr($pr->estimasi_jam_selesai, 0, 5) : '23:59') }}" data-route="{{ route('kegiatan.approve-extension', $pr->id) }}" onclick="window.openApproveExtensionModal(this)">
                                <i class="bi bi-clock-history me-1"></i> Beri Perpanjangan Waktu
                            </button>
                            @else
                            <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i> Menunggu Persetujuan Pimpinan</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title fw-bold">Form Input Kegiatan</h5>

                    <form action="{{ route('kegiatan.store') }}" method="POST" enctype="multipart/form-data" class="row g-3">
                        @csrf

                        @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isPimpinanRektorat() || auth()->user()->isPimpinanUnit() || auth()->user()->isSuperAdmin()))
                        <div class="col-md-12">
                            <label for="user_id" class="form-label fw-semibold">Jabatan - Nama Pegawai <span class="text-danger">*</span></label>
                            <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id">
                                <option value="" {{ old('user_id') ? '' : 'selected' }}>-- Pilih Jabatan - Nama Pegawai --</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>
                                        {{ $u->jabatan ?? '-' }} - {{ $u->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        @else
                        <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                        @endif

                        <div class="col-md-4">
                            <label for="periode_akademik_id" class="form-label fw-semibold">Periode Akademik <span class="text-danger">*</span></label>
                            <select class="form-select @error('periode_akademik_id') is-invalid @enderror" id="periode_akademik_id" name="periode_akademik_id" required>
                                <option value="" disabled {{ old('periode_akademik_id') ? '' : 'selected' }}>-- Pilih Periode Akademik --</option>
                                @foreach($periodeAkademiks as $pa)
                                    <option value="{{ $pa->id }}" {{ old('periode_akademik_id', $defaultPeriodeId ?? '') == $pa->id ? 'selected' : '' }}>
                                        {{ $pa->nama_periode }}
                                    </option>
                                @endforeach
                            </select>
                            @error('periode_akademik_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="kategori" class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                            <select class="form-select @error('kategori') is-invalid @enderror" id="kategori" name="kategori" required>
                                <option value="" disabled {{ old('kategori') ? '' : 'selected' }}>-- Pilih Kategori --</option>
                                @if(isset($kategoris) && $kategoris->count() > 0)
                                    @foreach($kategoris as $k)
                                        <option value="{{ $k->nama_kategori }}" {{ old('kategori') == $k->nama_kategori ? 'selected' : '' }}>
                                            {{ $k->nama_kategori }}
                                        </option>
                                    @endforeach
                                @else
                                    <option value="Rencana Kerja" {{ old('kategori') == 'Rencana Kerja' ? 'selected' : '' }}>Rencana Kerja</option>
                                    <option value="Kepanitiaan" {{ old('kategori') == 'Kepanitiaan' ? 'selected' : '' }}>Kepanitiaan</option>
                                    <option value="Insidentil" {{ old('kategori') == 'Insidentil' ? 'selected' : '' }}>Insidentil</option>
                                @endif
                            </select>
                            @error('kategori')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="hari" class="form-label fw-semibold">Hari Pelaksanaan</label>
                            <select class="form-select @error('hari') is-invalid @enderror" id="hari" name="hari">
                                <option value="" {{ old('hari') ? '' : 'selected' }}>-- Pilih Hari --</option>
                                @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $h)
                                    <option value="{{ $h }}" {{ old('hari') == $h ? 'selected' : '' }}>{{ $h }}</option>
                                @endforeach
                            </select>
                            @error('hari')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Input Dinamis: Jenis Absensi (Tampil jika Kategori = Kepanitiaan) -->
                        <div class="col-md-12" id="wrapper_jenis_absensi" style="display: none;">
                            <div class="p-3 bg-light rounded-3 border border-primary-subtle shadow-2xs">
                                <label for="jenis_absensi" class="form-label fw-bold text-primary d-flex align-items-center gap-2 mb-1">
                                    <i class="bi bi-qr-code-scan text-primary fs-5"></i> Jenis Absensi Kepanitiaan
                                </label>
                                <div class="text-muted small mb-2">Pilih jenis absensi yang terhubung dengan kegiatan kepanitiaan ini agar tombol <strong>Barcode Absensi</strong> otomatis tampil pada daftar tugas.</div>
                                <select class="form-select @error('jenis_absensi') is-invalid @enderror" id="jenis_absensi" name="jenis_absensi">
                                    <option value="">-- Pilih Jenis Absensi --</option>
                                    @if(isset($absensiSessions) && $absensiSessions->count() > 0)
                                        @foreach($absensiSessions as $kategoriAbsensi => $sessions)
                                            <optgroup label="Sesi Absensi {{ $kategoriAbsensi }}">
                                                @foreach($sessions as $sess)
                                                    <option value="{{ $sess->slug }}" {{ old('jenis_absensi') == $sess->slug ? 'selected' : '' }}>
                                                        {{ $sess->nama }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    @else
                                        <optgroup label="PKKMB">
                                            <option value="absensi-pkkmb-pertama" {{ old('jenis_absensi') == 'absensi-pkkmb-pertama' ? 'selected' : '' }}>Absensi PKKMB Hari Pertama</option>
                                            <option value="absensi-pkkmb-kedua" {{ old('jenis_absensi') == 'absensi-pkkmb-kedua' ? 'selected' : '' }}>Absensi PKKMB Hari Kedua</option>
                                            <option value="absensi-pkkmb-ketiga" {{ old('jenis_absensi') == 'absensi-pkkmb-ketiga' ? 'selected' : '' }}>Absensi PKKMB Hari Ketiga</option>
                                        </optgroup>
                                        <optgroup label="ESQ">
                                            <option value="absensi-esq-pertama" {{ old('jenis_absensi') == 'absensi-esq-pertama' ? 'selected' : '' }}>Absensi ESQ Hari Pertama</option>
                                            <option value="absensi-esq-kedua" {{ old('jenis_absensi') == 'absensi-esq-kedua' ? 'selected' : '' }}>Absensi ESQ Hari Kedua</option>
                                            <option value="absensi-esq-ketiga" {{ old('jenis_absensi') == 'absensi-esq-ketiga' ? 'selected' : '' }}>Absensi ESQ Hari Ketiga</option>
                                            <option value="absensi-esq-keempat" {{ old('jenis_absensi') == 'absensi-esq-keempat' ? 'selected' : '' }}>Absensi ESQ Hari Keempat</option>
                                        </optgroup>
                                        <optgroup label="MILAD UIS">
                                            <option value="absensi-milad-pertama" {{ old('jenis_absensi') == 'absensi-milad-pertama' ? 'selected' : '' }}>Absensi MILAD Hari Pertama</option>
                                            <option value="absensi-milad-kedua" {{ old('jenis_absensi') == 'absensi-milad-kedua' ? 'selected' : '' }}>Absensi MILAD Hari Kedua</option>
                                        </optgroup>
                                        <optgroup label="Kuliah Umum">
                                            <option value="absensi-kuliah-umum-pertama" {{ old('jenis_absensi') == 'absensi-kuliah-umum-pertama' ? 'selected' : '' }}>Absensi Kuliah Umum Hari Pertama</option>
                                        </optgroup>
                                    @endif
                                </select>
                                @error('jenis_absensi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label for="uraian_tugas" class="form-label fw-semibold">Uraian Kegiatan <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('uraian_tugas') is-invalid @enderror" id="uraian_tugas" name="uraian_tugas" rows="4" placeholder="Tuliskan detail rencana kegiatan yang akan dikerjakan" required>{{ old('uraian_tugas') }}</textarea>
                            @error('uraian_tugas')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label for="tagged_users" class="form-label fw-semibold">Tag Rekan Kerja</label>
                            <select class="form-select @error('tagged_users') is-invalid @enderror" id="tagged_users" name="tagged_users[]" multiple="multiple">
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" {{ (is_array(old('tagged_users')) && in_array($u->id, old('tagged_users'))) ? 'selected' : '' }}>
                                        {{ $u->jabatan ?? '-' }} - {{ $u->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text text-muted small">Pilih satu atau beberapa rekan kerja yang terlibat dalam kegiatan ini.</div>
                            @error('tagged_users')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="estimasi_tanggal_mulai" class="form-label fw-semibold text-secondary">Estimasi Tgl Mulai</label>
                            <input type="date" class="form-control @error('estimasi_tanggal_mulai') is-invalid @enderror" id="estimasi_tanggal_mulai" name="estimasi_tanggal_mulai" value="{{ old('estimasi_tanggal_mulai') }}">
                            @error('estimasi_tanggal_mulai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="estimasi_jam_mulai" class="form-label fw-semibold text-secondary">Estimasi Jam Mulai</label>
                            <input type="time" class="form-control @error('estimasi_jam_mulai') is-invalid @enderror" id="estimasi_jam_mulai" name="estimasi_jam_mulai" value="{{ old('estimasi_jam_mulai') }}">
                            @error('estimasi_jam_mulai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="estimasi_tanggal_selesai" class="form-label fw-semibold text-secondary">Estimasi Tgl Selesai</label>
                            <input type="date" class="form-control @error('estimasi_tanggal_selesai') is-invalid @enderror" id="estimasi_tanggal_selesai" name="estimasi_tanggal_selesai" value="{{ old('estimasi_tanggal_selesai') }}">
                            @error('estimasi_tanggal_selesai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="estimasi_jam_selesai" class="form-label fw-semibold text-secondary">Estimasi Jam Selesai</label>
                            <input type="time" class="form-control @error('estimasi_jam_selesai') is-invalid @enderror" id="estimasi_jam_selesai" name="estimasi_jam_selesai" value="{{ old('estimasi_jam_selesai') }}">
                            @error('estimasi_jam_selesai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="file" class="form-label fw-semibold text-secondary">Berkas / Lampiran (Opsional)</label>
                            <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file">
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="url_external" class="form-label fw-semibold text-secondary">Tautan Eksternal / Link Luar</label>
                            <input type="text" class="form-control @error('url_external') is-invalid @enderror" id="url_external" name="url_external" placeholder="https://drive.google.com/..." value="{{ old('url_external') }}">
                            @error('url_external')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 mt-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="start_now" name="start_now" value="1" {{ old('start_now') ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="start_now">Mulai Kegiatan Sekarang (Otomatis Catat Waktu Mulai)</label>
                            </div>
                        </div>

                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan</button>
                            <a href="{{ route('kegiatan.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
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
        if ($('#user_id').length) {
            $('#user_id').select2({
                theme: 'bootstrap-5',
                placeholder: "-- Pilih Jabatan - Nama --",
                allowClear: true,
                width: '100%'
            });
        }
        if ($('#tagged_users').length) {
            $('#tagged_users').select2({
                theme: 'bootstrap-5',
                placeholder: "-- Pilih Rekan Kerja --",
                allowClear: true,
                width: '100%'
            });
        }

        function toggleJenisAbsensi() {
            var selectedKategori = $('#kategori').val();
            if (selectedKategori && selectedKategori.trim().toLowerCase() === 'kepanitiaan') {
                $('#wrapper_jenis_absensi').slideDown(250);
            } else {
                $('#wrapper_jenis_absensi').slideUp(200);
            }
        }

        $('#kategori').on('change', toggleJenisAbsensi);
        toggleJenisAbsensi();
    });
</script>
@endpush
