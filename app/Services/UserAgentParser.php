<?php

namespace App\Services;

/**
 * Coarse device/browser detection from a User-Agent string. Not a full parser
 * library on purpose - this only needs to be accurate enough for a dashboard
 * stat, not for analytics-grade device fingerprinting.
 */
class UserAgentParser
{
    public function device(?string $userAgent): string
    {
        if (! $userAgent) {
            return 'unknown';
        }

        if (preg_match('/iPad|Tablet/i', $userAgent)) {
            return 'tablet';
        }

        if (preg_match('/Mobi|Android|iPhone/i', $userAgent)) {
            return 'mobile';
        }

        return 'desktop';
    }

    public function browser(?string $userAgent): string
    {
        if (! $userAgent) {
            return 'unknown';
        }

        return match (true) {
            (bool) preg_match('/Edg\//i', $userAgent) => 'Edge',
            (bool) preg_match('/Chrome\//i', $userAgent) && ! preg_match('/Chromium/i', $userAgent) => 'Chrome',
            (bool) preg_match('/CriOS/i', $userAgent) => 'Chrome',
            (bool) preg_match('/FxiOS|Firefox\//i', $userAgent) => 'Firefox',
            (bool) preg_match('/Version\/.*Safari/i', $userAgent) => 'Safari',
            default => 'Other',
        };
    }
}
