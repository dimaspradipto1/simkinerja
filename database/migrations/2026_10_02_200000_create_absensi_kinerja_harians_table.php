<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Konsep: 1 absensi kinerja per user per hari kerja.
     * Keterangan berisi daftar pekerjaan yang dikerjakan hari itu (JSON array).
     * Standar otomatis: 8 jam kerja ditambah 1 jam istirahat.
     */
    public function up(): void
    {
        Schema::create('absensi_kinerja_harians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('tanggal');                         // Tanggal hari kerja
            $table->string('status_datang')->nullable();     // Hadir, Izin, Sakit, Alpha, Terlambat
            $table->time('waktu_datang')->nullable();        // Jam pertama kali mulai kerja
            $table->string('status_pulang')->nullable();     // Hadir, Belum Pulang
            $table->time('waktu_pulang')->nullable();        // Jam terakhir selesai kerja
            $table->integer('jam_kerja_standar')->default(8);    // Standar jam kerja per hari (default 8)
            $table->integer('jam_istirahat')->default(1);        // Jam istirahat (default 1)
            $table->json('keterangan_kerja')->nullable();    // JSON array: [{id, jenis, uraian, mulai, selesai}]
            $table->text('catatan')->nullable();             // Catatan bebas (misal: izin, keterangan lain)
            $table->string('bukti_izin')->nullable();        // Path file bukti izin
            $table->timestamps();

            // Satu user hanya punya 1 absensi per hari
            $table->unique(['user_id', 'tanggal'], 'user_tanggal_uq');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi_kinerja_harians');
    }
};
