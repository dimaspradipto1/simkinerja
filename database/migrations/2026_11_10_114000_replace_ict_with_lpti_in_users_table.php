<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            DB::statement("UPDATE users SET jabatan = REPLACE(jabatan, 'ICT', 'LPTI') WHERE jabatan LIKE '%ICT%'");
            DB::statement("UPDATE users SET jabatan = REPLACE(jabatan, 'ict', 'LPTI') WHERE jabatan LIKE '%ict%'");
            DB::statement("UPDATE users SET unit = REPLACE(unit, 'ICT', 'LPTI') WHERE unit LIKE '%ICT%'");
            DB::statement("UPDATE users SET unit = REPLACE(unit, 'ict', 'LPTI') WHERE unit LIKE '%ict%'");
        }

        if (Schema::hasTable('roles')) {
            DB::statement("UPDATE roles SET name = REPLACE(name, 'ICT', 'LPTI') WHERE name LIKE '%ICT%'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse needed
    }
};
