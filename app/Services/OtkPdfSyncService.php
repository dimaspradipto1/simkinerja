<?php

namespace App\Services;

use App\Models\User;
use Database\Seeders\HierarchyOtkSeeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Smalot\PdfParser\Parser;

class OtkPdfSyncService
{
    private const POSITION_NAME_OVERRIDES = [
        'TATA USAHA|DIMAS' => 'ANSAR',
    ];

    private const FACULTIES = [
        'FAKULTAS EKONOMI DAN BISNIS',
        'FAKULTAS SAINS DAN TEKNOLOGI',
        'FAKULTAS ILMU KESEHATAN',
    ];

    public function sync(string $pdfPath): array
    {
        try {
            $pdf = (new Parser())->parseFile($pdfPath);
            $text = $pdf->getText();
        } catch (\Throwable $exception) {
            throw new RuntimeException('PDF SK OTK tidak dapat dibaca.', previous: $exception);
        }

        if (!str_contains(strtoupper($text), 'JABATAN UIS') || !str_contains(strtoupper($text), 'NAMA')) {
            throw new RuntimeException('PDF tidak memuat tabel jabatan dan nama OTK UIS.');
        }

        $members = $this->extractMembers($text);
        if (count($members) < 50) {
            throw new RuntimeException('Data personel OTK tidak terbaca lengkap dari PDF. Pastikan PDF berisi teks yang dapat disalin.');
        }

        $users = User::query()->get(['id', 'name', 'is_active']);
        $matchedMembers = [];
        $matchedIds = [];
        $unmatchedNames = [];

        foreach ($members as $sortOrder => $member) {
            try {
                $user = $this->resolveMemberUser($member, $users);
            } catch (RuntimeException $exception) {
                $unmatchedNames[] = "{$member['name']} (nama akun ambigu)";
                continue;
            }

            if (!$user) {
                $unmatchedNames[] = $member['name'];
                continue;
            }
            if (!$user->is_active) {
                $unmatchedNames[] = "{$member['name']} (akun tidak aktif: {$user->name})";
                continue;
            }
            if (isset($matchedIds[$user->id])) {
                $unmatchedNames[] = "{$member['name']} (terpetakan ganda ke {$user->name})";
                continue;
            }

            $matchedIds[$user->id] = true;
            $matchedMembers[] = [
                'user_id' => $user->id,
                'jabatan' => $member['jabatan'],
                'unit' => $member['unit'],
                'sort_order' => $sortOrder,
            ];
        }

        if ($unmatchedNames !== []) {
            throw new RuntimeException(
                'Sinkronisasi dibatalkan. Cocokkan atau aktifkan akun berikut terlebih dahulu: '
                . implode('; ', array_unique($unmatchedNames))
            );
        }

        DB::transaction(function () use ($matchedMembers): void {
            User::whereIn('id', DB::table('otk_members')->pluck('user_id'))
                ->update(['supervisor_id' => null]);
            (new HierarchyOtkSeeder())->run();

            foreach ($matchedMembers as $member) {
                User::whereKey($member['user_id'])->update([
                    'jabatan' => $member['jabatan'],
                    'unit' => $member['unit'],
                ]);
            }

            DB::table('otk_members')->delete();
            DB::table('otk_members')->insert(array_map(
                fn (array $member): array => [
                    'user_id' => $member['user_id'],
                    'sort_order' => $member['sort_order'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                $matchedMembers
            ));
            DB::table('otk_sync_state')->where('id', 1)->update(['synced_at' => now()]);
        });

        return ['updated_count' => count($matchedMembers)];
    }

    private function resolveMemberUser(array $member, $users): ?User
    {
        $position = $this->normalizeLabel($member['jabatan']);
        $nameWithoutCredentials = preg_split('/,/', $member['name'], 2)[0] ?? $member['name'];
        $name = $this->normalizeLabel($nameWithoutCredentials);
        $overrideName = self::POSITION_NAME_OVERRIDES["{$position}|{$name}"] ?? null;

        return HierarchyOtkSeeder::resolveUserByOtkName($overrideName ?? $member['name'], $users);
    }

    private function normalizeLabel(string $value): string
    {
        return trim(preg_replace('/[^A-Z0-9]+/u', ' ', mb_strtoupper($value)) ?? $value);
    }

    private function extractMembers(string $text): array
    {
        $members = [];
        $facultyIndex = 0;
        $currentFaculty = null;
        $currentCentralUnit = 'REKTORAT';

        foreach (preg_split('/\R/u', str_replace("\u{00A0}", ' ', $text)) ?: [] as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $columns = preg_split('/\t+/u', $line) ?: [];
            if (count($columns) >= 2) {
                $position = trim($columns[0]);
                $name = trim(end($columns));
            } else {
                [$position, $name] = $this->splitConcatenatedRow($line);
            }

            if (!$position || !$name || $this->isNonPersonnelRow($position, $name)) {
                continue;
            }

            $unit = $this->resolveUnit($position, $facultyIndex, $currentFaculty, $currentCentralUnit);
            $members[] = [
                'jabatan' => $position,
                'name' => $name,
                'unit' => $unit,
            ];
        }

        return $members;
    }

    private function splitConcatenatedRow(string $line): array
    {
        $prefixes = [
            'KA. BIRO KEMAHASISWAAN, ALUMNI, KERJASAMA, PERENCANAAN DAN PENGEMBANGAN' => 'Dr. HERDIANTI, M.Kes',
            'KETUA PROGRAM STUDI TEKNIK INFORMATIKA DAN PRODI SISTEM INFORMASI' => 'ARMY TRILIDIA DEVEGA, S.Kom, M.Pd',
            'SEKRETARIS PROGRAM STUDI TEKNIK INFORMATIKA DAN PRODI SISTEM INFORMASI' => 'ATMAN LUCKY F, S.Kom., M.Kom',
        ];

        foreach ($prefixes as $position => $name) {
            if (str_starts_with(mb_strtoupper($line), mb_strtoupper($position))) {
                $personName = trim(mb_substr($line, mb_strlen($position)));

                return $personName === '' ? [null, null] : [$position, $personName];
            }
        }

        return [null, null];
    }

    private function isNonPersonnelRow(string $position, string $name): bool
    {
        $position = mb_strtoupper(trim($position));
        $name = mb_strtoupper(trim($name));

        return $position === 'NO.'
            || $position === 'JABATAN UIS'
            || $name === 'NAMA'
            || preg_match('/^(?:[1-4]|UNIT|REKTORAT UIS|PERPUSTAKAAN|UPT TEKNIK INFORMASI DAN|KOORDINASI MELALUI|TANGGUNG JAWAB|UNSUR PELAKSANA|PELAKSANA AKADEMIK|BATAM,|NAMA - NAMA|TAHUN )/u', $position) === 1;
    }

    private function resolveUnit(
        string $position,
        int &$facultyIndex,
        ?string &$currentFaculty,
        string &$currentCentralUnit
    ): string
    {
        $normalizedPosition = mb_strtoupper($position);
        if (preg_match('/^DEKAN(?:\s|$)/u', $normalizedPosition) === 1) {
            $currentFaculty = self::FACULTIES[$facultyIndex] ?? self::FACULTIES[array_key_last(self::FACULTIES)];
            $facultyIndex++;
        }

        if ($currentFaculty !== null) {
            return $currentFaculty;
        }

        if (str_contains($normalizedPosition, 'KA. BIRO ADMINISTRASI AKADEMIK')) {
            $currentCentralUnit = 'BAAK';
        } elseif ($normalizedPosition === 'KEPALA' || str_contains($normalizedPosition, 'PUSTAKAWAN')) {
            $currentCentralUnit = 'PERPUSTAKAAN';
        } elseif (str_contains($normalizedPosition, 'KA. BIRO ADMINISTRASI UMUM')) {
            $currentCentralUnit = 'BAUK';
        } elseif (str_contains($normalizedPosition, 'KEPALA ICT')) {
            $currentCentralUnit = 'UPT TEKNIK INFORMASI DAN KOMUNIKASI';
        } elseif (str_contains($normalizedPosition, 'KA. BIRO KEMAHASISWAAN')) {
            $currentCentralUnit = 'BIRO KEMAHASISWAAN';
        } elseif (str_contains($normalizedPosition, 'KA. LPPM')) {
            $currentCentralUnit = 'LPPM';
        } elseif (str_contains($normalizedPosition, 'KA. LPMI')) {
            $currentCentralUnit = 'LPMI';
        }

        if (str_contains($normalizedPosition, 'FAKULTAS EKONOMI DAN BISNIS')) {
            return self::FACULTIES[0];
        }
        if (str_contains($normalizedPosition, 'FAKULTAS TEKNIK')
            || str_contains($normalizedPosition, 'FAKULTAS SAINS DAN TEKNOLOGI')) {
            return self::FACULTIES[1];
        }
        if (str_contains($normalizedPosition, 'FAKULTAS ILMU KESEHATAN')) {
            return self::FACULTIES[2];
        }

        return $currentCentralUnit;
    }
}
