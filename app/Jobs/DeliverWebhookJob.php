<?php

namespace App\Jobs;

use App\Models\Webhook;
use App\Models\WebhookDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class DeliverWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        private readonly int $webhookId,
        private readonly string $event,
        private readonly array $data,
    ) {}

    public function handle(): void
    {
        $webhook = Webhook::find($this->webhookId);
        if (! $webhook || ! $webhook->is_active) {
            return;
        }

        $payload = [
            'event' => $this->event,
            'timestamp' => now()->toIso8601String(),
            'data' => $this->data,
        ];

        $signature = hash_hmac('sha256', json_encode($payload), $webhook->secret ?? '');

        $delivery = WebhookDelivery::create([
            'webhook_id' => $webhook->id,
            'event' => $this->event,
            'payload' => $payload,
            'created_at' => now(),
        ]);

        try {
            $response = Http::withHeaders([
                'X-Survyra-Signature' => $signature,
                'Content-Type' => 'application/json',
            ])->timeout(10)->post($webhook->url, $payload);

            $delivery->update([
                'response_status' => $response->status(),
                'response_body' => substr($response->body(), 0, 1000),
                'delivered_at' => now(),
            ]);

            $webhook->update(['last_triggered_at' => now(), 'failure_count' => 0]);
        } catch (\Throwable $e) {
            $delivery->update(['failed_at' => now(), 'response_body' => $e->getMessage()]);

            $newCount = $webhook->failure_count + 1;
            $webhook->update(['failure_count' => $newCount]);

            if ($newCount >= 10) {
                $webhook->update(['is_active' => false]);
                return;
            }

            throw $e; // triggers retry
        }
    }
}
