@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div>
        <h1 class="fs-3 fw-bold text-dark mb-1">
            <i class="bi bi-calendar2-check text-primary me-2"></i>Data Absensi: {{ $sesi->nama_sesi }}
        </h1>
        <nav>
            <ol class="breadcrumb mb-0" style="font-size: 0.88rem;">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('kepanitiaan.index') }}">Rencana Kerja Kepanitiaan</a></li>
                <li class="breadcrumb-item active">{{ $sesi->nama_sesi }}</li>
            </ol>
        </nav>
    </div>

    <div class="d-flex align-items-center gap-2 flex-wrap">
        @if(isset($allSesis) && $allSesis->count() > 1)
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle d-inline-flex align-items-center gap-1 shadow-sm font-medium" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius: 8px; font-size: 0.86rem; padding: 7px 14px;">
                <i class="bi bi-arrow-left-right text-primary"></i> Ganti Sesi
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="min-width: 240px; border-radius: 10px;">
                <li class="dropdown-header text-uppercase fw-bold text-muted" style="font-size: 0.75rem;">Daftar Sesi Presensi Aktif</li>
                @foreach($allSesis as $otherSess)
                <li>
                    <a class="dropdown-item d-flex align-items-center justify-content-between py-2 {{ $otherSess->id === $sesi->id ? 'active fw-bold' : '' }}" href="{{ route('presensi.sesi.show', $otherSess->id) }}">
                        <span class="text-truncate me-2">{{ $otherSess->nama_sesi }}</span>
                        @if($otherSess->id === $sesi->id)
                            <i class="bi bi-check2"></i>
                        @endif
                    </a>
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        <a href="{{ route('kepanitiaan.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1 shadow-sm font-medium" style="border-radius: 8px; font-size: 0.86rem; padding: 7px 14px;">
            <i class="bi bi-arrow-left"></i> Rencana Kerja
        </a>
    </div>
</div>

<section class="section">
    <!-- Header Activity Banner Card -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #ffffff;">
        <div class="card-body p-4">
            <div class="row align-items-center g-3">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        @if($sesi->is_active)
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 font-semibold" style="font-size: 0.78rem;">
                                <i class="bi bi-broadcast me-1"></i>Sesi Presensi Aktif (Buka)
                            </span>
                        @else
                            <span class="badge bg-secondary-subtle text-white border border-secondary rounded-pill px-3 py-1 font-semibold" style="font-size: 0.78rem;">
                                <i class="bi bi-lock-fill me-1"></i>Sesi Presensi Ditutup (Terkunci)
                            </span>
                        @endif
                        @if($sesi->tanggal)
                            <span class="badge bg-white bg-opacity-20 text-white rounded-pill px-3 py-1 font-medium" style="font-size: 0.78rem;">
                                <i class="bi bi-calendar3 me-1"></i>{{ date('d M Y', strtotime($sesi->tanggal)) }}
                            </span>
                        @endif
                    </div>
                    <h3 class="fw-bold mb-1 text-white">{{ $sesi->nama_sesi }}</h3>
                    <p class="text-white-50 mb-0" style="font-size: 0.92rem;">
                        <i class="bi bi-bookmark-check me-1 text-primary-subtle"></i>Kegiatan: <strong>{{ $sesi->kepanitiaan->uraian_tugas ?? 'Kepanitiaan' }}</strong>
                        @if(!empty($sesi->kepanitiaan->unit))
                            <span class="ms-2 badge bg-white bg-opacity-10 text-white font-monospace">{{ $sesi->kepanitiaan->unit }}</span>
                        @endif
                    </p>
                </div>

                <div class="col-lg-4 text-lg-end">
                    <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                        <button type="button" class="btn btn-primary text-white fw-bold d-inline-flex align-items-center gap-1 shadow-sm btn-scan-barcode-modal"
                                data-slug="{{ $sesi->legacy_slug ?? 'sesi-' . $sesi->id }}"
                                data-token="{{ $sesi->token }}"
                                data-scan-url="{{ route('presensi.scan-proses', ['token' => $sesi->token]) }}"
                                data-title="{{ $sesi->nama_sesi }}"
                                onclick="window.openScanBarcodeModal(this)"
                                style="border-radius: 8px; font-size: 0.85rem; padding: 8px 14px;">
                            <i class="bi bi-qr-code-scan"></i> Scan Barcode
                        </button>

                        <button type="button" class="btn btn-light text-dark fw-bold d-inline-flex align-items-center gap-1 shadow-sm btn-barcode-absensi"
                                data-slug="{{ $sesi->legacy_slug ?? 'sesi-' . $sesi->id }}"
                                data-token="{{ $sesi->token }}"
                                data-scan-url="{{ route('presensi.scan-proses', ['token' => $sesi->token]) }}"
                                data-title="{{ $sesi->nama_sesi }}"
                                data-url="{{ route('presensi.scan-proses', ['token' => $sesi->token]) }}"
                                onclick="window.openBarcodeModal(this)"
                                style="border-radius: 8px; font-size: 0.85rem; padding: 8px 14px;">
                            <i class="bi bi-qr-code"></i> Barcode Absensi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Metric Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid #0d6efd !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem;">Total Kehadiran</div>
                        <div class="fs-4 fw-bold text-dark mt-1" id="stat-total-presensi">{{ $totalPresensi }}</div>
                    </div>
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-people-fill fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid #198754 !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem;">Hadir Datang</div>
                        <div class="fs-4 fw-bold text-success mt-1" id="stat-hadir-datang">{{ $totalHadirDatang }}</div>
                    </div>
                    <div class="rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-box-arrow-in-right fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid #0dcaf0 !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem;">Hadir Pulang</div>
                        <div class="fs-4 fw-bold text-info mt-1" id="stat-hadir-pulang">{{ $totalHadirPulang }}</div>
                    </div>
                    <div class="rounded-circle bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-box-arrow-right fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid #ffc107 !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem;">Izin / Sakit / Alpha</div>
                        <div class="fs-4 fw-bold text-warning mt-1" id="stat-izin-sakit">{{ $totalIzinSakit }}</div>
                    </div>
                    <div class="rounded-circle bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-file-earmark-medical fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Table Card -->
    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
        <div class="card-header bg-white py-3 px-4 d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 border-bottom">
            <div>
                <h5 class="card-title m-0 p-0 fs-5 fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-card-checklist text-primary"></i>Daftar Kehadiran Pegawai
                </h5>
                <p class="text-muted small mb-0 mt-0.5">Semua data absensi yang tercatat pada sesi ini secara real-time.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('presensi.sesi.export', $sesi->id) }}" class="btn btn-success d-inline-flex align-items-center gap-1 shadow-sm font-medium" style="border-radius: 8px; font-size: 0.85rem; padding: 7px 14px;">
                    <i class="bi bi-file-earmark-excel"></i> Export Excel
                </a>
                <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-1 shadow-sm font-medium" onclick="window.openManualPresensiModal()" style="border-radius: 8px; font-size: 0.85rem; padding: 7px 14px;">
                    <i class="bi bi-plus-circle"></i> + Input Presensi Manual
                </button>
            </div>
        </div>

        <div class="card-body p-4">
            <div class="table-responsive">
                <table id="presensi-session-table" class="table table-hover align-middle w-100" style="font-size: 0.88rem;">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th class="text-center" style="width: 130px;">Tanggal</th>
                            <th>Pegawai</th>
                            <th class="text-center" style="width: 160px;">Presensi Datang</th>
                            <th class="text-center" style="width: 160px;">Presensi Pulang</th>
                            <th>Keterangan / Bukti</th>
                            <th class="text-center" style="width: 90px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<!-- Modal Input / Edit Presensi Manual -->
<div class="modal fade" id="modalManualPresensi" tabindex="-1" aria-labelledby="modalManualPresensiLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 12px;">
            <div class="modal-header bg-light border-bottom py-3">
                <h5 class="modal-title fw-bold text-dark fs-6" id="modalManualPresensiLabel">
                    <i class="bi bi-person-check text-primary me-2"></i>Input Presensi Manual
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formManualPresensi">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="manual_user_id" class="form-label fw-semibold" style="font-size: 0.85rem;">Pilih Pegawai / Panitia <span class="text-danger">*</span></label>
                        <select class="form-select" id="manual_user_id" name="user_id" required style="border-radius: 8px;">
                            <option value="">-- Cari Nama Pegawai --</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->jabatan ?? '-' }} - {{ $u->unit ?? '-' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="manual_hadir_datang" class="form-label fw-semibold" style="font-size: 0.85rem;">Status Datang</label>
                            <select class="form-select" id="manual_hadir_datang" name="hadir_datang" style="border-radius: 8px;">
                                <option value="">-- Pilih Status --</option>
                                <option value="Hadir">Hadir</option>
                                <option value="Izin">Izin</option>
                                <option value="Sakit">Sakit</option>
                                <option value="Alpha">Alpha</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="manual_waktu_datang" class="form-label fw-semibold" style="font-size: 0.85rem;">Waktu Datang (WIB)</label>
                            <input type="time" class="form-control" id="manual_waktu_datang" name="waktu_datang" style="border-radius: 8px;">
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="manual_hadir_pulang" class="form-label fw-semibold" style="font-size: 0.85rem;">Status Pulang</label>
                            <select class="form-select" id="manual_hadir_pulang" name="hadir_pulang" style="border-radius: 8px;">
                                <option value="">-- Pilih Status --</option>
                                <option value="Hadir">Hadir</option>
                                <option value="Izin">Izin</option>
                                <option value="Sakit">Sakit</option>
                                <option value="Alpha">Alpha</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="manual_waktu_pulang" class="form-label fw-semibold" style="font-size: 0.85rem;">Waktu Pulang (WIB)</label>
                            <input type="time" class="form-control" id="manual_waktu_pulang" name="waktu_pulang" style="border-radius: 8px;">
                        </div>
                    </div>

                    <div class="mb-2">
                        <label for="manual_catatan" class="form-label fw-semibold" style="font-size: 0.85rem;">Catatan / Keterangan (Opsional)</label>
                        <textarea class="form-control" id="manual_catatan" name="catatan" rows="2" placeholder="Catatan kehadiran atau alasan izin/sakit..." style="border-radius: 8px;"></textarea>
                    </div>
                </div>

                <div class="modal-footer bg-light border-top py-2 px-4">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" style="border-radius: 6px;">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold" id="btnSubmitManualPresensi" style="border-radius: 6px;">
                        <i class="bi bi-check-circle me-1"></i> Simpan Presensi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Inisialisasi DataTables Presensi Sesi
    const presensiTable = $('#presensi-session-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('presensi.sesi.data', $sesi->id) }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center fw-semibold text-muted' },
            { data: 'tanggal', name: 'created_at', className: 'text-center' },
            { data: 'user_info', name: 'user_info' },
            { data: 'status_datang', name: 'hadir_datang', className: 'text-center' },
            { data: 'status_pulang', name: 'hadir_pulang', className: 'text-center' },
            { data: 'catatan_col', name: 'catatan_hadir_datang', orderable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' },
        ],
        order: [[0, 'desc']],
        pageLength: 25,
        language: {
            processing: '<div class="spinner-border spinner-border-sm text-primary me-2"></div> Memuat data presensi...',
            zeroRecords: '<div class="text-center py-4 text-muted"><i class="bi bi-people fs-3 d-block mb-1"></i>Belum ada data kehadiran pada sesi ini.</div>',
            info: "Menampilkan <strong>_START_-_END_</strong> dari <strong>_TOTAL_</strong> pegawai",
            infoEmpty: "Menampilkan 0 data",
            infoFiltered: "(disaring dari _MAX_ pegawai)",
            search: "Cari Pegawai:",
            paginate: {
                previous: '<i class="bi bi-chevron-left"></i>',
                next: '<i class="bi bi-chevron-right"></i>'
            }
        }
    });

    // Open Manual Presensi Modal (New)
    window.openManualPresensiModal = function() {
        $('#formManualPresensi')[0].reset();
        $('#manual_user_id').prop('disabled', false).val('');
        $('#modalManualPresensiLabel').html('<i class="bi bi-person-check text-primary me-2"></i>Input Presensi Manual');
        $('#btnSubmitManualPresensi').html('<i class="bi bi-check-circle me-1"></i> Simpan Presensi');

        // Set default time to current time
        const now = new Date();
        const currentTime = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
        $('#manual_waktu_datang').val(currentTime);
        $('#manual_hadir_datang').val('Hadir');

        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalManualPresensi'));
        modal.show();
    };

    // Open Manual Presensi Modal (Edit)
    window.editPresensiRow = function(data) {
        $('#formManualPresensi')[0].reset();
        $('#manual_user_id').val(data.user_id).prop('disabled', true);
        $('#manual_hadir_datang').val(data.hadir_datang || '');
        $('#manual_waktu_datang').val(data.waktu_datang || '');
        $('#manual_hadir_pulang').val(data.hadir_pulang || '');
        $('#manual_waktu_pulang').val(data.waktu_pulang || '');
        $('#manual_catatan').val(data.catatan || '');

        $('#modalManualPresensiLabel').html('<i class="bi bi-pencil-square text-primary me-2"></i>Ubah Data Presensi Pegawai');
        $('#btnSubmitManualPresensi').html('<i class="bi bi-check-circle me-1"></i> Perbarui Presensi');

        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalManualPresensi'));
        modal.show();
    };

    // Submit Manual Presensi
    $('#formManualPresensi').on('submit', function(e) {
        e.preventDefault();
        $('#manual_user_id').prop('disabled', false); // Ensure value is included in serialization
        const formData = $(this).serialize();

        const btn = $('#btnSubmitManualPresensi');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

        $.ajax({
            url: "{{ route('presensi.sesi.manual', $sesi->id) }}",
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> Simpan Presensi');
                if (res.success) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalManualPresensi'));
                    if (modal) modal.hide();
                    presensiTable.ajax.reload(null, false);

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: res.message,
                        timer: 1800,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire('Gagal', res.message || 'Gagal menyimpan presensi.', 'error');
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> Simpan Presensi');
                const errMsg = xhr.responseJSON?.message || 'Terjadi kesalahan sistem.';
                Swal.fire('Error', errMsg, 'error');
            }
        });
    });

    // Delete Presensi Row
    window.deletePresensiRow = function(id) {
        Swal.fire({
            title: 'Hapus data presensi?',
            text: 'Catatan kehadiran pegawai ini pada sesi ini akan dihapus permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-trash"></i> Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('presensi/presensi') }}/" + id,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (res.success) {
                            presensiTable.ajax.reload(null, false);
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus!',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire('Gagal', res.message || 'Gagal menghapus data.', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Terjadi kesalahan sistem saat menghapus.', 'error');
                    }
                });
            }
        });
    };
});
</script>
@endpush
