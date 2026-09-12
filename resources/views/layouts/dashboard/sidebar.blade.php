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
                  <i class="bi bi-grid-fill text-success"></i>
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
          <!-- 2. TUGAS & MANAJEMEN KINERJA (Staff, Dosen, Pimpinan, Admin) -->
          <!-- ============================================================ -->
          @if (!$isOnlyPanitia)
              <li class="nav-heading">
                  @if($isAdmin) Manajemen Kinerja
                  @elseif($isRektorat) Kinerja Rektorat
                  @elseif($isPimpinanUnit) Kinerja Unit
                  @else Tugas & Kinerja Saya
                  @endif
              </li>

              <li class="nav-item">
                  <a class="{{ request()->routeIs('kegiatan.*') ? 'nav-link' : 'nav-link collapsed' }}"
                      href="{{ route('kegiatan.create') }}">
                      <i class="bi bi-plus-circle-fill text-success"></i>
                      <span>Tambah Kegiatan</span>
                  </a>
              </li>

              <li class="nav-item">
                  <a class="{{ request()->routeIs('rencana-kerja.*') ? 'nav-link' : 'nav-link collapsed' }}"
                      href="{{ route('rencana-kerja.index') }}">
                      <i class="bi bi-journal-check"></i>
                      <span>
                          @if($isStaffOrDosen) Rencana Kerja Saya
                          @elseif($isPimpinanUnit) Rencana Kerja Unit
                          @else Rencana Kerja Utama
                          @endif
                      </span>
                  </a>
              </li>

              <li class="nav-item">
                  <a class="{{ request()->routeIs('kepanitiaan.*') ? 'nav-link' : 'nav-link collapsed' }}"
                      href="{{ route('kepanitiaan.index') }}">
                      <i class="bi bi-diagram-3-fill text-info"></i>
                      <span>Rencana Kerja Kepanitiaan</span>
                  </a>
              </li>

              <li class="nav-item">
                  <a class="{{ request()->routeIs('insidentil.*') ? 'nav-link' : 'nav-link collapsed' }}"
                      href="{{ route('insidentil.index') }}">
                      <i class="bi bi-lightning-charge-fill text-warning"></i>
                      <span>Rencana Kerja Insidentil</span>
                  </a>
              </li>
          @endif

          <!-- ============================================================ -->
          <!-- 4. MONITORING & REKAPITULASI (Semua Role sesuai porsi) -->
          <!-- ============================================================ -->
          <li class="nav-heading">
              @if($isAdmin || $isRektorat) Monitoring & Evaluasi
              @elseif($isPimpinanUnit) Monitoring Unit
              @else Capaian Saya
              @endif
          </li>

          <li class="nav-item">
              <a class="{{ request()->routeIs('rekapitulasi.*') || request()->routeIs('rekapitulasi-kepanitiaan.*') || request()->routeIs('rekapitulasi-insidentil.*') ? 'nav-link' : 'nav-link collapsed' }}"
                  href="{{ route('rekapitulasi.index') }}">
                  <i class="bi bi-pie-chart-fill text-primary"></i>
                  <span>
                      @if($isStaffOrDosen) Rekapitulasi Capaian Saya
                      @elseif($isPimpinanUnit) Rekapitulasi Kinerja Unit
                      @else Rekapitulasi Kinerja
                      @endif
                  </span>
              </a>
          </li>

          @if($isAdmin || $isRektorat || $isPimpinanUnit)
              <li class="nav-item">
                  <a class="{{ request()->routeIs('analisis-keterlambatan.*') ? 'nav-link' : 'nav-link collapsed' }}"
                      href="{{ route('analisis-keterlambatan.index') }}">
                      <i class="bi bi-exclamation-triangle-fill text-danger"></i>
                      <span>Analisis Keterlambatan</span>
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

          <!-- ============================================================ -->
          <!-- 5. PRESENSI KEPANITIAAN (Sesuai Role) -->
          <!-- ============================================================ -->
          @if ($isAdmin)
              <li class="nav-heading" id="sidebar-presensi-heading">Presensi Kepanitiaan</li>

              <!-- Sesi Presensi Aktif (Dropdown Menu) -->
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
                  <a class="{{ request()->routeIs('pengaturan-absensi.*') ? 'nav-link' : 'nav-link collapsed' }}"
                      href="{{ route('pengaturan-absensi.index') }}">
                      <i class="bi bi-sliders2"></i>
                      <span>Status Sesi Absensi</span>
                  </a>
              </li>

              <li class="nav-item">
                  <a class="{{ request()->routeIs('rekapitulasi-absensi.*') ? 'nav-link' : 'nav-link collapsed' }}"
                      href="{{ route('rekapitulasi-absensi.index') }}">
                      <i class="bi bi-clipboard-data-fill text-primary"></i>
                      <span>Rekapitulasi Absensi</span>
                  </a>
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
                  <a class="{{ request()->routeIs('rekapitulasi-absensi.*') ? 'nav-link' : 'nav-link collapsed' }}"
                      href="{{ route('rekapitulasi-absensi.index') }}">
                      <i class="bi bi-person-check-fill text-success"></i>
                      <span>Riwayat Presensi Saya</span>
                  </a>
              </li>
          @endif

          <!-- ============================================================ -->
          <!-- LAPORAN EKSEKUTIF & YAYASAN (Admin, Rektorat, & Pimpinan Unit) -->
          <!-- ============================================================ -->
          @if ($isAdmin || $isRektorat || $isPimpinanUnit)
              <li class="nav-heading">
                  @if($isAdmin || $isRektorat) Laporan Eksekutif & Yayasan
                  @else Laporan Unit Kerja
                  @endif
              </li>

              <li class="nav-item">
                  <a class="{{ request()->routeIs('laporan-kinerja-pegawai.*') ? 'nav-link' : 'nav-link collapsed' }}"
                      href="{{ route('laporan-kinerja-pegawai.index') }}">
                      <i class="bi bi-people-fill text-primary"></i>
                      <span>Laporan Kinerja Pegawai</span>
                  </a>
              </li>

              @if($isAdmin || $isRektorat)
                  <li class="nav-item">
                      <a class="{{ request()->routeIs('laporan-pengembangan.*') ? 'nav-link' : 'nav-link collapsed' }}"
                          href="{{ route('laporan-pengembangan.index') }}">
                          <i class="bi bi-award-fill text-warning"></i>
                          <span>Pengembangan Universitas</span>
                      </a>
                  </li>
              @endif
          @endif

          <!-- ============================================================ -->
          <!-- PENGATURAN MASTER SISTEM (Hanya Admin / Super Admin) -->
          <!-- ============================================================ -->
          @if ($isAdmin)
              <li class="nav-heading">Pengaturan Sistem</li>

              <li class="nav-item">
                  <a class="{{ request()->routeIs('user.*') ? 'nav-link' : 'nav-link collapsed' }}"
                      href="{{ route('user.index') }}">
                      <i class="bi bi-person-gear"></i>
                      <span>Kelola Pengguna</span>
                  </a>
              </li>

              <li class="nav-item">
                  <a class="{{ request()->routeIs('periode-akademik.*') ? 'nav-link' : 'nav-link collapsed' }}"
                      href="{{ route('periode-akademik.index') }}">
                      <i class="bi bi-calendar-event"></i>
                      <span>Periode Akademik</span>
                  </a>
              </li>

              <li class="nav-item">
                  <a class="{{ request()->routeIs('kategori.*') ? 'nav-link' : 'nav-link collapsed' }}"
                      href="{{ route('kategori.index') }}">
                      <i class="bi bi-tags-fill"></i>
                      <span>Kategori Kegiatan</span>
                  </a>
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
          margin: 12px 0 5px 15px;
          letter-spacing: 0.5px;
      }
      .sidebar-nav .nav-link {
          font-size: 13.5px;
          font-weight: 600;
          padding: 9px 15px;
          border-radius: 8px;
          margin-bottom: 2px;
          transition: all 0.2s ease;
      }
      .sidebar-nav .nav-link i {
          font-size: 16px;
          margin-right: 10px;
      }
      .sidebar-nav .nav-content a {
          padding: 7px 12px 7px 25px !important;
          text-decoration: none;
          display: flex;
          align-items: center;
          gap: 8px;
          border-radius: 6px;
          transition: all 0.2s ease;
      }
      .sidebar-nav .nav-content a i {
          font-size: 6px !important;
          margin-right: 0 !important;
          flex-shrink: 0;
      }
      .sidebar-nav .nav-content a span {
          font-size: 13px;
          line-height: 1.3;
          white-space: normal;
          word-break: break-word;
      }
  </style>
