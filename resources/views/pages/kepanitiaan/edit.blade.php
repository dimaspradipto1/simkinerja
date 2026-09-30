@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
    <h1>Edit Rencana Kerja Kepanitiaan</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('kepanitiaan.index') }}">Rencana Kerja Kepanitiaan</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Form Edit Rencana Kerja Kepanitiaan</h5>

                    <form action="{{ route('kepanitiaan.update', $kepanitiaan->id) }}" method="POST" enctype="multipart/form-data" class="row g-3">
                        @csrf
                        @method('PUT')

                        @if(auth()->check() && auth()->user()->isSystemAdmin())
                        <div class="col-md-12">
                            <label for="user_id" class="form-label">Pegawai / User <span class="text-danger">*</span></label>
                            <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id">
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" {{ old('user_id', $kepanitiaan->user_id) == $u->id ? 'selected' : '' }}>
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
                                <option value="" disabled {{ old('periode_akademik_id', $kepanitiaan->periode_akademik_id) ? '' : 'selected' }}>-- Pilih Periode Akademik --</option>
                                @foreach($periodeAkademiks as $pa)
                                    <option value="{{ $pa->id }}" {{ old('periode_akademik_id', $kepanitiaan->periode_akademik_id) == $pa->id ? 'selected' : '' }}>
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
                                <option value="" {{ old('hari', $kepanitiaan->hari) ? '' : 'selected' }}>-- Pilih Hari (Opsional) --</option>
                                @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $h)
                                    <option value="{{ $h }}" {{ old('hari', $kepanitiaan->hari) == $h ? 'selected' : '' }}>{{ $h }}</option>
                                @endforeach
                            </select>
                            @error('hari')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Sesi Presensi Kepanitiaan (Dinamis & Fleksibel) -->
                        <div class="col-md-12">
                            <div class="p-3 bg-light rounded-3 border border-primary-subtle shadow-2xs">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                                    <div>
                                        <label class="form-label fw-bold text-primary d-flex align-items-center gap-2 mb-0">
                                            <i class="bi bi-qr-code-scan text-primary fs-5"></i> Sesi Presensi Kepanitiaan
                                        </label>
                                        <div class="text-muted small">Kelola atau tambah sesi absensi baru untuk kegiatan kepanitiaan ini.</div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-sm btn-dark rounded-pill px-3 fw-semibold" onclick="window.openManageSesiModal({{ $kepanitiaan->id }}, '{{ addslashes(e($kepanitiaan->uraian_tugas)) }}')">
                                            <i class="bi bi-gear-fill me-1"></i> Buka Kelola Presensi
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold" id="btnTambahBarisSesiEdit">
                                            <i class="bi bi-plus-circle me-1"></i> Tambah Sesi
                                        </button>
                                    </div>
                                </div>

                                @if($kepanitiaan->sesiAbsensis && $kepanitiaan->sesiAbsensis->count() > 0)
                                    <div class="table-responsive bg-white rounded border mb-2">
                                        <table class="table table-sm table-hover align-middle mb-0" style="font-size: 0.83rem;">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Nama Sesi</th>
                                                    <th class="text-center">Tanggal</th>
                                                    <th class="text-center">Kehadiran</th>
                                                    <th class="text-center">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($kepanitiaan->sesiAbsensis as $s)
                                                    <tr>
                                                        <td class="fw-semibold text-dark">{{ $s->nama_sesi }}</td>
                                                        <td class="text-center">{{ $s->tanggal ? date('d/m/Y', strtotime($s->tanggal)) : '-' }}</td>
                                                        <td class="text-center"><span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $s->presensis->count() }} Hadir</span></td>
                                                        <td class="text-center">
                                                            <span class="badge {{ $s->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $s->is_active ? 'Aktif' : 'Tutup' }}</span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif

                                <div id="sesiPresensiContainerEdit" class="d-flex flex-column gap-2 mt-2">
                                    <!-- Baris sesi baru ditambahkan di sini -->
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label for="uraian_tugas" class="form-label fw-semibold">Uraian Tugas <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('uraian_tugas') is-invalid @enderror" id="uraian_tugas" name="uraian_tugas" rows="4" placeholder="Tuliskan detail rencana/uraian tugas" required>{{ old('uraian_tugas', $kepanitiaan->uraian_tugas) }}</textarea>
                            @error('uraian_tugas')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label for="tagged_users" class="form-label fw-semibold">Tag User Lain (Rekan Kerja)</label>
                            <select class="form-select @error('tagged_users') is-invalid @enderror" id="tagged_users" name="tagged_users[]" multiple="multiple">
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" {{ in_array($u->id, old('tagged_users', $kepanitiaan->taggedUsers->pluck('id')->toArray())) ? 'selected' : '' }}>
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
                        @elseif($kepanitiaan->estimasi_unlock_requested_at)
                        <div class="col-md-12">
                            <div class="alert alert-warning mb-0 py-2">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                <strong>Ada permintaan perubahan tanggal &amp; waktu</strong> dari {{ $kepanitiaan->user->name ?? 'staff' }}
                                pada {{ \Carbon\Carbon::parse($kepanitiaan->estimasi_unlock_requested_at)->translatedFormat('d M Y H:i') }}:
                                <div class="fst-italic mt-1">"{{ $kepanitiaan->estimasi_unlock_reason }}"</div>
                                <div class="small text-muted mt-1">Ubah tanggal/waktu di bawah ini lalu simpan untuk menyelesaikan permintaan.</div>
                            </div>
                        </div>
                        @endif

                        <div class="col-md-3">
                            <label for="estimasi_tanggal_mulai" class="form-label fw-semibold text-secondary">Estimasi Tgl Mulai</label>
                            <input type="date" class="form-control @error('estimasi_tanggal_mulai') is-invalid @enderror" id="estimasi_tanggal_mulai" name="estimasi_tanggal_mulai" value="{{ old('estimasi_tanggal_mulai', $kepanitiaan->estimasi_tanggal_mulai) }}" {{ $estimasiLocked ? 'disabled' : '' }}>
                            @error('estimasi_tanggal_mulai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="estimasi_tanggal_selesai" class="form-label fw-semibold text-secondary">Estimasi Tgl Selesai</label>
                            <input type="date" class="form-control @error('estimasi_tanggal_selesai') is-invalid @enderror" id="estimasi_tanggal_selesai" name="estimasi_tanggal_selesai" value="{{ old('estimasi_tanggal_selesai', $kepanitiaan->estimasi_tanggal_selesai) }}" {{ $estimasiLocked ? 'disabled' : '' }}>
                            @error('estimasi_tanggal_selesai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="estimasi_jam_mulai" class="form-label fw-semibold text-secondary">Estimasi Jam Mulai</label>
                            <input type="time" class="form-control @error('estimasi_jam_mulai') is-invalid @enderror" id="estimasi_jam_mulai" name="estimasi_jam_mulai" value="{{ old('estimasi_jam_mulai', $kepanitiaan->estimasi_jam_mulai) }}" {{ $estimasiLocked ? 'disabled' : '' }}>
                            @error('estimasi_jam_mulai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="estimasi_jam_selesai" class="form-label fw-semibold text-secondary">Estimasi Jam Selesai</label>
                            <input type="time" class="form-control @error('estimasi_jam_selesai') is-invalid @enderror" id="estimasi_jam_selesai" name="estimasi_jam_selesai" value="{{ old('estimasi_jam_selesai', $kepanitiaan->estimasi_jam_selesai) }}" {{ $estimasiLocked ? 'disabled' : '' }}>
                            @error('estimasi_jam_selesai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if(!$estimasiLocked)
                        <div class="col-md-3">
                            <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                            <input type="date" class="form-control @error('tanggal_mulai') is-invalid @enderror" id="tanggal_mulai" name="tanggal_mulai" value="{{ old('tanggal_mulai', $kepanitiaan->tanggal_mulai) }}">
                            @error('tanggal_mulai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="tanggal_selesai" class="form-label">Tanggal Selesai</label>
                            <input type="date" class="form-control @error('tanggal_selesai') is-invalid @enderror" id="tanggal_selesai" name="tanggal_selesai" value="{{ old('tanggal_selesai', $kepanitiaan->tanggal_selesai) }}">
                            @error('tanggal_selesai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="waktu_mulai" class="form-label">Waktu Mulai</label>
                            <input type="time" class="form-control @error('waktu_mulai') is-invalid @enderror" id="waktu_mulai" name="waktu_mulai" value="{{ old('waktu_mulai', $kepanitiaan->waktu_mulai) }}">
                            @error('waktu_mulai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="waktu_selesai" class="form-label">Waktu Selesai</label>
                            <input type="time" class="form-control @error('waktu_selesai') is-invalid @enderror" id="waktu_selesai" name="waktu_selesai" value="{{ old('waktu_selesai', $kepanitiaan->waktu_selesai) }}">
                            @error('waktu_selesai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        @endif

                        <div class="col-md-6">
                            <label for="file" class="form-label">Unggah Berkas Baru <small class="text-muted">(Biarkan kosong jika tidak diubah)</small></label>
                            <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file">
                            @if($kepanitiaan->file)
                                <div class="form-text mt-1">
                                    <i class="bi bi-file-earmark-check text-success me-1"></i> Berkas terpasang: 
                                    <a href="{{ asset('storage/' . $kepanitiaan->file) }}" target="_blank">Lihat Berkas</a>
                                </div>
                            @endif
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="url_external" class="form-label">Link Eksternal</label>
                            @php
                                $externalOnly = '';
                                if (!empty($kepanitiaan->url_external)) {
                                    $parts = explode(',', $kepanitiaan->url_external);
                                    $nonAbsensi = array_filter($parts, function($p) {
                                        foreach (\App\Models\AbsensiSetting::defaults() as $s => $c) {
                                            if (str_contains($p, $s)) return false;
                                        }
                                        return true;
                                    });
                                    $externalOnly = implode(',', $nonAbsensi);
                                }
                            @endphp
                            <input type="url" class="form-control @error('url_external') is-invalid @enderror" id="url_external" name="url_external" value="{{ old('url_external', $externalOnly) }}" placeholder="https://">
                            @error('url_external')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if($kepanitiaan->status === 'Di-pause' || $kepanitiaan->alasan_pause)
                        <div class="col-md-12">
                            <label for="alasan_pause" class="form-label fw-semibold text-warning-emphasis">
                                <i class="bi bi-pause-circle-fill text-warning me-1"></i> Penyebab Di-pause
                            </label>
                            <textarea class="form-control border-warning @error('alasan_pause') is-invalid @enderror" id="alasan_pause" name="alasan_pause" rows="3" placeholder="Penyebab atau kendala pekerjaan di-pause...">{{ old('alasan_pause', $kepanitiaan->alasan_pause) }}</textarea>
                            @error('alasan_pause')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        @endif

                        <div class="col-12 mt-4 d-flex flex-wrap align-items-center gap-2">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Perbarui Rencana Kerja Kepanitiaan</button>
                            <a href="{{ route('kepanitiaan.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
                            @if($estimasiLocked)
                                @if($kepanitiaan->estimasi_unlock_requested_at)
                                    <span class="badge text-bg-info fw-normal py-2 px-3">
                                        <i class="bi bi-hourglass-split me-1"></i>
                                        Menunggu persetujuan Pimpinan (diajukan {{ \Carbon\Carbon::parse($kepanitiaan->estimasi_unlock_requested_at)->translatedFormat('d M Y H:i') }})
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

@if($estimasiLocked && !$kepanitiaan->estimasi_unlock_requested_at)
<!-- Modal Ajukan Perubahan Estimasi -->
<div class="modal fade" id="modalRequestUnlockEstimasi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('kepanitiaan.request-unlock-estimasi', $kepanitiaan->id) }}" method="POST">
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

        // Dynamic Sesi Presensi Repeater (Edit)
        let sesiCountEdit = 0;
        function tambahBarisSesiEdit(nama = '', tgl = '') {
            sesiCountEdit++;
            const tglVal = tgl || ($('#tanggal_mulai').val() || new Date().toISOString().split('T')[0]);
            const html = `
            <div class="row g-2 align-items-center bg-white p-2 rounded border shadow-2xs baris-sesi-item" id="baris-sesi-edit-${sesiCountEdit}">
                <div class="col-12 col-md-7">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted"><i class="bi bi-tag-fill"></i></span>
                        <input type="text" name="nama_sesi_presensi[]" class="form-control" placeholder="Nama Sesi Baru..." value="${nama}" required>
                    </div>
                </div>
                <div class="col-9 col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted"><i class="bi bi-calendar-event"></i></span>
                        <input type="date" name="tanggal_sesi_presensi[]" class="form-control" value="${tglVal}">
                    </div>
                </div>
                <div class="col-3 col-md-1 text-end">
                    <button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="$('#baris-sesi-edit-${sesiCountEdit}').remove()" title="Hapus Baris Sesi">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>`;
            $('#sesiPresensiContainerEdit').append(html);
        }

        $('#btnTambahBarisSesiEdit').on('click', function() {
            tambahBarisSesiEdit();
        });
    });
</script>
@endpush
