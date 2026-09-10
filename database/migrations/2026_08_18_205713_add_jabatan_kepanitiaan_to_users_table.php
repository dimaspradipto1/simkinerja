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
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'kepanitiaan')) {
                $table->dropColumn('kepanitiaan');
            }
            if (!Schema::hasColumn('users', 'jabatan_kepanitiaan')) {
                $table->json('jabatan_kepanitiaan')->nullable()->after('jabatan');
            }

            $legacyCols = ['jabatan_pkkmb', 'jabatan_esq', 'jabatan_milad', 'jabatan_kuliah_umum'];
            foreach ($legacyCols as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'jabatan_kepanitiaan')) {
                $table->dropColumn('jabatan_kepanitiaan');
            }
        });
    }
};
