<?php

namespace App\Services;

use App\Models\CampaignRecipient;
use App\Models\ShortLink;
use Illuminate\Support\Str;

class ShortLinkService
{
    public function createFor(string $targetUrl): ShortLink
    {
        return ShortLink::query()->create([
            'code' => $this->generateUniqueCode(),
            'target_url' => $targetUrl,
        ]);
    }

    /**
     * Resolves a short code to its target URL, registering the click and,
     * if this link was sent as part of a campaign, stamping that recipient's
     * clicked_at too. The recipient id is appended to the redirect URL as
     * `cr=` so the destination (the public survey page) can attribute the
     * response it's about to create back to this contact/campaign.
     *
     * @return array{url: string, recipient: ?CampaignRecipient}|null
     */
    public function resolveAndTrack(string $code): ?array
    {
        $shortLink = ShortLink::query()->where('code', $code)->first();

        if (! $shortLink) {
            return null;
        }

        $shortLink->registerClick();

        $recipient = CampaignRecipient::query()->where('short_link_id', $shortLink->id)->first();

        if ($recipient && ! $recipient->clicked_at) {
            $recipient->update(['clicked_at' => now()]);
        }

        $targetUrl = $shortLink->target_url;

        if ($recipient) {
            $separator = str_contains($targetUrl, '?') ? '&' : '?';
            $targetUrl .= "{$separator}cr={$recipient->id}";
        }

        return ['url' => $targetUrl, 'recipient' => $recipient];
    }

    private function generateUniqueCode(): string
    {
        do {
            $code = Str::lower(Str::random(6));
        } while (ShortLink::query()->where('code', $code)->exists());

        return $code;
    }
}
