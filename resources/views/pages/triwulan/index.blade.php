@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
    <h1>Triwulan (TW)</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('periode-akademik.index') }}">Periode Akademik</a></li>
            <li class="breadcrumb-item active">Triwulan (TW)</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    <!-- Card Info Ketentuan Triwulan -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-left: 4px solid #0d6efd !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="p-2 rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                <i class="bi bi-calendar3 fs-5"></i>
                            </span>
                            <div>
                                <h6 class="fw-bold text-dark mb-0.5">Ketentuan Pembagian Triwulan (TW)</h6>
                                <p class="text-secondary small mb-0">Pembagian triwulan akademik resmi berdasarkan kelompok 3 bulanan:</p>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge bg-light text-dark border px-2.5 py-1.5 fw-semibold" style="font-size: 0.8rem;">
                                <span class="text-primary me-1">●</span> <strong>TW1</strong>: Sep – Nov (Bulan 9, 10, 11)
                            </span>
                            <span class="badge bg-light text-dark border px-2.5 py-1.5 fw-semibold" style="font-size: 0.8rem;">
                                <span class="text-info me-1">●</span> <strong>TW2</strong>: Des – Feb (Bulan 12, 1, 2)
                            </span>
                            <span class="badge bg-light text-dark border px-2.5 py-1.5 fw-semibold" style="font-size: 0.8rem;">
                                <span class="text-warning me-1">●</span> <strong>TW3</strong>: Mar – Mei (Bulan 3, 4, 5)
                            </span>
                            <span class="badge bg-light text-dark border px-2.5 py-1.5 fw-semibold" style="font-size: 0.8rem;">
                                <span class="text-success me-1">●</span> <strong>TW4</strong>: Jun – Agu (Bulan 6, 7, 8)
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h5 class="card-title m-0 fw-bold text-dark fs-6 d-flex align-items-center gap-2">
                                <i class="bi bi-calendar2-range text-primary"></i>
                                <span>Daftar Triwulan (TW)</span>
                            </h5>
                            @if($activePeriode)
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1" style="font-size: 0.76rem;">
                                    <i class="bi bi-check-circle-fill me-1"></i>T.A. Aktif: {{ $activePeriode->formatted_ta }}
                                </span>
                            @endif
                        </div>

                        <!-- Filter Periode & Action Buttons -->
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <form action="{{ route('triwulan.index') }}" method="GET" class="d-inline-flex align-items-center gap-2">
                                <select name="periode_id" class="form-select form-select-sm" style="min-width: 220px;" onchange="this.form.submit()">
                                    <option value="">-- Semua Periode Akademik --</option>
                                    @foreach($periodeAkademiks as $p)
                                        <option value="{{ $p->id }}" {{ $selectedPeriodeId == $p->id ? 'selected' : '' }}>
                                            {{ $p->formatted_ta }} {{ $p->is_active ? '★ (Aktif)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>

                            @if($selectedPeriodeId)
                                <form action="{{ route('triwulan.generate-standard') }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="periode_id" value="{{ $selectedPeriodeId }}">
                                    <button type="submit" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1 shadow-2xs fw-semibold" title="Generate atau sesuaikan 4 Triwulan standar untuk periode ini">
                                        <i class="bi bi-magic"></i> Generate TW1–TW4
                                    </button>
                                </form>
                            @endif

                            <a href="{{ route('triwulan.create', ['periode_id' => $selectedPeriodeId]) }}" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1 shadow-2xs fw-semibold">
                                <i class="bi bi-plus-circle"></i> Tambah Triwulan
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%" class="text-center">No</th>
                                    <th>Periode Akademik</th>
                                    <th>Nama Triwulan</th>
                                    <th>Rentang Bulan</th>
                                    <th>Bulan Pengerjaan</th>
                                    <th class="text-center" width="12%">Status</th>
                                    <th class="text-center" width="18%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($triwulans as $index => $item)
                                    <tr>
                                        <td class="text-center text-muted fw-bold small">{{ $index + 1 }}</td>
                                        <td>
                                            @if($item->periodeAkademik)
                                                <span class="fw-semibold text-dark">{{ $item->periodeAkademik->formatted_ta }}</span>
                                                @if($item->periodeAkademik->is_active)
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle ms-1" style="font-size: 0.7rem;">Aktif</span>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-primary px-2.5 py-1 rounded-pill fw-bold" style="font-size: 0.82rem;">
                                                {{ $item->nama }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-dark">{{ $item->rentang_bulan ?? '-' }}</span>
                                        </td>
                                        <td>
                                            <span class="text-secondary small">{{ $item->keterangan ?? '-' }}</span>
                                            <span class="text-muted small ms-1">(Bulan {{ $item->bulan_mulai }} s/d {{ $item->bulan_selesai }})</span>
                                        </td>
                                        <td class="text-center">
                                            @if($item->is_active)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill fw-bold" style="font-size: 0.78rem;">
                                                    <i class="bi bi-check-circle-fill me-1"></i>Aktif
                                                </span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2.5 py-1 rounded-pill" style="font-size: 0.78rem;">
                                                    Tidak Aktif
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="d-inline-flex gap-1.5 flex-nowrap align-items-center">
                                                @if(!$item->is_active)
                                                    <button type="button" onclick="setActiveTriwulan({{ $item->id }}, '{{ $item->nama }} ({{ $item->rentang_bulan }})')" class="btn btn-outline-success btn-sm d-inline-flex align-items-center gap-1" title="Aktifkan Triwulan Ini">
                                                        <i class="bi bi-check2-circle"></i> Aktifkan
                                                    </button>
                                                @endif
                                                <a href="{{ route('triwulan.edit', $item->id) }}" class="btn btn-warning btn-sm text-white d-inline-flex align-items-center gap-1" title="Edit Triwulan">
                                                    <i class="bi bi-pencil-square"></i> Edit
                                                </a>
                                                <button type="button" onclick="deleteTriwulan({{ $item->id }})" class="btn btn-danger btn-sm d-inline-flex align-items-center gap-1" title="Hapus Triwulan">
                                                    <i class="bi bi-trash"></i> Hapus
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="bi bi-calendar-x fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                            <div class="fw-semibold">Belum ada data Triwulan (TW) untuk periode ini.</div>
                                            @if($selectedPeriodeId)
                                                <div class="small mt-1">Klik tombol <strong>Generate TW1–TW4</strong> di atas untuk membuat 4 triwulan standar secara otomatis.</div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    function setActiveTriwulan(id, label) {
        Swal.fire({
            title: 'Aktifkan Triwulan?',
            text: "Triwulan " + label + " akan dijadikan triwulan aktif utama untuk periode akademik terkait.",
            icon: 'question',
            showCancelButton: true,
            confirmColor: '#198754',
            cancelColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-check2-circle me-1"></i> Ya, Aktifkan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('triwulan') }}/" + id + "/set-active",
                    type: 'POST',
                    data: {
                        "_token": "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: response.message,
                                icon: 'success',
                                timer: 1800,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Gagal!',
                            text: xhr.responseJSON?.message || 'Terjadi kesalahan saat mengaktifkan triwulan.',
                            icon: 'error'
                        });
                    }
                });
            }
        });
    }

    function deleteTriwulan(id) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data triwulan ini akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmColor: '#dc3545',
            cancelColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('triwulan') }}/" + id,
                    type: 'DELETE',
                    data: {
                        "_token": "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: response.message,
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Gagal!',
                            text: xhr.responseJSON?.message || 'Terjadi kesalahan saat menghapus data.',
                            icon: 'error'
                        });
                    }
                });
            }
        });
    }
</script>
@endpush
