<?php

use App\Models\Client;
use App\Models\QuestionType;
use App\Models\Response as SurveyResponse;
use App\Models\SlackIntegration;
use App\Models\SurveyTemplate;
use App\Models\User;
use App\Services\SurveyService;
use Database\Seeders\QuestionTypeSeeder;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->seed(QuestionTypeSeeder::class);
});

function slackAdmin(): User
{
    return User::factory()->create();
}

function slackIntegrationFor(Client $client, array $events): SlackIntegration
{
    return SlackIntegration::create([
        'client_id' => $client->id,
        'webhook_url' => 'https://hooks.slack.com/services/T00/B00/xxx',
        'events' => $events,
        'is_active' => true,
    ]);
}

test('publishing a survey notifies Slack when survey_published is subscribed', function () {
    Http::fake();

    $client = Client::factory()->create();
    $admin = slackAdmin();
    slackIntegrationFor($client, ['survey_published']);

    $template = SurveyTemplate::factory()->create();
    $nps = QuestionType::query()->where('key', 'nps')->firstOrFail();
    $template->questions()->create(['question_type_id' => $nps->id, 'question_text' => 'How likely to recommend?', 'order' => 0]);
    $survey = app(SurveyService::class)->createFromTemplate($client, $template, 'Slack Test Survey', $admin->id);

    app(SurveyService::class)->publish($survey);

    Http::assertSent(function ($request) {
        return $request->url() === 'https://hooks.slack.com/services/T00/B00/xxx'
            && str_contains(json_encode($request->data()), 'Survey Published');
    });
});

test('an unsubscribed event does not notify Slack', function () {
    Http::fake();

    $client = Client::factory()->create();
    $admin = slackAdmin();
    slackIntegrationFor($client, ['negative_feedback']); // not subscribed to survey_published

    $template = SurveyTemplate::factory()->create();
    $nps = QuestionType::query()->where('key', 'nps')->firstOrFail();
    $template->questions()->create(['question_type_id' => $nps->id, 'question_text' => 'How likely to recommend?', 'order' => 0]);
    $survey = app(SurveyService::class)->createFromTemplate($client, $template, 'Slack Test Survey', $admin->id);

    app(SurveyService::class)->publish($survey);

    Http::assertNothingSent();
});

test('completing a survey response notifies Slack for response_completed', function () {
    Http::fake();

    $client = Client::factory()->create();
    $admin = slackAdmin();
    slackIntegrationFor($client, ['response_completed']);

    $template = SurveyTemplate::factory()->create();
    $nps = QuestionType::query()->where('key', 'nps')->firstOrFail();
    $template->questions()->create(['question_type_id' => $nps->id, 'question_text' => 'How likely to recommend?', 'order' => 0]);
    $survey = app(SurveyService::class)->createFromTemplate($client, $template, 'Slack Response Survey', $admin->id);
    app(SurveyService::class)->publish($survey);
    $survey = $survey->fresh(['questions']);
    $question = $survey->questions->first();

    $this->get("/s/{$survey->slug}");
    $responseUuid = SurveyResponse::query()->where('survey_id', $survey->id)->first()->uuid;

    $this->postJson("/s/{$survey->slug}/answer", [
        'response_uuid' => $responseUuid,
        'question_id' => $question->id,
        'answer' => 9,
    ]);

    Http::assertSent(function ($request) {
        return $request->url() === 'https://hooks.slack.com/services/T00/B00/xxx'
            && str_contains(json_encode($request->data()), 'New Response Completed');
    });
});

test('negative sentiment completion notifies Slack for negative_feedback', function () {
    Http::fake();

    $client = Client::factory()->create();
    $admin = slackAdmin();
    slackIntegrationFor($client, ['negative_feedback']);

    $template = SurveyTemplate::factory()->create();
    $nps = QuestionType::query()->where('key', 'nps')->firstOrFail();
    $template->questions()->create(['question_type_id' => $nps->id, 'question_text' => 'How likely to recommend?', 'order' => 0]);
    $survey = app(SurveyService::class)->createFromTemplate($client, $template, 'Slack Negative Survey', $admin->id);
    app(SurveyService::class)->publish($survey);
    $survey = $survey->fresh(['questions']);
    $question = $survey->questions->first();

    $this->get("/s/{$survey->slug}");
    $responseUuid = SurveyResponse::query()->where('survey_id', $survey->id)->first()->uuid;

    $this->postJson("/s/{$survey->slug}/answer", [
        'response_uuid' => $responseUuid,
        'question_id' => $question->id,
        'answer' => 2,
    ]);

    Http::assertSent(function ($request) {
        return $request->url() === 'https://hooks.slack.com/services/T00/B00/xxx'
            && str_contains(json_encode($request->data()), 'Negative Feedback Received');
    });
});
