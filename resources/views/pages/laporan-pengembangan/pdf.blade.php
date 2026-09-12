<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $laporan->judul }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 12mm 15mm 12mm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8.5pt;
            color: #222222;
            line-height: 1.4;
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
            margin-bottom: 12px;
        }
        .report-title {
            background-color: #15432D;
            color: #ffffff;
            text-align: center;
            font-weight: bold;
            font-size: 11pt;
            padding: 8px;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 8.5pt;
        }
        .meta-table td {
            padding: 2px 4px;
        }
        .section-title {
            font-size: 9.5pt;
            font-weight: bold;
            color: #15432D;
            border-bottom: 1.5px solid #15432D;
            padding-bottom: 3px;
            margin-top: 14px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 8pt;
        }
        table.data-table th {
            background-color: #15432D;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            padding: 5px 4px;
            border: 1px solid #0d2c1d;
            font-size: 7.5pt;
        }
        table.data-table td {
            border: 1px solid #cccccc;
            padding: 4px;
            vertical-align: middle;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .page-break {
            page-break-before: always;
        }
        .footer-note {
            position: fixed;
            bottom: -8mm;
            left: 0;
            right: 0;
            font-size: 7pt;
            color: #777777;
            border-top: 0.5px solid #dddddd;
            padding-top: 3px;
        }
    </style>
</head>
<body>

    @php
        $mhs = $laporan->data_mahasiswa ?? [];
        $prodiList = $laporan->data_prodi ?? [];
        $prestasi = $laporan->data_prestasi ?? [];
        $sdm = $laporan->data_sdm ?? [];
        $riset = $laporan->data_penelitian_pkm ?? [];
        $kerjasama = $laporan->data_kerjasama ?? [];
        $sarpras = $laporan->data_sarpras ?? [];
    @endphp

    <!-- KOP RESMI YAYASAN & UNIVERSITAS IBNU SINA -->
    <table class="kop-table">
        <tr>
            <td class="kop-logo">
                @if(file_exists(public_path('assets/img/logouis.png')))
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('assets/img/logouis.png'))) }}" style="height: 68px; width: auto;">
                @endif
            </td>
            <td class="kop-text">
                <div style="font-size: 11pt; font-weight: bold; color: #15432D; text-transform: uppercase;">
                    YAYASAN PENDIDIKAN IBNU SINA BATAM (YAPISTA)
                </div>
                <div style="font-size: 8pt; font-style: italic; color: #555555; margin-bottom: 2px;">
                    AVICENNA EDUCATIONAL FOUNDATION
                </div>
                <div style="font-size: 16pt; font-weight: bold; color: #15432D; text-transform: uppercase; letter-spacing: 0.5px;">
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
        {{ strtoupper($laporan->judul) }}
    </div>

    <table class="meta-table">
        <tr>
            <td style="width: 140px;"><strong>Institusi Penyelenggara</strong></td>
            <td style="width: 10px;">:</td>
            <td>Universitas Ibnu Sina (UIS) Batam</td>
        </tr>
        <tr>
            <td><strong>Badan Hukum Penyelenggara</strong></td>
            <td>:</td>
            <td>Yayasan Pendidikan Ibnu Sina Batam (YAPISTA)</td>
        </tr>
        <tr>
            <td><strong>Tahun Akademik / Periode</strong></td>
            <td>:</td>
            <td><strong>{{ $laporan->tahun_akademik ?: '-' }} ({{ $laporan->periodeAkademik ? $laporan->periodeAkademik->nama_periode : 'Periode Berjalan' }})</strong></td>
        </tr>
    </table>

    <!-- BAB I: CAPAIAN PROGRAM KERJA STRATEGIS -->
    <div class="section-title">BAB I. Capaian Program Kerja dan Target Pengembangan Universitas</div>
    <div style="text-align: justify; margin-bottom: 12px;">
        {!! nl2br(e($laporan->capaian_program_kerja ?: 'Capaian program kerja dan target pengembangan Universitas Ibnu Sina pada periode ini difokuskan pada peningkatan akreditasi program studi, akselerasi kualifikasi sumber daya manusia, penguatan luaran riset dan publikasi terindeks, perluasan jejaring kerjasama dunia usaha dan industri, serta digitalisasi tata kelola operasional berbasis SIM Kinerja.')) !!}
    </div>

    <!-- BAB II: PERKEMBANGAN MAHASISWA -->
    <div class="section-title">BAB II. Perkembangan Mahasiswa, PMB, Retensi, dan Kelulusan</div>
    <table class="data-table">
        <thead>
            <tr>
                <th class="text-left">Indikator Kemahasiswaan</th>
                <th style="width: 120px;">Target</th>
                <th style="width: 120px;">Realisasi</th>
                <th style="width: 120px;">Persentase (%)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $tPmb = (int)($mhs['target_pmb'] ?? 0);
                $rPmb = (int)($mhs['realisasi_pmb'] ?? 0);
                $pctPmb = $tPmb > 0 ? round(($rPmb / $tPmb) * 100, 1) : 0;
            @endphp
            <tr>
                <td>Penerimaan Mahasiswa Baru (PMB)</td>
                <td class="text-center">{{ number_format($tPmb) }} Mahasiswa</td>
                <td class="text-center"><strong>{{ number_format($rPmb) }} Mahasiswa</strong></td>
                <td class="text-center">{{ $pctPmb }}%</td>
            </tr>
            <tr>
                <td>Total Mahasiswa Aktif (Seluruh Jenjang)</td>
                <td class="text-center">-</td>
                <td class="text-center"><strong>{{ number_format((int)($mhs['total_aktif'] ?? 0)) }} Mahasiswa</strong></td>
                <td class="text-center">-</td>
            </tr>
            <tr>
                <td>Tingkat Retensi Mahasiswa (Keberlanjutan Studi)</td>
                <td class="text-center">85%</td>
                <td class="text-center"><strong>{{ $mhs['tingkat_retensi'] ?? 0 }}%</strong></td>
                <td class="text-center">Tercapai</td>
            </tr>
            <tr>
                <td>Jumlah Lulusan / Wisudawan Periode Ini</td>
                <td class="text-center">-</td>
                <td class="text-center"><strong>{{ number_format((int)($mhs['jumlah_lulusan'] ?? 0)) }} Orang</strong></td>
                <td class="text-center">-</td>
            </tr>
            <tr>
                <td>Rata-rata Indeks Prestasi Kumulatif (IPK) Lulusan</td>
                <td class="text-center">&ge; 3.25</td>
                <td class="text-center"><strong>{{ $mhs['ipk_rata_rata'] ?? '3.40' }}</strong></td>
                <td class="text-center">Sangat Baik</td>
            </tr>
        </tbody>
    </table>

    <div class="page-break"></div>

    <!-- BAB III: PROGRAM STUDI & AKREDITASI -->
    <div class="section-title">BAB III. Perkembangan Program Studi, Akreditasi, dan Capaian Prestasi</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th class="text-left">Program Studi</th>
                <th style="width: 55px;">Jenjang</th>
                <th style="width: 90px;">Akreditasi</th>
                <th style="width: 90px;">Lembaga</th>
                <th style="width: 80px;">Masa Berlaku</th>
                <th class="text-left">Status Pengembangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($prodiList as $idx => $p)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td><strong>{{ $p['nama'] ?? '-' }}</strong></td>
                    <td class="text-center">{{ $p['jenjang'] ?? 'S1' }}</td>
                    <td class="text-center"><strong>{{ $p['akreditasi'] ?? 'Baik' }}</strong></td>
                    <td class="text-center">{{ $p['lembaga'] ?? 'BAN-PT' }}</td>
                    <td class="text-center">{{ !empty($p['kadaluarsa']) ? date('d/m/Y', strtotime($p['kadaluarsa'])) : '-' }}</td>
                    <td>{{ $p['status'] ?? 'Aktif' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 6px; font-size: 8pt;">
        <strong>Capaian Prestasi Akademik & Non-Akademik:</strong>
        Internasional: <strong>{{ $prestasi['internasional'] ?? 0 }}</strong> | Nasional: <strong>{{ $prestasi['nasional'] ?? 0 }}</strong> | Wilayah: <strong>{{ $prestasi['wilayah'] ?? 0 }}</strong> prestasi.
        @if(!empty($prestasi['keterangan']))
            <br><em>Catatan:</em> {{ $prestasi['keterangan'] }}
        @endif
    </div>

    <!-- BAB IV: PERKEMBANGAN SDM -->
    <div class="section-title">BAB IV. Perkembangan Sumber Daya Manusia (SDM) Dosen & Tendik</div>
    <table class="data-table">
        <thead>
            <tr>
                <th class="text-left">Kategori SDM</th>
                <th style="width: 140px;">Rincian Kualifikasi / Jabatan</th>
                <th style="width: 90px;">Jumlah</th>
                <th class="text-left">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td rowspan="4"><strong>Dosen Tetap</strong></td>
                <td>Pendidikan S3 (Doktor)</td>
                <td class="text-center"><strong>{{ $sdm['dosen_s3'] ?? 0 }} Orang</strong></td>
                <td>Memenuhi standar pengajaran & riset</td>
            </tr>
            <tr>
                <td>Sedang Studi Lanjut S3</td>
                <td class="text-center"><strong>{{ $sdm['dosen_studi_s3'] ?? 0 }} Orang</strong></td>
                <td>Akselerasi kualifikasi doktor</td>
            </tr>
            <tr>
                <td>Pendidikan S2 (Magister)</td>
                <td class="text-center"><strong>{{ $sdm['dosen_s2'] ?? 0 }} Orang</strong></td>
                <td>Dosen berpendidikan magister</td>
            </tr>
            <tr>
                <td>Sertifikasi Pendidik (Serdos)</td>
                <td class="text-center"><strong>{{ $sdm['dosen_serdos'] ?? 0 }} Orang</strong></td>
                <td>Dosen bersertifikat profesional</td>
            </tr>
            <tr>
                <td rowspan="4"><strong>Jabatan Fungsional Dosen</strong></td>
                <td>Guru Besar (Profesor)</td>
                <td class="text-center"><strong>{{ $sdm['dosen_guru_besar'] ?? 0 }} Orang</strong></td>
                <td>Jabatan fungsional tertinggi</td>
            </tr>
            <tr>
                <td>Lektor Kepala</td>
                <td class="text-center"><strong>{{ $sdm['dosen_lektor_kepala'] ?? 0 }} Orang</strong></td>
                <td>Dosen senior & pembimbing riset</td>
            </tr>
            <tr>
                <td>Lektor</td>
                <td class="text-center"><strong>{{ $sdm['dosen_lektor'] ?? 0 }} Orang</strong></td>
                <td>Dosen pengajar mandiri</td>
            </tr>
            <tr>
                <td>Asisten Ahli</td>
                <td class="text-center"><strong>{{ $sdm['dosen_asisten_ahli'] ?? 0 }} Orang</strong></td>
                <td>Dosen pemula</td>
            </tr>
            <tr>
                <td rowspan="2"><strong>Tenaga Kependidikan (Tendik)</strong></td>
                <td>Tendik Tetap & Kontrak</td>
                <td class="text-center"><strong>{{ ((int)($sdm['tendik_tetap'] ?? 0)) + ((int)($sdm['tendik_kontrak'] ?? 0)) }} Orang</strong></td>
                <td>{{ $sdm['tendik_tetap'] ?? 0 }} Tetap, {{ $sdm['tendik_kontrak'] ?? 0 }} Kontrak</td>
            </tr>
            <tr>
                <td>Tendik Bersertifikasi Profesi</td>
                <td class="text-center"><strong>{{ $sdm['tendik_sertifikasi'] ?? 0 }} Orang</strong></td>
                <td>Pustakawan, Laboran, IT, Keuangan</td>
            </tr>
        </tbody>
    </table>

    <!-- BAB V: PENELITIAN, PKM & KERJASAMA -->
    <div class="section-title">BAB V. Capaian Penelitian, Pengabdian Masyarakat & Kerjasama</div>
    <table class="data-table">
        <thead>
            <tr>
                <th class="text-left">Bidang Capaian</th>
                <th style="width: 120px;">Jumlah / Nominal</th>
                <th class="text-left">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Publikasi Ilmiah Internasional Bereputasi (Scopus / WoS)</td>
                <td class="text-center"><strong>{{ $riset['publikasi_scopus'] ?? 0 }} Judul</strong></td>
                <td>Terbit di jurnal bereputasi global</td>
            </tr>
            <tr>
                <td>Publikasi Ilmiah Nasional Terakreditasi (SINTA)</td>
                <td class="text-center"><strong>{{ $riset['publikasi_sinta'] ?? 0 }} Judul</strong></td>
                <td>Jurnal terakreditasi SINTA 1-4</td>
            </tr>
            <tr>
                <td>Hak Kekayaan Intelektual (HKI / Paten / Hak Cipta)</td>
                <td class="text-center"><strong>{{ $riset['hki_paten'] ?? 0 }} Sertifikat</strong></td>
                <td>Tercatat resmi di DJKI Kemenkumham</td>
            </tr>
            <tr>
                <td>Buku Ajar / Referensi Ber-ISBN</td>
                <td class="text-center"><strong>{{ $riset['buku'] ?? 0 }} Judul</strong></td>
                <td>Karya buku dosen ber-ISBN</td>
            </tr>
            <tr>
                <td>Total Pendanaan Hibah Penelitian & PkM</td>
                <td class="text-center"><strong>{{ $riset['dana_penelitian'] ?: 'Rp 0' }}</strong></td>
                <td>Riset: {{ $riset['dana_penelitian'] ?: 'Rp 0' }} &bull; PkM: {{ $riset['dana_pkm'] ?: 'Rp 0' }}</td>
            </tr>
            <tr>
                <td>Jejaring Kerjasama Aktif (Pemerintah, DUDI, PT)</td>
                <td class="text-center"><strong>{{ ((int)($kerjasama['mitra_pemerintah'] ?? 0)) + ((int)($kerjasama['mitra_dudi'] ?? 0)) + ((int)($kerjasama['mitra_pt'] ?? 0)) + ((int)($kerjasama['mitra_internasional'] ?? 0)) }} Mitra</strong></td>
                <td>DUDI: {{ $kerjasama['mitra_dudi'] ?? 0 }} &bull; Pemda: {{ $kerjasama['mitra_pemerintah'] ?? 0 }} &bull; PT: {{ ((int)($kerjasama['mitra_pt'] ?? 0)) + ((int)($kerjasama['mitra_internasional'] ?? 0)) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="page-break"></div>

    <!-- BAB VI: SARANA & PRASARANA -->
    <div class="section-title">BAB VI. Perkembangan Sarana dan Prasarana Pendukung Pendidikan</div>
    <div style="text-align: justify; margin-bottom: 12px;">
        <p><strong>1. Sarana Fisik & Laboratorium:</strong><br>
        {{ $sarpras['sarana_fisik'] ?: 'Penyediaan dan perawatan fasilitas ruang perkuliahan, laboratorium komputer, laboratorium teknik, dan laboratorium kesehatan masyarakat terus dioptimalkan guna menunjang kelancaran proses pembelajaran berbasis OBE.' }}
        </p>
        <p><strong>2. Infrastruktur IT & Transformasi Digital:</strong><br>
        {{ $sarpras['it_digital'] ?: 'Peningkatan kapasitas internet terintegrasi, server kampus berkecepatan tinggi, SIM Kinerja berbasis web, dan platform pembelajaran daring (e-learning).' }}
        </p>
    </div>

    <!-- BAB VII: KENDALA, SOLUSI & TINDAK LANJUT -->
    <div class="section-title">BAB VII. Kendala, Permasalahan Beserta Langkah Penyelesaian & Tindak Lanjut</div>
    <div style="text-align: justify; margin-bottom: 12px;">
        <p><strong>1. Kendala dan Permasalahan yang Dihadapi:</strong><br>
        {!! nl2br(e($laporan->kendala_solusi ?: 'Persaingan penerimaan mahasiswa baru, akselerasi studi doktor dosen yang memerlukan waktu, serta kebutuhan penguatan sarana laboratorium riset bertaraf internasional.')) !!}
        </p>
        <p><strong>2. Rencana Tindak Lanjut dan Strategi Pengembangan:</strong><br>
        {!! nl2br(e($laporan->tindak_lanjut ?: 'Memperkuat kerjasama strategis industri di kawasan Batam dan Kepulauan Riau, memberikan skema insentif publikasi ilmiah dan beasiswa studi lanjut bagi dosen, serta melanjutkan digitalisasi operasional secara menyeluruh.')) !!}
        </p>
    </div>

    <!-- LEMBAR PENGESAHAN DOKUMEN -->
    <table style="width: 100%; margin-top: 30px; border-collapse: collapse; font-size: 8.5pt;">
        <tr>
            <td style="width: 50%; text-align: center; vertical-align: top;">
                Menyetujui / Mengetahui,<br>
                <strong>Ketua Yayasan Pendidikan Ibnu Sina Batam</strong>
                <br><br><br><br><br>
                <strong><u>( H. Andi Ibrahim, B.A. )</u></strong>
            </td>
            <td style="width: 50%; text-align: center; vertical-align: top;">
                Batam, {{ date('d F Y') }}<br>
                <strong>Rektor Universitas Ibnu Sina</strong>
                <br><br><br><br><br>
                <strong><u>( Assoc. Prof. Dr. Ir. Larisang, M.T., IPU )</u></strong><br>
                NIDN. 1010046901
            </td>
        </tr>
    </table>

    <div class="footer-note">
        Laporan Pencapaian Pengembangan Universitas Ibnu Sina diterbitkan secara resmi melalui SIM Kinerja UIS. Dicetak pada: {{ date('d/m/Y H:i:s') }}.
    </div>

</body>
</html>
