<?php

namespace App\Policies;

use App\Models\Contact;
use App\Models\User;

class ContactPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('send-campaigns');
    }

    public function view(User $user, Contact $contact): bool
    {
        return $user->can('send-campaigns');
    }

    public function create(User $user): bool
    {
        return $user->can('send-campaigns');
    }

    public function update(User $user, Contact $contact): bool
    {
        return $user->can('send-campaigns');
    }

    public function delete(User $user, Contact $contact): bool
    {
        return $user->can('send-campaigns');
    }
}
