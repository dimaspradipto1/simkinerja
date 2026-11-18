<div class="row g-3 mb-4" id="kpiCardsRow">
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm rounded-4 h-100 card-kpi" style="border-left: 4px solid #15432D !important;">
            <div class="card-body p-3.5 d-flex align-items-center gap-3">
                <div class="icon-circle bg-success-subtle text-success rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; min-width: 48px;">
                    <i class="bi bi-diagram-3-fill fs-4" style="color: #15432D !important;"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem;">{{ ($isLeader ?? false) ? 'Anggota Tim (Sesuai OTK)' : 'Total Pegawai' }}</div>
                    <div class="fs-4 fw-bold text-dark mb-0">{{ number_format($kpi['total_staff']) }} <span class="fs-6 fw-normal text-muted">{{ ($isLeader ?? false) ? 'Orang' : 'Pegawai' }}</span></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm rounded-4 h-100 card-kpi" style="border-left: 4px solid #0d6efd !important;">
            <div class="card-body p-3.5 d-flex align-items-center gap-3">
                <div class="icon-circle bg-primary-subtle text-primary rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; min-width: 48px;">
                    <i class="bi bi-person-check fs-4"></i>
                </div>
                <div class="w-100">
                    <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem;">Aktif Mencatat ({{ $namaBulanIndonesia }})</div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fs-4 fw-bold text-dark">{{ $kpi['total_aktif'] }}</span>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.75rem;">{{ $kpi['aktif_percentage'] }}% keaktifan</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm rounded-4 h-100 card-kpi" style="border-left: 4px solid #f59e0b !important;">
            <div class="card-body p-3.5 d-flex align-items-center gap-3">
                <div class="icon-circle bg-warning-subtle text-warning rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; min-width: 48px;">
                    <i class="bi bi-journal-bookmark-fill fs-4" style="color: #b45309 !important;"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem;">Kegiatan Bulan Ini</div>
                    <div class="fs-4 fw-bold text-dark mb-0">{{ number_format($kpi['total_kegiatan_bulan']) }} <span class="fs-6 fw-normal text-muted">Kegiatan</span></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm rounded-4 h-100 card-kpi" style="border-left: 4px solid #10b981 !important;">
            <div class="card-body p-3.5 d-flex align-items-center gap-3">
                <div class="icon-circle bg-emerald-subtle rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; min-width: 48px; background: #e6f9f0; color: #047857;">
                    <i class="bi bi-clock-history fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem;">Akumulasi Jam Kerja</div>
                    <div class="fs-4 fw-bold text-dark mb-0">{{ number_format($kpi['total_jam_kerja_bulan'], 1, ',', '.') }} <span class="fs-6 fw-normal text-muted">Jam</span></div>
                    <small class="text-muted" style="font-size: 0.72rem;">Rata-rata: {{ number_format($kpi['avg_jam_kerja'], 1, ',', '.') }} jam/staf</small>
                </div>
            </div>
        </div>
    </div>
</div>
