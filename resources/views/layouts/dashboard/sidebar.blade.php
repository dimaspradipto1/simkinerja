  <!-- ======= Sidebar ======= -->
  <aside id="sidebar" class="sidebar">

      <ul class="sidebar-nav" id="sidebar-nav">

          <li class="nav-item">
              <a class="{{ request()->routeIs('dashboard') ? 'nav-link' : 'nav-link collapsed' }}"
                  href="{{ route('dashboard') }}">
                  <i class="bi bi-grid"></i>
                  <span>Dashboard</span>
              </a>
          </li><!-- End Dashboard Nav -->

          @if (Auth::check() && (Auth::user()->isAdmin() || Auth::user()->isSuperAdmin()))
              @php
                  // Ambil hanya sesi yang AKTIF (sesi yang dikunci tidak perlu ditampilkan sama sekali)
                  $activeSessions = \App\Models\KepanitiaanSesiAbsensi::where('is_active', true)
                      ->with('kepanitiaan')
                      ->orderBy('id', 'asc')
                      ->get();
              @endphp
              <li class="nav-heading">Presensi Kepanitiaan</li>

              @if($activeSessions->isNotEmpty())
                  <li class="nav-item">
                      <a class="{{ request()->routeIs('presensi.*') || request()->routeIs('absensi-*') ? 'nav-link' : 'nav-link collapsed' }}"
                          data-bs-target="#presensi-kepanitiaan-nav" data-bs-toggle="collapse" href="#">
                          <i class="bi bi-qr-code-scan"></i><span>Sesi Presensi Aktif</span>
                          <span class="badge bg-success rounded-pill ms-2 px-2 py-0" style="font-size: 10px;">{{ $activeSessions->count() }}</span>
                          <i class="bi bi-chevron-down ms-auto"></i>
                      </a>
                      <ul id="presensi-kepanitiaan-nav"
                          class="nav-content collapse {{ request()->routeIs('presensi.*') || request()->routeIs('absensi-*') ? 'show' : '' }}"
                          data-bs-parent="#sidebar-nav">
                          @foreach($activeSessions as $activeSess)
                              @php
                                  $targetUrl = route('presensi.sesi.show', $activeSess->id);
                              @endphp
                              <li>
                                  <a href="{{ $targetUrl }}" class="d-flex align-items-center justify-content-between pe-3 {{ request()->routeIs('presensi.sesi.show') && request()->route('id') == $activeSess->id ? 'active' : '' }}">
                                      <div class="submenu-item-wrapper text-truncate" style="max-width: 190px;" title="{{ $activeSess->nama_sesi }}">
                                          <i class="bi bi-circle-fill text-success" style="font-size: 6px;"></i>
                                          <span class="submenu-item-text">{{ $activeSess->nama_sesi }}</span>
                                      </div>
                                      <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill py-0 px-1.5 flex-shrink-0" style="font-size: 9px;">
                                          Buka
                                      </span>
                                  </a>
                              </li>
                          @endforeach
                      </ul>
                  </li>
              @endif

              <li class="nav-item">
                  <a class="{{ request()->routeIs('rekapitulasi-absensi.*') ? 'nav-link' : 'nav-link collapsed' }}"
                      href="{{ route('rekapitulasi-absensi.index') }}">
                      <i class="bi bi-clipboard-data-fill"></i>
                      <span>Rekapitulasi Absensi</span>
                  </a>
              </li>
          @endif

          @if (!Auth::check() || Auth::user()->canAccessKinerja())
              <li class="nav-heading">Tugas & Kinerja</li>
                <li class="nav-item">
                  <a class="{{ request()->routeIs('kegiatan.*') ? 'nav-link' : 'nav-link collapsed' }}"
                      href="{{ route('kegiatan.create') }}">
                      <i class="bi bi-calendar2-event-fill"></i>
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
                      <i class="bi bi-lightning-charge"></i>
                      <span>Rencana Kerja Insidentil</span>
                  </a>
              </li>

              <li class="nav-heading">Rekapitulasi & Laporan</li>

              <li class="nav-item">
                  <a class="{{ request()->routeIs('rekapitulasi.*') || request()->routeIs('rekapitulasi-kepanitiaan.*') || request()->routeIs('rekapitulasi-insidentil.*') ? 'nav-link' : 'nav-link collapsed' }}"
                      href="{{ route('rekapitulasi.index') }}">
                      <i class="bi bi-pie-chart-fill"></i>
                      <span>Rekapitulasi</span>
                  </a>
              </li>

              <li class="nav-item">
                  <a class="{{ request()->routeIs('analisis-keterlambatan.*') ? 'nav-link' : 'nav-link collapsed' }}"
                      href="{{ route('analisis-keterlambatan.index') }}">
                      <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                      <span>Analisis Keterlambatan</span>
                  </a>
              </li>

              <li class="nav-item">
                  <a class="{{ request()->routeIs('lpj.*') ? 'nav-link' : 'nav-link collapsed' }}"
                      href="{{ route('lpj.index') }}">
                      <i class="bi bi-file-earmark-text-fill"></i>
                      <span>Laporan Pertanggungjawaban (LPJ)</span>
                  </a>
              </li>
          @endif

          @if (Auth::user() && (Auth::user()->isAdmin() || Auth::user()->isSuperAdmin()))
              <li class="nav-heading">Pengaturan</li>

              <li class="nav-item">
                  <a class="{{ request()->routeIs('pengaturan-absensi.*') ? 'nav-link' : 'nav-link collapsed' }}"
                      href="{{ route('pengaturan-absensi.index') }}">
                      <i class="bi bi-toggle-on"></i>
                      <span>Status Sesi Absensi</span>
                  </a>
              </li>
              <li class="nav-item">
                  <a class="{{ request()->routeIs('user.*') ? 'nav-link' : 'nav-link collapsed' }}"
                      href="{{ route('user.index') }}">
                      <i class="bi bi-person"></i>
                      <span>Pengguna</span>
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
                      <i class="bi bi-tags"></i>
                      <span>Kategori</span>
                  </a>
              </li>
          @endif

      </ul>

  </aside><!-- End Sidebar-->

  <style>
      /* Styling untuk merapikan submenu dan sejajar dengan bullet */
      .sidebar-nav .nav-content a {
          padding: 8px 10px 8px 24px !important;
          text-decoration: none;
      }
      .sidebar-nav .nav-content a .submenu-item-wrapper {
          display: flex;
          align-items: center;
          gap: 10px;
          min-width: 0;
          flex: 1;
      }
      .sidebar-nav .nav-content a .submenu-item-wrapper i {
          font-size: 6px;
          margin-right: 0 !important;
          flex-shrink: 0;
      }
      .sidebar-nav .nav-content a .submenu-item-text {
          white-space: nowrap;
          font-size: 13.5px;
          line-height: 1.2;
      }
      @media (max-width: 320px) {
          .sidebar-nav .nav-content a .submenu-item-wrapper {
              align-items: flex-start;
          }
          .sidebar-nav .nav-content a .submenu-item-wrapper i {
              margin-top: 6px;
          }
          .sidebar-nav .nav-content a .submenu-item-text {
              white-space: normal;
          }
      }
  </style>
