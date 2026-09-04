<?php

namespace App\Models;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class AbsensiSetting
{
    /**
     * Path to the JSON persistence file.
     */
    protected static function getFilePath(): string
    {
        return storage_path('app/absensi_settings.json');
    }

    /**
     * Default attendance session configurations.
     */
    public static function defaults(): array
    {
        return [
            'absensi-pkkmb-pertama' => [
                'slug' => 'absensi-pkkmb-pertama',
                'kategori' => 'PKKMB',
                'nama' => 'Absensi PKKMB Hari Pertama',
                'is_active' => true,
                'keterangan' => 'Sesi absensi PKKMB hari ke-1',
            ],
            'absensi-pkkmb-kedua' => [
                'slug' => 'absensi-pkkmb-kedua',
                'kategori' => 'PKKMB',
                'nama' => 'Absensi PKKMB Hari Kedua',
                'is_active' => true,
                'keterangan' => 'Sesi absensi PKKMB hari ke-2',
            ],
            'absensi-pkkmb-ketiga' => [
                'slug' => 'absensi-pkkmb-ketiga',
                'kategori' => 'PKKMB',
                'nama' => 'Absensi PKKMB Hari Ketiga',
                'is_active' => true,
                'keterangan' => 'Sesi absensi PKKMB hari ke-3',
            ],
            'absensi-esq-pertama' => [
                'slug' => 'absensi-esq-pertama',
                'kategori' => 'ESQ',
                'nama' => 'Absensi ESQ Hari Pertama',
                'is_active' => true,
                'keterangan' => 'Sesi absensi ESQ hari ke-1',
            ],
            'absensi-esq-kedua' => [
                'slug' => 'absensi-esq-kedua',
                'kategori' => 'ESQ',
                'nama' => 'Absensi ESQ Hari Kedua',
                'is_active' => true,
                'keterangan' => 'Sesi absensi ESQ hari ke-2',
            ],
            'absensi-esq-ketiga' => [
                'slug' => 'absensi-esq-ketiga',
                'kategori' => 'ESQ',
                'nama' => 'Absensi ESQ Hari Ketiga',
                'is_active' => true,
                'keterangan' => 'Sesi absensi ESQ hari ke-3',
            ],
            'absensi-esq-keempat' => [
                'slug' => 'absensi-esq-keempat',
                'kategori' => 'ESQ',
                'nama' => 'Absensi ESQ Hari Keempat',
                'is_active' => true,
                'keterangan' => 'Sesi absensi ESQ hari ke-4',
            ],
            'absensi-milad-pertama' => [
                'slug' => 'absensi-milad-pertama',
                'kategori' => 'MILAD',
                'nama' => 'Absensi MILAD Hari Pertama',
                'is_active' => true,
                'keterangan' => 'Sesi absensi MILAD hari ke-1',
            ],
            'absensi-milad-kedua' => [
                'slug' => 'absensi-milad-kedua',
                'kategori' => 'MILAD',
                'nama' => 'Absensi MILAD Hari Kedua',
                'is_active' => true,
                'keterangan' => 'Sesi absensi MILAD hari ke-2',
            ],
            'absensi-kuliah-umum-pertama' => [
                'slug' => 'absensi-kuliah-umum-pertama',
                'kategori' => 'Kuliah Umum',
                'nama' => 'Absensi Kuliah Umum Hari Pertama',
                'is_active' => true,
                'keterangan' => 'Sesi absensi Kuliah Umum hari ke-1',
            ],
        ];
    }

    /**
     * Retrieve all attendance session settings as objects.
     */
    public static function all(): Collection
    {
        $filePath = self::getFilePath();

        if (!File::exists($filePath)) {
            $defaults = self::defaults();
            File::ensureDirectoryExists(dirname($filePath));
            File::put($filePath, json_encode($defaults, JSON_PRETTY_PRINT));
            $data = $defaults;
        } else {
            $content = File::get($filePath);
            $data = json_decode($content, true);
            if (!is_array($data)) {
                $data = self::defaults();
                File::put($filePath, json_encode($data, JSON_PRETTY_PRINT));
            }
        }

        // Merge any new defaults if missing
        $defaults = self::defaults();
        $updated = false;
        foreach ($defaults as $key => $val) {
            if (!isset($data[$key])) {
                $data[$key] = $val;
                $updated = true;
            }
        }
        if ($updated) {
            File::put($filePath, json_encode($data, JSON_PRETTY_PRINT));
        }

        return collect($data)->map(function ($item) {
            return (object) [
                'slug' => $item['slug'],
                'kategori' => $item['kategori'],
                'nama' => $item['nama'],
                'is_active' => (bool) ($item['is_active'] ?? true),
                'keterangan' => $item['keterangan'] ?? null,
            ];
        });
    }

    /**
     * Retrieve all settings grouped by category.
     */
    public static function getAllGrouped(): Collection
    {
        return self::all()->groupBy('kategori');
    }

    /**
     * Check if a specific attendance session is active.
     */
    public static function isActive(string $slug): bool
    {
        $statuses = self::getAllStatuses();
        return $statuses[$slug] ?? true;
    }

    /**
     * Get associative array of slug => is_active.
     */
    public static function getAllStatuses(): array
    {
        return Cache::remember('absensi_settings_statuses', 3600, function () {
            return self::all()->pluck('is_active', 'slug')->toArray();
        });
    }

    /**
     * Toggle the active status of a session.
     */
    public static function toggle(string $slug): object
    {
        $filePath = self::getFilePath();
        $items = self::all()->keyBy('slug')->toArray();

        if (!isset($items[$slug])) {
            abort(404, "Sesi absensi dengan slug {$slug} tidak ditemukan.");
        }

        $items[$slug]->is_active = !$items[$slug]->is_active;

        $saveData = [];
        foreach ($items as $k => $obj) {
            $saveData[$k] = (array) $obj;
        }

        File::ensureDirectoryExists(dirname($filePath));
        File::put($filePath, json_encode($saveData, JSON_PRETTY_PRINT));

        self::clearCache();

        return (object) $saveData[$slug];
    }

    /**
     * Clear cached attendance statuses.
     */
    public static function clearCache(): void
    {
        Cache::forget('absensi_settings_statuses');
    }
}
