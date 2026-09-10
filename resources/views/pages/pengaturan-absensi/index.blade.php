@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
    <h1 class="fw-bold text-dark">Pengaturan Status Absensi</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item">Pengaturan</li>
            <li class="breadcrumb-item active">Status Absensi</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    <!-- Header Banner -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 16px; background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #047857 100%); color: white;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div style="max-width: 680px;">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-success bg-opacity-25 text-success-light border border-success border-opacity-25 px-2.5 py-1 rounded-pill" style="font-size: 0.78rem;">
                                    <i class="bi bi-broadcast me-1"></i> Sinkronisasi Sidebar Presensi
                                </span>
                                <span class="badge bg-white bg-opacity-10 text-white border border-white border-opacity-10 px-2.5 py-1 rounded-pill" style="font-size: 0.78rem;">
                                    <i class="bi bi-shield-check me-1"></i> Admin &amp; Pimpinan
                                </span>
                            </div>
                            <h4 class="fw-bold mb-2 text-white"><i class="bi bi-sliders2 me-2 text-success"></i>Kontrol Sesi Presensi Kepanitiaan</h4>
                            <p class="mb-0 text-white" style="font-size: 0.92rem; line-height: 1.55; opacity: 0.95;">
                                Sesi presensi yang berstatus <strong class="text-white text-decoration-underline">Aktif</strong> otomatis tampil pada menu sidebar (<strong class="text-warning">Sesi Presensi Aktif</strong>) dan dapat diakses/discan oleh peserta. Sesi yang <strong class="text-white">Tutup</strong> otomatis disembunyikan dari sidebar.
                            </p>
                        </div>
                        <div class="d-flex flex-wrap align-items-center" style="gap: 16px;">
                            <div class="bg-white rounded-4 px-3 py-2 text-center shadow-sm" style="min-width: 115px; border: 1px solid rgba(255,255,255,0.8);">
                                <div class="fs-3 fw-bolder text-success" id="stat-active-sessions" style="line-height: 1.15;">{{ $activeCount }}</div>
                                <div class="fw-bold text-dark mt-1" style="font-size: 0.82rem; letter-spacing: 0.2px;">Aktif di Sidebar</div>
                            </div>
                            <div class="bg-white rounded-4 px-3 py-2 text-center shadow-sm" style="min-width: 115px; border: 1px solid rgba(255,255,255,0.8);">
                                <div class="fs-3 fw-bolder text-secondary" id="stat-closed-sessions" style="line-height: 1.15;">{{ $closedCount }}</div>
                                <div class="fw-bold text-dark mt-1" style="font-size: 0.82rem; letter-spacing: 0.2px;">Sesi Tutup</div>
                            </div>
                            <div class="bg-white rounded-4 px-3 py-2 text-center shadow-sm" style="min-width: 115px; border: 1px solid rgba(255,255,255,0.8);">
                                <div class="fs-3 fw-bolder text-primary" id="stat-total-sessions" style="line-height: 1.15;">{{ $totalCount }}</div>
                                <div class="fw-bold text-dark mt-1" style="font-size: 0.82rem; letter-spacing: 0.2px;">Total Sesi</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Toolbar: Filter Pills & Search -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
        <div class="card-body p-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <!-- Filter Pills -->
                <div class="d-flex flex-wrap align-items-center gap-2" id="filter-pills-group">
                    <button type="button" class="btn btn-sm btn-filter-pill active" data-filter="active">
                        <span class="pulse-dot-indicator"></span>
                        <span class="fw-semibold">Sedang Tampil di Sidebar</span>
                        <span class="badge rounded-pill bg-success text-white ms-1" id="badge-count-active">{{ $activeCount }}</span>
                    </button>
                    <button type="button" class="btn btn-sm btn-filter-pill" data-filter="all">
                        <i class="bi bi-collection me-1"></i>
                        <span class="fw-semibold">Semua Sesi Kepanitiaan</span>
                        <span class="badge rounded-pill bg-secondary text-white ms-1" id="badge-count-total">{{ $totalCount }}</span>
                    </button>
                    <button type="button" class="btn btn-sm btn-filter-pill" data-filter="closed">
                        <i class="bi bi-eye-slash me-1"></i>
                        <span class="fw-semibold">Tutup / Nonaktif</span>
                        <span class="badge rounded-pill bg-light text-secondary border ms-1" id="badge-count-closed">{{ $closedCount }}</span>
                    </button>
                </div>

                <!-- Quick Search -->
                <div class="position-relative" style="min-width: 250px;">
                    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    <input type="text" id="input-search-sesi" class="form-control form-control-sm rounded-pill ps-5 py-1.5 shadow-none" placeholder="Cari nama sesi atau kepanitiaan..." style="background-color: #f8fafc; border: 1px solid #e2e8f0; font-size: 0.86rem;">
                </div>
            </div>
        </div>
    </div>

    <!-- Grouped by Kepanitiaan -->
    <div id="kepanitiaan-container">
        @forelse ($kepanitiaans as $kepanitiaan)
            @php
                $activeInKep = $kepanitiaan->sesiAbsensis->where('is_active', true)->count();
                $totalInKep = $kepanitiaan->sesiAbsensis->count();
            @endphp
            <div class="card border-0 shadow-sm mb-4 kepanitiaan-card" 
                 data-kepanitiaan-id="{{ $kepanitiaan->id }}"
                 data-active-count="{{ $activeInKep }}"
                 style="border-radius: 14px; overflow: hidden;">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-bold" style="font-size: 0.84rem;">
                            <i class="bi bi-bookmark-check-fill me-1 text-primary"></i> Kepanitiaan: {{ $kepanitiaan->uraian_tugas }}
                        </span>
                        @if(!empty($kepanitiaan->unit))
                            <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1.5 fw-normal" style="font-size: 0.78rem;">
                                <i class="bi bi-building me-1"></i>{{ $kepanitiaan->unit }}
                            </span>
                        @endif
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1.5 small fw-semibold kep-session-summary">
                            <span class="text-success fw-bold kep-active-count">{{ $activeInKep }}</span> dari {{ $totalInKep }} Sesi Aktif
                        </span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                                <tr>
                                    <th style="width: 50px;" class="text-center text-uppercase fw-bold text-muted small">#</th>
                                    <th class="text-uppercase fw-bold text-muted small">Nama Sesi Absensi</th>
                                    <th class="text-uppercase fw-bold text-muted small">Jadwal &amp; Waktu</th>
                                    <th class="text-center text-uppercase fw-bold text-muted small" style="width: 130px;">Kehadiran</th>
                                    <th style="width: 170px;" class="text-center text-uppercase fw-bold text-muted small">Status di Sidebar</th>
                                    <th style="width: 130px;" class="text-center text-uppercase fw-bold text-muted small">Aksi / Switch</th>
                                    <th style="width: 190px;" class="text-center text-uppercase fw-bold text-muted small">Tautan Presensi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($kepanitiaan->sesiAbsensis as $sesi)
                                    @php
                                        $targetUrl = route('presensi.sesi.show', $sesi->id);
                                        $qrProcessUrl = route('presensi.scan-proses', ['token' => $sesi->token]);
                                    @endphp
                                    <tr class="sesi-row" 
                                        id="row-sesi-{{ $sesi->id }}"
                                        data-active="{{ $sesi->is_active ? '1' : '0' }}"
                                        data-search="{{ strtolower($sesi->nama_sesi . ' ' . $kepanitiaan->uraian_tugas . ' ' . $sesi->tanggal) }}">
                                        <td class="text-center fw-semibold text-muted">{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                @if($sesi->is_active)
                                                    <span class="pulse-dot-indicator flex-shrink-0 status-pulse-{{ $sesi->id }}"></span>
                                                @else
                                                    <span class="status-dot-closed flex-shrink-0 status-pulse-{{ $sesi->id }}"></span>
                                                @endif
                                                <div>
                                                    <div class="fw-bold text-dark" style="font-size: 0.94rem;">{{ $sesi->nama_sesi }}</div>
                                                    <small class="text-muted d-inline-flex align-items-center gap-1 mt-0.5" style="font-size: 0.76rem;">
                                                        <span class="badge bg-light text-secondary border px-1.5 py-0.5 rounded">Sesi #{{ $sesi->id }}</span>
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="small text-dark fw-semibold">
                                                <i class="bi bi-calendar3 text-primary" style="margin-right: 5px;"></i>
                                                {{ $sesi->tanggal ? date('d M Y', strtotime($sesi->tanggal)) : 'Tanggal Berlangsung' }}
                                            </div>
                                            <div class="text-muted small mt-0.5" style="font-size: 0.76rem;">
                                                <i class="bi bi-clock text-secondary" style="margin-right: 4px;"></i>
                                                {{ $sesi->jam_mulai ? date('H:i', strtotime($sesi->jam_mulai)) : '00:00' }} - {{ $sesi->jam_selesai ? date('H:i', strtotime($sesi->jam_selesai)) : 'Selesai' }}
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1.5 fw-semibold" style="font-size: 0.8rem;">
                                                <i class="bi bi-people-fill text-primary" style="margin-right: 4px;"></i>
                                                {{ $sesi->presensis_count ?? 0 }} Pegawai
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span id="badge-status-{{ $sesi->id }}" 
                                                  class="badge rounded-pill px-3 py-1.5 fw-semibold {{ $sesi->is_active ? 'bg-success text-white' : 'bg-secondary text-white' }}"
                                                  style="font-size: 0.8rem;">
                                                <i class="bi {{ $sesi->is_active ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }}" style="margin-right: 5px;"></i>
                                                <span class="badge-status-text">{{ $sesi->is_active ? 'Aktif (Tampil di Sidebar)' : 'Tutup (Sembunyi)' }}</span>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="form-check form-switch d-inline-block m-0">
                                                <input class="form-check-input toggle-status" 
                                                       type="checkbox" 
                                                       role="switch"
                                                       id="switch-{{ $sesi->id }}"
                                                       data-id="{{ $sesi->id }}"
                                                       data-slug="{{ $sesi->legacy_slug ?: 'sesi-' . $sesi->id }}"
                                                       data-nama="{{ $sesi->nama_sesi }}"
                                                       data-kepanitiaan-id="{{ $kepanitiaan->id }}"
                                                       style="width: 2.8em; height: 1.4em; cursor: pointer;"
                                                       {{ $sesi->is_active ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex align-items-center justify-content-center" style="gap: 10px;">
                                                <!-- Link Buka Langsung (Halaman yang sama dengan sidebar) -->
                                                <a href="{{ $targetUrl }}" 
                                                   class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center shadow-sm"
                                                   title="Buka Halaman Presensi Sesi Ini (Sesuai Sidebar)">
                                                    <i class="bi bi-box-arrow-up-right" style="margin-right: 6px;"></i>
                                                    <span>Buka</span>
                                                </a>

                                                <!-- Tombol Barcode QR Modal -->
                                                <button type="button" 
                                                        class="btn btn-sm btn-dark text-white rounded-circle shadow-sm btn-barcode-absensi d-inline-flex align-items-center justify-content-center"
                                                        style="width: 32px; height: 32px; min-width: 32px;"
                                                        data-slug="{{ $sesi->legacy_slug ?? 'sesi-' . $sesi->id }}"
                                                        data-token="{{ $sesi->token }}"
                                                        data-scan-url="{{ $qrProcessUrl }}"
                                                        data-title="{{ $sesi->nama_sesi }}"
                                                        data-url="{{ $qrProcessUrl }}"
                                                        data-active="{{ $sesi->is_active ? '1' : '0' }}"
                                                        onclick="window.openBarcodeModal(this)"
                                                        title="Tampilkan Barcode QR Sesi Ini">
                                                    <i class="bi bi-qr-code text-warning fs-6"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @empty
            <div class="alert alert-info py-4 px-4 rounded-3 border-0 d-flex align-items-center gap-3">
                <i class="bi bi-info-circle fs-3 text-info"></i>
                <div>
                    <h6 class="fw-bold mb-1">Belum Ada Sesi Presensi Kepanitiaan</h6>
                    <p class="mb-0 small text-muted">Silakan tambahkan kepanitiaan dan sesi absensi pada menu <a href="{{ route('kepanitiaan.index') }}" class="fw-bold alert-link">Rencana Kerja Kepanitiaan</a>.</p>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Empty Filter Notice -->
    <div id="filter-empty-alert" class="alert alert-warning py-4 px-4 rounded-3 border-0 d-none text-center">
        <i class="bi bi-funnel fs-3 text-warning d-block mb-2"></i>
        <h6 class="fw-bold text-dark mb-1">Tidak Ada Sesi Presensi yang Sesuai dengan Filter</h6>
        <p class="mb-0 text-muted small">Coba ubah pilihan filter di atas atau cari dengan kata kunci lain.</p>
    </div>
</section>

<style>
/* Filter Pills Styling */
.btn-filter-pill {
    display: inline-flex;
    align-items: center;
    padding: 0.45rem 0.95rem;
    border-radius: 50rem;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    color: #475569;
    font-size: 0.85rem;
    transition: all 0.2s ease;
    user-select: none;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
}
.btn-filter-pill:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
    color: #0f172a;
}
.btn-filter-pill.active {
    background: #0f172a;
    color: #ffffff;
    border-color: #0f172a;
    box-shadow: 0 3px 10px rgba(15, 23, 42, 0.2);
}
.btn-filter-pill.active .badge {
    background: rgba(255, 255, 255, 0.25) !important;
    color: #ffffff !important;
}

/* Pulsing dot for active status */
.pulse-dot-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: #198754;
    box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.7);
    animation: pulse-green 2s infinite;
    display: inline-block;
    margin-right: 6px;
    flex-shrink: 0;
}
.status-dot-closed {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: #94a3b8;
    display: inline-block;
    margin-right: 6px;
    flex-shrink: 0;
}
@keyframes pulse-green {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(25, 135, 84, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(25, 135, 84, 0); }
}
</style>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let currentFilter = 'active'; // Default: Sesuai yang tampil di sidebar!
    let searchQuery = '';

    // Apply Filter & Search Logic
    function applyFiltering() {
        let visibleRowsCount = 0;

        $('.kepanitiaan-card').each(function() {
            const card = $(this);
            let cardVisibleRows = 0;

            card.find('.sesi-row').each(function() {
                const row = $(this);
                const isActive = row.attr('data-active') === '1';
                const searchData = (row.attr('data-search') || '').toLowerCase();

                let matchesFilter = true;
                if (currentFilter === 'active') {
                    matchesFilter = isActive;
                } else if (currentFilter === 'closed') {
                    matchesFilter = !isActive;
                }

                let matchesSearch = true;
                if (searchQuery.length > 0) {
                    matchesSearch = searchData.includes(searchQuery);
                }

                if (matchesFilter && matchesSearch) {
                    row.show();
                    cardVisibleRows++;
                    visibleRowsCount++;
                } else {
                    row.hide();
                }
            });

            // Hide whole card if no sessions match
            if (cardVisibleRows > 0) {
                card.show();
            } else {
                card.hide();
            }
        });

        if (visibleRowsCount === 0) {
            $('#filter-empty-alert').removeClass('d-none');
        } else {
            $('#filter-empty-alert').addClass('d-none');
        }
    }

    // Filter pill button click
    $('.btn-filter-pill').on('click', function() {
        $('.btn-filter-pill').removeClass('active');
        $(this).addClass('active');
        currentFilter = $(this).attr('data-filter');
        applyFiltering();
    });

    // Search input typing
    $('#input-search-sesi').on('keyup', function() {
        searchQuery = $(this).val().toLowerCase().trim();
        applyFiltering();
    });

    // Run initial filter on page load (Default: show active sessions that are on the sidebar)
    applyFiltering();

    // Toggle switch handler
    $('.toggle-status').on('change', function() {
        const checkbox = $(this);
        const sesiId = checkbox.data('id');
        const slug = checkbox.data('slug') || sesiId;
        const nama = checkbox.data('nama');
        const kepanitiaanId = checkbox.data('kepanitiaan-id');
        const isChecked = checkbox.is(':checked');

        // Disable checkbox while processing
        checkbox.prop('disabled', true);

        $.ajax({
            url: "{{ url('pengaturan-absensi') }}/" + sesiId + "/toggle",
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                checkbox.prop('disabled', false);
                if (response.success) {
                    const row = $('#row-sesi-{{ "" }}' + sesiId);
                    const badge = $('#badge-status-' + sesiId);
                    const pulse = $('.status-pulse-' + sesiId);

                    row.attr('data-active', response.is_active ? '1' : '0');

                    if (response.is_active) {
                        badge.removeClass('bg-secondary').addClass('bg-success');
                        badge.find('i').removeClass('bi-x-circle-fill').addClass('bi-check-circle-fill');
                        badge.find('.badge-status-text').text('Aktif (Tampil di Sidebar)');
                        pulse.removeClass('status-dot-closed').addClass('pulse-dot-indicator');
                    } else {
                        badge.removeClass('bg-success').addClass('bg-secondary');
                        badge.find('i').removeClass('bi-check-circle-fill').addClass('bi-x-circle-fill');
                        badge.find('.badge-status-text').text('Tutup (Sembunyi)');
                        pulse.removeClass('pulse-dot-indicator').addClass('status-dot-closed');
                    }

                    // Update counter badges
                    if (response.active_count !== undefined) {
                        $('#stat-active-sessions').text(response.active_count);
                        $('#badge-count-active').text(response.active_count);
                    }
                    if (response.closed_count !== undefined) {
                        $('#stat-closed-sessions').text(response.closed_count);
                        $('#badge-count-closed').text(response.closed_count);
                    }
                    if (response.total_count !== undefined) {
                        $('#stat-total-sessions').text(response.total_count);
                        $('#badge-count-total').text(response.total_count);
                    }

                    // Update kepanitiaan card summary count
                    const kepCard = $('[data-kepanitiaan-id="' + kepanitiaanId + '"]');
                    let currentActiveInKep = kepCard.find('.sesi-row[data-active="1"]').length;
                    kepCard.find('.kep-active-count').text(currentActiveInKep);

                    // Live Real-Time Sidebar update!
                    if (typeof window.updateSidebarActiveSession === 'function') {
                        window.updateSidebarActiveSession({
                            id: response.id || sesiId,
                            nama: response.nama || nama,
                            url: response.url || ("{{ url('presensi/sesi') }}/" + sesiId),
                            is_active: response.is_active,
                            active_count: response.active_count
                        });
                    }

                    // Re-apply filter if user is viewing active-only or closed-only
                    applyFiltering();

                    // Toast SweetAlert notification
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: response.message || 'Status sesi berhasil diubah.',
                        showConfirmButton: false,
                        timer: 2500,
                        timerProgressBar: true
                    });
                }
            },
            error: function(xhr) {
                checkbox.prop('disabled', false);
                // Revert switch state
                checkbox.prop('checked', !isChecked);

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Mengubah Status',
                    text: xhr.responseJSON?.message || 'Terjadi kesalahan sistem saat mengubah status absensi.',
                });
            }
        });
    });
});
</script>
@endpush
