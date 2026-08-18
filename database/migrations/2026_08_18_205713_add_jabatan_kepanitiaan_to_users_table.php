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
            if (Schema::hasColumn('users', 'jabatan_kepanitiaan')) {
                $table->dropColumn('jabatan_kepanitiaan');
            }

            if (!Schema::hasColumn('users', 'jabatan_pkkmb')) {
                $table->string('jabatan_pkkmb')->nullable()->after('jabatan');
            }
            if (!Schema::hasColumn('users', 'jabatan_esq')) {
                $table->string('jabatan_esq')->nullable()->after('jabatan_pkkmb');
            }
            if (!Schema::hasColumn('users', 'jabatan_milad')) {
                $table->string('jabatan_milad')->nullable()->after('jabatan_esq');
            }
            if (!Schema::hasColumn('users', 'jabatan_kuliah_umum')) {
                $table->string('jabatan_kuliah_umum')->nullable()->after('jabatan_milad');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $cols = [];
            foreach (['jabatan_pkkmb', 'jabatan_esq', 'jabatan_milad', 'jabatan_kuliah_umum'] as $c) {
                if (Schema::hasColumn('users', $c)) {
                    $cols[] = $c;
                }
            }
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
