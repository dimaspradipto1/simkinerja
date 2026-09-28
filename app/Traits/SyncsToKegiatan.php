<?php

namespace App\Traits;

use App\Models\Kegiatan;

trait SyncsToKegiatan
{
    /**
     * Backward-compatible no-op trait since tasks are now unified in kegiatans table.
     */
    public function syncToKegiatan(): Kegiatan
    {
        return $this instanceof Kegiatan ? $this : (Kegiatan::find($this->id) ?? new Kegiatan());
    }
}
