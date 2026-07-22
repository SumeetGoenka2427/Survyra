<?php

namespace App\Notifications;

use App\Models\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CampaignSendCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Campaign $campaign)
    {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $stats = collect($this->campaign->stats ?? []);

        return (new MailMessage)
            ->subject("Campaign completed - {$this->campaign->name}")
            ->greeting('Your campaign has finished sending.')
            ->line("\"{$this->campaign->name}\" has finished sending to {$this->campaign->client->company_name}.")
            ->line('Sent: '.($stats->get('sent', 0) + $stats->get('delivered', 0)))
            ->line('Failed: '.$stats->get('failed', 0))
            ->action('View Campaign', route('admin.campaigns.show', $this->campaign));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'campaign_id' => $this->campaign->id,
            'campaign_name' => $this->campaign->name,
            'stats' => $this->campaign->stats,
            'url' => route('admin.campaigns.show', $this->campaign),
            'message' => "Campaign \"{$this->campaign->name}\" has finished sending.",
        ];
    }
}
