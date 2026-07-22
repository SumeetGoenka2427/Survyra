<?php

namespace App\Repositories;

use App\Models\Client;
use App\Repositories\Contracts\ClientRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ClientRepository implements ClientRepositoryInterface
{
    public function paginate(int $perPage = 15, ?string $search = null, ?string $status = null): LengthAwarePaginator
    {
        return Client::query()
            ->with('subscriptionPlan')
            ->withCount(['surveys', 'campaigns'])
            ->when($search, fn ($query) => $query->where('company_name', 'like', "%{$search}%"))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function all(): Collection
    {
        return Client::query()->orderBy('company_name')->get();
    }

    public function find(int $id): Client
    {
        return Client::query()->with('subscriptionPlan', 'clientUsers')->findOrFail($id);
    }

    public function create(array $attributes): Client
    {
        return Client::query()->create($attributes);
    }

    public function update(Client $client, array $attributes): Client
    {
        $client->update($attributes);

        return $client;
    }

    public function delete(Client $client): void
    {
        $client->delete();
    }
}
