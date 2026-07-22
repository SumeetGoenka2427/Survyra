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

function publishedOnePageSurveyWithLogic(): Survey
{
    $client = Client::factory()->create();
    $template = SurveyTemplate::factory()->create(['layout' => 'one_page']);

    $nps = QuestionType::query()->where('key', 'nps')->firstOrFail();
    $radio = QuestionType::query()->where('key', 'radio')->firstOrFail();
    $textarea = QuestionType::query()->where('key', 'textarea')->firstOrFail();

    $template->questions()->create(['question_type_id' => $nps->id, 'question_text' => 'How likely to recommend?', 'order' => 0]);
    $template->questions()->create(['question_type_id' => $radio->id, 'question_text' => 'Was the wait time ok?', 'options' => ['Yes', 'No'], 'order' => 1]);
    $template->questions()->create(['question_type_id' => $textarea->id, 'question_text' => 'Tell us about the wait', 'order' => 2, 'is_required' => false]);

    $admin = User::factory()->create();
    $admin->assignRole('survyra_admin');
    $admin->givePermissionTo('manage-surveys');

    $surveyService = app(SurveyService::class);
    $survey = $surveyService->createFromTemplate($client, $template, 'One Page Survey', $admin->id);
    $survey->load('questions');

    $waitQuestion = $survey->questions[1];
    $complaintQuestion = $survey->questions[2];

    $survey->logicRules()->create([
        'source_question_id' => $waitQuestion->id,
        'conditions' => [['question_id' => $waitQuestion->id, 'operator' => 'equals', 'value' => 'No']],
        'action' => 'show',
        'target_question_id' => $complaintQuestion->id,
    ]);

    $surveyService->publish($survey);

    return $survey->fresh(['questions', 'logicRules', 'thankyouRules']);
}

test('a one-page survey shows every currently applicable question at once, not one at a time', function () {
    $survey = publishedOnePageSurveyWithLogic();

    $page = $this->get("/s/{$survey->slug}");

    $page->assertOk();
    $page->assertSee('How likely to recommend?');
    $page->assertSee('Was the wait time ok?');
    $page->assertDontSee('Tell us about the wait');
    $page->assertSee('Submit Survey');
});

test('answering a one-page question returns the refreshed question set instead of advancing to the next question', function () {
    $survey = publishedOnePageSurveyWithLogic();
    [$nps] = $survey->questions->all();

    $this->get("/s/{$survey->slug}");
    $responseUuid = SurveyResponse::query()->where('survey_id', $survey->id)->first()->uuid;

    $result = $this->postJson("/s/{$survey->slug}/answer", [
        'response_uuid' => $responseUuid,
        'question_id' => $nps->id,
        'answer' => 8,
    ]);

    $result->assertOk();
    expect($result->json('done'))->toBeFalse();
    expect($result->json('html'))->toContain('How likely to recommend?');
    expect($result->json('html'))->toContain('Was the wait time ok?');
    $this->assertDatabaseHas('responses', ['uuid' => $responseUuid, 'status' => 'in_progress']);
});

test('a conditionally shown question appears on the one-page list only once its trigger answer is saved', function () {
    $survey = publishedOnePageSurveyWithLogic();
    [, $wait] = $survey->questions->all();

    $this->get("/s/{$survey->slug}");
    $responseUuid = SurveyResponse::query()->where('survey_id', $survey->id)->first()->uuid;

    $result = $this->postJson("/s/{$survey->slug}/answer", [
        'response_uuid' => $responseUuid,
        'question_id' => $wait->id,
        'answer' => 'No',
    ]);

    $result->assertOk();
    expect($result->json('html'))->toContain('Tell us about the wait');
    expect($result->json('questionIds'))->toHaveCount(3);
});

test('submitting a one-page survey before required questions are answered returns a 422', function () {
    $survey = publishedOnePageSurveyWithLogic();

    $this->get("/s/{$survey->slug}");
    $responseUuid = SurveyResponse::query()->where('survey_id', $survey->id)->first()->uuid;

    $result = $this->postJson("/s/{$survey->slug}/submit", ['response_uuid' => $responseUuid]);

    $result->assertStatus(422);
    $this->assertDatabaseMissing('responses', ['uuid' => $responseUuid, 'status' => 'completed']);
});

test('submitting a one-page survey after all required questions are answered completes it', function () {
    $survey = publishedOnePageSurveyWithLogic();
    [$nps, $wait] = $survey->questions->all();

    $this->get("/s/{$survey->slug}");
    $responseUuid = SurveyResponse::query()->where('survey_id', $survey->id)->first()->uuid;

    $this->postJson("/s/{$survey->slug}/answer", ['response_uuid' => $responseUuid, 'question_id' => $nps->id, 'answer' => 9]);
    $this->postJson("/s/{$survey->slug}/answer", ['response_uuid' => $responseUuid, 'question_id' => $wait->id, 'answer' => 'Yes']);

    $result = $this->postJson("/s/{$survey->slug}/submit", ['response_uuid' => $responseUuid]);

    $result->assertOk();
    expect($result->json('html'))->not->toContain('Submit Survey');
    $this->assertDatabaseHas('responses', ['uuid' => $responseUuid, 'status' => 'completed', 'sentiment' => 'positive']);
});
