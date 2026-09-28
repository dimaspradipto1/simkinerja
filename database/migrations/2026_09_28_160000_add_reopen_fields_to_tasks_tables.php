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
        $tables = ['rencana_kerjas', 'kepanitiaans', 'insidentils', 'kegiatans'];

        foreach ($tables as $t) {
            if (Schema::hasTable($t)) {
                Schema::table($t, function (Blueprint $table) use ($t) {
                    if (!Schema::hasColumn($t, 'reopen_reason')) {
                        $table->text('reopen_reason')->nullable()->after('alasan_pause');
                    }
                    if (!Schema::hasColumn($t, 'reopen_requested_at')) {
                        $table->timestamp('reopen_requested_at')->nullable()->after('reopen_reason');
                    }
                    if (!Schema::hasColumn($t, 'reopened_at')) {
                        $table->timestamp('reopened_at')->nullable()->after('reopen_requested_at');
                    }
                    if (!Schema::hasColumn($t, 'reopened_by')) {
                        $table->unsignedBigInteger('reopened_by')->nullable()->after('reopened_at');
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['rencana_kerjas', 'kepanitiaans', 'insidentils', 'kegiatans'];

        foreach ($tables as $t) {
            if (Schema::hasTable($t)) {
                Schema::table($t, function (Blueprint $table) use ($t) {
                    $cols = [];
                    foreach (['reopen_reason', 'reopen_requested_at', 'reopened_at', 'reopened_by'] as $col) {
                        if (Schema::hasColumn($t, $col)) {
                            $cols[] = $col;
                        }
                    }
                    if (!empty($cols)) {
                        $table->dropColumn($cols);
                    }
                });
            }
        }
    }
};
