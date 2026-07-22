<?php

use App\Models\ClientUser;

test('portal login screen can be rendered', function () {
    $response = $this->get('/portal/login');

    $response->assertStatus(200);
});

test('client users can authenticate using the portal login screen', function () {
    $clientUser = ClientUser::factory()->create(['is_active' => true]);

    $response = $this->post('/portal/login', [
        'email' => $clientUser->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated('client');
    $response->assertRedirect(route('portal.dashboard', absolute: false));
});

test('client users can not authenticate with invalid password', function () {
    $clientUser = ClientUser::factory()->create();

    $this->post('/portal/login', [
        'email' => $clientUser->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest('client');
});

test('deactivated client users can not authenticate', function () {
    $clientUser = ClientUser::factory()->create(['is_active' => false]);

    $this->post('/portal/login', [
        'email' => $clientUser->email,
        'password' => 'password',
    ]);

    $this->assertGuest('client');
});

test('client users can logout', function () {
    $clientUser = ClientUser::factory()->create();

    $response = $this->actingAs($clientUser, 'client')->post('/portal/logout');

    $this->assertGuest('client');
    $response->assertRedirect(route('portal.login'));
});
