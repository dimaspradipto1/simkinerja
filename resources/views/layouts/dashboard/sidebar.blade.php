<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

        @php
            $u = Auth::user();
            $isAdmin = $u && ($u->isAdmin() || $u->isSuperAdmin());
            $isRektorat = $u && $u->isPimpinanRektorat() && !$isAdmin;
            $isPimpinanUnit = $u && $u->isPimpinanUnit() && !$u->isPimpinanRektorat() && !$isAdmin;
            $isStaffOrDosen = $u && !$isAdmin && !$u->isPimpinanRektorat() && !$isPimpinanUnit && !$u->isOnlyKepanitiaan();
            $isOnlyPanitia = $u && $u->isOnlyKepanitiaan();

            // Ambil sesi presensi aktif untuk admin, pimpinan, atau panitia
            $activeSessions = collect();
            if ($isAdmin || ($u && $u->canAccessAbsensi())) {
                $activeSessions = \App\Models\KepanitiaanSesiAbsensi::where('is_active', true)
                    ->with('kepanitiaan')
                    ->orderBy('id', 'asc')
                    ->get();
            }
        @endphp

        <!-- 1. DASHBOARD UTAMA (Semua Role) -->
        <li class="nav-heading">Utama</li>
        <li class="nav-item">
            <a class="{{ request()->routeIs('dashboard') ? 'nav-link' : 'nav-link collapsed' }}"
                href="{{ route('dashboard') }}">
                <i class="bi bi-grid-fill text-primary"></i>
                <span>
                    @if($isAdmin) Dashboard Admin
                    @elseif($isRektorat) Dashboard Eksekutif
                    @elseif($isPimpinanUnit) Dashboard Unit
                    @else Dashboard Saya
                    @endif
                </span>
            </a>
        </li>

        <!-- ============================================================ -->
        <!-- 2. TUGAS & MANAJEMEN KINERJA (Dropdown Rencana Kerja) -->
        <!-- ============================================================ -->
        @if (!$isOnlyPanitia)
            <li class="nav-heading">
                @if($isAdmin) Manajemen Kinerja
                @elseif($isRektorat) Kinerja Rektorat
                @elseif($isPimpinanUnit) Kinerja Unit
                @else Tugas & Kinerja
                @endif
            </li>

            @php
                $isKinerjaActive = request()->routeIs('rencana-kerja.*') || 
                                   request()->routeIs('kepanitiaan.*') || 
                                   request()->routeIs('insidentil.*') || 
                                   request()->routeIs('kegiatan.*');
            @endphp
            <li class="nav-item">
                <a class="{{ $isKinerjaActive ? 'nav-link' : 'nav-link collapsed' }}"
                    data-bs-target="#rencana-kerja-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-briefcase-fill text-success"></i>
                    <span>
                        @if($isStaffOrDosen) Rencana Kerja
                        @elseif($isPimpinanUnit) Rencana Kerja Unit
                        @else Rencana Kerja
                        @endif
                    </span>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="rencana-kerja-nav"
                    class="nav-content collapse {{ $isKinerjaActive ? 'show' : '' }}"
                    data-bs-parent="#sidebar-nav">
                    <li>
                        <a href="{{ route('kegiatan.create') }}" class="{{ request()->routeIs('kegiatan.*') ? 'active' : '' }}">
                            <i class="bi bi-circle"></i><span>Tambah Kegiatan</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('rencana-kerja.index') }}" class="{{ request()->routeIs('rencana-kerja.*') ? 'active' : '' }}">
                            <i class="bi bi-circle"></i><span>
                                @if($isStaffOrDosen) Rencana Kerja Saya
                                @elseif($isPimpinanUnit) Rencana Kerja Unit
                                @else Rencana Kerja Utama
                                @endif
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('kepanitiaan.index') }}" class="{{ request()->routeIs('kepanitiaan.*') ? 'active' : '' }}">
                            <i class="bi bi-circle"></i><span>Rencana Kerja Kepanitiaan</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('insidentil.index') }}" class="{{ request()->routeIs('insidentil.*') ? 'active' : '' }}">
                            <i class="bi bi-circle"></i><span>Rencana Kerja Insidentil</span>
                        </a>
                    </li>
                </ul>
            </li>
        @endif

        <!-- ============================================================ -->
        <!-- 3. MONITORING & LAPORAN -->
        <!-- ============================================================ -->
        <li class="nav-heading">Monitoring & Laporan</li>

        @if($isAdmin || $isRektorat || $isPimpinanUnit)
            @php
                $isMonitoringActive = request()->routeIs('rekapitulasi.*') || 
                                      request()->routeIs('rekapitulasi-kepanitiaan.*') || 
                                      request()->routeIs('rekapitulasi-insidentil.*') || 
                                      request()->routeIs('analisis-keterlambatan.*');
            @endphp
            <li class="nav-item">
                <a class="{{ $isMonitoringActive ? 'nav-link' : 'nav-link collapsed' }}"
                    data-bs-target="#monitoring-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-pie-chart-fill text-info"></i>
                    <span>
                        @if($isAdmin || $isRektorat) Monitoring & Evaluasi
                        @else Monitoring Unit
                        @endif
                    </span>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="monitoring-nav"
                    class="nav-content collapse {{ $isMonitoringActive ? 'show' : '' }}"
                    data-bs-parent="#sidebar-nav">
                    <li>
                        <a href="{{ route('rekapitulasi.index') }}" class="{{ request()->routeIs('rekapitulasi.*') || request()->routeIs('rekapitulasi-kepanitiaan.*') || request()->routeIs('rekapitulasi-insidentil.*') ? 'active' : '' }}">
                            <i class="bi bi-circle"></i><span>
                                @if($isPimpinanUnit) Rekapitulasi Kinerja Unit
                                @else Rekapitulasi Kinerja
                                @endif
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('analisis-keterlambatan.index') }}" class="{{ request()->routeIs('analisis-keterlambatan.*') ? 'active' : '' }}">
                            <i class="bi bi-circle"></i><span>Analisis Keterlambatan</span>
                        </a>
                    </li>
                </ul>
            </li>
        @else
            <li class="nav-item">
                <a class="{{ request()->routeIs('rekapitulasi.*') || request()->routeIs('rekapitulasi-kepanitiaan.*') || request()->routeIs('rekapitulasi-insidentil.*') ? 'nav-link' : 'nav-link collapsed' }}"
                    href="{{ route('rekapitulasi.index') }}">
                    <i class="bi bi-pie-chart-fill text-info"></i>
                    <span>Rekapitulasi Capaian Saya</span>
                </a>
            </li>
        @endif

        <!-- LPJ Menu (Jika punya hak akses LPJ) -->
        @if ($u && $u->canAccessLpj())
            <li class="nav-item">
                <a class="{{ request()->routeIs('lpj.*') ? 'nav-link' : 'nav-link collapsed' }}"
                    data-bs-target="#lpj-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-file-earmark-text-fill text-secondary"></i>
                    <span>Laporan LPJ</span>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="lpj-nav"
                    class="nav-content collapse {{ request()->routeIs('lpj.*') ? 'show' : '' }}"
                    data-bs-parent="#sidebar-nav">
                    <li>
                        <a href="{{ route('lpj.index') }}" class="{{ request()->routeIs('lpj.index') ? 'active' : '' }}">
                            <i class="bi bi-circle"></i><span>Daftar LPJ</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('lpj.create') }}" class="{{ request()->routeIs('lpj.create') ? 'active' : '' }}">
                            <i class="bi bi-circle"></i><span>Tambah LPJ</span>
                        </a>
                    </li>
                    @if ($isAdmin || ($u && $u->isTimEvaluasi()))
                        <li>
                            <a href="{{ route('lpj.template') }}" class="{{ request()->routeIs('lpj.template*') ? 'active' : '' }}">
                                <i class="bi bi-circle"></i><span>Template LPJ</span>
                            </a>
                        </li>
                    @endif
                </ul>
            </li>
        @endif

        <!-- Laporan Eksekutif & Yayasan (Admin, Rektorat, & Pimpinan Unit) -->
        @if ($isAdmin || $isRektorat || $isPimpinanUnit)
            @php
                $isLaporanActive = request()->routeIs('laporan-kinerja-pegawai.*') || 
                                   request()->routeIs('laporan-pengembangan.*');
            @endphp
            <li class="nav-item">
                <a class="{{ $isLaporanActive ? 'nav-link' : 'nav-link collapsed' }}"
                    data-bs-target="#laporan-eksekutif-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-file-earmark-bar-graph-fill text-warning"></i>
                    <span>
                        @if($isAdmin || $isRektorat) Laporan Eksekutif
                        @else Laporan Unit Kerja
                        @endif
                    </span>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="laporan-eksekutif-nav"
                    class="nav-content collapse {{ $isLaporanActive ? 'show' : '' }}"
                    data-bs-parent="#sidebar-nav">
                    <li>
                        <a href="{{ route('laporan-kinerja-pegawai.index') }}" class="{{ request()->routeIs('laporan-kinerja-pegawai.*') ? 'active' : '' }}">
                            <i class="bi bi-circle"></i><span>Laporan Kinerja Pegawai</span>
                        </a>
                    </li>
                    @if($isAdmin || $isRektorat)
                        <li>
                            <a href="{{ route('laporan-pengembangan.index') }}" class="{{ request()->routeIs('laporan-pengembangan.*') ? 'active' : '' }}">
                                <i class="bi bi-circle"></i><span>Pengembangan Universitas</span>
                            </a>
                        </li>
                    @endif
                </ul>
            </li>
        @endif

        <!-- ============================================================ -->
        <!-- 4. PRESENSI KEPANITIAAN (Sesuai Role) -->
        <!-- ============================================================ -->
        @if ($isAdmin)
            <li class="nav-heading" id="sidebar-presensi-heading">Presensi Kepanitiaan</li>

            <!-- Sesi Presensi Aktif (Dropdown Menu dengan Real-Time Sync) -->
            <li class="nav-item" id="sidebar-presensi-active-item" style="{{ $activeSessions->isEmpty() ? 'display: none;' : '' }}">
                <a class="{{ request()->routeIs('presensi.*') || request()->routeIs('absensi-*') ? 'nav-link' : 'nav-link collapsed' }}"
                    data-bs-target="#presensi-kepanitiaan-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-qr-code-scan text-success"></i><span>Sesi Presensi Aktif</span>
                    <span class="badge bg-success rounded-pill ms-2 px-2 py-0" id="sidebar-active-sessions-badge" style="font-size: 10px;">{{ $activeSessions->count() }}</span>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="presensi-kepanitiaan-nav"
                    class="nav-content collapse {{ request()->routeIs('presensi.*') || request()->routeIs('absensi-*') ? 'show' : '' }}"
                    data-bs-parent="#sidebar-nav">
                    @foreach($activeSessions as $activeSess)
                        @php
                            $targetUrl = route('presensi.sesi.show', $activeSess->id);
                        @endphp
                        <li id="sidebar-sesi-item-{{ $activeSess->id }}">
                            <a href="{{ $targetUrl }}" 
                               class="d-flex align-items-center gap-2 py-1.5 px-3 {{ request()->routeIs('presensi.sesi.show') && request()->route('id') == $activeSess->id ? 'active' : '' }}" 
                               title="{{ $activeSess->nama_sesi }}">
                                <i class="bi bi-circle-fill text-success flex-shrink-0" style="font-size: 6px;"></i>
                                <span class="text-truncate" style="font-size: 13px; font-weight: 500;">{{ $activeSess->nama_sesi }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </li>

            <li class="nav-item">
                <a class="{{ request()->routeIs('absensi-kinerja.*') ? 'nav-link' : 'nav-link collapsed' }}"
                    href="{{ route('absensi-kinerja.index') }}">
                    <i class="bi bi-person-lines-fill text-primary"></i>
                    <span>Absensi Kinerja</span>
                </a>
            </li>

            @php
                $isPresensiAdminActive = request()->routeIs('pengaturan-absensi.*') || 
                                         request()->routeIs('rekapitulasi-absensi.*');
            @endphp
            <li class="nav-item">
                <a class="{{ $isPresensiAdminActive ? 'nav-link' : 'nav-link collapsed' }}"
                    data-bs-target="#kelola-presensi-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-clipboard-check-fill text-primary"></i>
                    <span>Kelola Presensi</span>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="kelola-presensi-nav"
                    class="nav-content collapse {{ $isPresensiAdminActive ? 'show' : '' }}"
                    data-bs-parent="#sidebar-nav">
                    <li>
                        <a href="{{ route('pengaturan-absensi.index') }}" class="{{ request()->routeIs('pengaturan-absensi.*') ? 'active' : '' }}">
                            <i class="bi bi-circle"></i><span>Status Sesi Absensi</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('rekapitulasi-absensi.index') }}" class="{{ request()->routeIs('rekapitulasi-absensi.*') ? 'active' : '' }}">
                            <i class="bi bi-circle"></i><span>Rekapitulasi Absensi</span>
                        </a>
                    </li>
                </ul>
            </li>
        @elseif ($u)
            <li class="nav-heading" id="sidebar-presensi-heading">Presensi Kepanitiaan</li>
            
            @if($activeSessions->isNotEmpty() && ($u->isKoordinatorPanitia() || $u->isKepanitiaan()))
                <li class="nav-item">
                    <a class="{{ request()->routeIs('presensi.*') ? 'nav-link' : 'nav-link collapsed' }}"
                        data-bs-target="#presensi-user-nav" data-bs-toggle="collapse" href="#">
                        <i class="bi bi-qr-code-scan text-success"></i><span>Sesi Aktif ({{ $activeSessions->count() }})</span>
                        <i class="bi bi-chevron-down ms-auto"></i>
                    </a>
                    <ul id="presensi-user-nav"
                        class="nav-content collapse {{ request()->routeIs('presensi.*') ? 'show' : '' }}"
                        data-bs-parent="#sidebar-nav">
                        @foreach($activeSessions as $activeSess)
                            <li>
                                <a href="{{ route('presensi.sesi.show', $activeSess->id) }}" class="{{ request()->routeIs('presensi.sesi.show') && request()->route('id') == $activeSess->id ? 'active' : '' }}">
                                    <i class="bi bi-circle"></i><span class="text-truncate">{{ $activeSess->nama_sesi }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </li>
            @endif

            <li class="nav-item">
                <a class="{{ request()->routeIs('absensi-kinerja.*') ? 'nav-link' : 'nav-link collapsed' }}"
                    href="{{ route('absensi-kinerja.index') }}">
                    <i class="bi bi-person-lines-fill text-primary"></i>
                    <span>Absensi Kinerja</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="{{ request()->routeIs('rekapitulasi-absensi.*') ? 'nav-link' : 'nav-link collapsed' }}"
                    href="{{ route('rekapitulasi-absensi.index') }}">
                    <i class="bi bi-person-check-fill text-success"></i>
                    <span>Riwayat Presensi Saya</span>
                </a>
            </li>
        @endif

        <!-- ============================================================ -->
        <!-- 5. PENGATURAN MASTER SISTEM (Hanya Admin / Super Admin) -->
        <!-- ============================================================ -->
        @if ($isAdmin)
            <li class="nav-heading">Pengaturan Sistem</li>

            @php
                $isSettingsActive = request()->routeIs('user.*') || 
                                    request()->routeIs('periode-akademik.*') || 
                                    request()->routeIs('kategori.*') ||
                                    request()->routeIs('hirarki.*') ||
                                    request()->routeIs('sk-kepanitiaan.*');
            @endphp
            <li class="nav-item">
                <a class="{{ $isSettingsActive ? 'nav-link' : 'nav-link collapsed' }}"
                    data-bs-target="#pengaturan-sistem-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-gear-wide-connected text-secondary"></i>
                    <span>Pengaturan Sistem</span>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="pengaturan-sistem-nav"
                    class="nav-content collapse {{ $isSettingsActive ? 'show' : '' }}"
                    data-bs-parent="#sidebar-nav">
                    <li>
                        <a href="{{ route('sk-kepanitiaan.index') }}" class="{{ request()->routeIs('sk-kepanitiaan.*') ? 'active' : '' }}">
                            <i class="bi bi-file-earmark-pdf"></i><span>SK Kepanitiaan</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('user.index') }}" class="{{ request()->routeIs('user.*') ? 'active' : '' }}">
                            <i class="bi bi-circle"></i><span>Kelola Pengguna</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('periode-akademik.index') }}" class="{{ request()->routeIs('periode-akademik.*') ? 'active' : '' }}">
                            <i class="bi bi-circle"></i><span>Periode Akademik</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('kategori.index') }}" class="{{ request()->routeIs('kategori.*') ? 'active' : '' }}">
                            <i class="bi bi-circle"></i><span>Kategori Kegiatan</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('hirarki.index') }}" class="{{ request()->routeIs('hirarki.*') ? 'active' : '' }}">
                            <i class="bi bi-diagram-3"></i><span>Struktur Hirarki</span>
                        </a>
                    </li>
                </ul>
            </li>
        @endif

    </ul>

</aside><!-- End Sidebar-->

<style>
    /* Styling untuk merapikan submenu dan navigasi */
    .sidebar-nav .nav-heading {
        font-size: 11px;
        text-transform: uppercase;
        color: #899bbd;
        font-weight: 700;
        margin: 14px 0 6px 15px;
        letter-spacing: 0.5px;
    }
    .sidebar-nav .nav-link {
        font-size: 13.5px;
        font-weight: 600;
        padding: 9px 15px;
        border-radius: 8px;
        margin-bottom: 3px;
        transition: all 0.2s ease;
    }
    .sidebar-nav .nav-link i {
        font-size: 16px;
        margin-right: 10px;
    }
    .sidebar-nav .nav-link:not(.collapsed) {
        background: #eef2ff;
        color: #4154f1;
    }
    .sidebar-nav .nav-link:not(.collapsed) i {
        color: #4154f1;
    }
    .sidebar-nav .nav-content {
        padding: 2px 0 6px 0;
    }
    .sidebar-nav .nav-content a {
        padding: 7px 12px 7px 22px !important;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 8px;
        border-radius: 6px;
        margin: 1px 0 1px 12px;
        transition: all 0.2s ease;
    }
    .sidebar-nav .nav-content a i {
        font-size: 6px !important;
        margin-right: 0 !important;
        flex-shrink: 0;
        transition: all 0.2s ease;
    }
    .sidebar-nav .nav-content a span {
        font-size: 13px;
        line-height: 1.3;
        white-space: normal;
        word-break: break-word;
    }
    .sidebar-nav .nav-content a:hover {
        background: rgba(65, 84, 241, 0.06);
        color: #4154f1;
    }
    .sidebar-nav .nav-content a:hover i {
        background-color: #4154f1;
    }
    .sidebar-nav .nav-content a.active {
        background: rgba(65, 84, 241, 0.1);
        color: #4154f1;
        font-weight: 700;
    }
    .sidebar-nav .nav-content a.active i {
        background-color: #4154f1;
        box-shadow: 0 0 6px rgba(65, 84, 241, 0.5);
    }
</style>
