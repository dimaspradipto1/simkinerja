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
    .rekap-card {
        transition: transform 0.2s;
    }
    .rekap-card:hover {
        transform: translateY(-2px);
    }
</style>

<div class="pagetitle">
    <h1>Rekapitulasi Capaian Insidentil</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Rekapitulasi Insidentil</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    <!-- Filter Section -->
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-header-green">
                    <i class="bi bi-filter-square me-1"></i> Filter Rekapitulasi Insidentil
                </div>
                <div class="card-body pt-3">
                    <form id="filter-form" class="row g-3">
                        <div class="col-md-6 col-lg-4">
                            <label for="filter_periode" class="form-label fw-semibold text-secondary small">Periode Akademik</label>
                            <select name="periode_akademik_id" id="filter_periode" class="form-select select2-simple">
                                <option value="">Semua Periode</option>
                                @foreach($periodeAkademiks as $p)
                                    <option value="{{ $p->id }}" {{ $p->id == $defaultPeriodeId ? 'selected' : '' }}>
                                        {{ $p->nama_periode }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        @if(auth()->check() && auth()->user()->isRektorOrSuperAdmin())
                        <div class="col-md-6 col-lg-5">
                            <label for="filter_jabatan" class="form-label fw-semibold text-secondary small">Kriteria Checklist Jabatan</label>
                            <select name="jabatan" id="filter_jabatan" class="form-select select2-simple">
                                <option value="">Semua Jabatan</option>
                                @foreach($usersWithJabatan as $u)
                                    <option value="{{ $u->id }}" {{ ($u->id == auth()->user()->id && !auth()->user()->isPimpinanRektorat()) ? 'selected' : '' }}>{{ $u->jabatan }} - {{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <div class="col-12 d-flex align-items-end justify-content-end">
                            <button type="button" id="btn-reset" class="btn btn-secondary btn-sm px-3">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Count Cards -->
    <div class="row g-3 mb-4">
        <!-- Total Rencana Kerja Insidentil -->
        <div class="col-6 col-lg-3">
            <div class="card rekap-card shadow-sm border-0 border-start border-4 border-info h-100 mb-0">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3 text-info d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-journal-text fs-4"></i>
                    </div>
                    <div>
                        <div class="text-secondary small fw-bold mb-0">TOTAL TUGAS</div>
                        <h4 class="mb-0 fw-bold text-dark" id="stat-total">0</h4>
                    </div>
                </div>
            </div>
        </div>
        <!-- Belum Mulai -->
        <div class="col-6 col-lg-3">
            <div class="card rekap-card shadow-sm border-0 border-start border-4 border-secondary h-100 mb-0">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="rounded-circle bg-secondary bg-opacity-10 p-3 me-3 text-secondary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-x-circle fs-4"></i>
                    </div>
                    <div>
                        <div class="text-secondary small fw-bold mb-0">BELUM DIMULAI</div>
                        <h4 class="mb-0 fw-bold text-dark" id="stat-belum">0</h4>
                    </div>
                </div>
            </div>
        </div>
        <!-- Dalam Proses -->
        <div class="col-6 col-lg-3">
            <div class="card rekap-card shadow-sm border-0 border-start border-4 border-warning h-100 mb-0">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3 text-warning d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-clock-history fs-4"></i>
                    </div>
                    <div>
                        <div class="text-secondary small fw-bold mb-0">PROSES</div>
                        <h4 class="mb-0 fw-bold text-dark" id="stat-proses">0</h4>
                    </div>
                </div>
            </div>
        </div>
        <!-- Selesai -->
        <div class="col-6 col-lg-3">
            <div class="card rekap-card shadow-sm border-0 border-start border-4 border-success h-100 mb-0">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3 text-success d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-check-circle fs-4"></i>
                    </div>
                    <div>
                        <div class="text-secondary small fw-bold mb-0">SELESAI</div>
                        <h4 class="mb-0 fw-bold text-dark" id="stat-selesai">0</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Bar Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold text-dark small"><i class="bi bi-pie-chart-fill text-success me-1"></i> Persentase Capaian Penyelesaian Insidentil</span>
                        <span class="fw-bold text-success" id="stat-percent-text">0%</span>
                    </div>
                    <div class="progress" style="height: 12px;">
                        <div id="stat-progress-bar" class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- DataTable Section -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-header-green">
                    <i class="bi bi-table me-1"></i> Tabel Rekapitulasi Rencana Kerja Insidentil
                </div>
                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle w-100" id="rekap-insidentil-table">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%" class="text-center">No</th>
                                    <th width="25%">Pembuat</th>
                                    <th width="30%">Uraian Tugas Insidentil</th>
                                    <th width="20%">Rekan Kerja</th>
                                    <th width="10%" class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
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
$(document).ready(function() {
    let rekapTable = $('#rekap-insidentil-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('rekapitulasi-insidentil.data') }}",
            data: function(d) {
                d.periode_akademik_id = $('#filter_periode').val();
                d.jabatan = $('#filter_jabatan').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
            { data: 'pembuat', name: 'pembuat' },
            { data: 'uraian_tugas', name: 'uraian_tugas' },
            { data: 'rekan_kerja', name: 'rekan_kerja' },
            { data: 'status_badge', name: 'status_badge', className: 'text-center' }
        ],
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            zeroRecords: "Data rekapitulasi insidentil tidak ditemukan",
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
            if (json && json.rekap) {
                $('#stat-total').text(json.rekap.total);
                $('#stat-belum').text(json.rekap.belum);
                $('#stat-proses').text(json.rekap.proses);
                $('#stat-selesai').text(json.rekap.selesai);
                
                let percent = json.rekap.percent;
                $('#stat-percent-text').text(percent + '%');
                $('#stat-progress-bar').css('width', percent + '%').attr('aria-valuenow', percent);
            }
        }
    });

    $('#filter_periode, #filter_jabatan').on('change', function() {
        rekapTable.ajax.reload();
    });

    $('#btn-reset').on('click', function() {
        $('#filter_periode').val('');
        $('#filter_jabatan').val('');
        rekapTable.ajax.reload();
    });
});
</script>
@endpush
