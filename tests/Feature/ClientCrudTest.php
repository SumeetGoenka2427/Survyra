<?php

use App\Models\Client;
use App\Models\ClientUser;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('super_admin', 'web');
    Role::findOrCreate('survyra_admin', 'web');
});

function makeAdmin(string $role = 'survyra_admin'): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

test('a survyra admin can create a client and its owner portal login', function () {
    $admin = makeAdmin();

    $response = $this->actingAs($admin)->post('/admin/clients', [
        'company_name' => 'Test Cafe',
        'timezone' => 'Asia/Kolkata',
        'language' => 'en',
        'status' => 'trial',
        'owner_name' => 'Owner Name',
        'owner_email' => 'owner@testcafe.test',
        'owner_password' => 'password123',
    ]);

    $client = Client::query()->where('company_name', 'Test Cafe')->firstOrFail();

    $response->assertRedirect(route('admin.clients.edit', $client));
    $this->assertDatabaseHas('clients', ['company_name' => 'Test Cafe', 'created_by' => $admin->id]);
    $this->assertDatabaseHas('client_users', ['email' => 'owner@testcafe.test', 'client_id' => $client->id, 'role' => 'owner']);
});

test('a survyra admin can update a client', function () {
    $admin = makeAdmin();
    $client = Client::factory()->create(['company_name' => 'Old Name']);

    $response = $this->actingAs($admin)->put("/admin/clients/{$client->id}", [
        'company_name' => 'New Name',
        'timezone' => 'Asia/Kolkata',
        'language' => 'en',
        'status' => 'active',
    ]);

    $response->assertRedirect(route('admin.clients.edit', $client));
    $this->assertSame('New Name', $client->fresh()->company_name);
});

test('a survyra admin can toggle a client status', function () {
    $admin = makeAdmin();
    $client = Client::factory()->create(['status' => 'active']);

    $this->actingAs($admin)->patch("/admin/clients/{$client->id}/toggle-status");

    $this->assertSame('inactive', $client->fresh()->status);
});

test('only super admin can delete a client', function () {
    $survyraAdmin = makeAdmin('survyra_admin');
    $client = Client::factory()->create();

    $this->actingAs($survyraAdmin)->delete("/admin/clients/{$client->id}")->assertForbidden();
    $this->assertNotSoftDeleted($client);

    $superAdmin = makeAdmin('super_admin');
    $this->actingAs($superAdmin)->delete("/admin/clients/{$client->id}")->assertOk()->assertJsonStructure(['html']);
    $this->assertSoftDeleted($client);
});

test('a client portal owner can update their own company profile', function () {
    $client = Client::factory()->create(['website' => null]);
    $clientUser = ClientUser::factory()->create(['client_id' => $client->id]);

    $response = $this->actingAs($clientUser, 'client')->patch('/portal/company', [
        'website' => 'https://example.com',
    ]);

    $response->assertRedirect(route('portal.company.edit'));
    $this->assertSame('https://example.com', $client->fresh()->website);
});

test('the clients ajax data endpoint filters by search and status', function () {
    $admin = makeAdmin();
    Client::factory()->create(['company_name' => 'Alpha Cafe', 'status' => 'active']);
    Client::factory()->create(['company_name' => 'Beta Diner', 'status' => 'trial']);

    $result = $this->actingAs($admin)->getJson('/admin/clients/data?search=Alpha');

    $result->assertOk();
    expect($result->json('html'))->toContain('Alpha Cafe');
    expect($result->json('html'))->not->toContain('Beta Diner');

    $result = $this->actingAs($admin)->getJson('/admin/clients/data?status=trial');
    expect($result->json('html'))->toContain('Beta Diner');
    expect($result->json('html'))->not->toContain('Alpha Cafe');
});

test('toggling a clients status through ajax returns the refreshed fragment', function () {
    $admin = makeAdmin();
    $client = Client::factory()->create(['status' => 'active']);

    $result = $this->actingAs($admin)->patchJson("/admin/clients/{$client->id}/toggle-status");

    $result->assertOk();
    expect($result->json('html'))->toContain('Inactive');
    $this->assertSame('inactive', $client->fresh()->status);
});
