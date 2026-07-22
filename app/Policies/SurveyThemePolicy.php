<?php

namespace App\Policies;

use App\Models\SurveyTheme;
use App\Models\User;

class SurveyThemePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage-surveys');
    }

    public function view(User $user, SurveyTheme $theme): bool
    {
        return $user->can('manage-surveys');
    }

    public function create(User $user): bool
    {
        return $user->can('manage-surveys');
    }

    public function update(User $user, SurveyTheme $theme): bool
    {
        return $user->can('manage-surveys');
    }

    public function delete(User $user, SurveyTheme $theme): bool
    {
        return $user->can('manage-surveys');
    }
}
