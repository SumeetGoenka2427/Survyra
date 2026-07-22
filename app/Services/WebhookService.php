<?php

namespace App\Services;

use App\Jobs\DeliverWebhookJob;
use App\Models\Client;
use App\Models\Response;
use App\Models\Webhook;

class WebhookService
{
    public function fire(string $event, Response $response): void
    {
        Webhook::where('client_id', $response->client_id)
            ->where('is_active', true)
            ->whereJsonContains('events', $event)
            ->get()
            ->each(function (Webhook $webhook) use ($event, $response) {
                DeliverWebhookJob::dispatch(
                    $webhook->id,
                    $event,
                    [
                        'response_uuid' => $response->uuid,
                        'survey_id' => $response->survey_id,
                        'status' => $response->status,
                        'sentiment' => $response->sentiment,
                        'score' => $response->score,
                        'started_at' => $response->started_at?->toIso8601String(),
                        'completed_at' => $response->completed_at?->toIso8601String(),
                    ]
                );
            });
    }

    public function allForClient(Client $client)
    {
        return Webhook::where('client_id', $client->id)->latest()->get();
    }

    public function create(Client $client, array $data): Webhook
    {
        return Webhook::create(['client_id' => $client->id, ...$data]);
    }

    public function delete(Webhook $webhook): void
    {
        $webhook->delete();
    }
}
