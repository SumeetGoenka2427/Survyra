<?php

namespace App\Services\Campaigns;

use App\Contracts\MessageProviderContract;
use App\Models\CampaignRecipient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Default WhatsApp provider: logs instead of calling a real API. There is no
 * Meta-approved message template or provider account in this environment -
 * swap this binding in config/campaign_providers.php once that exists.
 */
class LogWhatsAppProvider implements MessageProviderContract
{
    public function send(CampaignRecipient $recipient, string $message): SendResult
    {
        $messageId = (string) Str::uuid();

        Log::info('[WhatsApp:log-provider] would send message', [
            'recipient_id' => $recipient->id,
            'contact_id' => $recipient->contact_id,
            'message' => $message,
            'provider_message_id' => $messageId,
        ]);

        return SendResult::success($messageId);
    }
}
