@extends('layouts.dashboard.template')

@section('content')
<style>
    .welcome-card {
        background: linear-gradient(135deg, #15432D 0%, #2D6A4F 100%);
        border-radius: 12px;
        color: #ffffff;
        position: relative;
        overflow: hidden;
    }
    .welcome-card::after {
        content: "";
        position: absolute;
        right: -30px;
        bottom: -30px;
        width: 180px;
        height: 180px;
        background: rgba(255, 255, 255, 0.07);
        border-radius: 50%;
        pointer-events: none;
    }
    .stat-card {
        border-radius: 10px;
        border: none;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
    }
    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    .stat-icon-green { background: #e8f5e9; color: #2d6a4f; }
    .stat-icon-orange { background: #fff3e0; color: #e65100; }
    .stat-icon-blue { background: #e3f2fd; color: #1565c0; }
    .stat-icon-teal { background: #e0f2f1; color: #00695c; }
</style>

<div class="pagetitle mb-4">
    <h1 class="fw-bold text-dark fs-3">Dashboard Kinerja</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Dashboard</li>
        </ol>
    </nav>
</div>

<!-- Welcome Banner -->
<div class="card welcome-card mb-4 p-4 shadow-sm">
    <div class="row align-items-center">
        <div class="col-lg-8 mb-3 mb-lg-0">
            <h2 class="fw-bold fs-4 mb-2">Selamat Datang, {{ auth()->user()->name }}! 👋</h2>
            <p class="mb-2 text-white-50" style="font-size: 0.95rem;">
                <strong>Jabatan:</strong> {{ auth()->user()->jabatan ?? 'Pegawai' }} &nbsp;&bull;&nbsp; 
                <strong>Unit:</strong> {{ auth()->user()->unit ?? 'Universitas Ibnu Sina' }} &nbsp;&bull;&nbsp; 
                <strong>Role:</strong> <span class="badge bg-light text-dark fw-bold ms-1">{{ auth()->user()->roles }}</span>
            </p>
            <p class="mb-0 small text-white-50">Kelola dan pantau seluruh rencana kerja, timer kegiatan, serta bukti lampiran kinerja Anda secara efisien.</p>
        </div>
        <div class="col-lg-4 text-lg-end d-flex flex-wrap align-items-center justify-content-lg-end gap-2 mt-3 mt-lg-0">
            <button type="button" id="btn-voice-dashboard" class="btn btn-warning fw-bold text-dark px-3 py-2 shadow-sm">
                <i class="bi bi-volume-up-fill me-1 fs-6"></i> Dengarkan Suara
            </button>
            <a href="{{ route('rencana-kerja.index') }}" class="btn btn-light fw-bold text-success px-3 py-2 shadow-sm">
                <i class="bi bi-list-check me-1"></i> Rencana Kerja
            </a>
            @if(auth()->check() && (!auth()->user()->isPimpinanUnit() && !auth()->user()->isAdmin()))
                <a href="{{ route('rencana-kerja.create') }}" class="btn btn-outline-light fw-bold px-3 py-2">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Tugas
                </a>
            @endif
        </div>
    </div>
</div>

<!-- Stat Metric Cards -->
<div class="row g-3 mb-4">
    <!-- Total Tugas -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stat-card p-3 h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Total Rencana Kerja</span>
                    <h3 class="fw-bold mb-0 mt-1 text-dark">{{ $totalTugas }}</h3>
                </div>
                <div class="stat-icon stat-icon-teal">
                    <i class="bi bi-journal-text"></i>
                </div>
            </div>
            <div class="mt-3 pt-2 border-top">
                <small class="text-muted"><i class="bi bi-info-circle me-1"></i> Checklists terdaftar</small>
            </div>
        </div>
    </div>

    <!-- Belum Dimulai -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stat-card p-3 h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Belum Dimulai</span>
                    <h3 class="fw-bold mb-0 mt-1 text-warning">{{ $tugasBelumMulai }}</h3>
                </div>
                <div class="stat-icon stat-icon-orange">
                    <i class="bi bi-clock-history"></i>
                </div>
            </div>
            <div class="mt-3 pt-2 border-top">
                <small class="text-muted"><i class="bi bi-hourglass me-1"></i> Menunggu eksekusi</small>
            </div>
        </div>
    </div>

    <!-- Sedang Proses -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stat-card p-3 h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Sedang Berjalan</span>
                    <h3 class="fw-bold mb-0 mt-1 text-primary">{{ $tugasProses }}</h3>
                </div>
                <div class="stat-icon stat-icon-blue">
                    <i class="bi bi-play-circle-fill"></i>
                </div>
            </div>
            <div class="mt-3 pt-2 border-top">
                <small class="text-muted"><i class="bi bi-stopwatch me-1"></i> Timer aktif</small>
            </div>
        </div>
    </div>

    <!-- Selesai -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stat-card p-3 h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Selesai</span>
                    <h3 class="fw-bold mb-0 mt-1 text-success">{{ $tugasSelesai }}</h3>
                </div>
                <div class="stat-icon stat-icon-green">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
            </div>
            <div class="mt-3 pt-2 border-top">
                <div class="progress" style="height: 6px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $persentaseSelesai }}%" aria-valuenow="{{ $persentaseSelesai }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <small class="text-success fw-semibold mt-1 d-block">{{ $persentaseSelesai }}% Tingkat Capaian</small>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="row g-3">
    <!-- Left Column: Recent Activity Tasks -->
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-bottom">
                <h5 class="card-title m-0 fw-bold fs-6 text-dark">
                    <i class="bi bi-activity text-success me-2"></i>Rencana Kerja Terbaru
                </h5>
                <a href="{{ route('rencana-kerja.index') }}" class="btn btn-sm btn-outline-success fw-semibold">
                    Lihat Semua <i class="bi bi-arrow-right me-1"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="5%" class="ps-3">No</th>
                                <th width="35%">Uraian Tugas</th>
                                <th width="30%">Pegawai / Jabatan</th>
                                <th width="15%">Durasi</th>
                                <th width="15%" class="pe-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTasks as $index => $task)
                                <tr>
                                    <td class="ps-3 text-muted fw-semibold">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $task->uraian_tugas }}</div>
                                        @if($task->file || $task->url_external)
                                            <div class="small text-muted mt-1">
                                                @if($task->file)
                                                    <span class="badge bg-light text-secondary border me-1"><i class="bi bi-paperclip me-1"></i>Ada Berkas</span>
                                                @endif
                                                @if($task->url_external)
                                                    <span class="badge bg-light text-primary border"><i class="bi bi-link-45deg me-1"></i>Link External</span>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($task->user && $task->user->jabatan)
                                            <a href="{{ route('rencana-kerja.index', ['jabatan' => $task->user->jabatan]) }}" class="text-decoration-none text-dark fw-bold d-inline-block" title="Klik untuk filter tugas jabatan ini">
                                                <i class="bi bi-person-fill text-success me-1"></i>{{ $task->user->name ?? '-' }}
                                            </a>
                                            <div class="small mt-1">
                                                <a href="{{ route('rencana-kerja.index', ['jabatan' => $task->user->jabatan]) }}" class="text-decoration-none" title="Klik untuk filter tugas jabatan ini">
                                                    <span class="badge bg-light text-dark border fw-semibold me-1"><i class="bi bi-briefcase-fill text-secondary me-1"></i>{{ $task->user->jabatan ?? '-' }}</span>
                                                    @if($task->user->unit)
                                                        <span class="badge bg-light text-success border fw-normal"><i class="bi bi-building me-1"></i>{{ $task->user->unit }}</span>
                                                    @endif
                                                </a>
                                            </div>
                                        @else
                                            <div class="fw-bold text-secondary small">{{ $task->user->name ?? '-' }}</div>
                                            <div class="text-muted small" style="font-size: 0.8rem;">{{ $task->user->jabatan ?? '-' }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border fw-normal">{{ $task->durasi ?? '-' }}</span>
                                    </td>
                                    <td class="pe-3 text-center">
                                        @if($task->status == 'Selesai')
                                            <span class="badge bg-success px-2 py-1"><i class="bi bi-check-lg me-1"></i>Selesai</span>
                                        @elseif($task->status == 'Proses')
                                            <span class="badge bg-primary px-2 py-1"><i class="bi bi-play-fill me-1"></i>Proses</span>
                                        @else
                                            <span class="badge bg-secondary px-2 py-1">Belum Dimulai</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                                        Belum ada data Rencana Kerja.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Info & Summary -->
    <div class="col-lg-4">
        <!-- Monitoring Status Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="card-title m-0 fw-bold fs-6 text-dark">
                    <i class="bi bi-shield-check text-primary me-2"></i>Status Kinerja Sistem
                </h5>
            </div>
            <div class="card-body p-3">
                <div class="p-3 rounded mb-3" style="background-color: #f8fafc; border: 1px solid #e2e8f0;">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-secondary small fw-semibold">Unit Kerja Logged-in</span>
                        <span class="badge bg-success">{{ auth()->user()->unit ?? 'Sistem' }}</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-secondary small fw-semibold">
                            @if(auth()->user() && auth()->user()->isAdmin()) Total Pegawai Sistem @else Total Pegawai Unit @endif
                        </span>
                        <span class="fw-bold text-dark">{{ $totalPegawai }} Pegawai</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="text-secondary small fw-semibold">Tanggal Hari Ini</span>
                        <span class="fw-semibold text-muted small">{{ date('d M Y') }}</span>
                    </div>
                </div>

                <div class="p-3 rounded" style="background-color: #f0fdf4; border: 1px solid #bbf7d0;">
                    <h6 class="fw-bold text-success mb-2" style="font-size: 0.9rem;">
                        <i class="bi bi-lightbulb-fill me-1"></i> Petunjuk Pengisian
                    </h6>
                    <ul class="ps-3 mb-0 text-dark small" style="line-height: 1.6;">
                        <li>Klik <strong>Mulai</strong> saat Anda memulai pengerjaan tugas.</li>
                        <li>Klik <strong>Selesai</strong> setelah pekerjaan rampung.</li>
                        <li>Sertakan berkas atau link Drive jika diperlukan.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#btn-voice-dashboard').on('click', function() {
            let voiceText = "Halo {{ auth()->user()->name }}. Berikut ringkasan capaian kinerja untuk unit {{ auth()->user()->unit ?? 'Universitas Ibnu Sina' }}. Total rencana kerja terdaftar sebanyak {{ $totalTugas }} tugas dengan tingkat capaian {{ $persentaseSelesai }} persen. ";
            
            @if(count($recentTasks) > 0)
                voiceText += "Berikut rincian uraian kerja: ";
                @foreach($recentTasks as $index => $task)
                    @php
                        $no = $index + 1;
                        $uraianStr = "Uraian Kerja " . $no . ": " . addslashes($task->uraian_tugas) . ". ";

                        // Estimasi
                        $estMulai = !empty($task->estimasi_tanggal_mulai) ? date('d/m/Y', strtotime($task->estimasi_tanggal_mulai)) : '';
                        $estJamMulai = !empty($task->estimasi_jam_mulai) ? substr($task->estimasi_jam_mulai, 0, 5) : '';
                        $estSelesai = !empty($task->estimasi_tanggal_selesai) ? date('d/m/Y', strtotime($task->estimasi_tanggal_selesai)) : '';
                        $estJamSelesai = !empty($task->estimasi_jam_selesai) ? substr($task->estimasi_jam_selesai, 0, 5) : '';
                        
                        $estStr = 'Estimasi: ';
                        if ($estMulai && $estSelesai && $estMulai === $estSelesai) {
                            $estStr .= $estMulai . ($estJamMulai || $estJamSelesai ? ' jam ' . $estJamMulai . ' sampai ' . $estJamSelesai . ' WIB' : '');
                        } elseif ($estMulai || $estSelesai) {
                            $estStr .= trim($estMulai . ' ' . $estJamMulai . ' WIB sampai ' . $estSelesai . ' ' . $estJamSelesai . ' WIB');
                        } else {
                            $estStr .= 'Belum ada';
                        }
                        $estStr .= '. ';

                        // Waktu Mulai
                        $tglM = !empty($task->tanggal_mulai) ? date('d/m/Y', strtotime($task->tanggal_mulai)) . ' ' : '';
                        $mulaiStr = 'Waktu mulai: ' . (!empty($task->waktu_mulai) && $task->waktu_mulai !== '00:00:00' ? $tglM . 'jam ' . substr($task->waktu_mulai, 0, 5) . ' WIB' : 'Belum dimulai') . '. ';

                        // Waktu Selesai
                        $tglS = !empty($task->tanggal_selesai) ? date('d/m/Y', strtotime($task->tanggal_selesai)) . ' ' : '';
                        $selesaiStr = 'Waktu selesai: ' . (!empty($task->waktu_selesai) && $task->waktu_selesai !== '00:00:00' ? $tglS . 'jam ' . substr($task->waktu_selesai, 0, 5) . ' WIB' : 'Belum selesai') . '. ';

                        // Durasi
                        $durasiStr = 'Durasi: ' . (!empty($task->durasi) && $task->durasi !== '-' ? $task->durasi : 'Belum ada') . '. ';

                        // Analisis Ketepatan Waktu
                        $analisisStr = 'Analisis: ';
                        if ($task->status === 'Selesai') {
                            if (!empty($task->estimasi_tanggal_selesai) && !empty($task->tanggal_selesai)) {
                                $estEnd = strtotime($task->estimasi_tanggal_selesai . ' ' . ($task->estimasi_jam_selesai ?? '23:59:59'));
                                $actEnd = strtotime($task->tanggal_selesai . ' ' . ($task->waktu_selesai ?? '00:00:00'));
                                $analisisStr .= ($actEnd <= $estEnd) ? 'Selesai tepat waktu.' : 'Selesai terlambat.';
                            } else {
                                $analisisStr .= 'Selesai tepat waktu.';
                            }
                        } elseif ($task->status === 'Proses') {
                            $analisisStr .= 'Masih berproses.';
                        } else {
                            $analisisStr .= 'Belum dikerjakan.';
                        }
                        $analisisStr .= ' ';

                        // Link Eksternal
                        $linkStr = 'Link eksternal: ' . (!empty($task->url_external) ? 'Ada link eksternal.' : 'Tidak ada link eksternal.') . ' ';
                    @endphp
                    voiceText += "{{ $uraianStr }}{{ addslashes($estStr) }}{{ addslashes($mulaiStr) }}{{ addslashes($selesaiStr) }}{{ addslashes($durasiStr) }}{{ addslashes($analisisStr) }}{{ addslashes($linkStr) }} ";
                @endforeach

                @php
                    $rekapText = " Rekapitulasi capaian kinerja: Dari total " . $totalTugas . " rencana kerja, sebanyak " . $tugasSelesai . " tugas telah selesai, " . $tugasProses . " tugas sedang berproses, dan " . $tugasBelumMulai . " tugas belum dimulai. ";
                    $rekapText .= "Tingkat capaian kinerja Anda mencapai " . $persentaseSelesai . " persen. ";

                    if ($persentaseSelesai >= 80) {
                        $rekapText .= "Capaian kinerja Anda sangat bagus dan sangat memuaskan! Pertahankan prestasi kinerja luar biasa ini!";
                    } elseif ($persentaseSelesai >= 50) {
                        $rekapText .= "Capaian kinerja Anda sudah baik, mari tingkatkan penyelesaian tugas-tugas yang masih berproses!";
                    } else {
                        $rekapText .= "Capaian kinerja Anda perlu peningkatan dan percepatan agar seluruh target tugas dapat rampung tepat waktu!";
                    }
                @endphp
                voiceText += "{{ addslashes($rekapText) }}";
            @else
                voiceText += "Belum ada rincian rencana kerja terdaftar.";
            @endif

            toggleSpeech(voiceText, 'btn-voice-dashboard', 'Dengarkan Suara');
        });

        function toggleSpeech(text, btnId, defaultText) {
            if (!('speechSynthesis' in window)) {
                alert('Peramban Anda belum mendukung fitur narasi suara.');
                return;
            }

            const btn = $('#' + btnId);

            if (window.speechSynthesis.speaking && !window.speechSynthesis.paused) {
                window.speechSynthesis.cancel();
                btn.removeClass('btn-danger').addClass('btn-warning').html('<i class="bi bi-volume-up-fill me-1 fs-6"></i> ' + defaultText);
                return;
            }

            window.speechSynthesis.cancel();

            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = 'id-ID';
            utterance.rate = 0.95;

            const voices = window.speechSynthesis.getVoices();
            const idVoice = voices.find(v => v.lang.includes('id') || v.lang.includes('ID'));
            if (idVoice) utterance.voice = idVoice;

            utterance.onstart = function() {
                btn.removeClass('btn-warning').addClass('btn-danger').html('<i class="bi bi-stop-fill me-1 fs-6"></i> Hentikan Suara');
            };

            utterance.onend = function() {
                btn.removeClass('btn-danger').addClass('btn-warning').html('<i class="bi bi-volume-up-fill me-1 fs-6"></i> ' + defaultText);
            };

            utterance.onerror = function() {
                btn.removeClass('btn-danger').addClass('btn-warning').html('<i class="bi bi-volume-up-fill me-1 fs-6"></i> ' + defaultText);
            };

            window.speechSynthesis.speak(utterance);
        }
    });
</script>
@endpush