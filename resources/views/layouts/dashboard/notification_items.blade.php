@forelse($notifications as $notif)
    <li class="notification-item py-2 px-3 border-bottom position-relative" 
        style="cursor: pointer; transition: all 0.2s ease; background-color: #ffffff;" 
        onmouseover="this.style.backgroundColor='#f8fafc'" 
        onmouseout="this.style.backgroundColor='#ffffff'">

        @if(($notif['action_type'] ?? '') === 'modal_approve')
            <div class="d-flex align-items-start gap-2.5 w-100 text-decoration-none notif-clickable-item"
                 role="button"
                 tabindex="0"
                 data-id="{{ $notif['id'] }}"
                 data-uraian="{{ e($notif['uraian']) }}"
                 data-pegawai="{{ e($notif['pegawai']) }}"
                 data-reason="{{ e($notif['reason']) }}"
                 data-tgl-selesai="{{ e($notif['tgl_selesai']) }}"
                 data-jam-selesai="{{ e($notif['jam_selesai']) }}"
                 data-route="{{ $notif['route'] }}"
                 onclick="window.openApproveExtensionModal(this)">
                
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 mt-1 shadow-2xs" 
                     style="width: 36px; height: 36px; background-color: {{ $notif['bg_color'] ?? '#fef3c7' }};">
                    <i class="bi {{ $notif['icon'] }} fs-5"></i>
                </div>

                <div class="flex-grow-1 overflow-hidden">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="badge {{ $notif['badge_class'] ?? 'bg-warning text-dark' }} px-2 py-0.5 rounded-pill" style="font-size: 0.65rem; font-weight: 700; letter-spacing: 0.3px;">
                            {{ $notif['badge'] }}
                        </span>
                        <span class="text-muted" style="font-size: 0.70rem;">
                            <i class="bi bi-clock me-0.5"></i>{{ $notif['time'] }}
                        </span>
                    </div>

                    <h6 class="mb-1 text-dark fw-bold text-truncate" style="font-size: 0.83rem; line-height: 1.3;" title="{{ $notif['title'] }}">
                        {{ $notif['title'] }}
                    </h6>

                    <p class="mb-1.5 text-secondary text-truncate small" style="font-size: 0.77rem; line-height: 1.35;" title="{{ $notif['message'] }}">
                        {{ $notif['message'] }}
                    </p>

                    <div class="d-inline-flex align-items-center gap-1 text-warning-emphasis fw-bold bg-warning-subtle px-2 py-0.5 rounded border border-warning-subtle" style="font-size: 0.70rem;">
                        <i class="bi bi-pencil-square"></i>
                        <span>Klik untuk beri perpanjangan</span>
                    </div>
                </div>
            </div>
        @else
            <a href="{{ $notif['url'] }}" class="d-flex align-items-start gap-2.5 w-100 text-decoration-none text-dark notif-clickable-item">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 mt-1 shadow-2xs" 
                     style="width: 36px; height: 36px; background-color: {{ $notif['bg_color'] ?? '#e0f2fe' }};">
                    <i class="bi {{ $notif['icon'] }} fs-5"></i>
                </div>

                <div class="flex-grow-1 overflow-hidden">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="badge {{ $notif['badge_class'] ?? 'bg-secondary' }} px-2 py-0.5 rounded-pill" style="font-size: 0.65rem; font-weight: 700; letter-spacing: 0.3px;">
                            {{ $notif['badge'] }}
                        </span>
                        <span class="text-muted" style="font-size: 0.70rem;">
                            <i class="bi bi-clock me-0.5"></i>{{ $notif['time'] }}
                        </span>
                    </div>

                    <h6 class="mb-1 text-dark fw-bold text-truncate" style="font-size: 0.83rem; line-height: 1.3;" title="{{ $notif['title'] }}">
                        {{ $notif['title'] }}
                    </h6>

                    <p class="mb-1.5 text-secondary text-truncate small" style="font-size: 0.77rem; line-height: 1.35;" title="{{ $notif['message'] }}">
                        {{ $notif['message'] }}
                    </p>

                    <div class="d-inline-flex align-items-center gap-1 text-primary fw-semibold" style="font-size: 0.70rem;">
                        <span>Buka halaman</span>
                        <i class="bi bi-arrow-right"></i>
                    </div>
                </div>
            </a>
        @endif

    </li>
@empty
    <li class="p-4 text-center text-muted" id="notifEmptyState">
        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-2" style="width: 48px; height: 48px;">
            <i class="bi bi-bell-slash text-secondary fs-4"></i>
        </div>
        <p class="mb-1 fw-bold small text-dark">Tidak ada pemberitahuan baru</p>
        <small class="text-muted" style="font-size: 0.75rem;">Semua tugas dan aktivitas berjalan lancar!</small>
    </li>
@endforelse
