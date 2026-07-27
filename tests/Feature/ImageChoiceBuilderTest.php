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

function imageChoiceBuilderAdmin(): User
{
    $user = User::factory()->create();
    $user->assignRole('survyra_admin');
    $user->givePermissionTo('manage-surveys');

    return $user;
}

function imageChoiceTypeId(): int
{
    return QuestionType::query()->where('key', 'image_choice')->firstOrFail()->id;
}

test('adding an image_choice question through the survey builder stores structured options', function () {
    $admin = imageChoiceBuilderAdmin();
    $client = Client::factory()->create();
    $survey = app(SurveyService::class)->createBlank($client, 'Image Survey', 'multi_step', $admin->id);

    $response = $this->actingAs($admin)->post(route('admin.surveys.questions.store', $survey), [
        'question_type_id' => imageChoiceTypeId(),
        'question_text' => 'Pick your favorite',
        'image_options_json' => json_encode([
            ['label' => 'Red', 'image' => 'https://example.com/red.png', 'value' => 'red'],
            ['label' => 'Blue', 'image' => 'https://example.com/blue.png', 'value' => ''],
            ['label' => '', 'image' => 'https://example.com/ignored.png', 'value' => ''],
        ]),
        'is_required' => '1',
    ]);

    $response->assertRedirect();

    $question = $survey->fresh()->questions()->where('question_text', 'Pick your favorite')->firstOrFail();

    expect($question->options)->toBe([
        ['label' => 'Red', 'image' => 'https://example.com/red.png', 'value' => 'red'],
        ['label' => 'Blue', 'image' => 'https://example.com/blue.png', 'value' => 'Blue'],
    ]);
});

test('an image_choice question with a missing label fails validation on the survey builder', function () {
    $admin = imageChoiceBuilderAdmin();
    $client = Client::factory()->create();
    $survey = app(SurveyService::class)->createBlank($client, 'Image Survey', 'multi_step', $admin->id);

    $response = $this->actingAs($admin)->postJson(route('admin.surveys.questions.store', $survey), [
        'question_type_id' => imageChoiceTypeId(),
        'question_text' => 'Pick your favorite',
        'image_options_json' => json_encode([
            ['label' => '', 'image' => '', 'value' => ''],
        ]),
        'is_required' => '1',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('options');
    expect($survey->fresh()->questions)->toHaveCount(0);
});

test('a non image_choice question on the survey builder still parses options from plain text', function () {
    $admin = imageChoiceBuilderAdmin();
    $client = Client::factory()->create();
    $survey = app(SurveyService::class)->createBlank($client, 'Radio Survey', 'multi_step', $admin->id);
    $radioTypeId = QuestionType::query()->where('key', 'radio')->firstOrFail()->id;

    $this->actingAs($admin)->post(route('admin.surveys.questions.store', $survey), [
        'question_type_id' => $radioTypeId,
        'question_text' => 'Pick one',
        'options_text' => "Yes\nNo",
        'is_required' => '1',
    ])->assertRedirect();

    $question = $survey->fresh()->questions()->where('question_text', 'Pick one')->firstOrFail();
    expect($question->options)->toBe(['Yes', 'No']);
});

test('updating an existing image_choice question replaces its options', function () {
    $admin = imageChoiceBuilderAdmin();
    $client = Client::factory()->create();
    $survey = app(SurveyService::class)->createBlank($client, 'Image Survey', 'multi_step', $admin->id);
    $question = $survey->questions()->create([
        'question_type_id' => imageChoiceTypeId(),
        'question_text' => 'Pick your favorite',
        'options' => [['label' => 'Red', 'image' => '', 'value' => 'Red']],
        'order' => 1,
        'is_required' => true,
    ]);

    $response = $this->actingAs($admin)->put(route('admin.surveys.questions.update', [$survey, $question]), [
        'question_type_id' => imageChoiceTypeId(),
        'question_text' => 'Pick your favorite',
        'image_options_json' => json_encode([
            ['label' => 'Green', 'image' => 'https://example.com/green.png', 'value' => ''],
        ]),
        'is_required' => '1',
    ]);

    $response->assertRedirect();
    expect($question->fresh()->options)->toBe([
        ['label' => 'Green', 'image' => 'https://example.com/green.png', 'value' => 'Green'],
    ]);
});

test('the survey edit page renders an image_choice question with array options without error', function () {
    $admin = imageChoiceBuilderAdmin();
    $client = Client::factory()->create();
    $survey = app(SurveyService::class)->createBlank($client, 'Image Survey', 'multi_step', $admin->id);
    $survey->questions()->create([
        'question_type_id' => imageChoiceTypeId(),
        'question_text' => 'Pick your favorite',
        'options' => [['label' => 'Red', 'image' => 'https://example.com/red.png', 'value' => 'red']],
        'order' => 1,
        'is_required' => true,
    ]);

    $response = $this->actingAs($admin)->get(route('admin.surveys.edit', $survey));

    $response->assertOk();
    $response->assertSee('Pick your favorite');
});

test('adding an image_choice question through the template builder stores structured options', function () {
    $admin = imageChoiceBuilderAdmin();
    $template = SurveyTemplate::factory()->create();

    $response = $this->actingAs($admin)->post(route('admin.templates.questions.store', $template), [
        'question_type_id' => imageChoiceTypeId(),
        'question_text' => 'Pick your favorite',
        'image_options_json' => json_encode([
            ['label' => 'Red', 'image' => 'https://example.com/red.png', 'value' => ''],
        ]),
        'is_required' => '1',
    ]);

    $response->assertRedirect();

    $question = $template->fresh()->questions()->where('question_text', 'Pick your favorite')->firstOrFail();
    expect($question->options)->toBe([
        ['label' => 'Red', 'image' => 'https://example.com/red.png', 'value' => 'Red'],
    ]);
});

test('the template edit page renders an image_choice question with array options without error', function () {
    $admin = imageChoiceBuilderAdmin();
    $template = SurveyTemplate::factory()->create();
    $template->questions()->create([
        'question_type_id' => imageChoiceTypeId(),
        'question_text' => 'Pick your favorite',
        'options' => [['label' => 'Red', 'image' => 'https://example.com/red.png', 'value' => 'red']],
        'order' => 1,
        'is_required' => true,
        'settings' => [],
    ]);

    $response = $this->actingAs($admin)->get(route('admin.templates.edit', $template));

    $response->assertOk();
    $response->assertSee('Pick your favorite');
});

test('an image_choice question renders real img tags on the public survey page', function () {
    $admin = imageChoiceBuilderAdmin();
    $client = Client::factory()->create();
    $template = SurveyTemplate::factory()->create();
    $template->questions()->create([
        'question_type_id' => imageChoiceTypeId(),
        'question_text' => 'Pick your favorite',
        'options' => [
            ['label' => 'Red', 'image' => 'https://example.com/red.png', 'value' => 'red'],
            ['label' => 'Blue', 'image' => 'https://example.com/blue.png', 'value' => 'blue'],
        ],
        'order' => 1,
        'is_required' => true,
        'settings' => [],
    ]);

    $surveyService = app(SurveyService::class);
    $survey = $surveyService->createFromTemplate($client, $template, 'Image Choice Survey', $admin->id);
    $surveyService->publish($survey);

    $page = $this->get("/s/{$survey->slug}");

    $page->assertOk();
    $page->assertSee('https://example.com/red.png', false);
    $page->assertSee('https://example.com/blue.png', false);
});

test('image_choice carousel and list styles render without error on the public survey page', function (string $style) {
    $admin = imageChoiceBuilderAdmin();
    $client = Client::factory()->create();
    $template = SurveyTemplate::factory()->create();
    $template->questions()->create([
        'question_type_id' => imageChoiceTypeId(),
        'question_text' => 'Pick your favorite',
        'options' => [
            ['label' => 'Red', 'image' => 'https://example.com/red.png', 'value' => 'red'],
        ],
        'order' => 1,
        'is_required' => true,
        'settings' => ['display_style' => $style],
    ]);

    $surveyService = app(SurveyService::class);
    $survey = $surveyService->createFromTemplate($client, $template, ucfirst($style).' Survey', $admin->id);
    $surveyService->publish($survey);

    $page = $this->get("/s/{$survey->slug}");

    $page->assertOk();
    $page->assertSee('https://example.com/red.png', false);
})->with(['carousel', 'list']);
