<!-- ======= Top Navigation Bar ======= -->
<nav id="topbar" class="topbar-nav fixed-top">
    <div class="container-fluid topbar-container">
        <div class="topbar-wrapper">
            <ul class="topbar-menu" id="topbar-menu">

                @php
                    $u = Auth::user();
                    $isAdmin = $u && ($u->isAdmin() || $u->isSuperAdmin());
                    $isRektorat = $u && $u->isPimpinanRektorat() && !$isAdmin;
                    $isPimpinanUnit = $u && $u->isPimpinanUnit() && !$u->isPimpinanRektorat() && !$isAdmin;
                    $isStaffOrDosen = $u && !$isAdmin && !$u->isPimpinanRektorat() && !$isPimpinanUnit && !$u->isOnlyKepanitiaan();
                    $isOnlyPanitia = $u && $u->isOnlyKepanitiaan();

                    \App\Models\KepanitiaanSesiAbsensi::deleteWithoutKepanitiaan();

                    $activeSessions = collect();
                    if ($isAdmin || ($u && $u->canAccessAbsensi())) {
                        $activeSessions = \App\Models\KepanitiaanSesiAbsensi::where('is_active', true)
                            ->whereHas('kepanitiaan', fn ($query) => $query->where('kategori', 'Kepanitiaan'))
                            ->with('kepanitiaan')
                            ->orderBy('id', 'asc')
                            ->get();
                    }
                @endphp

                <!-- 1. DASHBOARD UTAMA -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        
                        <span>
                            @if($isAdmin) Dashboard Admin
                            @elseif($isRektorat) Dashboard Eksekutif
                            @elseif($isPimpinanUnit) Dashboard Unit
                            @else Dashboard Saya
                            @endif
                        </span>
                    </a>
                </li>

                <!-- ABSENSI KINERJA (Dipindahkan Sebelum Kegiatan) -->
                @if ($u && !$isOnlyPanitia)
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('absensi-kinerja.*') ? 'active' : '' }}" href="{{ route('absensi-kinerja.index') }}">
                            <span>Absensi Kinerja</span>
                        </a>
                    </li>
                @endif

                <!-- 2. TUGAS & MANAJEMEN KINERJA (Kegiatan) -->
                @if (!$isOnlyPanitia)
                    @php
                        $isFromLayanan = request()->is('layanan*') || 
                                         request()->routeIs('layanan.*') || 
                                         request('from') === 'layanan' || 
                                         (request()->routeIs('rencana-kerja.*') && request()->filled('jabatan'));

                        $isKinerjaActive = !$isFromLayanan && (
                            request()->routeIs('rencana-kerja.*') || 
                            request()->routeIs('kepanitiaan.*') || 
                            request()->routeIs('insidentil.*') || 
                            request()->routeIs('kegiatan.*') ||
                            request()->routeIs('pemantauan-staff.*') ||
                            request()->routeIs('analisis-keterlambatan.*')
                        );
                    @endphp
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ $isKinerjaActive ? 'active' : '' }}" href="#" id="kegiatanDropdown" role="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                            
                            <span>
                                @if($isStaffOrDosen) Kegiatan
                                @elseif($isPimpinanUnit) Kegiatan Unit
                                @else Kegiatan
                                @endif
                            </span>
                             <i class="bi bi-chevron-down dropdown-chevron"></i></a>
                        <ul class="dropdown-menu" aria-labelledby="kegiatanDropdown">
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('kegiatan.create') ? 'active' : '' }}" href="{{ route('kegiatan.create') }}">
                                    <span>Tambah Kegiatan</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('pemantauan-staff.*') ? 'active' : '' }}" href="{{ route('pemantauan-staff.index') }}">
                                    <span>Pemantauan Staff</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('analisis-keterlambatan.*') ? 'active' : '' }}" href="{{ route('analisis-keterlambatan.index') }}">
                                    <span>Analisis Keterlambatan</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                <!-- MODUL LAYANAN -->
                @if ($u && !$isOnlyPanitia)
                    @php
                        $isLayananActive = $isFromLayanan;
                    @endphp
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ $isLayananActive ? 'active' : '' }}" href="#" id="modulLayananDropdown" role="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                            <span>Modul Layanan</span>
                            <i class="bi bi-chevron-down dropdown-chevron"></i>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="modulLayananDropdown">
                            <!-- Submenu Layanan LPTI -->
                            <li class="dropdown-submenu">
                                <a class="dropdown-item dropdown-toggle d-flex align-items-center justify-content-between {{ (request()->is('layanan/lpti*') || $isFromLayanan) ? 'active' : '' }}" href="#" id="layananLptiSubmenu" role="button">
                                    <span class="d-flex align-items-center">
                                        <i class="bi bi-hdd-network me-2 text-dark"></i>
                                        <span>Layanan LPTI</span>
                                    </span>
                                    <i class="bi bi-chevron-right ms-2 dropdown-chevron-sub"></i>
                                </a>
                                @php
                                    $uJabatan = strtoupper($u->jabatan ?? '');
                                    $isAdminOrKepala = $isAdmin || $isRektorat || $isPimpinanUnit || str_contains($uJabatan, 'KEPALA') || str_contains($uJabatan, 'KA.');
                                    $isUserTroubleshooting = str_contains($uJabatan, 'TROUBLESHOOTING') || str_contains($uJabatan, 'HELPDESK') || str_contains($uJabatan, 'DUKUNGAN TEKNIS');
                                    $isUserSi = str_contains($uJabatan, 'SISTEM INFORMASI') || str_contains($uJabatan, 'PENGEMBANGAN SISTEM');
                                    $isUserJaringan = str_contains($uJabatan, 'INFRASTRUKTUR') || str_contains($uJabatan, 'JARINGAN');
                                    $isUserProgrammer = str_contains($uJabatan, 'PROGRAMMER') || str_contains($uJabatan, 'PEMELIHARAAN');
                                    $isUserKepalaLpti = $isAdmin || $isRektorat || str_contains($uJabatan, 'KEPALA LPTI') || str_contains($uJabatan, 'KA. LPTI') || str_contains($uJabatan, 'KA. UPT LPTI');

                                    // Hak Akses: Admin & Kepala melihat semua menu. Staff hanya melihat menu divisinya sendiri.
                                    $showManajemen = $isAdminOrKepala;
                                    $showSi = $isAdminOrKepala || $isUserSi;
                                    $showJaringan = $isAdminOrKepala || $isUserJaringan;
                                    $showProgrammer = $isAdminOrKepala || $isUserProgrammer;
                                    $showTroubleshooting = $isAdminOrKepala || $isUserTroubleshooting || (!$isUserSi && !$isUserJaringan && !$isUserProgrammer);
                                @endphp
                                <ul class="dropdown-menu dropdown-submenu-menu" aria-labelledby="layananLptiSubmenu">
                                    @if($showManajemen)
                                        <li>
                                            <a class="dropdown-item {{ (request()->routeIs('rencana-kerja.*') && (str_contains(strtoupper(request('jabatan', '')), 'KEPALA LPTI') || request('jabatan') == 'KEPALA LPTI')) || request()->routeIs('layanan.lpti.manajemen') ? 'active' : '' }}" href="{{ route('rencana-kerja.index', ['jabatan' => 'KEPALA LPTI', 'from' => 'layanan']) }}">
                                                <span class="d-flex align-items-center">
                                                    <i class="bi bi-briefcase me-2 text-dark"></i>
                                                    <span>Manajemen LPTI</span>
                                                </span>
                                                @if($isUserKepalaLpti && !$isAdmin)
                                                    <span class="badge badge-role-active ms-3 px-2 py-0.5 rounded-pill" style="background-color: #0d6efd !important; color: #ffffff !important; border: 1px solid #0d6efd !important; font-size: 10px; font-weight: 600;">
                                                        <i class="bi bi-check-circle-fill me-1"></i>Akses Anda (Kepala LPTI)
                                                    </span>
                                                @else
                                                    <span class="badge badge-role-kepala ms-3 px-2 py-0.5 rounded-pill" style="background-color: #e0edff !important; color: #0d6efd !important; border: 1px solid #b6d4fe !important; font-size: 10px; font-weight: 600;">
                                                        Kepala LPTI
                                                    </span>
                                                @endif
                                            </a>
                                        </li>
                                    @endif
                                    @if($showSi)
                                        <li>
                                            <a class="dropdown-item {{ (request()->routeIs('rencana-kerja.*') && str_contains(strtoupper(request('jabatan', '')), 'SISTEM INFORMASI')) || request()->routeIs('layanan.lpti.pengembangan-si') ? 'active' : '' }}" href="{{ route('rencana-kerja.index', ['jabatan' => 'DIVISI PENGEMBANGAN SISTEM INFORMASI DAN APLIKASI - LPTI BAUK', 'from' => 'layanan']) }}">
                                                <span class="d-flex align-items-center">
                                                    <i class="bi bi-window me-2 text-dark"></i>
                                                    <span>Pengembangan Sistem Informasi dan Aplikasi</span>
                                                </span>
                                                @if($isUserSi)
                                                    <span class="badge badge-role-active ms-3 px-2 py-0.5 rounded-pill" style="background-color: #087990 !important; color: #ffffff !important; border: 1px solid #087990 !important; font-size: 10px; font-weight: 600;">
                                                        <i class="bi bi-check-circle-fill me-1"></i>Akses Anda (Divisi SI)
                                                    </span>
                                                @else
                                                    <span class="badge badge-role-si ms-3 px-2 py-0.5 rounded-pill" style="background-color: #cff4fc !important; color: #055160 !important; border: 1px solid #9eeaf9 !important; font-size: 10px; font-weight: 600;">
                                                        Divisi SI
                                                    </span>
                                                @endif
                                            </a>
                                        </li>
                                    @endif
                                    @if($showJaringan)
                                        <li>
                                            <a class="dropdown-item {{ (request()->routeIs('rencana-kerja.*') && (str_contains(strtoupper(request('jabatan', '')), 'INFRASTRUKTUR') || str_contains(strtoupper(request('jabatan', '')), 'JARINGAN'))) || request()->routeIs('layanan.lpti.infrastruktur') ? 'active' : '' }}" href="{{ route('rencana-kerja.index', ['jabatan' => 'DIVISI INFRASTRUKTUR DAN JARINGAN', 'from' => 'layanan']) }}">
                                                <span class="d-flex align-items-center">
                                                    <i class="bi bi-diagram-3 me-2 text-dark"></i>
                                                    <span>Pengelolaan Infrastruktur dan Jaringan</span>
                                                </span>
                                                @if($isUserJaringan)
                                                    <span class="badge badge-role-active ms-3 px-2 py-0.5 rounded-pill" style="background-color: #b78103 !important; color: #ffffff !important; border: 1px solid #b78103 !important; font-size: 10px; font-weight: 600;">
                                                        <i class="bi bi-check-circle-fill me-1"></i>Akses Anda (Divisi Jaringan)
                                                    </span>
                                                @else
                                                    <span class="badge badge-role-jaringan ms-3 px-2 py-0.5 rounded-pill" style="background-color: #fff3cd !important; color: #7a4f01 !important; border: 1px solid #ffc107 !important; font-size: 10px; font-weight: 600;">
                                                        Divisi Jaringan
                                                    </span>
                                                @endif
                                            </a>
                                        </li>
                                    @endif
                                    @if($showProgrammer)
                                        <li>
                                            <a class="dropdown-item {{ (request()->routeIs('rencana-kerja.*') && str_contains(strtoupper(request('jabatan', '')), 'PROGRAMMER')) || request()->routeIs('layanan.lpti.pemeliharaan') ? 'active' : '' }}" href="{{ route('rencana-kerja.index', ['jabatan' => 'PROGRAMMER', 'from' => 'layanan']) }}">
                                                <span class="d-flex align-items-center">
                                                    <i class="bi bi-code-slash me-2 text-dark"></i>
                                                    <span>Pengembangan dan Pemeliharaan Aplikasi</span>
                                                </span>
                                                @if($isUserProgrammer)
                                                    <span class="badge badge-role-active ms-3 px-2 py-0.5 rounded-pill" style="background-color: #dc3545 !important; color: #ffffff !important; border: 1px solid #dc3545 !important; font-size: 10px; font-weight: 600;">
                                                        <i class="bi bi-check-circle-fill me-1"></i>Akses Anda (Programmer)
                                                    </span>
                                                @else
                                                    <span class="badge badge-role-programmer ms-3 px-2 py-0.5 rounded-pill" style="background-color: #f8d7da !important; color: #842029 !important; border: 1px solid #f5c2c7 !important; font-size: 10px; font-weight: 600;">
                                                        Programmer
                                                    </span>
                                                @endif
                                            </a>
                                        </li>
                                    @endif
                                    @if($showTroubleshooting)
                                        <li>
                                            <a class="dropdown-item {{ (request()->routeIs('rencana-kerja.*') && str_contains(strtoupper(request('jabatan', '')), 'TROUBLESHOOTING')) || request()->routeIs('layanan.lpti.dukungan-teknis') ? 'active' : '' }}" href="{{ route('rencana-kerja.index', ['jabatan' => 'STAFF LAYANAN TROUBLESHOOTING', 'from' => 'layanan']) }}">
                                                <span class="d-flex align-items-center">
                                                    <i class="bi bi-headset me-2 text-dark"></i>
                                                    <span>Layanan Dukungan Teknis</span>
                                                </span>
                                                @if($isUserTroubleshooting)
                                                    <span class="badge badge-role-active ms-3 px-2 py-0.5 rounded-pill" style="background-color: #198754 !important; color: #ffffff !important; border: 1px solid #198754 !important; font-size: 10px; font-weight: 600;">
                                                        <i class="bi bi-check-circle-fill me-1"></i>Akses Anda (Troubleshooting)
                                                    </span>
                                                @else
                                                    <span class="badge badge-role-troubleshooting ms-3 px-2 py-0.5 rounded-pill" style="background-color: #d1e7dd !important; color: #0f5132 !important; border: 1px solid #badbcc !important; font-size: 10px; font-weight: 600;">
                                                        Troubleshooting
                                                    </span>
                                                @endif
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        </ul>
                    </li>
                @endif

                <!-- 3. REKAPITULASI CAPAIAN / KINERJA -->
                <li class="nav-item">
                    <a class="nav-link {{ (request()->routeIs('rekapitulasi.*') || request()->routeIs('rekapitulasi-kepanitiaan.*') || request()->routeIs('rekapitulasi-insidentil.*')) ? 'active' : '' }}" href="{{ route('rekapitulasi.index') }}">
                        <span>
                            @if($isPimpinanUnit) Rekapitulasi Kinerja Unit
                            @elseif($isAdmin || $isRektorat) Rekapitulasi Kinerja
                            @else Rekapitulasi Capaian Saya
                            @endif
                        </span>
                    </a>
                </li>

                <!-- 4. LPJ (Jika punya akses LPJ) -->
                @if ($u && $u->canAccessLpj())
                    @php
                        $isLpjActive = request()->routeIs('lpj.*');
                    @endphp
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ $isLpjActive ? 'active' : '' }}" href="#" id="lpjDropdown" role="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                            
                            <span>Laporan LPJ</span>
                             <i class="bi bi-chevron-down dropdown-chevron"></i></a>
                        <ul class="dropdown-menu" aria-labelledby="lpjDropdown">
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('lpj.index') ? 'active' : '' }}" href="{{ route('lpj.index') }}">
                                    <span>Daftar LPJ</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('lpj.create') ? 'active' : '' }}" href="{{ route('lpj.create') }}">
                                    <span>Tambah LPJ</span>
                                </a>
                            </li>
                            @if ($isAdmin || ($u && $u->isTimEvaluasi()))
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('lpj.template*') ? 'active' : '' }}" href="{{ route('lpj.template') }}">
                                        <span>Template LPJ</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                <!-- 5. LAPORAN EKSEKUTIF / UNIT KERJA -->
                @if ($isAdmin || $isRektorat || $isPimpinanUnit)
                    @php
                        $isLaporanActive = request()->routeIs('laporan-kinerja-pegawai.*') || 
                                           request()->routeIs('laporan-pengembangan.*');
                    @endphp
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ $isLaporanActive ? 'active' : '' }}" href="#" id="laporanDropdown" role="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                            
                            <span>
                                @if($isAdmin || $isRektorat) Laporan Eksekutif
                                @else Laporan Unit Kerja
                                @endif
                            </span>
                             <i class="bi bi-chevron-down dropdown-chevron"></i></a>
                        <ul class="dropdown-menu" aria-labelledby="laporanDropdown">
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('laporan-kinerja-pegawai.*') ? 'active' : '' }}" href="{{ route('laporan-kinerja-pegawai.index') }}">
                                    <span>Laporan Kinerja Pegawai</span>
                                </a>
                            </li>
                            @if($isAdmin || $isRektorat)
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('laporan-pengembangan.*') ? 'active' : '' }}" href="{{ route('laporan-pengembangan.index') }}">
                                        <span>Pengembangan Universitas</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                <!-- 6. PRESENSI KEPANITIAAN -->
                @if ($isAdmin)
                    <li class="nav-item dropdown" id="sidebar-presensi-active-item" style="{{ $activeSessions->isEmpty() ? 'display: none;' : '' }}">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('presensi.*') || request()->routeIs('absensi-*') ? 'active' : '' }}" href="#" id="presensiAktifDropdown" role="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                            
                            <span>Sesi Presensi Aktif</span>
                            <span class="badge bg-success rounded-pill ms-2 px-2 py-0" id="sidebar-active-sessions-badge" style="font-size: 10px;">{{ $activeSessions->count() }}</span>
                             <i class="bi bi-chevron-down dropdown-chevron"></i></a>
                        <ul class="dropdown-menu" aria-labelledby="presensiAktifDropdown" id="presensi-kepanitiaan-nav">
                            @foreach($activeSessions as $activeSess)
                                @php
                                    $targetUrl = route('presensi.sesi.show', $activeSess->id);
                                @endphp
                                <li id="sidebar-sesi-item-{{ $activeSess->id }}">
                                    <a class="dropdown-item {{ request()->routeIs('presensi.sesi.show') && request()->route('id') == $activeSess->id ? 'active' : '' }}" href="{{ $targetUrl }}" title="{{ $activeSess->nama_sesi }}">
                                        <span class="text-truncate">{{ $activeSess->nama_sesi }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>

                    @php
                        $isPresensiAdminActive = request()->routeIs('pengaturan-absensi.*') || 
                                                 request()->routeIs('rekapitulasi-absensi.*');
                    @endphp
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ $isPresensiAdminActive ? 'active' : '' }}" href="#" id="kelolaPresensiDropdown" role="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                            
                            <span>Kelola Presensi</span>
                             <i class="bi bi-chevron-down dropdown-chevron"></i></a>
                        <ul class="dropdown-menu" aria-labelledby="kelolaPresensiDropdown">
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('pengaturan-absensi.*') ? 'active' : '' }}" href="{{ route('pengaturan-absensi.index') }}">
                                    <span>Status Sesi Absensi</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('rekapitulasi-absensi.*') ? 'active' : '' }}" href="{{ route('rekapitulasi-absensi.index') }}">
                                    <span>Rekapitulasi Absensi</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                @elseif ($u)
                    @if($activeSessions->isNotEmpty() && ($u->isKoordinatorPanitia() || $u->isKepanitiaan()))
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->routeIs('presensi.*') ? 'active' : '' }}" href="#" id="presensiUserDropdown" role="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                                
                                <span>Sesi Aktif (<span id="sidebar-user-active-sessions-badge">{{ $activeSessions->count() }}</span>)</span>
                                 <i class="bi bi-chevron-down dropdown-chevron"></i></a>
                            <ul class="dropdown-menu" aria-labelledby="presensiUserDropdown">
                                @foreach($activeSessions as $activeSess)
                                    <li id="sidebar-sesi-item-{{ $activeSess->id }}">
                                        <a class="dropdown-item {{ request()->routeIs('presensi.sesi.show') && request()->route('id') == $activeSess->id ? 'active' : '' }}" href="{{ route('presensi.sesi.show', $activeSess->id) }}">
                                            <span class="text-truncate">{{ $activeSess->nama_sesi }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @endif

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('rekapitulasi-absensi.*') ? 'active' : '' }}" href="{{ route('rekapitulasi-absensi.index') }}">
                            
                            <span>Riwayat Presensi Saya</span>
                        </a>
                    </li>
                @endif

                <!-- 7. PENGATURAN MASTER SISTEM -->
                @if ($isAdmin)
                    @php
                        $isSettingsActive = request()->routeIs('user.*') || 
                                            request()->routeIs('periode-akademik.*') || 
                                            request()->routeIs('triwulan.*') || 
                                            request()->routeIs('kategori.*') ||
                                            request()->routeIs('hirarki.*') ||
                                            request()->routeIs('sk-kepanitiaan.*') ||
                                            request()->routeIs('pengaturan-pesan-whatsapp.*') ||
                                            request()->routeIs('database-backup.*');
                    @endphp
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ $isSettingsActive ? 'active' : '' }}" href="#" id="pengaturanDropdown" role="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                            
                            <span>Pengaturan Sistem</span>
                             <i class="bi bi-chevron-down dropdown-chevron"></i></a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="pengaturanDropdown">
                            <li><a class="dropdown-item {{ request()->routeIs('sk-kepanitiaan.*') ? 'active' : '' }}" href="{{ route('sk-kepanitiaan.index') }}"><span>SK Kepanitiaan</span></a></li>
                            <li><a class="dropdown-item {{ request()->routeIs('user.*') ? 'active' : '' }}" href="{{ route('user.index') }}"><span>Kelola Pengguna</span></a></li>
                            <li><a class="dropdown-item {{ request()->routeIs('periode-akademik.*') ? 'active' : '' }}" href="{{ route('periode-akademik.index') }}"><span>Periode Akademik</span></a></li>
                            <li><a class="dropdown-item {{ request()->routeIs('triwulan.*') ? 'active' : '' }}" href="{{ route('triwulan.index') }}"><span>Triwulan (TW)</span></a></li>
                            <li><a class="dropdown-item {{ request()->routeIs('kategori.*') ? 'active' : '' }}" href="{{ route('kategori.index') }}"><span>Kategori Kegiatan</span></a></li>
                            <li><a class="dropdown-item {{ request()->routeIs('pengaturan-pesan-whatsapp.*') ? 'active' : '' }}" href="{{ route('pengaturan-pesan-whatsapp.create') }}"><span>Pesan WhatsApp</span></a></li>
                            <li><a class="dropdown-item {{ request()->routeIs('hirarki.*') ? 'active' : '' }}" href="{{ route('hirarki.index') }}"><span>Struktur OTK</span></a></li>
                            <li><a class="dropdown-item {{ request()->routeIs('database-backup.*') ? 'active' : '' }}" href="{{ route('database-backup.index') }}"><span>Backup Database &amp; Storage</span></a></li>
                        </ul>
                    </li>
                @endif

            </ul>
        </div>
    </div>
</nav><!-- End Top Navigation Bar -->
