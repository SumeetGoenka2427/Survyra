<?php

namespace App\Policies;

use App\Models\Survey;
use App\Models\User;

class SurveyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage-surveys');
    }

    public function view(User $user, Survey $survey): bool
    {
        return $user->can('manage-surveys');
    }

    public function create(User $user): bool
    {
        return $user->can('manage-surveys');
    }

    public function update(User $user, Survey $survey): bool
    {
        return $user->can('manage-surveys');
    }

    public function delete(User $user, Survey $survey): bool
    {
        return $user->can('manage-surveys');
    }
}
