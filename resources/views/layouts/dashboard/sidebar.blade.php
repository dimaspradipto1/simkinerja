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

          @if (!Auth::check() || Auth::user()->canAccessAbsensi())
              @php
                  $absensiStatuses = \App\Models\AbsensiSetting::getAllStatuses();
              @endphp
              <li class="nav-heading">Absensi</li>

              <li class="nav-item">
                  <a class="{{ request()->routeIs('absensi-pkkmb-pertama.*') || request()->routeIs('absensi-pkkmb-kedua.*') || request()->routeIs('absensi-pkkmb-ketiga.*') ? 'nav-link' : 'nav-link collapsed' }}"
                      data-bs-target="#absensi-nav" data-bs-toggle="collapse" href="#">
                      <i class="bi bi-calendar-check-fill"></i><span>Kepanitiaan PKKMB</span><i
                          class="bi bi-chevron-down ms-auto"></i>
                  </a>
                  <ul id="absensi-nav"
                      class="nav-content collapse {{ request()->routeIs('absensi-pkkmb-pertama.*') || request()->routeIs('absensi-pkkmb-kedua.*') || request()->routeIs('absensi-pkkmb-ketiga.*') ? 'show' : '' }}"
                      data-bs-parent="#sidebar-nav">
                      <li>
                          <a href="{{ route('absensi-pkkmb-pertama.index') }}"
                              class="{{ request()->routeIs('absensi-pkkmb-pertama.*') ? 'active' : '' }} d-flex align-items-center justify-content-between pe-3">
                              <div class="submenu-item-wrapper">
                                  <i class="bi bi-circle"></i>
                                  <span class="submenu-item-text">Absensi Hari Pertama</span>
                              </div>
                              <span class="badge {{ ($absensiStatuses['absensi-pkkmb-pertama'] ?? true) ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle' }} rounded-pill py-0 px-2 flex-shrink-0" style="font-size: 9px;">
                                  {{ ($absensiStatuses['absensi-pkkmb-pertama'] ?? true) ? 'Aktif' : 'Tutup' }}
                              </span>
                          </a>
                      </li>
                      <li>
                          <a href="{{ route('absensi-pkkmb-kedua.index') }}"
                              class="{{ request()->routeIs('absensi-pkkmb-kedua.*') ? 'active' : '' }} d-flex align-items-center justify-content-between pe-3">
                              <div class="submenu-item-wrapper">
                                  <i class="bi bi-circle"></i>
                                  <span class="submenu-item-text">Absensi Hari Kedua</span>
                              </div>
                              <span class="badge {{ ($absensiStatuses['absensi-pkkmb-kedua'] ?? true) ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle' }} rounded-pill py-0 px-2 flex-shrink-0" style="font-size: 9px;">
                                  {{ ($absensiStatuses['absensi-pkkmb-kedua'] ?? true) ? 'Aktif' : 'Tutup' }}
                              </span>
                          </a>
                      </li>
                      <li>
                          <a href="{{ route('absensi-pkkmb-ketiga.index') }}"
                              class="{{ request()->routeIs('absensi-pkkmb-ketiga.*') ? 'active' : '' }} d-flex align-items-center justify-content-between pe-3">
                              <div class="submenu-item-wrapper">
                                  <i class="bi bi-circle"></i>
                                  <span class="submenu-item-text">Absensi Hari Ketiga</span>
                              </div>
                              <span class="badge {{ ($absensiStatuses['absensi-pkkmb-ketiga'] ?? true) ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle' }} rounded-pill py-0 px-2 flex-shrink-0" style="font-size: 9px;">
                                  {{ ($absensiStatuses['absensi-pkkmb-ketiga'] ?? true) ? 'Aktif' : 'Tutup' }}
                              </span>
                          </a>
                      </li>
                  </ul>
              </li>

              <li class="nav-item">
                  <a class="{{ request()->routeIs('absensi-esq-pertama.*') || request()->routeIs('absensi-esq-kedua.*') || request()->routeIs('absensi-esq-ketiga.*') || request()->routeIs('absensi-esq-keempat.*') ? 'nav-link' : 'nav-link collapsed' }}"
                      data-bs-target="#absensi-esq-nav" data-bs-toggle="collapse" href="#">
                      <i class="bi bi-calendar-check-fill"></i><span>Kepanitiaan ESQ</span><i
                          class="bi bi-chevron-down ms-auto"></i>
                  </a>
                  <ul id="absensi-esq-nav"
                      class="nav-content collapse {{ request()->routeIs('absensi-esq-pertama.*') || request()->routeIs('absensi-esq-kedua.*') || request()->routeIs('absensi-esq-ketiga.*') || request()->routeIs('absensi-esq-keempat.*') ? 'show' : '' }}"
                      data-bs-parent="#sidebar-nav">
                      <li>
                          <a href="{{ route('absensi-esq-pertama.index') }}"
                              class="{{ request()->routeIs('absensi-esq-pertama.*') ? 'active' : '' }} d-flex align-items-center justify-content-between pe-3">
                              <div class="submenu-item-wrapper">
                                  <i class="bi bi-circle"></i>
                                  <span class="submenu-item-text">Absensi Hari Pertama</span>
                              </div>
                              <span class="badge {{ ($absensiStatuses['absensi-esq-pertama'] ?? true) ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle' }} rounded-pill py-0 px-2 flex-shrink-0" style="font-size: 9px;">
                                  {{ ($absensiStatuses['absensi-esq-pertama'] ?? true) ? 'Aktif' : 'Tutup' }}
                              </span>
                          </a>
                      </li>
                      <li>
                          <a href="{{ route('absensi-esq-kedua.index') }}"
                              class="{{ request()->routeIs('absensi-esq-kedua.*') ? 'active' : '' }} d-flex align-items-center justify-content-between pe-3">
                              <div class="submenu-item-wrapper">
                                  <i class="bi bi-circle"></i>
                                  <span class="submenu-item-text">Absensi Hari Kedua</span>
                              </div>
                              <span class="badge {{ ($absensiStatuses['absensi-esq-kedua'] ?? true) ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle' }} rounded-pill py-0 px-2 flex-shrink-0" style="font-size: 9px;">
                                  {{ ($absensiStatuses['absensi-esq-kedua'] ?? true) ? 'Aktif' : 'Tutup' }}
                              </span>
                          </a>
                      </li>
                      <li>
                          <a href="{{ route('absensi-esq-ketiga.index') }}"
                              class="{{ request()->routeIs('absensi-esq-ketiga.*') ? 'active' : '' }} d-flex align-items-center justify-content-between pe-3">
                              <div class="submenu-item-wrapper">
                                  <i class="bi bi-circle"></i>
                                  <span class="submenu-item-text">Absensi Hari Ketiga</span>
                              </div>
                              <span class="badge {{ ($absensiStatuses['absensi-esq-ketiga'] ?? true) ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle' }} rounded-pill py-0 px-2 flex-shrink-0" style="font-size: 9px;">
                                  {{ ($absensiStatuses['absensi-esq-ketiga'] ?? true) ? 'Aktif' : 'Tutup' }}
                              </span>
                          </a>
                      </li>
                      <li>
                          <a href="{{ route('absensi-esq-keempat.index') }}"
                              class="{{ request()->routeIs('absensi-esq-keempat.*') ? 'active' : '' }} d-flex align-items-center justify-content-between pe-3">
                              <div class="submenu-item-wrapper">
                                  <i class="bi bi-circle"></i>
                                  <span class="submenu-item-text">Absensi Hari Keempat</span>
                              </div>
                              <span class="badge {{ ($absensiStatuses['absensi-esq-keempat'] ?? true) ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle' }} rounded-pill py-0 px-2 flex-shrink-0" style="font-size: 9px;">
                                  {{ ($absensiStatuses['absensi-esq-keempat'] ?? true) ? 'Aktif' : 'Tutup' }}
                              </span>
                          </a>
                      </li>
                  </ul>
              </li>

              <li class="nav-item">
                  <a class="{{ request()->routeIs('absensi-milad-pertama.*') || request()->routeIs('absensi-milad-kedua.*') ? 'nav-link' : 'nav-link collapsed' }}"
                      data-bs-target="#absensi-milad-nav" data-bs-toggle="collapse" href="#">
                      <i class="bi bi-calendar-check-fill"></i><span>Kepanitiaan MILAD</span><i
                          class="bi bi-chevron-down ms-auto"></i>
                  </a>
                  <ul id="absensi-milad-nav"
                      class="nav-content collapse {{ request()->routeIs('absensi-milad-pertama.*') || request()->routeIs('absensi-milad-kedua.*') ? 'show' : '' }}"
                      data-bs-parent="#sidebar-nav">
                      <li>
                          <a href="{{ route('absensi-milad-pertama.index') }}"
                              class="{{ request()->routeIs('absensi-milad-pertama.*') ? 'active' : '' }} d-flex align-items-center justify-content-between pe-3">
                              <div class="submenu-item-wrapper">
                                  <i class="bi bi-circle"></i>
                                  <span class="submenu-item-text">Absensi Hari Pertama</span>
                              </div>
                              <span class="badge {{ ($absensiStatuses['absensi-milad-pertama'] ?? true) ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle' }} rounded-pill py-0 px-2 flex-shrink-0" style="font-size: 9px;">
                                  {{ ($absensiStatuses['absensi-milad-pertama'] ?? true) ? 'Aktif' : 'Tutup' }}
                              </span>
                          </a>
                      </li>
                      <li>
                          <a href="{{ route('absensi-milad-kedua.index') }}"
                              class="{{ request()->routeIs('absensi-milad-kedua.*') ? 'active' : '' }} d-flex align-items-center justify-content-between pe-3">
                              <div class="submenu-item-wrapper">
                                  <i class="bi bi-circle"></i>
                                  <span class="submenu-item-text">Absensi Hari Kedua</span>
                              </div>
                              <span class="badge {{ ($absensiStatuses['absensi-milad-kedua'] ?? true) ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle' }} rounded-pill py-0 px-2 flex-shrink-0" style="font-size: 9px;">
                                  {{ ($absensiStatuses['absensi-milad-kedua'] ?? true) ? 'Aktif' : 'Tutup' }}
                              </span>
                          </a>
                      </li>
                  </ul>
              </li>

              <li class="nav-item">
                  <a class="{{ request()->routeIs('absensi-kuliah-umum-pertama.*') ? 'nav-link' : 'nav-link collapsed' }} d-flex align-items-center justify-content-between pe-3"
                      href="{{ route('absensi-kuliah-umum-pertama.index') }}">
                      <div class="d-flex align-items-center gap-2">
                          <i class="bi bi-calendar-check-fill"></i>
                          <span>Kepanitiaan Kuliah Umum</span>
                      </div>
                      <span class="badge {{ ($absensiStatuses['absensi-kuliah-umum-pertama'] ?? true) ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle' }} rounded-pill py-0 px-2 flex-shrink-0" style="font-size: 9px;">
                          {{ ($absensiStatuses['absensi-kuliah-umum-pertama'] ?? true) ? 'Aktif' : 'Tutup' }}
                      </span>
                  </a>
              </li>

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
