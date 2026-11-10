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
}
