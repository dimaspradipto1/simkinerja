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
        <div class="col-12">
            <div class="p-2.5 p-sm-3 rounded-3 shadow-xs" style="background-color: #fffdf5; border: 1px solid #fde68a; border-left: 5px solid #f59e0b; width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
                <!-- Header Banner -->
                <div class="d-flex align-items-center gap-2 flex-wrap mb-2 pb-2 border-bottom" style="border-color: #fef08a !important;">
                    <span class="badge bg-danger rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.78rem;">
                        <i class="bi bi-bell-fill me-1"></i> {{ $pendingRequests->count() }} Permohonan Masuk
                    </span>
                    <span class="text-dark small fw-semibold">Tugas di bawah ini telah melewati batas estimasi dan menunggu persetujuan batas waktu baru:</span>
                </div>

                <!-- List of Items -->
                <div class="d-flex flex-column gap-2">
                    @foreach($pendingRequests as $pr)
                    @php
                        $rReason = $pr->estimasi_unlock_reason;
                        $tujuanPimpinan = null;
                        if (preg_match('/^\[Tujuan:\s*([^\]]+)\]\s*(.*)$/s', $rReason, $mMatches)) {
                            $tujuanPimpinan = $mMatches[1];
                            $rReason = $mMatches[2];
                        }
                    @endphp
                    <div class="p-2.5 p-sm-3 bg-white rounded-2 border shadow-2xs" style="border-color: #fed7aa !important; width: 100%; max-width: 100%; box-sizing: border-box;">
                        <!-- 1. Header: Pegawai & Timestamp -->
                        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-1.5">
                            <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                <i class="bi bi-person-circle text-primary fs-6"></i>
                                <span class="fw-bold text-dark" style="font-size: 0.88rem;">{{ $pr->user->name ?? '-' }}</span>
                                @if(!empty($pr->user->jabatan))
                                    <span class="badge bg-light text-secondary border fw-normal" style="font-size: 0.7rem;">{{ $pr->user->jabatan }}</span>
                                @endif
                            </div>
                            <span class="text-muted small" style="font-size: 0.73rem;">
                                <i class="bi bi-clock me-1"></i>{{ \Carbon\Carbon::parse($pr->estimasi_unlock_requested_at)->diffForHumans() }}
                            </span>
                        </div>

                        <!-- 2. Uraian Tugas -->
                        <div class="mb-2" style="word-break: break-word;">
                            <span class="badge bg-primary text-white me-1" style="font-size: 0.68rem; padding: 2px 6px;">Tugas</span>
                            <span class="fw-bold text-dark" style="font-size: 0.86rem;">{{ $pr->uraian_tugas }}</span>
                        </div>

                        <!-- 3. Tujuan & Kendala -->
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2.5" style="font-size: 0.75rem;">
                            @if($tujuanPimpinan)
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                    <i class="bi bi-person-badge me-1"></i>Tujuan: <strong>{{ $tujuanPimpinan }}</strong>
                                </span>
                            @endif
                            <div class="d-inline-flex align-items-start gap-1 p-1 px-2 rounded bg-danger-subtle text-danger border border-danger-subtle" style="word-break: break-word; max-width: 100%;">
                                <span class="fw-bold flex-shrink-0"><i class="bi bi-exclamation-circle-fill me-1"></i>Kendala:</span>
                                <span class="text-dark fw-medium">{{ strip_tags($rReason) }}</span>
                            </div>
                        </div>

                        <!-- 4. Tombol di Bawah -->
                        <div class="pt-2 border-top" style="border-color: #f1f5f9 !important;">
                            @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isPimpinanRektorat() || auth()->user()->isPimpinanUnit()))
                            <button type="button" 
                                    class="btn btn-warning btn-sm w-100 fw-bold d-flex align-items-center justify-content-center gap-1.5 text-dark" 
                                    style="background-color: #ffc107; border-color: #ffc107; padding: 7px 12px; font-size: 0.83rem; border-radius: 6px;"
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
                            <div class="text-center text-muted small py-1" style="font-size: 0.75rem;">
                                <i class="bi bi-hourglass-split me-1 text-warning"></i> Menunggu Persetujuan Pimpinan
                            </div>
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

                        @if(auth()->check() && auth()->user()->isSystemAdmin())
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

                        <!-- Input Dinamis: Sesi Presensi Kepanitiaan (Tampil jika Kategori = Kepanitiaan) -->
                        <div class="col-md-12" id="wrapper_jenis_absensi" style="display: none;">
                            <div class="p-3 bg-light rounded-3 border border-primary-subtle shadow-2xs">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                                    <div>
                                        <label class="form-label fw-bold text-primary d-flex align-items-center gap-2 mb-0">
                                            <i class="bi bi-qr-code-scan text-primary fs-5"></i> Sesi Presensi Kepanitiaan (Opsional)
                                        </label>
                                        <div class="text-muted small">Tentukan satu jenis absen untuk setiap sesi. Sesi rapat hanya mencatat satu kali kehadiran, bukan datang dan pulang.</div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold" id="btnTambahBarisSesi">
                                        <i class="bi bi-plus-circle me-1"></i> Tambah Sesi
                                    </button>
                                </div>

                                <div id="sesiPresensiContainer" class="d-flex flex-column gap-2 mt-2">
                                    <!-- Baris sesi ditambahkan dinamis -->
                                </div>
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

                        {{-- Durasi Pengerjaan (Hari / Jam - Opsional) --}}
                        <div class="col-12">
                            <div class="card border border-success-subtle bg-light-subtle rounded-3 p-3 shadow-2xs">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                                    <label class="form-label fw-semibold text-dark mb-0 d-inline-flex align-items-center gap-1.5">
                                        <i class="bi bi-stopwatch text-success fs-5"></i>
                                        <span>Estimasi Durasi Pengerjaan</span>
                                        <span class="badge bg-secondary-subtle text-secondary border fw-normal" style="font-size: 0.72rem;">Opsional</span>
                                    </label>
                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-success border-dashed py-0 px-2 rounded-pill shadow-none" id="btnHitungOtomatisDurasi" style="font-size: 0.75rem;" title="Hitung ulang otomatis berdasarkan rentang tanggal & jam di atas">
                                            <i class="bi bi-calculator me-1"></i>Hitung Otomatis
                                        </button>
                                        <small class="text-muted fst-italic">Jika dikosongkan/0, akan terhitung otomatis dari tanggal & jam.</small>
                                    </div>
                                </div>
                                <div class="row g-3 align-items-center">
                                    <div class="col-md-6 col-12">
                                        <label for="estimasi_durasi_hari" class="form-label small text-secondary mb-1">Durasi (Hari)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white text-secondary"><i class="bi bi-calendar-event"></i></span>
                                            <input type="number" min="0" step="1" class="form-control @error('estimasi_durasi_hari') is-invalid @enderror" id="estimasi_durasi_hari" name="estimasi_durasi_hari" placeholder="0" value="{{ old('estimasi_durasi_hari') }}">
                                            <span class="input-group-text bg-light text-dark fw-semibold">Hari</span>
                                        </div>
                                        @error('estimasi_durasi_hari')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <label for="estimasi_durasi_jam" class="form-label small text-secondary mb-1">Durasi (Jam)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white text-secondary"><i class="bi bi-clock"></i></span>
                                            <input type="number" min="0" step="1" class="form-control @error('estimasi_durasi_jam') is-invalid @enderror" id="estimasi_durasi_jam" name="estimasi_durasi_jam" placeholder="0" value="{{ old('estimasi_durasi_jam') }}">
                                            <span class="input-group-text bg-light text-dark fw-semibold">Jam</span>
                                        </div>
                                        @error('estimasi_durasi_jam')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-12 pt-1 d-none" id="preview-durasi-wrapper">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="small text-muted">Total target waktu:</span>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1" id="preview-durasi-badge" style="font-size: 0.8rem;"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
                            {{-- <a href="{{ route('kegiatan.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i> Kembali</a> --}}
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

        // Dynamic Sesi Presensi Repeater
        let sesiCount = 0;
        function tambahBarisSesi(nama = '', tgl = '', jenisAbsen = 'datang') {
            sesiCount++;
            const tglVal = tgl || ($('#tanggal_mulai').val() || new Date().toISOString().split('T')[0]);
            const html = `
            <div class="row g-2 align-items-center bg-white p-2 rounded border shadow-2xs baris-sesi-item" id="baris-sesi-${sesiCount}">
                <div class="col-12 col-md-7">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted"><i class="bi bi-tag-fill"></i></span>
                        <input type="text" name="nama_sesi_presensi[${sesiCount - 1}]" class="form-control" placeholder="Nama Sesi Presensi (misal: Absensi Hari Ke-${sesiCount})" value="${nama}" required>
                    </div>
                </div>
                <div class="col-9 col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted"><i class="bi bi-calendar-event"></i></span>
                        <input type="date" name="tanggal_sesi_presensi[${sesiCount - 1}]" class="form-control" value="${tglVal}">
                    </div>
                </div>
                <div class="col-3 col-md-1 text-end">
                    <button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="$('#baris-sesi-${sesiCount}').remove()" title="Hapus Baris Sesi">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <div class="col-12">
                    <div class="d-flex flex-wrap align-items-center gap-3 small">
                        <span class="fw-semibold text-secondary">Jenis absen:</span>
                        <label class="form-check form-check-inline mb-0"><input class="form-check-input" type="radio" name="jenis_absen_presensi[${sesiCount - 1}]" value="datang" ${jenisAbsen === 'datang' ? 'checked' : ''}> Datang</label>
                        <label class="form-check form-check-inline mb-0"><input class="form-check-input" type="radio" name="jenis_absen_presensi[${sesiCount - 1}]" value="pulang" ${jenisAbsen === 'pulang' ? 'checked' : ''}> Pulang</label>
                        <label class="form-check form-check-inline mb-0"><input class="form-check-input" type="radio" name="jenis_absen_presensi[${sesiCount - 1}]" value="rapat" ${jenisAbsen === 'rapat' ? 'checked' : ''}> Rapat (1 kali absen)</label>
                    </div>
                </div>
            </div>`;
            $('#sesiPresensiContainer').append(html);
        }

        $('#btnTambahBarisSesi').on('click', function() {
            tambahBarisSesi();
        });

        let manualDurasiTouched = false;

        $('#estimasi_durasi_hari, #estimasi_durasi_jam').on('input', function() {
            manualDurasiTouched = true;
        });

        function autoCalculateDurasi(force = false) {
            let tglMulai = $('#estimasi_tanggal_mulai').val();
            let tglSelesai = $('#estimasi_tanggal_selesai').val();
            let jamMulai = $('#estimasi_jam_mulai').val();
            let jamSelesai = $('#estimasi_jam_selesai').val();

            if (!tglMulai || !tglSelesai) return;

            let curHari = $('#estimasi_durasi_hari').val();
            let curJam = $('#estimasi_durasi_jam').val();
            let isDurasiEmpty = (!curHari || parseInt(curHari) === 0) && (!curJam || parseInt(curJam) === 0);

            // Jika durasi sudah diisi manual oleh user dan tidak kosong, jangan timpa kecuali force
            if (!force && manualDurasiTouched && !isDurasiEmpty) {
                return;
            }

            let hari = 0;
            let jam = 0;

            if (!jamMulai && !jamSelesai) {
                let d1 = new Date(tglMulai + 'T00:00:00');
                let d2 = new Date(tglSelesai + 'T00:00:00');
                let diffMs = d2.getTime() - d1.getTime();
                if (diffMs < 0) return;
                let diffDays = Math.round(diffMs / (1000 * 60 * 60 * 24));
                hari = (diffDays === 0) ? 1 : diffDays;
                jam = 0;
            } else {
                let jM = jamMulai || '08:00';
                let jS = jamSelesai || jM;
                let d1 = new Date(tglMulai + 'T' + jM);
                let d2 = new Date(tglSelesai + 'T' + jS);
                let diffMs = d2.getTime() - d1.getTime();
                if (diffMs < 0) return;

                let totalMinutes = Math.round(diffMs / 60000);
                hari = Math.floor(totalMinutes / 1440);
                jam = Math.round((totalMinutes % 1440) / 60);
                if (jam >= 24) {
                    hari += 1;
                    jam = 0;
                }
            }

            $('#estimasi_durasi_hari').val(hari);
            $('#estimasi_durasi_jam').val(jam);
            manualDurasiTouched = false;
            updateDurasiPreview();
        }

        $('#estimasi_tanggal_mulai, #estimasi_jam_mulai, #estimasi_tanggal_selesai, #estimasi_jam_selesai').on('change input', function() {
            autoCalculateDurasi();
        });

        $('#btnHitungOtomatisDurasi').on('click', function(e) {
            e.preventDefault();
            autoCalculateDurasi(true);
        });

        // Jika user mengisi durasi secara manual namun tanggal selesai belum diisi
        $('#estimasi_durasi_hari, #estimasi_durasi_jam').on('change', function() {
            let tglMulai = $('#estimasi_tanggal_mulai').val();
            let tglSelesai = $('#estimasi_tanggal_selesai').val();
            let h = parseInt($('#estimasi_durasi_hari').val()) || 0;
            let j = parseInt($('#estimasi_durasi_jam').val()) || 0;

            if (tglMulai && !tglSelesai && (h > 0 || j > 0)) {
                let jamMulai = $('#estimasi_jam_mulai').val() || '08:00';
                let d = new Date(tglMulai + 'T' + jamMulai);
                d.setDate(d.getDate() + h);
                d.setHours(d.getHours() + j);
                let yyyy = d.getFullYear();
                let mm = String(d.getMonth() + 1).padStart(2, '0');
                let dd = String(d.getDate()).padStart(2, '0');
                let hh = String(d.getHours()).padStart(2, '0');
                let min = String(d.getMinutes()).padStart(2, '0');
                $('#estimasi_tanggal_selesai').val(`${yyyy}-${mm}-${dd}`);
                if (!$('#estimasi_jam_selesai').val()) {
                    $('#estimasi_jam_selesai').val(`${hh}:${min}`);
                }
            }
        });

        function updateDurasiPreview() {
            let h = parseInt($('#estimasi_durasi_hari').val()) || 0;
            let j = parseInt($('#estimasi_durasi_jam').val()) || 0;
            let parts = [];
            if (h > 0) parts.push(h + ' hari');
            if (j > 0) parts.push(j + ' jam');

            let $badge = $('#preview-durasi-badge');
            let $wrap = $('#preview-durasi-wrapper');
            if (parts.length > 0) {
                $badge.html('<i class="bi bi-check2-circle me-1"></i>Target Durasi: ' + parts.join(' '));
                if ($wrap.length) $wrap.removeClass('d-none');
                $badge.removeClass('d-none');
            } else {
                if ($wrap.length) $wrap.addClass('d-none');
                $badge.addClass('d-none');
            }
        }

        $('#estimasi_durasi_hari, #estimasi_durasi_jam').on('input change', updateDurasiPreview);
        
        // Cek kalkulasi otomatis saat inisialisasi awal (jika tanggal terisi tapi durasi kosong/0)
        autoCalculateDurasi();
        updateDurasiPreview();
    });
</script>
@endpush
