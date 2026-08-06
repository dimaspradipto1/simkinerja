<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Milestone extends Model
{
    use HasFactory;

    protected $table = 'milestones';

    protected $fillable = [
        'milestonable_id',
        'milestonable_type',
        'nama_milestone',
        'catatan',
        'status',
        'waktu_mulai',
        'waktu_selesai',
        'last_started_at',
        'durasi_detik',
    ];

    protected $casts = [
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
        'last_started_at' => 'datetime',
        'durasi_detik' => 'integer',
    ];

    protected $appends = [
        'total_durasi_detik',
        'formatted_durasi',
    ];

    public function milestonable()
    {
        return $this->morphTo();
    }

    /**
     * Calculate total active duration in seconds (including ongoing elapsed time if status is Berjalan)
     */
    public function getTotalDurasiDetikAttribute(): int
    {
        $detik = $this->durasi_detik ?? 0;

        if ($this->status === 'Berjalan' && $this->last_started_at) {
            $detik += Carbon::now()->diffInSeconds($this->last_started_at);
        }

        return (int) max(0, $detik);
    }

    /**
     * Get human-readable formatted active duration (e.g. 1j 20m 15s)
     */
    public function getFormattedDurasiAttribute(): string
    {
        $totalDetik = $this->total_durasi_detik;
        if ($totalDetik <= 0) {
            return '0s';
        }

        $days = floor($totalDetik / 86400);
        $hours = floor(($totalDetik % 86400) / 3600);
        $minutes = floor(($totalDetik % 3600) / 60);
        $seconds = $totalDetik % 60;

        $parts = [];
        if ($days > 0) $parts[] = $days . 'h';
        if ($hours > 0) $parts[] = $hours . 'j';
        if ($minutes > 0) $parts[] = $minutes . 'm';
        if ($seconds > 0 || empty($parts)) $parts[] = $seconds . 's';

        return implode(' ', $parts);
    }
}
