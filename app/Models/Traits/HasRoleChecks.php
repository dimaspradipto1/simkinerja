<?php

namespace App\Models\Traits;

/**
 * Trait HasRoleChecks
 *
 * @mixin \App\Models\User
 * @property string $roles
 * @property array $roles_array
 * @property array $jabatan_kepanitiaan
 */
trait HasRoleChecks
{
    /**
     * Get array of roles for this user
     *
     * @return array<string>
     */
    public function getRolesArrayAttribute(): array
    {
        if (empty($this->roles)) {
            return [];
        }
        $parts = explode(',', $this->roles);
        return array_values(array_filter(array_map('trim', $parts)));
    }

    /**
     * Check if user has specific role (case-insensitive)
     */
    public function hasRole(string $role): bool
    {
        $target = strtolower(trim($role));
        $myRoles = array_map('strtolower', $this->roles_array);
        return in_array($target, $myRoles, true);
    }

    /**
     * Check if user has any of the given roles (case-insensitive)
     */
    public function hasAnyRole(array|string $roles): bool
    {
        if (is_string($roles)) {
            $roles = array_map('trim', explode(',', $roles));
        }

        $myRoles = array_map('strtolower', $this->roles_array);
        $targetRoles = array_map(function ($r) {
            return strtolower(trim($r));
        }, (array) $roles);

        foreach ($targetRoles as $target) {
            if (in_array($target, $myRoles, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user is Superadmin
     */
    public function isSuperAdmin(): bool
    {
        if (in_array(strtolower(trim($this->email ?? '')), ['admin@gmail.com', 'superadmin@uis.ac.id']) || strtolower(trim($this->name ?? '')) === 'superadmin') {
            return true;
        }
        return $this->hasAnyRole(['SUPER ADMIN', 'SUPERADMIN']) || str_contains(strtoupper($this->jabatan ?? ''), 'SUPERADMIN');
    }

    /**
     * Check if this user is a protected system account (Superadmin / Admin)
     */
    public function isProtectedAdmin(): bool
    {
        return $this->isSuperAdmin() || $this->isAdmin();
    }

    /**
     * Check if user has Tim Evaluasi role
     */
    public function isTimEvaluasi(): bool
    {
        return $this->hasAnyRole(['tim evaluasi', 'evaluasi', 'tim_evaluasi']);
    }

    /**
     * Check if user is Koordinator Panitia (Ketua / Koordinator dalam Kepanitiaan)
     */
    public function isKoordinatorPanitia(): bool
    {
        if ($this->hasAnyRole(['koordinator panitia', 'koordinator', 'ketua panitia', 'admin kepanitiaan', 'koordinator kepanitiaan'])) {
            return true;
        }

        $jabatan = strtolower($this->jabatan ?? '');
        if (str_contains($jabatan, 'koordinator')) {
            return true;
        }

        if (!empty($this->jabatan_kepanitiaan) && is_array($this->jabatan_kepanitiaan)) {
            foreach ($this->jabatan_kepanitiaan as $item) {
                $pos = strtolower($item['jabatan'] ?? '');
                if (str_contains($pos, 'koordinator') || str_contains($pos, 'ketua') || str_contains($pos, 'penanggung jawab')) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if user is Sekretaris Panitia / Pengelola Kepanitiaan
     */
    public function isSekretarisPanitia(?\App\Models\Kegiatan $kepanitiaan = null): bool
    {
        if ($this->isAdmin() || $this->isSuperAdmin()) {
            return true;
        }

        if ($this->hasAnyRole(['sekretaris panitia', 'sekretaris kepanitiaan', 'sekretaris', 'admin kepanitiaan', 'koordinator panitia', 'koordinator', 'ketua panitia'])) {
            return true;
        }

        $jabatan = strtolower($this->jabatan ?? '');
        if (str_contains($jabatan, 'sekretaris panitia') || str_contains($jabatan, 'sekretaris kepanitiaan') || str_contains($jabatan, 'koordinator panitia') || str_contains($jabatan, 'ketua panitia')) {
            return true;
        }

        if (!empty($this->jabatan_kepanitiaan) && is_array($this->jabatan_kepanitiaan)) {
            foreach ($this->jabatan_kepanitiaan as $item) {
                $pos = strtolower($item['jabatan'] ?? '');
                if (str_contains($pos, 'sekretaris') || str_contains($pos, 'koordinator') || str_contains($pos, 'ketua')) {
                    return true;
                }
            }
        }

        if ($kepanitiaan && $kepanitiaan->user_id === $this->id) {
            return true;
        }

        return false;
    }

    /**
     * Check if user can access Menu / Fitur LPJ
     * Hanya Tim Evaluasi, Koordinator Panitia, dan Admin/Superadmin
     */
    public function canAccessLpj(): bool
    {
        if ($this->isAdmin() || $this->isSuperAdmin()) {
            return true;
        }

        return $this->isTimEvaluasi() || $this->isKoordinatorPanitia();
    }

    /**
     * Get list of committee positions
     *
     * @return array<array{nama: string, jabatan: string}>
     */
    public function getDaftarJabatanKepanitiaanAttribute(): array
    {
        if (!empty($this->jabatan_kepanitiaan) && is_array($this->jabatan_kepanitiaan)) {
            return array_values(array_filter($this->jabatan_kepanitiaan, function ($item) {
                return !empty($item['nama']) && !empty($item['jabatan']);
            }));
        }

        return [];
    }

    /**
     * Check if user has kepanitiaan role
     */
    public function isKepanitiaan(): bool
    {
        if ($this->isAdmin() || $this->isSuperAdmin()) {
            return true;
        }

        $hasDynamic = !empty($this->jabatan_kepanitiaan)
            && is_array($this->jabatan_kepanitiaan)
            && count(array_filter($this->jabatan_kepanitiaan, fn($item) => !empty($item['jabatan'] ?? null))) > 0;

        return $this->hasAnyRole(['kepanitiaan', 'panitia', 'admin kepanitiaan', 'staff kepanitiaan', 'koordinator panitia', 'sekretaris panitia'])
            || $hasDynamic;
    }

    /**
     * Add or update dynamic kepanitiaan entry for user
     */
    public function addJabatanKepanitiaan(string $nama, string $jabatan): void
    {
        $current = is_array($this->jabatan_kepanitiaan) ? $this->jabatan_kepanitiaan : [];
        $found = false;
        foreach ($current as &$item) {
            if (strcasecmp(trim($item['nama'] ?? ''), trim($nama)) === 0) {
                $item['jabatan'] = trim($jabatan);
                $found = true;
                break;
            }
        }
        unset($item);

        if (!$found) {
            $current[] = [
                'nama' => trim($nama),
                'jabatan' => trim($jabatan),
            ];
        }

        $this->jabatan_kepanitiaan = array_values($current);

        if (!$this->hasRole('kepanitiaan')) {
            $existingRoles = $this->roles_array;
            $existingRoles[] = 'kepanitiaan';
            $this->roles = implode(', ', array_unique($existingRoles));
        }

        $this->save();
    }

    /**
     * Remove specific kepanitiaan entry from user
     */
    public function removeJabatanKepanitiaan(string $nama): void
    {
        $current = is_array($this->jabatan_kepanitiaan) ? $this->jabatan_kepanitiaan : [];
        $filtered = array_values(array_filter($current, function ($item) use ($nama) {
            return strcasecmp(trim($item['nama'] ?? ''), trim($nama)) !== 0;
        }));

        $this->jabatan_kepanitiaan = $filtered;
        $this->save();
    }

    /**
     * Check if user has ONLY kepanitiaan role (no other roles)
     */
    public function isOnlyKepanitiaan(): bool
    {
        if ($this->isAdmin() || $this->isSuperAdmin() || $this->isRektorOrSuperAdmin()) {
            return false;
        }

        $roles = array_map('strtolower', $this->roles_array);
        $kepanitiaanAliases = ['kepanitiaan', 'panitia', 'admin kepanitiaan', 'staff kepanitiaan'];

        $hasKepanitiaan = false;
        $hasOtherRole = false;

        foreach ($roles as $r) {
            if (in_array($r, $kepanitiaanAliases, true)) {
                $hasKepanitiaan = true;
            } elseif (!empty($r)) {
                $hasOtherRole = true;
            }
        }

        if ($hasOtherRole) {
            return false;
        }

        return $hasKepanitiaan;
    }

    /**
     * Check if user can access Absensi Kepanitiaan menu
     */
    public function canAccessAbsensi(): bool
    {
        if ($this->isAdmin() || $this->isSuperAdmin() || $this->isRektorOrSuperAdmin()) {
            return true;
        }

        return $this->isKepanitiaan();
    }

    /**
     * Check if user can access Tugas & Kinerja menu
     */
    public function canAccessKinerja(): bool
    {
        return !$this->isOnlyKepanitiaan();
    }

    /**
     * Check if user is specifically Rektor or Wakil Rektor (Pimpinan Rektorat, not Admin)
     */
    public function isRektor(): bool
    {
        if ($this->isAdmin() || $this->isSuperAdmin()) {
            return false;
        }

        if ($this->hasAnyRole(['pimpinan rektorat', 'REKTOR', 'WAKIL REKTOR I', 'WAKIL REKTOR II', 'WAKIL REKTOR III'])) {
            return true;
        }

        $jabatan = strtoupper($this->jabatan ?? '');
        return str_contains($jabatan, 'REKTOR') || str_contains($jabatan, 'WAKIL REKTOR');
    }

    /**
     * Check if user is Superadmin, Admin, or Rektor (Pimpinan Rektorat Utama)
     */
    public function isRektorOrSuperAdmin(): bool
    {
        if ($this->isSuperAdmin() || $this->isAdmin()) {
            return true;
        }

        if ($this->hasAnyRole(['pimpinan rektorat', 'REKTOR', 'WAKIL REKTOR I', 'WAKIL REKTOR II', 'WAKIL REKTOR III'])) {
            return true;
        }

        $jabatan = strtoupper($this->jabatan ?? '');
        return str_contains($jabatan, 'REKTOR') || str_contains($jabatan, 'WAKIL REKTOR');
    }

    /**
     * Check if user is strictly the system admin/superadmin account (pure admin, not regular staff/pimpinan).
     */
    public function isSystemAdmin(): bool
    {
        $email = strtolower(trim($this->email ?? ''));
        $name = strtolower(trim($this->name ?? ''));
        $jabatan = strtoupper(trim($this->jabatan ?? ''));

        if (in_array($email, ['admin@uis.ac.id', 'admin@gmail.com', 'superadmin@uis.ac.id']) || in_array($name, ['admin', 'superadmin'])) {
            return true;
        }

        if (in_array($jabatan, ['ADMIN', 'SUPERADMIN', 'ADMIN ICT'])) {
            return true;
        }

        return false;
    }

    /**
     * Check if user is Superadmin or Admin
     */
    public function isAdmin(): bool
    {
        if (in_array(strtolower(trim($this->email ?? '')), ['admin@uis.ac.id', 'admin@gmail.com', 'superadmin@uis.ac.id']) || in_array(strtolower(trim($this->name ?? '')), ['admin', 'superadmin'])) {
            return true;
        }
        return $this->hasAnyRole(['SUPER ADMIN', 'ADMIN ICT', 'SUPERADMIN', 'ADMIN']) || in_array(strtoupper(trim($this->jabatan ?? '')), ['ADMIN', 'SUPERADMIN', 'ADMIN ICT']);
    }

    /**
     * Check if user is Pimpinan Rektorat (Rektor / Wakil Rektor I, II, III)
     */
    public function isPimpinanRektorat(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($this->hasAnyRole(['pimpinan rektorat', 'REKTOR', 'WAKIL REKTOR I', 'WAKIL REKTOR II', 'WAKIL REKTOR III', 'SUPER ADMIN', 'ADMIN'])) {
            return true;
        }

        $jabatan = strtoupper($this->jabatan ?? '');
        return str_contains($jabatan, 'REKTOR') || str_contains($jabatan, 'WAKIL REKTOR');
    }

    /**
     * Check if user is Pimpinan Sub Unit (Wadek, Kaprodi, Sekprodi, Kabid, Ka Lab, dll)
     */
    public function isPimpinanSubUnit(): bool
    {
        return $this->hasAnyRole(['pimpinan sub unit', 'kaprodi', 'ketua program studi', 'sekretaris prodi', 'kabid']);
    }

    /**
     * Check if user is Staff / Pelaksana
     */
    public function isStaff(): bool
    {
        return $this->hasAnyRole(['staff', 'pegawai']);
    }

    /**
     * Check if user is Pimpinan Unit / Atasan (Kepala Biro, Kepala ICT, Dekan, Wakil Dekan, Kaprodi, Admin LPPM, Admin LPMI, dll)
     */
    public function isPimpinanUnit(): bool
    {
        if ($this->isPimpinanRektorat()) {
            return true;
        }

        if ($this->hasAnyRole(['pimpinan unit', 'pimpinan sub unit'])) {
            return true;
        }

        $pimpinanRoles = [
            'pimpinan unit', 'pimpinan sub unit',
            'KEPALA BIRO', 'KEPALA ICT', 'KEPALA LPTI', 'DEKAN', 'WAKIL DEKAN I', 'WAKIL DEKAN II', 
            'KETUA PROGRAM STUDI', 'KAPRODI', 'ADMIN PERPUSTAKAAN', 'ADMIN LPPM', 'ADMIN LPMI',
            'KEPALA LPPM', 'KEPALA LPMI', 'KEPALA UPMI', 'KEPALA UPPM', 'KEPALA LABORATORIUM',
            'ADMIN AKADEMIK UNIVERSITAS', 'ADMIN KEUANGAN UNIVERSITAS', 'ADMIN KEPEGAWAIAN',
            'ADMIN SARANA PRASARANA', 'ADMIN HUMAS DAN PUBLIKASI', 'ADMIN KERJASAMA',
            'ADMIN KARIR DAN ALUMNI', 'ADMIN PERENCANAAN DAN PENGEMBANGAN'
        ];

        if ($this->hasAnyRole($pimpinanRoles)) {
            return true;
        }

        $pimpinanKeywords = [
            'KA. BIRO', 'KEPALA BIRO', 'KABID', 'KEPALA', 'KA. LPTI', 
            'DEKAN', 'WAKIL DEKAN', 'KETUA PROGRAM STUDI', 'KA. PRODI', 
            'KA. LPPM', 'KA. HUMAS', 'KA. UPPM', 'KA. LABORATORIUM', 'KA. PUSAT'
        ];

        $jabatan = strtoupper($this->jabatan ?? '');
        foreach ($pimpinanKeywords as $kw) {
            if (str_contains($jabatan, $kw)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user is Admin or Pimpinan (can unlock / reopen completed tasks)
     */
    public function canUnlockTasks(): bool
    {
        return $this->isSuperAdmin() 
            || $this->isAdmin() 
            || $this->isPimpinanRektorat() 
            || $this->isPimpinanUnit() 
            || $this->isPimpinanSubUnit()
            || $this->isKoordinatorPanitia();
    }
}
