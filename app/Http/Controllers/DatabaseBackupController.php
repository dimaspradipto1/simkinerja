<?php

namespace App\Http\Controllers;

use App\Services\DatabaseBackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;
use RuntimeException;
use Throwable;

class DatabaseBackupController extends Controller
{
    public function __construct(
        protected DatabaseBackupService $backupService
    ) {}

    public function index()
    {
        $this->authorizeAdmin();

        return view('pages.database-backup.index', [
            'databaseDriver' => config('database.default'),
            'databaseName' => config('database.connections.' . config('database.default') . '.database'),
        ]);
    }

    public function export()
    {
        $this->authorizeAdmin();

        $connection = DB::connection();
        if ($connection->getDriverName() !== 'mysql') {
            abort(422, 'Ekspor backup SQL hanya tersedia untuk koneksi MySQL.');
        }

        $pdo = $connection->getPdo();
        $temporaryFile = tempnam(sys_get_temp_dir(), 'simkinerja-backup-');
        if ($temporaryFile === false) {
            throw new RuntimeException('Tidak dapat membuat berkas sementara untuk backup.');
        }

        try {
            $stream = fopen($temporaryFile, 'wb');
            if ($stream === false) {
                throw new RuntimeException('Tidak dapat membuka berkas sementara untuk backup.');
            }

            try {
                $this->backupService->exportDatabase($stream, $pdo);
            } finally {
                fclose($stream);
            }
        } catch (Throwable $exception) {
            if (is_file($temporaryFile)) {
                unlink($temporaryFile);
            }

            throw $exception;
        }

        $databaseName = (string) config('database.connections.' . config('database.default') . '.database');
        $safeDatabaseName = preg_replace('/[^A-Za-z0-9_-]/', '_', $databaseName) ?: 'database';
        $filename = $safeDatabaseName . '_backup_' . now()->format('Ymd_His') . '.sql';

        return response()->download($temporaryFile, $filename, [
            'Content-Type' => 'application/sql; charset=UTF-8',
        ])->deleteFileAfterSend(true);
    }

    public function import(Request $request)
    {
        $this->authorizeAdmin();

        $request->validate([
            'file_sql' => [
                'required',
                'file',
                function ($attribute, $value, $fail) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    if ($ext !== 'sql' && $ext !== 'txt') {
                        $fail('Berkas yang diunggah harus memiliki format SQL (.sql).');
                    }
                },
            ],
            'wipe_before_import' => 'nullable',
        ]);

        $wipeExisting = $request->has('wipe_before_import');

        try {
            $file = $request->file('file_sql');
            $result = $this->backupService->importDatabase($file->getRealPath(), $wipeExisting);

            $msg = "Database berhasil dipulihkan ({$result['statements']} perintah SQL dieksekusi).";
            if ($wipeExisting && $result['dropped_tables'] > 0) {
                $msg .= " Tabel lama ({$result['dropped_tables']} tabel) telah dibersihkan dan disesuaikan dengan data baru.";
            }

            Alert::success('Berhasil Dipulihkan', $msg);
            return redirect()->route('database-backup.index');
        } catch (Throwable $e) {
            Alert::error('Gagal Impor Database', 'Terjadi kesalahan saat memulihkan database: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function exportPublicStorage()
    {
        $this->authorizeAdmin();

        $temporaryFile = tempnam(sys_get_temp_dir(), 'simkinerja-public-backup-');
        if ($temporaryFile === false) {
            throw new RuntimeException('Tidak dapat membuat berkas sementara untuk backup storage.');
        }

        try {
            $this->backupService->exportPublicStorage($temporaryFile);
        } catch (Throwable $exception) {
            if (is_file($temporaryFile)) {
                unlink($temporaryFile);
            }

            throw $exception;
        }

        return response()->download(
            $temporaryFile,
            'storage_public_backup_' . now()->format('Ymd_His') . '.zip',
            ['Content-Type' => 'application/zip']
        )->deleteFileAfterSend(true);
    }

    public function importPublicStorage(Request $request)
    {
        $this->authorizeAdmin();

        $request->validate([
            'file_zip' => [
                'required',
                'file',
                function ($attribute, $value, $fail) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    if ($ext !== 'zip') {
                        $fail('Berkas yang diunggah harus berupa arsip ZIP (.zip).');
                    }
                },
            ],
        ]);

        try {
            $file = $request->file('file_zip');
            $result = $this->backupService->importPublicStorage($file->getRealPath());

            Alert::success(
                'Berhasil Dipulihkan',
                "Storage public berhasil diperbarui ({$result['files_count']} berkas/folder diekstrak). Berkas terbaru telah aktif pada aplikasi."
            );
            return redirect()->route('database-backup.index');
        } catch (Throwable $e) {
            Alert::error('Gagal Impor Storage', 'Terjadi kesalahan saat mengekstrak storage: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    private function authorizeAdmin(): void
    {
        $user = Auth::user();
        abort_unless($user && ($user->isAdmin() || $user->isSuperAdmin()), 403);
    }
}
