<?php

namespace App\Policies;

use App\Models\SurveyTemplate;
use App\Models\User;

class SurveyTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage-surveys');
    }

    public function view(User $user, SurveyTemplate $template): bool
    {
        return $user->can('manage-surveys');
    }

    public function create(User $user): bool
    {
        return $user->can('manage-surveys');
    }

    public function update(User $user, SurveyTemplate $template): bool
    {
        return $user->can('manage-surveys');
    }

    public function delete(User $user, SurveyTemplate $template): bool
    {
        return $user->can('manage-surveys');
    }
}
