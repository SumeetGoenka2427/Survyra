<?php

namespace App\Services\Campaigns;

class SendResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $providerMessageId = null,
        public readonly ?string $errorMessage = null,
    ) {
    }

    public static function success(?string $providerMessageId = null): self
    {
        return new self(true, $providerMessageId);
    }

    public static function failure(string $errorMessage): self
    {
        return new self(false, null, $errorMessage);
    }
}
