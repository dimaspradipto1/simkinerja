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

                        @if(auth()->check() && in_array(auth()->user()->roles, ['superadmin', 'admin']))
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

                        <div class="col-md-6">
                            <label for="periode_akademik_id" class="form-label fw-semibold">Periode Akademik <span class="text-danger">*</span></label>
                            <select class="form-select @error('periode_akademik_id') is-invalid @enderror" id="periode_akademik_id" name="periode_akademik_id" required>
                                <option value="" disabled {{ old('periode_akademik_id', $rencanaKerja->periode_akademik_id) ? '' : 'selected' }}>-- Pilih Periode Akademik --</option>
                                @foreach($periodeAkademiks as $pa)
                                    <option value="{{ $pa->id }}" {{ old('periode_akademik_id', $rencanaKerja->periode_akademik_id) == $pa->id ? 'selected' : '' }}>
                                        {{ $pa->nama_periode }}
                                    </option>
                                @endforeach
                            </select>
                            @error('periode_akademik_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
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
                            <label for="estimasi_tanggal_selesai" class="form-label fw-semibold text-secondary">Estimasi Tgl Selesai</label>
                            <input type="date" class="form-control @error('estimasi_tanggal_selesai') is-invalid @enderror" id="estimasi_tanggal_selesai" name="estimasi_tanggal_selesai" value="{{ old('estimasi_tanggal_selesai', $rencanaKerja->estimasi_tanggal_selesai) }}" {{ $estimasiLocked ? 'disabled' : '' }}>
                            @error('estimasi_tanggal_selesai')
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
                            <label for="estimasi_jam_selesai" class="form-label fw-semibold text-secondary">Estimasi Jam Selesai</label>
                            <input type="time" class="form-control @error('estimasi_jam_selesai') is-invalid @enderror" id="estimasi_jam_selesai" name="estimasi_jam_selesai" value="{{ old('estimasi_jam_selesai', $rencanaKerja->estimasi_jam_selesai) }}" {{ $estimasiLocked ? 'disabled' : '' }}>
                            @error('estimasi_jam_selesai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
        $('#tagged_users').select2({
            theme: 'bootstrap-5',
            placeholder: "Cari & pilih rekan kerja...",
            allowClear: true
        });
    });
</script>
@endpush
