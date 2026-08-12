<?php

namespace Database\Seeders;

use App\Models\Milestone;
use App\Models\PeriodeAkademik;
use App\Models\RencanaKerja;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RencanaKerjaProdiSeeder extends Seeder
{
    /**
     * Seed detailed Rencana Kerja (+ milestones) for every Kaprodi & Sekretaris Prodi.
     */
    public function run(): void
    {
        $periode = PeriodeAkademik::firstOrCreate(['nama_periode' => '2026/2027 Gasal']);

        $prodiUsers = User::where('unit', 'like', 'PRODI%')->orderBy('unit')->get();

        if ($prodiUsers->isEmpty()) {
            $this->command?->warn('Tidak ada user dengan unit "PRODI ..." ditemukan. Jalankan UserSeeder terlebih dahulu.');
            return;
        }

        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

        // index => status pattern applied to the 6 tasks of every user
        $statusPattern = ['Selesai', 'Selesai', 'Berjalan', 'Belum Dimulai', 'Selesai', 'Belum Dimulai'];
        // offset (hari) dari "hari ini" untuk estimasi_tanggal_mulai tiap index
        $startOffsetPattern = [-35, -21, -2, 5, -10, 14];

        $created = 0;

        foreach ($prodiUsers as $user) {
            $prodiLabel = trim(str_ireplace('PRODI', '', $user->unit));
            $isKetua = str_contains(strtoupper($user->jabatan), 'KETUA');

            $tasks = $isKetua ? $this->kaprodiTasks($prodiLabel) : $this->sekretarisTasks($prodiLabel);

            foreach ($tasks as $i => $task) {
                $status = $statusPattern[$i];
                $startOffset = $startOffsetPattern[$i];

                $estimasiMulai = Carbon::now()->addDays($startOffset);
                $estimasiSelesai = (clone $estimasiMulai)->addDays(4);
                $hari = $hariList[$estimasiMulai->dayOfWeekIso <= 5 ? $estimasiMulai->dayOfWeekIso - 1 : 0];

                $data = [
                    'user_id' => $user->id,
                    'periode_akademik_id' => $periode->id,
                    'uraian_tugas' => $task['uraian'],
                    'hari' => $hari,
                    'estimasi_tanggal_mulai' => $estimasiMulai->format('Y-m-d'),
                    'estimasi_tanggal_selesai' => $estimasiSelesai->format('Y-m-d'),
                    'estimasi_jam_mulai' => '08:00:00',
                    'estimasi_jam_selesai' => '16:00:00',
                    'status' => $status,
                    'rencana_tindak_lanjut' => $task['tindak_lanjut'],
                ];

                if ($status === 'Selesai') {
                    $data['tanggal_mulai'] = $estimasiMulai->format('Y-m-d');
                    $data['tanggal_selesai'] = $estimasiSelesai->format('Y-m-d');
                    $data['waktu_mulai'] = '08:00:00';
                    $data['waktu_selesai'] = '16:00:00';
                    $data['hasil_kerja'] = $task['hasil_kerja'];
                } elseif ($status === 'Berjalan') {
                    $data['tanggal_mulai'] = $estimasiMulai->format('Y-m-d');
                    $data['waktu_mulai'] = '08:00:00';
                    $data['tanggal_selesai'] = null;
                    $data['waktu_selesai'] = '00:00:00';
                    $data['hasil_kerja'] = $task['hasil_progres'] ?? $task['hasil_kerja'];
                } else {
                    $data['hasil_kerja'] = null;
                }

                $rk = RencanaKerja::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'periode_akademik_id' => $periode->id,
                        'uraian_tugas' => $task['uraian'],
                    ],
                    $data
                );

                if ($rk->wasRecentlyCreated) {
                    $created++;

                    if (!empty($task['milestones'])) {
                        $this->seedMilestones($rk, $task['milestones'], $estimasiMulai);
                    }
                }
            }
        }

        $this->command?->info("RencanaKerjaProdiSeeder: {$created} rencana kerja baru dibuat untuk " . $prodiUsers->count() . ' pejabat prodi.');
    }

    /**
     * @param array<int, array{nama: string, catatan: string, status: string}> $milestones
     */
    private function seedMilestones(RencanaKerja $rencanaKerja, array $milestones, Carbon $taskStart): void
    {
        $cursor = (clone $taskStart)->setTime(8, 0);

        foreach ($milestones as $ms) {
            $waktuMulai = null;
            $waktuSelesai = null;
            $lastStartedAt = null;
            $durasiDetik = 0;

            if ($ms['status'] === 'Selesai') {
                $waktuMulai = clone $cursor;
                $waktuSelesai = (clone $cursor)->addHours(3);
                $durasiDetik = 3 * 3600;
                $cursor = (clone $waktuSelesai)->addDay();
            } elseif ($ms['status'] === 'Berjalan') {
                $waktuMulai = Carbon::now()->subMinutes(90);
                $lastStartedAt = Carbon::now()->subMinutes(90);
                $durasiDetik = 0;
            }

            Milestone::create([
                'milestonable_id' => $rencanaKerja->id,
                'milestonable_type' => RencanaKerja::class,
                'nama_milestone' => $ms['nama'],
                'catatan' => $ms['catatan'],
                'status' => $ms['status'],
                'waktu_mulai' => $waktuMulai,
                'waktu_selesai' => $waktuSelesai,
                'last_started_at' => $lastStartedAt,
                'durasi_detik' => $durasiDetik,
            ]);
        }
    }

    private function kaprodiTasks(string $prodi): array
    {
        return [
            [
                'uraian' => "Evaluasi dan Pemutakhiran Kurikulum Program Studi {$prodi} Sesuai KKNI dan Kebutuhan Industri",
                'hasil_kerja' => "Kurikulum Program Studi {$prodi} telah dievaluasi dan diperbarui bersama tim dosen, mengacu pada KKNI level 6 dan masukan dari mitra industri/asosiasi profesi.",
                'tindak_lanjut' => 'Sosialisasikan kurikulum baru ke seluruh dosen dan mahasiswa pada rapat awal semester berikutnya.',
                'milestones' => [
                    ['nama' => 'Pengumpulan Masukan Stakeholder (Industri & Alumni)', 'catatan' => 'Menghimpun masukan melalui kuesioner dan FGD dengan alumni serta mitra industri.', 'status' => 'Selesai'],
                    ['nama' => 'Penyusunan Draft Kurikulum Baru', 'catatan' => 'Draft disusun bersama tim kurikulum program studi.', 'status' => 'Selesai'],
                    ['nama' => 'Finalisasi dan Pengesahan Kurikulum di Rapat Senat', 'catatan' => 'Kurikulum disahkan dalam rapat senat fakultas.', 'status' => 'Selesai'],
                ],
            ],
            [
                'uraian' => "Persiapan Dokumen Akreditasi Program Studi {$prodi} untuk Re-Akreditasi",
                'hasil_kerja' => 'Seluruh dokumen borang dan bukti dukung telah disusun dan siap diunggah ke sistem lembaga akreditasi.',
                'tindak_lanjut' => 'Koordinasi dengan LPM kampus untuk jadwal visitasi asesor.',
            ],
            [
                'uraian' => 'Rapat Koordinasi Bulanan dengan Dosen Program Studi',
                'hasil_progres' => 'Undangan dan materi rapat telah disiapkan, rapat koordinasi bulan ini sedang berlangsung membahas evaluasi tengah semester.',
                'hasil_kerja' => 'Rapat koordinasi bulanan terlaksana, membahas evaluasi perkuliahan dan kendala akademik mahasiswa.',
                'tindak_lanjut' => 'Menindaklanjuti hasil rapat dengan menyusun notulen dan mendistribusikan tugas ke masing-masing dosen.',
                'milestones' => [
                    ['nama' => 'Persiapan Materi dan Undangan Rapat', 'catatan' => 'Undangan dikirim ke seluruh dosen tetap program studi.', 'status' => 'Selesai'],
                    ['nama' => 'Pelaksanaan Rapat Koordinasi', 'catatan' => 'Rapat membahas evaluasi tengah semester dan kendala perkuliahan.', 'status' => 'Berjalan'],
                    ['nama' => 'Penyusunan Notulen dan Tindak Lanjut Rapat', 'catatan' => 'Menunggu rapat selesai untuk penyusunan notulen.', 'status' => 'Belum Dimulai'],
                ],
            ],
            [
                'uraian' => 'Monitoring dan Evaluasi Pelaksanaan Perkuliahan Semester Gasal 2026/2027',
                'hasil_kerja' => null,
                'tindak_lanjut' => 'Menyiapkan instrumen monitoring kehadiran dosen dan kesesuaian materi dengan RPS.',
            ],
            [
                'uraian' => "Pembinaan dan Bimbingan Mahasiswa Tugas Akhir/Skripsi Program Studi {$prodi}",
                'hasil_kerja' => 'Telah dilakukan pemetaan dosen pembimbing dan penjadwalan seminar proposal bagi mahasiswa tingkat akhir.',
                'tindak_lanjut' => 'Memantau progres bimbingan setiap dua minggu dan menjadwalkan sidang akhir semester.',
            ],
            [
                'uraian' => "Penyusunan Laporan Kinerja Program Studi {$prodi} untuk Rektorat",
                'hasil_kerja' => null,
                'tindak_lanjut' => 'Mengumpulkan data kinerja dosen, mahasiswa, dan sarana prasarana dari seluruh unit terkait.',
            ],
        ];
    }

    private function sekretarisTasks(string $prodi): array
    {
        return [
            [
                'uraian' => "Pengelolaan dan Pemutakhiran Data PDDIKTI Program Studi {$prodi}",
                'hasil_kerja' => 'Data mahasiswa aktif, nilai, dan dosen pengampu telah diverifikasi dan disinkronkan ke PDDIKTI.',
                'tindak_lanjut' => 'Melakukan pengecekan berkala setiap akhir bulan untuk memastikan data tetap sinkron.',
                'milestones' => [
                    ['nama' => 'Verifikasi Data Mahasiswa Aktif', 'catatan' => 'Verifikasi status aktif/cuti mahasiswa semester berjalan.', 'status' => 'Selesai'],
                    ['nama' => 'Sinkronisasi Data Nilai ke PDDIKTI', 'catatan' => 'Nilai semester sebelumnya berhasil diunggah tanpa kendala.', 'status' => 'Selesai'],
                    ['nama' => 'Pengecekan dan Validasi Akhir Laporan PDDIKTI', 'catatan' => 'Laporan divalidasi bersama Kaprodi sebelum dikunci.', 'status' => 'Selesai'],
                ],
            ],
            [
                'uraian' => 'Penyusunan dan Pengarsipan RPS (Rencana Pembelajaran Semester) Seluruh Mata Kuliah',
                'hasil_kerja' => 'RPS seluruh mata kuliah semester gasal telah terkumpul dan diarsipkan dalam format digital.',
                'tindak_lanjut' => 'Mengingatkan dosen yang belum mengumpulkan RPS sebelum minggu pertama perkuliahan.',
            ],
            [
                'uraian' => 'Koordinasi Jadwal Perkuliahan dan Ruang Kelas Semester Gasal 2026/2027',
                'hasil_progres' => 'Kesediaan mengajar dosen sudah terkumpul, draft jadwal sedang disusun untuk menghindari bentrok ruang/waktu.',
                'hasil_kerja' => 'Jadwal perkuliahan dan alokasi ruang kelas semester gasal telah selesai disusun dan disebarkan.',
                'tindak_lanjut' => 'Memantau pelaksanaan minggu pertama perkuliahan untuk memastikan tidak ada bentrok jadwal.',
                'milestones' => [
                    ['nama' => 'Pengumpulan Kesediaan Mengajar Dosen', 'catatan' => 'Seluruh dosen tetap telah mengisi form kesediaan mengajar.', 'status' => 'Selesai'],
                    ['nama' => 'Penyusunan Draft Jadwal Perkuliahan', 'catatan' => 'Menyusun jadwal dengan mempertimbangkan ketersediaan ruang dan bentrok dosen.', 'status' => 'Berjalan'],
                    ['nama' => 'Sosialisasi Jadwal ke Mahasiswa dan Dosen', 'catatan' => 'Akan diumumkan melalui portal akademik dan grup resmi.', 'status' => 'Belum Dimulai'],
                ],
            ],
            [
                'uraian' => "Pendataan dan Tracer Study Alumni Program Studi {$prodi}",
                'hasil_kerja' => null,
                'tindak_lanjut' => 'Menyebarkan kuesioner tracer study melalui email dan media sosial alumni.',
            ],
            [
                'uraian' => "Administrasi Surat Menyurat dan Kearsipan Program Studi {$prodi}",
                'hasil_kerja' => 'Seluruh surat masuk/keluar bulan ini telah dicatat dalam buku agenda dan diarsipkan sesuai kategori.',
                'tindak_lanjut' => 'Melanjutkan pengarsipan digital untuk mempermudah pencarian surat di masa mendatang.',
            ],
            [
                'uraian' => "Persiapan Berkas Pendukung Borang Akreditasi Program Studi {$prodi}",
                'hasil_kerja' => null,
                'tindak_lanjut' => 'Berkoordinasi dengan Kaprodi untuk menentukan prioritas dokumen yang perlu dilengkapi lebih dulu.',
            ],
        ];
    }
}
