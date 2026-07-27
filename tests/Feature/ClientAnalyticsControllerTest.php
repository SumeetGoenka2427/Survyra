<?php

use App\Models\Client;
use App\Models\ClientUser;
use App\Models\QuestionType;
use App\Models\Response as SurveyResponseModel;
use App\Models\Survey;
use App\Models\SurveyTemplate;
use App\Models\User;
use App\Services\AnalyticsService;
use App\Services\SurveyService;
use Database\Seeders\QuestionTypeSeeder;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(QuestionTypeSeeder::class);
    Role::findOrCreate('survyra_admin', 'web');
});

function clientAnalyticsAdmin(): User
{
    $user = User::factory()->create();
    $user->assignRole('survyra_admin');

    return $user;
}

function surveyWithResponses(Client $client, User $admin, int $count = 3): Survey
{
    $template = SurveyTemplate::factory()->create();
    $nps = QuestionType::query()->where('key', 'nps')->firstOrFail();
    $template->questions()->create(['question_type_id' => $nps->id, 'question_text' => 'How likely to recommend?', 'order' => 0]);

    $survey = app(SurveyService::class)->createFromTemplate($client, $template, 'Client Dashboard Survey', $admin->id);
    app(SurveyService::class)->publish($survey);
    $survey = $survey->fresh(['questions']);
    $question = $survey->questions->first();

    for ($i = 0; $i < $count; $i++) {
        $response = SurveyResponseModel::query()->create([
            'client_id' => $client->id,
            'survey_id' => $survey->id,
            'status' => 'completed',
            'started_at' => now()->subMinutes(10),
            'completed_at' => now(),
            'sentiment' => 'positive',
        ]);
        $response->answers()->create(['question_id' => $question->id, 'answer' => 9, 'score' => 9]);
    }

    return $survey;
}

test('forClientDashboard computes without error against real seeded data - this is the code that used to crash on every load', function () {
    $client = Client::factory()->create();
    $admin = clientAnalyticsAdmin();
    surveyWithResponses($client, $admin);

    // Give the client a team member so the ClientUser::is_active path
    // (the actual line that crashed - a nonexistent whereHas('user')) runs.
    ClientUser::factory()->create(['client_id' => $client->id, 'is_active' => true]);
    ClientUser::factory()->create(['client_id' => $client->id, 'is_active' => false]);

    $data = app(AnalyticsService::class)->forClientDashboard($client, now()->subDays(30), now());

    expect($data['summary']['total_members'])->toBe(2);
    expect($data['summary']['active_members'])->toBe(1);
    expect($data['summary']['total_responses'])->toBe(3);
});

test('a survyra admin can view the client analytics dashboard', function () {
    $client = Client::factory()->create();
    $admin = clientAnalyticsAdmin();
    surveyWithResponses($client, $admin);

    $response = $this->actingAs($admin)->get("/admin/clients/{$client->id}/analytics");

    $response->assertOk();
    $response->assertSee($client->company_name);
});

test('the client analytics data endpoint returns a valid json snapshot', function () {
    $client = Client::factory()->create();
    $admin = clientAnalyticsAdmin();
    surveyWithResponses($client, $admin);

    $response = $this->actingAs($admin)->getJson("/admin/clients/{$client->id}/analytics/data");

    $response->assertOk();
    $response->assertJsonStructure(['summary', 'survey_performance', 'recent_activities']);
});

test('a user without the survyra_admin or super_admin role cannot view the client analytics dashboard', function () {
    $client = Client::factory()->create();
    $plainUser = User::factory()->create();

    $response = $this->actingAs($plainUser)->get("/admin/clients/{$client->id}/analytics");

    $response->assertForbidden();
});

test('a client portal user is redirected away from the client analytics dashboard', function () {
    $client = Client::factory()->create();
    $clientUser = ClientUser::factory()->create(['client_id' => $client->id]);

    $response = $this->actingAs($clientUser, 'client')->get("/admin/clients/{$client->id}/analytics");

    $response->assertRedirect(route('admin.login'));
});

test('exporting the client analytics dashboard produces a non-empty file in every format', function () {
    $client = Client::factory()->create();
    $admin = clientAnalyticsAdmin();
    surveyWithResponses($client, $admin);

    foreach (['pdf', 'excel', 'csv'] as $format) {
        $result = $this->actingAs($admin)->get("/admin/clients/{$client->id}/analytics/export/{$format}");
        $result->assertOk();
        expect(strlen($result->streamedContent()))->toBeGreaterThan(0);
    }
});
