<?php

use App\Models\Campaign;
use App\Models\Client;
use App\Models\ClientUser;
use App\Models\Contact;
use App\Models\QuestionType;
use App\Models\SurveyTemplate;
use App\Models\User;
use App\Services\SurveyService;
use Database\Seeders\QuestionTypeSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(QuestionTypeSeeder::class);
    Role::findOrCreate('survyra_admin', 'web');
    Permission::findOrCreate('send-campaigns', 'web');
    Permission::findOrCreate('manage-surveys', 'web');
});

function makeCampaignManagerAdmin(): User
{
    $user = User::factory()->create();
    $user->assignRole('survyra_admin');
    $user->givePermissionTo(['send-campaigns', 'manage-surveys']);

    return $user;
}

function publishedSurveyForCampaigns(Client $client, User $admin): \App\Models\Survey
{
    $template = SurveyTemplate::factory()->create();
    $nps = QuestionType::query()->where('key', 'nps')->firstOrFail();
    $template->questions()->create(['question_type_id' => $nps->id, 'question_text' => 'How likely to recommend?', 'order' => 0]);

    $survey = app(SurveyService::class)->createFromTemplate($client, $template, 'Campaign Survey', $admin->id);
    app(SurveyService::class)->publish($survey);

    return $survey;
}

test('building a campaign excludes contacts without consent', function () {
    $admin = makeCampaignManagerAdmin();
    $client = Client::factory()->create();
    $survey = publishedSurveyForCampaigns($client, $admin);

    Contact::factory()->for($client)->create(['consent' => true]);
    Contact::factory()->for($client)->withoutConsent()->create();

    $response = $this->actingAs($admin)->post('/admin/campaigns', [
        'client_id' => $client->id,
        'survey_id' => $survey->id,
        'name' => 'Test Campaign',
        'type' => 'sms',
        'message_template' => 'Hi {name}, feedback please: {link}',
    ]);

    $response->assertRedirect();
    $campaign = Campaign::query()->where('name', 'Test Campaign')->firstOrFail();

    expect($campaign->recipients)->toHaveCount(1);
});

test('sending a campaign marks recipients sent via the log provider', function () {
    $admin = makeCampaignManagerAdmin();
    $client = Client::factory()->create();
    $survey = publishedSurveyForCampaigns($client, $admin);

    Contact::factory()->for($client)->create(['consent' => true]);
    Contact::factory()->for($client)->create(['consent' => true]);

    $this->actingAs($admin)->post('/admin/campaigns', [
        'client_id' => $client->id,
        'survey_id' => $survey->id,
        'name' => 'SMS Blast',
        'type' => 'sms',
        'message_template' => 'Hi {name}: {link}',
    ]);
    $campaign = Campaign::query()->where('name', 'SMS Blast')->firstOrFail();

    $this->actingAs($admin)->post("/admin/campaigns/{$campaign->id}/send")->assertRedirect();

    $campaign->refresh();
    expect($campaign->recipients()->where('status', 'sent')->count())->toBe(2);
    expect($campaign->recipients()->whereNotNull('short_link_id')->count())->toBe(2);
});

test('an email recipient without an email address fails without affecting others', function () {
    $admin = makeCampaignManagerAdmin();
    $client = Client::factory()->create();
    $survey = publishedSurveyForCampaigns($client, $admin);

    Contact::factory()->for($client)->create(['consent' => true, 'email' => 'has-email@example.com']);
    Contact::factory()->for($client)->create(['consent' => true, 'email' => null]);

    $this->actingAs($admin)->post('/admin/campaigns', [
        'client_id' => $client->id,
        'survey_id' => $survey->id,
        'name' => 'Email Blast',
        'type' => 'email',
        'message_template' => 'Hi {name}: {link}',
    ]);
    $campaign = Campaign::query()->where('name', 'Email Blast')->firstOrFail();

    $this->actingAs($admin)->post("/admin/campaigns/{$campaign->id}/send");

    $campaign->refresh();
    expect($campaign->recipients()->where('status', 'sent')->count())->toBe(1);
    expect($campaign->recipients()->where('status', 'failed')->count())->toBe(1);
    expect($campaign->recipients()->where('status', 'failed')->first()->error_message)->toContain('no email address');
});

test('retrying only re-attempts failed recipients', function () {
    $admin = makeCampaignManagerAdmin();
    $client = Client::factory()->create();
    $survey = publishedSurveyForCampaigns($client, $admin);

    Contact::factory()->for($client)->create(['consent' => true, 'email' => 'ok@example.com']);
    Contact::factory()->for($client)->create(['consent' => true, 'email' => null]);

    $this->actingAs($admin)->post('/admin/campaigns', [
        'client_id' => $client->id,
        'survey_id' => $survey->id,
        'name' => 'Retry Blast',
        'type' => 'email',
        'message_template' => 'Hi {name}: {link}',
    ]);
    $campaign = Campaign::query()->where('name', 'Retry Blast')->firstOrFail();
    $this->actingAs($admin)->post("/admin/campaigns/{$campaign->id}/send");

    $sentRecipient = $campaign->recipients()->where('status', 'sent')->firstOrFail();
    $firstSentAt = $sentRecipient->sent_at;

    $this->actingAs($admin)->post("/admin/campaigns/{$campaign->id}/retry")->assertRedirect();

    expect($sentRecipient->fresh()->sent_at->equalTo($firstSentAt))->toBeTrue();
    expect($campaign->recipients()->where('status', 'failed')->count())->toBe(1);
});

test('the campaigns ajax data endpoint filters by client', function () {
    $admin = makeCampaignManagerAdmin();
    $clientA = Client::factory()->create();
    $clientB = Client::factory()->create();
    $surveyA = publishedSurveyForCampaigns($clientA, $admin);
    $surveyB = publishedSurveyForCampaigns($clientB, $admin);

    $this->actingAs($admin)->post('/admin/campaigns', [
        'client_id' => $clientA->id, 'survey_id' => $surveyA->id, 'name' => 'Client A Campaign', 'type' => 'email', 'message_template' => 'Hi {name}: {link}',
    ]);
    $this->actingAs($admin)->post('/admin/campaigns', [
        'client_id' => $clientB->id, 'survey_id' => $surveyB->id, 'name' => 'Client B Campaign', 'type' => 'email', 'message_template' => 'Hi {name}: {link}',
    ]);

    $result = $this->actingAs($admin)->getJson("/admin/campaigns/data?client_id={$clientA->id}");

    $result->assertOk();
    expect($result->json('html'))->toContain('Client A Campaign');
    expect($result->json('html'))->not->toContain('Client B Campaign');
});

test('sending a campaign through ajax returns the refreshed fragment', function () {
    $admin = makeCampaignManagerAdmin();
    $client = Client::factory()->create();
    $survey = publishedSurveyForCampaigns($client, $admin);
    Contact::factory()->for($client)->create(['consent' => true, 'email' => 'ok@example.com']);

    $this->actingAs($admin)->post('/admin/campaigns', [
        'client_id' => $client->id, 'survey_id' => $survey->id, 'name' => 'AJAX Blast', 'type' => 'email', 'message_template' => 'Hi {name}: {link}',
    ]);
    $campaign = Campaign::query()->where('name', 'AJAX Blast')->firstOrFail();

    $result = $this->actingAs($admin)->postJson("/admin/campaigns/{$campaign->id}/send");

    $result->assertOk();
    expect($result->json('html'))->toContain('AJAX Blast');
});

test('a user without send-campaigns permission cannot access campaigns', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin/campaigns')->assertForbidden();
});

test('a client portal user is redirected away from campaign routes', function () {
    $clientUser = ClientUser::factory()->create();

    $this->actingAs($clientUser, 'client')->get('/admin/campaigns')->assertRedirect(route('admin.login'));
});
