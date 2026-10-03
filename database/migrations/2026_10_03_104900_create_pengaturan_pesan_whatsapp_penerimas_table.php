<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaturan_pesan_whatsapp_penerimas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pengaturan_pesan_whatsapp_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->unique(['pengaturan_pesan_whatsapp_id', 'user_id'], 'whatsapp_penerima_unique');
            $table->index('user_id');
            $table->foreign('pengaturan_pesan_whatsapp_id', 'wa_penerima_template_fk')
                ->references('id')->on('pengaturan_pesan_whatsapp')->cascadeOnDelete();
            $table->foreign('user_id', 'wa_penerima_user_fk')
                ->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan_pesan_whatsapp_penerimas');
    }
};
