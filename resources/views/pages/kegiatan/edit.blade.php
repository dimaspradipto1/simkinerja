@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
    <h1>Edit Kegiatan</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('kegiatan.index') }}">Kegiatan</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title fw-bold">Form Edit Kegiatan</h5>

                    <form action="{{ route('kegiatan.update', $kegiatan->id) }}" method="POST" enctype="multipart/form-data" class="row g-3">
                        @csrf
                        @method('PUT')

                        @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isPimpinanRektorat() || auth()->user()->isPimpinanUnit() || auth()->user()->isSuperAdmin()))
                        <div class="col-md-12">
                            <label for="user_id" class="form-label fw-semibold">Pegawai / User <span class="text-danger">*</span></label>
                            <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id">
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" {{ old('user_id', $kegiatan->user_id) == $u->id ? 'selected' : '' }}>
                                        {{ $u->jabatan ?? '-' }} - {{ $u->name }}
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
                                <option value="" disabled {{ old('periode_akademik_id', $kegiatan->periode_akademik_id) ? '' : 'selected' }}>-- Pilih Periode Akademik --</option>
                                @foreach($periodeAkademiks as $pa)
                                    <option value="{{ $pa->id }}" {{ old('periode_akademik_id', $kegiatan->periode_akademik_id) == $pa->id ? 'selected' : '' }}>
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
                                <option value="" disabled {{ old('kategori', $kegiatan->kategori) ? '' : 'selected' }}>-- Pilih Kategori --</option>
                                @if(isset($kategoris) && $kategoris->count() > 0)
                                    @foreach($kategoris as $k)
                                        <option value="{{ $k->nama_kategori }}" {{ old('kategori', $kegiatan->kategori ?? 'Rencana Kerja') == $k->nama_kategori ? 'selected' : '' }}>
                                            {{ $k->nama_kategori }}
                                        </option>
                                    @endforeach
                                @else
                                    <option value="Rencana Kerja" {{ old('kategori', $kegiatan->kategori ?? 'Rencana Kerja') == 'Rencana Kerja' ? 'selected' : '' }}>Rencana Kerja</option>
                                    <option value="Kepanitiaan" {{ old('kategori', $kegiatan->kategori) == 'Kepanitiaan' ? 'selected' : '' }}>Kepanitiaan</option>
                                    <option value="Insidentil" {{ old('kategori', $kegiatan->kategori) == 'Insidentil' ? 'selected' : '' }}>Insidentil</option>
                                @endif
                            </select>
                            @error('kategori')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="hari" class="form-label fw-semibold">Hari Pelaksanaan</label>
                            <select class="form-select @error('hari') is-invalid @enderror" id="hari" name="hari">
                                <option value="" {{ old('hari', $kegiatan->hari) ? '' : 'selected' }}>-- Pilih Hari (Opsional) --</option>
                                @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $h)
                                    <option value="{{ $h }}" {{ old('hari', $kegiatan->hari) == $h ? 'selected' : '' }}>{{ $h }}</option>
                                @endforeach
                            </select>
                            @error('hari')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label for="uraian_tugas" class="form-label fw-semibold">Uraian Kegiatan <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('uraian_tugas') is-invalid @enderror" id="uraian_tugas" name="uraian_tugas" rows="4" placeholder="Tuliskan detail rencana kegiatan" required>{{ old('uraian_tugas', $kegiatan->uraian_tugas) }}</textarea>
                            @error('uraian_tugas')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label for="tagged_users" class="form-label fw-semibold">Tag Rekan Kerja</label>
                            <select class="form-select @error('tagged_users') is-invalid @enderror" id="tagged_users" name="tagged_users[]" multiple="multiple">
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" {{ in_array($u->id, old('tagged_users', $kegiatan->taggedUsers->pluck('id')->toArray())) ? 'selected' : '' }}>
                                        {{ $u->jabatan ?? '-' }} - {{ $u->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text text-muted small">Pilih satu atau beberapa rekan kerja yang terlibat dalam kegiatan ini.</div>
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
                                    Kegiatan ini sudah tersimpan, sehingga estimasi maupun realisasi tanggal/jam hanya dapat diubah oleh Pimpinan.
                                    Ajukan permintaan perubahan jika perlu direvisi.
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="col-md-3">
                            <label for="estimasi_tanggal_mulai" class="form-label fw-semibold text-secondary">Estimasi Tgl Mulai</label>
                            <input type="date" class="form-control @error('estimasi_tanggal_mulai') is-invalid @enderror" id="estimasi_tanggal_mulai" name="estimasi_tanggal_mulai" value="{{ old('estimasi_tanggal_mulai', $kegiatan->estimasi_tanggal_mulai) }}" {{ $estimasiLocked ? 'disabled' : '' }}>
                        </div>

                        <div class="col-md-3">
                            <label for="estimasi_jam_mulai" class="form-label fw-semibold text-secondary">Estimasi Jam Mulai</label>
                            <input type="time" class="form-control @error('estimasi_jam_mulai') is-invalid @enderror" id="estimasi_jam_mulai" name="estimasi_jam_mulai" value="{{ old('estimasi_jam_mulai', $kegiatan->estimasi_jam_mulai ? substr($kegiatan->estimasi_jam_mulai, 0, 5) : '') }}" {{ $estimasiLocked ? 'disabled' : '' }}>
                        </div>

                        <div class="col-md-3">
                            <label for="estimasi_tanggal_selesai" class="form-label fw-semibold text-secondary">Estimasi Tgl Selesai</label>
                            <input type="date" class="form-control @error('estimasi_tanggal_selesai') is-invalid @enderror" id="estimasi_tanggal_selesai" name="estimasi_tanggal_selesai" value="{{ old('estimasi_tanggal_selesai', $kegiatan->estimasi_tanggal_selesai) }}" {{ $estimasiLocked ? 'disabled' : '' }}>
                        </div>

                        <div class="col-md-3">
                            <label for="estimasi_jam_selesai" class="form-label fw-semibold text-secondary">Estimasi Jam Selesai</label>
                            <input type="time" class="form-control @error('estimasi_jam_selesai') is-invalid @enderror" id="estimasi_jam_selesai" name="estimasi_jam_selesai" value="{{ old('estimasi_jam_selesai', $kegiatan->estimasi_jam_selesai ? substr($kegiatan->estimasi_jam_selesai, 0, 5) : '') }}" {{ $estimasiLocked ? 'disabled' : '' }}>
                        </div>

                        <div class="col-md-3">
                            <label for="tanggal_mulai" class="form-label fw-semibold text-secondary">Tgl Mulai (Realisasi)</label>
                            <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" value="{{ old('tanggal_mulai', $kegiatan->tanggal_mulai) }}" {{ $estimasiLocked ? 'disabled' : '' }}>
                        </div>

                        <div class="col-md-3">
                            <label for="waktu_mulai" class="form-label fw-semibold text-secondary">Jam Mulai (Realisasi)</label>
                            <input type="time" class="form-control" id="waktu_mulai" name="waktu_mulai" value="{{ old('waktu_mulai', $kegiatan->waktu_mulai ? substr($kegiatan->waktu_mulai, 0, 5) : '') }}" {{ $estimasiLocked ? 'disabled' : '' }}>
                        </div>

                        <div class="col-md-3">
                            <label for="tanggal_selesai" class="form-label fw-semibold text-secondary">Tgl Selesai (Realisasi)</label>
                            <input type="date" class="form-control" id="tanggal_selesai" name="tanggal_selesai" value="{{ old('tanggal_selesai', $kegiatan->tanggal_selesai) }}" {{ $estimasiLocked ? 'disabled' : '' }}>
                        </div>

                        <div class="col-md-3">
                            <label for="waktu_selesai" class="form-label fw-semibold text-secondary">Jam Selesai (Realisasi)</label>
                            <input type="time" class="form-control" id="waktu_selesai" name="waktu_selesai" value="{{ old('waktu_selesai', $kegiatan->waktu_selesai ? substr($kegiatan->waktu_selesai, 0, 5) : '') }}" {{ $estimasiLocked ? 'disabled' : '' }}>
                        </div>

                        <div class="col-md-6">
                            <label for="status" class="form-label fw-semibold">Status Pelaksanaan</label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                                @foreach(['Belum Dimulai', 'Berjalan', 'Di-pause', 'Selesai'] as $st)
                                    <option value="{{ $st }}" {{ old('status', $kegiatan->status) == $st ? 'selected' : '' }}>{{ $st }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="file" class="form-label fw-semibold">Unggah Berkas Baru</label>
                            <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file">
                            @if($kegiatan->file)
                                <div class="form-text small"><a href="{{ asset('storage/' . $kegiatan->file) }}" target="_blank" class="text-primary"><i class="bi bi-file-earmark-arrow-down me-1"></i>Lihat Berkas Saat Ini</a></div>
                            @endif
                        </div>

                        <div class="col-md-12">
                            <label for="url_external" class="form-label fw-semibold">Tautan Eksternal / Link Luar</label>
                            <input type="url" class="form-control @error('url_external') is-invalid @enderror" id="url_external" name="url_external" value="{{ old('url_external', $kegiatan->url_external) }}" placeholder="https://drive.google.com/...">
                        </div>

                        <div class="col-md-12">
                            <label for="hasil_kerja" class="form-label fw-semibold">Hasil Kegiatan</label>
                            <textarea class="form-control @error('hasil_kerja') is-invalid @enderror" id="hasil_kerja" name="hasil_kerja" rows="3" placeholder="Ringkasan hasil pelaksanaan kegiatan">{{ old('hasil_kerja', $kegiatan->hasil_kerja) }}</textarea>
                        </div>

                        <div class="col-md-12">
                            <label for="rencana_tindak_lanjut" class="form-label fw-semibold">Rencana Tindak Lanjut</label>
                            <textarea class="form-control @error('rencana_tindak_lanjut') is-invalid @enderror" id="rencana_tindak_lanjut" name="rencana_tindak_lanjut" rows="2" placeholder="Tindak lanjut yang direncanakan">{{ old('rencana_tindak_lanjut', $kegiatan->rencana_tindak_lanjut) }}</textarea>
                        </div>

                        <div class="col-12 mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Perbarui Kegiatan</button>
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
                placeholder: "-- Pilih Pegawai / User --",
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
    });
</script>
@endpush
