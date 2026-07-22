<?php

namespace App\Jobs;

use App\Models\CampaignRecipient;
use App\Services\CampaignProviderRegistry;
use App\Services\CampaignService;
use App\Services\Campaigns\EmailProvider;
use App\Services\ShortLinkService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendCampaignMessageJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public readonly int $recipientId)
    {
    }

    public function handle(
        CampaignProviderRegistry $providers,
        ShortLinkService $shortLinks,
        EmailProvider $emailProvider,
        CampaignService $campaigns,
    ): void {
        $recipient = CampaignRecipient::query()->with(['campaign.survey', 'contact'])->findOrFail($this->recipientId);
        $campaign = $recipient->campaign;
        $survey = $campaign->survey;

        $shortLink = $shortLinks->createFor(url("/s/{$survey->slug}"));
        $recipient->update(['short_link_id' => $shortLink->id]);

        $message = str_replace(
            ['{name}', '{link}'],
            [$recipient->contact->name, url("/l/{$shortLink->code}")],
            (string) $campaign->message_template
        );

        $result = $campaign->type === 'email'
            ? $emailProvider->send($recipient, $campaign->name, $message)
            : $providers->resolve($campaign->type)->send($recipient, $message);

        $recipient->update($result->success ? [
            'status' => 'sent',
            'sent_at' => now(),
            'provider_message_id' => $result->providerMessageId,
        ] : [
            'status' => 'failed',
            'error_message' => $result->errorMessage,
        ]);

        $campaigns->refreshStats($campaign);
    }
}
