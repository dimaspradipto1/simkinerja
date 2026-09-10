<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LpjTemplate extends Model
{
    use HasFactory;

    protected $table = 'lpj_templates';

    protected $fillable = [
        'nama_template',
        'link_lpj',
        'file_template',
        'keterangan',
        'updated_by',
    ];

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get URL for the uploaded file template if any.
     */
    public function getFileUrlAttribute(): ?string
    {
        return $this->file_template ? asset('storage/' . $this->file_template) : null;
    }
}
