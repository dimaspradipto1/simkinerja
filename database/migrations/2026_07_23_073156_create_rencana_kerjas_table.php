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
        Schema::create('rencana_kerjas', function (Blueprint $table) {
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rencana_kerjas');
    }
};
