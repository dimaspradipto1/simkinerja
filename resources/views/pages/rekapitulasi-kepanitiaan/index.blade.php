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
    <h1>Rekapitulasi Capaian Kepanitiaan</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Rekapitulasi Kepanitiaan</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    <!-- Filter Section -->
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-header-green">
                    <i class="bi bi-filter-square me-1"></i> Filter Rekapitulasi Kepanitiaan
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
                        
                        @if(auth()->check() && (auth()->user()->isPimpinanUnit() || auth()->user()->isAdmin()))
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
        <!-- Total Rencana Kerja Kepanitiaan -->
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
                        <h4 class="mb-0 fw-bold text-secondary" id="stat-belum">0</h4>
                    </div>
                </div>
            </div>
        </div>
        <!-- Sedang Proses -->
        <div class="col-6 col-lg-3">
            <div class="card rekap-card shadow-sm border-0 border-start border-4 border-primary h-100 mb-0">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3 text-primary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-play-circle fs-4"></i>
                    </div>
                    <div>
                        <div class="text-secondary small fw-bold mb-0">SEDANG PROSES</div>
                        <h4 class="mb-0 fw-bold text-primary" id="stat-proses">0</h4>
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
                        <div class="text-secondary small fw-bold mb-0">SUDAH SELESAI</div>
                        <h4 class="mb-0 fw-bold text-success" id="stat-selesai">0</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphics and Percentages -->
    <div class="row mb-4">
        <!-- Pie Chart Comparison -->
        <div class="col-lg-5 mb-3 mb-lg-0">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header-green">
                    <i class="bi bi-pie-chart-fill me-1"></i> Perbandingan Status
                </div>
                <div class="card-body pt-3 d-flex flex-column justify-content-center align-items-center" style="min-height: 280px;">
                    <div style="width: 220px; height: 220px;">
                        <canvas id="chartStatus"></canvas>
                    </div>
                    <div class="mt-2 text-center text-muted small" id="chart-no-data" style="display: none;">
                        <i class="bi bi-info-circle me-1"></i> Tidak ada data untuk ditampilkan grafik
                    </div>
                </div>
            </div>
        </div>

        <!-- Percentages and Progress Bar -->
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header-green">
                    <i class="bi bi-percent me-1"></i> Persentase Capaian
                </div>
                <div class="card-body pt-3 d-flex flex-column justify-content-between">
                    <div>
                        <h6 class="fw-bold text-secondary mb-3">Persentase Capaian Kerja Kepanitiaan (Selesai vs Total)</h6>
                        
                        <!-- Big Display -->
                        <div class="d-flex align-items-baseline mb-3">
                            <span class="fs-1 fw-bold text-success" id="big-percent">0%</span>
                            <span class="ms-2 text-secondary">Tugas Terselesaikan</span>
                        </div>

                        <!-- Progress Bar -->
                        <div class="progress mb-4" style="height: 18px; border-radius: 9px;">
                            <div id="rekap-bar-selesai" class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" title="Selesai">0%</div>
                            <div id="rekap-bar-proses" class="progress-bar bg-primary progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" title="Sedang Proses">0%</div>
                            <div id="rekap-bar-belum" class="progress-bar bg-secondary progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" title="Belum Dimulai">0%</div>
                        </div>
                    </div>

                    <!-- Breakdown Table/List -->
                    <div class="border rounded p-3 bg-light mt-auto">
                        <div class="row text-center">
                            <div class="col-4 border-end">
                                <div class="text-secondary small fw-bold">BELUM MULAI</div>
                                <div class="fw-semibold text-secondary" id="percent-belum">0%</div>
                            </div>
                            <div class="col-4 border-end">
                                <div class="text-secondary small fw-bold text-primary">SEDANG PROSES</div>
                                <div class="fw-semibold text-primary" id="percent-proses">0%</div>
                            </div>
                            <div class="col-4">
                                <div class="text-secondary small fw-bold text-success">SELESAI</div>
                                <div class="fw-semibold text-success" id="percent-selesai">0%</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed List Section -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-header-green">
                    <i class="bi bi-list-check me-1"></i> Rincian Rencana Kerja Kepanitiaan
                </div>
                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table id="rekap-table" class="table table-hover w-100" style="font-size: 0.9rem;">
                            <thead class="table-light text-secondary fw-semibold">
                                <tr>
                                    <th style="width: 50px;">No</th>
                                    <th>Uraian Tugas</th>
                                    <th>Pembuat</th>
                                    <th>Rekan Kerja (Tag)</th>
                                    <th style="width: 120px;">Status</th>
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
@endsection

@push('scripts')
<!-- Load Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    $(document).ready(function() {
        if ($('.select2-simple').length) {
            $('.select2-simple').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });
        }

        const ctx = document.getElementById('chartStatus').getContext('2d');
        const statusChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Belum Dimulai', 'Proses', 'Selesai'],
                datasets: [{
                    data: [0, 0, 0],
                    backgroundColor: ['#6c757d', '#0d6efd', '#198754'],
                    borderWidth: 2,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            padding: 15,
                            font: {
                                size: 11
                            }
                        }
                    }
                },
                cutout: '65%'
            }
        });

        const table = $('#rekap-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('rekapitulasi-kepanitiaan.data') }}",
                data: function(d) {
                    d.periode_akademik_id = $('#filter_periode').val();
                    d.jabatan = $('#filter_jabatan').val();
                }
            },
            columns: [
                { 
                    data: null, 
                    orderable: false, 
                    searchable: false,
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                { 
                    data: 'uraian_tugas', 
                    name: 'uraian_tugas',
                    render: function(data, type, row) {
                        return '<div class="fw-semibold text-dark text-wrap">' + data + '</div>';
                    }
                },
                { data: 'pembuat', name: 'user.name' },
                { data: 'rekan_kerja', name: 'taggedUsers.name', orderable: false },
                { data: 'status_badge', name: 'status', orderable: true, className: 'text-center' }
            ],
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
            },
            order: [[1, 'asc']],
            drawCallback: function(settings) {
                let api = this.api();
                let json = api.ajax.json();

                if (json && json.rekap) {
                    let r = json.rekap;

                    $('#stat-total').text(r.total);
                    $('#stat-belum').text(r.belum);
                    $('#stat-proses').text(r.proses);
                    $('#stat-selesai').text(r.selesai);

                    let pBelum = r.total > 0 ? Math.round((r.belum / r.total) * 100) : 0;
                    let pProses = r.total > 0 ? Math.round((r.proses / r.total) * 100) : 0;
                    let pSelesai = r.percent;

                    $('#big-percent').text(pSelesai + '%');
                    $('#rekap-bar-selesai').css('width', pSelesai + '%')
                        .attr('aria-valuenow', pSelesai)
                        .text(pSelesai >= 5 ? pSelesai + '%' : '');
                    $('#rekap-bar-proses').css('width', pProses + '%')
                        .attr('aria-valuenow', pProses)
                        .text(pProses >= 5 ? pProses + '%' : '');
                    $('#rekap-bar-belum').css('width', pBelum + '%')
                        .attr('aria-valuenow', pBelum)
                        .text(pBelum >= 5 ? pBelum + '%' : '');

                    $('#percent-belum').text(pBelum + '%');
                    $('#percent-proses').text(pProses + '%');
                    $('#percent-selesai').text(pSelesai + '%');

                    if (r.total > 0) {
                        $('#chartStatus').show();
                        $('#chart-no-data').hide();
                        statusChart.data.datasets[0].data = [r.belum, r.proses, r.selesai];
                        statusChart.update();
                    } else {
                        $('#chartStatus').hide();
                        $('#chart-no-data').show();
                    }
                }
            }
        });

        $('#filter_periode, #filter_jabatan').on('change', function() {
            table.ajax.reload();
        });

        $('#btn-reset').on('click', function() {
            $('#filter_periode').val('').trigger('change');
            $('#filter_jabatan').val('').trigger('change');
        });
    });
</script>
@endpush
