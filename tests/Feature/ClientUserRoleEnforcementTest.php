<?php

use App\Models\Client;
use App\Models\ClientUser;

test('a viewer cannot update the company profile', function () {
    $client = Client::factory()->create();
    $viewer = ClientUser::factory()->create(['client_id' => $client->id, 'role' => 'viewer']);

    $response = $this->actingAs($viewer, 'client')->patch('/portal/company', [
        'company_name' => 'New Name',
    ]);

    $response->assertForbidden();
});

test('an editor can update the company profile', function () {
    $client = Client::factory()->create(['phone' => '555-0000']);
    $editor = ClientUser::factory()->create(['client_id' => $client->id, 'role' => 'editor']);

    $response = $this->actingAs($editor, 'client')->patch('/portal/company', [
        'phone' => '555-1234',
    ]);

    $response->assertSessionDoesntHaveErrors();
    expect($client->fresh()->phone)->toBe('555-1234');
});

test('a viewer cannot create an API key', function () {
    $client = Client::factory()->create();
    $viewer = ClientUser::factory()->create(['client_id' => $client->id, 'role' => 'viewer']);

    $response = $this->actingAs($viewer, 'client')->post('/portal/integrations/api-keys', [
        'name' => 'My Key',
    ]);

    $response->assertForbidden();
});

test('a viewer cannot create a webhook', function () {
    $client = Client::factory()->create();
    $viewer = ClientUser::factory()->create(['client_id' => $client->id, 'role' => 'viewer']);

    $response = $this->actingAs($viewer, 'client')->post('/portal/integrations/webhooks', [
        'url' => 'https://example.com/hook',
        'events' => ['response.completed'],
    ]);

    $response->assertForbidden();
});

test('a viewer cannot configure Slack', function () {
    $client = Client::factory()->create();
    $viewer = ClientUser::factory()->create(['client_id' => $client->id, 'role' => 'viewer']);

    $response = $this->actingAs($viewer, 'client')->post('/portal/integrations/slack', [
        'webhook_url' => 'https://hooks.slack.com/services/x',
        'events' => ['negative_feedback'],
    ]);

    $response->assertForbidden();
});

test('a viewer cannot create a scheduled report', function () {
    $client = Client::factory()->create();
    $viewer = ClientUser::factory()->create(['client_id' => $client->id, 'role' => 'viewer']);

    $response = $this->actingAs($viewer, 'client')->post('/portal/analytics/reports', [
        'name' => 'Weekly Report',
        'frequency' => 'weekly',
        'format' => 'pdf',
    ]);

    $response->assertForbidden();
});

test('a viewer can still view read-only portal pages', function () {
    $client = Client::factory()->create();
    $viewer = ClientUser::factory()->create(['client_id' => $client->id, 'role' => 'viewer']);

    $this->actingAs($viewer, 'client')->get('/portal/dashboard')->assertOk();
    $this->actingAs($viewer, 'client')->get('/portal/integrations/api-keys')->assertOk();
    $this->actingAs($viewer, 'client')->get('/portal/integrations/webhooks')->assertOk();
});

test('an owner is unaffected by the editor gate', function () {
    $client = Client::factory()->create();
    $owner = ClientUser::factory()->create(['client_id' => $client->id, 'role' => 'owner']);

    $response = $this->actingAs($owner, 'client')->post('/portal/integrations/api-keys', [
        'name' => 'Owner Key',
    ]);

    $response->assertSessionDoesntHaveErrors();
    $response->assertStatus(302);
});
