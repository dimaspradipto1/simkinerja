@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
    <h1>Edit Triwulan (TW)</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('triwulan.index') }}">Triwulan (TW)</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h5 class="card-title fw-bold text-dark fs-6 mb-3">
                        <i class="bi bi-pencil-square text-primary me-2"></i>Form Edit Triwulan
                    </h5>

                    <!-- Template Preset Sesuai Ketentuan -->
                    <div class="p-3 mb-4 rounded-3 bg-light border">
                        <label class="form-label fw-bold text-dark small mb-2 d-flex align-items-center gap-1.5">
                            <i class="bi bi-lightning-charge-fill text-warning"></i>
                            <span>Pilih Cepat Sesuai Ketentuan Standar:</span>
                        </label>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($standardTws as $key => $st)
                                <button type="button" class="btn btn-sm btn-outline-primary btn-preset-tw fw-semibold"
                                        data-nama="{{ $st['nama'] }}"
                                        data-label="{{ $st['label'] }}"
                                        data-rentang="{{ $st['rentang_bulan'] }}"
                                        data-ket="{{ $st['keterangan'] }}"
                                        data-bmulai="{{ $st['bulan_mulai'] }}"
                                        data-bselesai="{{ $st['bulan_selesai'] }}"
                                        data-urutan="{{ $st['urutan'] }}">
                                    <i class="bi bi-check-circle me-1"></i> {{ $st['label'] }} ({{ $st['keterangan'] }})
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <form action="{{ route('triwulan.update', $triwulan->id) }}" method="POST" class="row g-3">
                        @csrf
                        @method('PUT')

                        <!-- Nama Triwulan -->
                        <div class="col-md-6">
                            <label for="nama" class="form-label fw-semibold text-dark">
                                Nama Triwulan <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama" name="nama" value="{{ old('nama', $triwulan->nama) }}" placeholder="Contoh: TW1" required>
                            @error('nama')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Label -->
                        <div class="col-md-6">
                            <label for="label" class="form-label fw-semibold text-dark">Label Tampilan</label>
                            <input type="text" class="form-control @error('label') is-invalid @enderror" id="label" name="label" value="{{ old('label', $triwulan->label) }}" placeholder="Contoh: TW1 (Sep–Nov)">
                            @error('label')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Rentang Bulan Singkat -->
                        <div class="col-md-6">
                            <label for="rentang_bulan" class="form-label fw-semibold text-dark">Rentang Bulan Singkat</label>
                            <input type="text" class="form-control @error('rentang_bulan') is-invalid @enderror" id="rentang_bulan" name="rentang_bulan" value="{{ old('rentang_bulan', $triwulan->rentang_bulan) }}" placeholder="Contoh: Sep–Nov">
                            @error('rentang_bulan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Keterangan Bulan -->
                        <div class="col-md-12">
                            <label for="keterangan" class="form-label fw-semibold text-dark">Keterangan Bulan Lengkap</label>
                            <input type="text" class="form-control @error('keterangan') is-invalid @enderror" id="keterangan" name="keterangan" value="{{ old('keterangan', $triwulan->keterangan) }}" placeholder="Contoh: September – November">
                            @error('keterangan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Bulan Mulai & Selesai (Angka) -->
                        <div class="col-md-4">
                            <label for="bulan_mulai" class="form-label fw-semibold text-dark">Bulan Mulai (1-12) <span class="text-danger">*</span></label>
                            <select name="bulan_mulai" id="bulan_mulai" class="form-select @error('bulan_mulai') is-invalid @enderror" required>
                                @for($m = 1; $m <= 12; $m++)
                                    @php $mName = DateTime::createFromFormat('!m', $m)->format('F'); @endphp
                                    <option value="{{ $m }}" {{ old('bulan_mulai', $triwulan->bulan_mulai) == $m ? 'selected' : '' }}>
                                        Bulan {{ $m }} ({{ $mName }})
                                    </option>
                                @endfor
                            </select>
                            @error('bulan_mulai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="bulan_selesai" class="form-label fw-semibold text-dark">Bulan Selesai (1-12) <span class="text-danger">*</span></label>
                            <select name="bulan_selesai" id="bulan_selesai" class="form-select @error('bulan_selesai') is-invalid @enderror" required>
                                @for($m = 1; $m <= 12; $m++)
                                    @php $mName = DateTime::createFromFormat('!m', $m)->format('F'); @endphp
                                    <option value="{{ $m }}" {{ old('bulan_selesai', $triwulan->bulan_selesai) == $m ? 'selected' : '' }}>
                                        Bulan {{ $m }} ({{ $mName }})
                                    </option>
                                @endfor
                            </select>
                            @error('bulan_selesai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="urutan" class="form-label fw-semibold text-dark">Urutan Tampilan</label>
                            <input type="number" class="form-control @error('urutan') is-invalid @enderror" id="urutan" name="urutan" value="{{ old('urutan', $triwulan->urutan) }}" min="1" max="10">
                            @error('urutan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Status Aktif Switch -->
                        <div class="col-12 mt-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $triwulan->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold text-dark" for="is_active">
                                    Jadikan Triwulan Aktif
                                </label>
                            </div>
                        </div>

                        <!-- Tombol Submit -->
                        <div class="col-12 mt-4 pt-2 border-top">
                            <button type="submit" class="btn btn-primary px-4 fw-semibold"><i class="bi bi-save me-1"></i> Perbarui Triwulan</button>
                            <a href="{{ route('triwulan.index') }}" class="btn btn-secondary px-3"><i class="bi bi-arrow-left me-1"></i> Batal</a>
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
        $('.btn-preset-tw').on('click', function() {
            let btn = $(this);
            $('#nama').val(btn.data('nama'));
            $('#label').val(btn.data('label'));
            $('#rentang_bulan').val(btn.data('rentang'));
            $('#keterangan').val(btn.data('ket'));
            $('#bulan_mulai').val(btn.data('bmulai'));
            $('#bulan_selesai').val(btn.data('bselesai'));
            $('#urutan').val(btn.data('urutan'));

            $('.btn-preset-tw').removeClass('btn-primary').addClass('btn-outline-primary');
            btn.removeClass('btn-outline-primary').addClass('btn-primary');
        });
    });
</script>
@endpush
