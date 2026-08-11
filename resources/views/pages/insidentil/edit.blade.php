@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
    <h1>Edit Rencana Kerja Insidentil</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('insidentil.index') }}">Rencana Kerja Insidentil</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title fw-bold">Form Edit Rencana Kerja Insidentil</h5>

                    <form action="{{ route('insidentil.update', $insidentil->id) }}" method="POST" enctype="multipart/form-data" class="row g-3">
                        @csrf
                        @method('PUT')

                        @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isPimpinanRektorat()))
                        <div class="col-md-12">
                            <label for="user_id" class="form-label fw-semibold">Jabatan - Nama Pegawai <span class="text-danger">*</span></label>
                            <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id">
                                <option value="" disabled>-- Pilih Jabatan - Nama --</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" {{ old('user_id', $insidentil->user_id) == $u->id ? 'selected' : '' }}>
                                        {{ $u->jabatan ?? '-' }} - {{ $u->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        @else
                        <input type="hidden" name="user_id" value="{{ $insidentil->user_id }}">
                        @endif

                        <div class="col-md-6">
                            <label for="periode_akademik_id" class="form-label fw-semibold">Periode Akademik <span class="text-danger">*</span></label>
                            <select class="form-select @error('periode_akademik_id') is-invalid @enderror" id="periode_akademik_id" name="periode_akademik_id" required>
                                <option value="" disabled>-- Pilih Periode Akademik --</option>
                                @foreach($periodeAkademiks as $pa)
                                    <option value="{{ $pa->id }}" {{ old('periode_akademik_id', $insidentil->periode_akademik_id) == $pa->id ? 'selected' : '' }}>
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
                                <option value="">-- Pilih Hari --</option>
                                @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $h)
                                    <option value="{{ $h }}" {{ old('hari', $insidentil->hari) == $h ? 'selected' : '' }}>{{ $h }}</option>
                                @endforeach
                            </select>
                            @error('hari')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label for="uraian_tugas" class="form-label fw-semibold">Uraian Tugas Insidentil <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('uraian_tugas') is-invalid @enderror" id="uraian_tugas" name="uraian_tugas" rows="4" required>{{ old('uraian_tugas', $insidentil->uraian_tugas) }}</textarea>
                            @error('uraian_tugas')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label for="tagged_users" class="form-label fw-semibold">Tag User Lain (Rekan Kerja)</label>
                            @php
                                $taggedIds = old('tagged_users', $insidentil->taggedUsers->pluck('id')->toArray());
                            @endphp
                            <select class="form-select @error('tagged_users') is-invalid @enderror" id="tagged_users" name="tagged_users[]" multiple="multiple">
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" {{ in_array($u->id, $taggedIds) ? 'selected' : '' }}>
                                        {{ $u->jabatan ?? '-' }} - {{ $u->name }}
                                    </option>
                                @endforeach
                            </select>
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
                        @elseif($insidentil->estimasi_unlock_requested_at)
                        <div class="col-md-12">
                            <div class="alert alert-warning mb-0 py-2">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                <strong>Ada permintaan perubahan tanggal &amp; waktu</strong> dari {{ $insidentil->user->name ?? 'staff' }}
                                pada {{ \Carbon\Carbon::parse($insidentil->estimasi_unlock_requested_at)->translatedFormat('d M Y H:i') }}:
                                <div class="fst-italic mt-1">"{{ $insidentil->estimasi_unlock_reason }}"</div>
                                <div class="small text-muted mt-1">Ubah tanggal/waktu di bawah ini lalu simpan untuk menyelesaikan permintaan.</div>
                            </div>
                        </div>
                        @endif

                        <div class="col-md-3">
                            <label for="estimasi_tanggal_mulai" class="form-label fw-semibold text-secondary">Estimasi Tgl Mulai</label>
                            <input type="date" class="form-control @error('estimasi_tanggal_mulai') is-invalid @enderror" id="estimasi_tanggal_mulai" name="estimasi_tanggal_mulai" value="{{ old('estimasi_tanggal_mulai', $insidentil->estimasi_tanggal_mulai) }}" {{ $estimasiLocked ? 'disabled' : '' }}>
                            @error('estimasi_tanggal_mulai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="estimasi_tanggal_selesai" class="form-label fw-semibold text-secondary">Estimasi Tgl Selesai</label>
                            <input type="date" class="form-control @error('estimasi_tanggal_selesai') is-invalid @enderror" id="estimasi_tanggal_selesai" name="estimasi_tanggal_selesai" value="{{ old('estimasi_tanggal_selesai', $insidentil->estimasi_tanggal_selesai) }}" {{ $estimasiLocked ? 'disabled' : '' }}>
                            @error('estimasi_tanggal_selesai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="estimasi_jam_mulai" class="form-label fw-semibold text-secondary">Estimasi Jam Mulai</label>
                            <input type="time" class="form-control @error('estimasi_jam_mulai') is-invalid @enderror" id="estimasi_jam_mulai" name="estimasi_jam_mulai" value="{{ old('estimasi_jam_mulai', $insidentil->estimasi_jam_mulai) }}" {{ $estimasiLocked ? 'disabled' : '' }}>
                            @error('estimasi_jam_mulai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="estimasi_jam_selesai" class="form-label fw-semibold text-secondary">Estimasi Jam Selesai</label>
                            <input type="time" class="form-control @error('estimasi_jam_selesai') is-invalid @enderror" id="estimasi_jam_selesai" name="estimasi_jam_selesai" value="{{ old('estimasi_jam_selesai', $insidentil->estimasi_jam_selesai) }}" {{ $estimasiLocked ? 'disabled' : '' }}>
                            @error('estimasi_jam_selesai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="file" class="form-label fw-semibold">Ganti Lampiran (Berkas/Dokumen)</label>
                            <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file">
                            @if($insidentil->file)
                                <div class="form-text mt-1">Berkas saat ini: <a href="{{ asset('storage/' . $insidentil->file) }}" target="_blank">Lihat Lampiran</a></div>
                            @endif
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="url_external" class="form-label fw-semibold">Tautan Luar / URL External</label>
                            <input type="url" class="form-control @error('url_external') is-invalid @enderror" id="url_external" name="url_external" value="{{ old('url_external', $insidentil->url_external) }}">
                            @error('url_external')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 mt-4 d-flex flex-wrap align-items-center justify-content-end gap-2">
                            @if($estimasiLocked)
                                @if($insidentil->estimasi_unlock_requested_at)
                                    <span class="badge text-bg-info fw-normal py-2 px-3">
                                        <i class="bi bi-hourglass-split me-1"></i>
                                        Menunggu persetujuan Pimpinan (diajukan {{ \Carbon\Carbon::parse($insidentil->estimasi_unlock_requested_at)->translatedFormat('d M Y H:i') }})
                                    </span>
                                @else
                                    <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalRequestUnlockEstimasi">
                                        <i class="bi bi-send-fill me-1"></i> Ajukan Perubahan Tanggal &amp; Waktu ke Pimpinan
                                    </button>
                                @endif
                            @endif
                            <a href="{{ route('insidentil.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i> Batal</a>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Update Data</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</section>

@if($estimasiLocked && !$insidentil->estimasi_unlock_requested_at)
<!-- Modal Ajukan Perubahan Estimasi -->
<div class="modal fade" id="modalRequestUnlockEstimasi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('insidentil.request-unlock-estimasi', $insidentil->id) }}" method="POST">
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

@push('scripts')
<script>
    $(document).ready(function() {
        if ($.fn.select2) {
            $('#tagged_users').select2({
                placeholder: "-- Pilih Rekan Kerja --",
                allowClear: true,
                width: '100%'
            });
        }
    });
</script>
@endpush
@endsection
