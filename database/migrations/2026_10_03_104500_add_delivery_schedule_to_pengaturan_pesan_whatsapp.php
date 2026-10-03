<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengaturan_pesan_whatsapp', function (Blueprint $table) {
            $table->string('mode_pengiriman', 20)->default('terjadwal')->after('isi_pesan');
            $table->time('waktu_kirim')->nullable()->after('mode_pengiriman');
        });

        $categories = [
            'Rencana Kerja',
            'Kepanitiaan',
            'Insidentil',
        ];
        $statuses = [
            'Belum Dimulai' => [
                'mode' => 'terjadwal',
                'message' => "Yth. {nama_penerima},\n\nMengingatkan rencana {kategori} Anda belum dimulai:\n{uraian_tugas}\n\nEstimasi selesai: {estimasi_tanggal_selesai} {estimasi_jam_selesai}\n\nMohon mulai mengerjakan sesuai rencana. Pesan ini dikirim oleh {nama_pemimpin}.",
            ],
            'Proses/Berjalan' => [
                'mode' => 'langsung',
                'message' => "Yth. {nama_penerima},\n\nRencana {kategori} berikut sedang dalam proses:\n{uraian_tugas}\n\nEstimasi selesai: {estimasi_tanggal_selesai} {estimasi_jam_selesai}\n\nPesan ini dikirim oleh {nama_pemimpin}.",
            ],
            'Gagal' => [
                'mode' => 'terjadwal',
                'message' => "Yth. {nama_penerima},\n\nRencana {kategori} berikut telah melewati estimasi waktu penyelesaian dan berstatus gagal:\n{uraian_tugas}\n\nEstimasi selesai: {estimasi_tanggal_selesai} {estimasi_jam_selesai}\n\nMohon segera memperbarui progres atau mengajukan perpanjangan waktu. Pesan ini dikirim oleh {nama_pemimpin}.",
            ],
        ];

        $now = now();
        $templates = [];
        foreach ($categories as $category) {
            foreach ($statuses as $status => $settings) {
                $templates[] = [
                    'kategori' => $category,
                    'status' => $status,
                    'isi_pesan' => str_replace(
                        '{kategori}',
                        match ($category) {
                            'Rencana Kerja' => 'kerja',
                            'Kepanitiaan' => 'kepanitiaan',
                            default => 'insidentil',
                        },
                        $settings['message']
                    ),
                    'mode_pengiriman' => $settings['mode'],
                    'waktu_kirim' => $settings['mode'] === 'terjadwal' ? '08:00:00' : null,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('pengaturan_pesan_whatsapp')
            ->where('status', 'Proses/Berjalan')
            ->update(['mode_pengiriman' => 'langsung', 'waktu_kirim' => null]);
        DB::table('pengaturan_pesan_whatsapp')
            ->whereIn('status', ['Belum Dimulai', 'Gagal'])
            ->whereNull('waktu_kirim')
            ->update(['mode_pengiriman' => 'terjadwal', 'waktu_kirim' => '08:00:00']);

        DB::table('pengaturan_pesan_whatsapp')->insertOrIgnore($templates);
    }

    public function down(): void
    {
        Schema::table('pengaturan_pesan_whatsapp', function (Blueprint $table) {
            $table->dropColumn(['mode_pengiriman', 'waktu_kirim']);
        });
    }
};
