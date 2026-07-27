<?php

use App\Models\Client;
use App\Models\QuestionType;
use App\Models\SurveyTemplate;
use App\Models\User;
use App\Services\SurveyService;
use Database\Seeders\QuestionTypeSeeder;

beforeEach(function () {
    $this->seed(QuestionTypeSeeder::class);
});

function welcomeSurvey(): \App\Models\Survey
{
    $client = Client::factory()->create();
    $admin = User::factory()->create();
    $template = SurveyTemplate::factory()->create();
    $nps = QuestionType::query()->where('key', 'nps')->firstOrFail();
    $template->questions()->create(['question_type_id' => $nps->id, 'question_text' => 'How likely to recommend?', 'order' => 0]);

    $survey = app(SurveyService::class)->createFromTemplate($client, $template, 'Welcome Test Survey', $admin->id);
    $survey->update(['welcome_screen' => ['title' => 'Before we begin', 'description' => 'Two quick questions.', 'button_text' => 'Start']]);
    app(SurveyService::class)->publish($survey);

    return $survey->fresh();
}

test('a survey with a welcome screen shows it on first visit instead of crashing', function () {
    $survey = welcomeSurvey();

    $response = $this->get("/s/{$survey->slug}");

    $response->assertOk();
    $response->assertSee('Before we begin');
    $response->assertSee('Start');
});

test('starting the survey moves past the welcome screen to the first question', function () {
    $survey = welcomeSurvey();

    // First visit creates the response record and shows the welcome screen.
    $this->get("/s/{$survey->slug}");

    $response = $this->get("/s/{$survey->slug}?start=1");

    $response->assertOk();
    $response->assertDontSee('Before we begin');
    $response->assertSee('How likely to recommend?');
});

test('a survey without a welcome screen goes straight to the first question as before', function () {
    $client = Client::factory()->create();
    $admin = User::factory()->create();
    $template = SurveyTemplate::factory()->create();
    $nps = QuestionType::query()->where('key', 'nps')->firstOrFail();
    $template->questions()->create(['question_type_id' => $nps->id, 'question_text' => 'How likely?', 'order' => 0]);
    $survey = app(SurveyService::class)->createFromTemplate($client, $template, 'No Welcome Survey', $admin->id);
    app(SurveyService::class)->publish($survey);

    $response = $this->get("/s/{$survey->slug}");

    $response->assertOk();
    $response->assertSee('How likely?');
});
