<?php

namespace App\Services;

use Illuminate\Foundation\Auth\User as Authenticatable;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;

class TwoFactorAuthService
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Generate a new TOTP secret for a user.
     */
    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey(32);
    }

    /**
     * Get the QR code URL for setting up in an authenticator app.
     */
    public function qrCodeUrl(string $email, string $company, string $secret): string
    {
        return $this->google2fa->getQRCodeUrl($company, $email, $secret);
    }

    /**
     * Verify a one-time password against the secret.
     */
    public function verify(string $secret, string $oneTimePassword): bool
    {
        return $this->google2fa->verifyKey($secret, $oneTimePassword, 2); // 2 windows = 30s grace
    }

    /**
     * Enable TOTP for a user.
     *
     * @return array{recovery_codes: array<int, string>}
     */
    public function enable(Authenticatable $user, string $secret, string $oneTimePassword): array
    {
        if (! $this->verify($secret, $oneTimePassword)) {
            throw new \InvalidArgumentException('Invalid verification code. Please try again.');
        }

        $recoveryCodes = $this->generateRecoveryCodes();

        $user->forceFill([
            'two_factor_secret' => encrypt($secret),
            'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes)),
            'two_factor_enabled' => true,
        ])->save();

        return ['recovery_codes' => $recoveryCodes];
    }

    /**
     * Disable TOTP for a user.
     */
    public function disable(Authenticatable $user): void
    {
        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_enabled' => false,
        ])->save();
    }

    /**
     * Verify a TOTP code for a user who has 2FA enabled.
     */
    public function verifyForUser(Authenticatable $user, string $code): bool
    {
        if (! $user->two_factor_enabled || ! $user->two_factor_secret) {
            return true;
        }

        $secret = decrypt($user->two_factor_secret);

        // Check if it's a recovery code
        if ($user->two_factor_recovery_codes) {
            $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);

            $found = collect($recoveryCodes)->search(function ($recoveryCode) use ($code) {
                return hash_equals($recoveryCode, $code);
            });

            if ($found !== false) {
                // Remove used recovery code
                unset($recoveryCodes[$found]);
                $user->forceFill([
                    'two_factor_recovery_codes' => encrypt(json_encode(array_values($recoveryCodes))),
                ])->save();

                return true;
            }
        }

        return $this->verify($secret, $code);
    }

    /**
     * Generate a set of recovery codes.
     *
     * @return array<int, string>
     */
    protected function generateRecoveryCodes(): array
    {
        $codes = [];

        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(
                implode('-', [
                    substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'), 0, 4),
                    substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'), 0, 4),
                    substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'), 0, 4),
                ])
            );
        }

        return $codes;
    }
}