<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    public function get(string $key, mixed $default = null, ?int $clientId = null, string $group = 'general'): mixed
    {
        return Cache::rememberForever(
            $this->cacheKey($clientId, $group, $key),
            fn () => Setting::query()
                ->where('client_id', $clientId)
                ->where('group', $group)
                ->where('key', $key)
                ->value('value') ?? $default
        );
    }

    public function set(string $key, mixed $value, ?int $clientId = null, string $group = 'general'): void
    {
        Setting::query()->updateOrCreate(
            ['client_id' => $clientId, 'group' => $group, 'key' => $key],
            ['value' => $value]
        );

        Cache::forget($this->cacheKey($clientId, $group, $key));
    }

    private function cacheKey(?int $clientId, string $group, string $key): string
    {
        return sprintf('settings.%s.%s.%s', $clientId ?? 'global', $group, $key);
    }
}
