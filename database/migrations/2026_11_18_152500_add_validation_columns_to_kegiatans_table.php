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
        Schema::table('kegiatans', function (Blueprint $table) {
            if (!Schema::hasColumn('kegiatans', 'validated_at')) {
                $table->timestamp('validated_at')->nullable()->after('saran_pimpinan');
            }
            if (!Schema::hasColumn('kegiatans', 'validated_by')) {
                $table->unsignedBigInteger('validated_by')->nullable()->after('validated_at');
            }
            if (!Schema::hasColumn('kegiatans', 'catatan_validasi')) {
                $table->text('catatan_validasi')->nullable()->after('validated_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kegiatans', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('kegiatans', 'validated_at')) $columns[] = 'validated_at';
            if (Schema::hasColumn('kegiatans', 'validated_by')) $columns[] = 'validated_by';
            if (Schema::hasColumn('kegiatans', 'catatan_validasi')) $columns[] = 'catatan_validasi';
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
