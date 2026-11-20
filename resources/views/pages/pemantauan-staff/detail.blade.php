@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
    <div>
        <h1 class="fw-bold mb-1" style="color: #15432D; font-size: 1.55rem;">
            <i class="bi bi-person-badge-fill me-2"></i>Rincian Kegiatan &amp; Validasi Staf
        </h1>
        <nav>
            <ol class="breadcrumb mb-0" style="font-size: 0.85rem;">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('pemantauan-staff.index') }}">Pemantauan Staff</a></li>
                <li class="breadcrumb-item active">{{ $pegawai->name }}</li>
            </ol>
        </nav>
    </div>

    <!-- Tombol Kembali -->
    <div>
        <a href="{{ route('pemantauan-staff.index', ['bulan' => $bulan === 'all' ? now()->month : $bulan, 'tahun' => $tahun]) }}" 
           class="btn btn-sm btn-outline-secondary fw-semibold d-inline-flex align-items-center gap-1.5 px-3 py-2 rounded-2 shadow-xs bg-white">
            <i class="bi bi-arrow-left"></i>
            <span>Kembali ke Pemantauan Staf</span>
        </a>
    </div>
</div>

<!-- Top Indicator Bar (Sesuai Referensi Gambar) -->
<div class="d-flex align-items-center flex-wrap gap-2 mb-3 pb-2 border-bottom" style="font-size: 0.85rem;">
    <div class="badge bg-light text-dark border px-3 py-2 fw-medium d-inline-flex align-items-center gap-2">
        <i class="bi bi-people-fill text-success"></i>
        <span>Tim saya <strong class="text-success ms-1">{{ $totalTeam }}</strong></span>
    </div>
    <div class="badge bg-light text-dark border px-3 py-2 fw-medium d-inline-flex align-items-center gap-2">
        <i class="bi bi-check2-square text-warning"></i>
        <span>Menunggu verifikasi <strong class="text-warning-emphasis ms-1">{{ $totalPendingTeam }}</strong></span>
    </div>
    <div class="badge bg-light text-secondary border px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
        <i class="bi bi-person-fill text-secondary"></i>
        <span class="text-dark">{{ $pegawai->name }}</span>
    </div>
</div>

@php
    $cleanWa = preg_replace('/\D/', '', $pegawai->nomor_wa ?? '');
    if (str_starts_with($cleanWa, '0')) {
        $cleanWa = '62' . substr($cleanWa, 1);
    }
    $waMsg = rawurlencode("Halo {$pegawai->name}, saya sedang meninjau catatan kegiatan Anda di SIMKINERJA.");
@endphp

<!-- Main Container Card -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <!-- Card Header Info Pegawai & Filter Periode -->
    <div class="card-header bg-white p-3 p-md-4 border-bottom">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
            <!-- Left Info -->
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center text-white fw-bold shadow-xs flex-shrink-0" 
                     style="width: 52px; height: 52px; background: linear-gradient(135deg, #046B26, #15432D); font-size: 1.25rem;">
                    <i class="bi bi-journal-check"></i>
                </div>
                <div>
                    <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2 flex-wrap" style="font-size: 1.15rem;">
                        <span>Catatan kegiatan · {{ $pegawai->name }}</span>
                        @if($pegawai->jabatan)
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill fw-normal" style="font-size: 0.72rem;">
                                {{ $pegawai->jabatan }}
                            </span>
                        @endif
                    </h5>
                    <div class="text-muted small d-flex flex-wrap align-items-center gap-1.5" style="font-size: 0.83rem;">
                        <span>{{ $pegawai->unit ?: 'Universitas Ibnu Sina' }}</span>
                        <span class="text-secondary opacity-50">•</span>
                        <span>{{ $totalTasks }} kegiatan (<strong class="text-success">{{ $disetujuiCount }} terverifikasi</strong>)</span>
                        <span class="text-secondary opacity-50">•</span>
                        <span>Total: <strong class="text-dark font-monospace">{{ $totalJamFormatted }}</strong></span>
                    </div>
                </div>
            </div>

            <!-- Right Controls: Filter & Bulk Action -->
            <div class="d-flex align-items-center flex-wrap gap-2">
                <!-- Dropdown Periode -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle d-inline-flex align-items-center gap-2 px-3 py-2 rounded-2 bg-white fw-medium shadow-xs" 
                            type="button" id="dropdownPeriode" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.84rem;">
                        <i class="bi bi-calendar-event text-secondary"></i>
                        <span>
                            @if($isAllPeriod)
                                Sepanjang periode
                            @else
                                {{ $daftarBulan[$bulan] ?? 'Bulan ' . $bulan }} {{ $tahun }}
                            @endif
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="dropdownPeriode" style="max-height: 320px; overflow-y: auto; font-size: 0.84rem;">
                        <li><h6 class="dropdown-header">Pilihan Periode</h6></li>
                        <li>
                            <a class="dropdown-item {{ $isAllPeriod ? 'active bg-success' : '' }}" 
                               href="{{ route('pemantauan-staff.detail', ['id' => $pegawai->id, 'periode' => 'all', 'tahun' => $tahun]) }}">
                                <i class="bi bi-clock-history me-1.5"></i>Sepanjang periode
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><h6 class="dropdown-header">Bulan (Tahun {{ $tahun }})</h6></li>
                        @foreach($daftarBulan as $num => $namaBln)
                            <li>
                                <a class="dropdown-item {{ (!$isAllPeriod && (int)$bulan === $num) ? 'active bg-success' : '' }}" 
                                   href="{{ route('pemantauan-staff.detail', ['id' => $pegawai->id, 'bulan' => $num, 'tahun' => $tahun]) }}">
                                    {{ $namaBln }} {{ $tahun }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Tombol Hubungi WhatsApp -->
                @if(!empty($cleanWa))
                    <a href="https://wa.me/{{ $cleanWa }}?text={{ $waMsg }}" target="_blank" 
                       class="btn btn-sm btn-outline-success fw-semibold d-inline-flex align-items-center gap-1.5 px-3 py-2 rounded-2 shadow-xs bg-white" 
                       style="font-size: 0.84rem;" title="Kirim Pesan WhatsApp ke {{ $pegawai->name }}">
                        <i class="bi bi-whatsapp"></i>
                        <span>Beri umpan balik</span>
                    </a>
                @endif

                <!-- Tombol Batch Approve -->
                <button type="button" class="btn btn-sm text-white fw-bold d-inline-flex align-items-center gap-1.5 px-3 py-2 rounded-2 shadow-xs" 
                        id="btnBatchApprove" style="background-color: #046B26; border-color: #046B26; font-size: 0.84rem;" disabled>
                    <i class="bi bi-check2-all"></i>
                    <span>Setujui Terpilih (<span id="selectedCountBadge">0</span>)</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Table Card Body -->
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0 w-100" id="tableCatatanKegiatan" style="border-collapse: collapse; border-color: #e2e8f0;">
                <thead style="background-color: #046B26 !important; color: #ffffff !important;">
                    <tr>
                        <th width="38px" class="text-center align-middle" style="background-color: #046B26 !important; color: #ffffff !important; border: 1px solid #03541e; padding: 12px 6px;">
                            <input type="checkbox" id="checkAllMaster" class="form-check-input" style="cursor: pointer; width: 16px; height: 16px;">
                        </th>
                        <th width="125px" class="text-center align-middle" style="background-color: #046B26 !important; color: #ffffff !important; font-weight: 600; font-size: 0.85rem; border: 1px solid #03541e; padding: 12px 8px;">
                            Jam
                        </th>
                        <th width="140px" class="text-center align-middle" style="background-color: #046B26 !important; color: #ffffff !important; font-weight: 600; font-size: 0.85rem; border: 1px solid #03541e; padding: 12px 8px;">
                            Total jam kerja
                        </th>
                        <th class="align-middle" style="background-color: #046B26 !important; color: #ffffff !important; font-weight: 600; font-size: 0.85rem; border: 1px solid #03541e; padding: 12px 14px;">
                            Kegiatan
                        </th>
                        <th width="115px" class="text-center align-middle" style="background-color: #046B26 !important; color: #ffffff !important; font-weight: 600; font-size: 0.85rem; border: 1px solid #03541e; padding: 12px 8px;">
                            Jenis
                        </th>
                        <th width="135px" class="text-center align-middle" style="background-color: #046B26 !important; color: #ffffff !important; font-weight: 600; font-size: 0.85rem; border: 1px solid #03541e; padding: 12px 8px;">
                            Status
                        </th>
                        <th width="150px" class="text-center align-middle" style="background-color: #046B26 !important; color: #ffffff !important; font-weight: 600; font-size: 0.85rem; border: 1px solid #03541e; padding: 12px 8px;">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($groupedTasks as $dateKey => $groupItems)
                        @php
                            $groupOwned = $groupItems->where('is_owner', true);
                            $groupDraftCount = $groupOwned->where('status', 'Belum Mulai')->count();
                            $groupActiveCount = $groupOwned->where('status', '!=', 'Belum Mulai')->count();
                            $groupTotalSeconds = $groupOwned->sum('durasi_detik');

                            $gH = floor($groupTotalSeconds / 3600);
                            $gM = floor(($groupTotalSeconds % 3600) / 60);
                            $gDurText = $gH > 0 
                                ? ($gM > 0 ? "{$gH} jam {$gM} menit" : "{$gH} jam") 
                                : ($gM > 0 ? "{$gM} menit" : "0 jam");

                            $summaryParts = [];
                            $summaryParts[] = $groupActiveCount . ' kegiatan';
                            $summaryParts[] = $gDurText;
                            if ($groupDraftCount > 0) {
                                $summaryParts[] = $groupDraftCount . ' draft';
                            }
                            $groupSummaryText = implode(' · ', $summaryParts);
                            $firstItem = $groupItems->first();
                        @endphp

                        <!-- Header Baris Per Tanggal (Persis Referensi Gambar) -->
                        <tr class="date-group-header-row" style="background-color: #f8fafc; border-top: 2px solid #e2e8f0; border-bottom: 1px solid #cbd5e1;">
                            <td colspan="7" class="py-2.5 px-3 align-middle">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-calendar-event text-secondary" style="font-size: 0.95rem;"></i>
                                        <span class="fw-bold text-dark" style="font-size: 0.88rem;">{{ $firstItem['date_group_label'] }}</span>
                                    </div>
                                    <div class="text-muted small" style="font-size: 0.82rem;">
                                        {{ $groupSummaryText }}
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <!-- Baris Tugas Dalam Tanggal Terkait -->
                        @foreach($groupItems as $t)
                            @php
                                $isApproved = in_array($t['status'], ['Disetujui', 'Selesai']);
                                $isPending = ($t['status'] === 'Menunggu Validasi');
                                $isRevisi = ($t['status'] === 'Perlu Revisi');
                                $isRejected = ($t['status'] === 'Ditolak');
                                $isRunning = in_array($t['status'], ['Berjalan', 'Proses']);
                                $isDraft = ($t['status'] === 'Belum Mulai');
                            @endphp
                            <tr class="task-row {{ $isApproved ? 'task-row-approved' : ($isPending ? 'task-row-pending' : '') }}" id="task-row-{{ $t['id'] }}">
                                <!-- Checkbox -->
                                <td class="text-center align-middle px-1">
                                    <input type="checkbox" class="form-check-input check-task-item" 
                                           value="{{ $t['id'] }}" 
                                           data-can-validate="{{ $t['can_validate'] ? '1' : '0' }}"
                                           data-status="{{ $t['status'] }}"
                                           {{ !$t['can_validate'] || $isApproved ? 'disabled' : '' }}
                                           style="cursor: pointer; width: 16px; height: 16px;">
                                </td>

                                <!-- Jam (Realisasi & Estimasi) -->
                                <td class="text-center align-middle px-2">
                                    @if($t['has_real_mulai'] && $t['has_real_selesai'])
                                        <div class="fw-semibold text-dark font-monospace text-nowrap" style="font-size: 0.84rem;">
                                            {{ $t['real_text'] }}
                                        </div>
                                        @if($t['est_text'])
                                            <div class="text-muted font-monospace text-nowrap" style="font-size: 0.72rem;">
                                                Est: {{ $t['est_text'] }}
                                            </div>
                                        @endif
                                    @elseif($t['has_real_mulai'])
                                        <div class="fw-semibold text-primary font-monospace text-nowrap" style="font-size: 0.84rem;">
                                            {{ $t['real_text'] }} - <span class="badge bg-primary-subtle text-primary border" style="font-size: 0.68rem; padding: 1px 4px;">Aktif</span>
                                        </div>
                                        @if($t['est_text'])
                                            <div class="text-muted font-monospace text-nowrap" style="font-size: 0.72rem;">
                                                Est: {{ $t['est_text'] }}
                                            </div>
                                        @endif
                                    @elseif($t['est_text'])
                                        <div class="fw-semibold text-secondary font-monospace text-nowrap" style="font-size: 0.84rem;">
                                            {{ $t['est_text'] }}
                                        </div>
                                        <div class="text-muted" style="font-size: 0.70rem;">Estimasi</div>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>

                                <!-- Total Jam Kerja -->
                                <td class="text-center align-middle px-2">
                                    @if($t['durasi_detik'] > 0)
                                        <span class="fw-medium text-dark small text-nowrap" title="Setara {{ $t['durasi_jam_dec'] }} jam">
                                            {{ $t['durasi_jam_menit'] }}
                                        </span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>

                                <!-- Uraian Kegiatan -->
                                <td class="align-middle px-3 py-2 text-start">
                                    <div class="fw-bold text-dark fs-6" style="font-size: 0.88rem !important; line-height: 1.35;">
                                        {{ $t['uraian_tugas'] }}
                                    </div>

                                    @if(!empty($t['hasil_kerja']) && $t['hasil_kerja'] !== '-')
                                        <div class="text-muted small mt-1" style="font-size: 0.80rem;">
                                            <span class="fw-semibold text-secondary">Hasil:</span> {{ $t['hasil_kerja'] }}
                                        </div>
                                    @endif

                                    <!-- Dokumen & Link Bukti -->
                                    <div class="d-flex align-items-center flex-wrap gap-2 mt-1">
                                        @if(!empty($t['file_url']))
                                            <a href="{{ $t['file_url'] }}" target="_blank" 
                                               class="badge bg-light text-success border border-success-subtle text-decoration-none py-1 px-2 d-inline-flex align-items-center gap-1"
                                               style="font-size: 0.73rem;">
                                                <i class="bi bi-paperclip"></i>
                                                <span>{{ $t['file_name'] ?: 'Bukti Dokumen' }}</span>
                                            </a>
                                        @endif

                                        @if(!empty($t['url_external']))
                                            <a href="{{ $t['url_external'] }}" target="_blank" 
                                               class="badge bg-light text-primary border border-primary-subtle text-decoration-none py-1 px-2 d-inline-flex align-items-center gap-1"
                                               style="font-size: 0.73rem;">
                                                <i class="bi bi-link-45deg"></i>
                                                <span>Bukti Tautan</span>
                                            </a>
                                        @endif

                                        @if(!$t['is_owner'])
                                            <span class="badge bg-light text-secondary border py-1 px-2" style="font-size: 0.72rem;">
                                                <i class="bi bi-info-circle me-1"></i>Ditandai (CC) · Pembuat: {{ $t['pemilik_nama'] }}
                                            </span>
                                        @endif
                                    </div>

                                    @if(!empty($t['catatan_validasi']))
                                        <div class="alert alert-warning py-1 px-2.5 mt-1.5 mb-0 small d-inline-block rounded-2" style="font-size: 0.78rem;">
                                            <i class="bi bi-chat-left-dots-fill me-1"></i>
                                            <strong>Catatan Atasan:</strong> {{ $t['catatan_validasi'] }}
                                        </div>
                                    @endif
                                </td>

                                <!-- Jenis Kegiatan -->
                                <td class="text-center align-middle px-2">
                                    @php
                                        $katLower = strtolower($t['kategori']);
                                    @endphp
                                    @if(str_contains($katLower, 'rutin') || str_contains($katLower, 'rencana'))
                                        <span class="badge bg-light text-dark border px-2 py-1 small fw-normal">
                                            <i class="bi bi-circle-fill text-success me-1" style="font-size: 6px; vertical-align: middle;"></i>Rutin
                                        </span>
                                    @elseif(str_contains($katLower, 'panitia'))
                                        <span class="badge bg-light text-dark border px-2 py-1 small fw-normal">
                                            <i class="bi bi-circle-fill text-info me-1" style="font-size: 6px; vertical-align: middle;"></i>Panitia
                                        </span>
                                    @else
                                        <span class="badge bg-light text-dark border px-2 py-1 small fw-normal">
                                            <i class="bi bi-circle-fill text-warning me-1" style="font-size: 6px; vertical-align: middle;"></i>Insidental
                                        </span>
                                    @endif
                                </td>

                                <!-- Status Badge -->
                                <td class="text-center align-middle px-2 status-cell">
                                    @if($isApproved)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 fw-bold" style="font-size: 0.78rem;">
                                            <i class="bi bi-check2-circle me-1"></i>Terverifikasi
                                        </span>
                                    @elseif($isPending)
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning px-2.5 py-1 fw-bold" style="font-size: 0.78rem;">
                                            <i class="bi bi-hourglass-split me-1"></i>Menunggu
                                        </span>
                                    @elseif($isRevisi)
                                        <span class="badge bg-danger-subtle text-danger border border-danger px-2.5 py-1 fw-bold" style="font-size: 0.78rem;">
                                            <i class="bi bi-arrow-repeat me-1"></i>Revisi
                                        </span>
                                    @elseif($isRejected)
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary px-2.5 py-1 fw-bold" style="font-size: 0.78rem;">
                                            <i class="bi bi-x-circle me-1"></i>Ditolak
                                        </span>
                                    @elseif($isRunning)
                                        <span class="badge bg-primary-subtle text-primary border border-primary px-2.5 py-1 fw-bold" style="font-size: 0.78rem;">
                                            <i class="bi bi-play-circle me-1"></i>Berjalan
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted border px-2.5 py-1 fw-medium" style="font-size: 0.78rem;">
                                            Draft
                                        </span>
                                    @endif
                                </td>

                                <!-- Aksi Validasi Atasan -->
                                <td class="text-center align-middle px-2 action-cell">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <!-- Tombol Preview Detail -->
                                        <button type="button" class="btn btn-sm btn-light border px-2 py-1 btn-view-task-detail" 
                                                data-id="{{ $t['id'] }}" title="Lihat Rincian Tugas">
                                            <i class="bi bi-eye text-primary"></i>
                                        </button>

                                        @if($t['can_validate'])
                                            @if(!$isApproved)
                                                <!-- Tombol Setujui -->
                                                <button type="button" class="btn btn-sm btn-success px-2 py-1 btn-action-approve" 
                                                        data-id="{{ $t['id'] }}" data-title="{{ $t['uraian_tugas'] }}" title="Setujui Tugas">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>

                                                <!-- Tombol Minta Revisi -->
                                                <button type="button" class="btn btn-sm btn-outline-warning px-2 py-1 btn-action-revise" 
                                                        data-id="{{ $t['id'] }}" data-title="{{ $t['uraian_tugas'] }}" title="Minta Revisi">
                                                    <i class="bi bi-arrow-repeat"></i>
                                                </button>

                                                <!-- Tombol Tolak -->
                                                <button type="button" class="btn btn-sm btn-outline-danger px-2 py-1 btn-action-reject" 
                                                        data-id="{{ $t['id'] }}" data-title="{{ $t['uraian_tugas'] }}" title="Tolak Tugas">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            @else
                                                <span class="text-success small fw-medium" title="Telah Disetujui">
                                                    <i class="bi bi-check-all fs-6"></i>
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-calendar-x display-5 d-block mb-3 text-secondary opacity-50"></i>
                                    <h6 class="fw-bold">Belum ada catatan kegiatan</h6>
                                    <p class="small mb-0">Pegawai ini belum memiliki tugas/kegiatan yang tercatat pada periode ini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Rincian Pekerjaan (AJAX Loaded) -->
<div class="modal fade" id="modalTaskDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white py-2.5 px-3">
                <h6 class="modal-title fw-bold" id="modalTaskDetailTitle"><i class="bi bi-info-circle me-1.5"></i>Rincian Kegiatan</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3" id="modalTaskDetailBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-success" role="status"></div>
                    <div class="small text-muted mt-2">Memuat rincian kegiatan...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Input Catatan Evaluasi (Revisi / Tolak) -->
<div class="modal fade" id="modalEvaluasiNote" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header py-2.5 px-3" id="modalEvaluasiHeader">
                <h6 class="modal-title fw-bold" id="modalEvaluasiTitle">Catatan Evaluasi Atasan</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <p class="small text-muted mb-2" id="modalEvaluasiPrompt">Tuliskan rincian poin evaluasi atau alasan perbaikan:</p>
                <div class="fw-bold text-dark mb-2 small" id="modalEvaluasiTaskName"></div>
                <textarea id="evaluasiCatatanInput" class="form-control form-control-sm" rows="4" 
                          placeholder="Tuliskan arahan perbaikan atau catatan revisi di sini..." required></textarea>
                <div class="invalid-feedback" id="evaluasiFeedback">Catatan wajib diisi.</div>
            </div>
            <div class="modal-footer py-2 px-3">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-sm text-white fw-semibold" id="btnSubmitEvaluasi">Kirim Evaluasi</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
    const checkAll = document.getElementById('checkAllMaster');
    const taskCheckboxes = document.querySelectorAll('.check-task-item:not([disabled])');
    const btnBatchApprove = document.getElementById('btnBatchApprove');
    const selectedCountBadge = document.getElementById('selectedCountBadge');

    // ─────────────────────────────────────────────────────────────────────────
    // Checkbox Master & Batch Selection
    // ─────────────────────────────────────────────────────────────────────────
    function updateBatchButton() {
        const checkedBoxes = document.querySelectorAll('.check-task-item:checked');
        const count = checkedBoxes.length;
        if (selectedCountBadge) selectedCountBadge.textContent = count;
        if (btnBatchApprove) btnBatchApprove.disabled = (count === 0);
    }

    if (checkAll) {
        checkAll.addEventListener('change', function () {
            taskCheckboxes.forEach(cb => {
                cb.checked = this.checked;
            });
            updateBatchButton();
        });
    }

    taskCheckboxes.forEach(cb => {
        cb.addEventListener('change', function () {
            const allChecked = Array.from(taskCheckboxes).every(c => c.checked);
            if (checkAll) checkAll.checked = allChecked;
            updateBatchButton();
        });
    });

    // ─────────────────────────────────────────────────────────────────────────
    // Batch Approve
    // ─────────────────────────────────────────────────────────────────────────
    if (btnBatchApprove) {
        btnBatchApprove.addEventListener('click', function () {
            const checkedBoxes = document.querySelectorAll('.check-task-item:checked');
            const ids = Array.from(checkedBoxes).map(cb => parseInt(cb.value));

            if (ids.length === 0) return;

            Swal.fire({
                title: 'Setujui ' + ids.length + ' Tugas?',
                text: 'Tugas yang dipilih akan divalidasi dan disetujui sebagai atasan.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#046B26',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Setujui Semua',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    btnBatchApprove.disabled = true;
                    btnBatchApprove.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Memproses...';

                    fetch("{{ route('pemantauan-staff.batch-validate') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            ids: ids,
                            action: 'approve'
                        })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: data.message || 'Semua tugas terpilih berhasil disetujui!',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('Gagal', data.message || 'Gagal memproses validasi tugas.', 'error');
                            btnBatchApprove.disabled = false;
                            updateBatchButton();
                        }
                    })
                    .catch(err => {
                        Swal.fire('Terjadi Kesalahan', 'Gagal menghubungi server.', 'error');
                        btnBatchApprove.disabled = false;
                        updateBatchButton();
                    });
                }
            });
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Quick Single Validate: Approve
    // ─────────────────────────────────────────────────────────────────────────
    document.querySelectorAll('.btn-action-approve').forEach(btn => {
        btn.addEventListener('click', function () {
            const taskId = this.getAttribute('data-id');
            const taskTitle = this.getAttribute('data-title');

            Swal.fire({
                title: 'Setujui Pekerjaan?',
                html: `<div class="small text-muted mb-2">Anda akan menyetujui tugas:</div><div class="fw-bold text-dark">${taskTitle}</div>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#046B26',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-check-lg me-1"></i>Ya, Setujui',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    executeValidate(taskId, 'approve', '');
                }
            });
        });
    });

    // ─────────────────────────────────────────────────────────────────────────
    // Quick Single Validate: Revise / Reject with Modal Note
    // ─────────────────────────────────────────────────────────────────────────
    let activeTaskId = null;
    let activeAction = null;
    const modalEvaluasi = new bootstrap.Modal(document.getElementById('modalEvaluasiNote'));
    const modalHeader = document.getElementById('modalEvaluasiHeader');
    const modalTitle = document.getElementById('modalEvaluasiTitle');
    const taskNameEl = document.getElementById('modalEvaluasiTaskName');
    const catatanInput = document.getElementById('evaluasiCatatanInput');
    const btnSubmit = document.getElementById('btnSubmitEvaluasi');

    document.querySelectorAll('.btn-action-revise').forEach(btn => {
        btn.addEventListener('click', function () {
            activeTaskId = this.getAttribute('data-id');
            activeAction = 'revise';
            modalHeader.className = 'modal-header py-2.5 px-3 bg-warning text-dark';
            modalTitle.innerHTML = '<i class="bi bi-arrow-repeat me-1.5"></i>Permintaan Revisi Pekerjaan';
            taskNameEl.textContent = this.getAttribute('data-title');
            catatanInput.value = '';
            catatanInput.placeholder = 'Tuliskan poin atau bagian yang perlu direvisi/diperbaiki...';
            btnSubmit.className = 'btn btn-sm btn-warning text-dark fw-bold';
            btnSubmit.textContent = 'Kirim Revisi';
            modalEvaluasi.show();
        });
    });

    document.querySelectorAll('.btn-action-reject').forEach(btn => {
        btn.addEventListener('click', function () {
            activeTaskId = this.getAttribute('data-id');
            activeAction = 'reject';
            modalHeader.className = 'modal-header py-2.5 px-3 bg-danger text-white';
            modalTitle.innerHTML = '<i class="bi bi-x-circle me-1.5"></i>Tolak Pekerjaan';
            taskNameEl.textContent = this.getAttribute('data-title');
            catatanInput.value = '';
            catatanInput.placeholder = 'Tuliskan alasan penolakan pekerjaan ini...';
            btnSubmit.className = 'btn btn-sm btn-danger text-white fw-bold';
            btnSubmit.textContent = 'Tolak Pekerjaan';
            modalEvaluasi.show();
        });
    });

    btnSubmit.addEventListener('click', function () {
        const note = catatanInput.value.trim();
        if (!note) {
            catatanInput.classList.add('is-invalid');
            return;
        }
        catatanInput.classList.remove('is-invalid');
        modalEvaluasi.hide();
        executeValidate(activeTaskId, activeAction, note);
    });

    function executeValidate(taskId, action, catatan) {
        Swal.fire({
            title: 'Memproses Validasi...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const targetUrl = `{{ url('pemantauan-staff/validate-task') }}/${taskId}`;

        fetch(targetUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                action: action,
                catatan: catatan
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: data.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire('Gagal', data.message || 'Gagal memproses validasi.', 'error');
            }
        })
        .catch(err => {
            Swal.fire('Error', 'Terjadi kesalahan sistem saat memproses validasi.', 'error');
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // View Task Detail Modal
    // ─────────────────────────────────────────────────────────────────────────
    const modalDetailEl = new bootstrap.Modal(document.getElementById('modalTaskDetail'));
    const modalBody = document.getElementById('modalTaskDetailBody');

    document.querySelectorAll('.btn-view-task-detail').forEach(btn => {
        btn.addEventListener('click', function () {
            const taskId = this.getAttribute('data-id');
            modalBody.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-success mb-2" role="status"></div>
                    <div class="small text-muted fw-semibold">Memuat rincian kegiatan...</div>
                </div>
            `;
            modalDetailEl.show();

            const url = `{{ url('rencana-kerja') }}/${taskId}/modal-detail`;

            fetch(url, {
                headers: {
                    'Accept': 'text/html, application/xhtml+xml',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => {
                if (!res.ok) throw new Error('Network response not ok');
                return res.text();
            })
            .then(html => {
                modalBody.innerHTML = html;
            })
            .catch(err => {
                modalBody.innerHTML = `
                    <div class="alert alert-danger py-3 small text-center mb-0">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        Gagal memuat rincian pekerjaan. Silakan coba lagi.
                    </div>
                `;
            });
        });
    });
});
</script>
@endsection
