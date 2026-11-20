<div class="row g-3 mb-4" id="kpiCardsRow">
    <!-- Card 1: Anggota Tim -->
    <div class="col-xl-3 col-md-6">
        <div class="card border border-1 shadow-xs rounded-3 h-100 card-kpi bg-white" style="border-color: #e2e8f0 !important;">
            <div class="card-body p-3.5 d-flex align-items-center gap-3">
                <div class="icon-circle rounded-3 d-flex align-items-center justify-content-center shadow-2xs flex-shrink-0" 
                     style="width: 48px; height: 48px; min-width: 48px; background: #ecfdf5; color: #046B26;">
                    <i class="bi bi-people-fill fs-4"></i>
                </div>
                <div class="overflow-hidden">
                    <div class="text-muted fw-bold text-uppercase text-truncate" style="letter-spacing: 0.5px; font-size: 0.72rem; color: #64748b !important;">
                        {{ ($isLeader ?? false) ? 'Anggota Tim (Sesuai OTK)' : 'Total Pegawai' }}
                    </div>
                    <div class="d-flex align-items-baseline gap-1.5 mt-0.5">
                        <span class="fs-4 fw-bold text-dark" style="line-height: 1.2;">{{ number_format($kpi['total_staff']) }}</span>
                        <span class="small fw-semibold text-muted">{{ ($isLeader ?? false) ? 'Orang' : 'Pegawai' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 2: Aktif Mencatat -->
    <div class="col-xl-3 col-md-6">
        <div class="card border border-1 shadow-xs rounded-3 h-100 card-kpi bg-white" style="border-color: #e2e8f0 !important;">
            <div class="card-body p-3.5 d-flex align-items-center gap-3">
                <div class="icon-circle rounded-3 d-flex align-items-center justify-content-center shadow-2xs flex-shrink-0" 
                     style="width: 48px; height: 48px; min-width: 48px; background: #eff6ff; color: #2563eb;">
                    <i class="bi bi-person-check-fill fs-4"></i>
                </div>
                <div class="overflow-hidden w-100">
                    <div class="text-muted fw-bold text-uppercase text-truncate" style="letter-spacing: 0.5px; font-size: 0.72rem; color: #64748b !important;">
                        Aktif Mencatat ({{ $namaBulanIndonesia }})
                    </div>
                    <div class="d-flex align-items-center gap-2 mt-0.5">
                        <span class="fs-4 fw-bold text-dark" style="line-height: 1.2;">{{ $kpi['total_aktif'] }}</span>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-0.5" style="font-size: 0.72rem; font-weight: 600;">
                            {{ $kpi['aktif_percentage'] }}% keaktifan
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 3: Kegiatan Bulan Ini -->
    <div class="col-xl-3 col-md-6">
        <div class="card border border-1 shadow-xs rounded-3 h-100 card-kpi bg-white" style="border-color: #e2e8f0 !important;">
            <div class="card-body p-3.5 d-flex align-items-center gap-3">
                <div class="icon-circle rounded-3 d-flex align-items-center justify-content-center shadow-2xs flex-shrink-0" 
                     style="width: 48px; height: 48px; min-width: 48px; background: #fffbeb; color: #d97706;">
                    <i class="bi bi-journal-text fs-4"></i>
                </div>
                <div class="overflow-hidden">
                    <div class="text-muted fw-bold text-uppercase text-truncate" style="letter-spacing: 0.5px; font-size: 0.72rem; color: #64748b !important;">
                        Kegiatan Bulan Ini
                    </div>
                    <div class="d-flex align-items-baseline gap-1.5 mt-0.5">
                        <span class="fs-4 fw-bold text-dark" style="line-height: 1.2;">{{ number_format($kpi['total_kegiatan_bulan']) }}</span>
                        <span class="small fw-semibold text-muted">Kegiatan</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 4: Akumulasi Jam Kerja -->
    <div class="col-xl-3 col-md-6">
        <div class="card border border-1 shadow-xs rounded-3 h-100 card-kpi bg-white" style="border-color: #e2e8f0 !important;">
            <div class="card-body p-3.5 d-flex align-items-center gap-3">
                <div class="icon-circle rounded-3 d-flex align-items-center justify-content-center shadow-2xs flex-shrink-0" 
                     style="width: 48px; height: 48px; min-width: 48px; background: #f0fdf4; color: #15803d;">
                    <i class="bi bi-clock-history fs-4"></i>
                </div>
                <div class="overflow-hidden">
                    <div class="text-muted fw-bold text-uppercase text-truncate" style="letter-spacing: 0.5px; font-size: 0.72rem; color: #64748b !important;">
                        Akumulasi Jam Kerja
                    </div>
                    <div class="d-flex align-items-baseline gap-1.5 mt-0.5">
                        <span class="fs-4 fw-bold text-dark" style="line-height: 1.2;">{{ number_format($kpi['total_jam_kerja_bulan'], 1, ',', '.') }}</span>
                        <span class="small fw-semibold text-muted">Jam</span>
                    </div>
                    <div class="text-muted small mt-0.5" style="font-size: 0.70rem;">
                        Rata-rata: <strong class="text-dark">{{ number_format($kpi['avg_jam_kerja'], 1, ',', '.') }} jam</strong>/staf
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
