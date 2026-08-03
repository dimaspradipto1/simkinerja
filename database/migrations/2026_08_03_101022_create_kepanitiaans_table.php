<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kepanitiaans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('periode_akademik_id')->constrained()->cascadeOnDelete();
            $table->text('uraian_tugas');
            $table->string('hari')->nullable();
            $table->time('estimasi_jam_mulai')->nullable();
            $table->time('estimasi_jam_selesai')->nullable();
            $table->date('estimasi_tanggal_mulai')->nullable();
            $table->date('estimasi_tanggal_selesai')->nullable();
            $table->time('waktu_mulai')->nullable();
            $table->time('waktu_selesai')->nullable();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->string('file')->nullable();
            $table->text('url_external')->nullable();
            $table->string('status')->default('Belum Dimulai');
            $table->timestamps();
        });

        Schema::create('kepanitiaan_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kepanitiaan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kepanitiaan_user');
        Schema::dropIfExists('kepanitiaans');
    }
};
