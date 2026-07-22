<?php

namespace App\Services;

use App\Models\Response;
use App\Models\ReviewClick;

class ReviewClickService
{
    private const CHANNELS = ['google_review', 'facebook', 'website', 'complaint_form', 'support_call', 'whatsapp'];

    /**
     * Logs the click and resolves where it should redirect to, or null if this
     * channel isn't actually configured for the response's client (a direct
     * hit on a stale/guessed URL rather than a real button click).
     */
    public function logAndResolve(Response $response, string $channel): ?string
    {
        if (! in_array($channel, self::CHANNELS, true)) {
            return null;
        }

        $target = $this->targetUrl($response, $channel);

        if (! $target) {
            return null;
        }

        ReviewClick::query()->create([
            'response_id' => $response->id,
            'client_id' => $response->client_id,
            'channel' => $channel,
            'clicked_at' => now(),
        ]);

        return $target;
    }

    private function targetUrl(Response $response, string $channel): ?string
    {
        $client = $response->client;

        return match ($channel) {
            'google_review' => $client->google_review_url ?: null,
            'facebook' => $client->facebook_url ?: null,
            'website' => $client->website ?: null,
            'complaint_form' => $client->email ? "mailto:{$client->email}" : null,
            'support_call' => $client->support_number ? 'tel:'.$client->support_number : null,
            'whatsapp' => $client->whatsapp_number ? 'https://wa.me/'.preg_replace('/\D/', '', $client->whatsapp_number) : null,
            default => null,
        };
    }
}
