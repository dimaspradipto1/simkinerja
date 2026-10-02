<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('otk_sync_state', function (Blueprint $table) {
            $table->string('title_line_1')->default('NAMA-NAMA PENEMPATAN ORGANISASI DAN TATA KERJA (OTK)');
            $table->string('title_line_2')->default('UNIVERSITAS IBNU SINA TAHUN 2024 - 2029');
        });
    }

    public function down(): void
    {
        Schema::table('otk_sync_state', function (Blueprint $table) {
            $table->dropColumn(['title_line_1', 'title_line_2']);
        });
    }
};
