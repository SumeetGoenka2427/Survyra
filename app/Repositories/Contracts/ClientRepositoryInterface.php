<?php

namespace App\Repositories\Contracts;

use App\Models\Client;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ClientRepositoryInterface
{
    public function paginate(int $perPage = 15, ?string $search = null, ?string $status = null): LengthAwarePaginator;

    public function all(): Collection;

    public function find(int $id): Client;

    public function create(array $attributes): Client;

    public function update(Client $client, array $attributes): Client;

    public function delete(Client $client): void;
}
