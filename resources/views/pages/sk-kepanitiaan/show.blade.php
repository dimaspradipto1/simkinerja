@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
    <h1>Detail SK Kepanitiaan</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item">Pengaturan Sistem</li>
            <li class="breadcrumb-item"><a href="{{ route('sk-kepanitiaan.index') }}">SK Kepanitiaan</a></li>
            <li class="breadcrumb-item active">{{ \Illuminate\Support\Str::limit($sk->nama_kepanitiaan, 30) }}</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    <!-- Header Banner Card -->
    <div class="card shadow-sm border-0 mb-4 rounded-3 overflow-hidden">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center p-3" style="width: 58px; height: 58px;">
                        <i class="bi bi-file-earmark-pdf-fill fs-2"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold text-dark mb-1">{{ $sk->nama_kepanitiaan }}</h4>
                        <div class="d-flex flex-wrap align-items-center gap-2 text-muted small">
                            <span><i class="bi bi-file-earmark-text text-primary me-1"></i>No. SK: <strong>{{ $sk->nomor_sk ?: '-' }}</strong></span>
                            <span>&bull;</span>
                            <span><i class="bi bi-calendar3 text-success me-1"></i>T.A: <strong>{{ $sk->tahun_akademik ?: '-' }}</strong></span>
                            <span>&bull;</span>
                            <span><i class="bi bi-calendar-check text-info me-1"></i>Ditetapkan: <strong>{{ $sk->tanggal_sk ? $sk->tanggal_sk->format('d F Y') : '-' }}</strong></span>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    @if($sk->file_sk)
                        <a href="{{ asset('storage/' . $sk->file_sk) }}" target="_blank" class="btn btn-outline-danger btn-sm d-inline-flex align-items-center gap-1.5 shadow-2xs fw-semibold rounded-2 px-3 py-2">
                            <i class="bi bi-box-arrow-up-right"></i> Buka Berkas PDF
                        </a>
                    @endif

                    <form action="{{ route('sk-kepanitiaan.re-extract', $sk->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Pindai ulang berkas SK ini dan sinkronkan kembali seluruh personel?');">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1.5 shadow-2xs fw-semibold rounded-2 px-3 py-2" title="Pindai Ulang Dokumen PDF">
                            <i class="bi bi-arrow-repeat"></i> Pindai Ulang
                        </button>
                    </form>

                    <a href="{{ route('sk-kepanitiaan.edit', $sk->id) }}" class="btn btn-warning btn-sm text-white d-inline-flex align-items-center gap-1.5 shadow-2xs fw-semibold rounded-2 px-3 py-2">
                        <i class="bi bi-pencil-square"></i> Edit SK
                    </a>

                    <a href="{{ route('sk-kepanitiaan.index') }}" class="btn btn-secondary btn-sm d-inline-flex align-items-center gap-1.5 shadow-2xs fw-semibold rounded-2 px-3 py-2">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Metric Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 rounded-3" style="border-left: 4px solid #0d6efd !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem;">Total Personel di SK</div>
                        <div class="fs-4 fw-bold text-dark mt-1" id="stat-total">{{ $sk->total_anggota }} Personel</div>
                    </div>
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 46px; height: 46px;">
                        <i class="bi bi-people-fill fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 rounded-3" style="border-left: 4px solid #198754 !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem;">Terhubung ke Akun SIMKINERJA</div>
                        <div class="fs-4 fw-bold text-success mt-1" id="stat-matched">{{ $sk->total_matched }} Pegawai</div>
                    </div>
                    <div class="rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width: 46px; height: 46px;">
                        <i class="bi bi-check-circle-fill fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            @php
                $unmatched = max(0, $sk->total_anggota - $sk->total_matched);
            @endphp
            <div class="card border-0 shadow-sm h-100 rounded-3" style="border-left: 4px solid #ffc107 !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem;">Belum Terhubung / Eksternal</div>
                        <div class="fs-4 fw-bold text-warning mt-1" id="stat-unmatched">{{ $unmatched }} Personel</div>
                    </div>
                    <div class="rounded-circle bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center" style="width: 46px; height: 46px;">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Personel Table Card -->
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
            <div>
                <h5 class="card-title m-0 fw-bold text-dark fs-6 d-flex align-items-center gap-2">
                    <i class="bi bi-people-fill text-primary"></i> Susunan Personel &amp; Penugasan Akun
                </h5>
                <p class="text-muted small mb-0 mt-0.5">Daftar personel yang terdeteksi dari dokumen SK dan status penugasan pada akun pegawai.</p>
            </div>
        </div>

        <div class="card-body p-4">
            @php
                $members = $sk->hasil_ekstraksi ?: [];
                $uniquePositions = [];
                foreach ($members as $m) {
                    $p = trim($m['posisi'] ?? '');
                    if ($p !== '') {
                        $uniquePositions[$p] = true;
                    }
                }
                $uniquePositions = array_keys($uniquePositions);
                sort($uniquePositions);
            @endphp

            @if(empty($members))
                <div class="text-center py-5">
                    <i class="bi bi-file-earmark-x text-muted fs-1 d-block mb-2"></i>
                    <p class="text-muted mb-0">Belum ada personel yang diekstraksi dari dokumen ini.</p>
                    <form action="{{ route('sk-kepanitiaan.re-extract', $sk->id) }}" method="POST" class="mt-3">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-arrow-repeat me-1"></i> Jalankan Ekstraksi Sekarang
                        </button>
                    </form>
                </div>
            @else
                <!-- Search & Filter Controls -->
                <div class="p-3 mb-3 bg-light bg-opacity-75 rounded-3 border">
                    <div class="row g-2 align-items-end">
                        <div class="col-12 col-md-5 col-lg-5">
                            <label class="form-label small fw-semibold text-secondary mb-1">
                                <i class="bi bi-search me-1 text-primary"></i>Pencarian Personel
                            </label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0 text-muted">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" id="search-personel" class="form-control border-start-0 ps-0" placeholder="Ketik nama personel, posisi, akun pegawai, unit..." autocomplete="off">
                                <button class="btn btn-outline-secondary border-start-0 d-none" type="button" id="btn-clear-search" title="Hapus pencarian">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>

                        <div class="col-6 col-md-3 col-lg-3">
                            <label class="form-label small fw-semibold text-secondary mb-1">
                                <i class="bi bi-funnel me-1 text-success"></i>Status Penugasan Akun
                            </label>
                            <select class="form-select form-select-sm" id="filter-status">
                                <option value="all">Semua Status ({{ count($members) }})</option>
                                <option value="matched">🟢 Terhubung ke SIMKINERJA ({{ $sk->total_matched }})</option>
                                <option value="unmatched">🟡 Belum Terhubung ({{ $unmatched }})</option>
                            </select>
                        </div>

                        <div class="col-6 col-md-2 col-lg-2">
                            <label class="form-label small fw-semibold text-secondary mb-1">
                                <i class="bi bi-person-badge me-1 text-info"></i>Posisi / Jabatan
                            </label>
                            <select class="form-select form-select-sm" id="filter-posisi">
                                <option value="all">Semua Posisi ({{ count($uniquePositions) }})</option>
                                @foreach($uniquePositions as $pos)
                                    <option value="{{ strtolower($pos) }}">{{ $pos }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-2 col-lg-2">
                            <label class="form-label small fw-semibold text-secondary mb-1 d-none d-md-block invisible">
                                &nbsp;
                            </label>
                            <button type="button" id="btn-reset-filter" class="btn btn-sm btn-light text-muted border border-secondary-subtle w-100 d-inline-flex align-items-center justify-content-center gap-1.5 fw-semibold shadow-2xs" style="height: 31px; border-radius: 6px;" onclick="resetPersonelFilter()" disabled title="Belum ada filter yang aktif">
                                <i class="bi bi-arrow-counterclockwise"></i> <span>Reset Filter</span>
                            </button>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top text-muted small">
                        <div>
                            Menampilkan <strong id="filtered-count" class="text-dark">{{ count($members) }}</strong> dari <strong>{{ count($members) }}</strong> personel
                        </div>
                        <div id="filter-active-indicator" class="d-none">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                <i class="bi bi-funnel-fill me-1"></i>Filter Aktif
                            </span>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle w-100" id="table-personel" style="font-size: 0.88rem;">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 45px;">No</th>
                                <th style="width: 220px;">Jabatan / Posisi dalam SK</th>
                                <th style="width: 260px;">Nama Personel pada Dokumen SK</th>
                                <th>Akun Pegawai Terhubung (SIMKINERJA)</th>
                                <th class="text-center" style="width: 140px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($members as $index => $item)
                                @php
                                    $isMatched = !empty($item['matched']) && !empty($item['user_id']);
                                @endphp
                                <tr id="row-personel-{{ $index }}"
                                    class="personel-item-row"
                                    data-index="{{ $index }}"
                                    data-nama="{{ strtolower($item['nama_sk'] ?? '') }}"
                                    data-posisi="{{ strtolower($item['posisi'] ?? '') }}"
                                    data-status="{{ $isMatched ? 'matched' : 'unmatched' }}"
                                    data-user-name="{{ strtolower($item['user_name'] ?? '') }}"
                                    data-user-jabatan="{{ strtolower($item['user_jabatan'] ?? '') }}"
                                    data-user-unit="{{ strtolower($item['user_unit'] ?? '') }}">
                                    <td class="text-center text-muted fw-semibold row-number">{{ $loop->iteration }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark border px-2.5 py-1.5 fw-semibold" style="font-size: 0.82rem;">
                                            {{ $item['posisi'] ?? 'Anggota' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $item['nama_sk'] ?? '-' }}</div>
                                    </td>
                                    <td id="cell-user-{{ $index }}">
                                        @if($isMatched)
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                                    <i class="bi bi-check-lg"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark" style="font-size: 0.88rem;">{{ $item['user_name'] ?? '-' }}</div>
                                                    <small class="text-muted" style="font-size: 0.75rem;">{{ $item['user_jabatan'] ?? '-' }} &bull; {{ $item['user_unit'] ?? '-' }}</small>
                                                </div>
                                            </div>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle py-1.5 px-2.5">
                                                <i class="bi bi-exclamation-circle me-1"></i> Belum Terhubung
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center" id="cell-action-{{ $index }}">
                                        <div class="d-inline-flex gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="openLinkModal({{ $index }}, '{{ addslashes($item['nama_sk'] ?? '') }}', '{{ addslashes($item['posisi'] ?? '') }}', {{ $item['user_id'] ? $item['user_id'] : 'null' }})" title="Hubungkan atau Ubah Akun Pegawai" style="border-radius: 6px; padding: 4px 8px;">
                                                <i class="bi bi-link-45deg"></i> {{ $isMatched ? 'Ubah' : 'Hubungkan' }}
                                            </button>
                                            @if($isMatched)
                                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="unlinkUser({{ $index }}, '{{ addslashes($item['nama_sk'] ?? '') }}')" title="Lepas Tautan Akun" style="border-radius: 6px; padding: 4px 8px;">
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            <!-- Empty Filter State -->
                            <tr id="row-empty-filter" style="display: none;">
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-muted mb-2">
                                        <i class="bi bi-search fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                        <strong>Tidak ditemukan personel yang sesuai</strong>
                                        <p class="small text-muted mb-0">Coba ubah kata kunci pencarian atau sesuaikan opsi filter status/posisi.</p>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 mt-2" onclick="resetPersonelFilter()">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Pencarian &amp; Filter
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</section>

<!-- Modal Hubungkan Manual Akun Pegawai -->
<div class="modal fade" id="modalLinkUser" tabindex="-1" aria-labelledby="modalLinkUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 14px;">
            <div class="modal-header bg-light border-bottom py-3 px-4">
                <h5 class="modal-title fw-bold text-dark fs-6" id="modalLinkUserLabel">
                    <i class="bi bi-person-check text-primary me-2"></i> Hubungkan ke Akun Pegawai
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formLinkUser">
                @csrf
                <input type="hidden" id="modal_index" name="index">
                <div class="modal-body p-4">
                    <div class="alert alert-light border p-3 rounded-3 mb-3">
                        <div class="small text-muted mb-1">Nama pada Dokumen SK:</div>
                        <div class="fw-bold text-dark fs-6" id="modal_nama_sk">-</div>
                        <div class="small text-muted mt-2 mb-1">Posisi / Jabatan SK:</div>
                        <span class="badge bg-primary-subtle text-primary" id="modal_posisi_sk">-</span>
                    </div>

                    <div class="mb-3">
                        <label for="modal_user_id" class="form-label fw-bold text-dark small">Pilih Akun Pegawai SIMKINERJA <span class="text-danger">*</span></label>
                        <select class="form-select select2-user" id="modal_user_id" name="user_id" style="width: 100%; border-radius: 8px;" required>
                            <option value="">-- Cari Nama Pegawai / NIDN / Unit --</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->jabatan ?? '-' }} - {{ $u->unit ?? '-' }})</option>
                            @endforeach
                        </select>
                        <div class="form-text text-muted" style="font-size: 0.76rem;">
                            Posisi kepanitiaan <strong>"{{ $sk->nama_kepanitiaan }}"</strong> akan otomatis ditambahkan ke akun pegawai yang dipilih.
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-top py-2.5 px-4">
                    <button type="button" class="btn btn-secondary btn-sm rounded-2" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold rounded-2 px-3" id="btnSubmitLink">
                        <i class="bi bi-check-circle me-1"></i> Simpan Penugasan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let activeModalInstance = null;

    function openLinkModal(index, namaSk, posisiSk, currentUserId) {
        $('#modal_index').val(index);
        $('#modal_nama_sk').text(namaSk);
        $('#modal_posisi_sk').text(posisiSk);
        $('#modal_user_id').val(currentUserId ? currentUserId : '').trigger('change');

        activeModalInstance = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalLinkUser'));
        activeModalInstance.show();
    }

    $('#formLinkUser').on('submit', function(e) {
        e.preventDefault();
        const index = $('#modal_index').val();
        const userId = $('#modal_user_id').val();

        if (!userId) {
            Swal.fire('Peringatan', 'Silakan pilih akun pegawai terlebih dahulu.', 'warning');
            return;
        }

        const btn = $('#btnSubmitLink');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

        $.ajax({
            url: "{{ route('sk-kepanitiaan.link-user', $sk->id) }}",
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                index: index,
                user_id: userId
            },
            dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> Simpan Penugasan');
                if (res.success) {
                    if (activeModalInstance) activeModalInstance.hide();

                    // Update UI cells
                    const u = res.user;
                    const cellUserHtml = `
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                <i class="bi bi-check-lg"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark" style="font-size: 0.88rem;">${u.name}</div>
                                <small class="text-muted" style="font-size: 0.75rem;">${u.jabatan || '-'} &bull; ${u.unit || '-'}</small>
                            </div>
                        </div>
                    `;
                    $('#cell-user-' + index).html(cellUserHtml);

                    const namaSk = $('#modal_nama_sk').text();
                    const posisiSk = $('#modal_posisi_sk').text();
                    const cellActionHtml = `
                        <div class="d-inline-flex gap-1">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="openLinkModal(${index}, '${namaSk.replace(/'/g, "\\'")}', '${posisiSk.replace(/'/g, "\\'")}', ${u.id})" title="Hubungkan atau Ubah Akun Pegawai" style="border-radius: 6px; padding: 4px 8px;">
                                <i class="bi bi-link-45deg"></i> Ubah
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="unlinkUser(${index}, '${namaSk.replace(/'/g, "\\'")}')" title="Lepas Tautan Akun" style="border-radius: 6px; padding: 4px 8px;">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </div>
                    `;
                    $('#cell-action-' + index).html(cellActionHtml);

                    // Update metrics
                    $('#stat-matched').text(res.total_matched + ' Pegawai');
                    const totalAll = parseInt($('#stat-total').text()) || 0;
                    const unmatchedCount = Math.max(0, totalAll - res.total_matched);
                    $('#stat-unmatched').text(unmatchedCount + ' Personel');

                    // Update filter option counters
                    $('#filter-status option[value="matched"]').text(`🟢 Terhubung ke SIMKINERJA (${res.total_matched})`);
                    $('#filter-status option[value="unmatched"]').text(`🟡 Belum Terhubung (${unmatchedCount})`);

                    // Update row data attributes for filter
                    const targetRow = $('#row-personel-' + index);
                    targetRow.attr('data-status', 'matched');
                    targetRow.attr('data-user-name', (u.name || '').toLowerCase());
                    targetRow.attr('data-user-jabatan', (u.jabatan || '').toLowerCase());
                    targetRow.attr('data-user-unit', (u.unit || '').toLowerCase());

                    applyPersonelFilter();

                    Swal.fire({
                        icon: 'success',
                        title: 'Terhubung!',
                        text: res.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire('Gagal', res.message || 'Gagal menghubungkan akun.', 'error');
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> Simpan Penugasan');
                const errMsg = xhr.responseJSON?.message || 'Terjadi kesalahan sistem.';
                Swal.fire('Error', errMsg, 'error');
            }
        });
    });

    function unlinkUser(index, namaSk) {
        Swal.fire({
            title: 'Lepas Tautan Akun?',
            text: 'Lepaskan penugasan akun pegawai untuk personel "' + namaSk + '"?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#6c757d',
            cancelColor: '#dc3545',
            confirmButtonText: 'Ya, Lepaskan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('sk-kepanitiaan.unlink-user', $sk->id) }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        index: index
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (res.success) {
                            $('#cell-user-' + index).html(`
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle py-1.5 px-2.5">
                                    <i class="bi bi-exclamation-circle me-1"></i> Belum Terhubung
                                </span>
                            `);

                            const posisiText = $('#row-personel-' + index + ' td:nth-child(2) span').text().trim();
                            $('#cell-action-' + index).html(`
                                <div class="d-inline-flex gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="openLinkModal(${index}, '${namaSk.replace(/'/g, "\\'")}', '${posisiText.replace(/'/g, "\\'")}', null)" title="Hubungkan atau Ubah Akun Pegawai" style="border-radius: 6px; padding: 4px 8px;">
                                        <i class="bi bi-link-45deg"></i> Hubungkan
                                    </button>
                                </div>
                            `);

                            $('#stat-matched').text(res.total_matched + ' Pegawai');
                            const totalAll = parseInt($('#stat-total').text()) || 0;
                            const unmatchedCount = Math.max(0, totalAll - res.total_matched);
                            $('#stat-unmatched').text(unmatchedCount + ' Personel');

                            // Update filter option counters
                            $('#filter-status option[value="matched"]').text(`🟢 Terhubung ke SIMKINERJA (${res.total_matched})`);
                            $('#filter-status option[value="unmatched"]').text(`🟡 Belum Terhubung (${unmatchedCount})`);

                            // Update row data attributes for filter
                            const targetRow = $('#row-personel-' + index);
                            targetRow.attr('data-status', 'unmatched');
                            targetRow.attr('data-user-name', '');
                            targetRow.attr('data-user-jabatan', '');
                            targetRow.attr('data-user-unit', '');

                            applyPersonelFilter();

                            Swal.fire({
                                icon: 'success',
                                title: 'Dilepas!',
                                text: res.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire('Gagal', res.message || 'Gagal melepas tautan.', 'error');
                        }
                    },
                    error: function(xhr) {
                        const errMsg = xhr.responseJSON?.message || 'Terjadi kesalahan sistem.';
                        Swal.fire('Error', errMsg, 'error');
                    }
                });
            }
        });
    }

    // --- Search & Filter Logic ---
    function applyPersonelFilter() {
        const query = ($('#search-personel').val() || '').toLowerCase().trim();
        const statusFilter = $('#filter-status').val(); // 'all', 'matched', 'unmatched'
        const posisiFilter = ($('#filter-posisi').val() || '').toLowerCase().trim();

        if (query) {
            $('#btn-clear-search').removeClass('d-none');
        } else {
            $('#btn-clear-search').addClass('d-none');
        }

        const isFiltered = (query !== '' || statusFilter !== 'all' || posisiFilter !== 'all');
        if (isFiltered) {
            $('#btn-reset-filter')
                .prop('disabled', false)
                .removeClass('btn-light text-muted border-secondary-subtle')
                .addClass('btn-outline-danger')
                .attr('title', 'Klik untuk mengembalikan semua filter');
            $('#filter-active-indicator').removeClass('d-none');
        } else {
            $('#btn-reset-filter')
                .prop('disabled', true)
                .removeClass('btn-outline-danger')
                .addClass('btn-light text-muted border-secondary-subtle')
                .attr('title', 'Belum ada filter yang aktif');
            $('#filter-active-indicator').addClass('d-none');
        }

        let visibleCount = 0;
        let visibleNumber = 1;

        $('.personel-item-row').each(function() {
            const row = $(this);
            const nama = row.attr('data-nama') || '';
            const posisi = row.attr('data-posisi') || '';
            const status = row.attr('data-status') || '';
            const userName = row.attr('data-user-name') || '';
            const userJabatan = row.attr('data-user-jabatan') || '';
            const userUnit = row.attr('data-user-unit') || '';

            // Check search text across all relevant fields
            let matchSearch = true;
            if (query) {
                matchSearch = nama.includes(query) || 
                              posisi.includes(query) || 
                              userName.includes(query) || 
                              userJabatan.includes(query) || 
                              userUnit.includes(query);
            }

            // Check status
            let matchStatus = true;
            if (statusFilter !== 'all') {
                matchStatus = (status === statusFilter);
            }

            // Check position
            let matchPosisi = true;
            if (posisiFilter !== 'all') {
                matchPosisi = posisi.includes(posisiFilter);
            }

            if (matchSearch && matchStatus && matchPosisi) {
                row.show();
                row.find('.row-number').text(visibleNumber++);
                visibleCount++;
            } else {
                row.hide();
            }
        });

        $('#filtered-count').text(visibleCount);

        if (visibleCount === 0) {
            $('#row-empty-filter').show();
        } else {
            $('#row-empty-filter').hide();
        }
    }

    function resetPersonelFilter() {
        $('#search-personel').val('');
        $('#filter-status').val('all');
        $('#filter-posisi').val('all');
        applyPersonelFilter();
    }

    $('#search-personel').on('input keyup', function() {
        applyPersonelFilter();
    });

    $('#btn-clear-search').on('click', function() {
        $('#search-personel').val('').focus();
        applyPersonelFilter();
    });

    $('#filter-status, #filter-posisi').on('change', function() {
        applyPersonelFilter();
    });
</script>
@endpush
