<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5" id="tableCardContainer">
    <div class="card-header bg-white py-3 px-4 border-0 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <h5 class="fw-bold mb-0 text-dark" style="font-size: 1.05rem;">
                {{ ($isLeader ?? false) ? 'Daftar Kinerja Anggota Tim Penempatan OTK (2024 - 2029)' : 'Kinerja Individu Penempatan OTK (2024 - 2029)' }}
            </h5>
            <small class="text-muted">Periode Pemantauan: <strong class="text-dark">{{ $namaBulanIndonesia }} {{ $tahun }}</strong> &bull; Diurutkan Berdasarkan Penempatan Resmi OTK</small>
        </div>
        <span class="badge bg-light text-dark border px-3 py-1.5 rounded-pill fw-semibold" style="font-size: 0.8rem;">
            {{ count($pegawaiData) }} {{ ($isLeader ?? false) ? 'Anggota Terdaftar' : 'Pegawai' }}
        </span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 pemantauan-table" style="font-size: 0.84rem;">
            <thead>
                <!-- Row 1: Header Grup Sesuai Standar OTK -->
                <tr class="text-white text-center align-middle" style="background: linear-gradient(135deg, #15432D 0%, #1e5c3e 100%);">
                    <th rowspan="2" class="text-center py-3" style="width: 55px; border-bottom: none;">No</th>
                    <th rowspan="2" class="text-start py-3" style="min-width: 280px; border-bottom: none;">Pegawai</th>
                    <th colspan="2" class="text-center py-2" style="border-left: 1px solid rgba(255,255,255,0.15); border-right: 1px solid rgba(255,255,255,0.15); background: rgba(0,0,0,0.1);">
                        Bulan {{ $namaBulanIndonesia }} {{ $tahun }}
                    </th>
                    <th colspan="4" class="text-center py-2" style="border-right: 1px solid rgba(255,255,255,0.15); background: rgba(0,0,0,0.05);">
                        Tahun Kinerja Berjalan ({{ $tahun }})
                    </th>
                    <th rowspan="2" class="text-center py-3" style="width: 140px; border-bottom: none;">Aksi</th>
                </tr>
                <!-- Row 2: Sub-Header Kolom -->
                <tr class="text-white text-center align-middle" style="background: #15432D; font-size: 0.77rem;">
                    <th class="py-2" style="border-left: 1px solid rgba(255,255,255,0.15); min-width: 105px;">Jum. Kegiatan</th>
                    <th class="py-2" style="border-right: 1px solid rgba(255,255,255,0.15); min-width: 110px;">Jum. Jam Kerja</th>
                    <th class="py-2" style="min-width: 75px;">Total</th>
                    <th class="py-2" style="min-width: 85px;">Rutin</th>
                    <th class="py-2" style="min-width: 95px;">Kepanitiaan</th>
                    <th class="py-2" style="border-right: 1px solid rgba(255,255,255,0.15); min-width: 85px;">Insidental</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pegawaiData as $index => $row)
                    @php
                        $pegawai = $row['pegawai'];
                        $hasActivities = $row['jum_kegiatan_bulan'] > 0;
                    @endphp
                    <tr class="pegawai-main-row" id="row-pegawai-{{ $pegawai->id }}">
                        <!-- No Urut OTK -->
                        <td class="text-center text-muted fw-bold">
                            {{ $loop->iteration }}
                        </td>

                        <!-- Profil Pegawai & Jabatan OTK -->
                        <td>
                            <div class="d-flex align-items-center">
                                <!-- Avatar Circle -->
                                <div class="avatar-circle rounded-circle text-white d-flex align-items-center justify-content-center fw-bold shadow-xs flex-shrink-0 me-3" 
                                     style="width: 38px; height: 38px; min-width: 38px; font-size: 12.5px; background-color: {{ $row['avatar_color'] }}; margin-right: 14px !important;">
                                    {{ $row['avatar_initial'] }}
                                </div>
                                <div class="lh-sm">
                                    <div class="fw-bold text-dark">
                                        {{ $pegawai->name }}
                                    </div>
                                    <div class="text-dark small fw-semibold" style="font-size: 0.77rem; color: #15432D !important;">
                                        {{ $pegawai->jabatan ?: 'Staf' }}
                                    </div>
                                    <div class="text-muted" style="font-size: 0.70rem;">
                                        {{ $pegawai->unit ?: 'Universitas Ibnu Sina' }} &bull; NUPTK/NUP: {{ $pegawai->nidn ?: '-' }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- Bulan Ini: Jum Kegiatan -->
                        <td class="text-center">
                            @if($hasActivities)
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1.5 fw-bold" style="font-size: 0.82rem;">
                                    {{ $row['jum_kegiatan_bulan'] }}
                                </span>
                            @else
                                <span class="text-muted fw-medium small">0</span>
                            @endif
                        </td>

                        <!-- Bulan Ini: Jum Jam Kerja -->
                        <td class="text-center">
                            @if($row['jam_kerja_bulan'] > 0)
                                <span class="badge bg-light text-dark border px-2.5 py-1.5 fw-bold" style="font-size: 0.82rem;">
                                    <i class="bi bi-clock me-1 text-primary"></i>{{ number_format($row['jam_kerja_bulan'], 1, ',', '.') }}
                                </span>
                            @else
                                <span class="text-muted small">0</span>
                            @endif
                        </td>

                        <!-- Tahun Berjalan: Total -->
                        <td class="text-center fw-bold text-dark">
                            {{ $row['total_tahunan'] }}
                        </td>

                        <!-- Tahun Berjalan: Rutin / Utama -->
                        <td class="text-center text-secondary fw-semibold">
                            {{ $row['rutin_tahunan'] }}
                        </td>

                        <!-- Tahun Berjalan: Kepanitiaan -->
                        <td class="text-center text-secondary fw-semibold">
                            {{ $row['kepanitiaan_tahunan'] }}
                        </td>

                        <!-- Tahun Berjalan: Insidental -->
                        <td class="text-center text-secondary fw-semibold">
                            {{ $row['insidentil_tahunan'] }}
                        </td>

                        <!-- Aksi: Tombol Menuju Halaman Detail & Validasi -->
                        <td class="text-center">
                            <a href="{{ route('pemantauan-staff.detail', ['id' => $pegawai->id, 'bulan' => $bulan, 'tahun' => $tahun]) }}" 
                               class="btn btn-sm text-white d-inline-flex align-items-center gap-1.5 px-2.5 py-1.5 rounded-2 shadow-xs text-decoration-none" 
                               style="font-size: 0.78rem; background-color: #046B26; border-color: #046B26; white-space: nowrap;"
                               title="Buka halaman rincian pekerjaan & validasi {{ $pegawai->name }}">
                                <i class="bi bi-journal-text"></i>
                                <span class="btn-text">Rincian &amp; Validasi</span>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi bi-diagram-3 display-4 d-block mb-3 text-secondary"></i>
                                <h6 class="fw-bold">Tidak ada staf dalam penempatan OTK ini</h6>
                                <p class="small mb-0">Coba ubah filter atasan, bidang koordinasi, atau kata kunci pencarian.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
