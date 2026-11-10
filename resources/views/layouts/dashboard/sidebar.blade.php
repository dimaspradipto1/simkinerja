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
                        $isKinerjaActive = request()->routeIs('rencana-kerja.*') || 
                                           request()->routeIs('kepanitiaan.*') || 
                                           request()->routeIs('insidentil.*') || 
                                           request()->routeIs('kegiatan.*');
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
                                <a class="dropdown-item {{ request()->routeIs('rencana-kerja.*') ? 'active' : '' }}" href="{{ route('rencana-kerja.index') }}">
                                    <span>
                                        @if($isStaffOrDosen) Kegiatan Saya
                                        @elseif($isPimpinanUnit) Kegiatan Unit
                                        @else Kegiatan Utama
                                        @endif
                                    </span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('kepanitiaan.*') ? 'active' : '' }}" href="{{ route('kepanitiaan.index') }}">
                                    <span>Kegiatan Kepanitiaan</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('insidentil.*') ? 'active' : '' }}" href="{{ route('insidentil.index') }}">
                                    <span>Kegiatan Insidentil</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                <!-- 3. MONITORING & LAPORAN -->
                @php
                    $hasOtkSubordinates = $u && $u->subordinates()->exists();
                @endphp
                @if($isAdmin || $isRektorat || $isPimpinanUnit || $hasOtkSubordinates)
                    @php
                        $isMonitoringActive = request()->routeIs('rekapitulasi.*') || 
                                              request()->routeIs('rekapitulasi-kepanitiaan.*') || 
                                              request()->routeIs('rekapitulasi-insidentil.*') || 
                                              request()->routeIs('pemantauan-staff.*') ||
                                              request()->routeIs('analisis-keterlambatan.*');
                    @endphp
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ $isMonitoringActive ? 'active' : '' }}" href="#" id="monitoringDropdown" role="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                            
                            <span>
                                @if($isAdmin || $isRektorat) Monitoring &amp; Evaluasi
                                @else Monitoring Unit
                                @endif
                            </span>
                             <i class="bi bi-chevron-down dropdown-chevron"></i></a>
                        <ul class="dropdown-menu" aria-labelledby="monitoringDropdown">
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
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('rekapitulasi.*') || request()->routeIs('rekapitulasi-kepanitiaan.*') || request()->routeIs('rekapitulasi-insidentil.*') ? 'active' : '' }}" href="{{ route('rekapitulasi.index') }}">
                                    <span>
                                        @if($isPimpinanUnit) Rekapitulasi Kinerja Unit
                                        @else Rekapitulasi Kinerja
                                        @endif
                                    </span>
                                </a>
                            </li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('rekapitulasi.*') || request()->routeIs('rekapitulasi-kepanitiaan.*') || request()->routeIs('rekapitulasi-insidentil.*') ? 'active' : '' }}" href="{{ route('rekapitulasi.index') }}">
                            
                            <span>Rekapitulasi Capaian Saya</span>
                        </a>
                    </li>
                @endif

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
