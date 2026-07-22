<?php

namespace App\Repositories\Contracts;

use App\Models\Survey;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface SurveyRepositoryInterface
{
    public function paginate(int $perPage = 15, ?int $clientId = null, ?string $status = null): LengthAwarePaginator;

    public function find(int $id): Survey;

    public function create(array $attributes): Survey;

    public function update(Survey $survey, array $attributes): Survey;

    public function delete(Survey $survey): void;
}
