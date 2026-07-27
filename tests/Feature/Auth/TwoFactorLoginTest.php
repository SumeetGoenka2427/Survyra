<?php

use App\Models\User;
use PragmaRX\Google2FA\Google2FA;

function enableTwoFactorFor(User $user): string
{
    $google2fa = new Google2FA();
    $secret = $google2fa->generateSecretKey();

    $user->forceFill([
        'two_factor_secret' => encrypt($secret),
        'two_factor_recovery_codes' => encrypt(json_encode(['RECOVERY-CODE-1234'])),
        'two_factor_enabled' => true,
    ])->save();

    return $secret;
}

test('logging in with a 2FA-enabled account does not authenticate immediately', function () {
    $user = User::factory()->create(['is_active' => true]);
    enableTwoFactorFor($user);

    $response = $this->post('/admin/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertGuest();
    $response->assertRedirect(route('admin.two-factor.challenge'));
});

test('a valid TOTP code completes login after password verification', function () {
    $user = User::factory()->create(['is_active' => true]);
    $secret = enableTwoFactorFor($user);

    $this->post('/admin/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);
    $this->assertGuest();

    $code = (new Google2FA())->getCurrentOtp($secret);

    $response = $this->post('/admin/two-factor-challenge', ['code' => $code]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('admin.dashboard', absolute: false));
});

test('an invalid code does not complete login', function () {
    $user = User::factory()->create(['is_active' => true]);
    enableTwoFactorFor($user);

    $this->post('/admin/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response = $this->post('/admin/two-factor-challenge', ['code' => '000000']);

    $this->assertGuest();
    $response->assertSessionHasErrors('code');
});

test('a recovery code completes login and is consumed', function () {
    $user = User::factory()->create(['is_active' => true]);
    enableTwoFactorFor($user);

    $this->post('/admin/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response = $this->post('/admin/two-factor-challenge', ['code' => 'RECOVERY-CODE-1234']);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('admin.dashboard', absolute: false));

    $remainingCodes = json_decode(decrypt($user->fresh()->two_factor_recovery_codes), true);
    expect($remainingCodes)->not->toContain('RECOVERY-CODE-1234');
});

test('the challenge page is inaccessible without a pending 2FA session', function () {
    $response = $this->get('/admin/two-factor-challenge');

    $response->assertRedirect(route('admin.login'));
});

test('logging in without 2FA enabled still authenticates immediately, unaffected by this change', function () {
    $user = User::factory()->create(['is_active' => true]);

    $response = $this->post('/admin/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('admin.dashboard', absolute: false));
});
