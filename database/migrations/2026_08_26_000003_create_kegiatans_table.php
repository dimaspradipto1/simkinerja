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
        if (!Schema::hasTable('kegiatans')) {
            Schema::create('kegiatans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('periode_akademik_id')->constrained('periode_akademiks')->cascadeOnDelete();
                $table->string('kategori')->nullable(); // Rencana Kerja, Kepanitiaan, Insidentil
                $table->string('source_type')->nullable();
                $table->unsignedBigInteger('source_id')->nullable();
                $table->text('uraian_tugas');
                $table->string('hari')->nullable();
                $table->time('estimasi_jam_mulai')->nullable();
                $table->time('estimasi_jam_selesai')->nullable();
                $table->date('estimasi_tanggal_mulai')->nullable();
                $table->date('estimasi_tanggal_selesai')->nullable();
                $table->unsignedInteger('estimasi_durasi_hari')->nullable();
                $table->unsignedInteger('estimasi_durasi_jam')->nullable();
                $table->date('estimasi_tanggal_selesai_awal')->nullable();
                $table->time('estimasi_jam_selesai_awal')->nullable();
                $table->time('waktu_mulai')->nullable();
                $table->time('waktu_selesai')->nullable();
                $table->date('tanggal_mulai')->nullable();
                $table->date('tanggal_selesai')->nullable();
                $table->string('file')->nullable();
                $table->text('url_external')->nullable();
                $table->text('hasil_kerja')->nullable();
                $table->text('rencana_tindak_lanjut')->nullable();
                $table->text('saran_pimpinan')->nullable();
                $table->dateTime('last_started_at')->nullable();
                $table->unsignedInteger('durasi_detik')->default(0);
                $table->string('status')->default('Belum Dimulai');
                $table->boolean('is_paused')->default(false);
                $table->timestamp('paused_at')->nullable();
                $table->unsignedInteger('total_paused_seconds')->default(0);
                $table->text('alasan_pause')->nullable();
                $table->text('estimasi_unlock_reason')->nullable();
                $table->timestamp('estimasi_unlock_requested_at')->nullable();
                $table->timestamp('estimasi_unlock_approved_at')->nullable();
                $table->text('reopen_reason')->nullable();
                $table->timestamp('reopen_requested_at')->nullable();
                $table->timestamp('reopened_at')->nullable();
                $table->foreignId('reopened_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('kegiatan_user')) {
            Schema::create('kegiatan_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('kegiatan_id')->constrained('kegiatans')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kegiatan_user');
        Schema::dropIfExists('kegiatans');
    }
};
