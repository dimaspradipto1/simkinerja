@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
    <h1>Data Absensi ESQ Hari Kedua</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Absensi Kedua</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    <div class="row">
        <div class="col-lg-12">
            <!-- Scan Absensi Mandiri Green Gradient Banner -->
            @unless(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()))
            <div class="card border-0 mb-4 text-white shadow-sm absensi-mobile-banner" style="background: linear-gradient(135deg, #198754 0%, #a3cfbb 100%); border-radius: 0.75rem;">
                <div class="card-body p-4 d-flex flex-column gap-3 justify-content-center">
                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white fs-4" style="width: 48px; height: 48px; min-width: 48px; background-color: rgba(255, 255, 255, 0.2);">
                            <i class="bi bi-qr-code"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold m-0 fs-5 mb-1">Scan Absensi Mandiri</h5>
                            <p class="m-0 bg-opacity-75 small">Silahkan scan QR Panitia untuk mencatat kehadiran Anda.</p>
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('absensi-esq-kedua.scan') }}?start=1" class="btn btn-light rounded-pill px-4 py-2 font-semibold shadow-sm d-inline-flex align-items-center gap-2" style="color: #198754; font-weight: 600;">
                            <i class="bi bi-qr-code-scan"></i> Scan QR Kehadiran (Sesi Aktif)
                        </a>
                    </div>
                </div>
            </div>
            @endunless

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title m-0 p-0 fs-5 font-semibold text-primary">List Absensi Hari II</h5>
                    @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()))
                    <div class="d-flex gap-2">
                        <!-- Premium QR Absensi Button -->
                        <button type="button" class="btn btn-outline-dark rounded btn-sm d-inline-flex align-items-center gap-1 font-medium" data-bs-toggle="modal" data-bs-target="#qrModal">
                            <i class="bi bi-qr-code"></i> QR Absensi
                        </button>
                        <!-- Export Excel Button -->
                        <a href="{{ route('absensi-esq-kedua.export-excel') }}" class="btn btn-success rounded btn-sm d-inline-flex align-items-center gap-1 font-medium bg-emerald-600 hover:bg-emerald-700 border-0">
                            <i class="bi bi-file-earmark-excel"></i> Export Excel
                        </a>
                        <!-- Add Button -->
                        <a href="{{ route('absensi-esq-kedua.create') }}" class="btn btn-primary rounded btn-sm d-inline-flex align-items-center gap-1 font-medium">
                            <i class="bi bi-plus-circle"></i> Tambah
                        </a>
                    </div>
                    @endif
                </div>
                <div class="card-body mt-3">
                    <div class="table-responsive">
                        {{ $dataTable->table([
                            'class' => 'table table-striped table-hover table-bordered align-middle',
                            'style' => 'width:100%; overflow-x: auto',
                        ]) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .btn-session-pulang-active {
        background-color: #2e1b82 !important;
        border-color: #2e1b82 !important;
        color: #ffffff !important;
    }
    .btn-session-pulang-inactive {
        background-color: transparent !important;
        border-color: #2e1b82 !important;
        color: #2e1b82 !important;
    }
</style>

<!-- QR Code Modal -->
<div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">
            <div class="modal-header border-0 pb-0 justify-content-center position-relative pt-4">
                <h5 class="modal-title font-bold text-center w-100" id="qrModalLabel" style="color: #1e3a8a; font-weight: 700; font-size: 1.35rem;">QR Absensi Hari 2 (Waktu Datang)</h5>
                <button type="button" class="btn-close position-absolute end-0 top-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center px-4 pb-4 pt-3">
                <!-- Session Toggle Buttons -->
                <div class="d-flex justify-content-center gap-3 mb-4">
                    <button type="button" id="btn-session-datang" class="btn btn-success d-inline-flex align-items-center gap-2 rounded px-4 py-2 font-semibold shadow-sm">
                        <i class="bi bi-sun-fill"></i> Waktu Datang
                    </button>
                    <button type="button" id="btn-session-pulang" class="btn btn-outline-primary btn-session-pulang-inactive d-inline-flex align-items-center gap-2 rounded px-4 py-2 font-semibold shadow-sm">
                        <i class="bi bi-moon-stars-fill"></i> Waktu Pulang
                    </button>
                </div>

                <!-- QR Code Display Box -->
                <div class="p-3 border rounded shadow-sm bg-white mb-3 mx-auto" style="border-radius: 0.75rem; max-width: 340px;">
                    <img id="qr-code-img" src="" alt="QR Absensi" style="width: 100%; max-width: 340px; height: auto; aspect-ratio: 1 / 1; object-fit: contain; display: block; margin: 0 auto;">
                </div>

                <!-- Progress Bar -->
                <div class="progress mb-2" style="height: 6px; width: 100%; max-width: 340px; margin: 0 auto; background-color: #e9ecef; border-radius: 1rem;">
                    <div id="qr-progress" class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%; border-radius: 1rem; transition: width 1s linear;"></div>
                </div>

                <!-- Countdown Text -->
                <p class="text-muted small mb-0 mt-2 font-medium">
                    <i class="bi bi-lightning-fill text-warning"></i> Selalu Aktif — berganti dalam <span id="countdown-timer" class="font-bold text-dark">60</span> detik
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @if (app()->environment('production'))
        {!! str_replace('http:', 'https:', $dataTable->scripts()) !!}
    @else
        {!! $dataTable->scripts() !!}
    @endif

    <script>
        $(document).ready(function() {
            let activeSession = 'datang';
            let countdown = 60;
            let timer = null;
            let currentUrl = window.location.href.split('?')[0];
            if (currentUrl.endsWith('/')) {
                currentUrl = currentUrl.slice(0, -1);
            }
            const baseUrl = currentUrl + "/scan-proses";

            function updateQrCode() {
                const timestamp = Math.floor(Date.now() / 60000); // changes every 1 minute
                const qrData = `${baseUrl}?session=${activeSession}&t=${timestamp}`;
                const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=340x340&data=${encodeURIComponent(qrData)}`;
                $('#qr-code-img').attr('src', qrUrl);
            }

            function startTimer() {
                clearInterval(timer);
                countdown = 60;
                updateTimerUI();

                timer = setInterval(function() {
                    countdown--;
                    if (countdown <= 0) {
                        countdown = 60;
                        updateQrCode();
                    }
                    updateTimerUI();
                }, 1000);
            }

            function updateTimerUI() {
                $('#countdown-timer').text(countdown);
                const percentage = (countdown / 60) * 100;
                $('#qr-progress').css('width', `${percentage}%`);
            }

            // Initialize on modal open
            $('#qrModal').on('shown.bs.modal', function () {
                updateQrCode();
                startTimer();
            });

            // Clear timer on modal close
            $('#qrModal').on('hidden.bs.modal', function () {
                clearInterval(timer);
            });

            // Tab session selection handlers
            $('#btn-session-datang').on('click', function() {
                if (activeSession === 'datang') return;
                activeSession = 'datang';

                // UI updates
                $('#qrModalLabel').text('QR Absensi Hari 2 (Waktu Datang)');
                $(this).removeClass('btn-outline-success').addClass('btn-success');
                $('#btn-session-pulang').removeClass('btn-session-pulang-active').addClass('btn-session-pulang-inactive');
                $('#qr-progress').removeClass('bg-primary').addClass('bg-success');

                updateQrCode();
                startTimer();
            });

            $('#btn-session-pulang').on('click', function() {
                if (activeSession === 'pulang') return;
                activeSession = 'pulang';

                // UI updates
                $('#qrModalLabel').text('QR Absensi Hari 2 (Waktu Pulang)');
                $(this).removeClass('btn-session-pulang-inactive').addClass('btn-session-pulang-active');
                $('#btn-session-datang').removeClass('btn-success').addClass('btn-outline-success');
                $('#qr-progress').removeClass('bg-success').addClass('bg-primary');

                updateQrCode();
                startTimer();
            });

            // Auto-open QR modal when navigated directly from sidebar menu
            try {
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.get('show_qr') === '1') {
                    $('#qrModal').modal('show');
                }
            } catch (e) {}

            // Auto reload DataTable every 5 seconds to show real-time scans
            setInterval(function() {
                if ($.fn.DataTable.isDataTable('#absensiesqkedua-table')) {
                    $('#absensiesqkedua-table').DataTable().ajax.reload(null, false);
                }
            }, 5000);
        });

        function deleteAbsensi(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data absensi ini akan dihapus secara permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmColor: '#dc3545',
                cancelColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('absensi-esq-kedua') }}/" + id,
                        type: 'DELETE',
                        data: {
                            "_token": "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    title: 'Berhasil!',
                                    text: response.message,
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                $('#absensiesqkedua-table').DataTable().ajax.reload();
                            } else {
                                Swal.fire({
                                    title: 'Gagal!',
                                    text: response.message || 'Gagal menghapus data.',
                                    icon: 'error'
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                title: 'Gagal!',
                                text: xhr.responseJSON?.message || 'Terjadi kesalahan saat menghapus data.',
                                icon: 'error'
                            });
                        }
                    });
                }
            });
        }
    </script>
@endpush
