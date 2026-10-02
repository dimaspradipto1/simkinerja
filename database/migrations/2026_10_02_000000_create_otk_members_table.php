<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otk_members', function (Blueprint $table) {
            $table->foreignId('user_id')->primary()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('otk_sync_state', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->timestamp('synced_at')->nullable();
        });
        DB::table('otk_sync_state')->insert(['id' => 1, 'synced_at' => null]);
    }

    public function down(): void
    {
        Schema::dropIfExists('otk_sync_state');
        Schema::dropIfExists('otk_members');
    }
};
