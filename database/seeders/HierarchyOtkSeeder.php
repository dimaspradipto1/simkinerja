<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HierarchyOtkSeeder extends Seeder
{
    /**
     * Run the database seeds according to OTK UIS 2024-2029.
     */
    public function run(): void
    {
        // 1. Sinkronisasi nama unit 'FAKULTAS TEKNIK' -> 'FAKULTAS SAINS DAN TEKNOLOGI'
        DB::statement("UPDATE users SET unit = 'FAKULTAS SAINS DAN TEKNOLOGI' WHERE unit = 'FAKULTAS TEKNIK' OR unit = 'FST'");
        DB::statement("UPDATE users SET unit = 'BAAK - FST' WHERE unit = 'BAAK - FT'");
        DB::statement("UPDATE users SET unit = 'BAUK - FST' WHERE unit = 'BAUK - FT'");
        DB::statement("UPDATE users SET unit = 'LABORATORIUM FST' WHERE unit = 'LABORATORIUM FT'");
        DB::statement("UPDATE users SET jabatan = REPLACE(jabatan, 'FAKULTAS TEKNIK', 'FAKULTAS SAINS DAN TEKNOLOGI') WHERE jabatan LIKE '%FAKULTAS TEKNIK%'");
        DB::statement("UPDATE users SET jabatan = REPLACE(jabatan, ' PUBLIKASI FT', ' PUBLIKASI FST') WHERE jabatan LIKE '% PUBLIKASI FT%'");

        // 2. Koreksi nama, jabatan, dan unit pegawai agar 100% presisi sesuai dokumen fisik OTK UIS
        $directCorrections = [
            4   => ['name' => 'FITRI SARI DEWI, SKM., M.KKK', 'jabatan' => 'WAKIL REKTOR I', 'unit' => 'REKTORAT'],
            5   => ['name' => 'ANDI AULYA RAMADHANY, SE., M.Akt', 'jabatan' => 'WAKIL REKTOR II', 'unit' => 'REKTORAT'],
            11  => ['name' => 'JUHANDA KARTIKA WIJAYA, SKI.,M.KM', 'jabatan' => 'STAFF AKADEMIK FAKULTAS ILMU KESEHATAN', 'unit' => 'BAAK - FIKES'],
            13  => ['name' => 'MEITA NILA SARI', 'jabatan' => 'STAFF - SUPPORT LAYANAN NILAI DAN PERKULIAHAN', 'unit' => 'BAAK'],
            17  => ['name' => 'NISRUL NURHAYANTI', 'jabatan' => 'PUSTAKAWAN', 'unit' => 'PERPUSTAKAAN'],
            18  => ['name' => 'NURMAIYUNITA, SE., MM', 'jabatan' => 'KABID. SDM DAN UMUM', 'unit' => 'SDM & UMUM'],
            22  => ['name' => 'SHINTA WAHYUNI, SE., MM', 'jabatan' => 'STAFF KEUANGAN FAKULTAS EKONOMI DAN BISNIS', 'unit' => 'BAUK - FEB'],
            24  => ['name' => 'ANDI HUMAIMAH, S. Km', 'jabatan' => 'STAFF KEUANGAN FAKULTAS SAINS DAN TEKNOLOGI', 'unit' => 'BAUK - FST'],
            25  => ['jabatan' => 'STAFF KEUANGAN FAKULTAS SAINS DAN TEKNOLOGI', 'unit' => 'BAUK - FST'],
            35  => ['name' => 'ANDI AWALUDDIN, ST', 'jabatan' => 'TATA USAHA BAUK', 'unit' => 'BAUK'],
            43  => ['name' => 'NOPAN ASWANDI, S.Kom', 'jabatan' => 'STAFF DOKUMENTASI', 'unit' => 'HUMAS'],
            44  => ['name' => 'BAGA PRASANDI, S.E', 'jabatan' => 'STAFF HUMAS', 'unit' => 'HUMAS'],
            49  => ['name' => 'MARDA USA, S.Ak', 'jabatan' => 'STAFF KERJASAMA - NASIONAL', 'unit' => 'KERJASAMA'],
            51  => ['name' => 'RASMIN NUR JADING, SKM., M.Kes', 'jabatan' => 'STAFF KEMAHASISWAAN - KIP', 'unit' => 'BIRO KEMAHASISWAAN'],
            52  => ['name' => 'ANDI MAHDI SYAHRAINI', 'jabatan' => 'STAFF KEMAHASISWAAN - PRESTASI OLAH RAGA', 'unit' => 'BIRO KEMAHASISWAAN'],
            53  => ['name' => 'AZMIL FAUZI FARISKA, SH., MH', 'jabatan' => 'STAFF KEMAHASISWAAN - PRESTASI SENI', 'unit' => 'BIRO KEMAHASISWAAN'],
            54  => ['name' => 'Dr. AHMADI, SKM., M.KL', 'jabatan' => 'KABID. PUSAT KARIR, ALUMNI DAN KEWIRAUSAHAAN', 'unit' => 'PUSAT KARIR'],
            58  => ['name' => 'Dr. ICE IRAWATI, SKM., M.Kes', 'jabatan' => 'KA. LPPM', 'unit' => 'LPPM'],
            59  => ['name' => 'YANDRA IRVIALDO, SE., MM', 'jabatan' => 'KABID. PENELITIAN', 'unit' => 'LPPM'],
            60  => ['name' => 'KHOERUN NISA SAFITRI, ST., MT', 'jabatan' => 'KABID. PENGABDIAN KEPADA MASYARAKAT', 'unit' => 'LPPM'],
            62  => ['name' => 'NANDA JARTI, M.Kom', 'jabatan' => 'KABID. HAKI DAN PUBLIKASI', 'unit' => 'LPPM'],
            65  => ['name' => 'Ir. INDAH KUSUMA DEWI, S.Kom., M.Kom', 'jabatan' => 'KABID. SOSIALISASI SPMI DAN KERJASAMA SPMI', 'unit' => 'LPMI'],
            66  => ['name' => 'CHINTA YOLANDA SARI, SKM., M.KM', 'jabatan' => 'KABID. AKREDITASI DAN DOKUMENTASI', 'unit' => 'LPMI'],
            67  => ['name' => 'WAN INTAN PARISMA, S.ST., M.KM', 'jabatan' => 'KABID. EVALUASI DAN AUDIT MUTU', 'unit' => 'LPMI'],
            69  => ['name' => 'MULYADI, S.E., MM', 'jabatan' => 'WAKIL DEKAN I (Akademik, Kemahasiswaan dan Pusat Karir)', 'unit' => 'FAKULTAS EKONOMI DAN BISNIS'],
            72  => ['name' => 'INA KURNIATI, SE., MM', 'jabatan' => 'SEKRETARIS PRODI S1 MANAJEMEN', 'unit' => 'PRODI S1 MANAJEMEN'],
            74  => ['name' => 'KHADIJAH, SE., M.Ak', 'jabatan' => 'SEKRETARIS PRODI S1 AKUNTANSI', 'unit' => 'PRODI S1 AKUNTANSI'],
            77  => ['name' => 'Dr. ANDIKA PRASETYA NUGRAHA, SE., MM', 'jabatan' => 'UPMI FEB', 'unit' => 'FAKULTAS EKONOMI DAN BISNIS'],
            78  => ['name' => 'DEWI PERMATA SARI, S.E., MM', 'jabatan' => 'UPPM FEB', 'unit' => 'FAKULTAS EKONOMI DAN BISNIS'],
            79  => ['name' => 'MUHAMMAD GUNAWAN, S.KL., MM', 'jabatan' => 'TATA USAHA SARJANA FEB', 'unit' => 'FAKULTAS EKONOMI DAN BISNIS'],
            82  => ['name' => 'Ir. SANUSI, ST., M.Eng., Ph.D., IPM', 'jabatan' => 'DEKAN FAKULTAS SAINS DAN TEKNOLOGI', 'unit' => 'FAKULTAS SAINS DAN TEKNOLOGI'],
            84  => ['jabatan' => 'WAKIL DEKAN II (Administrasi dan SDM)', 'unit' => 'FAKULTAS SAINS DAN TEKNOLOGI'],
            85  => ['name' => 'Ir. HERMAN, ST., MT', 'jabatan' => 'KETUA PROGRAM STUDI TEKNIK INDUSTRI', 'unit' => 'PRODI TEKNIK INDUSTRI'],
            86  => ['name' => 'RIZKY PRAKASA HASIBUAN, S.T., M.T', 'jabatan' => 'SEKRETARIS PROGRAM STUDI TEKNIK INDUSTRI', 'unit' => 'PRODI TEKNIK INDUSTRI'],
            91  => ['name' => 'Ansar, S.E., M.M', 'jabatan' => 'TATA USAHA FST', 'unit' => 'FAKULTAS SAINS DAN TEKNOLOGI'],
            92  => ['unit' => 'FAKULTAS SAINS DAN TEKNOLOGI', 'jabatan' => 'UPMI FST'],
            93  => ['name' => 'Ir. ERY SUGITO, S.T., M.T', 'unit' => 'FAKULTAS SAINS DAN TEKNOLOGI', 'jabatan' => 'KA. UPPM FST'],
            94  => ['name' => 'NOFRI YUDI, S.Kom, M.Kom', 'unit' => 'FAKULTAS SAINS DAN TEKNOLOGI', 'jabatan' => 'STAFF UPPM FST'],
            96  => ['name' => 'LUTHFI A, S.T', 'jabatan' => 'STAFF LABOR TEKNIK INDUSTRI', 'unit' => 'LABORATORIUM FST'],
            98  => ['name' => 'M. ISRA NUR ABDULLAH YUZUK, S.T', 'jabatan' => 'STAFF LABOR TEKNIK PROSES PRODUKSI', 'unit' => 'LABORATORIUM FST'],
            99  => ['jabatan' => 'KA. HUMAS DAN PUBLIKASI FST', 'unit' => 'FAKULTAS SAINS DAN TEKNOLOGI'],
            102 => ['name' => 'dr. Hj. ELUSI MARTHA, M.KM', 'jabatan' => 'WAKIL DEKAN I (Akademik, Kemahasiswaan dan Pusat Karir)', 'unit' => 'FAKULTAS ILMU KESEHATAN'],
            103 => ['name' => 'RIZQI ULLA AMALIAH, S.KM., M.KKK', 'jabatan' => 'WAKIL DEKAN II (Administrasi dan SDM)', 'unit' => 'FAKULTAS ILMU KESEHATAN'],
            104 => ['name' => 'IRISNA DEWITA, S.KM., M.Kes', 'jabatan' => 'KETUA PROGRAM STUDI K3', 'unit' => 'PRODI K3'],
            105 => ['name' => 'IRISNA JAYATI, M.KM', 'jabatan' => 'SEKRETARIS PRODI K3', 'unit' => 'PRODI K3'],
            107 => ['name' => 'ALFIAN TRILESIN, S.KM., M.KL', 'jabatan' => 'SEKRETARIS PRODI KESLING', 'unit' => 'PRODI KESLING'],
            108 => ['name' => 'NOVELA SARI, SKM., M.Kes', 'jabatan' => 'UPMI FIKES', 'unit' => 'FAKULTAS ILMU KESEHATAN'],
            110 => ['name' => 'ANITA PRAMAWARI, S.KM., M.Si', 'jabatan' => 'UPPM FIKES', 'unit' => 'FAKULTAS ILMU KESEHATAN'],
            111 => ['name' => 'JAMAL, S.KL., M.KM', 'jabatan' => 'LABORAN FIKES', 'unit' => 'FAKULTAS ILMU KESEHATAN'],
            112 => ['name' => 'PURWANTY SELVIA NASUTION, Amd., KL', 'jabatan' => 'TATA USAHA FIKES', 'unit' => 'FAKULTAS ILMU KESEHATAN'],
            113 => ['name' => 'UDANG AMIR HAMZAH, S.Kom', 'jabatan' => 'HUMAS DAN PUBLIKASI WEB FIKES', 'unit' => 'FAKULTAS ILMU KESEHATAN'],
            124 => ['name' => 'Dr. OKTAVESA, S.Kom., M.Kom', 'jabatan' => 'WAKIL DEKAN I (Akademik, Kemahasiswaan dan Pusat Karir)', 'unit' => 'FAKULTAS SAINS DAN TEKNOLOGI'],
            126 => ['name' => 'AGUS SURYADI, S.Kom., M.Kom', 'jabatan' => 'KABID. PERENCANAAN DAN PENGEMBANGAN', 'unit' => 'PERENCANAAN'],
            130 => ['name' => 'ANDI HIDAYATULLAH AZIZAH, S.T., M.Si., Ak.', 'jabatan' => 'KA. BIRO ADMINISTRASI UMUM DAN KEUANGAN (BAUK)', 'unit' => 'BAUK'],
            132 => ['name' => 'ITA MUSTIKA, S.E., M.Ak', 'jabatan' => 'WAKIL DEKAN II (Administrasi dan SDM)', 'unit' => 'FAKULTAS EKONOMI DAN BISNIS'],
            136 => ['name' => 'MUHAMMAD ALBAR, ST', 'jabatan' => 'STAFF AKADEMIK FAKULTAS SAINS DAN TEKNOLOGI', 'unit' => 'BAAK - FST'],
            145 => ['name' => 'ANDI AULIA VITRIZKY, A.md. Ak', 'jabatan' => 'STAFF KEUANGAN FAKULTAS ILMU KESEHATAN', 'unit' => 'BAUK - FIKES'],
            231 => ['name' => 'ANDI AMIN AMRUL MUKMININ, S.Kom', 'jabatan' => 'STAFF HUMAS DAN PUBLIKASI FST', 'unit' => 'FAKULTAS SAINS DAN TEKNOLOGI'],
            260 => ['name' => 'ANDI NURIMAN TEGUH, S.I', 'jabatan' => 'PUSTAKAWAN', 'unit' => 'PERPUSTAKAAN'],
            275 => ['jabatan' => 'KA. LABORATORIUM FAKULTAS SAINS DAN TEKNOLOGI', 'unit' => 'LABORATORIUM FST'],
        ];

        foreach ($directCorrections as $uid => $attr) {
            User::where('id', $uid)->update($attr);
        }

        // 3. Terapkan pemetaan hirarki atasan-bawahan sesuai OTK
        $tree = self::getOtkTree();

        foreach ($tree as $group => $data) {
            $leader = self::findUser($data['leader']);
            if (!$leader) {
                continue;
            }

            foreach ($data['subordinates'] as $subTerm) {
                $sub = self::findUser($subTerm);
                if ($sub && $sub->id !== $leader->id) {
                    $sub->supervisor_id = $leader->id;
                    $sub->save();
                }
            }
        }
    }

    /**
     * Helper to find user by name or email keyword.
     */
    public static function findUser(string $term): ?User
    {
        return User::where('name', 'LIKE', "%{$term}%")
            ->orWhere('email', 'LIKE', "%{$term}%")
            ->first();
    }

    /**
     * Structure of OTK UIS 2024 - 2029.
     */
    public static function getOtkTree(): array
    {
        return [
            // === REKTORAT: REKTOR ===
            'REKTOR' => [
                'leader' => 'LARISANG',
                'subordinates' => [
                    'FITRI SARI DEWI',        // WR I
                    'ANDI AULIYA RAMADHANY',  // WR II
                    'SUMARDIN',               // WR III
                    'SABRI',                  // Dekan FEB
                    'SANUSI',                 // Dekan Fakultas Sains dan Teknologi
                    'HENGKY OKTARIZAL',       // Dekan FIKES
                    'ICE IRAWATI',            // Ka LPPM
                    'RONI SAPUTRA',           // Ka LPMI
                ],
            ],

            // === WAKIL REKTOR I (FITRI SARI DEWI) ===
            'WR_I' => [
                'leader' => 'FITRI SARI DEWI',
                'subordinates' => [
                    'LENI UTAMI',    // Ka. Biro BAAK
                    'HASAN HUSEN',   // Kepala Perpustakaan
                ],
            ],
            // BAAK (LENI UTAMI)
            'BAAK' => [
                'leader' => 'LENI UTAMI',
                'subordinates' => [
                    'MUHAMMAD FADLI LURAN', // Kabid. Akademik
                    'ADNAN',                // Kabid. Administrasi & Layanan Kemahasiswaan
                ],
            ],
            'KABID_AKADEMIK' => [
                'leader' => 'MUHAMMAD FADLI LURAN',
                'subordinates' => [
                    'ANDI AMIN SURYAWAN',    // Staff Akademik FEB
                    'MUHAMMAD ALBAR',        // Staff Akademik FST
                    'JUHANDA KARTIKA WIJAYA',// Staff Akademik FIKES
                ],
            ],
            'KABID_LAYANAN_MHS' => [
                'leader' => 'ADNAN',
                'subordinates' => [
                    'META NILA SARI',        // Staff - Support Layanan Nilai dan Perkuliahan
                    'ANDI RICKY HAMUR',      // Staff Support Layanan Ijazah
                ],
            ],
            'PERPUSTAKAAN' => [
                'leader' => 'HASAN HUSEN',
                'subordinates' => [
                    'FITRA AYU KUSUMA',
                    'NISRUL NURHAYANTI',
                    'ANDI NURMAN TEGUH',
                ],
            ],

            // === WAKIL REKTOR II (ANDI AULIYA RAMADHANY) ===
            'WR_II' => [
                'leader' => 'ANDI AULIYA RAMADHANY',
                'subordinates' => [
                    'ANDI HIDAYATULLAH AZIZAH', // Ka. Biro BAUK
                ],
            ],
            // BAUK (ANDI HIDAYATULLAH AZIZAH)
            'BAUK' => [
                'leader' => 'ANDI HIDAYATULLAH AZIZAH',
                'subordinates' => [
                    'MAISYITA RISMA SURYANI', // Kabid. Keuangan
                    'NURMAYUNITA',            // Kabid. SDM dan Umum
                    'ANDI KHAIRUNNISA',       // Kabid. Sarana dan Prasarana
                    'ANDI AWALUDDIN',         // Tata Usaha BAUK
                ],
            ],
            'KEUANGAN' => [
                'leader' => 'MAISYITA RISMA SURYANI',
                'subordinates' => [
                    'ANDI SAIDAH',           // Kasir Rektorat
                    'SHINTA WAHYUNI',        // Staff Keuangan FEB
                    'ZHALFA PUTRI ELISA',    // Staff Keuangan FEB
                    'ANDI HUMAIMAH',         // Staff Keuangan FST
                    'MAISARAH PERTIWI',      // Staff Keuangan FST
                    'ANDI AULIA VITRIZKY',   // Staff Keuangan FIKES
                    'SALSABILA RIVANIA',     // Staff Keuangan FIKES
                ],
            ],
            'SDM' => [
                'leader' => 'NURMAYUNITA',
                'subordinates' => [
                    'ISHAK',                 // Operator SDM
                    'ROSALINA',              // Staff Kepegawaian
                ],
            ],
            'SARPRAS' => [
                'leader' => 'ANDI KHAIRUNNISA',
                'subordinates' => [
                    'CHOIRUL ANAM',          // Admin Umum Sarpras
                    'APRIYADI',              // Staff Sarpras
                    'ANDI TAHER',            // Staff Sarpras
                ],
            ],

            // === WAKIL REKTOR III (Dr. SUMARDIN) ===
            'WR_III' => [
                'leader' => 'SUMARDIN',
                'subordinates' => [
                    'MUHAMMAD ROPIANTO',     // Ka. UPT ICT / LPTI
                    'HERDIANTI',             // Ka. Biro Kemahasiswaan, Alumni, Kerjasama, dsb.
                ],
            ],
            // UPT ICT / LPTI (Dr. MUHAMMAD ROPIANTO)
            'LPTI' => [
                'leader' => 'MUHAMMAD ROPIANTO',
                'subordinates' => [
                    'ANDI MUAMAR',           // Divisi Pengembangan Sistem Informasi & Aplikasi
                    'DICKY ASHRUL IBRAHIM',  // Divisi Infrastruktur, Jaringan, dan Layanan Troubleshooting
                    'Dimas Pradipto',        // Programmer LPTI
                    'Rusdiyanto',            // Staff Troubleshooting
                ],
            ],
            // BIRO KEMAHASISWAAN (Dr. HERDIANTI)
            'BIRO_KEMAHASISWAAN' => [
                'leader' => 'HERDIANTI',
                'subordinates' => [
                    'ANDI AKBAR',            // Kabid. Humas dan Publikasi
                    'AMIRULLAH',             // Kabid. Kerjasama
                    'ABDUL ROHMAD BASAR',    // Kabid. Kemahasiswaan
                    'AHMADI',                // Kabid. Pusat Karir, Alumni dan Kewirausahaan
                    'AGUS SURYADI',          // Kabid. Perencanaan dan Pengembangan
                ],
            ],
            'HUMAS' => [
                'leader' => 'ANDI AKBAR',
                'subordinates' => [
                    'NOPAN ASWANDI',         // Staff Dokumentasi
                    'RAGA PRASANDI',         // Staff Humas
                    'TASLIMAHUDIN',          // Staff Website
                ],
            ],
            'KERJASAMA' => [
                'leader' => 'AMIRULLAH',
                'subordinates' => [
                    'AGUS SUTIANDI',         // Staff Kerjasama - Internasional
                    'ANDI RUSLAN',           // Staff Kerjasama - Nasional
                    'MARDA LISA',            // Staff Kerjasama - Nasional
                ],
            ],
            'KEMAHASISWAAN' => [
                'leader' => 'ABDUL ROHMAD BASAR',
                'subordinates' => [
                    'RASMIN NUR JADING',     // Staff Kemahasiswaan - KIP
                    'Andi Mahdi Syahraini',  // Staff Kemahasiswaan - Prestasi Olahraga
                    'AZMIL FAUZI FARISKA',   // Staff Kemahasiswaan - Prestasi Seni
                ],
            ],
            'PUSAT_KARIR' => [
                'leader' => 'AHMADI',
                'subordinates' => [
                    'LAILATUL QADRY',        // Staff Pusat Karir, Alumni dan Kewirausahaan
                ],
            ],
            'PERENCANAAN' => [
                'leader' => 'AGUS SURYADI',
                'subordinates' => [
                    'ASRAF',                 // Staff Perencanaan dan Pengembangan
                ],
            ],

            // === LPPM (Dr. ICE IRAWATI) ===
            'LPPM' => [
                'leader' => 'ICE IRAWATI',
                'subordinates' => [
                    'YANDRA RIVALDO',        // Kabid. Penelitian
                    'Khoerun Nisa Safitri',  // Kabid. Pengabdian Kepada Masyarakat
                    'MARLINA UMAR',          // Staff Administrasi Penelitian & PKM
                    'NANDA JARTI',           // Kabid. HAKI dan Publikasi
                ],
            ],

            // === LPMI (RONI SAPUTRA) ===
            'LPMI' => [
                'leader' => 'RONI SAPUTRA',
                'subordinates' => [
                    'NOVI HENDRI ADI',       // Kabid. Pengembangan SPMI dan SDM SPMI
                    'INDAH KUSUMA DEWI',     // Kabid. Sosialisasi SPMI dan Kerjasama SPMI
                    'CHINTA YOLANDA SARI',   // Kabid. Akreditasi dan Dokumentasi
                    'WAN INTAN PARISMA',     // Kabid. Evaluasi dan Audit Mutu
                ],
            ],

            // === FAKULTAS EKONOMI DAN BISNIS (Dr. SABRI) ===
            'FEB' => [
                'leader' => 'SABRI',
                'subordinates' => [
                    'MULYADI',               // Wakil Dekan I (Akademik, Kemahasiswaan dan Pusat Karir)
                    'ITA MUSTIKA',           // Wakil Dekan II (Administrasi dan SDM)
                    'SEPTA DIANA NABELLA',   // Ketua Program Studi S1 Manajemen
                    'MAYA RICHMAYATI',       // Ketua Program Studi S1 Akuntansi
                    'HENDRI HERMAN',         // Ketua Program Studi Pascasarjana Magister Manajemen
                    'ANDIKA PRASETYA NUGRAHA', // UPMI
                    'DEWI PERMATA SARI',     // UPPM
                    'MUHAMMAD GUNAWAN',      // Tata Usaha Sarjana
                    'RAHMAN SYAHPUTRA',      // Tata Usaha Pascasarjana
                    'MUHAMMAD FIRNANDA',     // Humas dan Publikasi Web
                    'MUKHIRZIN',             // CS FEB
                ],
            ],
            'PRODI_MANAJEMEN' => [
                'leader' => 'SEPTA DIANA NABELLA',
                'subordinates' => [
                    'INA KURNIATI',          // Sekretaris Prodi S1 Manajemen
                ],
            ],
            'PRODI_AKUNTANSI' => [
                'leader' => 'MAYA RICHMAYATI',
                'subordinates' => [
                    'KHADIJAH',              // Sekretaris Prodi S1 Akuntansi
                ],
            ],
            'PRODI_S2_MM' => [
                'leader' => 'HENDRI HERMAN',
                'subordinates' => [
                    'SUHARDI',               // Sekretaris Prodi Pascasarjana Magister Manajemen
                ],
            ],

            // === FAKULTAS SAINS DAN TEKNOLOGI (Ir. SANUSI) ===
            'FST' => [
                'leader' => 'SANUSI',
                'subordinates' => [
                    'OKTAVESA',              // Wakil Dekan I (Akademik, Kemahasiswaan dan Pusat Karir)
                    'ANDI HEPY SUSANTI',     // Wakil Dekan II (Administrasi dan SDM)
                    'Ir. HERMAN',            // Ketua Program Studi Teknik Industri
                    'ARMY TRILIDIA DEVEGA',  // Ketua Program Studi TI & SI
                    'ALBERTUS LAURENSIUS SETYABUDHI', // Ketua Program Studi Teknik Logistik dan Perkapalan
                    'Ansar',                 // Tata Usaha FST
                    'AFRINA',                // UPMI FST
                    'Ery Sugito',            // Ka. UPPM FST
                    'FAJRINALDI',            // Ka. Laboratorium Fakultas Sains dan Teknologi
                    'MUTIARA AYU MAWADDAH',  // Ka. Humas dan Publikasi FST
                ],
            ],
            'PRODI_TI_INDUSTRI' => [
                'leader' => 'Ir. HERMAN',
                'subordinates' => [
                    'RIZKY PRAKASA HASIBUAN', // Sekretaris Program Studi Teknik Industri
                ],
            ],
            'PRODI_TI_INFORMATIKA' => [
                'leader' => 'ARMY TRILIDIA DEVEGA',
                'subordinates' => [
                    'ATMAN LUCKY F',          // Sekretaris Program Studi TI & SI
                ],
            ],
            'PRODI_LOGISTIK' => [
                'leader' => 'ALBERTUS LAURENSIUS SETYABUDHI',
                'subordinates' => [
                    'SITI AISYAH JAMAL',      // Sekretaris Program Studi Teknik Logistik dan Perkapalan
                ],
            ],
            'UPPM_FST' => [
                'leader' => 'Ery Sugito',
                'subordinates' => [
                    'NOFRI YUDI',             // Staff UPPM FST
                ],
            ],
            'LAB_FST' => [
                'leader' => 'FAJRINALDI',
                'subordinates' => [
                    'LUTHFI A',               // Staff Labor Teknik Industri
                    'MARDINUR',               // Staff Labor Teknik Komputer
                    'M. ISRA NUR ABDULLAH YUZUK', // Staff Labor Teknik Proses Produksi
                ],
            ],
            'HUMAS_FST' => [
                'leader' => 'MUTIARA AYU MAWADDAH',
                'subordinates' => [
                    'ANDI AMIN AMRUL MUKMININ', // Staff Humas dan Publikasi FST
                ],
            ],

            // === FAKULTAS ILMU KESEHATAN (Dr. HENGKY OKTARIZAL) ===
            'FIKES' => [
                'leader' => 'HENGKY OKTARIZAL',
                'subordinates' => [
                    'ELUSI MARTHA',          // Wakil Dekan I (Akademik, Kemahasiswaan dan Pusat Karir)
                    'RIZQI ULLA AMALIAH',     // Wakil Dekan II (Administrasi dan SDM)
                    'IRISNA DEWITA',          // Ketua Program Studi K3
                    'KAFIT',                  // Ketua Program Studi Kesling
                    'NOVELA SARI',            // UPMI FIKES
                    'FAJAR',                  // GKM FIKES
                    'ANITA PRAMAWARI',        // UPPM FIKES
                    'JAMAL',                  // Laboran FIKES
                    'PURWANTY SELVIA NASUTION', // Tata Usaha FIKES
                    'UDANG AMIR HAMZAH',      // Humas dan Publikasi Web FIKES
                ],
            ],
            'PRODI_K3' => [
                'leader' => 'IRISNA DEWITA',
                'subordinates' => [
                    'IRISNA JAYATI',          // Sekretaris Prodi K3
                ],
            ],
            'PRODI_KESLING' => [
                'leader' => 'KAFIT',
                'subordinates' => [
                    'ALFIAN TRILESIN',        // Sekretaris Prodi Kesling
                ],
            ],
        ];
    }
}
