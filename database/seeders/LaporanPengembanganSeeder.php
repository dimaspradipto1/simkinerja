<?php

namespace Database\Seeders;

use App\Models\LaporanPengembangan;
use App\Models\PeriodeAkademik;
use App\Models\User;
use Illuminate\Database\Seeder;

class LaporanPengembanganSeeder extends Seeder
{
    public function run(): void
    {
        $periode = PeriodeAkademik::latest()->first();
        $admin = User::where('email', 'admin@uis.ac.id')->orWhere('email', 'superadmin@uis.ac.id')->first();

        LaporanPengembangan::updateOrCreate(
            ['judul' => 'Laporan Pencapaian Pengembangan Universitas Ibnu Sina Tahun 2026'],
            [
                'periode_akademik_id' => $periode ? $periode->id : null,
                'tahun_akademik' => '2026/2027',
                'capaian_program_kerja' => "Pada periode ini, Universitas Ibnu Sina (UIS) telah merealisasikan 92% program kerja strategis yang ditetapkan oleh Rektorat dan Yayasan Pendidikan Ibnu Sina Batam (YAPISTA).\nFokus pencapaian meliputi peningkatan akreditasi seluruh program studi menuju Baik Sekali dan Unggul, akselerasi studi doktor (S3) dosen, penambahan kuota penerimaan mahasiswa baru, hilirisasi hasil riset terapan bersama industri kawasan Batam, serta digitalisasi tata kelola terintegrasi.",
                'data_mahasiswa' => [
                    'target_pmb' => 1250,
                    'realisasi_pmb' => 1180,
                    'total_aktif' => 4620,
                    'tingkat_retensi' => 93.4,
                    'jumlah_lulusan' => 710,
                    'ipk_rata_rata' => '3.48',
                    'catatan' => 'Peningkatan peminat prodi Teknik Informatika dan Manajemen melonjak 18%. Beasiswa KIP-Kuliah dan Yayasan terserap 100%.'
                ],
                'data_prodi' => [
                    ['nama' => 'Teknik Informatika', 'jenjang' => 'S1', 'akreditasi' => 'Baik Sekali', 'lembaga' => 'LAM INFOKOM', 'kadaluarsa' => '2028-08-30', 'status' => 'Persiapan Akreditasi Unggul & Kelas Internasional'],
                    ['nama' => 'Sistem Informasi', 'jenjang' => 'S1', 'akreditasi' => 'Baik Sekali', 'lembaga' => 'LAM INFOKOM', 'kadaluarsa' => '2027-10-15', 'status' => 'Peningkatan Kurikulum OBE & Lab IoT'],
                    ['nama' => 'Teknik Industri', 'jenjang' => 'S1', 'akreditasi' => 'Baik Sekali', 'lembaga' => 'LAM TEKNIK', 'kadaluarsa' => '2028-05-20', 'status' => 'Kerjasama Magang Industri Galangan Kapal & Manufaktur Batam'],
                    ['nama' => 'Kesehatan Masyarakat', 'jenjang' => 'S1', 'akreditasi' => 'Baik Sekali', 'lembaga' => 'LAM-PTKes', 'kadaluarsa' => '2028-12-10', 'status' => 'Pengembangan Peminatan K3 Industri Maritim'],
                    ['nama' => 'Manajemen', 'jenjang' => 'S1', 'akreditasi' => 'Baik Sekali', 'lembaga' => 'LAMEMBA', 'kadaluarsa' => '2028-03-12', 'status' => 'Pengembangan Inkubator Bisnis & Kewirausahaan Mahasiswa'],
                    ['nama' => 'Akuntansi', 'jenjang' => 'S1', 'akreditasi' => 'Baik Sekali', 'lembaga' => 'LAMEMBA', 'kadaluarsa' => '2027-06-25', 'status' => 'Pemberian Sertifikasi Kompetensi Teknisi Akuntansi'],
                    ['nama' => 'Ilmu Hukum', 'jenjang' => 'S1', 'akreditasi' => 'Baik Sekali', 'lembaga' => 'BAN-PT', 'kadaluarsa' => '2028-09-01', 'status' => 'Penguatan Pos Bantuan Hukum (Posbakum) Mahasiswa'],
                    ['nama' => 'Magister Manajemen (S2)', 'jenjang' => 'S2', 'akreditasi' => 'Baik', 'lembaga' => 'LAMEMBA', 'kadaluarsa' => '2029-01-15', 'status' => 'Prodi Pascasarjana Baru Berjalan Efektif'],
                ],
                'data_prestasi' => [
                    'internasional' => 4,
                    'nasional' => 18,
                    'wilayah' => 25,
                    'keterangan' => 'Juara 1 Lomba Inovasi Teknologi Tepat Guna Tingkat Nasional, Medali Emas Kompetisi Robotika Wilayah Sumatera, serta Best Paper pada International Conference on Sustainable Technology.'
                ],
                'data_sdm' => [
                    'dosen_s2' => 84,
                    'dosen_s3' => 26,
                    'dosen_studi_s3' => 14,
                    'dosen_serdos' => 58,
                    'dosen_asisten_ahli' => 38,
                    'dosen_lektor' => 46,
                    'dosen_lektor_kepala' => 18,
                    'dosen_guru_besar' => 4,
                    'tendik_tetap' => 42,
                    'tendik_kontrak' => 16,
                    'tendik_sertifikasi' => 22,
                    'pelatihan' => 'Workshop Penulisan Proposal Hibah Dikti, Bimtek Kurikulum Berbasis OBE, Sertifikasi Pengadaan Barang/Jasa untuk Tendik, dan Pelatihan Asesor Kompetensi BNSP.'
                ],
                'data_penelitian_pkm' => [
                    'publikasi_scopus' => 22,
                    'publikasi_sinta' => 64,
                    'hki_paten' => 19,
                    'buku' => 15,
                    'dana_penelitian' => 'Rp 580.000.000',
                    'dana_pkm' => 'Rp 310.000.000'
                ],
                'data_kerjasama' => [
                    'mitra_pemerintah' => 12,
                    'mitra_dudi' => 34,
                    'mitra_pt' => 18,
                    'mitra_internasional' => 6,
                    'realisasi' => 'Kerjasama magang bersertifikat dan rekrutmen lulusan dengan kawasan industri Mukakuning & Kabil, riset bersama Pemko Batam terkait penataan pesisir, serta pertukaran mahasiswa dengan Universitas di Malaysia.'
                ],
                'data_sarpras' => [
                    'sarana_fisik' => 'Perluasan Gedung Rektorat dan Perkuliahan Terpadu, renovasi laboratorium CNC dan Robotika, serta penambahan fasilitas Creative Hub & Co-working Space mahasiswa.',
                    'it_digital' => 'Upgrade backbone fiber optic antar gedung menjadi 1 Gbps, implementasi server cloud cadangan, serta penerapan SIM Kinerja dan Sistem Presensi QR Dinamis.'
                ],
                'kendala_solusi' => "1. Kendala masa tunggu akreditasi instrumen baru LAM yang membutuhkan pemenuhan luaran publikasi secara cepat.\nSolusi: Diberikan reward insentif khusus bagi dosen yang berhasil publish artikel di jurnal Scopus Q1-Q3 dan SINTA 2.\n2. Tantangan persaingan penerimaan mahasiswa baru di era digital.\nSolusi: Mengoptimalkan digital marketing, program referral alumni, dan kerjasama beasiswa kemitraan industri.",
                'tindak_lanjut' => "1. Pembukaan prodi baru Magister Teknik Informatika (S2) dan Rekayasa Perangkat Lunak.\n2. Akselerasi peningkatan 8 dosen berkualifikasi lektor kepala menjadi Guru Besar (Profesor).\n3. Pembangunan Gedung Laboratorium Terpadu tahap II untuk menunjang riset berstandar internasional.",
                'created_by' => $admin ? $admin->id : null,
                'updated_by' => $admin ? $admin->id : null,
            ]
        );
    }
}
