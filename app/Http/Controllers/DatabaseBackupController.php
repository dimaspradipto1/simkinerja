<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use FilesystemIterator;
use PDO;
use RuntimeException;
use Throwable;
use ZipArchive;

class DatabaseBackupController extends Controller
{
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
                $this->writeSqlDump($stream, $pdo);
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

    public function exportPublicStorage()
    {
        $this->authorizeAdmin();

        if (!class_exists(ZipArchive::class)) {
            abort(500, 'Ekstensi PHP ZIP belum aktif. Aktifkan ekstensi zip untuk membuat backup storage public.');
        }

        $publicRoot = config('filesystems.disks.public.root');
        if (!$publicRoot || !is_dir($publicRoot) || !is_readable($publicRoot)) {
            abort(500, 'Folder storage public tidak ditemukan atau tidak dapat dibaca.');
        }

        $temporaryFile = tempnam(sys_get_temp_dir(), 'simkinerja-public-backup-');
        if ($temporaryFile === false) {
            throw new RuntimeException('Tidak dapat membuat berkas sementara untuk backup storage.');
        }

        $archive = new ZipArchive();
        try {
            if ($archive->open($temporaryFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException('Tidak dapat membuat arsip backup storage public.');
            }

            $rootPath = rtrim(realpath($publicRoot) ?: $publicRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($rootPath, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($iterator as $file) {
                if (!$file->isFile() || $file->isLink()) {
                    continue;
                }

                $filePath = $file->getPathname();
                $relativePath = substr($filePath, strlen($rootPath));
                if ($relativePath === false || $relativePath === '') {
                    continue;
                }

                $archivePath = str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);
                if (!$archive->addFile($filePath, $archivePath)) {
                    throw new RuntimeException('Gagal menambahkan berkas ke arsip backup storage public.');
                }
            }

            if (!$archive->close()) {
                throw new RuntimeException('Gagal menyelesaikan arsip backup storage public.');
            }
        } catch (Throwable $exception) {
            $archive->close();
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

    private function authorizeAdmin(): void
    {
        $user = Auth::user();
        abort_unless($user && ($user->isAdmin() || $user->isSuperAdmin()), 403);
    }

    /**
     * Write a complete MySQL table-definition and table-data dump.
     *
     * @param resource $stream
     */
    private function writeSqlDump($stream, PDO $pdo): void
    {
        $this->write($stream, "-- SIM KINERJA MySQL backup\n");
        $this->write($stream, '-- Created: ' . now()->toDateTimeString() . "\n\n");
        $this->write($stream, "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n");

        $tables = $pdo->query('SHOW FULL TABLES')
            ->fetchAll(PDO::FETCH_NUM);
        foreach ($tables as $tableRow) {
            if (($tableRow[1] ?? '') !== 'BASE TABLE') {
                continue;
            }

            $table = (string) $tableRow[0];
            $quotedTable = $this->quoteIdentifier($table);
            $createResult = $pdo->query('SHOW CREATE TABLE ' . $quotedTable)->fetch(PDO::FETCH_ASSOC);
            if (!$createResult) {
                throw new RuntimeException("Tidak dapat membaca struktur tabel {$table}.");
            }

            $createStatement = $createResult['Create Table'] ?? array_values($createResult)[1] ?? null;
            if (!is_string($createStatement)) {
                throw new RuntimeException("Struktur tabel {$table} tidak tersedia.");
            }

            $this->write($stream, "-- Table: {$table}\n");
            $this->write($stream, 'DROP TABLE IF EXISTS ' . $quotedTable . ";\n");
            $this->write($stream, $createStatement . ";\n\n");

            $columns = $pdo->query('SHOW COLUMNS FROM ' . $quotedTable)->fetchAll(PDO::FETCH_ASSOC);
            $binaryColumns = [];
            foreach ($columns as $column) {
                $type = strtolower((string) ($column['Type'] ?? ''));
                if (preg_match('/^(tinyblob|blob|mediumblob|longblob|binary|varbinary)(\(|$)/', $type)) {
                    $binaryColumns[(string) $column['Field']] = true;
                }
            }

            $offset = 0;
            do {
                $rows = $pdo->query('SELECT * FROM ' . $quotedTable . ' LIMIT 500 OFFSET ' . $offset)
                    ->fetchAll(PDO::FETCH_ASSOC);
                foreach ($rows as $row) {
                    $values = [];
                    foreach ($row as $columnName => $value) {
                        if ($value === null) {
                            $values[] = 'NULL';
                        } elseif (isset($binaryColumns[$columnName])) {
                            $values[] = "0x" . bin2hex((string) $value);
                        } else {
                            $quotedValue = $pdo->quote((string) $value);
                            if ($quotedValue === false) {
                                throw new RuntimeException("Gagal mengamankan nilai tabel {$table}.");
                            }
                            $values[] = $quotedValue;
                        }
                    }

                    $this->write(
                        $stream,
                        'INSERT INTO ' . $quotedTable . ' VALUES (' . implode(', ', $values) . ");\n"
                    );
                }
                $offset += count($rows);
            } while (count($rows) === 500);

            $this->write($stream, "\n");
        }

        $this->write($stream, "SET FOREIGN_KEY_CHECKS=1;\n");
    }

    /**
     * @param resource $stream
     */
    private function write($stream, string $contents): void
    {
        if (fwrite($stream, $contents) === false) {
            throw new RuntimeException('Gagal menulis berkas backup database.');
        }
    }

    private function quoteIdentifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }
}
