<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Absensi Kinerja - Universitas Ibnu Sina</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 2.54cm;
        }

        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 8pt;
            color: #222;
            margin: 0;
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

        .letterhead-line {
            border-top: 2px solid #15432d;
            border-bottom: 1px solid #15432d;
            height: 2px;
            margin-bottom: 8px;
        }

        .title {
            background: #15432d;
            color: #fff;
            text-align: center;
            font-weight: bold;
            font-size: 10pt;
            padding: 7px;
            margin-bottom: 7px;
        }

        .metadata {
            width: 100%;
            border-collapse: collapse;
            background: #f8fafc;
            margin-bottom: 9px;
            font-size: 7.5pt;
        }

        .metadata td {
            border: 1px solid #dbe3ea;
            padding: 4px 6px;
        }

        .metadata .label {
            width: 15%;
            font-weight: bold;
            white-space: nowrap;
        }

        .metadata .value {
            width: 35%;
        }

        .target {
            font-weight: bold;
            color: #15432d;
        }

        .data-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            font-size: 7pt;
        }

        .data-table th {
            background: #15432d;
            color: #fff;
            border: 1px solid #0d2c1d;
            padding: 5px 3px;
            text-align: center;
            vertical-align: middle;
            font-size: 6.6pt;
        }

        .data-table td {
            border: 1px solid #cbd5e1;
            padding: 4px 3px;
            vertical-align: top;
            word-wrap: break-word;
        }

        .center {
            text-align: center;
        }

        .muted {
            color: #64748b;
        }

        .status {
            display: inline-block;
            border-radius: 3px;
            padding: 2px 4px;
            font-weight: bold;
            white-space: nowrap;
        }

        .status-good {
            color: #15803d;
            background: #dcfce7;
            border: 1px solid #86efac;
        }

        .status-low {
            color: #b45309;
            background: #fef3c7;
            border: 1px solid #fde68a;
        }

        .status-over {
            color: #b91c1c;
            background: #fee2e2;
            border: 1px solid #fca5a5;
        }

        .activity {
            margin-bottom: 4px;
            padding-bottom: 3px;
            border-bottom: 1px solid #e2e8f0;
        }

        .activity:last-child {
            border-bottom: 0;
            margin-bottom: 0;
        }

        .category {
            color: #0369a1;
            font-weight: bold;
        }

        .evaluation {
            margin-top: 3px;
            padding-top: 3px;
            border-top: 1px solid #e2e8f0;
            color: #475569;
        }

        .empty {
            text-align: center;
            color: #64748b;
            padding: 14px;
        }

        .footer {
            position: fixed;
            bottom: -1.4cm;
            left: 0;
            right: 0;
            border-top: 1px solid #cbd5e1;
            padding-top: 4px;
            color: #64748b;
            font-size: 6.5pt;
        }

        .page-number:after {
            content: counter(page);
        }

        .page-count:after {
            content: counter(pages);
        }
    </style>
</head>
<body>
    <table class="letterhead">
        <tr>
            <td class="logo">
                @if($logoData)
                    <img src="{{ $logoData }}" alt="Logo Universitas Ibnu Sina">
                @endif
            </td>
            <td class="institution">
                <div class="foundation">YAYASAN PENDIDIKAN IBNU SINA BATAM (YAPISTA)</div>
                <div class="university">UNIVERSITAS IBNU SINA (UIS)</div>
                <div class="contact">Jl. Teuku Umar Lubuk Baja, Kota Batam-Indonesia Telp. 0778-408 3113</div>
                <div class="contact">Email : <span style="color: #3333cc; text-decoration: underline;">info@uis.ac.id/ibnusina@gmail.com</span> Website : uis.ac.id</div>
            </td>
            <td class="logo"></td>
        </tr>
    </table>
    <div class="letterhead-line"></div>
    <div class="title">LAPORAN ABSENSI KINERJA HARIAN</div>

    <table class="metadata">
        <tr>
            <td class="label">Periode / Waktu</td>
            <td class="value">{{ $periodText }}</td>
            <td class="label">Pegawai</td>
            <td class="value">{{ $employeeText }}</td>
        </tr>
        <tr>
            <td class="label">Kategori</td>
            <td class="value">Semua kegiatan</td>
            <td class="label">Target Kerja</td>
            <td class="value target">7 jam kerja + 1 jam istirahat (total 8 jam)</td>
        </tr>
        @if($searchText !== '')
            <tr>
                <td class="label">Pencarian</td>
                <td colspan="3">{{ $searchText }}</td>
            </tr>
        @endif
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 3%;">No.</th>
                <th style="width: 10%;">Tanggal</th>
                <th style="width: 14%;">Pegawai / Jabatan</th>
                <th style="width: 8%;">Datang</th>
                <th style="width: 8%;">Pulang</th>
                <th style="width: 18%;">Total Kerja, Status &amp; Evaluasi</th>
                <th style="width: 15%;">Kategori</th>
                <th style="width: 24%;">Keterangan Kerja</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $index => $row)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ $row['tanggal'] }}</td>
                    <td><strong>{{ $row['nama'] }}</strong><br><span class="muted">{{ $row['jabatan'] }}</span></td>
                    <td class="center">{{ $row['datang_status'] }}<br>{{ $row['datang_waktu'] }}</td>
                    <td class="center">{{ $row['pulang_status'] }}<br>{{ $row['pulang_waktu'] }}</td>
                    <td>
                        <strong>{{ $row['total_durasi'] }}</strong>
                        <div class="muted">{{ $row['progress'] }} dari target 7 jam</div>
                        <div>
                            <span class="status {{ $row['status_kinerja'] === 'Bagus' ? 'status-good' : ($row['status_kinerja'] === 'Over' ? 'status-over' : 'status-low') }}">
                                {{ $row['status_kinerja'] }}
                            </span>
                        </div>
                        <div class="evaluation">{{ $row['evaluasi'] }}</div>
                    </td>
                    <td>
                        @forelse($row['kegiatan'] as $activity)
                            <div class="activity">{{ $activity['kategori'] }}</div>
                        @empty
                            <span class="muted">-</span>
                        @endforelse
                    </td>
                    <td>
                        @forelse($row['kegiatan'] as $activity)
                            <div class="activity">
                                <strong>{{ $activity['uraian'] }}</strong>
                                <div class="muted">Durasi: {{ $activity['durasi'] }}@if($activity['status']) · {{ $activity['status'] }}@endif</div>
                            </div>
                        @empty
                            <span class="muted">Tidak ada keterangan kegiatan.</span>
                        @endforelse
                    </td>
                </tr>
            @empty
                <tr>
                    <td class="empty" colspan="8">Tidak ada data absensi kinerja sesuai filter yang dipilih.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @include('pages.partials.pdf-signature', [
        'employeeName' => $signatureEmployeeName,
        'employeeLabel' => $signatureEmployeeLabel,
        'employeeNup' => $signatureEmployeeNup,
    ])

    <div class="footer">
        Dokumen diterbitkan otomatis oleh SIM KINERJA Universitas Ibnu Sina pada {{ $generatedAt }}.
        <span style="float: right;">Halaman <span class="page-number"></span> dari <span class="page-count"></span></span>
    </div>
</body>
</html>
