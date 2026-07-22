<?php

namespace App\Notifications;

use App\Models\Response as SurveyResponse;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NegativeFeedbackReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly SurveyResponse $response)
    {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['mail', 'database'];

        // Add Slack if the client has a webhook URL configured
        if ($notifiable->client && $notifiable->client->settings_service_slack_url ?? false) {
            $channels[] = 'slack_webhook';
        }

        return $channels;
    }

    public function toSlackWebhook(object $notifiable): array
    {
        $survey = $this->response->survey;
        $slackUrl = optional($notifiable->client)->getSlackWebhookUrl();

        if (! $slackUrl) {
            return [];
        }

        \Illuminate\Support\Facades\Http::post($slackUrl, [
            'text' => "⚠️ Negative feedback on *{$survey->title}* — Score: " . ($this->response->score ?? 'N/A'),
        ]);

        return [];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $survey = $this->response->survey;

        return (new MailMessage)
            ->subject("Negative feedback received - {$survey->title}")
            ->greeting('Heads up!')
            ->line("A respondent just completed \"{$survey->title}\" with negative feedback.")
            ->line('Score: '.($this->response->score ?? 'N/A'))
            ->action('View Dashboard', route('portal.dashboard'))
            ->line('Consider following up with your customer soon.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $survey = $this->response->survey;

        return [
            'response_id' => $this->response->id,
            'survey_id' => $survey->id,
            'survey_title' => $survey->title,
            'score' => $this->response->score,
            'url' => route('portal.dashboard'),
            'message' => "Negative feedback received for \"{$survey->title}\".",
        ];
    }
}
