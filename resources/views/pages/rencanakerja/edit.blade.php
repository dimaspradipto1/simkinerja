@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
    <h1>Edit Rencana Kerja</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('rencana-kerja.index') }}">Rencana Kerja</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Form Edit Rencana Kerja</h5>

                    <form action="{{ route('rencana-kerja.update', $rencanaKerja->id) }}" method="POST" enctype="multipart/form-data" class="row g-3">
                        @csrf
                        @method('PUT')

                        @if(auth()->check() && auth()->user()->isSystemAdmin())
                        <div class="col-md-12">
                            <label for="user_id" class="form-label">Pegawai / User <span class="text-danger">*</span></label>
                            <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id">
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" {{ old('user_id', $rencanaKerja->user_id) == $u->id ? 'selected' : '' }}>
                                        {{ $u->name }} ({{ $u->unit ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        @endif

                        <div class="col-md-4">
                            <label for="periode_akademik_id" class="form-label fw-semibold">Periode Akademik <span class="text-danger">*</span></label>
                            <select class="form-select @error('periode_akademik_id') is-invalid @enderror" id="periode_akademik_id" name="periode_akademik_id" required>
                                <option value="" disabled {{ old('periode_akademik_id', $rencanaKerja->periode_akademik_id) ? '' : 'selected' }}>-- Pilih Periode Akademik --</option>
                                @foreach($periodeAkademiks as $pa)
                                    <option value="{{ $pa->id }}" {{ old('periode_akademik_id', $rencanaKerja->periode_akademik_id) == $pa->id ? 'selected' : '' }}>
                                        {{ $pa->formatted_ta ?? $pa->nama_periode }}
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
                                <option value="" disabled {{ old('kategori', $rencanaKerja->kategori) ? '' : 'selected' }}>-- Pilih Kategori --</option>
                                @if(isset($kategoris) && $kategoris->count() > 0)
                                    @foreach($kategoris as $k)
                                        <option value="{{ $k->nama_kategori }}" {{ old('kategori', $rencanaKerja->kategori ?? 'Rencana Kerja') == $k->nama_kategori ? 'selected' : '' }}>
                                            {{ $k->nama_kategori }}
                                        </option>
                                    @endforeach
                                @else
                                    <option value="Rencana Kerja" {{ old('kategori', $rencanaKerja->kategori ?? 'Rencana Kerja') == 'Rencana Kerja' ? 'selected' : '' }}>Rencana Kerja</option>
                                    <option value="Kepanitiaan" {{ old('kategori', $rencanaKerja->kategori) == 'Kepanitiaan' ? 'selected' : '' }}>Kepanitiaan</option>
                                    <option value="Insidentil" {{ old('kategori', $rencanaKerja->kategori) == 'Insidentil' ? 'selected' : '' }}>Insidentil</option>
                                @endif
                            </select>
                            @error('kategori')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="hari" class="form-label fw-semibold">Hari Pelaksanaan</label>
                            <select class="form-select @error('hari') is-invalid @enderror" id="hari" name="hari">
                                <option value="" {{ old('hari', $rencanaKerja->hari) ? '' : 'selected' }}>-- Pilih Hari (Opsional) --</option>
                                @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $h)
                                    <option value="{{ $h }}" {{ old('hari', $rencanaKerja->hari) == $h ? 'selected' : '' }}>{{ $h }}</option>
                                @endforeach
                            </select>
                            @error('hari')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>



                        <div class="col-md-12">
                            <label for="uraian_tugas" class="form-label fw-semibold">Uraian Tugas <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('uraian_tugas') is-invalid @enderror" id="uraian_tugas" name="uraian_tugas" rows="4" placeholder="Tuliskan detail rencana/uraian tugas" required>{{ old('uraian_tugas', $rencanaKerja->uraian_tugas) }}</textarea>
                            @error('uraian_tugas')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if($rencanaKerja->subKegiatans && $rencanaKerja->subKegiatans->count() > 0)
                        <div class="col-12">
                            <div class="card border rounded-3 p-3 shadow-2xs bg-white">
                                <label class="form-label fw-bold text-dark mb-2 d-flex align-items-center gap-2">
                                    <i class="bi bi-diagram-3 text-success fs-5"></i>
                                    <span>Daftar Sub Kegiatan Terdaftar ({{ $rencanaKerja->subKegiatans->count() }})</span>
                                </label>
                                <div class="d-flex flex-column gap-1.5 ms-2 ps-2 border-start border-2 border-success-subtle">
                                    @foreach($rencanaKerja->subKegiatans as $sub)
                                        <div class="d-flex align-items-center justify-content-between gap-2 p-2 bg-light rounded border small">
                                            <div>
                                                <i class="bi bi-arrow-return-right text-success me-1"></i>
                                                <strong class="text-dark">{{ $sub->uraian_tugas }}</strong>
                                                <span class="badge bg-secondary-subtle text-secondary ms-1">{{ $sub->status }}</span>
                                            </div>
                                            <a href="{{ route('rencana-kerja.edit', $sub->id) }}" class="btn btn-xs btn-sm btn-outline-secondary py-0.5 px-2" style="font-size: 0.75rem;" target="_blank"><i class="bi bi-pencil"></i> Edit Sub</a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="col-md-12">
                            <label for="tagged_users" class="form-label fw-semibold">Tag User Lain (Rekan Kerja)</label>
                            <select class="form-select @error('tagged_users') is-invalid @enderror" id="tagged_users" name="tagged_users[]" multiple="multiple">
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" {{ in_array($u->id, old('tagged_users', $rencanaKerja->taggedUsers->pluck('id')->toArray())) ? 'selected' : '' }}>
                                        {{ $u->jabatan ?? '-' }} - {{ $u->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text text-muted small">Pilih satu atau beberapa rekan kerja yang mengerjakan pekerjaan yang sama.</div>
                            @error('tagged_users')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if($estimasiLocked)
                        <div class="col-md-12">
                            <div class="alert alert-secondary d-flex align-items-start gap-2 mb-0 py-2">
                                <i class="bi bi-lock-fill mt-1"></i>
                                <div>
                                    <strong>Tanggal &amp; Waktu Pelaksanaan terkunci.</strong>
                                    Tugas ini sudah tersimpan, sehingga estimasi maupun realisasi tanggal/jam hanya dapat diubah oleh Pimpinan.
                                    Ajukan permintaan perubahan jika perlu direvisi.
                                </div>
                            </div>
                        </div>
                        @elseif($rencanaKerja->estimasi_unlock_requested_at)
                        <div class="col-md-12">
                            <div class="alert alert-warning mb-0 py-2">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                <strong>Ada permintaan perubahan tanggal &amp; waktu</strong> dari {{ $rencanaKerja->user->name ?? 'staff' }}
                                pada {{ \Carbon\Carbon::parse($rencanaKerja->estimasi_unlock_requested_at)->translatedFormat('d M Y H:i') }}:
                                <div class="fst-italic mt-1">"{{ $rencanaKerja->estimasi_unlock_reason }}"</div>
                                <div class="small text-muted mt-1">Ubah tanggal/waktu di bawah ini lalu simpan untuk menyelesaikan permintaan.</div>
                            </div>
                        </div>
                        @endif

                        <div class="col-md-3">
                            <label for="estimasi_tanggal_mulai" class="form-label fw-semibold text-secondary">Estimasi Tgl Mulai</label>
                            <input type="date" class="form-control @error('estimasi_tanggal_mulai') is-invalid @enderror" id="estimasi_tanggal_mulai" name="estimasi_tanggal_mulai" value="{{ old('estimasi_tanggal_mulai', $rencanaKerja->estimasi_tanggal_mulai) }}" {{ $estimasiLocked ? 'disabled' : '' }}>
                            @error('estimasi_tanggal_mulai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="estimasi_jam_mulai" class="form-label fw-semibold text-secondary">Estimasi Jam Mulai</label>
                            <input type="time" class="form-control @error('estimasi_jam_mulai') is-invalid @enderror" id="estimasi_jam_mulai" name="estimasi_jam_mulai" value="{{ old('estimasi_jam_mulai', $rencanaKerja->estimasi_jam_mulai) }}" {{ $estimasiLocked ? 'disabled' : '' }}>
                            @error('estimasi_jam_mulai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="estimasi_tanggal_selesai" class="form-label fw-semibold text-secondary">Estimasi Tgl Selesai</label>
                            <input type="date" class="form-control @error('estimasi_tanggal_selesai') is-invalid @enderror" id="estimasi_tanggal_selesai" name="estimasi_tanggal_selesai" value="{{ old('estimasi_tanggal_selesai', $rencanaKerja->estimasi_tanggal_selesai) }}" {{ $estimasiLocked ? 'disabled' : '' }}>
                            @error('estimasi_tanggal_selesai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="estimasi_jam_selesai" class="form-label fw-semibold text-secondary">Estimasi Jam Selesai</label>
                            <input type="time" class="form-control @error('estimasi_jam_selesai') is-invalid @enderror" id="estimasi_jam_selesai" name="estimasi_jam_selesai" value="{{ old('estimasi_jam_selesai', $rencanaKerja->estimasi_jam_selesai) }}" {{ $estimasiLocked ? 'disabled' : '' }}>
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
                                        @if(!$estimasiLocked)
                                            <button type="button" class="btn btn-sm btn-outline-success border-dashed py-0 px-2 rounded-pill shadow-none" id="btnHitungOtomatisDurasi" style="font-size: 0.75rem;" title="Hitung ulang otomatis berdasarkan rentang tanggal & jam di atas">
                                                <i class="bi bi-calculator me-1"></i>Hitung Otomatis
                                            </button>
                                        @endif
                                        <small class="text-muted fst-italic">Jika dikosongkan/0, akan terhitung otomatis dari tanggal & jam.</small>
                                    </div>
                                </div>
                                <div class="row g-3 align-items-center">
                                    <div class="col-md-6 col-12">
                                        <label for="estimasi_durasi_hari" class="form-label small text-secondary mb-1">Durasi (Hari)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white text-secondary"><i class="bi bi-calendar-event"></i></span>
                                            <input type="number" min="0" step="1" class="form-control @error('estimasi_durasi_hari') is-invalid @enderror" id="estimasi_durasi_hari" name="estimasi_durasi_hari" placeholder="0" value="{{ old('estimasi_durasi_hari', $rencanaKerja->estimasi_durasi_hari) }}" {{ $estimasiLocked ? 'disabled' : '' }}>
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
                                            <input type="number" min="0" step="1" class="form-control @error('estimasi_durasi_jam') is-invalid @enderror" id="estimasi_durasi_jam" name="estimasi_durasi_jam" placeholder="0" value="{{ old('estimasi_durasi_jam', $rencanaKerja->estimasi_durasi_jam) }}" {{ $estimasiLocked ? 'disabled' : '' }}>
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

                        @if(!$estimasiLocked)
                        <div class="col-md-3">
                            <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                            <input type="date" class="form-control @error('tanggal_mulai') is-invalid @enderror" id="tanggal_mulai" name="tanggal_mulai" value="{{ old('tanggal_mulai', $rencanaKerja->tanggal_mulai) }}">
                            @error('tanggal_mulai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="tanggal_selesai" class="form-label">Tanggal Selesai</label>
                            <input type="date" class="form-control @error('tanggal_selesai') is-invalid @enderror" id="tanggal_selesai" name="tanggal_selesai" value="{{ old('tanggal_selesai', $rencanaKerja->tanggal_selesai) }}">
                            @error('tanggal_selesai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="waktu_mulai" class="form-label">Waktu Mulai</label>
                            <input type="time" class="form-control @error('waktu_mulai') is-invalid @enderror" id="waktu_mulai" name="waktu_mulai" value="{{ old('waktu_mulai', $rencanaKerja->waktu_mulai) }}">
                            @error('waktu_mulai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="waktu_selesai" class="form-label">Waktu Selesai</label>
                            <input type="time" class="form-control @error('waktu_selesai') is-invalid @enderror" id="waktu_selesai" name="waktu_selesai" value="{{ old('waktu_selesai', $rencanaKerja->waktu_selesai) }}">
                            @error('waktu_selesai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        @endif

                        <div class="col-md-6">
                            <label for="file" class="form-label">Unggah Berkas Baru <small class="text-muted">(Biarkan kosong jika tidak diubah)</small></label>
                            <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file">
                            @if($rencanaKerja->file)
                                <div class="form-text mt-1">
                                    <i class="bi bi-file-earmark-check text-success me-1"></i> Berkas terpasang: 
                                    <a href="{{ asset('storage/' . $rencanaKerja->file) }}" target="_blank">Lihat Berkas</a>
                                </div>
                            @endif
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="url_external" class="form-label">Link Eksternal</label>
                            <input type="url" class="form-control @error('url_external') is-invalid @enderror" id="url_external" name="url_external" value="{{ old('url_external', $rencanaKerja->url_external) }}" placeholder="https://">
                            @error('url_external')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if($rencanaKerja->status === 'Di-pause' || $rencanaKerja->alasan_pause)
                        <div class="col-md-12">
                            <label for="alasan_pause" class="form-label fw-semibold text-warning-emphasis">
                                <i class="bi bi-pause-circle-fill text-warning me-1"></i> Penyebab Di-pause
                            </label>
                            <textarea class="form-control border-warning @error('alasan_pause') is-invalid @enderror" id="alasan_pause" name="alasan_pause" rows="3" placeholder="Penyebab atau kendala pekerjaan di-pause...">{{ old('alasan_pause', $rencanaKerja->alasan_pause) }}</textarea>
                            @error('alasan_pause')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        @endif

                        <div class="col-12 mt-4 d-flex flex-wrap align-items-center gap-2">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Perbarui Rencana Kerja</button>
                            <a href="{{ route('rencana-kerja.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
                            @if($estimasiLocked)
                                @if($rencanaKerja->estimasi_unlock_requested_at)
                                    <span class="badge text-bg-info fw-normal py-2 px-3">
                                        <i class="bi bi-hourglass-split me-1"></i>
                                        Menunggu persetujuan Pimpinan (diajukan {{ \Carbon\Carbon::parse($rencanaKerja->estimasi_unlock_requested_at)->translatedFormat('d M Y H:i') }})
                                    </span>
                                @else
                                    <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalRequestUnlockEstimasi">
                                        <i class="bi bi-send-fill me-1"></i> Ajukan Perubahan Tanggal &amp; Waktu ke Pimpinan
                                    </button>
                                @endif
                            @endif
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</section>

@if($estimasiLocked && !$rencanaKerja->estimasi_unlock_requested_at)
<!-- Modal Ajukan Perubahan Estimasi -->
<div class="modal fade" id="modalRequestUnlockEstimasi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('rencana-kerja.request-unlock-estimasi', $rencanaKerja->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-send-fill me-1"></i> Ajukan Perubahan Tanggal &amp; Waktu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">Jelaskan alasan mengapa tanggal/waktu (estimasi maupun realisasi) tugas ini perlu diubah. Permintaan akan dikirim ke Pimpinan untuk ditindaklanjuti.</p>
                    <label for="estimasi_unlock_reason" class="form-label fw-semibold">Alasan Perubahan <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="estimasi_unlock_reason" name="estimasi_unlock_reason" rows="4" maxlength="1000" required placeholder="Contoh: Jadwal mundur karena menunggu data dari unit lain"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning"><i class="bi bi-send-fill me-1"></i> Kirim Permintaan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        if ($('#user_id').length) {
            $('#user_id').select2({
                theme: 'bootstrap-5',
                placeholder: "-- Pilih Pegawai / User --",
                allowClear: true,
                width: '100%'
            });
        }
        $('#tagged_users').select2({
            theme: 'bootstrap-5',
            placeholder: "Cari & pilih rekan kerja...",
            allowClear: true
        });


        let manualDurasiTouched = false;

        $('#estimasi_durasi_hari, #estimasi_durasi_jam').on('input', function() {
            manualDurasiTouched = true;
        });

        function autoCalculateDurasi(force = false) {
            if ($('#estimasi_durasi_hari').prop('disabled')) return;

            let tglMulai = $('#estimasi_tanggal_mulai').val();
            let tglSelesai = $('#estimasi_tanggal_selesai').val();
            let jamMulai = $('#estimasi_jam_mulai').val();
            let jamSelesai = $('#estimasi_jam_selesai').val();

            if (!tglMulai || !tglSelesai) return;

            let curHari = $('#estimasi_durasi_hari').val();
            let curJam = $('#estimasi_durasi_jam').val();
            let isDurasiEmpty = (!curHari || parseInt(curHari) === 0) && (!curJam || parseInt(curJam) === 0);

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
        autoCalculateDurasi();
        updateDurasiPreview();
    });
</script>
@endpush
