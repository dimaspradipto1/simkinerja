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
        Schema::create('milestones', function (Blueprint $table) {
            $table->id();
            $table->morphs('milestonable'); // milestonable_type & milestonable_id
            $table->string('nama_milestone');
            $table->text('catatan')->nullable();
            $table->enum('status', ['Belum Dimulai', 'Berjalan', 'Di-pause', 'Selesai'])->default('Belum Dimulai');
            $table->dateTime('waktu_mulai')->nullable();
            $table->dateTime('waktu_selesai')->nullable();
            $table->dateTime('last_started_at')->nullable();
            $table->unsignedInteger('durasi_detik')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('milestones');
    }
};
