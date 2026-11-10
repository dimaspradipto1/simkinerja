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
                  $notifications = \App\Http\Controllers\NotificationController::fetchData($authUser);
                  $notifCount = $notifications->count();
              @endphp

              <li class="nav-item dropdown" id="notificationDropdownContainer">

                  <a class="nav-link nav-icon" href="#" id="notificationDropdownBtn" data-bs-toggle="dropdown" aria-expanded="false" title="Pemberitahuan">
                      <i class="bi bi-bell"></i>
                      <span class="badge bg-danger badge-number {{ $notifCount > 0 ? '' : 'd-none' }}" id="notificationBadge">
                          {{ $notifCount > 0 ? $notifCount : '' }}
                      </span>
                  </a><!-- End Notification Icon -->

                  <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications shadow-lg border-0 p-0" 
                      style="min-width: 340px; max-width: 400px; border-radius: 12px; overflow: hidden;" 
                      aria-labelledby="notificationDropdownBtn">
                      
                      <li class="dropdown-header d-flex align-items-center justify-content-between py-2.5 px-3 bg-light border-bottom">
                          <span class="fw-bold text-dark" id="notificationHeaderTitle" style="font-size: 0.82rem;">
                              @if($notifCount > 0)
                                  Anda memiliki {{ $notifCount }} pemberitahuan
                              @else
                                  Tidak ada pemberitahuan baru
                              @endif
                          </span>
                          <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none text-muted" id="btnRefreshNotif" title="Segarkan notifikasi" onclick="window.refreshNotifications(true)">
                              <i class="bi bi-arrow-clockwise fs-6" id="iconRefreshNotif"></i>
                          </button>
                      </li>

                      <div id="notificationItemsContainer" style="max-height: 420px; overflow-y: auto;">
                          @include('layouts.dashboard.notification_items', ['notifications' => $notifications])
                      </div>

                  </ul><!-- End Notification Dropdown Items -->

              </li>

              <li class="nav-item dropdown pe-3">

                  <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#"
                      data-bs-toggle="dropdown">
                      <img src="{{ Auth::check() ? Auth::user()->avatar_url : 'https://ui-avatars.com/api/?name=User' }}" alt="Profile" class="rounded-circle" style="width: 36px; height: 36px; object-fit: cover;">
                      <span class="d-none d-md-block dropdown-toggle ps-2">{{ Auth::check() ? Auth::user()->name : 'User' }}</span>
                  </a><!-- End Profile Iamge Icon -->

                  <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                      <li class="dropdown-header">
                          <h6>{{ Auth::check() ? Auth::user()->name : 'User' }}</h6>
                          <span>{{ Auth::check() ? (Auth::user()->jabatan ?: (Auth::user()->roles ?: 'Pegawai')) : 'Staff' }}</span>
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

  <style>
      @keyframes spinClockwise {
          from { transform: rotate(0deg); }
          to { transform: rotate(360deg); }
      }
      .spin-animation {
          display: inline-block;
          animation: spinClockwise 0.8s linear infinite;
      }
      .notif-clickable-item {
          cursor: pointer;
      }
      .notif-clickable-item:hover h6 {
          color: #0d6efd !important;
      }
  </style>

  <script>
      (function() {
          let isFetchingNotif = false;

          window.refreshNotifications = function(showSpinner = false) {
              if (isFetchingNotif) return;
              isFetchingNotif = true;

              const iconRefresh = document.getElementById('iconRefreshNotif');
              if (showSpinner && iconRefresh) {
                  iconRefresh.classList.add('spin-animation');
              }

              fetch('{{ route('notifications.data') }}', {
                  headers: {
                      'X-Requested-With': 'XMLHttpRequest',
                      'Accept': 'application/json'
                  }
              })
              .then(res => {
                  if (!res.ok) throw new Error('Network error ' + res.status);
                  return res.json();
              })
              .then(data => {
                  if (data && data.success) {
                      const badge = document.getElementById('notificationBadge');
                      const headerTitle = document.getElementById('notificationHeaderTitle');
                      const container = document.getElementById('notificationItemsContainer');

                      if (badge) {
                          if (data.count > 0) {
                              badge.textContent = data.count;
                              badge.classList.remove('d-none');
                          } else {
                              badge.textContent = '';
                              badge.classList.add('d-none');
                          }
                      }

                      if (headerTitle) {
                          headerTitle.textContent = data.count > 0 
                              ? `Anda memiliki ${data.count} pemberitahuan` 
                              : 'Tidak ada pemberitahuan baru';
                      }

                      if (container) {
                          container.innerHTML = data.html;
                      }
                  }
              })
              .catch(err => {
                  console.error('Error refreshing notifications:', err);
              })
              .finally(() => {
                  isFetchingNotif = false;
                  if (iconRefresh) {
                      iconRefresh.classList.remove('spin-animation');
                  }
              });
          };

          document.addEventListener('DOMContentLoaded', function() {
              // Otomatis refresh saat dropdown notifikasi diklik / dibuka
              const dropdownEl = document.getElementById('notificationDropdownContainer');
              if (dropdownEl) {
                  dropdownEl.addEventListener('show.bs.dropdown', function() {
                      window.refreshNotifications(false);
                  });
              }

              // Polling latar belakang setiap 30 detik untuk update badge secara dinamis
              setInterval(function() {
                  window.refreshNotifications(false);
              }, 30000);
          });
      })();
  </script>
