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
    .btn-mulai-green {
        background-color: #15432d;
        color: #ffffff !important;
        border: none;
        padding: 4px 14px;
        font-size: 0.85rem;
        font-weight: 600;
        border-radius: 4px;
    }
    .btn-mulai-green:hover {
        background-color: #0f3121;
    }
    .btn-selesai-red {
        background-color: #8b0000;
        color: #ffffff !important;
        border: none;
        padding: 4px 14px;
        font-size: 0.85rem;
        font-weight: 600;
        border-radius: 4px;
    }
    .btn-selesai-red:hover {
        background-color: #660000;
    }
    #rencanakerja-table tbody tr td {
        vertical-align: middle;
        padding: 10px 14px;
        border-bottom: 1px solid #edf2f7;
    }
    #rencanakerja-table thead {
        display: none; /* Clean list look matching reference screenshot */
    }
    @media (max-width: 991.98px) {
        .card-header-green {
            padding: 12px 14px;
        }
        .header-title-text {
            font-size: 0.9rem !important;
            line-height: 1.4 !important;
        }
        #rencanakerja-table tbody tr td {
            padding: 12px 8px;
        }
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            float: none !important;
            text-align: left !important;
            margin-bottom: 0.75rem;
        }
        .dataTables_wrapper .dataTables_filter input {
            width: 100% !important;
            margin-left: 0 !important;
        }
        .inline-upload-left {
            border-right: none !important;
            border-bottom: 1px solid #dee2e6 !important;
            padding-bottom: 1rem !important;
            margin-bottom: 0.5rem !important;
            padding-right: 0 !important;
        }
        .inline-upload-right {
            padding-left: 0 !important;
        }
        .action-buttons-wrap {
            justify-content: flex-start !important;
            margin-top: 8px;
        }
    }
</style>

<div class="pagetitle">
    <h1>Rencana Kerja</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Rencana Kerja</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    @if(auth()->check() && (auth()->user()->isPimpinanUnit() || auth()->user()->isAdmin()))
    <!-- Top Section: Kriteria Checklist Jabatan (Khusus Pimpinan & Admin) -->
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-header-green d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                    <span class="header-title-text">Kriteria Checklist Jabatan</span>
                    <a href="{{ route('rencana-kerja.create') }}" class="btn btn-green-add text-nowrap ms-auto ms-md-0">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Tugas
                    </a>
                </div>
                <div class="card-body pt-3">
                    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                        <label class="form-label mb-0 fw-semibold text-secondary small text-nowrap">Kriteria Checklist untuk jabatan:</label>
                        <div style="min-width: 250px; max-width: 650px;" class="flex-grow-1">
                            <select class="form-select" id="filter-jabatan">
                                <option value="">-- Semua Jabatan --</option>
                                @foreach($usersWithJabatan as $u)
                                    <option value="{{ $u->jabatan }}" {{ auth()->user()->jabatan == $u->jabatan ? 'selected' : '' }}>
                                        {{ $u->jabatan }} - {{ $u->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Bottom Section: Checklist Tugas -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-header-green d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                    <div class="header-title-text fw-semibold text-break">
                        Rencana Kerja &mdash; <span id="judul-jabatan">{{ auth()->user()->jabatan ? auth()->user()->jabatan . ' - ' . auth()->user()->name : 'Semua Jabatan' }}</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap flex-sm-nowrap me-auto me-md-0">
                        @if(auth()->check() && (auth()->user()->isPimpinanUnit() || auth()->user()->isAdmin()))
                            <a href="{{ route('rencana-kerja.export-excel', auth()->user()->jabatan ? ['jabatan' => auth()->user()->jabatan] : []) }}" id="btn-export-excel" class="btn btn-sm btn-success text-white fw-semibold text-nowrap" style="background-color: #2d6a4f; border-color: #2d6a4f;">
                                <i class="bi bi-file-earmark-excel-fill me-1"></i> Export Laporan
                            </a>
                        @endif
                        <button type="button" class="btn btn-sm btn-light border text-dark fw-semibold text-nowrap" data-bs-toggle="modal" data-bs-target="#modalImportExcel">
                            <i class="bi bi-file-earmark-excel text-success me-1"></i> Import Excel
                        </button>
                        @if(auth()->check() && (!auth()->user()->isPimpinanUnit() && !auth()->user()->isAdmin()))
                            <a href="{{ route('rencana-kerja.create') }}" class="btn btn-green-add text-nowrap">
                                <i class="bi bi-plus-lg me-1"></i> Tambah Tugas
                            </a>
                        @endif
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table table-hover w-100" id="rencanakerja-table">
                            <thead>
                                <tr>
                                    <th width="4%">No</th>
                                    <th>Uraian Tugas</th>
                                    <th width="25%" class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Import Excel -->
<div class="modal fade" id="modalImportExcel" tabindex="-1" aria-labelledby="modalImportExcelLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white" style="background-color: #2d6a4f;">
                <h5 class="modal-title fw-bold" id="modalImportExcelLabel">
                    <i class="bi bi-file-earmark-excel me-2"></i> Import Rencana Kerja Excel
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('rencana-kerja.import-excel') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="p-3 mb-3 rounded" style="background-color: #e0f2fe; border: 1px solid #bae6fd;">
                        <p class="mb-1 text-dark" style="font-size: 0.9rem;">
                            Format header: <strong>uraian_tugas</strong>
                        </p>
                        <a href="{{ route('rencana-kerja.download-template') }}" class="btn btn-sm text-primary p-0 fw-bold border-0 bg-transparent" style="font-size: 0.95rem;">
                            <i class="bi bi-download me-1"></i> Download Template
                        </a>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark mb-2">Pilih File Excel (.xlsx, .xls, .csv)</label>
                        <input type="file" name="file_excel" class="form-control bg-white" accept=".xlsx,.xls,.csv" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success px-4 text-white fw-bold" style="background-color: #15432d; border-color: #15432d;">
                        <i class="bi bi-cloud-arrow-up-fill me-1"></i> Import
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
        if ($('#filter-jabatan').length) {
            $('#filter-jabatan').select2({
                theme: 'bootstrap-5',
                placeholder: "Cari & pilih jabatan - nama...",
                allowClear: true
            });
        }

        var table = $('#rencanakerja-table').DataTable({
            processing: true,
            serverSide: true,
            ordering: false,
            autoWidth: false,
            ajax: {
                url: "{{ app()->environment('production') ? str_replace('http:', 'https:', route('rencana-kerja.index')) : route('rencana-kerja.index') }}",
                data: function(d) {
                    if ($('#filter-jabatan').length) {
                        d.jabatan = $('#filter-jabatan').val();
                    }
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-muted fw-semibold text-center align-middle', width: '4%' },
                { data: 'task_details', name: 'uraian_tugas', searchable: true, className: 'align-middle' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-end align-middle text-nowrap', width: '25%' },
            ],
            language: {
                search: "Cari Tugas:",
                lengthMenu: "Tampilkan _MENU_ data",
                zeroRecords: "Belum ada rencana kerja / checklist tugas untuk jabatan ini",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ tugas",
                infoEmpty: "Tidak ada data",
            }
        });

        if ($('#filter-jabatan').length) {
            $('#filter-jabatan').on('change', function() {
                var selectedText = $(this).find('option:selected').text();
                var val = $(this).val();
                $('#judul-jabatan').text(val ? selectedText.trim() : 'Semua Jabatan');

                if ($('#btn-export-excel').length) {
                    var exportUrl = "{{ route('rencana-kerja.export-excel') }}";
                    if (val) {
                        exportUrl += '?jabatan=' + encodeURIComponent(val);
                    }
                    $('#btn-export-excel').attr('href', exportUrl);
                }

                table.ajax.reload();
            });
        }
    });

    $(document).on('click', '.btn-start-timer', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        if (id) window.startTimer(id);
    });

    $(document).on('click', '.btn-stop-timer', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        if (id) window.stopTimer(id);
    });

    $(document).on('click', '.btn-delete-timer', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        if (id) window.deleteRencanaKerja(id);
    });

    // Handle inline file and url_external upload form submit
    $(document).on('submit', '.form-inline-upload', function(e) {
        e.preventDefault();
        var form = $(this);
        var id = form.data('id');
        var btn = form.find('.btn-simpan-inline');
        var formData = new FormData(this);
        formData.append('_token', "{{ csrf_token() }}");

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>');

        $.ajax({
            url: "{{ url('rencana-kerja') }}/" + id + "/upload-attachment",
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                btn.prop('disabled', false).html('<i class="bi bi-cloud-arrow-up-fill me-1"></i> Simpan');
                if (response.success) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: response.message,
                        showConfirmButton: false,
                        timer: 4000,
                        timerProgressBar: true
                    });
                    $('#rencanakerja-table').DataTable().ajax.reload(null, false);
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="bi bi-cloud-arrow-up-fill me-1"></i> Simpan');
                var msg = 'Terjadi kesalahan saat menyimpan berkas/link.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Menyimpan',
                    text: msg
                });
            }
        });
    });

    // Get current laptop time formatted as HH:mm:ss and date YYYY-MM-DD
    window.getLaptopDateTime = function() {
        var now = new Date();
        var year = now.getFullYear();
        var month = String(now.getMonth() + 1).padStart(2, '0');
        var day = String(now.getDate()).padStart(2, '0');
        var hours = String(now.getHours()).padStart(2, '0');
        var minutes = String(now.getMinutes()).padStart(2, '0');
        var seconds = String(now.getSeconds()).padStart(2, '0');

        return {
            date: year + '-' + month + '-' + day,
            time: hours + ':' + minutes + ':' + seconds
        };
    };

    window.startTimer = function(id) {
        var dt = window.getLaptopDateTime();
        
        Swal.fire({
            title: 'Konfirmasi Pelaksanaan Tugas',
            text: 'Waktu mulai pelaksanaan tugas akan dicatat secara otomatis sesuai waktu sistem Anda (' + dt.time + ')',
            icon: 'question',
            showCancelButton: true,
            confirmColor: '#15432d',
            cancelColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-play-fill"></i> Ya, Mulai Pelaksanaan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('rencana-kerja') }}/" + id + "/start",
                    type: 'POST',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "client_time": dt.time,
                        "client_date": dt.date
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: response.message,
                                showConfirmButton: false,
                                timer: 4000,
                                timerProgressBar: true
                            });
                            $('#rencanakerja-table').DataTable().ajax.reload(null, false);
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Terjadi kesalahan saat mencatat waktu mulai.'
                        });
                    }
                });
            }
        });
    };

    window.stopTimer = function(id) {
        var dt = window.getLaptopDateTime();

        Swal.fire({
            title: 'Konfirmasi Penyelesaian Tugas',
            text: 'Waktu penyelesaian tugas akan dicatat secara otomatis sesuai waktu sistem Anda (' + dt.time + ')',
            icon: 'question',
            showCancelButton: true,
            confirmColor: '#8b0000',
            cancelColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-stop-fill"></i> Ya, Selesaikan Tugas',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('rencana-kerja') }}/" + id + "/stop",
                    type: 'POST',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "client_time": dt.time,
                        "client_date": dt.date
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: response.message,
                                showConfirmButton: false,
                                timer: 4000,
                                timerProgressBar: true
                            });
                            $('#rencanakerja-table').DataTable().ajax.reload(null, false);
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Terjadi kesalahan saat mencatat waktu selesai.'
                        });
                    }
                });
            }
        });
    };

    window.deleteRencanaKerja = function(id) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data rencana kerja ini akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmColor: '#d33',
            cancelColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('rencana-kerja') }}/" + id,
                    type: 'DELETE',
                    data: {
                        "_token": "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: response.message,
                                showConfirmButton: false,
                                timer: 4000,
                                timerProgressBar: true
                            });
                            $('#rencanakerja-table').DataTable().ajax.reload(null, false);
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Terjadi kesalahan saat menghapus data.'
                        });
                    }
                });
            }
        });
    };
</script>
@if(isset($dataTable))
    @if(app()->environment('production'))
        {!! str_replace('http:', 'https:', $dataTable->scripts()) !!}
    @else
        {!! $dataTable->scripts() !!}
    @endif
@endif
@endpush
