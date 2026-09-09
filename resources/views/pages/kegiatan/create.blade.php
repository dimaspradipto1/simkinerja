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
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-0" style="background: linear-gradient(135deg, #fffdf2 0%, #fff9e6 100%); border: 1px solid #fde68a !important; border-left: 5px solid #f59e0b !important;">
                <div class="card-body p-3 p-sm-4">
                    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2 mb-3 pb-2 border-bottom" style="border-color: rgba(245, 158, 11, 0.25) !important;">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge bg-danger rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 shadow-xs" style="font-size: 0.82rem;">
                                <i class="bi bi-bell-fill"></i>
                                <span>{{ $pendingRequests->count() }} Permohonan Perpanjangan Waktu Masuk</span>
                            </span>
                            <span class="text-dark small fw-semibold">Tugas di bawah ini telah melewati batas estimasi dan menunggu persetujuan batas waktu baru:</span>
                        </div>
                    </div>
                    <div class="d-flex flex-column gap-2.5">
                        @foreach($pendingRequests as $pr)
                        @php
                            $rReason = $pr->estimasi_unlock_reason;
                            $tujuanPimpinan = null;
                            if (preg_match('/^\[Tujuan:\s*([^\]]+)\]\s*(.*)$/s', $rReason, $mMatches)) {
                                $tujuanPimpinan = $mMatches[1];
                                $rReason = $mMatches[2];
                            }
                        @endphp
                        <div class="card border-0 shadow-xs rounded-3 bg-white p-3 mb-0" style="border: 1px solid #f1f5f9 !important; transition: all 0.2s ease;">
                            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">
                                <div class="flex-grow-1 min-w-0 w-100">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-1.5">
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width: 28px; height: 28px; background: #e0f2fe; color: #0284c7; font-size: 0.8rem;">
                                                <i class="bi bi-person-fill"></i>
                                            </div>
                                            <span class="fw-bold text-dark" style="font-size: 0.92rem;">{{ $pr->user->name ?? '-' }}</span>
                                            @if(!empty($pr->user->jabatan))
                                                <span class="badge bg-light text-secondary border fw-normal" style="font-size: 0.72rem; padding: 3px 8px;">{{ $pr->user->jabatan }}</span>
                                            @endif
                                        </div>
                                        <span class="text-muted small d-inline-flex align-items-center gap-1" style="font-size: 0.75rem;">
                                            <i class="bi bi-clock-history text-secondary"></i> Diajukan: {{ \Carbon\Carbon::parse($pr->estimasi_unlock_requested_at)->diffForHumans() }}
                                        </span>
                                    </div>

                                    <div class="p-2 px-2.5 rounded-2 my-2" style="background-color: #f8fafc; border-left: 3px solid #0ea5e9;">
                                        <div class="d-flex align-items-start gap-2">
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-semibold flex-shrink-0 mt-0.5" style="font-size: 0.68rem; padding: 2px 6px;">Tugas</span>
                                            <span class="fw-semibold text-dark text-break" style="font-size: 0.88rem; line-height: 1.45;">{{ $pr->uraian_tugas }}</span>
                                        </div>
                                    </div>

                                    <div class="d-flex flex-wrap align-items-center gap-2 mt-1" style="font-size: 0.78rem;">
                                        @if($tujuanPimpinan)
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 d-inline-flex align-items-center gap-1">
                                                <i class="bi bi-person-badge-fill"></i>
                                                <span>Tujuan: <strong>{{ $tujuanPimpinan }}</strong></span>
                                            </span>
                                        @endif
                                        <div class="d-inline-flex align-items-center gap-1.5 p-1 px-2.5 rounded-2" style="background-color: #fef2f2; border: 1px solid #fee2e2;">
                                            <span class="badge bg-danger text-white rounded-pill px-1.5 py-0.5" style="font-size: 0.68rem;"><i class="bi bi-exclamation-triangle-fill"></i> Kendala</span>
                                            <span class="text-danger-emphasis fw-medium text-break">{{ $rReason }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="w-100 w-lg-auto d-flex justify-content-start justify-content-lg-end flex-shrink-0 align-self-stretch align-self-lg-center pt-2 pt-lg-0">
                                    @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isPimpinanRektorat() || auth()->user()->isPimpinanUnit()))
                                    <button type="button" 
                                            class="btn btn-warning btn-approve-ext d-inline-flex align-items-center justify-content-center gap-1.5 fw-bold" 
                                            data-id="{{ $pr->id }}" 
                                            data-uraian="{{ e($pr->uraian_tugas) }}" 
                                            data-pegawai="{{ e($pr->user->name ?? '-') }}" 
                                            data-reason="{{ e($pr->estimasi_unlock_reason) }}" 
                                            data-tgl-selesai="{{ e($pr->estimasi_tanggal_selesai ?? date('Y-m-d')) }}" 
                                            data-jam-selesai="{{ e($pr->estimasi_jam_selesai ? substr($pr->estimasi_jam_selesai, 0, 5) : '23:59') }}" 
                                            data-route="{{ route('kegiatan.approve-extension', $pr->id) }}" 
                                            onclick="window.openApproveExtensionModal(this)">
                                        <i class="bi bi-clock-history"></i>
                                        <span>Beri Perpanjangan Waktu</span>
                                    </button>
                                    @else
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle py-2 px-3 text-center w-100" style="font-size: 0.78rem;">
                                        <i class="bi bi-hourglass-split me-1"></i> Menunggu Persetujuan Pimpinan
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
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
