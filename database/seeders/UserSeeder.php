<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            // REKTORAT & PIMPINAN
            ['jabatan' => 'REKTOR', 'name' => 'Assoc. Prof. Dr. Ir. LARISANG, MT.,IPU', 'unit' => 'REKTORAT', 'roles' => 'superadmin'],
            ['jabatan' => 'WAKIL REKTOR I', 'name' => 'FITRI SARI DEWI, SKM.,M.KKK', 'unit' => 'REKTORAT'],
            ['jabatan' => 'WAKIL REKTOR II', 'name' => 'ANDI AULIYA RAMADHANY, SE., M.Akt', 'unit' => 'REKTORAT'],
            ['jabatan' => 'WAKIL REKTOR III', 'name' => 'Dr. SUMARDIN, SE., M.Si', 'unit' => 'REKTORAT'],

            // BAAK
            ['jabatan' => 'KA. BIRO ADMINISTRASI AKADEMIK KEMAHASISWAAN (BAAK)', 'name' => 'LENI UTAMI, S.Si., M. KM', 'unit' => 'BAAK'],
            ['jabatan' => 'KABID. AKADEMIK', 'name' => 'MUHAMMAD FADLI LURAN, SE., MM', 'unit' => 'BAAK'],
            ['jabatan' => 'STAFF AKADEMIK FAKULTAS EKONOMI DAN BISNIS', 'name' => 'ANDI AMIN SURYAWAN, SE', 'unit' => 'BAAK - FEB'],
            ['jabatan' => 'STAFF AKADEMIK FAKULTAS SAINS DAN TEKNOLOGI', 'name' => 'MUHAMMAD ALBAR, ST', 'unit' => 'BAAK - FST'],
            ['jabatan' => 'STAFF AKADEMIK FAKULTAS ILMU KESEHATAN', 'name' => 'JUHANDA KARTIKA WIJAYA, SKL.,M.KM', 'unit' => 'BAAK - FIKES'],
            ['jabatan' => 'KABID. ADMINISTRASI DAN LAYANAN KEMAHASISWAAN', 'name' => 'ADNAN, SE.,MM', 'unit' => 'BAAK'],
            ['jabatan' => 'STAFF - SUPPORT LAYANAN NILAI DAN PERKULIAHAN', 'name' => 'META NILA SARI', 'unit' => 'BAAK'],
            ['jabatan' => 'STAFF - SUPPORT LAYANAN IJAZAH', 'name' => 'ANDI RICKY HAMUR, SE', 'unit' => 'BAAK'],

            // PERPUSTAKAAN
            ['jabatan' => 'KEPALA PERPUSTAKAAN', 'name' => 'HASAN HUSEN, S.IP', 'unit' => 'PERPUSTAKAAN'],
            ['jabatan' => 'PUSTAKAWAN', 'name' => 'FITRA AYU KUSUMA, S.HUM', 'unit' => 'PERPUSTAKAAN'],
            ['jabatan' => 'PUSTAKAWAN', 'name' => 'NISHFU NURBAYANTI', 'unit' => 'PERPUSTAKAAN'],
            ['jabatan' => 'PUSTAKAWAN', 'name' => 'ANDI NURMAN TEGUH, S.E', 'unit' => 'PERPUSTAKAAN'],

            // BAUK & KEUANGAN
            ['jabatan' => 'KA. BIRO ADMINISTRASI UMUM DAN KEUANGAN', 'name' => 'ANDI HIDAYATUL FADLILAH, S.E., M.Si., Ak.', 'unit' => 'BAUK'],
            ['jabatan' => 'KABID. KEUANGAN', 'name' => 'MAISYITA RISMA SURYANI, SKL', 'unit' => 'BAUK'],
            ['jabatan' => 'KASIR REKTORAT', 'name' => 'ANDI SAIDAH, SE., M.M', 'unit' => 'BAUK'],
            ['jabatan' => 'STAFF KEUANGAN FAKULTAS EKONOMI DAN BISNIS', 'name' => 'SHINTA WAHYUNI, SE.,MM', 'unit' => 'BAUK - FEB'],
            ['jabatan' => 'STAFF KEUANGAN FAKULTAS EKONOMI DAN BISNIS', 'name' => 'ZHALFA PUTRI ELISA, SE., M.M', 'unit' => 'BAUK - FEB'],
            ['jabatan' => 'STAFF KEUANGAN FAKULTAS SAINS DAN TEKNOLOGI', 'name' => 'ANDI HUMAIMAH, S. Kes', 'unit' => 'BAUK - FST'],
            ['jabatan' => 'STAFF KEUANGAN FAKULTAS SAINS DAN TEKNOLOGI', 'name' => 'MAISARAH PERTIWI, S.M', 'unit' => 'BAUK - FST'],
            ['jabatan' => 'STAFF KEUANGAN FAKULTAS ILMU KESEHATAN', 'name' => 'ANDI AULIA VITRIZKY, A. md. Ak', 'unit' => 'BAUK - FIKES'],
            ['jabatan' => 'STAFF KEUANGAN FAKULTAS ILMU KESEHATAN', 'name' => 'SALSABILA RIVANIA, S.Ak', 'unit' => 'BAUK - FIKES'],

            // SDM & SARPRAS
            ['jabatan' => 'KABID. SDM DAN UMUM', 'name' => 'NURMAYUNITA, SE., MM', 'unit' => 'SDM & UMUM'],
            ['jabatan' => 'OPERATOR SDM', 'name' => 'ISHAK, S.E., M.M', 'unit' => 'SDM & UMUM'],
            ['jabatan' => 'STAFF KEPEGAWAIAN', 'name' => 'ROSALINA, SE', 'unit' => 'SDM & UMUM'],
            ['jabatan' => 'KABID. SARANA DAN PRASARANA', 'name' => 'dr. ANDI KHAIRUNNISA', 'unit' => 'SARPRAS'],
            ['jabatan' => 'ADMIN UMUM SARPRAS', 'name' => 'CHOIRUL ANAM, S.Kom', 'unit' => 'SARPRAS'],
            ['jabatan' => 'STAFF SARPRAS', 'name' => 'APRIYADI', 'unit' => 'SARPRAS'],
            ['jabatan' => 'STAFF SARPRAS', 'name' => 'ANDI TAHER, SE, MM', 'unit' => 'SARPRAS'],
            ['jabatan' => 'TATA USAHA BAUK', 'name' => 'ANDI AWALUDDIN, ST', 'unit' => 'BAUK'],

            // LPTI
            ['jabatan' => 'KEPALA LPTI', 'name' => 'Dr. MUHAMMAD ROPIANTO, S.Kom., M.Kom', 'unit' => 'LPTI'],
            ['jabatan' => 'DIVISI PENGEMBANGAN SISTEM INFORMASI DAN APLIKASI - LPTI BAUK', 'name' => 'ANDI MUAMAR, S.Kom', 'unit' => 'LPTI'],
            ['jabatan' => 'DIVISI INFRASTRUKTUR, JARINGAN, DAN LAYANAN TROUBLESHOOTING', 'name' => 'DICKY ASHRUL IBRAHIM, S.Kom', 'unit' => 'LPTI'],
            ['jabatan' => 'PROGRAMMER', 'name' => 'DIMAS PRADIPTO, S.KOM., M.KOM', 'unit' => 'LPTI'],
            ['jabatan' => 'IT SUPPORT', 'name' => 'RUSDIYANTO, S.Kom', 'unit' => 'LPTI'],

            // BIRO KEMAHASISWAAN, ALUMNI & KERJASAMA
            ['jabatan' => 'KA. BIRO KEMAHASISWAAN, ALUMNI, KERJASAMA, PERENCANAAN DAN PENGEMBANGAN', 'name' => 'Dr. HERDIANTI, M.Kes', 'unit' => 'BIRO KEMAHASISWAAN'],
            ['jabatan' => 'KABID. HUMAS DAN PUBLIKASI', 'name' => 'ANDI AKBAR, SE., MM', 'unit' => 'HUMAS'],
            ['jabatan' => 'STAFF DOKUMENTASI', 'name' => 'NOPAN ASWANDI, S. Kom', 'unit' => 'HUMAS'],
            ['jabatan' => 'STAFF HUMAS', 'name' => 'RAGA PRASANDI, S.E', 'unit' => 'HUMAS'],
            ['jabatan' => 'STAFF WEBSITE', 'name' => 'TASLIMAHUDIN, S.Sos', 'unit' => 'HUMAS'],
            ['jabatan' => 'KABID. KERJASAMA', 'name' => 'AMIRULLAH, S.Kom., MM', 'unit' => 'KERJASAMA'],
            ['jabatan' => 'STAFF KERJASAMA - INTERNASIONAL', 'name' => 'AGUS SUTIANDI, S.Ak', 'unit' => 'KERJASAMA'],
            ['jabatan' => 'STAFF KERJASAMA - NASIONAL', 'name' => 'ANDI RUSLAN, SE., M.M', 'unit' => 'KERJASAMA'],
            ['jabatan' => 'STAFF KERJASAMA - NASIONAL', 'name' => 'MARDA LISA, S.Ak', 'unit' => 'KERJASAMA'],
            ['jabatan' => 'KABID. KEMAHASISWAAN', 'name' => 'Dr. ABDUL ROHMAD BASAR, S.Kom., M.Kom', 'unit' => 'BIRO KEMAHASISWAAN'],
            ['jabatan' => 'STAFF KEMAHASISWAAN - KIP', 'name' => 'RASMIN NUR JADING, SKM.,M.Kes', 'unit' => 'BIRO KEMAHASISWAAN'],
            ['jabatan' => 'STAFF KEMAHASISWAAN - PRESTASI OLAH RAGA', 'name' => 'ANDI MAHDI SYAHRAINI', 'unit' => 'BIRO KEMAHASISWAAN'],
            ['jabatan' => 'STAFF KEMAHASISWAAN - PRESTASI SENI', 'name' => 'AZMIL FAUZI FARISKA, SH.,MH', 'unit' => 'BIRO KEMAHASISWAAN'],
            ['jabatan' => 'KABID. PUSAT KARIR, ALUMNI DAN KEWIRAUSAHAAN', 'name' => 'AHMADI, SKM., M. KL', 'unit' => 'PUSAT KARIR'],
            ['jabatan' => 'STAFF PUSAT KARIR, ALUMNI DAN KEWIRAUSAHAAN', 'name' => 'LAILATUL QADRY, S.Kom', 'unit' => 'PUSAT KARIR'],
            ['jabatan' => 'KABID. PERENCANAAN DAN PENGEMBANGAN', 'name' => 'AGUS SURYADI, S. Kom., M. Kom', 'unit' => 'PERENCANAAN'],
            ['jabatan' => 'STAFF PERENCANAAN DAN PENGEMBANGAN', 'name' => 'ASRAF', 'unit' => 'PERENCANAAN'],

            // LPPM
            ['jabatan' => 'KA. LPPM', 'name' => 'Dr. ICE IRAWATI, SKM.,M.Kes', 'unit' => 'LPPM'],
            ['jabatan' => 'KABID. PENELITIAN', 'name' => 'YANDRA RIVALDO, SE.,MM', 'unit' => 'LPPM'],
            ['jabatan' => 'KABID. PENGABDIAN KEPADA MASYARAKAT', 'name' => 'KHOERUN NISA SAFITRI, ST.,MT', 'unit' => 'LPPM'],
            ['jabatan' => 'STAFF ADMINISTRASI PENELITIAN DAN PENGABDIAN KEPADA MASYARAKAT', 'name' => 'MARLINA UMAR, S.Kom', 'unit' => 'LPPM'],
            ['jabatan' => 'KABID. HAKI DAN PUBLIKASI', 'name' => 'NANDA JARTI, M.Kom', 'unit' => 'LPPM'],

            // LPMI
            ['jabatan' => 'KA. LPMI', 'name' => 'RONI SAPUTRA, S.Si., M.Si', 'unit' => 'LPMI'],
            ['jabatan' => 'KABID. PENGEMBANGAN SPMI DAN SDM SPMI', 'name' => 'NOVI HENDRI ADI, S.Pd., M.Pd.T', 'unit' => 'LPMI'],
            ['jabatan' => 'KABID. SOSIALISASI SPMI DAN KERJASAMA SPMI', 'name' => 'Ir. INDAH KUSUMA DEWI, S.Kom.,M.Kom', 'unit' => 'LPMI'],
            ['jabatan' => 'KABID. AKREDITASI DAN DOKUMENTASI', 'name' => 'CHINTA YOLANDA SARI, SKM.,M.KM', 'unit' => 'LPMI'],
            ['jabatan' => 'KABID. EVALUASI DAN AUDIT MUTU', 'name' => 'WAN INTAN PARISMA, S.ST.,M.KM', 'unit' => 'LPMI'],

            // FAKULTAS EKONOMI DAN BISNIS (FEB)
            ['jabatan' => 'DEKAN FAKULTAS EKONOMI DAN BISNIS', 'name' => 'Dr. SABRI, S.E., MM', 'unit' => 'FAKULTAS EKONOMI DAN BISNIS'],
            ['jabatan' => 'WAKIL DEKAN I (Akademik, Kemahasiswaan dan Pusat Karir)', 'name' => 'MULYADI, S.E.,MM', 'unit' => 'FAKULTAS EKONOMI DAN BISNIS'],
            ['jabatan' => 'WAKIL DEKAN II (Administrasi dan SDM)', 'name' => 'ITA MUSTIKA, S.E., M.Ak', 'unit' => 'FAKULTAS EKONOMI DAN BISNIS'],
            ['jabatan' => 'KETUA PROGRAM STUDI S1 MANAJEMEN', 'name' => 'SEPTA DIANA NABELLA, SE., MM', 'unit' => 'PRODI S1 MANAJEMEN'],
            ['jabatan' => 'SEKRETARIS PRODI S1 MANAJEMEN', 'name' => 'INA KURNIATI, SE.,MM', 'unit' => 'PRODI S1 MANAJEMEN'],
            ['jabatan' => 'KETUA PROGRAM STUDI S1 AKUNTANSI', 'name' => 'MAYA RICHMAYATI, SE., M.Ak', 'unit' => 'PRODI S1 AKUNTANSI'],
            ['jabatan' => 'SEKRETARIS PRODI S1 AKUNTANSI', 'name' => 'KHADIJAH, SE.,M.Ak', 'unit' => 'PRODI S1 AKUNTANSI'],
            ['jabatan' => 'KETUA PROGRAM STUDI PASCASARJANA MAGISTER MANAJEMEN', 'name' => 'Dr. HENDRI HERMAN, SE., M.Si', 'unit' => 'PRODI S2 MANAJEMEN'],
            ['jabatan' => 'SEKRETARIS PRODI PASCASARJANA MAGISTER MANAJEMEN', 'name' => 'Dr. SUHARDI, S.E., M.M', 'unit' => 'PRODI S2 MANAJEMEN'],
            ['jabatan' => 'UPMI FEB', 'name' => 'Dr. ANDIKA PRASETYA NUGRAHA, SE.,MM', 'unit' => 'FAKULTAS EKONOMI DAN BISNIS'],
            ['jabatan' => 'UPPM FEB', 'name' => 'DEWI PERMATA SARI, S.E., M.M', 'unit' => 'FAKULTAS EKONOMI DAN BISNIS'],
            ['jabatan' => 'TATA USAHA SARJANA FEB', 'name' => 'MUHAMMAD GUNAWAN, S.KL.,MM', 'unit' => 'FAKULTAS EKONOMI DAN BISNIS'],
            ['jabatan' => 'TATA USAHA PASCASARJANA FEB', 'name' => 'RAHMAN SYAHPUTRA, S.Sos', 'unit' => 'FAKULTAS EKONOMI DAN BISNIS'],
            ['jabatan' => 'HUMAS DAN PUBLIKASI WEB FEB', 'name' => 'MUHAMMAD FIRNANDA, S.T', 'unit' => 'FAKULTAS EKONOMI DAN BISNIS'],

            // FAKULTAS SAINS DAN TEKNOLOGI (FST)
            ['jabatan' => 'DEKAN FAKULTAS SAINS DAN TEKNOLOGI', 'name' => 'Ir. SANUSI, ST., M.Eng., Ph.D., IPM', 'unit' => 'FAKULTAS SAINS DAN TEKNOLOGI'],
            ['jabatan' => 'WAKIL DEKAN I (Akademik, Kemahasiswaan dan Pusat Karir)', 'name' => 'OKTA VESA, S.Kom., M.Kom', 'unit' => 'FAKULTAS SAINS DAN TEKNOLOGI'],
            ['jabatan' => 'WAKIL DEKAN II (Administrasi dan SDM)', 'name' => 'ANDI HEPY SUSANTI, S.E, M.M', 'unit' => 'FAKULTAS SAINS DAN TEKNOLOGI'],
            ['jabatan' => 'KETUA PROGRAM STUDI TEKNIK INDUSTRI', 'name' => 'Ir. HERMAN, ST.,MT', 'unit' => 'PRODI TEKNIK INDUSTRI'],
            ['jabatan' => 'SEKRETARIS PROGRAM STUDI TEKNIK INDUSTRI', 'name' => 'RIZKY PRAKASA HASIBUAN, S.T., M.T', 'unit' => 'PRODI TEKNIK INDUSTRI'],
            ['jabatan' => 'KETUA PROGRAM STUDI TEKNIK INFORMATIKA DAN SISTEM INFORMASI', 'name' => 'ARMY TRILIDIA DEVEGA, S.Kom, M.Pd', 'unit' => 'PRODI TEKNIK INFORMATIKA'],
            ['jabatan' => 'SEKRETARIS PROGRAM STUDI TEKNIK INFORMATIKA DAN SISTEM INFORMASI', 'name' => 'ATMAN LUCKY F, S.Kom., M.Kom', 'unit' => 'PRODI TEKNIK INFORMATIKA'],
            ['jabatan' => 'KETUA PROGRAM STUDI TEKNIK LOGISTIK DAN PERKAPALAN', 'name' => 'ALBERTUS LAURENSIUS SETYABUDHI, ST., M.MT., IPM', 'unit' => 'PRODI TEKNIK LOGISTIK'],
            ['jabatan' => 'SEKRETARIS PROGRAM STUDI TEKNIK LOGISTIK DAN PERKAPALAN', 'name' => 'SITI AISYAH JAMAL, S.T., M.T', 'unit' => 'PRODI TEKNIK LOGISTIK'],
            ['jabatan' => 'TATA USAHA FST', 'name' => 'Ansar, S.E., M.M', 'unit' => 'FAKULTAS SAINS DAN TEKNOLOGI'],
            ['jabatan' => 'UPMI FST', 'name' => 'AFRINA, S.Kom, M.Si', 'unit' => 'FAKULTAS SAINS DAN TEKNOLOGI'],
            ['jabatan' => 'KA. UPPM FST', 'name' => 'Ir. ERY SUGITO, S.T., M.T', 'unit' => 'FAKULTAS SAINS DAN TEKNOLOGI'],
            ['jabatan' => 'STAFF UPPM FST', 'name' => 'NOFRI YUDI, S.Kom., M.Kom', 'unit' => 'FAKULTAS SAINS DAN TEKNOLOGI'],
            ['jabatan' => 'KA. LABORATORIUM FAKULTAS SAINS DAN TEKNOLOGI', 'name' => 'FAJRINALDI, S.T', 'unit' => 'LABORATORIUM FST'],
            ['jabatan' => 'STAFF LABOR TEKNIK INDUSTRI', 'name' => 'LUTHFIA, S.T', 'unit' => 'LABORATORIUM FST'],
            ['jabatan' => 'STAFF LABOR TEKNIK KOMPUTER', 'name' => 'MARDINUR, S.Kom', 'unit' => 'LABORATORIUM FST'],
            ['jabatan' => 'STAFF LABOR TEKNIK PROSES PRODUKSI', 'name' => 'M. ISRA NUR ABDULLAH YUZUL, S.T', 'unit' => 'LABORATORIUM FST'],
            ['jabatan' => 'KA. HUMAS DAN PUBLIKASI FST', 'name' => 'MUTIARA AYU MAWADDAH, S.Kom', 'unit' => 'FAKULTAS SAINS DAN TEKNOLOGI'],
            ['jabatan' => 'STAFF HUMAS DAN PUBLIKASI FST', 'name' => 'ANDI AMIN AMIRUL MUKMININ, S.Kom', 'unit' => 'FAKULTAS SAINS DAN TEKNOLOGI'],

            // FAKULTAS ILMU KESEHATAN (FIKES)
            ['jabatan' => 'DEKAN FAKULTAS ILMU KESEHATAN', 'name' => 'Dr. HENGKY OKTARIZAL, SKM., M.KM', 'unit' => 'FAKULTAS ILMU KESEHATAN'],
            ['jabatan' => 'WAKIL DEKAN I (Akademik, Kemahasiswaan dan Pusat Karir)', 'name' => 'dr. Hj. ELSUSI MARTHA, M.KM', 'unit' => 'FAKULTAS ILMU KESEHATAN'],
            ['jabatan' => 'WAKIL DEKAN II (Administrasi dan SDM)', 'name' => 'RIZQI ULLA AMALIAH, SKM., M.KKK', 'unit' => 'FAKULTAS ILMU KESEHATAN'],
            ['jabatan' => 'KETUA PROGRAM STUDI K3', 'name' => 'TRISNA DEWITA, S.KM., M.Kes', 'unit' => 'PRODI K3'],
            ['jabatan' => 'SEKRETARIS PRODI K3', 'name' => 'TRISNA JAYATI, M.KM', 'unit' => 'PRODI K3'],
            ['jabatan' => 'KETUA PROGRAM STUDI KESLING', 'name' => 'M. KAFIT, SKM., M.Kes', 'unit' => 'PRODI KESLING'],
            ['jabatan' => 'SEKRETARIS PRODI KESLING', 'name' => 'AL HAFEZ HUSEIN, S.KM., M.KL', 'unit' => 'PRODI KESLING'],
            ['jabatan' => 'UPMI FIKES', 'name' => 'NOVELA SARI, SKM.,M.Kes', 'unit' => 'FAKULTAS ILMU KESEHATAN'],
            ['jabatan' => 'GKM FIKES', 'name' => 'FAJAR, SKM., M.Kes', 'unit' => 'FAKULTAS ILMU KESEHATAN'],
            ['jabatan' => 'UPPM FIKES', 'name' => 'ANITA PRAMAWATI, S.KM., M.Si', 'unit' => 'FAKULTAS ILMU KESEHATAN'],
            ['jabatan' => 'LABORAN FIKES', 'name' => 'JAMAL, S.KL.,M.KM', 'unit' => 'FAKULTAS ILMU KESEHATAN'],
            ['jabatan' => 'TATA USAHA FIKES', 'name' => 'PERAWATY SELFIA NASUTION, Amd., KL', 'unit' => 'FAKULTAS ILMU KESEHATAN'],
            ['jabatan' => 'HUMAS DAN PUBLIKASI WEB FIKES', 'name' => 'UJANG AMIR HAMZAH, S.Kom', 'unit' => 'FAKULTAS ILMU KESEHATAN'],
        ];

        // Superadmin & Admin default
        User::updateOrCreate(
            ['email' => 'superadmin@uis.ac.id'],
            [
                'name' => 'superadmin',
                'roles' => 'superadmin',
                'nidn' => '-',
                'jabatan' => 'SUPERADMIN',
                'status' => 'Aktif',
                'unit' => 'REKTORAT',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin@uis.ac.id'],
            [
                'name' => 'admin',
                'roles' => 'admin',
                'nidn' => '-',
                'jabatan' => 'ADMIN',
                'status' => 'Aktif',
                'unit' => 'REKTORAT',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );

        $usedEmails = ['superadmin@uis.ac.id', 'admin@uis.ac.id'];

        foreach ($data as $item) {
            // Generate clean email from name
            $cleanName = preg_replace('/^(Assoc\.|Prof\.|Dr\.|dr\.|Ir\.|Hj\.)\s*/i', '', $item['name']);
            $nameParts = explode(',', $cleanName);
            $baseName = trim($nameParts[0]);
            $slug = Str::slug($baseName, '.');
            if (empty($slug)) {
                $slug = 'user.' . Str::random(5);
            }
            $email = $slug . '@uis.ac.id';

            // Ensure email uniqueness
            $counter = 1;
            $originalEmail = $email;
            while (in_array($email, $usedEmails)) {
                $email = str_replace('@uis.ac.id', $counter . '@uis.ac.id', $originalEmail);
                $counter++;
            }
            $usedEmails[] = $email;

            $usedRole = 'DOSEN';
            $j = strtoupper($item['jabatan']);

            if (!empty($item['roles']) && strtoupper($item['roles']) === 'SUPERADMIN') {
                $usedRole = 'SUPER ADMIN';
            } elseif ($j === 'REKTOR') {
                $usedRole = 'REKTOR';
            } elseif ($j === 'WAKIL REKTOR I') {
                $usedRole = 'WAKIL REKTOR I';
            } elseif ($j === 'WAKIL REKTOR II') {
                $usedRole = 'WAKIL REKTOR II';
            } elseif ($j === 'WAKIL REKTOR III') {
                $usedRole = 'WAKIL REKTOR III';
            } elseif (str_contains($j, 'KEPALA LPTI') || str_contains($j, 'KEPALA ICT')) {
                $usedRole = 'KEPALA ICT';
            } elseif (str_contains($j, 'PROGRAMMER') || str_contains($j, 'IT SUPPORT') || str_contains($j, 'DIVISI PENGEMBANGAN SISTEM') || str_contains($j, 'DIVISI INFRASTRUKTUR')) {
                $usedRole = 'ADMIN ICT';
            } elseif (str_contains($j, 'KA. BIRO ADMINISTRASI AKADEMIK') || str_contains($j, 'KABID. AKADEMIK')) {
                $usedRole = 'ADMIN AKADEMIK UNIVERSITAS';
            } elseif (str_contains($j, 'STAFF AKADEMIK FAKULTAS')) {
                $usedRole = 'STAFF AKADEMIK FAKULTAS';
            } elseif (str_contains($j, 'LAYANAN KEMAHASISWAAN') || str_contains($j, 'KABID. KEMAHASISWAAN')) {
                $usedRole = 'ADMIN KEMAHASISWAAN';
            } elseif (str_contains($j, 'LAYANAN IJAZAH')) {
                $usedRole = 'ADMIN IJAZAH';
            } elseif (str_contains($j, 'PERPUSTAKAAN') || str_contains($j, 'PUSTAKAWAN')) {
                $usedRole = 'ADMIN PERPUSTAKAAN';
            } elseif (str_contains($j, 'KABID. KEUANGAN') || str_contains($j, 'STAFF KEUANGAN') || str_contains($j, 'KA. BIRO ADMINISTRASI UMUM DAN KEUANGAN')) {
                $usedRole = 'ADMIN KEUANGAN';
            } elseif (str_contains($j, 'KASIR')) {
                $usedRole = 'KASIR';
            } elseif (str_contains($j, 'SDM') || str_contains($j, 'KEPEGAWAIAN')) {
                $usedRole = 'ADMIN SDM';
            } elseif (str_contains($j, 'SARANA DAN PRASARANA') || str_contains($j, 'SARPRAS')) {
                $usedRole = 'ADMIN SARPRAS';
            } elseif (str_contains($j, 'HUMAS') || str_contains($j, 'DOKUMENTASI') || str_contains($j, 'WEBSITE')) {
                $usedRole = 'ADMIN HUMAS';
            } elseif (str_contains($j, 'KERJASAMA')) {
                $usedRole = 'ADMIN KERJASAMA';
            } elseif (str_contains($j, 'PRESTASI')) {
                $usedRole = 'ADMIN KEMAHASISWAAN PRESTASI';
            } elseif (str_contains($j, 'PUSAT KARIR') || str_contains($j, 'ALUMNI')) {
                $usedRole = 'ADMIN KARIR ALUMNI';
            } elseif (str_contains($j, 'PERENCANAAN')) {
                $usedRole = 'ADMIN PERENCANAAN';
            } elseif (str_contains($j, 'KA. LPPM') || str_contains($j, 'PENELITIAN') || str_contains($j, 'PENGABDIAN')) {
                $usedRole = 'ADMIN LPPM';
            } elseif (str_contains($j, 'HAKI')) {
                $usedRole = 'ADMIN HAKI';
            } elseif (str_contains($j, 'LPMI') || str_contains($j, 'SPMI') || str_contains($j, 'AKREDITASI') || str_contains($j, 'AUDIT MUTU')) {
                $usedRole = 'ADMIN LPMI';
            } elseif (str_contains($j, 'DEKAN FAKULTAS')) {
                $usedRole = 'DEKAN';
            } elseif (str_contains($j, 'WAKIL DEKAN I')) {
                $usedRole = 'WAKIL DEKAN I';
            } elseif (str_contains($j, 'WAKIL DEKAN II')) {
                $usedRole = 'WAKIL DEKAN II';
            } elseif (str_contains($j, 'KETUA PROGRAM STUDI')) {
                $usedRole = 'KAPRODI';
            } elseif (str_contains($j, 'SEKRETARIS PRODI')) {
                $usedRole = 'SEKRETARIS PRODI';
            } elseif (str_contains($j, 'DOSEN PEMBIMBING')) {
                $usedRole = 'DOSEN PEMBIMBING';
            } elseif (str_contains($j, 'DOSEN')) {
                $usedRole = 'DOSEN';
            } elseif (str_contains($j, 'TATA USAHA') || str_contains($j, 'STAFF TU')) {
                $usedRole = 'STAFF TU FAKULTAS';
            } elseif (str_contains($j, 'LABORATORIUM') || str_contains($j, 'LABORAN')) {
                $usedRole = 'ADMIN LABORATORIUM';
            } elseif (str_contains($j, 'KA. BIRO')) {
                $usedRole = 'KEPALA BIRO';
            }

            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $item['name'],
                    'roles' => $usedRole,
                    'nidn' => '-',
                    'jabatan' => $item['jabatan'],
                    'status' => 'Aktif',
                    'unit' => $item['unit'],
                    'password' => Hash::make('password'),
                    'is_active' => true,
                ]
            );
        }
    }
}
