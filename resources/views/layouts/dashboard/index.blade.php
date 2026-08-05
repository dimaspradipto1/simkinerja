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
    .stat-icon-purple { background: #f3e5f5; color: #7b1fa2; }
    .stat-icon-amber { background: #fff8e1; color: #ff8f00; }
    .btn-equal-width {
        min-width: 185px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-align: center;
    }
    @media (max-width: 575.98px) {
        .btn-equal-width {
            width: 100%;
        }
    }

    /* EdLink Style Weekly Calendar Styling */
    .edlink-card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        border: 1px solid #e9ecef;
    }
    .edlink-header {
        padding: 1.25rem 1.5rem 0.75rem 1.5rem;
    }
    .edlink-calendar-strip {
        padding: 0.5rem 1rem 1rem 1rem;
        border-bottom: 1px solid #f0f2f5;
        position: relative;
    }
    .edlink-day-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-decoration: none;
        padding: 6px 12px;
        border-radius: 12px;
        transition: all 0.2s ease;
        color: #495057;
        position: relative;
        cursor: pointer;
        user-select: none;
    }
    .edlink-day-btn:hover {
        background-color: #f8f9fa;
        color: #198754;
    }
    .edlink-day-btn.active {
        color: #198754;
        font-weight: 700;
    }
    .edlink-day-name {
        font-size: 0.85rem;
        font-weight: 500;
        margin-bottom: 4px;
    }
    .edlink-day-badge {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
        font-weight: 600;
        transition: all 0.2s ease;
    }
    .edlink-day-btn.active .edlink-day-badge {
        background-color: #198754;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(25, 135, 84, 0.35);
    }
    .edlink-active-arrow {
        position: absolute;
        bottom: -16px;
        width: 0;
        height: 0;
        border-left: 7px solid transparent;
        border-right: 7px solid transparent;
        border-bottom: 7px solid #f0f2f5;
        display: none;
    }
    .edlink-day-btn.active .edlink-active-arrow {
        display: block;
    }
    .edlink-empty-state {
        padding: 3rem 1.5rem;
        text-align: center;
    }
    .edlink-empty-icon {
        width: 90px;
        height: 90px;
        margin: 0 auto 1.25rem auto;
        opacity: 0.85;
    }
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
            <p class="mb-0 small text-white-50">Kelola dan pantau seluruh rencana kerja utama, kepanitiaan, insidentil, serta timer kegiatan Anda secara efisien.</p>
        </div>
        <div class="col-lg-4 text-lg-end d-flex flex-column align-items-lg-end gap-2 mt-3 mt-lg-0">
            <button type="button" id="btn-voice-dashboard" class="btn btn-warning fw-bold text-dark px-3 py-2 shadow-sm btn-equal-width">
                <i class="bi bi-volume-up-fill me-1 fs-6"></i> Dengarkan Suara
            </button>
            <a href="{{ route('rencana-kerja.index') }}" class="btn btn-light fw-bold text-success px-3 py-2 shadow-sm btn-equal-width">
                <i class="bi bi-list-check me-1"></i> Rencana Kerja
            </a>
            @if(auth()->check() && (!auth()->user()->isPimpinanUnit() && !auth()->user()->isAdmin()))
                <a href="{{ route('rencana-kerja.create') }}" class="btn btn-outline-light fw-bold px-3 py-2 btn-equal-width">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Tugas
                </a>
            @endif
        </div>
    </div>
</div>

<!-- Stat Metric Cards -->
<div class="row g-3 mb-4">
    <!-- Total Rencana Kerja Utama -->
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card stat-card p-3 h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Rencana Kerja Utama</span>
                    <h3 class="fw-bold mb-0 mt-1 text-dark">{{ $totalTugas }}</h3>
                </div>
                <div class="stat-icon stat-icon-teal">
                    <i class="bi bi-journal-check"></i>
                </div>
            </div>
            <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center">
                <small class="text-muted"><i class="bi bi-check-circle me-1"></i> {{ $tugasSelesai }} Selesai</small>
                <small class="text-success fw-bold">{{ $persentaseSelesai }}% Capaian</small>
            </div>
        </div>
    </div>

    <!-- Total Kepanitiaan -->
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card stat-card p-3 h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Tugas Kepanitiaan</span>
                    <h3 class="fw-bold mb-0 mt-1 text-primary">{{ $totalKepanitiaan }}</h3>
                </div>
                <div class="stat-icon stat-icon-purple">
                    <i class="bi bi-people-fill"></i>
                </div>
            </div>
            <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center">
                <small class="text-muted"><i class="bi bi-check-circle me-1"></i> {{ $kepanitiaanSelesai }} Selesai</small>
                <small class="text-primary fw-bold">{{ $totalKepanitiaan > 0 ? round(($kepanitiaanSelesai / $totalKepanitiaan) * 100) : 0 }}% Capaian</small>
            </div>
        </div>
    </div>

    <!-- Total Insidentil -->
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card stat-card p-3 h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Tugas Insidentil</span>
                    <h3 class="fw-bold mb-0 mt-1 text-warning">{{ $totalInsidentil }}</h3>
                </div>
                <div class="stat-icon stat-icon-amber">
                    <i class="bi bi-lightning-charge"></i>
                </div>
            </div>
            <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center">
                <small class="text-muted"><i class="bi bi-check-circle me-1"></i> {{ $insidentilSelesai }} Selesai</small>
                <small class="text-warning fw-bold">{{ $totalInsidentil > 0 ? round(($insidentilSelesai / $totalInsidentil) * 100) : 0 }}% Capaian</small>
            </div>
        </div>
    </div>
</div>

<!-- EdLink-Style Weekly Calendar Widget (Jadwal Minggu Ini) -->
<div class="card edlink-card shadow-sm border-0 mb-4">
    <div class="edlink-header d-flex flex-wrap align-items-center justify-content-between">
        <div>
            <h5 class="fw-bold text-dark fs-5 mb-0">Jadwal Minggu Ini</h5>
        </div>
        <div class="text-muted small">
            Hari ini: <span class="fw-bold text-dark">{{ \Carbon\Carbon::parse(date('Y-m-d'))->translatedFormat('j F Y') }}</span>
        </div>
    </div>

    <!-- Weekly Calendar Horizontal Strip -->
    <div class="edlink-calendar-strip">
        <div class="d-flex align-items-center justify-content-around text-center">
            @foreach($weekDays as $day)
                <a href="{{ request()->fullUrlWithQuery(['selected_date' => $day['date']]) }}" 
                   class="edlink-day-btn {{ $day['is_selected'] ? 'active' : '' }}" 
                   title="Lihat jadwal {{ $day['day_name'] }}, {{ $day['day_num'] }}">
                    <span class="edlink-day-name">{{ $day['day_name'] }}</span>
                    <span class="edlink-day-badge">{{ $day['day_num'] }}</span>
                    <div class="edlink-active-arrow"></div>
                </a>
            @endforeach
        </div>
    </div>

    <!-- Scheduled Tasks List on Selected Date -->
    <div class="card-body p-0">
        @if($scheduledTasksOnDate->count() > 0)
            <div class="p-3 bg-light border-bottom d-flex align-items-center justify-content-between">
                <span class="small fw-bold text-secondary text-uppercase">
                    <i class="bi bi-calendar-event text-success me-1"></i>
                    Jadwal {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('l, j F Y') }}
                </span>
                <span class="badge bg-success px-2 py-1">{{ $scheduledTasksOnDate->count() }} Kegiatan</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="4%" class="ps-3">No</th>
                            <th width="14%">Kategori</th>
                            <th width="32%">Uraian Tugas</th>
                            <th width="28%">Tanggal & Jam Estimasi</th>
                            <th width="22%" class="pe-3 text-center">Status & Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($scheduledTasksOnDate as $idx => $task)
                            <tr>
                                <td class="ps-3 text-muted fw-semibold">{{ $idx + 1 }}</td>
                                <td>
                                    <span class="badge {{ $task->badge_class }} px-2 py-1">
                                        {{ $task->kategori_label }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $task->uraian_tugas }}</div>
                                    <div class="small text-muted mt-1">
                                        <i class="bi bi-person-fill text-secondary me-1"></i>{{ $task->user ? $task->user->name : '-' }}
                                    </div>
                                    @if($task->taggedUsers && $task->taggedUsers->count() > 0)
                                        <div class="small text-muted" style="font-size: 0.75rem;">
                                            <strong>Rekan:</strong> {{ $task->taggedUsers->pluck('name')->implode(', ') }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="small">
                                        <div class="text-secondary">
                                            <i class="bi bi-calendar-range me-1 text-secondary"></i>
                                            {{ $task->estimasi_tanggal_mulai ? date('d/m/Y', strtotime($task->estimasi_tanggal_mulai)) : '-' }} 
                                            s/d 
                                            {{ $task->estimasi_tanggal_selesai ? date('d/m/Y', strtotime($task->estimasi_tanggal_selesai)) : '-' }}
                                        </div>
                                        @if($task->estimasi_jam_mulai)
                                            <div class="text-muted mt-1">
                                                <i class="bi bi-clock me-1"></i>{{ substr($task->estimasi_jam_mulai, 0, 5) }} - {{ substr($task->estimasi_jam_selesai ?? '23:59', 0, 5) }} WIB
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="pe-3 text-center">
                                    @if($task->status == 'Selesai')
                                        <span class="badge bg-success px-3 py-2 fw-semibold text-nowrap"><i class="bi bi-check-circle-fill me-1"></i>Selesai</span>
                                    @elseif($task->status == 'Proses' || $task->status == 'Berjalan')
                                        <button type="button" class="btn btn-sm btn-danger px-3 py-1 fw-bold shadow-sm btn-action-dash text-nowrap" data-type="{{ $task->kategori_code }}" data-action="stop" data-id="{{ $task->id }}">
                                            <i class="bi bi-stop-fill me-1"></i>Selesaikan
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-sm btn-success px-3 py-1 fw-bold shadow-sm btn-action-dash text-nowrap" data-type="{{ $task->kategori_code }}" data-action="start" data-id="{{ $task->id }}">
                                            <i class="bi bi-play-fill me-1"></i>Mulai
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <!-- EdLink Empty State Graphic -->
            <div class="edlink-empty-state">
                <div class="edlink-empty-icon">
                    <svg viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="25" y="30" width="70" height="65" rx="12" fill="#F1F3F5" stroke="#CED4DA" stroke-width="3"/>
                        <rect x="25" y="30" width="70" height="18" rx="12" fill="#E9ECEF"/>
                        <circle cx="42" cy="22" r="4" fill="#6C757D"/>
                        <circle cx="78" cy="22" r="4" fill="#6C757D"/>
                        <rect x="36" y="58" width="10" height="10" rx="3" fill="#DDE2E5"/>
                        <rect x="55" y="58" width="10" height="10" rx="3" fill="#DDE2E5"/>
                        <rect x="74" y="58" width="10" height="10" rx="3" fill="#DDE2E5"/>
                        <rect x="36" y="74" width="10" height="10" rx="3" fill="#DDE2E5"/>
                        <rect x="55" y="74" width="10" height="10" rx="3" fill="#DDE2E5"/>
                        <circle cx="85" cy="85" r="18" fill="#FFFFFF" stroke="#ADB5BD" stroke-width="3"/>
                        <path d="M80 85L84 89L91 81" stroke="#198754" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h6 class="fw-semibold text-secondary mb-1">Tidak ada jadwal kegiatan di tanggal ini</h6>
                <small class="text-muted">Pilih tanggal lain pada kalender minggu di atas untuk melihat jadwal tugas.</small>
            </div>
        @endif
    </div>
</div>



<!-- Main Content Grid -->
<div class="row g-3">
    <!-- Left Column: Recent Activity Tables -->
    <div class="col-lg-8">
        
        <!-- Table 1: Rencana Kerja Utama Terbaru -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <h5 class="card-title m-0 fw-bold fs-6 text-dark">
                        <i class="bi bi-journal-check text-success me-2"></i>Rencana Kerja Utama
                    </h5>
                    <div class="d-flex align-items-center gap-2">
                        <form action="{{ route('dashboard') }}" method="GET" class="d-flex align-items-center">
                            @if(request('page_kepanitiaan')) <input type="hidden" name="page_kepanitiaan" value="{{ request('page_kepanitiaan') }}"> @endif
                            @if(request('page_insidentil')) <input type="hidden" name="page_insidentil" value="{{ request('page_insidentil') }}"> @endif
                            <div class="input-group input-group-sm" style="max-width: 220px;">
                                <input type="text" name="search_utama" class="form-control" placeholder="Cari..." value="{{ request('search_utama') }}">
                                <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                            </div>
                        </form>
                        <a href="{{ route('rencana-kerja.index') }}" class="btn btn-sm btn-outline-success fw-semibold text-nowrap">
                            Lihat Semua <i class="bi bi-arrow-right me-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="4%" class="ps-3">No</th>
                                <th width="30%">Uraian Tugas</th>
                                <th width="26%">Pegawai / Jabatan</th>
                                <th width="22%">Tanggal (Estimasi & Realisasi)</th>
                                <th width="18%" class="pe-3 text-center">Status & Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTasks as $index => $task)
                                <tr>
                                    <td class="ps-3 text-muted fw-semibold">{{ $recentTasks->firstItem() + $index }}</td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $task->uraian_tugas }}</div>
                                        @if($task->file || $task->url_external)
                                            <div class="small text-muted mt-1">
                                                @if($task->file)
                                                    <span class="badge bg-light text-secondary border me-1"><i class="bi bi-paperclip me-1"></i>Berkas</span>
                                                @endif
                                                @if($task->url_external)
                                                    <span class="badge bg-light text-primary border"><i class="bi bi-link-45deg me-1"></i>Link</span>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($task->user && $task->user->jabatan)
                                            <div class="text-dark fw-bold small">
                                                <i class="bi bi-person-fill text-success me-1"></i>{{ $task->user->name ?? '-' }}
                                            </div>
                                            <div class="small mt-1">
                                                <span class="badge bg-light text-dark border fw-semibold me-1"><i class="bi bi-briefcase-fill text-secondary me-1"></i>{{ $task->user->jabatan ?? '-' }}</span>
                                            </div>
                                        @else
                                            <div class="fw-bold text-secondary small">{{ $task->user->name ?? '-' }}</div>
                                        @endif
                                        @if($task->taggedUsers && $task->taggedUsers->count() > 0)
                                            <div class="small text-muted mt-1" style="font-size: 0.75rem;">
                                                <strong>Rekan:</strong> {{ $task->taggedUsers->pluck('name')->implode(', ') }}
                                            </div>
                                        @endif
                                    </td>

                                    <td>
                                        <div class="small">
                                            <div class="text-secondary">
                                                <i class="bi bi-calendar-event me-1 text-secondary"></i>
                                                {{ $task->estimasi_tanggal_mulai ? date('d/m/Y', strtotime($task->estimasi_tanggal_mulai)) : '-' }} 
                                                s/d 
                                                {{ $task->estimasi_tanggal_selesai ? date('d/m/Y', strtotime($task->estimasi_tanggal_selesai)) : '-' }}
                                            </div>
                                            @if($task->tanggal_mulai)
                                                <div class="text-success mt-1">
                                                    <i class="bi bi-play-circle me-1"></i>Mulai: {{ date('d/m/Y', strtotime($task->tanggal_mulai)) }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="pe-3 text-center">
                                        @if($task->status == 'Selesai')
                                            <span class="badge bg-success px-3 py-2 fw-semibold text-nowrap"><i class="bi bi-check-circle-fill me-1"></i>Selesai</span>
                                        @elseif($task->status == 'Proses' || $task->status == 'Berjalan')
                                            <button type="button" class="btn btn-sm btn-danger px-3 py-1 fw-bold shadow-sm btn-action-dash text-nowrap" data-type="rencana-kerja" data-action="stop" data-id="{{ $task->id }}">
                                                <i class="bi bi-stop-fill me-1"></i>Selesaikan
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-sm btn-success px-3 py-1 fw-bold shadow-sm btn-action-dash text-nowrap" data-type="rencana-kerja" data-action="start" data-id="{{ $task->id }}">
                                                <i class="bi bi-play-fill me-1"></i>Mulai
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                                        Belum ada data Rencana Kerja Utama.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($recentTasks->hasPages() || $recentTasks->total() > 0)
                <div class="card-footer bg-white border-top py-2 d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div class="small text-muted">
                        Menampilkan <strong>{{ $recentTasks->firstItem() ?? 0 }}</strong> s/d <strong>{{ $recentTasks->lastItem() ?? 0 }}</strong> dari <strong>{{ $recentTasks->total() }}</strong> data
                    </div>
                    <div>
                        {{ $recentTasks->links() }}
                    </div>
                </div>
            @endif
        </div>

        <!-- Table 2: Kepanitiaan Terbaru -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <h5 class="card-title m-0 fw-bold fs-6 text-dark">
                        <i class="bi bi-people-fill text-primary me-2"></i>Tugas Kepanitiaan
                    </h5>
                    <div class="d-flex align-items-center gap-2">
                        <form action="{{ route('dashboard') }}" method="GET" class="d-flex align-items-center">
                            @if(request('page_utama')) <input type="hidden" name="page_utama" value="{{ request('page_utama') }}"> @endif
                            @if(request('page_insidentil')) <input type="hidden" name="page_insidentil" value="{{ request('page_insidentil') }}"> @endif
                            <div class="input-group input-group-sm" style="max-width: 220px;">
                                <input type="text" name="search_kepanitiaan" class="form-control" placeholder="Cari..." value="{{ request('search_kepanitiaan') }}">
                                <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                            </div>
                        </form>
                        <a href="{{ route('kepanitiaan.index') }}" class="btn btn-sm btn-outline-primary fw-semibold text-nowrap">
                            Lihat Semua <i class="bi bi-arrow-right me-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="4%" class="ps-3">No</th>
                                <th width="30%">Uraian Tugas Kepanitiaan</th>
                                <th width="26%">Pembuat & Rekan Kerja</th>
                                <th width="22%">Tanggal (Estimasi & Realisasi)</th>
                                <th width="18%" class="pe-3 text-center">Status & Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentKepanitiaan as $index => $kp)
                                <tr>
                                    <td class="ps-3 text-muted fw-semibold">{{ $recentKepanitiaan->firstItem() + $index }}</td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $kp->uraian_tugas }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark small"><i class="bi bi-person-circle me-1 text-primary"></i>{{ $kp->user ? $kp->user->name : '-' }}</div>
                                        @if($kp->taggedUsers->count() > 0)
                                            <div class="small text-muted mt-1" style="font-size: 0.75rem;">
                                                <strong>Rekan:</strong> {{ $kp->taggedUsers->pluck('name')->implode(', ') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="small">
                                            <div class="text-secondary">
                                                <i class="bi bi-calendar-event me-1 text-secondary"></i>
                                                {{ $kp->estimasi_tanggal_mulai ? date('d/m/Y', strtotime($kp->estimasi_tanggal_mulai)) : '-' }}
                                                s/d
                                                {{ $kp->estimasi_tanggal_selesai ? date('d/m/Y', strtotime($kp->estimasi_tanggal_selesai)) : '-' }}
                                            </div>
                                            @if($kp->tanggal_mulai)
                                                <div class="text-success mt-1">
                                                    <i class="bi bi-play-circle me-1"></i>Mulai: {{ date('d/m/Y', strtotime($kp->tanggal_mulai)) }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="pe-3 text-center">
                                        @if($kp->status == 'Selesai')
                                            <span class="badge bg-success px-3 py-2 fw-semibold text-nowrap"><i class="bi bi-check-circle-fill me-1"></i>Selesai</span>
                                        @elseif($kp->status == 'Proses' || $kp->status == 'Berjalan')
                                            <button type="button" class="btn btn-sm btn-danger px-3 py-1 fw-bold shadow-sm btn-action-dash text-nowrap" data-type="kepanitiaan" data-action="stop" data-id="{{ $kp->id }}">
                                                <i class="bi bi-stop-fill me-1"></i>Selesaikan
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-sm btn-success px-3 py-1 fw-bold shadow-sm btn-action-dash text-nowrap" data-type="kepanitiaan" data-action="start" data-id="{{ $kp->id }}">
                                                <i class="bi bi-play-fill me-1"></i>Mulai
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                                        Belum ada data Tugas Kepanitiaan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($recentKepanitiaan->hasPages() || $recentKepanitiaan->total() > 0)
                <div class="card-footer bg-white border-top py-2 d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div class="small text-muted">
                        Menampilkan <strong>{{ $recentKepanitiaan->firstItem() ?? 0 }}</strong> s/d <strong>{{ $recentKepanitiaan->lastItem() ?? 0 }}</strong> dari <strong>{{ $recentKepanitiaan->total() }}</strong> data
                    </div>
                    <div>
                        {{ $recentKepanitiaan->links() }}
                    </div>
                </div>
            @endif
        </div>

        <!-- Table 3: Insidentil Terbaru -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <h5 class="card-title m-0 fw-bold fs-6 text-dark">
                        <i class="bi bi-lightning-charge text-warning me-2"></i>Tugas Insidentil
                    </h5>
                    <div class="d-flex align-items-center gap-2">
                        <form action="{{ route('dashboard') }}" method="GET" class="d-flex align-items-center">
                            @if(request('page_utama')) <input type="hidden" name="page_utama" value="{{ request('page_utama') }}"> @endif
                            @if(request('page_kepanitiaan')) <input type="hidden" name="page_kepanitiaan" value="{{ request('page_kepanitiaan') }}"> @endif
                            <div class="input-group input-group-sm" style="max-width: 220px;">
                                <input type="text" name="search_insidentil" class="form-control" placeholder="Cari..." value="{{ request('search_insidentil') }}">
                                <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                            </div>
                        </form>
                        <a href="{{ route('insidentil.index') }}" class="btn btn-sm btn-outline-warning text-dark fw-semibold text-nowrap">
                            Lihat Semua <i class="bi bi-arrow-right me-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="4%" class="ps-3">No</th>
                                <th width="30%">Uraian Tugas Insidentil</th>
                                <th width="26%">Pembuat & Rekan Kerja</th>
                                <th width="22%">Tanggal (Estimasi & Realisasi)</th>
                                <th width="18%" class="pe-3 text-center">Status & Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentInsidentil as $index => $ins)
                                <tr>
                                    <td class="ps-3 text-muted fw-semibold">{{ $recentInsidentil->firstItem() + $index }}</td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $ins->uraian_tugas }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark small"><i class="bi bi-person-circle me-1 text-warning"></i>{{ $ins->user ? $ins->user->name : '-' }}</div>
                                        @if($ins->taggedUsers->count() > 0)
                                            <div class="small text-muted mt-1" style="font-size: 0.75rem;">
                                                <strong>Rekan:</strong> {{ $ins->taggedUsers->pluck('name')->implode(', ') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="small">
                                            <div class="text-secondary">
                                                <i class="bi bi-calendar-event me-1 text-secondary"></i>
                                                {{ $ins->estimasi_tanggal_mulai ? date('d/m/Y', strtotime($ins->estimasi_tanggal_mulai)) : '-' }}
                                                s/d
                                                {{ $ins->estimasi_tanggal_selesai ? date('d/m/Y', strtotime($ins->estimasi_tanggal_selesai)) : '-' }}
                                            </div>
                                            @if($ins->tanggal_mulai)
                                                <div class="text-success mt-1">
                                                    <i class="bi bi-play-circle me-1"></i>Mulai: {{ date('d/m/Y', strtotime($ins->tanggal_mulai)) }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="pe-3 text-center">
                                        @if($ins->status == 'Selesai')
                                            <span class="badge bg-success px-3 py-2 fw-semibold text-nowrap"><i class="bi bi-check-circle-fill me-1"></i>Selesai</span>
                                        @elseif($ins->status == 'Proses' || $ins->status == 'Berjalan')
                                            <button type="button" class="btn btn-sm btn-danger px-3 py-1 fw-bold shadow-sm btn-action-dash text-nowrap" data-type="insidentil" data-action="stop" data-id="{{ $ins->id }}">
                                                <i class="bi bi-stop-fill me-1"></i>Selesaikan
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-sm btn-success px-3 py-1 fw-bold shadow-sm btn-action-dash text-nowrap" data-type="insidentil" data-action="start" data-id="{{ $ins->id }}">
                                                <i class="bi bi-play-fill me-1"></i>Mulai
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                                        Belum ada data Tugas Insidentil.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($recentInsidentil->hasPages() || $recentInsidentil->total() > 0)
                <div class="card-footer bg-white border-top py-2 d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div class="small text-muted">
                        Menampilkan <strong>{{ $recentInsidentil->firstItem() ?? 0 }}</strong> s/d <strong>{{ $recentInsidentil->lastItem() ?? 0 }}</strong> dari <strong>{{ $recentInsidentil->total() }}</strong> data
                    </div>
                    <div>
                        {{ $recentInsidentil->links() }}
                    </div>
                </div>
            @endif
        </div>

    </div>

    <!-- Right Column: Monitoring & Quick Navigation -->
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
        // Handle Mulai & Selesai button click on Dashboard
        $(document).on('click', '.btn-action-dash', function() {
            let btn = $(this);
            let type = btn.data('type');
            let action = btn.data('action');
            let id = btn.data('id');
            let actionText = action === 'start' ? 'memulai' : 'menyelesaikan';

            btn.prop('disabled', true);

            $.ajax({
                url: "/" + type + "/" + id + "/" + action,
                type: "POST",
                data: { _token: "{{ csrf_token() }}" },
                success: function(res) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        location.reload();
                    }
                },
                error: function(err) {
                    btn.prop('disabled', false);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Gagal', err.responseJSON ? err.responseJSON.message : 'Terjadi kesalahan.', 'error');
                    } else {
                        alert('Terjadi kesalahan saat ' + actionText + ' tugas.');
                    }
                }
            });
        });

        $('#btn-voice-dashboard').on('click', function() {
            let voiceText = "Halo {{ auth()->user()->name }}. Berikut ringkasan capaian kinerja untuk unit {{ auth()->user()->unit ?? 'Universitas Ibnu Sina' }}. Total rencana kerja utama terdaftar sebanyak {{ $totalTugas }} tugas dengan tingkat capaian {{ $persentaseSelesai }} persen. Tugas kepanitiaan terdaftar sebanyak {{ $totalKepanitiaan }} tugas, dan tugas insidentil terdaftar sebanyak {{ $totalInsidentil }} tugas. ";
            
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