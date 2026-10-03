<?php

namespace App\Services;

use App\Models\PengaturanPesanWhatsapp;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class WhatsappRecipientResolver
{
    /**
     * Resolve recipients from the task owner and the configured OTK chain.
     *
     * @return Collection<int, User>
     */
    public function resolve(PengaturanPesanWhatsapp $template, User $taskOwner): Collection
    {
        if (
            !$taskOwner->is_active
            || !$taskOwner->supervisor_id
            || !$template->recipients()->whereKey($taskOwner->getKey())->exists()
        ) {
            return new Collection();
        }

        return new Collection([$taskOwner]);
    }
}
