<?php

namespace App\Models\Traits;

use App\Models\User;

/**
 * Trait HasHierarchyTree
 *
 * @mixin \App\Models\User
 * @property int $id
 * @property int|null $supervisor_id
 * @property string|null $unit
 */
trait HasHierarchyTree
{
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
            $dbSupervisor = User::where('id', $this->supervisor_id)->where('is_active', true)->get();
            if ($dbSupervisor->isNotEmpty()) {
                return $dbSupervisor;
            }
        }

        // Fallback: cari berdasarkan unit & role (legacy behavior)
        $allPimpinan = User::where('is_active', true)
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
