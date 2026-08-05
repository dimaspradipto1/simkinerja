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
        'status',
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
        ];
    }

    /**
     * Check if user is Superadmin
     */
    public function isSuperAdmin(): bool
    {
        $role = strtoupper(trim($this->roles ?? ''));
        return in_array($role, ['SUPER ADMIN', 'SUPERADMIN']) || $this->email === 'admin@gmail.com';
    }

    /**
     * Check if user is Superadmin or Rektor (Pimpinan Rektorat Utama)
     */
    public function isRektorOrSuperAdmin(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $role = strtoupper(trim($this->roles ?? ''));
        $jabatan = strtoupper($this->jabatan ?? '');

        if (in_array($role, ['REKTOR', 'WAKIL REKTOR I', 'WAKIL REKTOR II', 'WAKIL REKTOR III'])) {
            return true;
        }

        return str_contains($jabatan, 'REKTOR') || str_contains($jabatan, 'WAKIL REKTOR');
    }

    /**
     * Check if user is Superadmin or Admin
     */
    public function isAdmin(): bool
    {
        $role = strtoupper(trim($this->roles ?? ''));
        return in_array($role, ['SUPER ADMIN', 'ADMIN ICT', 'ADMIN LPTI', 'SUPERADMIN', 'ADMIN']);
    }

    /**
     * Check if user is Pimpinan Rektorat (Rektor / Wakil Rektor I, II, III)
     */
    public function isPimpinanRektorat(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $role = strtoupper(trim($this->roles ?? ''));
        $jabatan = strtoupper($this->jabatan ?? '');
        $unit = strtoupper($this->unit ?? '');

        if (in_array($role, ['REKTOR', 'WAKIL REKTOR I', 'WAKIL REKTOR II', 'WAKIL REKTOR III', 'SUPER ADMIN', 'ADMIN'])) {
            return true;
        }

        return str_contains($jabatan, 'REKTOR') || str_contains($jabatan, 'WAKIL REKTOR');
    }

    /**
     * Check if user is Pimpinan Unit / Atasan (Kepala Biro, Kepala ICT, Dekan, Wakil Dekan, Kaprodi, Admin LPPM, Admin LPMI, dll)
     */
    public function isPimpinanUnit(): bool
    {
        if ($this->isPimpinanRektorat()) {
            return true;
        }

        $role = strtoupper(trim($this->roles ?? ''));
        $jabatan = strtoupper($this->jabatan ?? '');

        $pimpinanRoles = [
            'KEPALA BIRO', 'KEPALA ICT', 'KEPALA LPTI', 'DEKAN', 'WAKIL DEKAN I', 'WAKIL DEKAN II', 
            'KAPRODI', 'ADMIN PERPUSTAKAAN', 'ADMIN LPPM', 'ADMIN LPMI'
        ];

        if (in_array($role, $pimpinanRoles)) {
            return true;
        }

        $pimpinanKeywords = [
            'KA. BIRO', 'KEPALA BIRO', 'KABID', 'KEPALA', 'KA. LPTI', 
            'DEKAN', 'WAKIL DEKAN', 'KETUA PROGRAM STUDI', 'KA. PRODI', 
            'KA. LPPM', 'KA. HUMAS', 'KA. UPPM', 'KA. LABORATORIUM', 'KA. PUSAT'
        ];

        foreach ($pimpinanKeywords as $kw) {
            if (str_contains($jabatan, $kw)) {
                return true;
            }
        }

        return false;
    }

    public function taggedRencanaKerjas()
    {
        return $this->belongsToMany(RencanaKerja::class, 'rencana_kerja_user', 'user_id', 'rencana_kerja_id')->withTimestamps();
    }

    public function taggedKepanitiaans()
    {
        return $this->belongsToMany(Kepanitiaan::class, 'kepanitiaan_user', 'user_id', 'kepanitiaan_id')->withTimestamps();
    }

    public function taggedInsidentils()
    {
        return $this->belongsToMany(Insidentil::class, 'insidentil_user', 'user_id', 'insidentil_id')->withTimestamps();
    }
}

