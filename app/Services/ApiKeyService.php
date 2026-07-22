<?php

namespace App\Services;

use App\Models\ApiKey;
use App\Models\Client;
use Illuminate\Support\Str;

class ApiKeyService
{
    public function allForClient(Client $client)
    {
        return ApiKey::where('client_id', $client->id)->latest()->get();
    }

    /** Returns the plain-text key (shown once). */
    public function create(Client $client, string $name): array
    {
        $plain = 'sk_' . Str::random(40);

        $key = ApiKey::create([
            'client_id' => $client->id,
            'name' => $name,
            'key_hash' => hash('sha256', $plain),
            'is_active' => true,
        ]);

        return ['key' => $key, 'plain' => $plain];
    }

    public function revoke(ApiKey $key): void
    {
        $key->update(['is_active' => false]);
    }

    public function delete(ApiKey $key): void
    {
        $key->delete();
    }

    public function findByPlain(string $plain): ?ApiKey
    {
        return ApiKey::where('key_hash', hash('sha256', $plain))
            ->where('is_active', true)
            ->first();
    }
}
