<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kepanitiaan_sesi_absensis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kepanitiaan_id')->constrained('kepanitiaans')->cascadeOnDelete();
            $table->string('nama_sesi'); // Contoh: "Hari Pertama", "Gladi Bersih", "Pelaksanaan"
            $table->date('tanggal')->nullable();
            $table->time('waktu_mulai_datang')->nullable();
            $table->time('waktu_selesai_datang')->nullable();
            $table->time('waktu_mulai_pulang')->nullable();
            $table->time('waktu_selesai_pulang')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('token', 64)->unique(); // Token unik untuk QR code & scanner URL
            $table->string('legacy_slug')->nullable()->index(); // Untuk pemetaan data lama jika ada
            $table->timestamps();
        });

        Schema::create('kepanitiaan_presensis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kepanitiaan_sesi_absensi_id')->constrained('kepanitiaan_sesi_absensis')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('hadir_datang')->nullable(); // Hadir, Izin, Sakit, Alpha
            $table->time('waktu_datang')->nullable();
            $table->text('catatan_hadir_datang')->nullable();
            $table->string('hadir_pulang')->nullable(); // Hadir, Izin, Sakit, Alpha
            $table->time('waktu_pulang')->nullable();
            $table->text('catatan_hadir_pulang')->nullable();
            $table->string('bukti_izin')->nullable();
            $table->timestamps();

            $table->unique(['kepanitiaan_sesi_absensi_id', 'user_id'], 'sesi_user_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kepanitiaan_presensis');
        Schema::dropIfExists('kepanitiaan_sesi_absensis');
    }
};
