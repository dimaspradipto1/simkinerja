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
                if (!Schema::hasColumn('rencana_kerjas', 'hasil_kerja')) {
                    $table->longText('hasil_kerja')->nullable()->after('url_external');
                }
                if (!Schema::hasColumn('rencana_kerjas', 'rencana_tindak_lanjut')) {
                    $table->longText('rencana_tindak_lanjut')->nullable()->after('hasil_kerja');
                }
            });
        }

        if (Schema::hasTable('kepanitiaans')) {
            Schema::table('kepanitiaans', function (Blueprint $table) {
                if (!Schema::hasColumn('kepanitiaans', 'hasil_kerja')) {
                    $table->longText('hasil_kerja')->nullable()->after('url_external');
                }
                if (!Schema::hasColumn('kepanitiaans', 'rencana_tindak_lanjut')) {
                    $table->longText('rencana_tindak_lanjut')->nullable()->after('hasil_kerja');
                }
            });
        }

        if (Schema::hasTable('insidentils')) {
            Schema::table('insidentils', function (Blueprint $table) {
                if (!Schema::hasColumn('insidentils', 'hasil_kerja')) {
                    $table->longText('hasil_kerja')->nullable()->after('url_external');
                }
                if (!Schema::hasColumn('insidentils', 'rencana_tindak_lanjut')) {
                    $table->longText('rencana_tindak_lanjut')->nullable()->after('hasil_kerja');
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
                $table->dropColumn(['hasil_kerja', 'rencana_tindak_lanjut']);
            });
        }
        if (Schema::hasTable('kepanitiaans')) {
            Schema::table('kepanitiaans', function (Blueprint $table) {
                $table->dropColumn(['hasil_kerja', 'rencana_tindak_lanjut']);
            });
        }
        if (Schema::hasTable('insidentils')) {
            Schema::table('insidentils', function (Blueprint $table) {
                $table->dropColumn(['hasil_kerja', 'rencana_tindak_lanjut']);
            });
        }
    }
};
