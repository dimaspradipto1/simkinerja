<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Traits\HasHierarchyTree;
use App\Models\Traits\HasRoleChecks;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoleChecks, HasHierarchyTree;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'foto',
        'nomor_wa',
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

    public function getAvatarUrlAttribute(): string
    {
        if ($this->foto && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->foto)) {
            return asset('storage/' . $this->foto);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=15432d&color=ffffff&bold=true';
    }

    public function getJabatanAttribute($value): ?string
    {
        if ($value && preg_match('/\bICT\b/i', $value)) {
            return preg_replace('/\bICT\b/i', 'LPTI', $value);
        }
        return $value;
    }

    public function setJabatanAttribute($value): void
    {
        $this->attributes['jabatan'] = $value && preg_match('/\bICT\b/i', $value)
            ? preg_replace('/\bICT\b/i', 'LPTI', $value)
            : $value;
    }

    public function getUnitAttribute($value): ?string
    {
        if ($value && preg_match('/\bICT\b/i', $value)) {
            return preg_replace('/\bICT\b/i', 'LPTI', $value);
        }
        return $value;
    }

    public function setUnitAttribute($value): void
    {
        $this->attributes['unit'] = $value && preg_match('/\bICT\b/i', $value)
            ? preg_replace('/\bICT\b/i', 'LPTI', $value)
            : $value;
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

    /**
     * Cek apakah user (atasan) memiliki wewenang memvalidasi kegiatan tertentu.
     */
    public function canValidateTask(\App\Models\Kegiatan $kegiatan): bool
    {
        // 1. Pegawai TIDAK PERNAH bisa memvalidasi tugasnya sendiri (harus divalidasi oleh atasan)
        if ((int)$kegiatan->user_id === (int)$this->id) {
            return false;
        }

        // 2. Superadmin sejati dapat memvalidasi semua tugas
        if ($this->isSuperAdmin()) {
            return true;
        }

        // 3. Pimpinan Rektorat sah (Rektor & Wakil Rektor I, II, III)
        $isRealPimpinanRektorat = $this->hasAnyRole(['pimpinan rektorat', 'rektor', 'wakil rektor i', 'wakil rektor ii', 'wakil rektor iii'])
            || str_contains(strtoupper($this->jabatan ?? ''), 'REKTOR');
        if ($isRealPimpinanRektorat) {
            return true;
        }

        // 4. Pimpinan Unit sah (Kepala Biro, Kepala UPT/LPTI, Dekan, dll) untuk pegawai di unit yang sama
        $jabatanUpper = strtoupper($this->jabatan ?? '');
        $isPimpinanOfUnit = str_contains($jabatanUpper, 'KEPALA') 
            || str_contains($jabatanUpper, 'KA.') 
            || str_contains($jabatanUpper, 'DEKAN')
            || str_contains($jabatanUpper, 'DIREKTUR');

        $kegiatanOwner = $kegiatan->user;
        if ($isPimpinanOfUnit && !empty($this->unit) && $kegiatanOwner) {
            $uUnit = strtoupper($this->unit);
            $oUnit = strtoupper($kegiatanOwner->unit ?? '');
            if ($uUnit === $oUnit 
                || (str_contains($uUnit, 'LPTI') && str_contains($oUnit, 'LPTI')) 
                || (str_contains($uUnit, 'TEKNIK INFORMASI') && str_contains($oUnit, 'TEKNIK INFORMASI'))) {
                return true;
            }
        }

        // 5. Atasan langsung atau atasan berjenjang dalam hierarki OTK
        $subordinateIds = $this->getAllSubordinateIds();
        if (in_array((int)$kegiatan->user_id, array_map('intval', $subordinateIds), true)) {
            return true;
        }

        return false;
    }
}
