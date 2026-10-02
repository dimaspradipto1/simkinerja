<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->whereIn('id', DB::table('otk_members')->select('user_id'))
            ->where('unit', 'FAKULTAS TEKNIK')
            ->update(['unit' => 'FAKULTAS SAINS DAN TEKNOLOGI']);
    }

    public function down(): void
    {
        DB::table('users')
            ->whereIn('id', DB::table('otk_members')->select('user_id'))
            ->where('unit', 'FAKULTAS SAINS DAN TEKNOLOGI')
            ->update(['unit' => 'FAKULTAS TEKNIK']);
    }
};
