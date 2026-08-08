<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rencana Kerja Insidentil</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 6mm 6mm 8mm 6mm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8pt;
            color: #333333;
            margin: 0;
            padding: 0;
        }
        .title-header {
            background-color: #15432D;
            color: #ffffff;
            text-align: center;
            font-weight: bold;
            font-size: 11pt;
            padding: 7px;
            margin-bottom: 10px;
            border-radius: 3px;
            text-transform: uppercase;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 8.5pt;
        }
        .meta-table td {
            padding: 2px 4px;
            vertical-align: top;
        }
        .meta-label {
            font-weight: bold;
            width: 100px;
        }
        .meta-sep {
            width: 10px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
        }
        .data-table th {
            background-color: #15432D;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            padding: 5px 3px;
            border: 1px solid #0d2c1d;
            font-size: 7pt;
        }
        .data-table td {
            border: 1px solid #cccccc;
            padding: 4px 3px;
            vertical-align: middle;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .page-number:after {
            content: counter(page);
        }
        .footer-info {
            position: fixed;
            bottom: -5mm;
            left: 0;
            right: 0;
            font-size: 7pt;
            color: #777777;
            border-top: 1px solid #eeeeee;
            padding-top: 3px;
        }
    </style>
</head>
<body>

    <!-- Kop Surat Universitas Ibnu Sina -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 4px;">
        <tr>
            <td style="width: 75px; text-align: left; vertical-align: middle; padding: 0;">
                @if(file_exists(public_path('assets/img/logouis.png')))
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('assets/img/logouis.png'))) }}" style="height: 72px; width: auto; display: block; margin: 0;">
                @endif
            </td>
            <td style="text-align: center; vertical-align: middle; padding-right: 75px; padding-left: 0;">
                <div style="font-size: 11pt; font-weight: bold; color: #356B3A; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;">
                    YAYASAN PENDIDIKAN IBNU SINA BATAM (YAPISTA)
                </div>
                <div style="font-size: 19pt; font-weight: bold; color: #356B3A; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 3px;">
                    UNIVERSITAS IBNU SINA
                </div>
                <div style="font-size: 8.5pt; color: #222222; margin-bottom: 2px;">
                    Jalan Teuku Umar, Lubuk Baja, Kota Batam-Indonesia Telp. 0778 &ndash; 408 3113
                </div>
                <div style="font-size: 8.5pt; color: #222222;">
                    Email : info@uis.ac.id / uibnusina@gmail.com Website : uis.ac.id
                </div>
            </td>
        </tr>
    </table>
    <div style="border-top: 2.5px solid #356B3A; border-bottom: 1px solid #356B3A; height: 2px; margin-bottom: 10px;"></div>

    <div class="title-header">
        LAPORAN RENCANA KERJA INSIDENTIL ({{ $periodeText }})
    </div>

    <table class="meta-table">
        <tr>
            <td class="meta-label">NAMA STAFF</td>
            <td class="meta-sep">:</td>
            <td><strong>{{ $namaStaff }}</strong></td>
        </tr>
        <tr>
            <td class="meta-label">JABATAN</td>
            <td class="meta-sep">:</td>
            <td><strong>{{ $jabatanStaff }}</strong></td>
        </tr>
        <tr>
            <td class="meta-label">UNIT</td>
            <td class="meta-sep">:</td>
            <td><strong>{{ $unitStaff }}</strong></td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 3%;">NO</th>
                <th style="width: 5%;">HARI</th>
                <th style="width: 17%;">URAIAN TUGAS</th>
                <th style="width: 12.5%;">ESTIMASI PELAKSANAAN</th>
                <th style="width: 12.5%;">REALISASI PELAKSANAAN</th>
                <th style="width: 5.5%;">DURASI</th>
                <th style="width: 6.5%;">STATUS & BERKAS</th>
                <th style="width: 19%;">HASIL KERJA & BUKTI</th>
                <th style="width: 19%;">RENCANA TINDAK LANJUT</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $index => $item)
                @php
                    $durasiStr = '-';
                    if (!empty($item->waktu_mulai) && !empty($item->waktu_selesai) && $item->waktu_selesai !== '00:00:00') {
                        try {
                            $tglMulaiStr = !empty($item->tanggal_mulai) ? $item->tanggal_mulai : date('Y-m-d');
                            $tglSelesaiStr = !empty($item->tanggal_selesai) ? $item->tanggal_selesai : $tglMulaiStr;
                            $startTs = strtotime($tglMulaiStr . ' ' . $item->waktu_mulai);
                            $endTs = strtotime($tglSelesaiStr . ' ' . $item->waktu_selesai);
                            $diffInSeconds = max(0, $endTs - $startTs);

                            $days = floor($diffInSeconds / 86400);
                            $hours = floor(($diffInSeconds % 86400) / 3600);
                            $minutes = floor(($diffInSeconds % 3600) / 60);
                            $seconds = $diffInSeconds % 60;

                            $durasiParts = [];
                            if ($days > 0) $durasiParts[] = $days . 'h';
                            if ($hours > 0) $durasiParts[] = $hours . 'j';
                            if ($minutes > 0) $durasiParts[] = $minutes . 'm';
                            if ($seconds > 0 || empty($durasiParts)) $durasiParts[] = $seconds . 's';
                            $durasiStr = implode(' ', $durasiParts);
                        } catch (\Exception $e) {
                            $durasiStr = '-';
                        }
                    }
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $item->hari ?? '-' }}</td>
                    <td class="text-left">
                        <strong>{{ $item->uraian_tugas }}</strong>
                        @if($item->taggedUsers && $item->taggedUsers->count() > 0)
                            <div style="font-size: 6.5pt; color: #555555; margin-top: 2px;">
                                <strong>Tag:</strong> {{ implode(', ', $item->taggedUsers->pluck('name')->toArray()) }}
                            </div>
                        @endif
                    </td>
                    <td class="text-center" style="font-size: 7pt; line-height: 1.3;">
                        @if($item->estimasi_tanggal_mulai)
                            <div><strong>Mulai:</strong> {{ date('d/m/Y', strtotime($item->estimasi_tanggal_mulai)) }} {{ $item->estimasi_jam_mulai ? substr($item->estimasi_jam_mulai, 0, 5) : '' }}</div>
                        @endif
                        @if($item->estimasi_tanggal_selesai)
                            <div><strong>Selesai:</strong> {{ date('d/m/Y', strtotime($item->estimasi_tanggal_selesai)) }} {{ $item->estimasi_jam_selesai ? substr($item->estimasi_jam_selesai, 0, 5) : '' }}</div>
                        @endif
                        @if(!$item->estimasi_tanggal_mulai && !$item->estimasi_tanggal_selesai)
                            -
                        @endif
                    </td>
                    <td class="text-center" style="font-size: 7pt; line-height: 1.3;">
                        @if($item->tanggal_mulai)
                            <div><strong>Mulai:</strong> {{ date('d/m/Y', strtotime($item->tanggal_mulai)) }} {{ $item->waktu_mulai ? substr($item->waktu_mulai, 0, 5) : '' }}</div>
                        @else
                            <div><strong>Mulai:</strong> -</div>
                        @endif
                        @if($item->tanggal_selesai && $item->waktu_selesai !== '00:00:00')
                            <div><strong>Selesai:</strong> {{ date('d/m/Y', strtotime($item->tanggal_selesai)) }} {{ substr($item->waktu_selesai, 0, 5) }}</div>
                        @else
                            <div><strong>Selesai:</strong> -</div>
                        @endif
                    </td>
                    <td class="text-center"><strong>{{ $durasiStr }}</strong></td>
                    <td class="text-center" style="font-size: 7pt;">
                        <div><strong>{{ $item->status ?? 'Selesai' }}</strong></div>
                    </td>
                    <td class="text-left" style="font-size: 7pt; line-height: 1.3;">
                        <div>{!! $item->hasil_kerja ? trim(strip_tags($item->hasil_kerja)) : '-' !!}</div>

                        <div style="font-size: 6.5pt; margin-top: 3px;">
                            <strong>Berkas:</strong> 
                            @if($item->file)
                                @php $fileUrl = asset('storage/' . $item->file); @endphp
                                <a href="{{ $fileUrl }}" target="_blank" style="color: #15803d; text-decoration: underline;">{{ $fileUrl }}</a>
                            @else
                                <span style="color: #777777;">-</span>
                            @endif
                        </div>

                        <div style="font-size: 6.5pt; margin-top: 2px; word-break: break-all;">
                            <strong>Link External:</strong> 
                            @if($item->url_external)
                                <a href="{{ $item->url_external }}" target="_blank" style="color: #0369a1; text-decoration: underline;">{{ $item->url_external }}</a>
                            @else
                                <span style="color: #777777;">-</span>
                            @endif
                        </div>
                    </td>
                    <td class="text-left" style="font-size: 7pt; line-height: 1.3;">{!! $item->rencana_tindak_lanjut ? trim(strip_tags($item->rencana_tindak_lanjut)) : '-' !!}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 15px; color: #777777;">
                        Tidak ada data rencana kerja insidentil untuk kriteria yang dipilih.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer-info">
        <table style="width: 100%;">
            <tr>
                <td style="text-align: left;">Dicetak pada: {{ date('d/m/Y H:i:s') }}</td>
                <td style="text-align: right;">Halaman <span class="page-number"></span></td>
            </tr>
        </table>
    </div>

</body>
</html>
