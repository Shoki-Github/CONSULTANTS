<?php

namespace App\Policies;

use App\Models\Consultation;
use App\Models\User;

class ConsultationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('manage_consultations');
    }

    public function update(User $user, Consultation $consultation): bool
    {
        return $this->viewAny($user);
    }
}
