@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
    <h1>Tambah Rencana Kerja</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('rencana-kerja.index') }}">Rencana Kerja</a></li>
            <li class="breadcrumb-item active">Tambah</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title fw-bold">Form Tambah Rencana Kerja</h5>

                    <form action="{{ route('rencana-kerja.store') }}" method="POST" class="row g-3">
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
                                <option value="" disabled {{ old('kategori') ? '' : 'selected' }}>-- Pilih Kategori --</option>
                                @if(isset($kategoris) && $kategoris->count() > 0)
                                    @foreach($kategoris as $k)
                                        <option value="{{ $k->nama_kategori }}" {{ old('kategori', 'Rencana Kerja') == $k->nama_kategori ? 'selected' : '' }}>
                                            {{ $k->nama_kategori }}
                                        </option>
                                    @endforeach
                                @else
                                    <option value="Rencana Kerja" {{ old('kategori', 'Rencana Kerja') == 'Rencana Kerja' ? 'selected' : '' }}>Rencana Kerja</option>
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

                        <div class="col-md-12">
                            <label for="uraian_tugas" class="form-label fw-semibold">Uraian Tugas <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('uraian_tugas') is-invalid @enderror" id="uraian_tugas" name="uraian_tugas" rows="4" placeholder="Tuliskan detail rencana/uraian tugas yang akan dikerjakan" required>{{ old('uraian_tugas') }}</textarea>
                            @error('uraian_tugas')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label for="tagged_users" class="form-label fw-semibold">Tag User Lain (Rekan Kerja)</label>
                            <select class="form-select @error('tagged_users') is-invalid @enderror" id="tagged_users" name="tagged_users[]" multiple="multiple">
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" {{ (is_array(old('tagged_users')) && in_array($u->id, old('tagged_users'))) ? 'selected' : '' }}>
                                        {{ $u->jabatan ?? '-' }} - {{ $u->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text text-muted small">Pilih satu atau beberapa rekan kerja yang mengerjakan pekerjaan yang sama.</div>
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
                                    <small class="text-muted fst-italic">Boleh diisi Hari saja, Jam saja, keduanya, atau dihitung otomatis.</small>
                                </div>
                                <div class="row g-2 align-items-center">
                                    <div class="col-md-3 col-sm-6">
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
                                    <div class="col-md-3 col-sm-6">
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
                                    <div class="col-md-6 col-12 pt-md-3">
                                        <div class="d-flex flex-wrap align-items-center gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-success fw-semibold" id="btn-hitung-durasi-auto" title="Hitung durasi dari tanggal/jam mulai ke selesai">
                                                <i class="bi bi-calculator me-1"></i> Hitung dari Tanggal
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-primary fw-semibold" id="btn-hitung-selesai-auto" title="Hitung tanggal/jam selesai berdasarkan estimasi mulai + durasi">
                                                <i class="bi bi-calendar-plus me-1"></i> Terapkan ke Tgl Selesai
                                            </button>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 ms-auto d-none" id="preview-durasi-badge"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="alert alert-info mb-0">
                                Absensi harian dikelola melalui menu
                                <a href="{{ route('absensi-kinerja.index') }}" class="alert-link">Absensi Kinerja</a>.
                                Setelah tugas dimulai, uraian dan statusnya otomatis masuk ke keterangan pada tanggal kerja yang sama.
                            </div>
                        </div>

                        <div class="col-12 mt-4 d-flex align-items-center gap-2">
                            <button type="submit" class="btn text-white px-4 fw-bold shadow-sm" style="background-color: #15432d; border-radius: 8px; min-height: 40px;"><i class="bi bi-save me-1"></i> Simpan Rencana Kerja</button>
                            <a href="{{ route('rencana-kerja.index') }}" class="btn btn-secondary px-3" style="border-radius: 8px; min-height: 40px;"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
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
        $('#tagged_users').select2({
            theme: 'bootstrap-5',
            placeholder: "Cari & pilih rekan kerja...",
            allowClear: true
        });

        function updateDurasiPreview() {
            let h = parseInt($('#estimasi_durasi_hari').val()) || 0;
            let j = parseInt($('#estimasi_durasi_jam').val()) || 0;
            let parts = [];
            if (h > 0) parts.push(h + ' hari');
            if (j > 0) parts.push(j + ' jam');

            let $badge = $('#preview-durasi-badge');
            if (parts.length > 0) {
                $badge.html('<i class="bi bi-check2-circle me-1"></i>Target Durasi: ' + parts.join(' ')).removeClass('d-none');
            } else {
                $badge.addClass('d-none');
            }
        }

        $('#estimasi_durasi_hari, #estimasi_durasi_jam').on('input change', updateDurasiPreview);
        updateDurasiPreview();

        // Hitung durasi otomatis dari tanggal/jam mulai & selesai
        $('#btn-hitung-durasi-auto').on('click', function() {
            let tglMulai = $('#estimasi_tanggal_mulai').val();
            let tglSelesai = $('#estimasi_tanggal_selesai').val();
            let jamMulai = $('#estimasi_jam_mulai').val() || '08:00';
            let jamSelesai = $('#estimasi_jam_selesai').val() || '16:00';

            if (!tglMulai || !tglSelesai) {
                alert('Silakan isi Estimasi Tanggal Mulai dan Estimasi Tanggal Selesai terlebih dahulu.');
                return;
            }

            let startDt = new Date(tglMulai + 'T' + (jamMulai.length === 5 ? jamMulai + ':00' : jamMulai));
            let endDt = new Date(tglSelesai + 'T' + (jamSelesai.length === 5 ? jamSelesai + ':00' : jamSelesai));

            let diffMs = endDt - startDt;
            if (diffMs < 0) {
                alert('Estimasi Selesai tidak boleh mendahului Estimasi Mulai.');
                return;
            }

            let totalMinutes = Math.floor(diffMs / (1000 * 60));
            let days = Math.floor(totalMinutes / (24 * 60));
            let remMinutes = totalMinutes % (24 * 60);
            let hours = Math.round(remMinutes / 60);

            if (hours === 24) {
                days += 1;
                hours = 0;
            }

            $('#estimasi_durasi_hari').val(days > 0 ? days : '');
            $('#estimasi_durasi_jam').val(hours > 0 ? hours : '');
            updateDurasiPreview();
        });

        // Hitung tanggal/jam selesai otomatis dari tanggal mulai + durasi
        $('#btn-hitung-selesai-auto').on('click', function() {
            let tglMulai = $('#estimasi_tanggal_mulai').val();
            let jamMulai = $('#estimasi_jam_mulai').val() || '08:00';
            let h = parseInt($('#estimasi_durasi_hari').val()) || 0;
            let j = parseInt($('#estimasi_durasi_jam').val()) || 0;

            if (!tglMulai) {
                alert('Silakan isi Estimasi Tanggal Mulai terlebih dahulu.');
                return;
            }

            if (h === 0 && j === 0) {
                alert('Silakan isi durasi (hari atau jam) terlebih dahulu.');
                return;
            }

            let startDt = new Date(tglMulai + 'T' + (jamMulai.length === 5 ? jamMulai + ':00' : jamMulai));
            let addedMs = (h * 24 * 60 * 60 * 1000) + (j * 60 * 60 * 1000);
            let endDt = new Date(startDt.getTime() + addedMs);

            let year = endDt.getFullYear();
            let month = String(endDt.getMonth() + 1).padStart(2, '0');
            let day = String(endDt.getDate()).padStart(2, '0');
            let hours = String(endDt.getHours()).padStart(2, '0');
            let minutes = String(endDt.getMinutes()).padStart(2, '0');

            $('#estimasi_tanggal_selesai').val(`${year}-${month}-${day}`);
            $('#estimasi_jam_selesai').val(`${hours}:${minutes}`);
        });
    });
</script>
@endpush
