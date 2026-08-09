  <!-- ======= Sidebar ======= -->
  <aside id="sidebar" class="sidebar">

      <ul class="sidebar-nav" id="sidebar-nav">

          <li class="nav-item">
              <a class="{{ request()->routeIs('dashboard') ? 'nav-link' : 'nav-link collapsed' }}" href="{{ route('dashboard') }}">
                  <i class="bi bi-grid"></i>
                  <span>Dashboard</span>
              </a>
          </li><!-- End Dashboard Nav -->

          <li class="nav-heading">Tugas & Kinerja</li>
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
              <a class="{{ (request()->routeIs('rekapitulasi.*') || request()->routeIs('rekapitulasi-kepanitiaan.*') || request()->routeIs('rekapitulasi-insidentil.*')) ? 'nav-link' : 'nav-link collapsed' }}"
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

          @if(Auth::user() && (Auth::user()->isAdmin() || Auth::user()->isSuperAdmin()))
          <li class="nav-heading">Pengaturan</li>

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
          @endif

      </ul>

  </aside><!-- End Sidebar-->
