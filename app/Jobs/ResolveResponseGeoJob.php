<?php

namespace App\Jobs;

use App\Models\Response;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ResolveResponseGeoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function __construct(
        private readonly int $responseId,
        private readonly string $ip,
    ) {}

    public function handle(): void
    {
        // Skip private/loopback IPs
        if (in_array($this->ip, ['127.0.0.1', '::1']) || str_starts_with($this->ip, '192.168.') || str_starts_with($this->ip, '10.')) {
            return;
        }

        try {
            $geo = Http::timeout(5)->get("http://ip-api.com/json/{$this->ip}?fields=country,city,status")->json();

            if (($geo['status'] ?? '') === 'success') {
                Response::where('id', $this->responseId)->update([
                    'country' => $geo['country'] ?? null,
                    'city' => $geo['city'] ?? null,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Geo lookup failed', ['ip' => $this->ip, 'error' => $e->getMessage()]);
        }
    }
}
