<?php

namespace App\Jobs;

use App\Models\Campaign;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Fans out one SendCampaignMessageJob per pending recipient, so a campaign of
 * hundreds of contacts can't hang an HTTP request and a slow/failing provider
 * call for one recipient can't block the rest.
 */
class SendCampaignJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly int $campaignId)
    {
    }

    public function handle(): void
    {
        $campaign = Campaign::query()->findOrFail($this->campaignId);

        $campaign->recipients()->where('status', 'pending')->pluck('id')->each(
            fn (int $recipientId) => SendCampaignMessageJob::dispatch($recipientId)
        );
    }
}
