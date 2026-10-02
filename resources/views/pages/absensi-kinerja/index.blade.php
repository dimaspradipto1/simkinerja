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
            <small class="text-muted">Datang tercatat saat pekerjaan dimulai; pulang otomatis setelah 8 jam kerja dan 1 jam istirahat.</small>
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
                @if($isAdmin || $isPimpinan)
                    <div class="col-sm-8 col-lg-4">
                        <label for="filter-user" class="form-label">Pegawai</label>
                        <select id="filter-user" class="form-select">
                            <option value="">Semua pegawai</option>
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
                            <th>Tanggal</th>
                            <th>Datang</th>
                            <th>Pulang</th>
                            <th>Total Durasi Kerja</th>
                            <th>Keterangan Kerja</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    $(function () {
        const canFilterPegawai = @json($isAdmin || $isPimpinan);
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
            { data: 'tanggal_fmt', name: 'tanggal' },
            { data: 'datang_col', name: 'waktu_datang', orderable: false, searchable: false },
            { data: 'pulang_col', name: 'waktu_pulang', orderable: false, searchable: false },
            { data: 'total_durasi_col', name: 'total_durasi_col', orderable: false, searchable: false },
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

        $('#filter-dari, #filter-sampai, #filter-user').on('change', function () {
            table.ajax.reload();
        });

        $('#reset-filter').on('click', function () {
            $('#filter-dari, #filter-sampai').val('');
            $('#filter-user').val('');
            table.ajax.reload();
        });

        window.setInterval(function () {
            table.ajax.reload(null, false);
        }, 60000);
    });
</script>
@endpush
