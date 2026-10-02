@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
    <h1>Absensi Kinerja</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Absensi Kinerja</li>
        </ol>
    </nav>
</div>

<section class="section">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <h5 class="mb-0 fw-semibold">Daftar Absensi Kinerja Harian</h5>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <small class="text-muted">Target harian: 7 jam kerja + 1 jam istirahat (total 8 jam).</small>
                <button type="button" id="export-absensi" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalExportAbsensi">
                    <i class="bi bi-file-earmark-arrow-down me-1"></i>Export
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3 my-1">
                <div class="col-sm-6 col-lg-3">
                    <label for="filter-dari" class="form-label">Tanggal dari</label>
                    <input type="date" id="filter-dari" class="form-control">
                </div>
                <div class="col-sm-6 col-lg-3">
                    <label for="filter-sampai" class="form-label">Tanggal sampai</label>
                    <input type="date" id="filter-sampai" class="form-control">
                </div>
                @if($hasPimpinanScope)
                    <div class="col-sm-6 col-lg-3">
                        <label for="filter-cakupan" class="form-label">Tampilkan absensi</label>
                        <select id="filter-cakupan" class="form-select">
                            <option value="semua">Absensi saya dan bawahan</option>
                            <option value="saya">Absensi saya</option>
                            <option value="bawahan">Absensi bawahan</option>
                        </select>
                    </div>
                @endif
                @if($isAdmin || $isPimpinan)
                    <div class="col-sm-8 col-lg-{{ $hasPimpinanScope ? '3' : '4' }}">
                        <label for="filter-user" class="form-label">Pegawai</label>
                        <select id="filter-user" class="form-select">
                            <option value="">{{ $hasPimpinanScope ? 'Semua pegawai terkait' : 'Semua pegawai' }}</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->jabatan ?? '-' }} - {{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-sm-4 col-lg-2 d-flex align-items-end">
                    <button type="button" id="reset-filter" class="btn btn-outline-secondary w-100">Reset filter</button>
                </div>
            </div>

            <div class="table-responsive mt-3">
                <table id="absensi-kinerja-table" class="table table-striped table-bordered align-middle w-100">
                    <thead>
                        <tr>
                            <th>No.</th>
                            @if($isAdmin || $isPimpinan)
                                <th>Nama Pegawai</th>
                                <th>Jabatan</th>
                            @endif
                            <th class="text-nowrap" style="min-width: 155px;">Tanggal</th>
                            <th class="text-nowrap" style="min-width: 105px;">Datang</th>
                            <th class="text-nowrap" style="min-width: 105px;">Pulang</th>
                            <th>Total Durasi Kerja</th>
                            <th>Kategori</th>
                            <th>Keterangan Kerja</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="modalExportAbsensi" tabindex="-1" aria-labelledby="modalExportAbsensiLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header text-white px-4 py-3" style="background-color: #15432d;">
                <h5 class="modal-title fw-bold fs-6 d-flex align-items-center gap-2" id="modalExportAbsensiLabel">
                    <i class="bi bi-file-earmark-arrow-down-fill"></i>
                    <span>Export Absensi Kinerja</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold text-dark small mb-1">Pilihan Filter Waktu</label>
                    <div class="d-flex gap-3 p-2 bg-light rounded border">
                        @if($periods->isNotEmpty())
                            <div class="form-check mb-0">
                                <input class="form-check-input export-absensi-filter-choice" type="radio" name="export-absensi-filter-type" id="export-absensi-filter-periode" value="periode">
                                <label class="form-check-label fw-semibold text-dark" for="export-absensi-filter-periode">
                                    <i class="bi bi-mortarboard me-1 text-success"></i>Periode Akademik
                                </label>
                            </div>
                        @endif
                        <div class="form-check mb-0">
                            <input class="form-check-input export-absensi-filter-choice" type="radio" name="export-absensi-filter-type" id="export-absensi-filter-range" value="range" checked>
                            <label class="form-check-label fw-semibold text-dark" for="export-absensi-filter-range">
                                <i class="bi bi-calendar-range me-1 text-primary"></i>Rentang Tanggal
                            </label>
                        </div>
                    </div>
                </div>
                @if($periods->isNotEmpty())
                    <div class="mb-3 d-none" id="wrapper-export-absensi-periode">
                        <label for="export-absensi-periode" class="form-label fw-bold text-dark small mb-1">Periode Akademik</label>
                        <select id="export-absensi-periode" class="form-select">
                            <option value="">Pilih periode akademik</option>
                            @foreach($periods as $period)
                                <option value="{{ $period->id }}">{{ $period->nama_periode }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="mb-3" id="wrapper-export-absensi-range">
                    <label class="form-label fw-bold text-dark small mb-1">Rentang Tanggal</label>
                    <div class="row g-2">
                        <div class="col-6">
                            <label for="export-absensi-dari" class="form-label small text-muted mb-1">Tanggal dari</label>
                            <input type="date" id="export-absensi-dari" class="form-control">
                        </div>
                        <div class="col-6">
                            <label for="export-absensi-sampai" class="form-label small text-muted mb-1">Tanggal sampai</label>
                            <input type="date" id="export-absensi-sampai" class="form-control">
                        </div>
                    </div>
                </div>
                @if($isAdmin || $isPimpinan)
                    <div class="mb-3">
                        <label for="export-absensi-user" class="form-label fw-bold text-dark small mb-1">Pegawai / Jabatan</label>
                        <select id="export-absensi-user" class="form-select">
                            <option value="">{{ $hasPimpinanScope ? 'Semua pegawai terkait' : 'Semua pegawai' }}</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->jabatan ?? '-' }} - {{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="p-2 rounded bg-light border text-muted small d-flex align-items-start gap-2">
                    <i class="bi bi-info-circle-fill text-success"></i>
                    <span>Data mengikuti periode atau rentang tanggal, pegawai/jabatan, dan pencarian pada tabel.</span>
                </div>
            </div>
            <div class="modal-footer bg-light px-4 py-3 d-flex justify-content-between">
                <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Batal</button>
                <div class="d-flex gap-2">
                    <button type="button" id="btn-export-absensi-excel" class="btn btn-success px-3 fw-bold">
                        <i class="bi bi-file-earmark-excel-fill me-1"></i>Unduh Excel
                    </button>
                    <button type="button" id="btn-export-absensi-pdf" class="btn btn-danger px-3 fw-bold">
                        <i class="bi bi-file-earmark-pdf-fill me-1"></i>Unduh PDF
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        const canFilterPegawai = @json($isAdmin || $isPimpinan);
        const canFilterCakupan = @json($hasPimpinanScope);
        if (canFilterPegawai && $.fn.select2) {
            $('#filter-user').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: canFilterCakupan ? '-- Cari nama pimpinan / bawahan --' : '-- Cari nama pegawai --',
                allowClear: true
            });
        }
        const columns = [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false }
        ];

        if (canFilterPegawai) {
            columns.push(
                { data: 'nama_pegawai', name: 'user.name' },
                { data: 'jabatan', name: 'user.jabatan' }
            );
        }

        columns.push(
            { data: 'tanggal_fmt', name: 'tanggal', width: '155px', className: 'text-nowrap' },
            { data: 'datang_col', name: 'waktu_datang', orderable: false, searchable: false, width: '105px', className: 'text-nowrap' },
            { data: 'pulang_col', name: 'waktu_pulang', orderable: false, searchable: false, width: '105px', className: 'text-nowrap' },
            { data: 'total_durasi_col', name: 'total_durasi_col', orderable: false, searchable: false },
            { data: 'kategori_col', name: 'kategori_col', orderable: false, searchable: false, className: 'align-top' },
            { data: 'keterangan_col', name: 'keterangan_kerja', orderable: false, searchable: false }
        );

        const table = $('#absensi-kinerja-table').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: @json(route('absensi-kinerja.index')),
                data: function (data) {
                    data.dari = $('#filter-dari').val();
                    data.sampai = $('#filter-sampai').val();
                    if (canFilterPegawai) {
                        data.user_id = $('#filter-user').val();
                    }
                    if (canFilterCakupan) {
                        data.cakupan = $('#filter-cakupan').val();
                    }
                }
            },
            columns: columns,
            order: [[canFilterPegawai ? 3 : 1, 'desc']],
            language: {
                processing: 'Memuat data...',
                emptyTable: 'Belum ada data absensi kinerja.',
                zeroRecords: 'Data tidak ditemukan.',
                search: 'Cari:',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                infoEmpty: 'Tidak ada data',
                paginate: { previous: 'Sebelumnya', next: 'Berikutnya' }
            }
        });

        $('#filter-dari, #filter-sampai, #filter-user, #filter-cakupan').on('change', function () {
            if (this.id === 'filter-cakupan') {
                $('#filter-user').val('').trigger('change.select2');
            }
            table.ajax.reload();
        });

        $('#reset-filter').on('click', function () {
            $('#filter-dari, #filter-sampai').val('');
            $('#filter-user').val('').trigger('change.select2');
            $('#filter-cakupan').val('semua');
            table.ajax.reload();
        });

        $('#modalExportAbsensi').on('show.bs.modal', function () {
            const hasDateFilter = Boolean($('#filter-dari').val() || $('#filter-sampai').val());
            const defaultFilterType = hasDateFilter ? 'range' : (@json($periods->isNotEmpty()) ? 'periode' : 'range');
            $('input[name="export-absensi-filter-type"][value="' + defaultFilterType + '"]').prop('checked', true).trigger('change');
            $('#export-absensi-dari').val($('#filter-dari').val());
            $('#export-absensi-sampai').val($('#filter-sampai').val());
            if (defaultFilterType === 'periode' && !$('#export-absensi-periode').val()) {
                $('#export-absensi-periode').val($('#export-absensi-periode option:eq(1)').val());
            }
            if (canFilterPegawai) {
                $('#export-absensi-user').val($('#filter-user').val() || '');
            }
        });

        $('.export-absensi-filter-choice').on('change', function () {
            const usePeriod = $(this).val() === 'periode';
            $('#wrapper-export-absensi-periode').toggleClass('d-none', !usePeriod);
            $('#wrapper-export-absensi-range').toggleClass('d-none', usePeriod);
        });

        if (canFilterPegawai && $.fn.select2) {
            $('#export-absensi-user').select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: $('#modalExportAbsensi'),
                placeholder: '-- Cari nama pegawai / jabatan --',
                allowClear: true,
                minimumResultsForSearch: 0
            });
        }
        if ($('#export-absensi-periode').length && $.fn.select2) {
            $('#export-absensi-periode').select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: $('#modalExportAbsensi'),
                placeholder: 'Pilih periode akademik',
                allowClear: true,
                minimumResultsForSearch: 0
            });
        }

        function exportAbsensi(format) {
            const params = new URLSearchParams();
            const filterType = $('input[name="export-absensi-filter-type"]:checked').val() || 'range';
            const search = table.search();
            if (filterType === 'periode') {
                const periodId = $('#export-absensi-periode').val();
                if (!periodId) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Periode belum dipilih',
                            text: 'Pilih periode akademik untuk melanjutkan export.',
                            confirmButtonColor: '#15432d'
                        });
                    }
                    return;
                }
                params.set('periode_akademik_id', periodId);
            } else {
                const dari = $('#export-absensi-dari').val();
                const sampai = $('#export-absensi-sampai').val();

                if (dari && sampai && dari > sampai) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Rentang tanggal tidak valid',
                            text: 'Tanggal dari tidak boleh melewati tanggal sampai.',
                            confirmButtonColor: '#15432d'
                        });
                    }
                    return;
                }
                if (dari) params.set('dari', dari);
                if (sampai) params.set('sampai', sampai);
            }
            if (canFilterPegawai && $('#export-absensi-user').val()) {
                params.set('user_id', $('#export-absensi-user').val());
            }
            if (search) params.set('search', search);

            const query = params.toString();
            const url = format === 'pdf'
                ? @json(route('absensi-kinerja.export-pdf'))
                : @json(route('absensi-kinerja.export'));
            const exportUrl = url + (query ? '?' + query : '');
            if (format === 'pdf') {
                window.open(exportUrl, '_blank', 'noopener');
            } else {
                window.location.href = exportUrl;
            }
        }

        $('#btn-export-absensi-excel').on('click', function () {
            exportAbsensi('excel');
        });
        $('#btn-export-absensi-pdf').on('click', function () {
            exportAbsensi('pdf');
        });

        window.setInterval(function () {
            table.ajax.reload(null, false);
        }, 60000);
    });
</script>
@endpush
