<?php

namespace App\Repositories\Contracts;

use App\Models\SurveyTemplate;
use Illuminate\Database\Eloquent\Collection;

interface SurveyTemplateRepositoryInterface
{
    public function allGroupedByIndustry(?string $search = null): Collection;

    public function find(int $id): SurveyTemplate;

    public function create(array $attributes): SurveyTemplate;

    public function update(SurveyTemplate $template, array $attributes): SurveyTemplate;

    public function delete(SurveyTemplate $template): void;
}
