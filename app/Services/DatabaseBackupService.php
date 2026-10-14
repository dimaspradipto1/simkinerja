<?php

namespace App\Services;

use FilesystemIterator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use PDO;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Throwable;
use ZipArchive;

class DatabaseBackupService
{
    /**
     * Export complete database structure and data to an open file stream.
     *
     * @param resource $stream
     */
    public function exportDatabase($stream, PDO $pdo): void
    {
        $this->write($stream, "-- SIM KINERJA MySQL backup\n");
        $this->write($stream, '-- Created: ' . now()->toDateTimeString() . "\n\n");
        $this->write($stream, "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n");

        $tables = $pdo->query('SHOW FULL TABLES')->fetchAll(PDO::FETCH_NUM);
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
     * Import an SQL file stream into the database.
     * Optionally wipes existing tables before import to ensure an exact synchronization.
     *
     * @return array{statements: int, dropped_tables: int}
     */
    public function importDatabase(string $sqlFilePath, bool $wipeExistingTables = false): array
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '1024M');

        $connection = DB::connection();
        if ($connection->getDriverName() !== 'mysql') {
            throw new RuntimeException('Impor database SQL hanya tersedia untuk koneksi database MySQL.');
        }

        $pdo = $connection->getPdo();
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);

        // Turn off foreign key constraints & strict modes during batch restoration
        $pdo->exec("SET NAMES utf8mb4;");
        $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");
        $pdo->exec("SET SQL_MODE='';");

        $droppedCount = 0;
        if ($wipeExistingTables) {
            $tables = $pdo->query('SHOW FULL TABLES')->fetchAll(PDO::FETCH_NUM);
            foreach ($tables as $tableRow) {
                if (($tableRow[1] ?? '') === 'BASE TABLE') {
                    $tableName = (string) $tableRow[0];
                    $pdo->exec('DROP TABLE IF EXISTS ' . $this->quoteIdentifier($tableName));
                    $droppedCount++;
                }
            }
        }

        $handle = fopen($sqlFilePath, 'r');
        if ($handle === false) {
            throw new RuntimeException('Tidak dapat membuka berkas SQL yang diunggah.');
        }

        $query = '';
        $inSingleQuote = false;
        $inDoubleQuote = false;
        $inBacktick = false;
        $inBlockComment = false;
        $isEscaped = false;
        $statementsExecuted = 0;

        try {
            while (($line = fgets($handle)) !== false) {
                $trimmed = trim($line);

                // Skip single-line comments when outside string literals
                if (!$inSingleQuote && !$inDoubleQuote && !$inBacktick && !$inBlockComment) {
                    if ($trimmed === '' || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '#')) {
                        continue;
                    }
                }

                $len = strlen($line);
                for ($i = 0; $i < $len; $i++) {
                    $c = $line[$i];

                    if ($inBlockComment) {
                        if ($c === '*' && $i + 1 < $len && $line[$i + 1] === '/') {
                            $inBlockComment = false;
                            $i++;
                        }
                        continue;
                    }

                    if ($isEscaped) {
                        $isEscaped = false;
                        $query .= $c;
                        continue;
                    }

                    if ($c === '\\') {
                        $isEscaped = true;
                        $query .= $c;
                        continue;
                    }

                    // Check for block comment open /*
                    if (!$inSingleQuote && !$inDoubleQuote && !$inBacktick && $c === '/' && $i + 1 < $len && $line[$i + 1] === '*') {
                        $inBlockComment = true;
                        $i++;
                        continue;
                    }

                    if ($c === "'" && !$inDoubleQuote && !$inBacktick) {
                        $inSingleQuote = !$inSingleQuote;
                        $query .= $c;
                    } elseif ($c === '"' && !$inSingleQuote && !$inBacktick) {
                        $inDoubleQuote = !$inDoubleQuote;
                        $query .= $c;
                    } elseif ($c === '`' && !$inSingleQuote && !$inDoubleQuote) {
                        $inBacktick = !$inBacktick;
                        $query .= $c;
                    } elseif ($c === ';' && !$inSingleQuote && !$inDoubleQuote && !$inBacktick) {
                        $stmt = trim($query);
                        if ($stmt !== '') {
                            $pdo->exec($stmt);
                            $statementsExecuted++;
                        }
                        $query = '';
                    } else {
                        $query .= $c;
                    }
                }
            }

            $leftover = trim($query);
            if ($leftover !== '') {
                $pdo->exec($leftover);
                $statementsExecuted++;
            }
        } finally {
            fclose($handle);
            $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");
        }

        // Clear all Laravel runtime caches to ensure clean synchronization
        try {
            Artisan::call('optimize:clear');
        } catch (Throwable) {
            // Ignore if artisan cache clear encounters non-critical environment issue
        }

        return [
            'statements' => $statementsExecuted,
            'dropped_tables' => $droppedCount,
        ];
    }

    /**
     * Archive the public storage disk into a ZIP file.
     */
    public function exportPublicStorage(string $temporaryZipPath): void
    {
        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('Ekstensi PHP ZIP belum aktif di server.');
        }

        $publicRoot = config('filesystems.disks.public.root');
        if (!$publicRoot || !is_dir($publicRoot) || !is_readable($publicRoot)) {
            throw new RuntimeException('Folder storage public tidak ditemukan atau tidak dapat dibaca.');
        }

        $archive = new ZipArchive();
        if ($archive->open($temporaryZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Tidak dapat membuat berkas arsip ZIP untuk storage public.');
        }

        try {
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
                    throw new RuntimeException("Gagal menambahkan berkas {$archivePath} ke arsip ZIP.");
                }
            }

            if (!$archive->close()) {
                throw new RuntimeException('Gagal menutup dan menyelesaikan berkas arsip ZIP.');
            }
        } catch (Throwable $e) {
            $archive->close();
            throw $e;
        }
    }

    /**
     * Import and extract a ZIP archive into the public storage disk.
     *
     * @return array{files_count: int}
     */
    public function importPublicStorage(string $zipFilePath): array
    {
        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('Ekstensi PHP ZIP belum aktif di server.');
        }

        $publicRoot = config('filesystems.disks.public.root');
        if (!$publicRoot) {
            throw new RuntimeException('Konfigurasi storage public root tidak valid.');
        }

        if (!is_dir($publicRoot)) {
            mkdir($publicRoot, 0755, true);
        }

        $archive = new ZipArchive();
        $openResult = $archive->open($zipFilePath);
        if ($openResult !== true) {
            throw new RuntimeException('Berkas ZIP tidak dapat dibuka atau berkas rusak.');
        }

        $totalFiles = $archive->numFiles;

        try {
            // Security validation: Prevent Zip Slip path traversal
            for ($i = 0; $i < $totalFiles; $i++) {
                $entryName = $archive->getNameIndex($i);
                if ($entryName === false) {
                    continue;
                }
                $normalized = str_replace('\\', '/', $entryName);
                if (str_contains($normalized, '../') || str_starts_with($normalized, '/') || preg_match('/^[a-zA-Z]:/', $normalized)) {
                    throw new RuntimeException("Arsip ZIP memuat jalur direktori yang tidak aman: {$entryName}");
                }
            }

            if (!$archive->extractTo($publicRoot)) {
                throw new RuntimeException('Gagal mengekstrak berkas ZIP ke dalam direktori storage public.');
            }

            $archive->close();
        } catch (Throwable $e) {
            $archive->close();
            throw $e;
        }

        // Ensure storage public symlink is active
        try {
            Artisan::call('storage:link');
        } catch (Throwable) {
            // Continue if link already exists
        }

        return [
            'files_count' => $totalFiles,
        ];
    }

    private function write($stream, string $contents): void
    {
        if (fwrite($stream, $contents) === false) {
            throw new RuntimeException('Gagal menulis data ke berkas backup.');
        }
    }

    private function quoteIdentifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }
}
