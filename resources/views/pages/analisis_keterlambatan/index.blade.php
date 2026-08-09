@extends('layouts.dashboard.template')

@section('title', 'Analisis Kendala & Keterlambatan Kinerja')

@section('content')
<div class="pagetitle">
    <h1><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>Analisis Kendala & Keterlambatan Kinerja</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item">Rekapitulasi & Laporan</li>
            <li class="breadcrumb-item active">Analisis Keterlambatan</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    <!-- Filter Card -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body py-3">
            <form id="filterForm" class="row g-3 align-items-center">
                <div class="{{ $isStaffOnly ? 'col-md-6' : 'col-md-3' }}">
                    <label for="filter_periode" class="form-label small fw-bold text-muted mb-1"><i class="bi bi-calendar3 me-1"></i> Periode Akademik</label>
                    <select id="filter_periode" name="periode_akademik_id" class="form-select form-select-sm">
                        <option value="">-- Semua Periode --</option>
                        @foreach($periodeAkademiks as $periode)
                            <option value="{{ $periode->id }}" {{ $defaultPeriodeId == $periode->id ? 'selected' : '' }}>
                                {{ $periode->nama_periode }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                @if(!$isStaffOnly)
                <div class="col-md-3">
                    <label for="filter_jabatan" class="form-label small fw-bold text-muted mb-1"><i class="bi bi-briefcase me-1"></i> Filter Jabatan / Unit</label>
                    <select id="filter_jabatan" name="jabatan" class="form-select form-select-sm">
                        <option value="">-- Semua Jabatan & Unit --</option>
                        @php
                            $uniqueJabatans = $usersWithJabatan->pluck('jabatan')->unique();
                        @endphp
                        @foreach($uniqueJabatans as $j)
                            <option value="{{ $j }}">{{ $j }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="filter_user" class="form-label small fw-bold text-muted mb-1"><i class="bi bi-person me-1"></i> Filter Staff</label>
                    <select id="filter_user" name="user_id" class="form-select form-select-sm">
                        <option value="">-- Semua Staff --</option>
                        @foreach($usersWithJabatan as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->jabatan }})</option>
                        @endforeach
                    </select>
                </div>
                @else
                    <input type="hidden" id="filter_jabatan" name="jabatan" value="">
                    <input type="hidden" id="filter_user" name="user_id" value="{{ auth()->user()->id }}">
                @endif

                <div class="{{ $isStaffOnly ? 'col-md-6' : 'col-md-3' }}">
                    <label for="filter_kendala" class="form-label small fw-bold text-muted mb-1"><i class="bi bi-funnel me-1"></i> Kategori Kendala</label>
                    <select id="filter_kendala" name="kategori_kendala" class="form-select form-select-sm">
                        <option value="semua">-- Semua Kategori Kendala --</option>
                        <option value="insidentil">🔴 Terganggu Tugas Insidentil</option>
                        <option value="kepanitiaan">🟠 Beban Tugas Kepanitiaan</option>
                        <option value="beban_ganda">🟡 Beban Ganda (Panitia & Insidentil)</option>
                        <option value="murni">⚪ Keterlambatan Murni Staff</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Stat Cards Summary -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm border-start border-danger border-4 h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-danger bg-opacity-10 p-3 me-3 text-danger fs-3">
                            <i class="bi bi-exclamation-octagon-fill"></i>
                        </div>
                        <div>
                            <span class="text-muted small fw-bold text-uppercase d-block">Total Pekerjaan Terlambat</span>
                            <h3 id="stat-total" class="fw-bold mb-0 text-danger">0</h3>
                            <span class="small text-muted">Rencana Kerja Utama</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm border-start border-danger border-4 h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-danger bg-opacity-10 p-3 me-3 text-danger fs-3">
                            <i class="bi bi-lightning-charge-fill"></i>
                        </div>
                        <div>
                            <span class="text-muted small fw-bold text-uppercase d-block">Disebabkan Insidentil</span>
                            <h3 id="stat-insidentil" class="fw-bold mb-0 text-dark">0</h3>
                            <span id="stat-percent-ins" class="small badge bg-danger text-white">0% dari keterlambatan</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm border-start border-warning border-4 h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3 text-warning fs-3">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div>
                            <span class="text-muted small fw-bold text-uppercase d-block">Disebabkan Kepanitiaan</span>
                            <h3 id="stat-kepanitiaan" class="fw-bold mb-0 text-dark">0</h3>
                            <span id="stat-percent-pan" class="small badge bg-warning text-dark">0% dari keterlambatan</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm border-start border-secondary border-4 h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-secondary bg-opacity-10 p-3 me-3 text-secondary fs-3">
                            <i class="bi bi-person-x-fill"></i>
                        </div>
                        <div>
                            <span class="text-muted small fw-bold text-uppercase d-block">Keterlambatan Murni</span>
                            <h3 id="stat-murni" class="fw-bold mb-0 text-dark">0</h3>
                            <span id="stat-percent-murni" class="small badge bg-secondary text-white">0% dari keterlambatan</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-3 mb-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h5 class="card-title m-0 fw-bold text-dark fs-6"><i class="bi bi-pie-chart-fill me-2 text-danger"></i>Distribusi Penyebab Keterlambatan</h5>
                </div>
                <div class="card-body p-3 d-flex align-items-center justify-content-center" style="min-height: 260px;">
                    <canvas id="chartPenyebab" style="max-height: 240px;"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h5 class="card-title m-0 fw-bold text-dark fs-6"><i class="bi bi-bar-chart-line-fill me-2 text-primary"></i>Ringkasan Kendala Beban Kerja Per Kategori</h5>
                </div>
                <div class="card-body p-3 d-flex align-items-center justify-content-center" style="min-height: 260px;">
                    <canvas id="chartBarKendala" style="max-height: 240px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Audit Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2 border-0">
            <h5 class="card-title m-0 fw-bold text-dark fs-6"><i class="bi bi-list-task me-2 text-danger"></i>Audit Detail Keterlambatan Pekerjaan & Interupsi Task</h5>
            <div class="d-flex gap-2">
                <button type="button" id="btn-export-excel" class="btn btn-sm btn-outline-success px-3"><i class="bi bi-file-earmark-excel me-1"></i> Export Excel</button>
                <button type="button" id="btn-export-pdf" class="btn btn-sm btn-outline-danger px-3"><i class="bi bi-file-earmark-pdf me-1"></i> Export PDF</button>
            </div>
        </div>
        <div class="card-body p-3">
            <div class="table-responsive">
                <table id="analisis-table" class="table table-hover align-middle w-100" style="font-size: 0.85rem;">
                    <thead class="table-light border-top">
                        <tr>
                            <th style="width: 4%;">NO</th>
                            <th style="width: 18%;">STAFF & JABATAN</th>
                            <th style="width: 25%;">TUGAS UTAMA (TERLAMBAT)</th>
                            <th style="width: 15%;">DIAGNOSTIK KENDALA</th>
                            <th style="width: 23%;">PEKERJAAN BENTROK (OVERLAP)</th>
                            <th style="width: 15%;">REKOMENDASI EVALUASI</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<!-- Modal Edit Saran Pimpinan / Rektor -->
<div class="modal fade" id="modalSaranPimpinan" tabindex="-1" aria-labelledby="modalSaranPimpinanLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formSaranPimpinan">
                @csrf
                <input type="hidden" id="saran_task_id" name="task_id">
                <div class="modal-header bg-primary text-white py-2">
                    <h5 class="modal-title fs-6 fw-bold" id="modalSaranPimpinanLabel">
                        <i class="bi bi-pencil-square me-2"></i>Input / Edit Saran Pimpinan (Rektor)
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label text-muted small mb-0">Nama Staff:</label>
                        <div class="fw-bold text-dark" id="modal_staff_name">-</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-0">Uraian Tugas:</label>
                        <div class="small text-secondary" id="modal_task_name">-</div>
                    </div>
                    <div class="mb-3">
                        <label for="saran_pimpinan_input" class="form-label fw-bold small">Catatan / Saran Rektor:</label>
                        <textarea class="form-control" id="saran_pimpinan_input" name="saran_pimpinan" rows="4" placeholder="Tuliskan saran, instruksi, atau arahan pimpinan untuk staff ini..."></textarea>
                        <div class="form-text small text-muted">Kosongkan jika ingin mengembalikan ke rekomendasi saran otomatis sistem.</div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-light border btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm" id="btn-save-saran">
                        <i class="bi bi-save me-1"></i>Simpan Saran
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    let chartPenyebabInst = null;
    let chartBarKendalaInst = null;

    let table = $('#analisis-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('analisis-keterlambatan.data') }}",
            data: function(d) {
                d.periode_akademik_id = $('#filter_periode').val();
                d.jabatan = $('#filter_jabatan').val();
                d.user_id = $('#filter_user').val();
                d.kategori_kendala = $('#filter_kendala').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'staff_info', name: 'staff_info' },
            { data: 'task_details', name: 'task_details' },
            { data: 'diagnostik_kendala', name: 'diagnostik_kendala' },
            { data: 'rincian_bentrokan', name: 'rincian_bentrokan' },
            { data: 'rekomendasi_evaluasi', name: 'rekomendasi_evaluasi' }
        ],
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            zeroRecords: "Tidak ada data keterlambatan pekerjaan yang ditemukan",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 data",
            paginate: { first: "Awal", last: "Akhir", next: "►", previous: "◄" }
        },
        drawCallback: function(settings) {
            let json = settings.json;
            if (json && json.stats) {
                let st = json.stats;
                $('#stat-total').text(st.total);
                $('#stat-insidentil').text(st.insidentil);
                $('#stat-kepanitiaan').text(st.kepanitiaan);
                $('#stat-murni').text(st.murni);

                $('#stat-percent-ins').text(st.percent_ins + '% dari keterlambatan');
                $('#stat-percent-pan').text(st.percent_pan + '% dari keterlambatan');
                $('#stat-percent-murni').text(st.percent_murni + '% dari keterlambatan');

                updateCharts(st);
            }
        }
    });

    $('#filter_periode, #filter_jabatan, #filter_user, #filter_kendala').on('change', function() {
        table.ajax.reload();
    });

    function updateCharts(st) {
        // Render Donut Chart
        let ctxDonut = document.getElementById('chartPenyebab').getContext('2d');
        if (chartPenyebabInst) chartPenyebabInst.destroy();

        chartPenyebabInst = new Chart(ctxDonut, {
            type: 'doughnut',
            data: {
                labels: ['Terganggu Insidentil', 'Beban Kepanitiaan', 'Beban Ganda', 'Murni Staff'],
                datasets: [{
                    data: [st.insidentil, st.kepanitiaan, st.beban_ganda, st.murni],
                    backgroundColor: ['#dc3545', '#ffc107', '#fd7e14', '#6c757d'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }
                }
            }
        });

        // Render Bar Chart
        let ctxBar = document.getElementById('chartBarKendala').getContext('2d');
        if (chartBarKendalaInst) chartBarKendalaInst.destroy();

        chartBarKendalaInst = new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: ['Insidentil', 'Kepanitiaan', 'Beban Ganda', 'Murni Staff'],
                datasets: [{
                    label: 'Jumlah Tugas Terlambat',
                    data: [st.insidentil, st.kepanitiaan, st.beban_ganda, st.murni],
                    backgroundColor: ['#dc3545', '#ffc107', '#fd7e14', '#6c757d'],
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });
    }

    // Export Handlers
    $('#btn-export-excel').on('click', function() {
        let params = $('#filterForm').serialize();
        window.location.href = "{{ route('analisis-keterlambatan.export-excel') }}?" + params;
    });

    $('#btn-export-pdf').on('click', function() {
        let params = $('#filterForm').serialize();
        window.location.href = "{{ route('analisis-keterlambatan.export-pdf') }}?" + params;
    });

    // Handle Edit Saran Button Click
    $(document).on('click', '.btn-edit-saran', function() {
        let id = $(this).data('id');
        let staff = $(this).data('staff');
        let task = $(this).data('task');
        let saran = $(this).data('saran');

        $('#saran_task_id').val(id);
        $('#modal_staff_name').text(staff);
        $('#modal_task_name').text(task);
        $('#saran_pimpinan_input').val(saran);

        $('#modalSaranPimpinan').modal('show');
    });

    // Handle Form Submit for Saran Pimpinan
    $('#formSaranPimpinan').on('submit', function(e) {
        e.preventDefault();
        let id = $('#saran_task_id').val();
        let btn = $('#btn-save-saran');
        let originalText = btn.html();

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...');

        $.ajax({
            url: "{{ url('analisis-keterlambatan') }}/" + id + "/saran-pimpinan",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                saran_pimpinan: $('#saran_pimpinan_input').val()
            },
            success: function(response) {
                btn.prop('disabled', false).html(originalText);
                if (response.success) {
                    $('#modalSaranPimpinan').modal('hide');
                    table.ajax.reload(null, false);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        alert(response.message);
                    }
                } else {
                    alert(response.message || 'Gagal menyimpan saran.');
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(originalText);
                let msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Terjadi kesalahan saat menyimpan saran.';
                alert(msg);
            }
        });
    });
});
</script>
@endpush
