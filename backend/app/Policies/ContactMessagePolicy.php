<?php

namespace App\Policies;

use App\Models\ContactMessage;
use App\Models\User;

class ContactMessagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('manage_consultations');
    }

    public function update(User $user, ContactMessage $contactMessage): bool
    {
        return $this->viewAny($user);
    }
}
