<?php

use App\Models\Client;
use App\Models\QuestionType;
use App\Models\Response as SurveyResponse;
use App\Models\Survey;
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

function publishedCardBasedSurvey(): Survey
{
    $client = Client::factory()->create();
    $template = SurveyTemplate::factory()->create(['layout' => 'card_based']);

    $nps = QuestionType::query()->where('key', 'nps')->firstOrFail();
    $textarea = QuestionType::query()->where('key', 'textarea')->firstOrFail();

    $template->questions()->create(['question_type_id' => $nps->id, 'question_text' => 'How likely to recommend?', 'order' => 0]);
    $template->questions()->create(['question_type_id' => $textarea->id, 'question_text' => 'Any comments?', 'order' => 1, 'is_required' => false]);

    $admin = User::factory()->create();
    $admin->assignRole('survyra_admin');
    $admin->givePermissionTo('manage-surveys');

    $survey = app(SurveyService::class)->createFromTemplate($client, $template, 'Card Based Survey', $admin->id);
    app(SurveyService::class)->publish($survey);

    return $survey->fresh(['questions', 'thankyouRules']);
}

test('a card-based survey renders every question as a numbered card and shares the one-page engine', function () {
    $survey = publishedCardBasedSurvey();

    $page = $this->get("/s/{$survey->slug}");

    $page->assertOk();
    $page->assertSee('card-based-list', false);
    $page->assertSee('card-based-question-number', false);
    $page->assertSee('How likely to recommend?');
    $page->assertSee('Submit Survey');
});

test('a card-based survey can be answered and submitted end to end', function () {
    $survey = publishedCardBasedSurvey();
    [$nps] = $survey->questions->all();

    $this->get("/s/{$survey->slug}");
    $responseUuid = SurveyResponse::query()->where('survey_id', $survey->id)->first()->uuid;

    $answer = $this->postJson("/s/{$survey->slug}/answer", [
        'response_uuid' => $responseUuid,
        'question_id' => $nps->id,
        'answer' => 9,
    ]);
    $answer->assertOk();
    expect($answer->json('html'))->toContain('card-based-question');

    $submit = $this->postJson("/s/{$survey->slug}/submit", ['response_uuid' => $responseUuid]);

    $submit->assertOk();
    $this->assertDatabaseHas('responses', ['uuid' => $responseUuid, 'status' => 'completed', 'sentiment' => 'positive']);
});
