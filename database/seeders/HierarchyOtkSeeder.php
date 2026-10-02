<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class HierarchyOtkSeeder extends Seeder
{
    private const ACCOUNT_NAME_ALIASES = [
        'ANDI AULIYA RAMADHANY' => ['ANDI AULYA RAMADHANY'],
        'META NILA SARI' => ['MEITA NILA SARI'],
        'NISRUL NURHAYANTI' => ['NISRUL NURBAYANTI', 'NISHFU NURBAYANTI'],
        'ANDI NURMAN TEGUH' => ['ANDI NURIMAN TEGUH'],
        'ANDI HIDAYATULLAH AZIZAH' => ['ANDI HIDAYATUL FADILAH', 'ANDI HIDAYATUL FADLILAH'],
        'NURMAYUNITA' => ['NURMAIYUNITA'],
        'RAGA PRASANDI' => ['BAGA PRASANDI'],
        'MARDA USA' => ['MARDA LISA'],
        'YANDRA IRVIALDO' => ['YANDRA RIVALDO'],
        'DEWI PERMATA SARI' => ['DEWI PERMASARI'],
        'OKTAVESA' => ['OKTA VEZA', 'OKTA VESA'],
        'RIZKY PRAKASA HASIBUAN' => ['RIZKI PRAKASA HASIBUAN'],
        'LUTHFI A' => ['LUTFIA', 'LUTHFIA'],
        'M ISRA NUR ABDULLAH YUZUL' => ['M. ISRA NUR ABDULLAH YUZUK'],
        'ANDI AMIN AMRUL MUKMININ' => ['ANDI AMIN AMIRUL MUKMININ'],
        'ELUSI MARTHA' => ['ELSUSI MARTHA'],
        'TRISNA DEWITA' => ['IRISNA DEWITA'],
        'TRISNA JAYATI' => ['IRISNA JAYATI'],
        'ANITA PRAMAWATI' => ['ANITA PRAMAWARI'],
        'PERAWATY SELFIA NASUTION' => ['PURWANTY SELVIA NASUTION'],
        'UJANG AMIR HAMZAH' => ['UDANG AMIR HAMZAH'],
        'AL HAFEZ HUSEIN' => ['ALFIAN TRILESIN'],
    ];

    /**
     * Run the database seeds according to OTK UIS 2024-2029.
     */
    public function run(): void
    {
        $tree = self::getOtkTree();
        $users = User::query()->get(['id', 'name']);
        $assignments = [];
        $otkUserIds = [];
        $missingUsers = [];

        foreach ($tree as $group => $data) {
            $leader = self::findUniqueUser($data['leader'], $users, "{$group} (atasan)");
            if (!$leader) {
                $missingUsers[] = "{$group}: {$data['leader']}";
                continue;
            }
            $otkUserIds[$leader->id] = true;

            foreach ($data['subordinates'] as $subTerm) {
                $subordinate = self::findUniqueUser($subTerm, $users, "{$group} (bawahan)");
                if (!$subordinate) {
                    $missingUsers[] = "{$group}: {$subTerm}";
                    continue;
                }

                $otkUserIds[$subordinate->id] = true;
                if ($subordinate->is($leader)) {
                    throw new RuntimeException("Pemetaan OTK tidak valid: {$subTerm} menjadi atasan bagi dirinya sendiri.");
                }

                if (isset($assignments[$subordinate->id]) && $assignments[$subordinate->id] !== $leader->id) {
                    throw new RuntimeException("Pemetaan OTK ambigu: {$subTerm} tercantum di lebih dari satu atasan.");
                }

                $assignments[$subordinate->id] = $leader->id;
            }
        }

        if ($missingUsers !== []) {
            throw new RuntimeException('Sinkronisasi OTK dibatalkan karena nama akun tidak ditemukan atau tidak unik: ' . implode('; ', $missingUsers));
        }

        DB::transaction(function () use ($assignments, $otkUserIds): void {
            User::whereIn('id', array_keys($otkUserIds))->update(['supervisor_id' => null]);

            foreach ($assignments as $userId => $supervisorId) {
                User::whereKey($userId)->update(['supervisor_id' => $supervisorId]);
            }
        });
    }

    private static function findUniqueUser(string $term, EloquentCollection $users, string $context): ?User
    {
        try {
            return self::resolveUserByOtkName($term, $users);
        } catch (RuntimeException $exception) {
            throw new RuntimeException("Nama \"{$term}\" pada {$context} cocok dengan lebih dari satu akun. Pemetaan dihentikan agar tidak salah orang.", previous: $exception);
        }
    }

    public static function resolveUserByOtkName(string $name, EloquentCollection $users): ?User
    {
        $normalizedName = self::normalizeName($name);
        $acceptedNames = [$normalizedName];

        foreach (self::ACCOUNT_NAME_ALIASES as $canonicalName => $aliases) {
            $normalizedCanonical = self::normalizeName($canonicalName);
            $normalizedAliases = array_map(self::normalizeName(...), $aliases);
            if ($normalizedName === $normalizedCanonical || in_array($normalizedName, $normalizedAliases, true)) {
                $acceptedNames = array_merge([$normalizedCanonical], $normalizedAliases);
                break;
            }
        }

        $matches = $users->filter(fn (User $user): bool => in_array(
            self::normalizeName($user->name),
            $acceptedNames,
            true
        ));

        if ($matches->count() > 1) {
            throw new RuntimeException("Nama \"{$name}\" cocok dengan lebih dari satu akun.");
        }

        return $matches->first();
    }

    private static function normalizeName(string $name): string
    {
        $name = preg_split('/,/', $name, 2)[0] ?? $name;
        $name = mb_strtoupper(trim($name));
        $name = preg_replace('/^(?:(?:ASSOC(?:IATE)?|PROF(?:ESSOR)?|DR|DRG|IR|HJ|H)\\.?\\s+)+/u', '', $name) ?? $name;

        return trim(preg_replace('/[^A-Z0-9]+/u', ' ', $name) ?? $name);
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
                    'ICE IRAWATI',           // Ka. LPPM
                    'RONI SAPUTRA',          // Ka. LPMI
                ],
            ],
            // UPT ICT / LPTI (Dr. MUHAMMAD ROPIANTO)
            'LPTI' => [
                'leader' => 'MUHAMMAD ROPIANTO',
                'subordinates' => [
                    'ANDI MUAMAR',           // Divisi Pengembangan Sistem Informasi & Aplikasi
                    'DICKY ASHRUL IBRAHIM',  // Divisi Infrastruktur, Jaringan, dan Layanan Troubleshooting
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
                    'MARDA USA',             // Staff Kerjasama - Nasional
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
                    'YANDRA IRVIALDO',       // Kabid. Penelitian
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
                    'M. ISRA NUR ABDULLAH YUZUL', // Staff Labor Teknik Proses Produksi
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
                    'TRISNA DEWITA',          // Ketua Program Studi K3
                    'M. KAFIT',               // Ketua Program Studi Kesling
                    'NOVELA SARI',            // UPMI FIKES
                    'FAJAR',                  // GKM FIKES
                    'ANITA PRAMAWATI',        // UPPM FIKES
                    'JAMAL',                  // Laboran FIKES
                    'PERAWATY SELFIA NASUTION', // Tata Usaha FIKES
                    'UJANG AMIR HAMZAH',      // Humas dan Publikasi Web FIKES
                ],
            ],
            'PRODI_K3' => [
                'leader' => 'TRISNA DEWITA',
                'subordinates' => [
                    'TRISNA JAYATI',          // Sekretaris Prodi K3
                ],
            ],
            'PRODI_KESLING' => [
                'leader' => 'M. KAFIT',
                'subordinates' => [
                    'AL HAFEZ HUSEIN',        // Sekretaris Prodi Kesling
                ],
            ],
        ];
    }
}
