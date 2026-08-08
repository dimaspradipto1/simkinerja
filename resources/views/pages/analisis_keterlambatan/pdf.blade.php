<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Analisis Kendala dan Keterlambatan Kinerja Staff</title>
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
            background-color: #8B0000;
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
            background-color: #8B0000;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            padding: 5px 3px;
            border: 1px solid #5a0000;
            font-size: 7pt;
        }
        .data-table td {
            border: 1px solid #cccccc;
            padding: 4px 3px;
            vertical-align: top;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .badge-danger { color: #b91c1c; font-weight: bold; }
        .badge-warning { color: #b45309; font-weight: bold; }
        .badge-info { color: #0369a1; font-weight: bold; }
        .badge-secondary { color: #4b5563; font-weight: bold; }
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
        LAPORAN ANALISIS KENDALA DAN KETERLAMBATAN KINERJA STAFF ({{ $periodeText }})
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
                <th style="width: 14%;">STAFF & JABATAN</th>
                <th style="width: 20%;">TUGAS UTAMA (TERLAMBAT)</th>
                <th style="width: 13%;">ESTIMASI VS REALISASI</th>
                <th style="width: 15%;">DIAGNOSTIK KENDALA</th>
                <th style="width: 20%;">PEKERJAAN BENTROK</th>
                <th style="width: 15%;">REKOMENDASI EVALUASI</th>
            </tr>
        </thead>
        <tbody>
            @forelse($lateTasks as $index => $item)
                @php
                    $insCount = $item->overlapping_insidentil ? $item->overlapping_insidentil->count() : 0;
                    $panCount = $item->overlapping_kepanitiaan ? $item->overlapping_kepanitiaan->count() : 0;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-left">
                        <strong>{{ $item->user ? $item->user->name : '-' }}</strong>
                        <div style="font-size: 6.5pt; color: #555555;">{{ $item->user ? ($item->user->jabatan ?? '-') : '-' }}</div>
                        <div style="font-size: 6.5pt; color: #555555;">Unit: {{ $item->user ? ($item->user->unit ?? '-') : '-' }}</div>
                    </td>
                    <td class="text-left">
                        <strong>{{ $item->uraian_tugas }}</strong>
                    </td>
                    <td class="text-center" style="font-size: 7pt;">
                        <div><strong>Est:</strong> {{ $item->estimasi_tanggal_selesai ? date('d/m/Y', strtotime($item->estimasi_tanggal_selesai)) : '-' }}</div>
                        <div><strong>Real:</strong> {{ $item->tanggal_selesai ? date('d/m/Y', strtotime($item->tanggal_selesai)) : 'Belum Selesai' }}</div>
                        <div style="color: #b91c1c; font-weight: bold; margin-top: 2px;">(Terlambat {{ $item->delay_days }} Hari)</div>
                    </td>
                    <td class="text-center" style="font-size: 7pt;">
                        @if($item->kategori_kendala === 'beban_ganda')
                            <span class="badge-warning">Beban Ganda (Panitia & Insidentil)</span>
                        @elseif($item->kategori_kendala === 'insidentil')
                            <span class="badge-danger">Terganggu Tugas Insidentil</span>
                        @elseif($item->kategori_kendala === 'kepanitiaan')
                            <span class="badge-info">Beban Tugas Kepanitiaan</span>
                        @else
                            <span class="badge-secondary">Keterlambatan Murni Staff</span>
                        @endif
                    </td>
                    <td class="text-left" style="font-size: 6.5pt; line-height: 1.3;">
                        @if($insCount === 0 && $panCount === 0)
                            <span style="color: #777777;">- Tidak Ada Bentrokan -</span>
                        @endif

                        @if($insCount > 0)
                            <div style="font-weight: bold; color: #b91c1c;">Insidentil ({{ $insCount }}):</div>
                            <ul style="margin: 0; padding-left: 12px;">
                                @foreach($item->overlapping_insidentil as $ins)
                                    <li>{{ $ins->uraian_tugas }}</li>
                                @endforeach
                            </ul>
                        @endif

                        @if($panCount > 0)
                            <div style="font-weight: bold; color: #0369a1; margin-top: 3px;">Kepanitiaan ({{ $panCount }}):</div>
                            <ul style="margin: 0; padding-left: 12px;">
                                @foreach($item->overlapping_kepanitiaan as $pan)
                                    <li>{{ $pan->nama_kegiatan ?? $pan->uraian_tugas }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </td>
                    <td class="text-left" style="font-size: 6.5pt; line-height: 1.3;">
                        @if($item->kategori_kendala === 'beban_ganda' || $item->kategori_kendala === 'insidentil')
                            Diperlukan redistribusi porsi tugas mendesak atau penyesuaian ulang estimasi deadline tugas utama.
                        @elseif($item->kategori_kendala === 'kepanitiaan')
                            Evaluasi porsi keterlibatan anggota panitia agar tidak mengganggu performa target rutin bulanan.
                        @else
                            Perlu pendampingan dan monitoring manajemen waktu pengerjaan tugas rutin staff.
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 15px; color: #777777;">
                        Tidak ada data keterlambatan pekerjaan yang ditemukan pada periode ini.
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
