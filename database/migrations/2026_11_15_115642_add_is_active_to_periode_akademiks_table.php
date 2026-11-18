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
        if (Schema::hasTable('periode_akademiks') && !Schema::hasColumn('periode_akademiks', 'is_active')) {
            Schema::table('periode_akademiks', function (Blueprint $table) {
                $table->boolean('is_active')->default(false)->after('nama_periode');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('periode_akademiks') && Schema::hasColumn('periode_akademiks', 'is_active')) {
            Schema::table('periode_akademiks', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }
    }
};
