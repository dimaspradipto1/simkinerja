@php
    $estFormatted = '-';
    if ($row->estimasi_jam_mulai || $row->estimasi_tanggal_mulai) {
        $tglEstMulai = $row->estimasi_tanggal_mulai ? date('d/m/Y', strtotime($row->estimasi_tanggal_mulai)) : '';
        $tglEstSelesai = $row->estimasi_tanggal_selesai ? date('d/m/Y', strtotime($row->estimasi_tanggal_selesai)) : '';
        $jamEstMulai = $row->estimasi_jam_mulai ? substr($row->estimasi_jam_mulai, 0, 5) : '';
        $jamEstSelesai = $row->estimasi_jam_selesai ? substr($row->estimasi_jam_selesai, 0, 5) : '';

        if ($tglEstMulai && $tglEstSelesai && $tglEstMulai === $tglEstSelesai) {
            $jamPart = '';
            if ($jamEstMulai && $jamEstSelesai) {
                $jamPart = ' (' . $jamEstMulai . ' - ' . $jamEstSelesai . ' WIB)';
            } elseif ($jamEstMulai) {
                $jamPart = ' (' . $jamEstMulai . ' WIB)';
            } elseif ($jamEstSelesai) {
                $jamPart = ' (' . $jamEstSelesai . ' WIB)';
            }
            $estFormatted = $tglEstMulai . $jamPart;
        } else {
            $startPart = trim($tglEstMulai . ($jamEstMulai ? ' (' . $jamEstMulai . ' WIB)' : ''));
            $endPart = trim($tglEstSelesai . ($jamEstSelesai ? ' (' . $jamEstSelesai . ' WIB)' : ''));
            $estFormatted = trim($startPart . ($endPart ? ' - ' . $endPart : ''));
        }
    }

    $mulaiFormatted = '-';
    if ($row->waktu_mulai && $row->waktu_mulai !== '00:00:00') {
        $tglMulai = $row->tanggal_mulai ? date('d/m/Y', strtotime($row->tanggal_mulai)) : '';
        $jamMulai = substr($row->waktu_mulai, 0, 5);
        $mulaiFormatted = $tglMulai ? ($tglMulai . ' (' . $jamMulai . ' WIB)') : '(' . $jamMulai . ' WIB)';
    }

    $selesaiFormatted = '-';
    if ($row->waktu_selesai && $row->waktu_selesai !== '00:00:00') {
        $tglSelesai = $row->tanggal_selesai ? date('d/m/Y', strtotime($row->tanggal_selesai)) : '';
        $jamSelesai = substr($row->waktu_selesai, 0, 5);
        $selesaiFormatted = $tglSelesai ? ($tglSelesai . ' (' . $jamSelesai . ' WIB)') : '(' . $jamSelesai . ' WIB)';
    }

    $durasiStr = '-';
    $durasiDetik = \App\Helpers\KinerjaWaktuHelper::hitungDetikRealisasi($row);
    if ($durasiDetik > 0) {
        $days = floor($durasiDetik / 86400);
        $hours = floor(($durasiDetik % 86400) / 3600);
        $minutes = floor(($durasiDetik % 3600) / 60);
        $seconds = $durasiDetik % 60;

        $durasiParts = [];
        if ($days > 0) $durasiParts[] = $days . 'h';
        if ($hours > 0) $durasiParts[] = $hours . 'j';
        if ($minutes > 0) $durasiParts[] = $minutes . 'm';
        if ($seconds > 0 || empty($durasiParts)) $durasiParts[] = $seconds . 's';

        $durasiStr = implode(' ', $durasiParts) . ' (' . str_replace('.', ',', (string)round($durasiDetik / 3600, 1)) . ' jam)';
    }

    $status = $row->status ?? 'Belum Dimulai';
    $waktuMulai = !empty($row->waktu_mulai) && $row->waktu_mulai !== '00:00:00';
    $waktuSelesai = !empty($row->waktu_selesai) && $row->waktu_selesai !== '00:00:00';
    $authUser = $authUser ?? auth()->user();
    $canValidate = $authUser && $authUser->canValidateTask($row);
@endphp

<div class="d-flex flex-column gap-3">
    <!-- 1. Header Tugas -->
    <div class="p-3 bg-light rounded-3 border shadow-2xs">
        <div class="d-flex align-items-start gap-2.5 mb-2">
            <span class="p-2 rounded-circle bg-success-subtle text-success flex-shrink-0 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                <i class="bi bi-card-checklist fs-5"></i>
            </span>
            <div class="flex-grow-1">
                <h5 class="fw-bold text-dark mb-1" style="font-size: 1.05rem; line-height: 1.4;">{{ $row->uraian_tugas }}</h5>
                <div class="d-flex flex-wrap align-items-center gap-2 mt-1">
                    @if($row->kategori)
                        <span class="badge px-2 py-1" style="background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a; font-weight: 600; font-size: 0.76rem;">
                            <i class="bi bi-tag-fill me-1"></i>{{ $row->kategori }}
                        </span>
                    @endif
                    @if($row->periodeAkademik)
                        <span class="badge px-2 py-1" style="background-color: #e8f5e9; color: #1b5e20; border: 1px solid #c8e6c9; font-weight: 600; font-size: 0.76rem;">
                            <i class="bi bi-mortarboard-fill me-1"></i>{{ $row->periodeAkademik->formatted_ta ?? ('T.A. ' . $row->periodeAkademik->nama_periode) }}
                        </span>
                    @endif
                    @if($row->hari)
                        <span class="badge px-2 py-1" style="background-color: #eef2ff; color: #3730a3; border: 1px solid #c7d2fe; font-weight: 600; font-size: 0.76rem;">
                            <i class="bi bi-calendar-day me-1"></i>{{ $row->hari }}
                        </span>
                    @endif
                    @if($status === 'Disetujui' || $status === 'Selesai')
                        <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.76rem; background-color: #ecfdf5 !important; color: #065f46 !important; border: 1px solid #a7f3d0 !important;">
                            <i class="bi bi-patch-check-fill me-1" style="color: #059669 !important;"></i>Disetujui
                        </span>
                    @elseif($status === 'Menunggu Validasi')
                        <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.76rem; background-color: #fffbeb !important; color: #b45309 !important; border: 1px solid #fde68a !important;">
                            <i class="bi bi-clock-history me-1" style="color: #d97706 !important;"></i>Menunggu Validasi
                        </span>
                    @elseif($status === 'Perlu Revisi')
                        <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.76rem; background-color: #fff7ed !important; color: #c2410c !important; border: 1px solid #fed7aa !important;">
                            <i class="bi bi-exclamation-circle-fill me-1" style="color: #ea580c !important;"></i>Perlu Revisi
                        </span>
                    @elseif($status === 'Ditolak')
                        <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.76rem; background-color: #fef2f2 !important; color: #991b1b !important; border: 1px solid #fecaca !important;">
                            <i class="bi bi-x-circle-fill me-1" style="color: #dc2626 !important;"></i>Ditolak
                        </span>
                    @elseif($status === 'Proses' || $status === 'Berjalan' || ($waktuMulai && !$waktuSelesai))
                        <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.76rem; background-color: #eff6ff !important; color: #1d4ed8 !important; border: 1px solid #bfdbfe !important;">
                            <i class="bi bi-play-circle-fill me-1" style="color: #2563eb !important;"></i>Berjalan
                        </span>
                    @elseif($status === 'Di-pause')
                        <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.76rem; background-color: #fef2f2 !important; color: #991b1b !important; border: 1px solid #fecaca !important;">
                            <i class="bi bi-pause-circle-fill me-1" style="color: #dc2626 !important;"></i>Di-pause
                        </span>
                    @else
                        <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.76rem; background-color: #f8fafc !important; color: #475569 !important; border: 1px solid #cbd5e1 !important;">
                            <i class="bi bi-dash-circle me-1" style="color: #64748b !important;"></i>Belum Dimulai
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Status Validation Feedback Banner -->
    @if($status === 'Perlu Revisi')
        <div class="p-3 rounded-3 border" style="background-color: #fff7ed; border-color: #fed7aa !important;">
            <div class="d-flex align-items-start gap-2.5">
                <span class="p-2 rounded-circle bg-warning-subtle text-warning flex-shrink-0 d-inline-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                    <i class="bi bi-exclamation-triangle-fill text-warning fs-5"></i>
                </span>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
                        <span class="fw-bold text-dark" style="font-size: 0.92rem;">Perlu Revisi dari Atasan</span>
                        @if($row->validator)
                            <span class="text-muted small">Oleh: <strong>{{ $row->validator->name }}</strong> ({{ $row->validated_at ? date('d/m/Y H:i', strtotime($row->validated_at)) : '-' }} WIB)</span>
                        @endif
                    </div>
                    <div class="p-2.5 rounded bg-white border border-warning-subtle small text-dark mb-2">
                        <strong class="text-warning-emphasis d-block mb-1"><i class="bi bi-chat-left-dots-fill me-1"></i>Catatan Revisi Atasan:</strong>
                        {{ $row->catatan_validasi ?: 'Mohon periksa kembali kelengkapan hasil kerja dan berkas pendukung.' }}
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <button type="button" class="btn btn-sm btn-success fw-bold text-white d-inline-flex align-items-center" onclick="window.resubmitValidationQuick({{ $row->id }})" style="background-color: #046B26; border-color: #046B26;">
                            <i class="bi bi-send-check-fill me-1.5"></i> Ajukan Ulang Validasi
                        </button>
                        <span class="text-muted small fst-italic">Perbarui hasil kerja atau berkas di bawah sebelum mengajukan ulang.</span>
                    </div>
                </div>
            </div>
        </div>
    @elseif($status === 'Disetujui' || $status === 'Selesai')
        <div class="p-3 rounded-3 border" style="background-color: #f0fdf4; border-color: #bbf7d0 !important;">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-patch-check-fill text-success fs-5"></i>
                    <div>
                        <span class="fw-bold text-success" style="font-size: 0.9rem;">Tugas Telah Divalidasi & Disetujui</span>
                        <div class="text-muted small">
                            Divalidasi oleh: <strong>{{ $row->validator->name ?? 'Atasan / Pimpinan' }}</strong>
                            @if($row->validated_at)
                                pada {{ date('d/m/Y H:i', strtotime($row->validated_at)) }} WIB
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @if(!empty($row->catatan_validasi))
                <div class="w-100 mt-2 p-2 rounded bg-white border border-success-subtle small text-dark">
                    <strong class="text-success d-block mb-0.5"><i class="bi bi-chat-quote-fill me-1"></i>Catatan Atasan:</strong>
                    {{ $row->catatan_validasi }}
                </div>
            @endif
        </div>
    @elseif($status === 'Ditolak')
        <div class="p-3 rounded-3 border" style="background-color: #fef2f2; border-color: #fecaca !important;">
            <div class="d-flex align-items-start gap-2.5">
                <i class="bi bi-x-circle-fill text-danger fs-5 mt-0.5"></i>
                <div class="flex-grow-1">
                    <span class="fw-bold text-danger" style="font-size: 0.9rem;">Tugas Ditolak oleh Atasan</span>
                    <div class="text-muted small">
                        Oleh: <strong>{{ $row->validator->name ?? 'Atasan / Pimpinan' }}</strong>
                        @if($row->validated_at)
                            pada {{ date('d/m/Y H:i', strtotime($row->validated_at)) }} WIB
                        @endif
                    </div>
                    @if(!empty($row->catatan_validasi))
                        <div class="p-2 mt-2 rounded bg-white border border-danger-subtle small text-dark">
                            <strong class="text-danger d-block mb-0.5"><i class="bi bi-chat-square-text-fill me-1"></i>Alasan Penolakan:</strong>
                            {{ $row->catatan_validasi }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if($canValidate)
        <!-- Box Validasi Pekerjaan (Khusus Atasan / Pimpinan) -->
        <div class="p-3 rounded-3 border shadow-2xs" style="background-color: #f8fafc; border-color: #cbd5e1 !important; border-left: 4px solid #046B26 !important;">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-shield-check text-success fs-5"></i>
                    <span class="fw-bold text-dark" style="font-size: 0.92rem;">Panel Validasi Atasan</span>
                </div>
                <span class="badge bg-light text-secondary border fw-normal" style="font-size: 0.72rem;">Wewenang: {{ $authUser->jabatan ?? ($authUser->roles ?? 'Atasan') }}</span>
            </div>
            <div class="mb-2.5">
                <label class="form-label text-secondary small fw-semibold mb-1">Catatan / Arahan Atasan (Wajib jika meminta revisi atau menolak):</label>
                <textarea id="modal-validation-catatan" class="form-control form-control-sm bg-white" rows="2" placeholder="Tuliskan catatan apresiasi, arahan perbaikan, atau rincian poin revisi...">{{ $row->catatan_validasi }}</textarea>
            </div>
            <div class="d-flex align-items-center justify-content-end gap-2 flex-wrap">
                <button type="button" class="btn btn-sm fw-semibold d-inline-flex align-items-center px-3 btn-validation-reject" onclick="window.submitModalValidation({{ $row->id }}, 'reject')" style="height: 34px; background-color: #fef2f2; border: 1.5px solid #dc3545; color: #dc3545;">
                    <i class="bi bi-x-circle me-1.5" style="color: #dc3545;"></i> Tolak
                </button>
                <button type="button" class="btn btn-sm fw-semibold d-inline-flex align-items-center px-3 btn-validation-revise" onclick="window.submitModalValidation({{ $row->id }}, 'revise')" style="height: 34px; background-color: #fffbeb; border: 1.5px solid #d97706; color: #b45309;">
                    <i class="bi bi-arrow-repeat me-1.5" style="color: #d97706;"></i> Minta Revisi
                </button>
                <button type="button" class="btn btn-sm btn-success fw-bold text-white d-inline-flex align-items-center px-4 shadow-2xs" onclick="window.submitModalValidation({{ $row->id }}, 'approve')" style="background-color: #046B26; border-color: #046B26; height: 34px;">
                    <i class="bi bi-check-circle-fill me-1.5 text-white"></i> Setujui Pekerjaan
                </button>
            </div>
        </div>
    @endif

    <!-- 2. Informasi Pegawai & Rekan Kerja -->
    <div class="row g-2">
        <div class="col-12 col-md-6">
            <div class="p-3 bg-white rounded-3 border h-100 shadow-2xs">
                <span class="text-secondary small fw-bold d-block mb-1"><i class="bi bi-person-badge text-primary me-1"></i>Penanggung Jawab (PIC):</span>
                <div class="fw-bold text-dark fs-6">{{ $row->user->name ?? '-' }}</div>
                <div class="text-muted small">{{ $row->user->jabatan ?? '-' }} ({{ $row->user->unit ?? '-' }})</div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="p-3 bg-white rounded-3 border h-100 shadow-2xs">
                <span class="text-secondary small fw-bold d-block mb-1"><i class="bi bi-people-fill text-info me-1"></i>Rekan Kerja (Tagged):</span>
                @if($row->taggedUsers->count() > 0)
                    <div class="d-flex flex-column gap-1">
                        @foreach($row->taggedUsers as $idx => $tu)
                            <div class="small fw-semibold text-dark">
                                {{ $idx + 1 }}. {{ $tu->name }} <span class="text-muted fw-normal">({{ $tu->jabatan ?? '-' }})</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <span class="text-muted small fst-italic">Tidak ada rekan kerja ditandai</span>
                @endif
            </div>
        </div>
    </div>

    <!-- 3. Rincian Waktu & Durasi -->
    <div class="p-3 bg-white rounded-3 border shadow-2xs">
        <div class="row g-3">
            <div class="col-12 col-md-6 border-end-md">
                <span class="text-secondary small fw-bold d-block mb-1"><i class="bi bi-clock-history text-secondary me-1"></i>Estimasi Pelaksanaan:</span>
                <div class="fw-semibold text-dark fs-6">{{ $estFormatted }}</div>
                @php
                    $targetDurasiStr = \App\Helpers\KinerjaWaktuHelper::formatDurasiTarget($row->estimasi_durasi_hari ?? null, $row->estimasi_durasi_jam ?? null);
                @endphp
                @if($targetDurasiStr)
                    <span class="badge bg-success-subtle text-success border border-success-subtle mt-1" style="font-size: 0.75rem;">
                        <i class="bi bi-hourglass-top me-1"></i>Target Durasi: {{ $targetDurasiStr }}
                    </span>
                @endif
            </div>
            <div class="col-12 col-md-6">
                <span class="text-secondary small fw-bold d-block mb-1"><i class="bi bi-stopwatch text-success me-1"></i>Realisasi Waktu & Durasi:</span>
                <div class="small text-dark mb-0.5">Mulai: <strong class="text-primary">{{ $mulaiFormatted }}</strong></div>
                <div class="small text-dark mb-0.5">Selesai: <strong class="text-success">{{ $selesaiFormatted }}</strong></div>
                <div class="small text-dark">Total Durasi: <strong class="text-dark">{{ $durasiStr }}</strong></div>
            </div>
        </div>
    </div>

    <!-- 4. Widget Milestone Checklist -->
    <div>
        {!! \App\Helpers\MilestoneHelper::renderWidget($row->milestones, $row->id, \App\Models\RencanaKerja::class) !!}
    </div>

    <!-- 5. Form Upload & Hasil Kerja -->
    <form class="form-modal-upload p-3 bg-light rounded-3 border shadow-2xs" data-id="{{ $row->id }}" enctype="multipart/form-data">
        @csrf
        <div class="row g-2">
            @if($row->status === 'Di-pause')
                <div class="col-12 mb-2">
                    <label class="form-label mb-1 text-warning-emphasis small fw-bold"><i class="bi bi-pause-circle-fill text-warning me-1"></i>Penyebab Di-pause:</label>
                    <textarea name="alasan_pause" class="form-control form-control-sm" rows="2" placeholder="Isikan penyebab di-pause...">{{ $row->alasan_pause }}</textarea>
                </div>
            @endif

            <div class="col-12 col-md-6 mb-2">
                <label class="form-label mb-1 text-dark small fw-bold"><i class="bi bi-file-earmark-text-fill text-success me-1"></i>Hasil Kerja:</label>
                <textarea name="hasil_kerja" class="form-control form-control-sm" rows="3" placeholder="Isikan rincian hasil kerja...">{{ $row->hasil_kerja }}</textarea>
            </div>

            <div class="col-12 col-md-6 mb-2">
                <label class="form-label mb-1 text-dark small fw-bold"><i class="bi bi-arrow-right-circle-fill text-primary me-1"></i>Rencana Tindak Lanjut:</label>
                <textarea name="rencana_tindak_lanjut" class="form-control form-control-sm" rows="3" placeholder="Isikan rencana tindak lanjut...">{{ $row->rencana_tindak_lanjut }}</textarea>
            </div>

            <div class="col-12 col-md-6">
                <label class="form-label mb-1 text-secondary small fw-semibold"><i class="bi bi-paperclip me-1"></i>Unggah Berkas Kinerja:</label>
                <input type="file" name="file" class="form-control form-control-sm bg-white">
            </div>

            <div class="col-12 col-md-6">
                <label class="form-label mb-1 text-secondary small fw-semibold"><i class="bi bi-link-45deg me-1"></i>Tautan Google Drive / Link External:</label>
                <input type="url" name="url_external" class="form-control form-control-sm bg-white" placeholder="https://drive.google.com/..." value="{{ $row->url_external }}">
            </div>

            <div class="col-12 mt-3 text-start">
                <button type="submit" class="btn btn-sm text-white px-4 fw-bold" style="background-color: #046B26; border-color: #046B26; height: 34px;">
                    <i class="bi bi-cloud-arrow-up-fill me-1"></i> Simpan Hasil & Berkas
                </button>
            </div>

            <!-- Hasil Tersimpan -->
            <div class="col-12 mt-3 pt-2 border-top">
                <span class="text-dark small fw-bold d-block mb-1.5"><i class="bi bi-folder-check text-success me-1"></i>Hasil & Berkas Tersimpan:</span>
                <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                    @if($row->file)
                        <a href="{{ asset('storage/' . $row->file) }}" target="_blank" class="btn btn-sm btn-outline-success text-nowrap fw-semibold px-3 d-inline-flex align-items-center" style="font-size: 0.8rem;">
                            <i class="bi bi-file-earmark-check-fill me-1"></i> Unduh / Lihat Berkas
                        </a>
                    @endif
                    @if($row->url_external)
                        <a href="{{ $row->url_external }}" target="_blank" class="btn btn-sm btn-outline-primary text-nowrap fw-semibold px-3 d-inline-flex align-items-center" style="font-size: 0.8rem;">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Buka Link Eksternal
                        </a>
                    @endif
                    @if(!$row->file && !$row->url_external && empty($row->hasil_kerja))
                        <span class="text-muted small fst-italic">Belum ada berkas atau link tersimpan</span>
                    @endif
                </div>

                @if(!empty($row->hasil_kerja))
                    <div class="w-100 p-2.5 mt-2 rounded bg-white border small text-dark">
                        <strong class="text-success d-block mb-1"><i class="bi bi-file-earmark-text-fill me-1"></i>Hasil Kerja:</strong>
                        <div>{!! nl2br(e($row->hasil_kerja)) !!}</div>
                    </div>
                @endif

                @if(!empty($row->rencana_tindak_lanjut))
                    <div class="w-100 p-2.5 mt-2 rounded bg-white border small text-dark">
                        <strong class="text-primary d-block mb-1"><i class="bi bi-arrow-right-circle-fill me-1"></i>Rencana Tindak Lanjut:</strong>
                        <div>{!! nl2br(e($row->rencana_tindak_lanjut)) !!}</div>
                    </div>
                @endif
            </div>
        </div>
    </form>
</div>
