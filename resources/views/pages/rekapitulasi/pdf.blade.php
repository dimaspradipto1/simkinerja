<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Capaian & Analisis Kendala Kinerja - Universitas Ibnu Sina</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 2.54cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8pt;
            color: #222222;
            margin: 0;
            padding: 0;
        }
        .letterhead {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .letterhead td {
            vertical-align: middle;
        }
        .logo {
            width: 18%;
            text-align: center;
            padding-right: 10px;
        }
        .logo img {
            width: 76px;
            height: 76px;
        }
        .institution {
            text-align: center;
            line-height: 1.2;
            color: #326641;
        }
        .institution .foundation {
            font-family: 'Times New Roman', serif;
            font-weight: bold;
            font-size: 16pt;
            white-space: nowrap;
        }
        .institution .university {
            font-family: 'Times New Roman', serif;
            font-weight: bold;
            font-size: 25pt;
            white-space: nowrap;
        }
        .institution .contact {
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
            white-space: nowrap;
        }
        .kop-line {
            border-top: 2px solid #15432d;
            border-bottom: 1px solid #15432d;
            height: 2px;
            margin-bottom: 8px;
        }
        .report-title {
            background-color: #15432D;
            color: #ffffff;
            text-align: center;
            font-weight: bold;
            font-size: 10pt;
            padding: 6px;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .meta-info {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 8pt;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
        }
        .meta-info td {
            padding: 3px 6px;
        }

        .section-title {
            font-size: 8.5pt;
            font-weight: bold;
            color: #15432D;
            margin-top: 8px;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1.5px solid #15432D;
            padding-bottom: 2px;
        }

        /* Detail Data Table */
        table.data-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 7pt;
        }
        table.data-table th {
            background-color: #15432D;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            padding: 5px 3px;
            border: 1px solid #0d2c1d;
            font-size: 6.8pt;
            word-wrap: break-word;
        }
        table.data-table td {
            border: 1px solid #cccccc;
            padding: 4px 3px;
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        
        .badge {
            display: inline-block;
            padding: 2px 5px;
            font-size: 6.5pt;
            border-radius: 3px;
            font-weight: bold;
        }
        .badge-success { background-color: #dcfce7; color: #15803d; border: 1px solid #86efac; }
        .badge-primary { background-color: #dbeafe; color: #1d4ed8; border: 1px solid #93c5fd; }
        .badge-danger  { background-color: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }
        .badge-warning { background-color: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
        .badge-secondary { background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
        .badge-info    { background-color: #e0f2fe; color: #0369a1; border: 1px solid #7dd3fc; }

        /* KPI Analysis Summary Section (Dipindahkan ke Bawah) */
        .kpi-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .kpi-table th {
            padding: 4px 6px;
            font-size: 7pt;
            font-weight: bold;
            text-align: center;
            background-color: #f1f5f9;
            color: #334155;
            border: 1px solid #cbd5e1;
        }
        .kpi-table td {
            padding: 5px;
            text-align: center;
            vertical-align: middle;
            border: 1px solid #cbd5e1;
        }
        .kpi-val {
            font-size: 13pt;
            font-weight: bold;
            display: block;
        }
        .kpi-sub {
            font-size: 6.5pt;
            color: #64748b;
            text-transform: uppercase;
        }

        /* Criteria & Legend Tables */
        .criteria-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 6.8pt;
        }
        .criteria-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: bold;
            padding: 2.5px 4px;
            border: 1px solid #cbd5e1;
            text-align: left;
            height: 17px;
            vertical-align: middle;
        }
        .criteria-table td {
            padding: 2.5px 4px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
            height: 17px;
            line-height: 1.2;
        }

        .footer-note {
            position: fixed;
            bottom: -15mm;
            left: 0;
            right: 0;
            font-size: 6.5pt;
            color: #777777;
            border-top: 0.5px solid #dddddd;
            padding-top: 2px;
        }
    </style>
</head>
<body>

    <!-- KOP RESMI UNIVERSITAS IBNU SINA -->
    <table class="letterhead">
        <tr>
            <td class="logo">
                @if(file_exists(public_path('assets/img/logouis.png')))
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('assets/img/logouis.png'))) }}" alt="Logo Universitas Ibnu Sina">
                @endif
            </td>
            <td class="institution">
                <div class="foundation">
                    YAYASAN PENDIDIKAN IBNU SINA BATAM (YAPISTA)
                </div>
                <div class="university">
                    UNIVERSITAS IBNU SINA (UIS)
                </div>
                <div class="contact">
                    Jl. Teuku Umar Lubuk Baja, Kota Batam-Indonesia Telp. 0778-408 3113
                </div>
                <div class="contact">
                    Email : <span style="color: #3333cc; text-decoration: underline;">info@uis.ac.id/ibnusina@gmail.com</span> Website : uis.ac.id
                </div>
            </td>
            <td class="logo"></td>
        </tr>
    </table>
    <div class="kop-line"></div>

    <!-- TITLE -->
    <div class="report-title">
        REKAPITULASI CAPAIAN & ANALISIS KENDALA KINERJA
    </div>

    <!-- METADATA FILTER -->
    <table class="meta-info">
        <tr>
            <td width="15%"><strong>Periode / Waktu</strong></td>
            <td width="35%">: {{ $periodeText }}</td>
            <td width="15%"><strong>Kategori Laporan</strong></td>
            <td width="35%">: {{ $kategoriText }}</td>
        </tr>
        <tr>
            <td><strong>Filter Status / Kendala</strong></td>
            <td>: {{ $statusFilterText }}</td>
            <td><strong>Pegawai / Jabatan</strong></td>
            <td>: {{ $staffText }}</td>
        </tr>
        <tr>
            <td><strong>Tanggal Unduh</strong></td>
            <td>: {{ date('d/m/Y H:i') }} WIB</td>
            <td><strong>Tercetak Oleh</strong></td>
            <td>: {{ auth()->check() ? auth()->user()->name : 'System' }}</td>
        </tr>
    </table>

    <!-- 1. DETAIL DATA TABLE (DI ATAS AGAR LANGSUNG TERLIHAT) -->
    <div class="section-title">
        1. DAFTAR RINCIAN KEGIATAN & REALISASI KINERJA
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 2.5%; padding-left: 1px; padding-right: 1px;">NO</th>
                <th style="width: 8.5%;">KATEGORI</th>
                <th style="width: 27%;">URAIAN KEGIATAN / TUGAS</th>
                <th style="width: 14%;">PEGAWAI / JABATAN</th>
                <th style="width: 11%;">ESTIMASI WAKTU</th>
                <th style="width: 11%;">REALISASI WAKTU</th>
                <th style="width: 7%;">STATUS</th>
                <th style="width: 19%;">ANALISIS KENDALA & TINDAK LANJUT</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $idx => $item)
                @php
                    $isSelesai = $item->status === 'Selesai';
                    $isTerlambat = false;
                    if (!$isSelesai && !empty($item->estimasi_tanggal_selesai)) {
                        $endTime = $item->estimasi_jam_selesai ?: '23:59:59';
                        try {
                            $estimasiEnd = \Carbon\Carbon::parse($item->estimasi_tanggal_selesai . ' ' . $endTime);
                            if (\Carbon\Carbon::now()->gt($estimasiEnd)) {
                                $isTerlambat = true;
                            }
                        } catch (\Throwable $e) {}
                    }
                    
                    $estStr = '-';
                    if ($item->estimasi_tanggal_mulai && $item->estimasi_tanggal_selesai) {
                        $estStr = date('d/m/Y', strtotime($item->estimasi_tanggal_mulai)) . ' - ' . date('d/m/Y', strtotime($item->estimasi_tanggal_selesai));
                    } elseif ($item->estimasi_tanggal_selesai) {
                        $estStr = 's/d ' . date('d/m/Y', strtotime($item->estimasi_tanggal_selesai));
                    }

                    $realStr = '-';
                    if ($item->tanggal_mulai && $item->tanggal_selesai) {
                        $realStr = date('d/m/Y', strtotime($item->tanggal_mulai)) . ' - ' . date('d/m/Y', strtotime($item->tanggal_selesai));
                    } elseif ($item->tanggal_mulai) {
                        $realStr = 'Mulai ' . date('d/m/Y', strtotime($item->tanggal_mulai));
                    } elseif ($item->tanggal_selesai) {
                        $realStr = 'Selesai ' . date('d/m/Y', strtotime($item->tanggal_selesai));
                    }

                    // Total waktu pengerjaan dalam jam dan menit
                    $durasiStr = null;
                    $durasiDetik = 0;
                    $isBerjalan = false;

                    if (!empty($item->waktu_mulai) && !empty($item->waktu_selesai) && $item->waktu_selesai !== '00:00:00') {
                        $tglM = !empty($item->tanggal_mulai) ? $item->tanggal_mulai : now()->format('Y-m-d');
                        $tglS = !empty($item->tanggal_selesai) ? $item->tanggal_selesai : $tglM;
                        $startTs = strtotime($tglM . ' ' . $item->waktu_mulai);
                        $endTs = strtotime($tglS . ' ' . $item->waktu_selesai);
                        if ($endTs > $startTs) {
                            $durasiDetik = $endTs - $startTs;
                        } else {
                            $tDiff = strtotime($item->waktu_selesai) - strtotime($item->waktu_mulai);
                            if ($tDiff > 0) $durasiDetik = $tDiff;
                        }
                    }

                    if ($durasiDetik <= 0 && !empty($item->durasi_detik) && $item->durasi_detik > 0) {
                        $durasiDetik = (int) $item->durasi_detik;
                    }

                    if ($durasiDetik <= 0 && in_array($item->status, ['Proses', 'Berjalan'])) {
                        if (!empty($item->waktu_mulai)) {
                            $tglM = !empty($item->tanggal_mulai) ? $item->tanggal_mulai : now()->format('Y-m-d');
                            $diff = time() - strtotime($tglM . ' ' . $item->waktu_mulai);
                            if ($diff > 0) {
                                $durasiDetik = $diff;
                                $isBerjalan = true;
                            }
                        } elseif (!empty($item->last_started_at)) {
                            $diff = now()->diffInSeconds($item->last_started_at);
                            if ($diff > 0) {
                                $durasiDetik = (int) $diff + (int) ($item->durasi_detik ?? 0);
                                $isBerjalan = true;
                            }
                        }
                    }

                    if ($durasiDetik <= 0 && !empty($item->tanggal_mulai) && !empty($item->tanggal_selesai)) {
                        $d1 = strtotime($item->tanggal_mulai);
                        $d2 = strtotime($item->tanggal_selesai);
                        if ($d2 >= $d1) {
                            $days = floor(($d2 - $d1) / 86400) + 1;
                            $durasiDetik = $days * 8 * 3600;
                        }
                    }

                    if ($durasiDetik > 0) {
                        $h = floor($durasiDetik / 3600);
                        $m = floor(($durasiDetik % 3600) / 60);
                        if ($h > 0 && $m > 0) {
                            $durasiStr = $h . ' jam ' . $m . ' menit';
                        } elseif ($h > 0) {
                            $durasiStr = $h . ' jam';
                        } elseif ($m > 0) {
                            $durasiStr = $m . ' menit';
                        } else {
                            $durasiStr = '< 1 menit';
                        }
                        if ($isBerjalan) {
                            $durasiStr .= ' (berjalan)';
                        }
                    }
                @endphp
                <tr>
                    <td class="text-center" style="padding-left: 1px; padding-right: 1px;">{{ $idx + 1 }}</td>
                    <td class="text-center">
                        @php
                            $kat = $item->kategori ?: 'Rencana Kerja';
                            $katBadge = match($kat) {
                                'Kepanitiaan' => 'badge-info',
                                'Insidentil' => 'badge-warning',
                                default => 'badge-success'
                            };
                        @endphp
                        <span class="badge {{ $katBadge }}">{{ $kat }}</span>
                    </td>
                    <td>
                        <strong style="color: #1e293b;">{{ $item->uraian_tugas ?: '-' }}</strong>
                    </td>
                    <td>
                        @if($item->taggedUsers && $item->taggedUsers->count() > 0)
                            @php
                                // Filter tagged users: hanya tampilkan yang merupakan bawahan unit kepala yang login
                                // $subordinateIds = null berarti super admin/rektorat (tampilkan semua)
                                $visibleTagged = isset($subordinateIds) && $subordinateIds !== null
                                    ? $item->taggedUsers->filter(fn($u) => in_array($u->id, $subordinateIds))
                                    : $item->taggedUsers;
                            @endphp
                            @if($visibleTagged->count() > 0)
                                @foreach($visibleTagged as $tUser)
                                    <div style="{{ !$loop->first ? 'margin-top: 3px; padding-top: 3px; border-top: 0.5px dashed #cbd5e1;' : '' }}">
                                        <strong>{{ $tUser->name }}</strong>
                                        @if($tUser->jabatan)
                                            <br><small style="color: #64748b;">{{ $tUser->jabatan }}</small>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                {{-- Jika semua tagged users berasal dari unit lain, tampilkan pemilik kegiatan --}}
                                <strong>{{ $item->user ? $item->user->name : '-' }}</strong>
                                @if($item->user && $item->user->jabatan)
                                    <br><small style="color: #64748b;">{{ $item->user->jabatan }}</small>
                                @endif
                            @endif
                        @else
                            <strong>{{ $item->user ? $item->user->name : '-' }}</strong>
                            @if($item->user && $item->user->jabatan)
                                <br><small style="color: #64748b;">{{ $item->user->jabatan }}</small>
                            @endif
                        @endif
                    </td>
                    <td class="text-center" style="font-size: 6.5pt;">{{ $estStr }}</td>
                    <td class="text-center" style="font-size: 6.5pt;">
                        @if($realStr !== '-')
                            <div>{{ $realStr }}</div>
                        @endif
                        @if($durasiStr)
                            <div style="{{ $realStr !== '-' ? 'margin-top: 3px;' : '' }} font-size: 5.8pt; line-height: 1.2;">
                                <span style="color: #dc2626; font-weight: bold;">Lama Pengerjaan:</span><br>
                                <span style="color: #0f766e; font-weight: bold;">{{ $durasiStr }}</span>
                            </div>
                        @elseif($realStr === '-')
                            -
                        @endif
                    </td>
                    <td class="text-center">
                        @if($isSelesai)
                            <span class="badge badge-success">Selesai</span>
                        @elseif($item->status === 'Berjalan' || $item->status === 'Proses')
                            <span class="badge badge-primary">Proses</span>
                        @elseif($item->status === 'Di-pause')
                            <span class="badge badge-warning">Di-pause</span>
                        @else
                            <span class="badge badge-secondary">Belum Mulai</span>
                        @endif
                    </td>
                    <td>
                        @if($isTerlambat)
                            <div style="color: #b91c1c; font-weight: bold; margin-bottom: 2px;">
                                ⚠️ Terlambat (Lewat Estimasi)
                            </div>
                        @endif
                        @if($item->alasan_pause)
                            <div style="color: #b45309; margin-bottom: 2px;">
                                <strong>Alasan Pause:</strong> {{ $item->alasan_pause }}
                            </div>
                        @endif
                        @if($item->rencana_tindak_lanjut)
                            <div>
                                <strong>Tindak Lanjut:</strong> {{ trim(strip_tags($item->rencana_tindak_lanjut)) }}
                            </div>
                        @elseif(!$isTerlambat && !$item->alasan_pause)
                            <span style="color: #94a3b8;">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 12px; color: #64748b;">
                        Tidak ada data kegiatan yang sesuai dengan filter yang dipilih.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- 2. HASIL ANALISIS & DIAGNOSIS KINERJA (DIPINDAHKAN KE BAWAH TABEL) -->
    <div class="section-title">
        2. HASIL ANALISIS & DIAGNOSIS KINERJA
    </div>
    <table class="kpi-table">
        <tr>
            <th width="15%">TOTAL KEGIATAN</th>
            <th width="14%">BELUM MULAI</th>
            <th width="14%">SEDANG PROSES</th>
            <th width="15%">TERLAMBAT (KENDALA)</th>
            <th width="14%">SUDAH SELESAI</th>
            <th width="14%">% CAPAIAN KERJA</th>
            <th width="14%">PREDIKAT KINERJA</th>
        </tr>
        <tr>
            <td style="background-color: #f0f9ff;">
                <span class="kpi-val" style="color: #0284c7;">{{ $rekap['total'] }}</span>
                <span class="kpi-sub">Total Tugas</span>
            </td>
            <td style="background-color: #f8fafc;">
                <span class="kpi-val" style="color: #64748b;">{{ $rekap['belum'] }}</span>
                <span class="kpi-sub">Belum Mulai</span>
            </td>
            <td style="background-color: #eff6ff;">
                <span class="kpi-val" style="color: #2563eb;">{{ $rekap['proses'] }}</span>
                <span class="kpi-sub">Dalam Proses</span>
            </td>
            <td style="background-color: #fef2f2;">
                <span class="kpi-val" style="color: #dc2626;">{{ $rekap['terlambat'] }}</span>
                <span class="kpi-sub">Lewat Estimasi</span>
            </td>
            <td style="background-color: #f0fdf4;">
                <span class="kpi-val" style="color: #16a34a;">{{ $rekap['selesai'] }}</span>
                <span class="kpi-sub">Terselesaikan</span>
            </td>
            <td style="background-color: #dcfce7;">
                <span class="kpi-val" style="color: #15803d;">{{ $rekap['percent'] }}%</span>
                <span class="kpi-sub">{{ $rekap['selesai'] }} / {{ $rekap['total'] }} Tugas</span>
            </td>
            <td style="background-color: #f8fafc;">
                @php
                    $predikat = 'Sangat Kurang';
                    $predColor = '#991b1b';
                    if ($rekap['percent'] >= 90) { $predikat = 'Sangat Baik'; $predColor = '#15803d'; }
                    elseif ($rekap['percent'] >= 75) { $predikat = 'Baik'; $predColor = '#16a34a'; }
                    elseif ($rekap['percent'] >= 60) { $predikat = 'Cukup'; $predColor = '#d97706'; }
                    elseif ($rekap['percent'] >= 40) { $predikat = 'Kurang'; $predColor = '#ea580c'; }
                @endphp
                <span class="kpi-val" style="color: {{ $predColor }}; font-size: 11pt;">{{ $predikat }}</span>
                <span class="kpi-sub">Tingkat Capaian</span>
            </td>
        </tr>
        <tr>
            <td colspan="7" style="text-align: left; padding: 4px 8px; background-color: #ffffff; font-size: 7pt; color: #475569;">
                <strong>Ringkasan Diagnosis:</strong>
                Persentase penyelesaian kegiatan mencapai <strong>{{ $rekap['percent'] }}%</strong>.
                @if($rekap['terlambat'] > 0)
                    Terdapat <strong style="color: #dc2626;">{{ $rekap['terlambat'] }} kegiatan mengalami kendala keterlambatan</strong> melewati estimasi tanggal selesai yang memerlukan tindak lanjut percepatan.
                @else
                    Tidak terdapat kegiatan yang mengalami keterlambatan pelaksanaan.
                @endif
                @if($rekap['proses'] > 0)
                    Sebanyak <strong>{{ $rekap['proses'] }} kegiatan</strong> masih dalam proses pengerjaan aktif.
                @endif
            </td>
        </tr>
    </table>

    <!-- 3. PEDOMAN STANDAR KRITERIA PENILAIAN & KETERANGAN STATUS -->
    <div class="section-title">
        3. PEDOMAN STANDAR KRITERIA PENILAIAN & KETERANGAN STATUS
    </div>
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 12px;">
        <tr>
            <!-- Tabel Kriteria Predikat (5 Baris) -->
            <td style="width: 49.5%; vertical-align: top; padding-right: 0.5%;">
                <div style="font-weight: bold; font-size: 7.2pt; color: #15432D; margin-bottom: 3px; height: 13px; line-height: 13px;">
                    Matriks Standar Predikat Kinerja:
                </div>
                <table class="criteria-table">
                    <thead>
                        <tr>
                            <th style="width: 28%;">PREDIKAT</th>
                            <th style="width: 24%; text-align: center;">RENTANG</th>
                            <th style="width: 48%;">STANDAR PENILAIAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong style="color: #15803d;">Sangat Baik</strong></td>
                            <td class="text-center"><strong>90% - 100%</strong></td>
                            <td>Melampaui target, tuntas & berkualitas prima.</td>
                        </tr>
                        <tr>
                            <td><strong style="color: #16a34a;">Baik</strong></td>
                            <td class="text-center"><strong>75% - 89%</strong></td>
                            <td>Memenuhi target operasional dengan baik.</td>
                        </tr>
                        <tr>
                            <td><strong style="color: #d97706;">Cukup</strong></td>
                            <td class="text-center"><strong>60% - 74%</strong></td>
                            <td>Memenuhi sasaran dasar, perlu perbaikan kendala.</td>
                        </tr>
                        <tr>
                            <td><strong style="color: #ea580c;">Kurang</strong></td>
                            <td class="text-center"><strong>40% - 59%</strong></td>
                            <td>Di bawah target minimal, butuh percepatan.</td>
                        </tr>
                        <tr>
                            <td><strong style="color: #991b1b;">Sangat Kurang</strong></td>
                            <td class="text-center"><strong>&lt; 40%</strong></td>
                            <td>Jauh di bawah target, evaluasi khusus & mendalam.</td>
                        </tr>
                    </tbody>
                </table>
            </td>
            <!-- Tabel Keterangan Status (5 Baris) -->
            <td style="width: 49.5%; vertical-align: top; padding-left: 0.5%;">
                <div style="font-weight: bold; font-size: 7.2pt; color: #15432D; margin-bottom: 3px; height: 13px; line-height: 13px;">
                    Definisi Indikator Status Pelaksanaan:
                </div>
                <table class="criteria-table">
                    <thead>
                        <tr>
                            <th style="width: 25%;">STATUS</th>
                            <th style="width: 19%;">WARNA</th>
                            <th style="width: 56%;">DEFINISI & REKOMENDASI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="badge badge-success">Selesai</span></td>
                            <td>Hijau</td>
                            <td>Kegiatan tuntas dilaporkan dan terverifikasi.</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-primary">Proses</span></td>
                            <td>Biru</td>
                            <td>Kegiatan aktif berjalan dalam tahap pelaksanaan.</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-warning">Di-pause</span></td>
                            <td>Kuning</td>
                            <td>Dihentikan sementara karena hambatan tertentu.</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-secondary">Belum Mulai</span></td>
                            <td>Abu-abu</td>
                            <td>Telah dijadwalkan, belum dimulai pengerjaannya.</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-danger">Terlambat</span></td>
                            <td>Merah</td>
                            <td>Belum selesai dan melewati batas estimasi waktu.</td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    @include('pages.partials.pdf-signature', [
        'leaderLabel' => $signer['kepalaJabatan'] ?? 'Pimpinan Universitas Ibnu Sina',
        'leaderName' => $signer['kepalaNama'] ?? 'Dr. MUHAMMAD ROPIANTO, S.Kom., M.Kom',
        'leaderNup' => $signer['kepalaNidn'] ?? '1028067804',
        'employeeLabel' => $signer['pegawaiLabel'] ?? 'Pegawai Yang Bersangkutan',
        'employeeName' => $signer['pegawaiNama'] ?? (auth()->user()?->name ?? '...................................'),
        'employeeNup' => $signer['pegawaiNidn'] ?? (auth()->user()?->nidn ?: '-'),
    ])

    <div class="footer-note">
        Dokumen ini diterbitkan secara otomatis oleh SIMKINERJA Universitas Ibnu Sina pada {{ date('d/m/Y H:i') }} WIB.
    </div>

</body>
</html>
