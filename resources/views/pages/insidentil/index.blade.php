@extends('layouts.dashboard.template')

@section('content')
<style>
    .card-header-green {
        background-color: #15432d;
        color: #ffffff;
        padding: 10px 16px;
        border-top-left-radius: 6px;
        border-top-right-radius: 6px;
        font-weight: 600;
        font-size: 0.95rem;
    }
    .btn-green-add {
        background-color: #2d6a4f;
        color: #ffffff !important;
        border: none;
        padding: 4px 12px;
        font-size: 0.85rem;
        border-radius: 4px;
    }
    .btn-green-add:hover {
        background-color: #1b4332;
    }
    #insidentil-table tbody tr td {
        vertical-align: middle;
        padding: 10px 14px;
        border-bottom: 1px solid #edf2f7;
    }
</style>

<div class="pagetitle">
    <h1>Rencana Kerja Insidentil</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Rencana Kerja Insidentil</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    <!-- Stat Cards -->
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-success text-white">
                <div class="card-body p-3 text-center">
                    <h6 class="text-uppercase fw-bold text-white-50 mb-1">Selesai</h6>
                    <h2 class="mb-0 fw-bold" id="stat-selesai">0</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-primary text-white">
                <div class="card-body p-3 text-center">
                    <h6 class="text-uppercase fw-bold text-white-50 mb-1">Dalam Proses</h6>
                    <h2 class="mb-0 fw-bold" id="stat-proses">0</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-secondary text-white">
                <div class="card-body p-3 text-center">
                    <h6 class="text-uppercase fw-bold text-white-50 mb-1">Belum Dimulai</h6>
                    <h2 class="mb-0 fw-bold" id="stat-belum">0</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-dark text-white">
                <div class="card-body p-3 text-center">
                    <h6 class="text-uppercase fw-bold text-white-50 mb-1">Capaian Rekap</h6>
                    <h2 class="mb-0 fw-bold" id="stat-percent">0%</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Action Header -->
    <div class="card shadow-sm border-0">
        <div class="card-header-green d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="header-title-text"><i class="bi bi-lightning-charge me-2"></i>Daftar Rencana Kerja Insidentil</span>
            <div class="header-action-group d-flex gap-2">
                <a href="{{ route('insidentil.create') }}" class="btn btn-green-add btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Data
                </a>
                <button type="button" class="btn btn-danger btn-sm text-white" id="btn-bulk-delete" style="display: none;">
                    <i class="bi bi-trash me-1"></i> Hapus Terpilih
                </button>
                <a href="#" id="btn-export-pdf" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-file-pdf me-1"></i> PDF
                </a>
                <a href="#" id="btn-export-excel" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-file-excel me-1"></i> Excel
                </a>
            </div>
        </div>

        <div class="card-body mt-3">
            <!-- Filter Options -->
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label for="filter_periode" class="form-label fw-bold text-secondary small">Filter Periode Akademik:</label>
                    <select id="filter_periode" class="form-select form-select-sm">
                        <option value="">-- Semua Periode --</option>
                        @foreach($periodeAkademiks as $p)
                            <option value="{{ $p->id }}">{{ $p->nama_periode }}</option>
                        @endforeach
                    </select>
                </div>
                @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isPimpinanRektorat() || auth()->user()->isPimpinanUnit()))
                <div class="col-md-4">
                    <label for="filter_jabatan" class="form-label fw-bold text-secondary small">Filter Jabatan Pegawai:</label>
                    <select id="filter_jabatan" class="form-select form-select-sm">
                        <option value="">-- Semua Jabatan --</option>
                        @php
                            $uniqueJabatans = collect($usersWithJabatan)->pluck('jabatan')->unique()->filter();
                        @endphp
                        @foreach($uniqueJabatans as $jbt)
                            <option value="{{ $jbt }}">{{ $jbt }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>

            <!-- DataTable -->
            <div class="table-responsive">
                <table class="table table-hover align-middle w-100" id="insidentil-table">
                    <thead class="table-light">
                        <tr>
                            <th width="3%" class="text-center">
                                <input type="checkbox" id="select-all-checkbox" class="form-check-input" style="cursor: pointer; width: 18px; height: 18px;">
                            </th>
                            <th width="5%">No</th>
                            <th width="35%">Rincian Tugas Insidentil</th>
                            <th width="15%">Estimasi Waktu</th>
                            <th width="15%">Waktu Realisasi</th>
                            <th width="10%">Lampiran</th>
                            <th width="8%">Status</th>
                            <th width="9%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<!-- Modal Tag Rekan Kerja -->
<div class="modal fade" id="modalTagUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formTagUser">
            @csrf
            <input type="hidden" id="tag_insidentil_id" name="insidentil_id">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-people-fill me-2"></i>Tag Rekan Kerja</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label fw-semibold">Pilih Rekan Kerja yang Terlibat:</label>
                    <select class="form-select" id="tag_select_users" name="tagged_users[]" multiple="multiple" style="width: 100%;">
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->jabatan ?? '-' }} - {{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save me-1"></i> Simpan Rekan Kerja</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Upload Berkas -->
<div class="modal fade" id="modalUploadFile" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formUploadFile" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-upload me-2"></i>Upload Lampiran Insidentil</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="modal_file" class="form-label fw-semibold">Unggah Berkas/Dokumen:</label>
                        <input type="file" class="form-control" id="modal_file" name="file">
                    </div>
                    <div class="mb-3">
                        <label for="modal_url" class="form-label fw-semibold">Atau Masukkan Tautan Luar (URL):</label>
                        <input type="url" class="form-control" id="modal_url" name="url_external" placeholder="https://...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-cloud-upload me-1"></i> Upload</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let table = $('#insidentil-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('insidentil.index') }}",
            data: function(d) {
                d.periode_akademik_id = $('#filter_periode').val();
                d.jabatan = $('#filter_jabatan').val();
            }
        },
        columns: [
            { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'task_details', name: 'task_details' },
            { data: 'estimasi_waktu', name: 'estimasi_waktu' },
            { data: 'waktu_realisasi', name: 'waktu_realisasi' },
            { data: 'attachment', name: 'attachment' },
            { data: 'status_badge', name: 'status_badge' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[2, 'asc']],
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            zeroRecords: "Data rencana kerja insidentil tidak ditemukan",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 data",
            paginate: {
                first: "Awal",
                last: "Akhir",
                next: "►",
                previous: "◄"
            }
        },
        drawCallback: function(settings) {
            let json = settings.json;
            if (json && json.stats) {
                $('#stat-selesai').text(json.stats.selesai);
                $('#stat-proses').text(json.stats.proses);
                $('#stat-belum').text(json.stats.belum);
                $('#stat-percent').text(json.stats.percent + '%');
            }
            $('#select-all-checkbox').prop('checked', false);
            updateBulkDeleteButton();
        }
    });

    $('#filter_periode, #filter_jabatan').on('change', function() {
        table.ajax.reload();
        updateExportLinks();
    });

    function updateExportLinks() {
        let p = $('#filter_periode').val();
        let pdfUrl = "{{ route('insidentil.export-pdf') }}" + (p ? "?periode_akademik_id=" + p : "");
        let excelUrl = "{{ route('insidentil.export-excel') }}" + (p ? "?periode_akademik_id=" + p : "");
        $('#btn-export-pdf').attr('href', pdfUrl);
        $('#btn-export-excel').attr('href', excelUrl);
    }
    updateExportLinks();

    // Checkbox bulk select
    $('#select-all-checkbox').on('click', function() {
        $('.select-row-checkbox').prop('checked', this.checked);
        updateBulkDeleteButton();
    });

    $(document).on('change', '.select-row-checkbox', function() {
        updateBulkDeleteButton();
    });

    function updateBulkDeleteButton() {
        let count = $('.select-row-checkbox:checked').length;
        if (count > 0) {
            $('#btn-bulk-delete').show().html('<i class="bi bi-trash me-1"></i> Hapus (' + count + ') Terpilih');
        } else {
            $('#btn-bulk-delete').hide();
        }
    }

    // Bulk Delete Action
    $('#btn-bulk-delete').on('click', function() {
        let ids = [];
        $('.select-row-checkbox:checked').each(function() {
            ids.push($(this).val());
        });

        if (ids.length === 0) return;

        Swal.fire({
            title: 'Hapus Data Terpilih?',
            text: "Anda yakin ingin menghapus " + ids.length + " data Rencana Kerja Insidentil yang dipilih?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('insidentil.bulk-delete') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        ids: ids
                    },
                    success: function(res) {
                        Swal.fire('Berhasil', res.message, 'success');
                        table.ajax.reload();
                    },
                    error: function(err) {
                        Swal.fire('Gagal', err.responseJSON ? err.responseJSON.message : 'Terjadi kesalahan.', 'error');
                    }
                });
            }
        });
    });

    // Start Timer Action
    $(document).on('click', '.btn-start', function() {
        let id = $(this).data('id');
        $.ajax({
            url: "/insidentil/" + id + "/start",
            type: "POST",
            data: { _token: "{{ csrf_token() }}" },
            success: function(res) {
                Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false });
                table.ajax.reload();
            }
        });
    });

    // Stop Timer Action
    $(document).on('click', '.btn-stop', function() {
        let id = $(this).data('id');
        $.ajax({
            url: "/insidentil/" + id + "/stop",
            type: "POST",
            data: { _token: "{{ csrf_token() }}" },
            success: function(res) {
                Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false });
                table.ajax.reload();
            }
        });
    });

    // Delete Single Item
    $(document).on('click', '.btn-delete', function() {
        let id = $(this).data('id');
        Swal.fire({
            title: 'Hapus Data Insidentil?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "/insidentil/" + id,
                    type: "DELETE",
                    data: { _token: "{{ csrf_token() }}" },
                    success: function(res) {
                        Swal.fire('Berhasil', res.message, 'success');
                        table.ajax.reload();
                    },
                    error: function(err) {
                        Swal.fire('Gagal', err.responseJSON ? err.responseJSON.message : 'Terjadi kesalahan.', 'error');
                    }
                });
            }
        });
    });

    // Modal Tag Rekan Kerja
    $(document).on('click', '.btn-tag-user', function() {
        let id = $(this).data('id');
        let tags = $(this).data('tags');
        $('#tag_insidentil_id').val(id);
        if ($.fn.select2) {
            $('#tag_select_users').val(tags).trigger('change');
        } else {
            $('#tag_select_users').val(tags);
        }
        $('#modalTagUser').modal('show');
    });

    $('#formTagUser').on('submit', function(e) {
        e.preventDefault();
        let id = $('#tag_insidentil_id').val();
        let taggedUsers = $('#tag_select_users').val();

        $.ajax({
            url: "/insidentil/" + id + "/update-tags",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                tagged_users: taggedUsers
            },
            success: function(res) {
                $('#modalTagUser').modal('hide');
                Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false });
                table.ajax.reload();
            }
        });
    });

    // Modal Upload Berkas
    $(document).on('click', '.btn-upload-file', function() {
        let id = $(this).data('id');
        $('#formUploadFile').attr('action', "/insidentil/" + id + "/upload-attachment");
        $('#modalUploadFile').modal('show');
    });
});
</script>
@endpush
