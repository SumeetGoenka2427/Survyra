<?php

namespace App\Repositories;

use App\Models\SurveyTemplate;
use App\Repositories\Contracts\SurveyTemplateRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class SurveyTemplateRepository implements SurveyTemplateRepositoryInterface
{
    public function allGroupedByIndustry(?string $search = null): Collection
    {
        return SurveyTemplate::query()
            ->withCount(['questions', 'surveys'])
            ->with('createdBy')
            ->when($search, fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('industry_category')
            ->orderBy('name')
            ->get()
            ->groupBy('industry_category');
    }

    public function find(int $id): SurveyTemplate
    {
        return SurveyTemplate::query()->with('questions.questionType')->findOrFail($id);
    }

    public function create(array $attributes): SurveyTemplate
    {
        return SurveyTemplate::query()->create($attributes);
    }

    public function update(SurveyTemplate $template, array $attributes): SurveyTemplate
    {
        $template->update($attributes);

        return $template;
    }

    public function delete(SurveyTemplate $template): void
    {
        $template->delete();
    }
}
