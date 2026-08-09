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
        if (Schema::hasTable('rencana_kerjas')) {
            Schema::table('rencana_kerjas', function (Blueprint $table) {
                if (!Schema::hasColumn('rencana_kerjas', 'saran_pimpinan')) {
                    $table->text('saran_pimpinan')->nullable()->after('status');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('rencana_kerjas')) {
            Schema::table('rencana_kerjas', function (Blueprint $table) {
                if (Schema::hasColumn('rencana_kerjas', 'saran_pimpinan')) {
                    $table->dropColumn('saran_pimpinan');
                }
            });
        }
    }
};
