<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'roles',
        'is_active',
        'nidn',
        'unit',
        'jabatan',
        'jabatan_kepanitiaan',
        'status',
        'supervisor_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'jabatan_kepanitiaan' => 'array',
        ];
    }

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
                $namaKep = strtolower($item['nama'] ?? '');

                if ($kepanitiaan && !empty($kepanitiaan->uraian_tugas)) {
                    $targetKep = strtolower($kepanitiaan->uraian_tugas);
                    if ((str_contains($pos, 'sekretaris') || str_contains($pos, 'koordinator') || str_contains($pos, 'ketua')) && (str_contains($targetKep, $namaKep) || str_contains($namaKep, $targetKep))) {
                        return true;
                    }
                } else {
                    if (str_contains($pos, 'sekretaris') || str_contains($pos, 'koordinator') || str_contains($pos, 'ketua')) {
                        return true;
                    }
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
     * Check if user has Kepanitiaan role
     */
    public function isKepanitiaan(): bool
    {
        $hasDynamic = !empty($this->jabatan_kepanitiaan)
            && is_array($this->jabatan_kepanitiaan)
            && count(array_filter($this->jabatan_kepanitiaan, fn($item) => !empty($item['jabatan'] ?? null))) > 0;

        return $this->hasAnyRole(['kepanitiaan', 'panitia', 'admin kepanitiaan', 'staff kepanitiaan'])
            || $hasDynamic;
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

        // Ensure roles includes 'kepanitiaan'
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

        // If user has non-kepanitiaan roles, they are NOT only kepanitiaan
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

    public function taggedRencanaKerjas()
    {
        return $this->belongsToMany(RencanaKerja::class, 'kegiatan_user', 'user_id', 'kegiatan_id')->withTimestamps();
    }

    public function taggedKepanitiaans()
    {
        return $this->belongsToMany(Kepanitiaan::class, 'kegiatan_user', 'user_id', 'kegiatan_id')->withTimestamps();
    }

    public function taggedKegiatans()
    {
        return $this->belongsToMany(Kegiatan::class, 'kegiatan_user', 'user_id', 'kegiatan_id')->withTimestamps();
    }

    public function taggedInsidentils()
    {
        return $this->belongsToMany(Insidentil::class, 'kegiatan_user', 'user_id', 'kegiatan_id')->withTimestamps();
    }

    // =========================================================================
    // HIRARKI PIMPINAN - BAWAHAN (berbasis supervisor_id di DB)
    // =========================================================================

    /**
     * Relasi ke atasan langsung (supervisor).
     */
    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    /**
     * Relasi ke semua bawahan langsung (subordinates) berdasarkan supervisor_id.
     */
    public function subordinates()
    {
        return $this->hasMany(User::class, 'supervisor_id');
    }

    /**
     * Dapatkan semua ID bawahan langsung dan berjenjang (recursive).
     *
     * @param array $visited Array untuk mencegah circular recursion
     * @return int[]
     */
    public function getAllSubordinateIds(array $visited = []): array
    {
        $visited[] = $this->id;
        $ids = [];

        $directSubs = $this->subordinates()->where('is_active', true)->get(['id', 'supervisor_id']);
        foreach ($directSubs as $sub) {
            if (!in_array($sub->id, $visited, true)) {
                $ids[] = $sub->id;
                $ids = array_merge($ids, $sub->getAllSubordinateIds($visited));
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * Ambil semua ID bawahan langsung & berjenjang dari DB hierarchy.
     * Mengembalikan array ID termasuk ID diri sendiri jika punya bawahan, atau null jika belum di-set.
     *
     * @return array|null  array of user IDs, or null if not configured in DB
     */
    public function getSubordinateIdsFromDb(): ?array
    {
        $ids = $this->getAllSubordinateIds();
        if (!empty($ids)) {
            // Sertakan ID diri sendiri agar pimpinan juga muncul di laporannya
            array_unshift($ids, $this->id);
            return array_values(array_unique($ids));
        }

        // Jika user adalah pegawai biasa yang memiliki supervisor tetapi tidak memiliki bawahan
        if ($this->supervisor_id) {
            return [$this->id];
        }

        return null;
    }

    /**
     * Get direct supervisor(s) based on DB hierarchy first, then unit/role fallback.
     */
    public function getDirectSupervisors()
    {
        // Prioritas: gunakan supervisor dari DB jika ada
        if ($this->supervisor_id) {
            $dbSupervisor = self::where('id', '=', $this->supervisor_id)->where('is_active', '=', 1)->get();
            if ($dbSupervisor->isNotEmpty()) {
                return $dbSupervisor;
            }
        }

        // Fallback: cari berdasarkan unit & role (legacy behavior)
        $allPimpinan = self::where('is_active', '=', 1)
            ->where('id', '!=', $this->id)
            ->get()
            ->filter(function ($u) {
                return $u->isPimpinanUnit() || $u->isPimpinanRektorat() || $u->isAdmin();
            });

        $myUnit = trim(strtoupper($this->unit ?? ''));

        if (!empty($myUnit)) {
            $unitPimpinan = $allPimpinan->filter(function ($u) use ($myUnit) {
                $pUnit = trim(strtoupper($u->unit ?? ''));
                return $pUnit === $myUnit || str_contains($pUnit, $myUnit) || str_contains($myUnit, $pUnit);
            });

            if ($unitPimpinan->isNotEmpty()) {
                return $unitPimpinan->concat($allPimpinan->diff($unitPimpinan));
            }
        }

        return $allPimpinan;
    }
}

