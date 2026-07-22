<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ClientUser;
use App\Repositories\Contracts\ClientRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ClientService
{
    public function __construct(private readonly ClientRepositoryInterface $clients)
    {
    }

    public function paginate(int $perPage = 15, ?string $search = null, ?string $status = null): LengthAwarePaginator
    {
        return $this->clients->paginate($perPage, $search, $status);
    }

    public function all(): Collection
    {
        return $this->clients->all();
    }

    /**
     * @return array<string, int>
     */
    public function stats(): array
    {
        return [
            'total' => Client::query()->count(),
            'active' => Client::query()->where('status', 'active')->count(),
            'trial' => Client::query()->where('status', 'trial')->count(),
            'inactive' => Client::query()->where('status', 'inactive')->count(),
        ];
    }

    public function find(int $id): Client
    {
        return $this->clients->find($id);
    }

    /**
     * Creates the client company plus its first (owner) portal login in one transaction.
     */
    public function create(array $data, int $createdByUserId): Client
    {
        return DB::transaction(function () use ($data, $createdByUserId) {
            if (! empty($data['logo'])) {
                $data['logo_path'] = $this->storeLogo($data['logo']);
            }

            $client = $this->clients->create([
                ...collect($data)->except(['logo', 'owner_name', 'owner_email', 'owner_password'])->toArray(),
                'created_by' => $createdByUserId,
            ]);

            ClientUser::query()->create([
                'client_id' => $client->id,
                'name' => $data['owner_name'],
                'email' => $data['owner_email'],
                'password' => Hash::make($data['owner_password']),
                'role' => 'owner',
            ]);

            return $client;
        });
    }

    public function update(Client $client, array $data): Client
    {
        if (! empty($data['logo'])) {
            $data['logo_path'] = $this->storeLogo($data['logo'], $client->logo_path);
        }

        return $this->clients->update($client, collect($data)->except('logo')->toArray());
    }

    public function toggleStatus(Client $client): Client
    {
        return $this->clients->update($client, [
            'status' => $client->status === 'active' ? 'inactive' : 'active',
        ]);
    }

    public function delete(Client $client): void
    {
        $this->clients->delete($client);
    }

    private function storeLogo(UploadedFile $logo, ?string $previousPath = null): string
    {
        if ($previousPath) {
            Storage::disk('public')->delete($previousPath);
        }

        $manager = new ImageManager(new Driver);
        $path = 'client-logos/'.uniqid('logo_', true).'.webp';

        $encoded = $manager->read($logo->getRealPath())
            ->scaleDown(width: 512)
            ->toWebp(quality: 85);

        Storage::disk('public')->put($path, (string) $encoded);

        return $path;
    }
}
