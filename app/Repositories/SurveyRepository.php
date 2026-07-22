<?php

namespace App\Repositories;

use App\Models\Survey;
use App\Repositories\Contracts\SurveyRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SurveyRepository implements SurveyRepositoryInterface
{
    public function paginate(int $perPage = 15, ?int $clientId = null, ?string $status = null): LengthAwarePaginator
    {
        return Survey::query()
            ->with('client', 'theme')
            ->withCount(['questions', 'responses'])
            ->when($clientId, fn ($query) => $query->where('client_id', $clientId))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function find(int $id): Survey
    {
        return Survey::query()
            ->with(['client', 'theme', 'template', 'questions.questionType', 'logicRules', 'thankyouRules'])
            ->findOrFail($id);
    }

    public function create(array $attributes): Survey
    {
        return Survey::query()->create($attributes);
    }

    public function update(Survey $survey, array $attributes): Survey
    {
        $survey->update($attributes);

        return $survey;
    }

    public function delete(Survey $survey): void
    {
        $survey->delete();
    }
}
