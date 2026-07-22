<?php

namespace App\Services;

use App\Contracts\MessageProviderContract;
use InvalidArgumentException;

class CampaignProviderRegistry
{
    public function resolve(string $channel): MessageProviderContract
    {
        $class = config("campaign_providers.{$channel}");

        if (! $class) {
            throw new InvalidArgumentException("No message provider registered for channel [{$channel}].");
        }

        return app($class);
    }
}
