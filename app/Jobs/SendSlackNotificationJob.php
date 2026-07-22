<?php

namespace App\Jobs;

use App\Models\SlackIntegration;
use App\Models\Response;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SendSlackNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        private readonly int $clientId,
        private readonly string $event,
        private readonly array $data,
    ) {}

    public function handle(): void
    {
        $integrations = SlackIntegration::where('client_id', $this->clientId)
            ->where('is_active', true)
            ->whereJsonContains('events', $this->event)
            ->get();

        foreach ($integrations as $integration) {
            $message = $this->buildMessage($this->event, $this->data);

            try {
                Http::timeout(10)->post($integration->webhook_url, $message);
            } catch (\Throwable $e) {
                // Log failure but don't retry individual webhook failures
                \Illuminate\Support\Facades\Log::warning('Slack notification failed', [
                    'client_id' => $this->clientId,
                    'event' => $this->event,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    protected function buildMessage(string $event, array $data): array
    {
        $color = match ($event) {
            'negative_feedback' => '#dc3545',
            'response_completed' => '#198754',
            'survey_published' => '#0d6efd',
            default => '#6c757d',
        };

        $blocks = [
            [
                'type' => 'header',
                'text' => ['type' => 'plain_text', 'text' => $this->eventTitle($event)],
            ],
            [
                'type' => 'section',
                'fields' => [],
            ],
        ];

        foreach ($data as $key => $value) {
            $blocks[1]['fields'][] = [
                'type' => 'mrkdwn',
                'text' => "*{$key}:*\n{$value}",
            ];
        }

        return [
            'attachments' => [
                [
                    'color' => $color,
                    'blocks' => $blocks,
                ],
            ],
        ];
    }

    protected function eventTitle(string $event): string
    {
        return match ($event) {
            'negative_feedback' => '🚨 Negative Feedback Received',
            'response_completed' => '✅ New Response Completed',
            'survey_published' => '📢 Survey Published',
            default => '🔔 Survyra Notification',
        };
    }
}