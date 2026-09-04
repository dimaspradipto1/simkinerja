@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
    <h1>Pengaturan Status Absensi</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item">Pengaturan</li>
            <li class="breadcrumb-item active">Status Absensi</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    <!-- Summary Alert / Info Box -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 12px; background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); color: white;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div>
                            <h4 class="fw-bold mb-1"><i class="bi bi-toggle-on me-2"></i>Kontrol Sesi Presensi Kepanitiaan</h4>
                            <p class="mb-0 text-white-50" style="font-size: 14px;">
                                Aktifkan atau nonaktifkan sesi absensi. Sesi yang <strong>Nonaktif (Tutup)</strong> tidak dapat discan atau diakses untuk presensi oleh peserta.
                            </p>
                        </div>
                        <div class="d-flex gap-2">
                            <span class="badge bg-light text-dark px-3 py-2 rounded-pill fs-6 fw-semibold shadow-sm">
                                <i class="bi bi-shield-check text-success me-1"></i> Admin Control
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Grouped by Category -->
    @foreach ($settings as $kategori => $items)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-bold">
                                <i class="bi bi-calendar-event me-1"></i> Kepanitiaan {{ $kategori }}
                            </span>
                            <span class="text-muted small">({{ count($items) }} Hari Sesi)</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;" class="text-center">#</th>
                                        <th>Nama Sesi Absensi</th>
                                        <th>Keterangan</th>
                                        <th style="width: 140px;" class="text-center">Status</th>
                                        <th style="width: 160px;" class="text-center">Aksi / Switch</th>
                                        <th style="width: 120px;" class="text-center">Tautan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($items as $index => $item)
                                        <tr id="row-{{ $item->slug }}">
                                            <td class="text-center fw-semibold text-muted">{{ $loop->iteration }}</td>
                                            <td>
                                                <div class="fw-bold text-dark">{{ $item->nama }}</div>
                                                <code class="small text-muted">{{ $item->slug }}</code>
                                            </td>
                                            <td class="text-muted small">
                                                {{ $item->keterangan ?? '-' }}
                                            </td>
                                            <td class="text-center">
                                                <span id="badge-{{ $item->slug }}" class="badge rounded-pill px-3 py-2 {{ $item->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                    <i class="bi {{ $item->is_active ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }} me-1"></i>
                                                    <span id="badge-text-{{ $item->slug }}">{{ $item->is_active ? 'Aktif' : 'Tutup' }}</span>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="form-check form-switch d-inline-block">
                                                    <input class="form-check-input toggle-status" type="checkbox" role="switch"
                                                        id="switch-{{ $item->slug }}"
                                                        data-slug="{{ $item->slug }}"
                                                        data-nama="{{ $item->nama }}"
                                                        style="width: 2.8em; height: 1.4em; cursor: pointer;"
                                                        {{ $item->is_active ? 'checked' : '' }}>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                @if (Route::has($item->slug . '.index'))
                                                    <a href="{{ route($item->slug . '.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                                        <i class="bi bi-box-arrow-up-right me-1"></i> Buka
                                                    </a>
                                                @else
                                                    <span class="text-muted small">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</section>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('.toggle-status').on('change', function() {
        const checkbox = $(this);
        const slug = checkbox.data('slug');
        const nama = checkbox.data('nama');
        const isChecked = checkbox.is(':checked');

        // Disable checkbox while loading
        checkbox.prop('disabled', true);

        $.ajax({
            url: "{{ url('pengaturan-absensi') }}/" + slug + "/toggle",
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                checkbox.prop('disabled', false);
                if (response.success) {
                    const badge = $('#badge-' + slug);
                    const badgeText = $('#badge-text-' + slug);

                    if (response.is_active) {
                        badge.removeClass('bg-secondary').addClass('bg-success');
                        badge.find('i').removeClass('bi-x-circle-fill').addClass('bi-check-circle-fill');
                        badgeText.text('Aktif');
                    } else {
                        badge.removeClass('bg-success').addClass('bg-secondary');
                        badge.find('i').removeClass('bi-check-circle-fill').addClass('bi-x-circle-fill');
                        badgeText.text('Tutup');
                    }

                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: response.message,
                        showConfirmButton: false,
                        timer: 2500,
                        timerProgressBar: true
                    });
                }
            },
            error: function(xhr) {
                checkbox.prop('disabled', false);
                // Revert checkbox state
                checkbox.prop('checked', !isChecked);

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: xhr.responseJSON?.message || 'Terjadi kesalahan saat mengubah status absensi.',
                });
            }
        });
    });
});
</script>
@endpush
