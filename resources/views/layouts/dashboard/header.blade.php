  <header id="header" class="header fixed-top d-flex align-items-center">

      <div class="d-flex align-items-center justify-content-between">
          <a href="{{ route('dashboard') }}" class="logo d-flex align-items-center">
              <img src="{{ asset('assets/img/logouis.png') }}" alt="Logo UIS">
              <span class="d-none d-lg-block">SIM KINERJA</span>
          </a>
          <i class="bi bi-list toggle-sidebar-btn"></i>
      </div>

      {{-- <div class="search-bar">
          <form class="search-form d-flex align-items-center" method="POST" action="#">
              <input type="text" name="query" placeholder="Search" title="Enter search keyword">
              <button type="submit" title="Search"><i class="bi bi-search"></i></button>
          </form>
      </div> --}}

      <nav class="header-nav ms-auto">
          <ul class="d-flex align-items-center">

              {{-- <li class="nav-item d-block d-lg-none">
                  <a class="nav-link nav-icon search-bar-toggle " href="#">
                      <i class="bi bi-search"></i>
                  </a>
              </li> --}}
              @php
                  $authUser = auth()->user();
                  $notifications = collect();

                  if ($authUser) {
                      $today = \Carbon\Carbon::now()->format('Y-m-d');

                      // 1. Overdue Tasks
                      $overdueTasks = \App\Models\RencanaKerja::where(function($q) use ($authUser) {
                              $q->where('user_id', $authUser->id)
                                ->orWhereHas('taggedUsers', function($qu) use ($authUser) {
                                    $qu->where('users.id', $authUser->id);
                                });
                          })
                          ->where('status', '!=', 'Selesai')
                          ->whereNotNull('estimasi_tanggal_selesai')
                          ->where('estimasi_tanggal_selesai', '<', $today)
                          ->latest()
                          ->take(3)
                          ->get();

                      foreach ($overdueTasks as $t) {
                          $notifications->push([
                              'icon' => 'bi-exclamation-triangle-fill text-danger',
                              'title' => 'Tugas Melewati Deadline',
                              'message' => \Illuminate\Support\Str::limit($t->uraian_tugas, 45),
                              'time' => 'Batas: ' . date('d/m/Y', strtotime($t->estimasi_tanggal_selesai)),
                              'url' => route('rencana-kerja.index')
                          ]);
                      }

                      // 2. Running Tasks
                      $runningTasks = \App\Models\RencanaKerja::where(function($q) use ($authUser) {
                              $q->where('user_id', $authUser->id)
                                ->orWhereHas('taggedUsers', function($qu) use ($authUser) {
                                    $qu->where('users.id', $authUser->id);
                                });
                          })
                          ->where('status', 'Berjalan')
                          ->latest()
                          ->take(2)
                          ->get();

                      foreach ($runningTasks as $t) {
                          $notifications->push([
                              'icon' => 'bi-play-circle-fill text-primary',
                              'title' => 'Tugas Sedang Berjalan',
                              'message' => \Illuminate\Support\Str::limit($t->uraian_tugas, 45),
                              'time' => 'Timer aktif saat ini',
                              'url' => route('rencana-kerja.index')
                          ]);
                      }

                      // 3. Tagged Kepanitiaan Tasks
                      $taggedKepanitiaans = $authUser->taggedKepanitiaans()->latest()->take(2)->get();
                      foreach ($taggedKepanitiaans as $k) {
                          $notifications->push([
                              'icon' => 'bi-people-fill text-warning',
                              'title' => 'Penugasan Kepanitiaan',
                              'message' => \Illuminate\Support\Str::limit($k->nama_kegiatan ?? $k->uraian_tugas, 45),
                              'time' => 'Terdaftar dalam tim',
                              'url' => route('kepanitiaan.index')
                          ]);
                      }

                      // 4. Permintaan Perubahan Estimasi dari bawahan (khusus Pimpinan/Admin)
                      if ($authUser->isAdmin() || $authUser->isPimpinanRektorat() || $authUser->isPimpinanUnit()) {
                          $isFullAccess = $authUser->isAdmin() || $authUser->isPimpinanRektorat();

                          $unlockSources = [
                              ['model' => \App\Models\RencanaKerja::class, 'label' => 'Rencana Kerja', 'route' => 'rencana-kerja.edit'],
                              ['model' => \App\Models\Kepanitiaan::class, 'label' => 'Kepanitiaan', 'route' => 'kepanitiaan.edit'],
                              ['model' => \App\Models\Insidentil::class, 'label' => 'Insidentil', 'route' => 'insidentil.edit'],
                          ];

                          foreach ($unlockSources as $src) {
                              $unlockQuery = $src['model']::whereNotNull('estimasi_unlock_requested_at');

                              if (!$isFullAccess) {
                                  $unlockQuery->whereHas('user', function ($q) use ($authUser) {
                                      $q->where('unit', $authUser->unit);
                                  });
                              }

                              $unlockRequests = $unlockQuery->latest('estimasi_unlock_requested_at')->take(2)->get();

                              foreach ($unlockRequests as $r) {
                                  $notifications->push([
                                      'icon' => 'bi-unlock-fill text-warning',
                                      'title' => 'Permintaan Perubahan Estimasi (' . $src['label'] . ')',
                                      'message' => \Illuminate\Support\Str::limit(($r->user->name ?? '-') . ': ' . $r->uraian_tugas, 45),
                                      'time' => 'Diajukan ' . \Carbon\Carbon::parse($r->estimasi_unlock_requested_at)->diffForHumans(),
                                      'url' => route($src['route'], $r->id)
                                  ]);
                              }
                          }
                      }
                  }
                  $notifCount = $notifications->count();
              @endphp

              <li class="nav-item dropdown">

                  <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
                      <i class="bi bi-bell"></i>
                      @if($notifCount > 0)
                          <span class="badge bg-danger badge-number">{{ $notifCount }}</span>
                      @endif
                  </a><!-- End Notification Icon -->

                  <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">
                      <li class="dropdown-header">
                          @if($notifCount > 0)
                              Anda memiliki {{ $notifCount }} pemberitahuan
                          @else
                              Tidak ada pemberitahuan baru
                          @endif
                      </li>
                      <li>
                          <hr class="dropdown-divider">
                      </li>

                      @forelse($notifications as $notif)
                          <li class="notification-item">
                              <a href="{{ $notif['url'] }}" class="d-flex text-dark text-decoration-none w-100">
                                  <i class="bi {{ $notif['icon'] }} me-2 fs-5"></i>
                                  <div>
                                      <h4 class="mb-1" style="font-size: 0.85rem; font-weight: 600;">{{ $notif['title'] }}</h4>
                                      <p class="mb-1 text-muted" style="font-size: 0.78rem;">{{ $notif['message'] }}</p>
                                      <p class="mb-0 text-secondary" style="font-size: 0.72rem;">{{ $notif['time'] }}</p>
                                  </div>
                              </a>
                          </li>
                          <li>
                              <hr class="dropdown-divider">
                          </li>
                      @empty
                          <li class="p-3 text-center text-muted small">
                              <i class="bi bi-check-circle text-success d-block fs-3 mb-1"></i>
                              Semua tugas Anda tepat waktu dan berjalan lancar!
                          </li>
                      @endforelse

                  </ul><!-- End Notification Dropdown Items -->

              </li>

              <li class="nav-item dropdown pe-3">

                  <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#"
                      data-bs-toggle="dropdown">
                      <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=cbd5e1&color=334155" alt="Profile" class="rounded-circle">
                      <span class="d-none d-md-block dropdown-toggle ps-2">{{ Auth::check() ? Auth::user()->name : 'User' }}</span>
                  </a><!-- End Profile Iamge Icon -->

                  <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                      <li class="dropdown-header">
                          <h6>{{ Auth::check() ? Auth::user()->name : 'User' }}</h6>
                          <span>{{ Auth::check() ? Auth::user()->roles : 'Staff' }}</span>
                      </li>
                      <li>
                          <hr class="dropdown-divider">
                      </li>

                      <li>
                          <a class="dropdown-item d-flex align-items-center" href="{{ route('profile') }}">
                              <i class="bi bi-person"></i>
                              <span>My Profile</span>
                          </a>
                      </li>
                      <li>
                          <hr class="dropdown-divider">
                      </li>

                      <li>
                          <a class="dropdown-item d-flex align-items-center" href="{{ route('profile.password') }}">
                              <i class="bi bi-key"></i>
                              <span>Update Password</span>
                          </a>
                      </li>

                      <li>
                          <hr class="dropdown-divider">
                      </li>

                      <li>
                          <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                              @csrf
                          </form>
                          <a class="dropdown-item d-flex align-items-center" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                              <i class="bi bi-box-arrow-right"></i>
                              <span>Sign Out</span>
                          </a>
                      </li>

                  </ul><!-- End Profile Dropdown Items -->
              </li><!-- End Profile Nav -->

          </ul>
      </nav><!-- End Icons Navigation -->

  </header><!-- End Header -->
