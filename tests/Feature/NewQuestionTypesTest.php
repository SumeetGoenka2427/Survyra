<?php

use App\Models\Client;
use App\Models\QuestionType;
use App\Models\Response as SurveyResponse;
use App\Models\SurveyTemplate;
use App\Models\User;
use App\QuestionTypes\MatrixQuestionType;
use App\QuestionTypes\RankingQuestionType;
use App\QuestionTypes\SliderQuestionType;
use App\Services\SurveyService;
use Database\Seeders\QuestionTypeSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(QuestionTypeSeeder::class);
    Role::findOrCreate('survyra_admin', 'web');
    Permission::findOrCreate('manage-surveys', 'web');
});

test('matrix falls back to its first style and resolves its alternate style', function () {
    $matrix = new MatrixQuestionType();

    expect($matrix->renderComponent('nope'))->toBe('survey-questions.matrix.table');
    expect($matrix->renderComponent('stacked'))->toBe('survey-questions.matrix.stacked');
});

test('slider falls back to its first style and resolves its alternate style', function () {
    $slider = new SliderQuestionType();

    expect($slider->renderComponent('nope'))->toBe('survey-questions.slider.range');
    expect($slider->renderComponent('buttons'))->toBe('survey-questions.slider.buttons');
});

test('ranking has a single style and renders the flat ranking view', function () {
    $ranking = new RankingQuestionType();

    expect($ranking->availableStyles())->toBe(['default' => 'Default']);
    expect($ranking->renderComponent())->toBe('survey-questions.ranking');
});

function newTypeAdmin(): User
{
    $user = User::factory()->create();
    $user->assignRole('survyra_admin');
    $user->givePermissionTo('manage-surveys');

    return $user;
}

function publishedSurveyWithQuestion(string $typeKey, array $attributes = []): array
{
    $admin = newTypeAdmin();
    $client = Client::factory()->create();
    $template = SurveyTemplate::factory()->create();
    $questionType = QuestionType::query()->where('key', $typeKey)->firstOrFail();

    $template->questions()->create(array_merge([
        'question_type_id' => $questionType->id,
        'question_text' => 'Sample question',
        'order' => 0,
        'is_required' => true,
        'settings' => [],
        'options' => [],
    ], $attributes));

    $surveyService = app(SurveyService::class);
    $survey = $surveyService->createFromTemplate($client, $template, ucfirst($typeKey).' Survey', $admin->id);
    $surveyService->publish($survey);

    return [$survey->fresh(['questions', 'thankyouRules']), $admin];
}

test('a matrix question renders a row per option on the public survey page', function () {
    [$survey] = publishedSurveyWithQuestion('matrix', [
        'options' => ['Service', 'Price', 'Quality'],
        'settings' => ['display_style' => 'stacked'],
    ]);

    $page = $this->get("/s/{$survey->slug}");

    $page->assertOk();
    $page->assertSee('Service');
    $page->assertSee('Price');
    $page->assertSee('Quality');
    $page->assertSee('sq-matrix-stacked-row', false);
});

test('submitting a matrix answer for only some rows is rejected when the question is required', function () {
    [$survey] = publishedSurveyWithQuestion('matrix', ['options' => ['Service', 'Price', 'Quality']]);
    $question = $survey->questions->first();

    $this->get("/s/{$survey->slug}");
    $responseUuid = SurveyResponse::query()->where('survey_id', $survey->id)->first()->uuid;

    $answer = $this->postJson("/s/{$survey->slug}/answer", [
        'response_uuid' => $responseUuid,
        'question_id' => $question->id,
        'answer' => [0 => 4, 1 => 5],
    ]);

    $answer->assertStatus(422);
});

test('a complete matrix answer saves and scores as the average of its rows', function () {
    [$survey] = publishedSurveyWithQuestion('matrix', ['options' => ['Service', 'Price', 'Quality']]);
    $question = $survey->questions->first();

    $this->get("/s/{$survey->slug}");
    $responseUuid = SurveyResponse::query()->where('survey_id', $survey->id)->first()->uuid;

    $answer = $this->postJson("/s/{$survey->slug}/answer", [
        'response_uuid' => $responseUuid,
        'question_id' => $question->id,
        'answer' => [0 => 4, 1 => 5, 2 => 3],
    ]);

    $answer->assertOk();
    $this->assertDatabaseHas('response_answers', ['question_id' => $question->id, 'score' => 4.0]);
});

test('a ranking question renders every item in the reorderable list', function () {
    [$survey] = publishedSurveyWithQuestion('ranking', ['options' => ['Price', 'Quality', 'Support']]);

    $page = $this->get("/s/{$survey->slug}");

    $page->assertOk();
    $page->assertSee('sq-ranking-list', false);
    $page->assertSee('Price');
    $page->assertSee('Quality');
    $page->assertSee('Support');
});

test('a ranking answer must be a permutation of the configured items', function () {
    [$survey] = publishedSurveyWithQuestion('ranking', ['options' => ['Price', 'Quality', 'Support']]);
    $question = $survey->questions->first();

    $this->get("/s/{$survey->slug}");
    $responseUuid = SurveyResponse::query()->where('survey_id', $survey->id)->first()->uuid;

    $invalid = $this->postJson("/s/{$survey->slug}/answer", [
        'response_uuid' => $responseUuid,
        'question_id' => $question->id,
        'answer' => ['Price', 'Price', 'Support'],
    ]);
    $invalid->assertStatus(422);

    $valid = $this->postJson("/s/{$survey->slug}/answer", [
        'response_uuid' => $responseUuid,
        'question_id' => $question->id,
        'answer' => ['Quality', 'Price', 'Support'],
    ]);
    $valid->assertOk();
    $this->assertDatabaseHas('response_answers', [
        'question_id' => $question->id,
        'answer' => json_encode(['Quality', 'Price', 'Support']),
    ]);
});

test('a slider question renders with the number-boxes style when configured', function () {
    [$survey] = publishedSurveyWithQuestion('slider', [
        'settings' => ['scale_min' => 0, 'scale_max' => 10, 'display_style' => 'buttons'],
    ]);

    $page = $this->get("/s/{$survey->slug}");

    $page->assertOk();
    $page->assertSee('sq-nps-row', false);
});

test('an out of range slider answer is rejected', function () {
    [$survey] = publishedSurveyWithQuestion('slider', ['settings' => ['scale_min' => 0, 'scale_max' => 10]]);
    $question = $survey->questions->first();

    $this->get("/s/{$survey->slug}");
    $responseUuid = SurveyResponse::query()->where('survey_id', $survey->id)->first()->uuid;

    $answer = $this->postJson("/s/{$survey->slug}/answer", [
        'response_uuid' => $responseUuid,
        'question_id' => $question->id,
        'answer' => 99,
    ]);

    $answer->assertStatus(422);
});

test('a slider question is auto-picked as the primary score question', function () {
    [$survey] = publishedSurveyWithQuestion('slider', ['settings' => ['scale_min' => 0, 'scale_max' => 10]]);

    expect($survey->primary_score_question_id)->toBe($survey->questions->first()->id);
    expect($survey->thankyouRules)->toHaveCount(3);
});

test('the template builder page lists the three new question types and can add a matrix question', function () {
    $admin = newTypeAdmin();
    $template = SurveyTemplate::factory()->create();

    $page = $this->actingAs($admin)->get("/admin/templates/{$template->id}/edit");
    $page->assertOk();
    $page->assertSee('Matrix (Rate Multiple Rows)');
    $page->assertSee('Ranking (Reorder Items)');
    $page->assertSee('Slider (Drag a Scale)');

    $matrixType = QuestionType::query()->where('key', 'matrix')->firstOrFail();

    $response = $this->actingAs($admin)->post("/admin/templates/{$template->id}/questions", [
        'question_type_id' => $matrixType->id,
        'question_text' => 'Rate these aspects',
        'options_text' => "Service\nPrice\nQuality",
        'scale_min' => 1,
        'scale_max' => 5,
        'display_style' => 'table',
        'is_required' => '1',
    ]);

    $response->assertRedirect();

    $question = \App\Models\SurveyTemplateQuestion::query()->where('question_text', 'Rate these aspects')->firstOrFail();
    expect($question->options)->toBe(['Service', 'Price', 'Quality']);
    expect($question->settings['display_style'])->toBe('table');
});
