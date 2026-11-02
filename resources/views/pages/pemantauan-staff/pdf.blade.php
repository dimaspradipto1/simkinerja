<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pemantauan Staff - {{ $namaBulanIndonesia }} {{ $tahun }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 8pt;
            color: #333333;
            margin: 10px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #15432D;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header h2 {
            margin: 0;
            color: #15432D;
            font-size: 13pt;
            text-transform: uppercase;
        }
        .header h4 {
            margin: 3px 0 0 0;
            color: #4b5563;
            font-size: 9.5pt;
            font-weight: normal;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 10px;
            font-size: 8pt;
        }
        .meta-table td {
            padding: 2px 4px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #cbd5e1;
            padding: 5px 6px;
        }
        table.data-table th {
            background-color: #15432D;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            font-size: 7.5pt;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-start { text-align: left; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .footer-sign {
            margin-top: 25px;
            width: 100%;
        }
        .footer-sign td {
            text-align: center;
            font-size: 8pt;
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>UNIVERSITAS IBNU SINA</h2>
        <h4>LAPORAN PEMANTAUAN KINERJA STAFF &amp; TIM</h4>
    </div>

    <table class="meta-table">
        <tr>
            <td style="width: 120px;" class="fw-bold">Periode Pemantauan</td>
            <td>: {{ $namaBulanIndonesia }} {{ $tahun }}</td>
            <td style="width: 100px;" class="fw-bold">Tanggal Cetak</td>
            <td style="width: 150px;">: {{ now()->translatedFormat('d F Y, H:i') }} WIB</td>
        </tr>
        <tr>
            <td class="fw-bold">Unit Kerja</td>
            <td>: {{ $selectedUnit ?: 'Seluruh Unit Kerja Universitas' }}</td>
            <td class="fw-bold">Total Staf</td>
            <td>: {{ count($pegawaiData) }} Orang</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 25px;">No</th>
                <th rowspan="2" class="text-start">Nama Pegawai &amp; Jabatan</th>
                <th rowspan="2" style="width: 130px;">Unit Kerja</th>
                <th colspan="2">Bulan {{ $namaBulanIndonesia }} {{ $tahun }}</th>
                <th colspan="4">Tahun Kinerja Berjalan ({{ $tahun }})</th>
            </tr>
            <tr>
                <th style="width: 65px;">Jum. Kegiatan</th>
                <th style="width: 70px;">Jam Kerja</th>
                <th style="width: 50px;">Total</th>
                <th style="width: 60px;">Rutin</th>
                <th style="width: 65px;">Kepanitiaan</th>
                <th style="width: 60px;">Insidental</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pegawaiData as $index => $row)
                @php
                    $pegawai = $row['pegawai'];
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-start">
                        <span class="fw-bold">{{ $pegawai->name }}</span>
                        <div style="font-size: 7pt; color: #64748b;">{{ $pegawai->jabatan ?: 'Staf' }}</div>
                    </td>
                    <td>{{ $pegawai->unit ?: 'Universitas' }}</td>
                    <td class="text-center fw-bold">{{ $row['jum_kegiatan_bulan'] }}</td>
                    <td class="text-center">{{ number_format($row['jam_kerja_bulan'], 1, ',', '.') }} Jam</td>
                    <td class="text-center fw-bold">{{ $row['total_tahunan'] }}</td>
                    <td class="text-center">{{ $row['rutin_tahunan'] }}</td>
                    <td class="text-center">{{ $row['kepanitiaan_tahunan'] }}</td>
                    <td class="text-center">{{ $row['insidentil_tahunan'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 20px;">Tidak ada data staf untuk kriteria ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="footer-sign">
        <tr>
            <td style="width: 60%;"></td>
            <td>
                Batam, {{ now()->translatedFormat('d F Y') }}<br>
                Mengetahui,<br>
                <strong>Pimpinan Universitas / Unit Kerja</strong>
                <br><br><br><br>
                <u><strong>{{ auth()->user()->name }}</strong></u><br>
                <span>NUPTK/NUP: {{ auth()->user()->nidn ?: '-' }}</span>
            </td>
        </tr>
    </table>

</body>
</html>

