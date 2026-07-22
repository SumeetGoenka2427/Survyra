<?php

use App\Models\ClientUser;
use App\Models\User;
use Spatie\Permission\Models\Role;

test('a client portal user cannot access admin routes', function () {
    $clientUser = ClientUser::factory()->create();

    $response = $this->actingAs($clientUser, 'client')->get('/admin/dashboard');

    $response->assertRedirect(route('admin.login'));
});

test('an internal staff user cannot access portal routes', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'web')->get('/portal/dashboard');

    $response->assertRedirect(route('portal.login'));
});

test('a survyra admin without a role cannot manage clients', function () {
    Role::findOrCreate('survyra_admin', 'web');

    $user = User::factory()->create();

    $response = $this->actingAs($user, 'web')->get('/admin/clients');

    $response->assertForbidden();
});

test('a survyra admin with the role can manage clients', function () {
    Role::findOrCreate('survyra_admin', 'web');

    $user = User::factory()->create();
    $user->assignRole('survyra_admin');

    $response = $this->actingAs($user, 'web')->get('/admin/clients');

    $response->assertOk();
});
