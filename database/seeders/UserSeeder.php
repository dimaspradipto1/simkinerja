<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Akun Sistem Default
        User::updateOrCreate(
            ['email' => 'superadmin@uis.ac.id'],
            [
                'name' => 'superadmin',
                'roles' => 'super admin',
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

        $users = [
            [
                'name' => 'Assoc. Prof. Dr. Ir. LARISANG, MT.,IPU',
                'email' => 'larisang@uis.ac.id',
                'jabatan' => 'REKTOR',
                'unit' => 'REKTORAT',
                'roles' => 'pimpinan rektorat, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Penanggung Jawab',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Penasehat',
  ),
),
            ],
            [
                'name' => 'FITRI SARI DEWI, SKM.,M.KKK',
                'email' => 'fitri.sari@uis.ac.id',
                'jabatan' => 'WAKIL REKTOR I',
                'unit' => 'REKTORAT',
                'roles' => 'pimpinan rektorat, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Pengarah',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Pengarah',
  ),
),
            ],
            [
                'name' => 'ANDI AULIYA RAMADHANY, SE., M.Akt',
                'email' => 'andi.auliya@uis.ac.id',
                'jabatan' => 'WAKIL REKTOR II',
                'unit' => 'REKTORAT',
                'roles' => 'pimpinan rektorat, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Pengarah',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Pengarah',
  ),
),
            ],
            [
                'name' => 'Dr. SUMARDIN, SE., M.Si',
                'email' => 'sumardin@uis.ac.id',
                'jabatan' => 'WAKIL REKTOR III',
                'unit' => 'REKTORAT',
                'roles' => 'pimpinan rektorat, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Pengarah',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Pengarah',
  ),
),
            ],
            [
                'name' => 'LENI UTAMI, S.Si., M. KM',
                'email' => 'leni.utami@uis.ac.id',
                'jabatan' => 'KA. BIRO ADMINISTRASI AKADEMIK KEMAHASISWAAN (BAAK)',
                'unit' => 'BAAK',
                'roles' => 'pimpinan unit, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Koordinator Seksi Kesekretariatan',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Wakil Ketua',
  ),
),
            ],
            [
                'name' => 'MUHAMMAD FADLI LURAN, SE., MM',
                'email' => 'fadli@uis.ac.id',
                'jabatan' => 'KABID. AKADEMIK',
                'unit' => 'BAAK',
                'roles' => 'pimpinan sub unit, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Acara',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Sekretariat',
  ),
),
            ],
            [
                'name' => 'ANDI AMIN SURYAWAN, SE',
                'email' => 'andi.fariz@uis.ac.id',
                'jabatan' => 'STAFF AKADEMIK FAKULTAS EKONOMI DAN BISNIS',
                'unit' => 'BAAK - FEB',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Kesekretariatan FEB',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Sekretariat',
  ),
),
            ],
            [
                'name' => 'JUHANDA KARTIKA WIJAYA, SKL.,M.KM',
                'email' => 'juhanda@uis.ac.id',
                'jabatan' => 'STAFF AKADEMIK FAKULTAS ILMU KESEHATAN',
                'unit' => 'BAAK - FIKES',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Seksi Kelompok 21',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Pengawas',
  ),
),
            ],
            [
                'name' => 'ADNAN, SE.,MM',
                'email' => 'adnan@uis.ac.id',
                'jabatan' => 'KABID. ADMINISTRASI DAN LAYANAN KEMAHASISWAAN',
                'unit' => 'BAAK',
                'roles' => 'pimpinan sub unit, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Seksi Kelompok 5',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Koordinator Seksi Sekretariat',
  ),
),
            ],
            [
                'name' => 'META NILA SARI, S.M',
                'email' => 'metanilasari@uis.ac.id',
                'jabatan' => 'STAFF - SUPPORT LAYANAN NILAI DAN PERKULIAHAN',
                'unit' => 'BAAK',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Kesekretariatan FIKes',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Sekretariat',
  ),
),
            ],
            [
                'name' => 'ANDI RICKY HAMUR, SE',
                'email' => 'andi.ricky@uis.ac.id',
                'jabatan' => 'STAFF - SUPPORT LAYANAN IJAZAH',
                'unit' => 'BAAK',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Kesekretariatan FEB',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Sekretariat',
  ),
),
            ],
            [
                'name' => 'HASAN HUSEN, S.IP',
                'email' => 'hasan.husen@uis.ac.id',
                'jabatan' => 'KEPALA PERPUSTAKAAN',
                'unit' => 'PERPUSTAKAAN',
                'roles' => 'pimpinan unit, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Seksi Kelompok 23',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Pengawas',
  ),
),
            ],
            [
                'name' => 'NISHFU NURBAYANTI',
                'email' => 'nishfu@uis.ac.id',
                'jabatan' => 'PUSTAKAWAN',
                'unit' => 'PERPUSTAKAAN',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Konsumsi',
  ),
),
            ],
            [
                'name' => 'NURMAYUNITA, SE., MM',
                'email' => 'nurmayunita@uis.ac.id',
                'jabatan' => 'KABID. SDM DAN UMUM',
                'unit' => 'SDM & UMUM',
                'roles' => 'pimpinan sub unit, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Seksi Kelompok 3',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'anggota Seksi Konsumsi',
  ),
),
            ],
            [
                'name' => 'MAISYITA RISMA SURYANI, SKL',
                'email' => 'maisyita.risma@uis.ac.id',
                'jabatan' => 'KABID. KEUANGAN',
                'unit' => 'BAUK',
                'roles' => 'pimpinan sub unit, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Wakil Bendahara I',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Wakil Bendehara',
  ),
),
            ],
            [
                'name' => 'ANDI SAIDAH, SE., M.M',
                'email' => 'andi.saidah@uis.ac.id',
                'jabatan' => 'KASIR REKTORAT',
                'unit' => 'BAUK',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Konsumsi',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'anggota Seksi Konsumsi',
  ),
),
            ],
            [
                'name' => 'SHINTA WAHYUNI, SE.,MM',
                'email' => 'shinta.wahyuni@uis.ac.id',
                'jabatan' => 'STAFF KEUANGAN FAKULTAS EKONOMI DAN BISNIS',
                'unit' => 'BAUK - FEB',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Kesekretariatan FEB',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Validasi Pendaftaran FEB',
  ),
),
            ],
            [
                'name' => 'ZHALFA PUTRI ELISA, SE., M.M',
                'email' => 'zhalfa.putri@uis.ac.id',
                'jabatan' => 'STAFF KEUANGAN FAKULTAS EKONOMI DAN BISNIS',
                'unit' => 'BAUK - FEB',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Kesekretariatan FEB',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Validasi Pendaftaran FEB',
  ),
),
            ],
            [
                'name' => 'ANDI HUMAIMAH, S. Kes',
                'email' => 'umi@uis.ac.id',
                'jabatan' => 'STAFF KEUANGAN FAKULTAS SAINS DAN TEKNOLOGI',
                'unit' => 'BAUK - FST',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Kesekretariatan FST',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Validasi Pendaftaran FST',
  ),
),
            ],
            [
                'name' => 'MAISARAH PERTIWI, S.M',
                'email' => 'maisarah@uis.ac.id',
                'jabatan' => 'STAFF KEUANGAN FAKULTAS SAINS DAN TEKNOLOGI',
                'unit' => 'BAUK - FST',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Kesekretariatan FST',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Validasi Pendaftaran FST',
  ),
),
            ],
            [
                'name' => 'SALSABILA RIVANIA, S.Ak',
                'email' => 'salsabila@uis.ac.id',
                'jabatan' => 'STAFF KEUANGAN FAKULTAS ILMU KESEHATAN',
                'unit' => 'BAUK - FIKES',
                'roles' => 'staff',
                'jabatan_kepanitiaan' => null,
            ],
            [
                'name' => 'ISHAK, S.E., M.M',
                'email' => 'ishak@uis.ac.id',
                'jabatan' => 'OPERATOR SDM',
                'unit' => 'SDM & UMUM',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Seksi Kelompok 8',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Pengawas',
  ),
),
            ],
            [
                'name' => 'ROSALINA, SE',
                'email' => 'rosalina@uis.ac.id',
                'jabatan' => 'STAFF KEPEGAWAIAN',
                'unit' => 'SDM & UMUM',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Konsumsi',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'anggota Seksi Konsumsi',
  ),
),
            ],
            [
                'name' => 'dr. ANDI KHAIRUNNISA',
                'email' => 'andi.khairunnisa@uis.ac.id',
                'jabatan' => 'KABID. SARANA DAN PRASARANA',
                'unit' => 'SARPRAS',
                'roles' => 'pimpinan sub unit, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Koordinator Seksi Perlengkapan',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Koordinator Seksi Perlengkapan',
  ),
),
            ],
            [
                'name' => 'CHOIRUL ANAM, S.Kom',
                'email' => 'choirulanam@uis.ac.id',
                'jabatan' => 'ADMIN UMUM SARPRAS',
                'unit' => 'SARPRAS',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Perlengkapan',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Perlengkapan',
  ),
),
            ],
            [
                'name' => 'APRIYADI',
                'email' => 'apriyadi@uis.ac.id',
                'jabatan' => 'STAFF SARPRAS',
                'unit' => 'SARPRAS',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Perlengkapan',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Perlengkapan',
  ),
),
            ],
            [
                'name' => 'ANDI TAHER, SE, MM',
                'email' => 'taher@uis.ac.id',
                'jabatan' => 'STAFF SARPRAS',
                'unit' => 'SARPRAS',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Perlengkapan',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Perlengkapan',
  ),
),
            ],
            [
                'name' => 'ANDI AWALUDDIN, S.Kom',
                'email' => 'andi.awal@uis.ac.id',
                'jabatan' => 'TATA USAHA BAUK',
                'unit' => 'BAUK',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Acara',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Sekretariat',
  ),
),
            ],
            [
                'name' => 'Dr. MUHAMMAD ROPIANTO, S.Kom., M.Kom',
                'email' => 'ropianto@uis.ac.id',
                'jabatan' => 'KEPALA LPTI',
                'unit' => 'LPTI',
                'roles' => 'admin, pimpinan unit, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Koordinator Seksi LPTI',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Koordinator Seksi LPTI',
  ),
),
            ],
            [
                'name' => 'ANDI MUAMAR, S.Kom',
                'email' => 'andi.muamar@uis.ac.id',
                'jabatan' => 'DIVISI PENGEMBANGAN SISTEM INFORMASI DAN APLIKASI - LPTI BAUK',
                'unit' => 'LPTI',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi LPTI',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi LPTI',
  ),
),
            ],
            [
                'name' => 'DICKY ASHRUL IBRAHIM, S.Kom',
                'email' => 'dickyashrul@uis.ac.id',
                'jabatan' => 'DIVISI INFRASTRUKTUR, JARINGAN, DAN LAYANAN TROUBLESHOOTING',
                'unit' => 'LPTI',
                'roles' => 'staff',
                'jabatan_kepanitiaan' => null,
            ],
            [
                'name' => 'Dr. HERDIANTI, M.Kes',
                'email' => 'herdianti@uis.ac.id',
                'jabatan' => 'KA. BIRO KEMAHASISWAAN, ALUMNI, KERJASAMA, PERENCANAAN DAN PENGEMBANGAN',
                'unit' => 'BIRO KEMAHASISWAAN',
                'roles' => 'pimpinan unit, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Wakil Ketua IV',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Wakil Ketua',
  ),
),
            ],
            [
                'name' => 'ANDI AKBAR, SE., MM',
                'email' => 'andiakbar@uis.ac.id',
                'jabatan' => 'KABID. HUMAS DAN PUBLIKASI',
                'unit' => 'HUMAS',
                'roles' => 'pimpinan sub unit, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Ketua Pelaksana',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Koordinator Seksi Desain Publikasi dan Dokumentasi',
  ),
),
            ],
            [
                'name' => 'NOPAN ASWANDI, S. Kom',
                'email' => 'novan@uis.ac.id',
                'jabatan' => 'STAFF DOKUMENTASI',
                'unit' => 'HUMAS',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Desain, Dokumentasi dan Publikasi',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Desain Publikasi dan Dokumentasi',
  ),
),
            ],
            [
                'name' => 'RAGA PRASANDI, S.E',
                'email' => 'ragaprasandi@uis.ac.id',
                'jabatan' => 'STAFF HUMAS',
                'unit' => 'HUMAS',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Acara',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Acara',
  ),
),
            ],
            [
                'name' => 'TASLIMAHUDIN, S.Sos',
                'email' => 'taslimahudin@gmail.com',
                'jabatan' => 'STAFF WEBSITE',
                'unit' => 'HUMAS',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Desain, Dokumentasi dan Publikasi',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Ketua Pelaksana',
  ),
),
            ],
            [
                'name' => 'AMIRULLAH, S.Kom., MM',
                'email' => 'amirullah@uis.ac.id',
                'jabatan' => 'KABID. KERJASAMA',
                'unit' => 'KERJASAMA',
                'roles' => 'pimpinan sub unit, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Acara',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Acara',
  ),
),
            ],
            [
                'name' => 'AGUS SUTIANDI, S.Ak',
                'email' => 'agus.s@uis.ac.id',
                'jabatan' => 'STAFF KERJASAMA - INTERNASIONAL',
                'unit' => 'KERJASAMA',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Koordinator Seksi Acara',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Koordinator Seksi Acara',
  ),
),
            ],
            [
                'name' => 'ANDI RUSLAN, SE., M.M',
                'email' => 'andi.ruslan@uis.ac.id',
                'jabatan' => 'STAFF KERJASAMA - NASIONAL',
                'unit' => 'KERJASAMA',
                'roles' => 'staff',
                'jabatan_kepanitiaan' => null,
            ],
            [
                'name' => 'MARDA LISA, S.Ak',
                'email' => 'marda.lisa@uis.ac.id',
                'jabatan' => 'STAFF KERJASAMA - NASIONAL',
                'unit' => 'KERJASAMA',
                'roles' => 'staff',
                'jabatan_kepanitiaan' => null,
            ],
            [
                'name' => 'Dr. ABDUL ROHMAD BASAR, S.Kom., M.Kom',
                'email' => 'basar@uis.ac.id',
                'jabatan' => 'KABID. KEMAHASISWAAN',
                'unit' => 'BIRO KEMAHASISWAAN',
                'roles' => 'pimpinan sub unit, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Koordinator Seksi Kelompok',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Pengawas',
  ),
),
            ],
            [
                'name' => 'RASMIN NUR JADING, SKM.,M.Kes',
                'email' => 'rasmin@uis.ac.id',
                'jabatan' => 'STAFF KEMAHASISWAAN - KIP',
                'unit' => 'BIRO KEMAHASISWAAN',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Koordinator Seksi Kelompok',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Koordinator Seksi Pengawas',
  ),
),
            ],
            [
                'name' => 'Andi Mahdi Syahraini, S.M',
                'email' => 'andimahdi1991@uis.ac.id',
                'jabatan' => 'STAFF KEMAHASISWAAN - PRESTASI OLAH RAGA',
                'unit' => 'BIRO KEMAHASISWAAN',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Acara',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Acara',
  ),
),
            ],
            [
                'name' => 'AZMIL FAUZI FARISKA, SH.,MH',
                'email' => 'azmil@uis.ac.id',
                'jabatan' => 'STAFF KEMAHASISWAAN - PRESTASI SENI',
                'unit' => 'UNSUR PELAKSANA ADMINISTRASI',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Wakil Sekretaris I',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Wakil Sekretaris',
  ),
),
            ],
            [
                'name' => 'Dr. Ahmadi, S.KM., M.K',
                'email' => 'ahmadi@uis.ac.id',
                'jabatan' => 'KABID. PUSAT KARIR, ALUMNI DAN KEWIRAUSAHAAN',
                'unit' => 'PUSAT KARIR',
                'roles' => 'pimpinan sub unit, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Acara',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Acara',
  ),
),
            ],
            [
                'name' => 'LAILATUL QADRY, S.Kom',
                'email' => 'lailatulqadry@uis.ac.id',
                'jabatan' => 'STAFF PUSAT KARIR, ALUMNI DAN KEWIRAUSAHAAN',
                'unit' => 'PUSAT KARIR',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Wakil Sekretaris II',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Acara',
  ),
),
            ],
            [
                'name' => 'ASRAF',
                'email' => 'asraf@uis.ac.id',
                'jabatan' => 'STAFF PERENCANAAN DAN PENGEMBANGAN',
                'unit' => 'PERENCANAAN',
                'roles' => 'staff',
                'jabatan_kepanitiaan' => null,
            ],
            [
                'name' => 'Dr. ICE IRAWATI, SKM.,M.Kes',
                'email' => 'iceirawati@uis.ac.id',
                'jabatan' => 'KA. LPPM',
                'unit' => 'LPPM',
                'roles' => 'pimpinan unit',
                'jabatan_kepanitiaan' => null,
            ],
            [
                'name' => 'YANDRA RIVALDO, SE.,MM',
                'email' => 'yandra.rivaldo@gmail.com',
                'jabatan' => 'KABID. PENELITIAN',
                'unit' => 'LPPM',
                'roles' => 'pimpinan sub unit',
                'jabatan_kepanitiaan' => null,
            ],
            [
                'name' => 'Ir. Khoerun Nisa Safitri, S.T., M.T',
                'email' => 'khoerunnisa@uis.ac.id',
                'jabatan' => 'KABID. PENGABDIAN KEPADA MASYARAKAT',
                'unit' => 'LPPM',
                'roles' => 'pimpinan sub unit',
                'jabatan_kepanitiaan' => null,
            ],
            [
                'name' => 'MARLINA UMAR, S.Kom',
                'email' => 'marlina.umar@uis.ac.id',
                'jabatan' => 'STAFF ADMINISTRASI PENELITIAN DAN PENGABDIAN KEPADA MASYARAKAT',
                'unit' => 'LPPM',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Konsumsi',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Koordinator Seksi Konsumsi',
  ),
),
            ],
            [
                'name' => 'Nanda Jarti, S.Kom, M.Kom.',
                'email' => 'nanda.jarti@uis.ac.id',
                'jabatan' => 'KABID. HAKI DAN PUBLIKASI',
                'unit' => 'LPPM',
                'roles' => 'pimpinan sub unit',
                'jabatan_kepanitiaan' => null,
            ],
            [
                'name' => 'RONI SAPUTRA, S.Si., M.Si',
                'email' => 'roni@uis.ac.id',
                'jabatan' => 'KA. LPMI',
                'unit' => 'LPMI',
                'roles' => 'pimpinan unit, tim evaluasi, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Koordinator Seksi Perencanaan dan Evaluasi Kegiatan',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Koordinator Seksi Evaluasi',
  ),
),
            ],
            [
                'name' => 'NOVI HENDRI ADI, S.Pd., M.Pd.T',
                'email' => 'novihendriadi@uis.ac.id',
                'jabatan' => 'KABID. PENGEMBANGAN SPMI DAN SDM SPMI',
                'unit' => 'LPMI',
                'roles' => 'pimpinan sub unit, tim evaluasi, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Perencanaan dan Evaluasi Kegiatan',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Evaluasi',
  ),
),
            ],
            [
                'name' => 'Ir. INDAH KUSUMA DEWI, S.Kom.,M.Kom',
                'email' => 'indah.kusuma@uis.ac.id',
                'jabatan' => 'KABID. SOSIALISASI SPMI DAN KERJASAMA SPMI',
                'unit' => 'LPMI',
                'roles' => 'pimpinan sub unit, tim evaluasi, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Perencanaan dan Evaluasi Kegiatan',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Evaluasi',
  ),
),
            ],
            [
                'name' => 'CHINTA YOLANDA SARI, SKM.,M.KM',
                'email' => 'chinta@uis.ac.id',
                'jabatan' => 'KABID. AKREDITASI DAN DOKUMENTASI',
                'unit' => 'LPMI',
                'roles' => 'pimpinan sub unit, tim evaluasi, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Perencanaan dan Evaluasi Kegiatan',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Evaluasi',
  ),
),
            ],
            [
                'name' => 'WAN INTAN PARISMA, S.ST.,M.KM',
                'email' => 'wanintan@uis.ac.id',
                'jabatan' => 'KABID. EVALUASI DAN AUDIT MUTU',
                'unit' => 'LPMI',
                'roles' => 'pimpinan sub unit, tim evaluasi, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Acara',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Evaluasi',
  ),
),
            ],
            [
                'name' => 'Dr. SABRI, S.E., MM',
                'email' => 'sabri@uis.ac.id',
                'jabatan' => 'DEKAN FAKULTAS EKONOMI DAN BISNIS',
                'unit' => 'FAKULTAS EKONOMI DAN BISNIS',
                'roles' => 'pimpinan unit, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Pengarah',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Pengarah',
  ),
),
            ],
            [
                'name' => 'MULYADI, S.E.,MM',
                'email' => 'mulyadi@uis.ac.id',
                'jabatan' => 'WAKIL DEKAN I (Akademik, Kemahasiswaan dan Pusat Karir)',
                'unit' => 'FAKULTAS EKONOMI DAN BISNIS',
                'roles' => 'pimpinan sub unit, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Wakil Ketua II',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Wakil Ketua',
  ),
),
            ],
            [
                'name' => 'SEPTA DIANA NABELLA, SE., MM',
                'email' => 'septa@uis.ac.id',
                'jabatan' => 'KETUA PROGRAM STUDI S1 MANAJEMEN',
                'unit' => 'PRODI S1 MANAJEMEN',
                'roles' => 'pimpinan sub unit, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Seksi Kelompok 4',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Pengawas',
  ),
),
            ],
            [
                'name' => 'INA KURNIATI, SE.,MM',
                'email' => 'inakurniati@uis.ac.id',
                'jabatan' => 'SEKRETARIS PRODI S1 MANAJEMEN',
                'unit' => 'PRODI S1 MANAJEMEN',
                'roles' => 'pimpinan sub unit, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Seksi Kelompok 7',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Pengawas',
  ),
),
            ],
            [
                'name' => 'MAYA RICHMAYATI, SE., M.Ak',
                'email' => 'maya@uis.ac.id',
                'jabatan' => 'KETUA PROGRAM STUDI S1 AKUNTANSI',
                'unit' => 'PRODI S1 AKUNTANSI',
                'roles' => 'pimpinan sub unit, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Seksi Kelompok 5',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Pengawas',
  ),
),
            ],
            [
                'name' => 'KHADIJAH, SE.,M.Ak',
                'email' => 'khadijah_mak@uis.ac.id',
                'jabatan' => 'SEKRETARIS PRODI S1 AKUNTANSI',
                'unit' => 'PRODI S1 AKUNTANSI',
                'roles' => 'pimpinan sub unit, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Seksi Kelompok 8',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Pengawas',
  ),
),
            ],
            [
                'name' => 'Dr. HENDRI HERMAN, SE., M.Si',
                'email' => 'hendri.herman@uis.ac.id',
                'jabatan' => 'KETUA PROGRAM STUDI PASCASARJANA MAGISTER MANAJEMEN',
                'unit' => 'PRODI S2 MANAJEMEN',
                'roles' => 'pimpinan sub unit, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Seksi Kelompok 1',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Pengawas',
  ),
),
            ],
            [
                'name' => 'Dr. SUHARDI, S.E., M.M',
                'email' => 'suhardi@uis.ac.id',
                'jabatan' => 'SEKRETARIS PRODI PASCASARJANA MAGISTER MANAJEMEN',
                'unit' => 'PRODI S2 MANAJEMEN',
                'roles' => 'pimpinan sub unit',
                'jabatan_kepanitiaan' => null,
            ],
            [
                'name' => 'Dr. ANDIKA PRASETYA NUGRAHA, SE.,MM',
                'email' => 'andika@uis.ac.id',
                'jabatan' => 'UPMI FEB',
                'unit' => 'FAKULTAS EKONOMI DAN BISNIS',
                'roles' => 'pimpinan sub unit, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Seksi Kelompok 2',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Pengawas',
  ),
),
            ],
            [
                'name' => 'Dr. Hj. Dewi Permasari, SE., MM',
                'email' => 'dewi.permata.sari@uis.ac.id',
                'jabatan' => 'UPPM FEB',
                'unit' => 'FAKULTAS EKONOMI DAN BISNIS',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Perencanaan dan Evaluasi Kegiatan',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Pengawas',
  ),
),
            ],
            [
                'name' => 'MUHAMMAD GUNAWAN, S.KL.,MM',
                'email' => 'muhammad.gunawan@uis.ac.id',
                'jabatan' => 'TATA USAHA SARJANA FEB',
                'unit' => 'FAKULTAS EKONOMI DAN BISNIS',
                'roles' => 'staff',
                'jabatan_kepanitiaan' => null,
            ],
            [
                'name' => 'RAHMAN SYAHPUTRA, S.Sos',
                'email' => 'rahman@uis.ac.id',
                'jabatan' => 'TATA USAHA PASCASARJANA FEB',
                'unit' => 'FAKULTAS EKONOMI DAN BISNIS',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Seksi Kelompok 3',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Pengawas',
  ),
),
            ],
            [
                'name' => 'MUHAMMAD FIRNANDA, S.T',
                'email' => 'mohammad.firnanda@uis.ac.id',
                'jabatan' => 'HUMAS DAN PUBLIKASI WEB FEB',
                'unit' => 'FAKULTAS EKONOMI DAN BISNIS',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Desain, Dokumentasi dan Publikasi',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Desain Publikasi dan Dokumentasi',
  ),
),
            ],
            [
                'name' => 'Ir. SANUSI, ST., M.Eng., Ph.D., IPM',
                'email' => 'sanusi@uis.ac.id',
                'jabatan' => 'DEKAN FAKULTAS SAINS DAN TEKNOLOGI',
                'unit' => 'FAKULTAS SAINS DAN TEKNOLOGI',
                'roles' => 'pimpinan unit, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Pengarah',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Pengarah',
  ),
),
            ],
            [
                'name' => 'ANDI HEPY SUSANTI, S.E, M.M',
                'email' => 'hepy@uis.ac.id',
                'jabatan' => 'WAKIL DEKAN II (Administrasi dan SDM)',
                'unit' => 'FAKULTAS SAINS DAN TEKNOLOGI',
                'roles' => 'pimpinan sub unit, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Wakil Bendahara III',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Wakil Bendehara',
  ),
),
            ],
            [
                'name' => 'Ir. HERMAN, ST.,MT',
                'email' => 'herman@uis.ac.id',
                'jabatan' => 'KETUA PROGRAM STUDI TEKNIK INDUSTRI',
                'unit' => 'PRODI TEKNIK INDUSTRI',
                'roles' => 'pimpinan sub unit, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Seksi Kelompok 9',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Pengawas',
  ),
),
            ],
            [
                'name' => 'RIZKI PRAKASA HASIBUAN, S.T., M.T, IPM',
                'email' => 'rizki.ph@uis.ac.id',
                'jabatan' => 'SEKRETARIS PROGRAM STUDI TEKNIK INDUSTRI',
                'unit' => 'PRODI TEKNIK INDUSTRI',
                'roles' => 'pimpinan sub unit, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Seksi Kelompok 13',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Pengawas',
  ),
),
            ],
            [
                'name' => 'ARMY TRILIDIA DEVEGA, S.Kom, M.Pd',
                'email' => 'army@uis.ac.id',
                'jabatan' => 'KETUA PROGRAM STUDI TEKNIK INFORMATIKA DAN SISTEM INFORMASI',
                'unit' => 'PRODI TEKNIK INFORMATIKA',
                'roles' => 'pimpinan sub unit, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Seksi Kelompok 12',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Pengawas',
  ),
),
            ],
            [
                'name' => 'ATMAN LUCKY F, S.Kom., M.Kom',
                'email' => 'atmanluckyf@uis.ac.id',
                'jabatan' => 'SEKRETARIS PROGRAM STUDI TEKNIK INFORMATIKA DAN SISTEM INFORMASI',
                'unit' => 'PRODI TEKNIK INFORMATIKA',
                'roles' => 'pimpinan sub unit, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Koordinator Seksi Desain, Dokumentasi dan Publikasi',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Desain Publikasi dan Dokumentasi',
  ),
),
            ],
            [
                'name' => 'ALBERTUS LAURENSIUS SETYABUDHI, ST., M.MT., IPM',
                'email' => 'abyan@uis.ac.id',
                'jabatan' => 'KETUA PROGRAM STUDI TEKNIK LOGISTIK DAN PERKAPALAN',
                'unit' => 'PRODI TEKNIK LOGISTIK',
                'roles' => 'pimpinan sub unit, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Seksi Kelompok 10',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Pengawas',
  ),
),
            ],
            [
                'name' => 'Ansar, S.E., M.M',
                'email' => 'ansar@uis.ac.id',
                'jabatan' => 'TATA USAHA FST',
                'unit' => 'FAKULTAS SAINS DAN TEKNOLOGI',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Acara',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Acara',
  ),
),
            ],
            [
                'name' => 'AFRINA, S.Kom, M.Si',
                'email' => 'afrina@uis.ac.id',
                'jabatan' => 'UPMI FST',
                'unit' => 'FAKULTAS SAINS DAN TEKNOLOGI',
                'roles' => 'pimpinan sub unit, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Perencanaan dan Evaluasi Kegiatan',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Pengawas',
  ),
),
            ],
            [
                'name' => 'Ir. Ery Sugito, ST., MT., ASCA',
                'email' => 'ery@uis.ac.id',
                'jabatan' => 'KA. UPPM FST',
                'unit' => 'FAKULTAS SAINS DAN TEKNOLOGI',
                'roles' => 'pimpinan sub unit, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Seksi Kelompok 11',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Pengawas',
  ),
),
            ],
            [
                'name' => 'NOFRI YUDI, S.Kom., M.Kom',
                'email' => 'nofri.yudi@uis.ac.id',
                'jabatan' => 'STAFF UPPM FST',
                'unit' => 'FAKULTAS SAINS DAN TEKNOLOGI',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Seksi Kelompok 16',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Pengawas',
  ),
),
            ],
            [
                'name' => 'Lutfia, ST., M.T',
                'email' => 'lutfia@uis.ac.id',
                'jabatan' => 'STAFF LABOR TEKNIK INDUSTRI',
                'unit' => 'LABORATORIUM FST',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Koordinator Seksi Konsumsi',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'anggota Seksi Konsumsi',
  ),
),
            ],
            [
                'name' => 'MARDINUR, S.Kom',
                'email' => 'mardinur@uis.ac.id',
                'jabatan' => 'STAFF LABOR TEKNIK KOMPUTER',
                'unit' => 'LABORATORIUM FST',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Acara',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Acara',
  ),
),
            ],
            [
                'name' => 'M. ISRA NUR ABDULLAH YUZUL, S.T',
                'email' => 'yuzul@uis.ac.id',
                'jabatan' => 'STAFF LABOR TEKNIK PROSES PRODUKSI',
                'unit' => 'LABORATORIUM FST',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Seksi Kelompok 11',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Pengawas',
  ),
),
            ],
            [
                'name' => 'MUTIARA AYU MAWADDAH, S.Kom',
                'email' => 'mutiaraayu@uis.ac.id',
                'jabatan' => 'KA. HUMAS DAN PUBLIKASI FST',
                'unit' => 'FAKULTAS SAINS DAN TEKNOLOGI',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Desain, Dokumentasi dan Publikasi',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Desain Publikasi dan Dokumentasi',
  ),
),
            ],
            [
                'name' => 'Dr. HENGKY OKTARIZAL, SKM., M.KM',
                'email' => 'hengky.oktarizal@uis.ac.id',
                'jabatan' => 'DEKAN FAKULTAS ILMU KESEHATAN',
                'unit' => 'FAKULTAS ILMU KESEHATAN',
                'roles' => 'pimpinan unit, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Pengarah',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Pengarah',
  ),
),
            ],
            [
                'name' => 'dr. Hj. ELSUSI MARTHA, M.KM',
                'email' => 'elsusi@uis.ac.id',
                'jabatan' => 'WAKIL DEKAN I (Akademik, Kemahasiswaan dan Pusat Karir)',
                'unit' => 'FAKULTAS ILMU KESEHATAN',
                'roles' => 'pimpinan sub unit, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Wakil Ketua I',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Wakil Ketua',
  ),
),
            ],
            [
                'name' => 'RIZQI ULLA AMALIAH, SKM., M.KKK',
                'email' => 'rizqi.ulla@uis.ac.id',
                'jabatan' => 'WAKIL DEKAN II (Administrasi dan SDM)',
                'unit' => 'FAKULTAS ILMU KESEHATAN',
                'roles' => 'pimpinan sub unit, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Wakil Bendahara IV',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Wakil Bendehara',
  ),
),
            ],
            [
                'name' => 'TRISNA DEWITA, S.KM., M.Kes',
                'email' => 'trisna.dewita@uis.ac.id',
                'jabatan' => 'KETUA PROGRAM STUDI K3',
                'unit' => 'PRODI K3',
                'roles' => 'pimpinan sub unit, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Seksi Kelompok 22',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Pengawas',
  ),
),
            ],
            [
                'name' => 'TRISNA JAYATI, M.KM',
                'email' => 'trisna.jayati@uis.ac.id',
                'jabatan' => 'SEKRETARIS PRODI K3',
                'unit' => 'PRODI K3',
                'roles' => 'pimpinan sub unit, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Seksi Kelompok 21',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Pengawas',
  ),
),
            ],
            [
                'name' => 'M. KAFIT, SKM., M.Kes',
                'email' => 'muhammad.kafit@uis.ac.id',
                'jabatan' => 'KETUA PROGRAM STUDI KESLING',
                'unit' => 'PRODI KESLING',
                'roles' => 'pimpinan sub unit, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Seksi Kelompok 19',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Pengawas',
  ),
),
            ],
            [
                'name' => 'AL HAFEZ HUSEIN, S.KM., M.KL',
                'email' => 'alhafez@uis.ac.id',
                'jabatan' => 'SEKRETARIS PRODI KESLING',
                'unit' => 'PRODI KESLING',
                'roles' => 'pimpinan sub unit',
                'jabatan_kepanitiaan' => null,
            ],
            [
                'name' => 'NOVELA SARI, SKM.,M.Kes',
                'email' => 'novela.sari@uis.ac.id',
                'jabatan' => 'UPMI FIKES',
                'unit' => 'FAKULTAS ILMU KESEHATAN',
                'roles' => 'pimpinan sub unit, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Perencanaan dan Evaluasi Kegiatan',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Pengawas',
  ),
),
            ],
            [
                'name' => 'FAJAR, SKM., M.Kes',
                'email' => 'fajar@uis.ac.id',
                'jabatan' => 'GKM FIKES',
                'unit' => 'FAKULTAS ILMU KESEHATAN',
                'roles' => 'pimpinan sub unit, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Seksi Kelompok 18',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Pengawas',
  ),
),
            ],
            [
                'name' => 'ANITA PRAMAWATI, S.KM., M.Si',
                'email' => 'anita@uis.ac.id',
                'jabatan' => 'UPPM FIKES',
                'unit' => 'FAKULTAS ILMU KESEHATAN',
                'roles' => 'staff',
                'jabatan_kepanitiaan' => null,
            ],
            [
                'name' => 'JAMAL, S.KL.,M.KM',
                'email' => 'jamal@uis.ac.id',
                'jabatan' => 'LABORAN FIKES',
                'unit' => 'FAKULTAS ILMU KESEHATAN',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Seksi Kelompok 17',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Pengawas',
  ),
),
            ],
            [
                'name' => 'PERAWATY SELFIA NASUTION, Amd., KL',
                'email' => 'perawati.selfia@uis.ac.id',
                'jabatan' => 'TATA USAHA FIKES',
                'unit' => 'FAKULTAS ILMU KESEHATAN',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Seksi Kelompok 24',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Pengawas',
  ),
),
            ],
            [
                'name' => 'UJANG AMIR HAMZAH, S.Kom',
                'email' => 'ujang@uis.ac.id',
                'jabatan' => 'HUMAS DAN PUBLIKASI WEB FIKES',
                'unit' => 'FAKULTAS ILMU KESEHATAN',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Desain, Dokumentasi dan Publikasi',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Desain Publikasi dan Dokumentasi',
  ),
),
            ],
            [
                'name' => 'Dr. Okta Veza, S.Kom., M.Kom',
                'email' => 'okta@uis.ac.id',
                'jabatan' => 'Wakil Dekan I',
                'unit' => 'PELAKSANA AKADEMIK',
                'roles' => 'pimpinan sub unit, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Wakil Ketua III',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Wakil Ketua',
  ),
),
            ],
            [
                'name' => 'Agus Suryadi, S.Kom., M.Kom',
                'email' => 'agussuryadi@uis.ac.id',
                'jabatan' => 'KABID. PERENCANAAN DAN PENGEMBANGAN',
                'unit' => '-',
                'roles' => 'pimpinan sub unit, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Sekretaris',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Sekretariat',
  ),
),
            ],
            [
                'name' => 'Dimas Pradipto, S.Kom., M.Kom',
                'email' => 'dimas@uis.ac.id',
                'jabatan' => 'PROGRAMMER',
                'unit' => 'LPTI',
                'roles' => 'admin, staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Wakil Sekretaris III',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi LPTI',
  ),
),
            ],
            [
                'name' => 'Andi Hidayatul Fadlilah, SE, M.Si, AK',
                'email' => 'andihidaya@uis.ac.id',
                'jabatan' => '-',
                'unit' => '-',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Bendahara',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Bendahara',
  ),
),
            ],
            [
                'name' => 'Dr. Ita Mustika, SE., M.Ak',
                'email' => 'ita@uis.ac.id',
                'jabatan' => 'Wakil Dekan II',
                'unit' => 'PELAKSANA AKADEMIK',
                'roles' => 'pimpinan sub unit, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Wakil Bendahara II',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Wakil Bendehara',
  ),
),
            ],
            [
                'name' => 'Muhammad Albar, S.Kom',
                'email' => 'albar@uis.ac.id',
                'jabatan' => 'STAFF AKADEMIK FAKULTAS SAINS DAN TEKNOLOGI',
                'unit' => 'UNSUR PELAKSANA ADMINISTRASI',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Kesekretariatan FST',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Sekretaris Umum',
  ),
),
            ],
            [
                'name' => 'Andi Aulia Vitrizky, Amd.Ak',
                'email' => 'vitrizky@uis.ac.id',
                'jabatan' => '-',
                'unit' => '-',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Kesekretariatan FIKes',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Validasi Pendaftaran FIKes',
  ),
),
            ],
            [
                'name' => 'Deza Zalia Permata Dewi, S.Pd., M.Pd., MM',
                'email' => 'deza.zalia@uis.ac.id',
                'jabatan' => '-',
                'unit' => '-',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Acara',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Pengawas',
  ),
),
            ],
            [
                'name' => 'Intan Zatura',
                'email' => '231026201199@uis.ac.id',
                'jabatan' => '-',
                'unit' => '-',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Acara',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Acara',
  ),
),
            ],
            [
                'name' => 'Putri Redina',
                'email' => 'putri.redina@uis.ac.id',
                'jabatan' => '-',
                'unit' => '-',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Acara',
  ),
),
            ],
            [
                'name' => 'M. Tarmizi, SE., MM',
                'email' => 'tarmizi@uis.ac.id',
                'jabatan' => '-',
                'unit' => '-',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Seksi Kelompok 6',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Pengawas',
  ),
),
            ],
            [
                'name' => 'Dr. Ir. Hanafi, S.Kom., M.Kom',
                'email' => 'hanafi@uis.ac.id',
                'jabatan' => '-',
                'unit' => '-',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Seksi Kelompok 12',
  ),
),
            ],
            [
                'name' => 'Drs. Dasman Johan, M.Pd.T',
                'email' => 'dasman.johan@uis.ac.id',
                'jabatan' => '-',
                'unit' => '-',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Seksi Kelompok 15',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Pengawas',
  ),
),
            ],
            [
                'name' => 'Andi Suhriyanti, S.Farm',
                'email' => 'andi.suhriyanti@uis.ac.id',
                'jabatan' => '-',
                'unit' => '-',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Konsumsi',
  ),
),
            ],
            [
                'name' => 'Misbatul Hasanah',
                'email' => 'misbatul.hasanah@uis.ac.id',
                'jabatan' => '-',
                'unit' => '-',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Konsumsi',
  ),
),
            ],
            [
                'name' => 'Sri Winarsih',
                'email' => 'sri.winarsih@uis.ac.id',
                'jabatan' => '-',
                'unit' => '-',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Konsumsi',
  ),
),
            ],
            [
                'name' => 'Yusro Zamilatussyifa Siregar, SE',
                'email' => 'syifa@uis.ac.id',
                'jabatan' => '-',
                'unit' => '-',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Konsumsi',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'anggota Seksi Konsumsi',
  ),
),
            ],
            [
                'name' => 'Salasiah',
                'email' => 'salasiah@uis.ac.id',
                'jabatan' => '-',
                'unit' => '-',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Konsumsi',
  ),
),
            ],
            [
                'name' => 'Rizky Nurrochmad Ismail, S.T., M.T',
                'email' => 'rizky.nurrochmad.ismail@uis.ac.id',
                'jabatan' => '-',
                'unit' => '-',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Konsumsi',
  ),
),
            ],
            [
                'name' => 'Amelia Rachmi Nasution, ST., M.T',
                'email' => 'amelia@uis.ac.id',
                'jabatan' => '-',
                'unit' => '-',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Konsumsi',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Pengawas',
  ),
),
            ],
            [
                'name' => 'Agus Setiawan',
                'email' => 'agus.setiawan@uis.ac.id',
                'jabatan' => '-',
                'unit' => '-',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Perlengkapan',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Perlengkapan',
  ),
),
            ],
            [
                'name' => 'Andi Harun Asrul',
                'email' => 'andi.harun.asrul@uis.ac.id',
                'jabatan' => '-',
                'unit' => '-',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Perlengkapan',
  ),
),
            ],
            [
                'name' => 'Edi Susanto',
                'email' => 'edi.susanto@uis.ac.id',
                'jabatan' => '-',
                'unit' => '-',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Perlengkapan',
  ),
),
            ],
            [
                'name' => 'Said',
                'email' => 'said@uis.ac.id',
                'jabatan' => '-',
                'unit' => '-',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Perlengkapan',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Perlengkapan',
  ),
),
            ],
            [
                'name' => 'Andi Heri',
                'email' => 'andi.heri@uis.ac.id',
                'jabatan' => '-',
                'unit' => '-',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Perlengkapan',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Perlengkapan',
  ),
),
            ],
            [
                'name' => 'Riki',
                'email' => 'riki@uis.ac.id',
                'jabatan' => '-',
                'unit' => '-',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Perlengkapan',
  ),
),
            ],
            [
                'name' => 'M. Ridho',
                'email' => 'm.ridho@uis.ac.id',
                'jabatan' => '-',
                'unit' => '-',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Perlengkapan',
  ),
),
            ],
            [
                'name' => 'Jimmy',
                'email' => 'jimmy@uis.ac.id',
                'jabatan' => '-',
                'unit' => '-',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Perlengkapan',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Perlengkapan',
  ),
),
            ],
            [
                'name' => 'Juliansyah',
                'email' => 'juliansyah@uis.ac.id',
                'jabatan' => '-',
                'unit' => '-',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Perlengkapan',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Perlengkapan',
  ),
),
            ],
            [
                'name' => 'Randi',
                'email' => 'randi@uis.ac.id',
                'jabatan' => '-',
                'unit' => '-',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Perlengkapan',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Perlengkapan',
  ),
),
            ],
            [
                'name' => 'Rudiansyah',
                'email' => 'rudiansyah@uis.ac.id',
                'jabatan' => '-',
                'unit' => '-',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Perlengkapan',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Perlengkapan',
  ),
),
            ],
            [
                'name' => 'Ahmad Nur',
                'email' => 'ahmad.nur@uis.ac.id',
                'jabatan' => '-',
                'unit' => '-',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Perlengkapan',
  ),
),
            ],
            [
                'name' => 'Azmi',
                'email' => 'azmi@uis.ac.id',
                'jabatan' => '-',
                'unit' => '-',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Perlengkapan',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Perlengkapan',
  ),
),
            ],
            [
                'name' => 'Rizal',
                'email' => 'rizal@uis.ac.id',
                'jabatan' => '-',
                'unit' => '-',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Perlengkapan',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Perlengkapan',
  ),
),
            ],
            [
                'name' => 'Muri',
                'email' => 'muri@uis.ac.id',
                'jabatan' => '-',
                'unit' => '-',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Perlengkapan',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Perlengkapan',
  ),
),
            ],
            [
                'name' => 'Andi Amin Amirul Mukminin, M.Kom',
                'email' => 'andi.amin@uis.ac.id',
                'jabatan' => '-',
                'unit' => '-',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Desain, Dokumentasi dan Publikasi',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi Desain Publikasi dan Dokumentasi',
  ),
),
            ],
            [
                'name' => 'Habib Armanda, S.Kom',
                'email' => 'habib@uis.ac.id',
                'jabatan' => '-',
                'unit' => '-',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi Desain, Dokumentasi dan Publikasi',
  ),
),
            ],
            [
                'name' => 'Rusdiyanto, S.Kom',
                'email' => 'rusdi@uis.ac.id',
                'jabatan' => 'STAFF LAYANAN TROUBLESHOOTING',
                'unit' => 'LPTI',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi LPTI',
  ),
  1 => 
  array (
    'nama' => 'Pendidikan Karakter',
    'jabatan' => 'Anggota Seksi LPTI',
  ),
),
            ],
            [
                'name' => 'dr. Andi Sarbiah. M.KKK',
                'email' => 'andi.sarbiah.m.kkk@uis.ac.id',
                'jabatan' => '-',
                'unit' => '-',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Koordinator Seksi P3K',
  ),
),
            ],
            [
                'name' => 'Megawati hitajulu, AM.Kep',
                'email' => 'sabitaanan0612@gmail.com',
                'jabatan' => '-',
                'unit' => '-',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi P3K',
  ),
),
            ],
            [
                'name' => 'Andi Hawa',
                'email' => 'ahawanas@77gmail.com',
                'jabatan' => '-',
                'unit' => '-',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'PKKMB',
    'jabatan' => 'Anggota Seksi P3K',
  ),
),
            ],
            [
                'name' => 'ANDI HILDA., MM',
                'email' => 'andihildarosalind@yahoo.com',
                'jabatan' => '-',
                'unit' => '-',
                'roles' => 'staff, kepanitiaan',
                'jabatan_kepanitiaan' => array (
  0 => 
  array (
    'nama' => 'MILAD',
    'jabatan' => 'TIM KESEHATAN',
  ),
),
            ],
            [
                'name' => 'MUKHIRZIN',
                'email' => 'mukhirzin@uis.ac.id',
                'jabatan' => 'CS',
                'unit' => 'FEB',
                'roles' => 'staff',
                'jabatan_kepanitiaan' => null,
            ],
            [
                'name' => 'FITRA AYU KUSUMA, S.HUM',
                'email' => 'fitra@uis.ac.id',
                'jabatan' => 'PUSTAKAWAN',
                'unit' => 'PERPUSTAKAAN',
                'roles' => 'staff',
                'jabatan_kepanitiaan' => null,
            ],
            [
                'name' => 'ANDI NURMAN TEGUH, S.E',
                'email' => 'teguh@uis.ac.id',
                'jabatan' => 'PUSTAKAWAN',
                'unit' => 'PERPUSTAKAAN',
                'roles' => 'staff',
                'jabatan_kepanitiaan' => null,
            ],
            [
                'name' => 'SITI AISYAH JAMAL, S.T., M.T',
                'email' => 'siti.aisyah.jamal@uis.ac.id',
                'jabatan' => 'SEKRETARIS PROGRAM STUDI TEKNIK LOGISTIK DAN PERKAPALAN',
                'unit' => 'PRODI TEKNIK LOGISTIK DAN PERKAPALAN',
                'roles' => 'pimpinan sub unit',
                'jabatan_kepanitiaan' => null,
            ],
            [
                'name' => 'FAJRINALDI, S.T',
                'email' => 'fajrinaldi@uis.ac.id',
                'jabatan' => 'KA. LABORATORIUM FAKULTAS SAINS DAN TEKNOLOGI',
                'unit' => 'LABORATORIUM FST',
                'roles' => 'pimpinan sub unit',
                'jabatan_kepanitiaan' => null,
            ],
        ];

        foreach ($users as $item) {
            User::updateOrCreate(
                ['email' => $item['email']],
                [
                    'name' => $item['name'],
                    'roles' => $item['roles'],
                    'nidn' => '-',
                    'jabatan' => $item['jabatan'],
                    'status' => 'Aktif',
                    'unit' => $item['unit'],
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'jabatan_kepanitiaan' => $item['jabatan_kepanitiaan'] ?? null,
                ]
            );
        }
    }
}
