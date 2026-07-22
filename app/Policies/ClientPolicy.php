<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

/**
 * Per blueprint §5: only internal staff manage clients - Client portal users
 * can never build, edit, or delete anything here, only view/manage their own profile
 * (handled separately by Portal\CompanyProfileController).
 */
class ClientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'survyra_admin']);
    }

    public function view(User $user, Client $client): bool
    {
        return $user->hasAnyRole(['super_admin', 'survyra_admin']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'survyra_admin']);
    }

    public function update(User $user, Client $client): bool
    {
        return $user->hasAnyRole(['super_admin', 'survyra_admin']);
    }

    public function delete(User $user, Client $client): bool
    {
        return $user->hasRole('super_admin');
    }
}
