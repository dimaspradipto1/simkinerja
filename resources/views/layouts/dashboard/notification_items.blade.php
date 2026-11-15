@forelse($notifications as $notif)
    <li class="notification-item p-2.5 border-bottom position-relative" 
        style="cursor: pointer; transition: background-color 0.15s ease; background-color: #ffffff; font-size: 10px;" 
        onmouseover="this.style.backgroundColor='#f8fafc'" 
        onmouseout="this.style.backgroundColor='#ffffff'">

        <div class="d-flex align-items-start gap-2 w-100">
            <!-- Icon Circle (28px) -->
            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 mt-0.5 shadow-2xs" 
                 style="width: 28px; height: 28px; background-color: {{ $notif['bg_color'] ?? '#e0f2fe' }};">
                <i class="bi {{ $notif['icon'] }}" style="font-size: 13px;"></i>
            </div>

            <!-- Content Area (10px) -->
            <div class="flex-grow-1 overflow-hidden" style="font-size: 10px;">
                <!-- Row 1: Badge & Time -->
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="badge {{ $notif['badge_class'] ?? 'bg-secondary' }} px-1.5 py-0.5 rounded" style="font-size: 9px; font-weight: 700; letter-spacing: 0.2px;">
                        {{ $notif['badge'] }}
                    </span>
                    <span class="text-muted d-inline-flex align-items-center gap-1" style="font-size: 10px;">
                        <i class="bi bi-clock" style="font-size: 9.5px;"></i>{{ $notif['time'] }}
                    </span>
                </div>

                <!-- Row 2: Title (10px Bold) -->
                <div class="text-truncate fw-bold mb-0.5 {{ ($notif['type'] ?? '') === 'running' ? 'text-primary' : 'text-dark' }}" style="font-size: 10px; line-height: 1.3;" title="{{ $notif['title'] }}">
                    {{ $notif['title'] }}
                </div>

                <!-- Row 3: Description (10px Regular) -->
                <p class="mb-1 text-secondary text-truncate" style="font-size: 10px; line-height: 1.35;" title="{{ $notif['message'] }}">
                    {{ $notif['message'] }}
                </p>

                <!-- Row 4: Action link & Individual Mark-as-read (10px) -->
                <div class="d-flex align-items-center justify-content-between pt-0.5">
                    @if(($notif['action_type'] ?? '') === 'modal_approve')
                        <span class="text-warning-emphasis fw-bold d-inline-flex align-items-center gap-1" 
                              style="font-size: 10px; cursor: pointer;"
                              role="button"
                              data-id="{{ $notif['id'] }}"
                              data-uraian="{{ e($notif['uraian']) }}"
                              data-pegawai="{{ e($notif['pegawai']) }}"
                              data-reason="{{ e($notif['reason']) }}"
                              data-tgl-selesai="{{ e($notif['tgl_selesai']) }}"
                              data-jam-selesai="{{ e($notif['jam_selesai']) }}"
                              data-route="{{ $notif['route'] }}"
                              onclick="window.openApproveExtensionModal(this)">
                            <i class="bi bi-pencil-square" style="font-size: 10px;"></i>
                            <span>Beri Perpanjangan</span>
                        </span>
                    @else
                        <a href="{{ $notif['url'] }}" class="text-success text-decoration-none fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 10px;">
                            <span>Buka halaman</span>
                            <i class="bi bi-arrow-right" style="font-size: 9.5px;"></i>
                        </a>
                    @endif

                    <!-- Individual Mark as Read Button -->
                    <button type="button" 
                            class="btn btn-link p-0 text-decoration-none text-muted d-inline-flex align-items-center gap-0.5 border-0 bg-transparent" 
                            style="font-size: 10px; cursor: pointer;" 
                            title="Tandai sudah dibaca"
                            onclick="event.preventDefault(); event.stopPropagation(); window.markSingleNotificationRead('{{ $notif['key'] }}')">
                        <i class="bi bi-check" style="font-size: 13px; color: #64748b;"></i>
                        <span style="color: #64748b; font-size: 10px;">Dibaca</span>
                    </button>
                </div>
            </div>
        </div>

    </li>
@empty
    <li class="p-3 text-center text-muted" id="notifEmptyState" style="font-size: 10px;">
        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-1.5" style="width: 32px; height: 32px;">
            <i class="bi bi-check2-circle text-success" style="font-size: 16px;"></i>
        </div>
        <p class="mb-0.5 fw-bold text-dark" style="font-size: 10px;">Tidak ada pemberitahuan</p>
        <span class="text-muted d-block" style="font-size: 10px;">Semua tugas dan aktivitas berjalan lancar!</span>
    </li>
@endforelse
