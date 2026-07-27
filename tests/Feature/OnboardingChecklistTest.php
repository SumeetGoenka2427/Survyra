<?php

use App\Models\Client;
use App\Models\ClientUser;
use App\Models\Survey;
use App\Services\OnboardingService;

test('the portal dashboard renders without error - previously fatal on every load', function () {
    $client = Client::factory()->create();
    $user = ClientUser::factory()->create(['client_id' => $client->id, 'role' => 'owner']);

    $response = $this->actingAs($user, 'client')->get('/portal/dashboard');

    $response->assertOk();
});

test('checklist flags stay live instead of freezing at first creation', function () {
    $client = Client::factory()->create();
    $service = app(OnboardingService::class);

    $before = $service->checklistFor($client);
    expect($before->first_survey_created)->toBeFalse();

    Survey::query()->create([
        'client_id' => $client->id,
        'title' => 'Test Survey',
        'slug' => 'test-survey-'.uniqid(),
        'status' => 'draft',
    ]);

    $after = $service->checklistFor($client);
    expect($after->first_survey_created)->toBeTrue();
    expect($after->id)->toBe($before->id);
});

test('dismissing the checklist is preserved across recomputation', function () {
    $client = Client::factory()->create();
    $service = app(OnboardingService::class);

    $service->checklistFor($client);
    $service->dismiss($client);

    expect($service->checklistFor($client)->dismissed)->toBeTrue();
});
