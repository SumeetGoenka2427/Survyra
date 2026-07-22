<?php

use App\Mail\ScheduledReportMail;
use App\Models\Client;
use App\Models\ClientUser;
use App\Models\QuestionType;
use App\Models\Report;
use App\Models\Response as SurveyResponseModel;
use App\Models\Survey;
use App\Models\SurveyTemplate;
use App\Models\User;
use App\Services\AnalyticsService;
use App\Services\SurveyService;
use Database\Seeders\QuestionTypeSeeder;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(QuestionTypeSeeder::class);
    Role::findOrCreate('survyra_admin', 'web');
    Permission::findOrCreate('view-analytics', 'web');
});

function analyticsAdmin(): User
{
    $user = User::factory()->create();
    $user->assignRole('survyra_admin');
    $user->givePermissionTo('view-analytics');

    return $user;
}

function npsSurvey(Client $client, User $admin): Survey
{
    $template = SurveyTemplate::factory()->create();
    $nps = QuestionType::query()->where('key', 'nps')->firstOrFail();
    $template->questions()->create(['question_type_id' => $nps->id, 'question_text' => 'How likely to recommend?', 'order' => 0]);

    $survey = app(SurveyService::class)->createFromTemplate($client, $template, 'NPS Survey', $admin->id);
    app(SurveyService::class)->publish($survey);

    return $survey->fresh(['questions']);
}

function csatSurvey(Client $client, User $admin): Survey
{
    $template = SurveyTemplate::factory()->create();
    $csat = QuestionType::query()->where('key', 'csat')->firstOrFail();
    $template->questions()->create(['question_type_id' => $csat->id, 'question_text' => 'How satisfied were you?', 'order' => 0]);

    $survey = app(SurveyService::class)->createFromTemplate($client, $template, 'CSAT Survey', $admin->id);
    app(SurveyService::class)->publish($survey);

    return $survey->fresh(['questions']);
}

function npsAndRadioSurvey(Client $client, User $admin): Survey
{
    $template = SurveyTemplate::factory()->create();
    $nps = QuestionType::query()->where('key', 'nps')->firstOrFail();
    $radio = QuestionType::query()->where('key', 'radio')->firstOrFail();
    $template->questions()->create(['question_type_id' => $nps->id, 'question_text' => 'How likely to recommend?', 'order' => 0]);
    $template->questions()->create(['question_type_id' => $radio->id, 'question_text' => 'Which channel?', 'options' => ['Store', 'Online'], 'order' => 1]);

    $survey = app(SurveyService::class)->createFromTemplate($client, $template, 'Combo Survey', $admin->id);
    app(SurveyService::class)->publish($survey);

    return $survey->fresh(['questions']);
}

function seedResponse(Survey $survey, array $overrides = []): SurveyResponseModel
{
    return SurveyResponseModel::query()->create(array_merge([
        'client_id' => $survey->client_id,
        'survey_id' => $survey->id,
        'status' => 'completed',
        'started_at' => now()->subMinutes(5),
        'completed_at' => now(),
        'sentiment' => 'positive',
    ], $overrides));
}

test('nps is computed from promoters passives and detractors', function () {
    $admin = analyticsAdmin();
    $client = Client::factory()->create();
    $survey = npsSurvey($client, $admin);
    $question = $survey->questions->first();

    foreach ([10, 9, 7, 3] as $score) {
        $response = seedResponse($survey);
        $response->answers()->create(['question_id' => $question->id, 'answer' => $score, 'score' => $score]);
    }

    $snapshot = app(AnalyticsService::class)->forClient($client, null, now()->subDay(), now()->addDay());

    expect($snapshot['metrics']['nps'])->toMatchArray([
        'value' => 25.0,
        'promoters' => 2,
        'passives' => 1,
        'detractors' => 1,
        'total' => 4,
    ]);
});

test('a csat survey never produces an nps metric', function () {
    $admin = analyticsAdmin();
    $client = Client::factory()->create();
    $survey = csatSurvey($client, $admin);
    $question = $survey->questions->first();

    $response = seedResponse($survey);
    $response->answers()->create(['question_id' => $question->id, 'answer' => 4, 'score' => 4]);

    $snapshot = app(AnalyticsService::class)->forClient($client, null, now()->subDay(), now()->addDay());

    expect($snapshot['metrics'])->not->toHaveKey('nps');
    expect($snapshot['metrics']['csat']['value'])->toBe(80.0);
});

test('completion rate and average completion time match hand calculated expectations', function () {
    $admin = analyticsAdmin();
    $client = Client::factory()->create();
    $survey = npsSurvey($client, $admin);

    seedResponse($survey, ['started_at' => now()->subSeconds(60), 'completed_at' => now(), 'status' => 'completed']);
    seedResponse($survey, ['started_at' => now()->subSeconds(120), 'completed_at' => now(), 'status' => 'completed']);
    seedResponse($survey, ['status' => 'abandoned', 'completed_at' => null]);

    $snapshot = app(AnalyticsService::class)->forClient($client, null, now()->subDay(), now()->addDay());

    expect($snapshot['total_responses'])->toBe(3);
    expect($snapshot['completion_rate'])->toBe(66.7);
    expect($snapshot['avg_completion_seconds'])->toBe(90);
});

test('question breakdown counts match seeded answers for a choice question', function () {
    $admin = analyticsAdmin();
    $client = Client::factory()->create();
    $survey = npsAndRadioSurvey($client, $admin);
    $radioQuestion = $survey->questions[1];

    foreach (['Store', 'Store', 'Online'] as $answer) {
        $response = seedResponse($survey);
        $response->answers()->create(['question_id' => $radioQuestion->id, 'answer' => $answer]);
    }

    $snapshot = app(AnalyticsService::class)->forClient($client, $survey, now()->subDay(), now()->addDay());

    $breakdown = collect($snapshot['question_breakdown'])->firstWhere('question.id', $radioQuestion->id);

    expect($breakdown['type'])->toBe('choice');
    expect($breakdown['data']['Store'])->toBe(2);
    expect($breakdown['data']['Online'])->toBe(1);
    expect($breakdown['total'])->toBe(3);
});

test('admin can view any clients analytics dashboard', function () {
    $admin = analyticsAdmin();
    $client = Client::factory()->create();
    npsSurvey($client, $admin);

    $response = $this->actingAs($admin)->get("/admin/analytics?client_id={$client->id}");

    $response->assertOk();
    $response->assertSee($client->company_name);
});

test('a user without view-analytics permission cannot access admin analytics', function () {
    $user = User::factory()->create();
    $user->assignRole('survyra_admin');

    $this->actingAs($user)->get('/admin/analytics')->assertForbidden();
});

test('a client guard user is redirected away from admin analytics', function () {
    $clientUser = ClientUser::factory()->create();

    $this->actingAs($clientUser, 'client')->get('/admin/analytics')->assertRedirect(route('admin.login'));
});

test('response detail shows every answer for that response', function () {
    $admin = analyticsAdmin();
    $client = Client::factory()->create();
    $survey = npsSurvey($client, $admin);
    $question = $survey->questions->first();

    $response = seedResponse($survey);
    $response->answers()->create(['question_id' => $question->id, 'answer' => 9, 'score' => 9]);

    $result = $this->actingAs($admin)->getJson("/admin/analytics/responses/{$response->id}");

    $result->assertOk();
    expect($result->json('html'))->toContain('How likely to recommend?');
});

test('a client portal user cannot view another clients response even by guessing the id', function () {
    $admin = analyticsAdmin();
    $clientA = Client::factory()->create();
    $clientB = Client::factory()->create();
    $surveyB = npsSurvey($clientB, $admin);
    $responseB = seedResponse($surveyB);

    $clientUserA = ClientUser::factory()->create(['client_id' => $clientA->id]);

    $this->actingAs($clientUserA, 'client')
        ->getJson("/portal/analytics/responses/{$responseB->id}")
        ->assertNotFound();
});

test('exporting to pdf excel and csv produces non-empty files', function () {
    $admin = analyticsAdmin();
    $client = Client::factory()->create();
    $survey = npsSurvey($client, $admin);
    $question = $survey->questions->first();

    $response = seedResponse($survey);
    $response->answers()->create(['question_id' => $question->id, 'answer' => 9, 'score' => 9]);

    foreach (['pdf', 'excel', 'csv'] as $format) {
        $result = $this->actingAs($admin)->get("/admin/analytics/export/{$format}?client_id={$client->id}");
        $result->assertOk();
        expect(strlen($result->streamedContent()))->toBeGreaterThan(0);
    }
});

test('reports send-scheduled sends only due reports and advances their schedule', function () {
    Mail::fake();

    $admin = analyticsAdmin();
    $client = Client::factory()->create();
    $survey = npsSurvey($client, $admin);
    $question = $survey->questions->first();
    $response = seedResponse($survey);
    $response->answers()->create(['question_id' => $question->id, 'answer' => 9, 'score' => 9]);

    $due = Report::query()->create([
        'client_id' => $client->id,
        'survey_id' => $survey->id,
        'type' => 'pdf',
        'frequency' => 'weekly',
        'recipients' => ['owner@example.com'],
        'next_run_at' => now()->subDay(),
        'is_active' => true,
    ]);

    $notDue = Report::query()->create([
        'client_id' => $client->id,
        'survey_id' => $survey->id,
        'type' => 'csv',
        'frequency' => 'weekly',
        'recipients' => ['owner@example.com'],
        'next_run_at' => now()->addWeek(),
        'is_active' => true,
    ]);

    $this->artisan('reports:send-scheduled')->assertExitCode(0);

    Mail::assertQueued(ScheduledReportMail::class, 1);

    $due->refresh();
    $notDue->refresh();

    expect($due->last_sent_at)->not->toBeNull();
    expect($due->next_run_at->isFuture())->toBeTrue();
    expect($notDue->last_sent_at)->toBeNull();
});
