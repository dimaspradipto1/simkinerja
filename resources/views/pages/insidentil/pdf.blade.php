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
        <tr>
            <td class="meta-label">PERIODE AKADEMIK</td>
            <td class="meta-sep">:</td>
            <td><strong>{{ $periodeText }}</strong></td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 3%;">NO</th>
                <th style="width: 6%;">HARI</th>
                <th style="width: 22%;">URAIAN TUGAS</th>
                <th style="width: 12%;">REKAN KERJA</th>
                <th style="width: 7%;">EST. TGL MULAI</th>
                <th style="width: 5%;">EST. JAM MULAI</th>
                <th style="width: 7%;">EST. TGL SELESAI</th>
                <th style="width: 5%;">EST. JAM SELESAI</th>
                <th style="width: 7%;">TGL MULAI</th>
                <th style="width: 5%;">WAKTU MULAI</th>
                <th style="width: 7%;">TGL SELESAI</th>
                <th style="width: 5%;">WAKTU SELESAI</th>
                <th style="width: 8%;">STATUS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $item->hari ?? '-' }}</td>
                    <td class="text-left">
                        {{ $item->uraian_tugas }}
                    </td>
                    <td class="text-left" style="font-size: 7pt;">
                        {{ $item->taggedUsers && $item->taggedUsers->count() > 0 ? implode(', ', $item->taggedUsers->pluck('name')->toArray()) : '-' }}
                    </td>
                    <td class="text-center">{{ $item->estimasi_tanggal_mulai ? date('d/m/Y', strtotime($item->estimasi_tanggal_mulai)) : '-' }}</td>
                    <td class="text-center">{{ $item->estimasi_jam_mulai ? substr($item->estimasi_jam_mulai, 0, 5) . ' WIB' : '-' }}</td>
                    <td class="text-center">{{ $item->estimasi_tanggal_selesai ? date('d/m/Y', strtotime($item->estimasi_tanggal_selesai)) : '-' }}</td>
                    <td class="text-center">{{ $item->estimasi_jam_selesai ? substr($item->estimasi_jam_selesai, 0, 5) . ' WIB' : '-' }}</td>
                    <td class="text-center">{{ $item->tanggal_mulai ? date('d/m/Y', strtotime($item->tanggal_mulai)) : '-' }}</td>
                    <td class="text-center">{{ $item->waktu_mulai ? substr($item->waktu_mulai, 0, 5) . ' WIB' : '-' }}</td>
                    <td class="text-center">{{ $item->tanggal_selesai ? date('d/m/Y', strtotime($item->tanggal_selesai)) : '-' }}</td>
                    <td class="text-center">{{ $item->waktu_selesai && $item->waktu_selesai !== '00:00:00' ? substr($item->waktu_selesai, 0, 5) . ' WIB' : '-' }}</td>
                    <td class="text-center">{{ $item->status ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="13" class="text-center" style="padding: 15px; color: #777777;">
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
