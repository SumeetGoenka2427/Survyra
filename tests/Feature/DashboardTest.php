<?php

use App\Models\Client;
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
    Permission::findOrCreate('manage-surveys', 'web');
});

function dashboardAdmin(): User
{
    $user = User::factory()->create();
    $user->assignRole('survyra_admin');
    $user->givePermissionTo('manage-surveys');

    return $user;
}

function publishedSurveyWithCompletedResponse(string $sentiment = 'positive'): void
{
    $admin = dashboardAdmin();
    $client = Client::factory()->create();
    $template = SurveyTemplate::factory()->create();
    $nps = QuestionType::query()->where('key', 'nps')->firstOrFail();
    $template->questions()->create(['question_type_id' => $nps->id, 'question_text' => 'How likely?', 'order' => 0]);

    $survey = app(SurveyService::class)->createFromTemplate($client, $template, 'Dashboard Survey', $admin->id);
    app(SurveyService::class)->publish($survey);

    $survey->responses()->create([
        'client_id' => $client->id,
        'status' => 'completed',
        'started_at' => now(),
        'completed_at' => now(),
        'sentiment' => $sentiment,
    ]);
}

test('the dashboard shows platform-wide survey and response stats, not just client stats', function () {
    $admin = dashboardAdmin();
    publishedSurveyWithCompletedResponse('positive');
    publishedSurveyWithCompletedResponse('negative');

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertOk();
    $response->assertSee('Published Surveys');
    $response->assertSee('Responses This Week');
    $response->assertSee('Needs Attention');
    $response->assertViewHas('stats', function ($stats) {
        return $stats['published_surveys'] === 2
            && $stats['responses_this_week'] === 2
            && $stats['negative_responses_this_week'] === 1;
    });
});

test('the dashboard shows a recent survey responses panel distinct from the clients list', function () {
    $admin = dashboardAdmin();
    publishedSurveyWithCompletedResponse('positive');

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertOk();
    $response->assertSee('Recent Survey Responses');
    $response->assertSee('Dashboard Survey');
});

test('the recent-responses fragment endpoint returns refreshed html', function () {
    $admin = dashboardAdmin();
    publishedSurveyWithCompletedResponse('neutral');

    $response = $this->actingAs($admin)->getJson(route('admin.dashboard.recent-responses'));

    $response->assertOk();
    $response->assertJsonStructure(['html']);
    expect($response->json('html'))->toContain('Dashboard Survey');
});

test('a survey with no completed responses does not appear in recent responses', function () {
    $admin = dashboardAdmin();
    $client = Client::factory()->create();
    $template = SurveyTemplate::factory()->create();
    $nps = QuestionType::query()->where('key', 'nps')->firstOrFail();
    $template->questions()->create(['question_type_id' => $nps->id, 'question_text' => 'How likely?', 'order' => 0]);
    $survey = app(SurveyService::class)->createFromTemplate($client, $template, 'Untouched Survey', $admin->id);
    app(SurveyService::class)->publish($survey);

    $survey->responses()->create([
        'client_id' => $client->id,
        'status' => 'started',
        'started_at' => now(),
    ]);

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertOk();
    $response->assertDontSee('Untouched Survey');
});
