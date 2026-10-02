<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kinerja Pegawai - Universitas Ibnu Sina</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm 8mm 10mm 8mm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 7.5pt;
            color: #222222;
            margin: 0;
            padding: 0;
        }
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .kop-table td {
            vertical-align: middle;
            padding: 0;
        }
        .kop-logo {
            width: 75px;
            text-align: left;
        }
        .kop-text {
            text-align: center;
            padding-right: 75px;
        }
        .kop-line {
            border-top: 2.5px solid #15432D;
            border-bottom: 1px solid #15432D;
            height: 2px;
            margin-bottom: 8px;
        }
        .report-title {
            background-color: #15432D;
            color: #ffffff;
            text-align: center;
            font-weight: bold;
            font-size: 10.5pt;
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
        }
        .meta-info td {
            padding: 2px 4px;
        }
        .section-heading {
            font-size: 8.5pt;
            font-weight: bold;
            color: #15432D;
            margin-top: 10px;
            margin-bottom: 4px;
            border-bottom: 1.5px solid #15432D;
            padding-bottom: 2px;
            text-transform: uppercase;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 7pt;
        }
        table.data-table th {
            background-color: #15432D;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            padding: 4px 3px;
            border: 1px solid #0d2c1d;
            font-size: 6.8pt;
        }
        table.data-table td {
            border: 1px solid #cccccc;
            padding: 3px;
            vertical-align: middle;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .badge {
            display: inline-block;
            padding: 1px 4px;
            font-size: 6.5pt;
            border-radius: 2px;
            font-weight: bold;
        }
        .page-break {
            page-break-before: always;
        }
        .footer-note {
            position: fixed;
            bottom: -5mm;
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

    <!-- KOP RESMI YAYASAN & UNIVERSITAS IBNU SINA -->
    <table class="kop-table">
        <tr>
            <td class="kop-logo">
                @if(file_exists(public_path('assets/img/logouis.png')))
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('assets/img/logouis.png'))) }}" style="height: 65px; width: auto;">
                @endif
            </td>
            <td class="kop-text">
                <div style="font-size: 11pt; font-weight: bold; color: #15432D; text-transform: uppercase;">
                    YAYASAN PENDIDIKAN IBNU SINA BATAM (YAPISTA)
                </div>
                <div style="font-size: 8pt; font-style: italic; color: #555555; margin-bottom: 2px;">
                    AVICENNA EDUCATIONAL FOUNDATION
                </div>
                <div style="font-size: 15pt; font-weight: bold; color: #15432D; text-transform: uppercase; letter-spacing: 0.5px;">
                    UNIVERSITAS IBNU SINA
                </div>
                <div style="font-size: 7.5pt; color: #333333;">
                    Jalan Teuku Umar, Kec. Lubuk Baja. Lubuk Baja Kota Telp. (0778) 4881714 / 408 3113
                </div>
                <div style="font-size: 7.5pt; color: #333333;">
                    Email: yapista.ibnusina@gmail.com &bull; Sekretariat: yapista@ibnusinabatam.com &bull; Website: uis.ac.id
                </div>
                <div style="font-size: 7.5pt; font-weight: bold; color: #15432D;">
                    KOTA BATAM &ndash; INDONESIA
                </div>
            </td>
        </tr>
    </table>
    <div class="kop-line"></div>

    <div class="report-title">
        LAPORAN KINERJA PEGAWAI UNIVERSITAS IBNU SINA
    </div>

    <table class="meta-info">
        <tr>
            <td style="width: 120px;"><strong>Periode Akademik</strong></td>
            <td style="width: 10px;">:</td>
            <td><strong>{{ $selectedPeriode ? strtoupper($selectedPeriode->nama_periode) : 'SEMUA PERIODE' }}</strong></td>
            <td style="width: 120px;"><strong>Total Pegawai</strong></td>
            <td style="width: 10px;">:</td>
            <td><strong>{{ $totalPegawai }} Orang ({{ $totalDosen }} Dosen, {{ $totalTendik }} Tendik)</strong></td>
        </tr>
        <tr>
            <td><strong>Unit Kerja</strong></td>
            <td>:</td>
            <td><strong>{{ $selectedUnit ? strtoupper($selectedUnit) : 'SEMUA UNIT KERJA' }}</strong></td>
            <td><strong>Rata-Rata Capaian</strong></td>
            <td>:</td>
            <td><strong>{{ $persentaseRataRata }}% (Total {{ $totalSelesaiSemua }} Selesai / {{ $totalTugasSemua }} Tugas)</strong></td>
        </tr>
    </table>

    <!-- BAGIAN 1: REKAPITULASI PEGAWAI -->
    <div class="section-heading">I. Rekapitulasi Jumlah Pegawai Berdasarkan Unit Kerja & Bidang Tugas</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th class="text-left">Unit Kerja</th>
                <th class="text-left">Bidang Tugas Utama</th>
                <th style="width: 70px;">Jumlah Dosen</th>
                <th style="width: 70px;">Jumlah Tendik</th>
                <th style="width: 70px;">Total Pegawai</th>
                <th style="width: 70px;">Rasio Dosen/Tendik</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rekapUnit as $idx => $r)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td><strong>{{ $r['unit'] }}</strong></td>
                    <td>{{ $r['bidang'] }}</td>
                    <td class="text-center">{{ $r['dosen'] }}</td>
                    <td class="text-center">{{ $r['tendik'] }}</td>
                    <td class="text-center"><strong>{{ $r['total'] }}</strong></td>
                    <td class="text-center">{{ $r['tendik'] > 0 ? round($r['dosen'] / $r['tendik'], 1) . ' : 1' : $r['dosen'] . ' : 0' }}</td>
                </tr>
            @endforeach
            <tr style="background-color: #f2f2f2; font-weight: bold;">
                <td colspan="3" class="text-center">TOTAL KESELURUHAN:</td>
                <td class="text-center">{{ $totalDosen }}</td>
                <td class="text-center">{{ $totalTendik }}</td>
                <td class="text-center">{{ $totalPegawai }}</td>
                <td class="text-center">-</td>
            </tr>
        </tbody>
    </table>

    <div class="page-break"></div>

    <!-- BAGIAN 2: CAPAIAN KINERJA PEGAWAI -->
    <div class="section-heading">II. Capaian Kinerja Pegawai Berdasarkan Tugas & Tanggung Jawab</div>
    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 18px;">No</th>
                <th rowspan="2" class="text-left" style="width: 130px;">Nama Pegawai</th>
                <th rowspan="2" style="width: 60px;">NIDN/NUP</th>
                <th rowspan="2" class="text-left" style="width: 90px;">Unit Kerja</th>
                <th rowspan="2" class="text-left">Jabatan</th>
                <th colspan="4">Realisasi Tugas (Selesai / Total)</th>
                <th rowspan="2" style="width: 50px;">Total Selesai</th>
                <th rowspan="2" style="width: 42px;">% Capaian</th>
                <th rowspan="2" style="width: 60px;">Predikat</th>
            </tr>
            <tr>
                <th style="width: 52px;">Rencana Kerja</th>
                <th style="width: 45px;">Kegiatan</th>
                <th style="width: 52px;">Kepanitiaan</th>
                <th style="width: 45px;">Insidentil</th>
            </tr>
        </thead>
        <tbody>
            @foreach($capaianPegawai as $idx => $p)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td><strong>{{ $p['name'] }}</strong></td>
                    <td class="text-center">{{ $p['nidn'] }}</td>
                    <td>{{ $p['unit'] }}</td>
                    <td>{{ $p['jabatan'] }}</td>
                    <td class="text-center">{{ $p['tasks']['rk_selesai'] }}/{{ $p['tasks']['rk_total'] }}</td>
                    <td class="text-center">{{ $p['tasks']['kg_selesai'] }}/{{ $p['tasks']['kg_total'] }}</td>
                    <td class="text-center">{{ $p['tasks']['kp_selesai'] }}/{{ $p['tasks']['kp_total'] }}</td>
                    <td class="text-center">{{ $p['tasks']['in_selesai'] }}/{{ $p['tasks']['in_total'] }}</td>
                    <td class="text-center"><strong>{{ $p['tasks']['selesai'] }}</strong>/{{ $p['tasks']['total'] }}</td>
                    <td class="text-center"><strong>{{ $p['persentase'] }}%</strong></td>
                    <td class="text-center">{{ $p['predikat'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="page-break"></div>

    <!-- BAGIAN 3: EVALUASI KINERJA DOSEN -->
    <div class="section-heading">III. Evaluasi Kinerja Dosen (Tri Dharma Perguruan Tinggi)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th class="text-left" style="width: 150px;">Nama Dosen</th>
                <th style="width: 75px;">NIDN</th>
                <th class="text-left">Unit / Program Studi</th>
                <th style="width: 100px;">Pendidikan & Pengajaran</th>
                <th style="width: 100px;">Penelitian & Publikasi</th>
                <th style="width: 100px;">Pengabdian Masyarakat</th>
                <th style="width: 55px;">% Capaian</th>
                <th style="width: 90px;">Status Tri Dharma</th>
            </tr>
        </thead>
        <tbody>
            @forelse($evaluasiDosen as $idx => $ed)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td><strong>{{ $ed['name'] }}</strong></td>
                    <td class="text-center">{{ $ed['nidn'] }}</td>
                    <td>{{ $ed['unit'] }}</td>
                    <td class="text-center">{{ $ed['dharma']['pendidikan_selesai'] }} / {{ $ed['dharma']['pendidikan_total'] }}</td>
                    <td class="text-center">{{ $ed['dharma']['penelitian_selesai'] }} / {{ $ed['dharma']['penelitian_total'] }}</td>
                    <td class="text-center">{{ $ed['dharma']['pkm_selesai'] }} / {{ $ed['dharma']['pkm_total'] }}</td>
                    <td class="text-center"><strong>{{ $ed['persentase'] }}%</strong></td>
                    <td class="text-center">{{ $ed['status_tri_dharma'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center py-2">Belum ada data evaluasi dosen.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- BAGIAN 4: PERMASALAHAN & LANGKAH PENYELESAIAN -->
    <div class="section-heading">IV. Permasalahan / Kendala yang Dihadapi & Langkah Penyelesaian</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th class="text-left" style="width: 130px;">Pegawai & Unit</th>
                <th style="width: 75px;">Jenis Tugas</th>
                <th class="text-left" style="width: 160px;">Uraian Tugas</th>
                <th class="text-left">Permasalahan / Kendala</th>
                <th class="text-left">Langkah Penyelesaian</th>
                <th style="width: 65px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($kendalaList as $idx => $k)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td><strong>{{ $k['user'] }}</strong><br><span style="font-size: 6.5pt; color: #555;">{{ $k['unit'] }}</span></td>
                    <td class="text-center">{{ $k['jenis'] }}</td>
                    <td>{{ strip_tags($k['tugas']) }}</td>
                    <td style="color: #900;">{{ strip_tags($k['kendala']) }}</td>
                    <td style="color: #060;">{{ strip_tags($k['solusi']) }}</td>
                    <td class="text-center">{{ $k['status'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-2">Tidak ada kendala kritis pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- BAGIAN 5: RENCANA TINDAK LANJUT -->
    <div class="section-heading">V. Rencana Tindak Lanjut Peningkatan Kinerja, Disiplin & Produktivitas</div>
    <div style="font-size: 7.5pt; line-height: 1.4; margin-bottom: 8px;">
        Berdasarkan hasil evaluasi kinerja periode {{ $selectedPeriode ? $selectedPeriode->nama_periode : 'berjalan' }}, disusun rencana tindak lanjut institusional:
        <ol style="margin: 4px 0 8px 15px; padding: 0;">
            <li><strong>Peningkatan Kinerja & Produktivitas:</strong> Mendorong percepatan penyelesaian tugas operasional berbasis target milestone dan optimalisasi pelaporan berkala melalui SIM Kinerja.</li>
            <li><strong>Penguatan Disiplin:</strong> Pengetatan kepatuhan estimasi waktu penyelesaian tugas serta monitoring kehadiran/presensi secara konsisten.</li>
            <li><strong>Peningkatan Kompetensi:</strong> Memfasilitasi keikutsertaan pelatihan kompetensi bersertifikat, akselerasi publikasi ilmiah bereputasi (SINTA/Scopus), serta pengajuan kenaikan jabatan fungsional dosen secara berkala.</li>
        </ol>
    </div>

    @include('pages.partials.pdf-signature')

    <div class="footer-note">
        Dokumen ini diterbitkan secara resmi melalui Sistem Informasi Manajemen Kinerja (SIM Kinerja) Universitas Ibnu Sina Batam. Dicetak pada: {{ date('d/m/Y H:i:s') }}.
    </div>

</body>
</html>
