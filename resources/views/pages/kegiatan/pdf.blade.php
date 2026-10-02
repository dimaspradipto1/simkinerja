<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kegiatan</title>
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
        .data-table {
            width: 100%;
            table-layout: fixed;
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
            word-wrap: break-word;
        }
        .data-table td {
            border: 1px solid #cccccc;
            padding: 4px 3px;
            vertical-align: middle;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
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

    <!-- Kop Surat -->
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
        LAPORAN REKAPITULASI KEGIATAN {{ !empty($periodeText) ? '(' . $periodeText . ')' : ($periode ? '(' . $periode->nama_periode . ')' : '') }}
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 3%;">NO</th>
                <th style="width: 13%;">PEGAWAI</th>
                <th style="width: 9%;">KATEGORI</th>
                <th style="width: 5%;">HARI</th>
                <th style="width: 16%;">URAIAN KEGIATAN</th>
                <th style="width: 12%;">ESTIMASI</th>
                <th style="width: 12%;">REALISASI</th>
                <th style="width: 7%;">STATUS</th>
                <th style="width: 11%;">HASIL KEGIATAN</th>
                <th style="width: 12%;">TINDAK LANJUT</th>
            </tr>
        </thead>
        <tbody>
            @forelse($kegiatans as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-left">
                        <strong>{{ $item->user->name ?? '-' }}</strong><br>
                        <span style="font-size: 6.5pt; color: #666;">{{ $item->user->jabatan ?? '-' }}</span>
                        @if($item->taggedUsers && $item->taggedUsers->count() > 0)
                            <div style="font-size: 6pt; color: #0284c7; margin-top: 2px;">
                                <strong>Rekan:</strong> {{ implode(', ', $item->taggedUsers->pluck('name')->toArray()) }}
                            </div>
                        @endif
                    </td>
                    <td class="text-center" style="font-size: 6.5pt;">
                        <span style="font-weight: bold;">{{ $item->kategori ?: 'Rencana Kerja' }}</span>
                    </td>
                    <td class="text-center">{{ $item->hari ?? '-' }}</td>
                    <td class="text-left">
                        <strong>{{ $item->uraian_tugas }}</strong>
                    </td>
                    <td class="text-left" style="font-size: 7pt;">
                        @if($item->estimasi_tanggal_mulai)
                            <div>{{ date('d/m/Y', strtotime($item->estimasi_tanggal_mulai)) }} {{ $item->estimasi_jam_mulai ? substr($item->estimasi_jam_mulai, 0, 5) : '' }}</div>
                        @endif
                        @if($item->estimasi_tanggal_selesai)
                            <div>s/d {{ date('d/m/Y', strtotime($item->estimasi_tanggal_selesai)) }} {{ $item->estimasi_jam_selesai ? substr($item->estimasi_jam_selesai, 0, 5) : '' }}</div>
                        @endif
                    </td>
                    <td class="text-left" style="font-size: 7pt;">
                        @if($item->tanggal_mulai)
                            <div>Mulai: {{ date('d/m/Y', strtotime($item->tanggal_mulai)) }} {{ $item->waktu_mulai ? substr($item->waktu_mulai, 0, 5) : '' }}</div>
                        @endif
                        @if($item->tanggal_selesai)
                            <div>Selesai: {{ date('d/m/Y', strtotime($item->tanggal_selesai)) }} {{ $item->waktu_selesai ? substr($item->waktu_selesai, 0, 5) : '' }}</div>
                        @endif
                    </td>
                    <td class="text-center" style="font-size: 7pt;">
                        <strong>{{ $item->status }}</strong>
                    </td>
                    <td class="text-left" style="font-size: 7pt;">{!! $item->hasil_kerja ? nl2br(e(trim(strip_tags(html_entity_decode((string)$item->hasil_kerja, ENT_QUOTES | ENT_HTML5, 'UTF-8'))))) : '-' !!}</td>
                    <td class="text-left" style="font-size: 7pt;">{!! $item->rencana_tindak_lanjut ? nl2br(e(trim(strip_tags(html_entity_decode((string)$item->rencana_tindak_lanjut, ENT_QUOTES | ENT_HTML5, 'UTF-8'))))) : '-' !!}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center" style="padding: 15px; color: #777777;">
                        Tidak ada data kegiatan.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @include('pages.partials.pdf-signature')

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
