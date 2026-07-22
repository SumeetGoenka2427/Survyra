<?php

namespace App\Contracts;

use App\Models\CampaignRecipient;
use App\Services\Campaigns\SendResult;

/**
 * Shared by SMS and WhatsApp providers - both send one message to one
 * recipient and report back success/failure, so one contract covers both.
 * Resolved via config/campaign_providers.php, same pattern as
 * config/question_types.php in Phase 2.
 */
interface MessageProviderContract
{
    public function send(CampaignRecipient $recipient, string $message): SendResult;
}
