  <!-- ======= Sidebar ======= -->
  <aside id="sidebar" class="sidebar">

      <ul class="sidebar-nav" id="sidebar-nav">

          <!-- 1. Dashboard -->
          <li class="nav-item">
              <a class="{{ request()->routeIs('dashboard') ? 'nav-link' : 'nav-link collapsed' }}"
                  href="{{ route('dashboard') }}">
                  <i class="bi bi-grid-fill text-primary"></i>
                  <span>Dashboard</span>
              </a>
          </li><!-- End Dashboard Nav -->

          <!-- 2. Presensi Kepanitiaan (Admin & Pimpinan) -->
          @if (Auth::check() && (Auth::user()->isAdmin() || Auth::user()->isSuperAdmin()))
              @php
                  // Ambil hanya sesi yang AKTIF (sesi yang dikunci tidak perlu ditampilkan sama sekali)
                  $activeSessions = \App\Models\KepanitiaanSesiAbsensi::where('is_active', true)
                      ->with('kepanitiaan')
                      ->orderBy('id', 'asc')
                      ->get();
              @endphp
              <li class="nav-heading" id="sidebar-presensi-heading">Presensi Kepanitiaan</li>

              <!-- Sesi Presensi Aktif (Dropdown Menu) -->
              <li class="nav-item" id="sidebar-presensi-active-item" style="{{ $activeSessions->isEmpty() ? 'display: none;' : '' }}">
                  <a class="{{ request()->routeIs('presensi.*') || request()->routeIs('absensi-*') ? 'nav-link' : 'nav-link collapsed' }}"
                      data-bs-target="#presensi-kepanitiaan-nav" data-bs-toggle="collapse" href="#">
                      <i class="bi bi-qr-code-scan"></i><span>Sesi Presensi Aktif</span>
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

              <!-- Status Sesi Absensi (Kontrol Aktif/Tutup Sesi) -->
              <li class="nav-item">
                  <a class="{{ request()->routeIs('pengaturan-absensi.*') ? 'nav-link' : 'nav-link collapsed' }}"
                      href="{{ route('pengaturan-absensi.index') }}">
                      <i class="bi bi-sliders2"></i>
                      <span>Status Sesi Absensi</span>
                  </a>
              </li>

              <!-- Rekapitulasi Absensi -->
              <li class="nav-item">
                  <a class="{{ request()->routeIs('rekapitulasi-absensi.*') ? 'nav-link' : 'nav-link collapsed' }}"
                      href="{{ route('rekapitulasi-absensi.index') }}">
                      <i class="bi bi-clipboard-data-fill"></i>
                      <span>Rekapitulasi Absensi</span>
                  </a>
              </li>
          @elseif (Auth::check())
              <!-- 2. Presensi Kepanitiaan (Pegawai / Panitia) -->
              <li class="nav-heading" id="sidebar-presensi-heading">Presensi Kepanitiaan</li>
              <li class="nav-item">
                  <a class="{{ request()->routeIs('rekapitulasi-absensi.*') ? 'nav-link' : 'nav-link collapsed' }}"
                      href="{{ route('rekapitulasi-absensi.index') }}">
                      <i class="bi bi-person-check-fill text-success"></i>
                      <span>Riwayat Presensi Saya</span>
                  </a>
              </li>
          @endif

          <!-- 3. Tugas & Kinerja -->
          @if (!Auth::check() || Auth::user()->canAccessKinerja() || Auth::user()->isTimEvaluasi())
              <li class="nav-heading">Tugas & Kinerja</li>

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
                      <span>Rencana Kerja Utama</span>
                  </a>
              </li>

              <li class="nav-item">
                  <a class="{{ request()->routeIs('kepanitiaan.*') ? 'nav-link' : 'nav-link collapsed' }}"
                      href="{{ route('kepanitiaan.index') }}">
                      <i class="bi bi-people-fill"></i>
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

              <!-- 4. Rekapitulasi & Laporan -->
              <li class="nav-heading">Rekapitulasi & Laporan</li>

              <li class="nav-item">
                  <a class="{{ request()->routeIs('rekapitulasi.*') || request()->routeIs('rekapitulasi-kepanitiaan.*') || request()->routeIs('rekapitulasi-insidentil.*') ? 'nav-link' : 'nav-link collapsed' }}"
                      href="{{ route('rekapitulasi.index') }}">
                      <i class="bi bi-pie-chart-fill"></i>
                      <span>Rekapitulasi Kinerja</span>
                  </a>
              </li>

              <li class="nav-item">
                  <a class="{{ request()->routeIs('analisis-keterlambatan.*') ? 'nav-link' : 'nav-link collapsed' }}"
                      href="{{ route('analisis-keterlambatan.index') }}">
                      <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                      <span>Analisis Keterlambatan</span>
                  </a>
              </li>

              <!-- Menu Gabungan LPJ (Hanya Tim Evaluasi, Koordinator Panitia, & Admin) -->
              @if (Auth::user() && Auth::user()->canAccessLpj())
              <li class="nav-item">
                  <a class="{{ request()->routeIs('lpj.*') ? 'nav-link' : 'nav-link collapsed' }}"
                      data-bs-target="#lpj-nav" data-bs-toggle="collapse" href="#">
                      <i class="bi bi-file-earmark-text-fill"></i>
                      <span>Laporan Pertanggungjawaban (LPJ)</span>
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
                      @if (Auth::user()->isSuperAdmin() || Auth::user()->isAdmin() || Auth::user()->isTimEvaluasi())
                          <li>
                              <a href="{{ route('lpj.template') }}" class="{{ request()->routeIs('lpj.template*') ? 'active' : '' }}">
                                  <i class="bi bi-circle"></i><span>Template LPJ</span>
                              </a>
                          </li>
                      @endif
                  </ul>
              </li>
              @endif
          @endif

          <!-- 5. Pengaturan Sistem (Admin & Super Admin) -->
          @if (Auth::user() && (Auth::user()->isAdmin() || Auth::user()->isSuperAdmin()))
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
      /* Styling untuk merapikan submenu dan sejajar dengan bullet */
      .sidebar-nav .nav-content a {
          padding: 8px 12px 8px 24px !important;
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
          font-size: 13.5px;
          line-height: 1.3;
          white-space: normal;
          word-break: break-word;
      }
  </style>
