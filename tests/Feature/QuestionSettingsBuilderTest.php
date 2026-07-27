<?php

use App\Models\Client;
use App\Models\QuestionType;
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

function settingsBuilderAdmin(): User
{
    $user = User::factory()->create();
    $user->assignRole('survyra_admin');
    $user->givePermissionTo('manage-surveys');

    return $user;
}

test('help_text is stored and shown to respondents regardless of question type', function () {
    $admin = settingsBuilderAdmin();
    $client = Client::factory()->create();
    $survey = app(SurveyService::class)->createBlank($client, 'Help Text Survey', 'multi_step', $admin->id);
    $radioTypeId = QuestionType::query()->where('key', 'radio')->firstOrFail()->id;

    $this->actingAs($admin)->post(route('admin.surveys.questions.store', $survey), [
        'question_type_id' => $radioTypeId,
        'question_text' => 'Pick one',
        'options_text' => "Yes\nNo",
        'help_text' => 'Choose the option that best describes you.',
        'is_required' => '1',
    ])->assertRedirect();

    $survey = $survey->fresh();
    $question = $survey->questions()->where('question_text', 'Pick one')->firstOrFail();
    expect($question->settings['help_text'])->toBe('Choose the option that best describes you.');

    app(SurveyService::class)->publish($survey);
    $page = $this->get("/s/{$survey->fresh()->slug}");

    $page->assertOk();
    $page->assertSee('Choose the option that best describes you.');
});

test('image_choice multiple selection and max_choices settings are stored and enforced', function () {
    $admin = settingsBuilderAdmin();
    $client = Client::factory()->create();
    $survey = app(SurveyService::class)->createBlank($client, 'Multi Image Survey', 'multi_step', $admin->id);
    $imageChoiceTypeId = QuestionType::query()->where('key', 'image_choice')->firstOrFail()->id;

    $this->actingAs($admin)->post(route('admin.surveys.questions.store', $survey), [
        'question_type_id' => $imageChoiceTypeId,
        'question_text' => 'Pick your favorites',
        'image_options_json' => json_encode([
            ['label' => 'Red', 'image' => '', 'value' => 'red'],
            ['label' => 'Blue', 'image' => '', 'value' => 'blue'],
            ['label' => 'Green', 'image' => '', 'value' => 'green'],
        ]),
        'multiple' => '1',
        'max_choices' => 2,
        'is_required' => '0',
    ])->assertRedirect();

    $question = $survey->fresh()->questions()->where('question_text', 'Pick your favorites')->firstOrFail();
    expect($question->settings['multiple'])->toBeTrue();
    expect($question->settings['max_choices'])->toEqual(2);

    $rules = $question->questionType->contract()->validationRules($question->settings, false);
    expect($rules)->toContain('max:2');
});

test('checkbox max_choices setting is enforced when answering a published survey', function () {
    $admin = settingsBuilderAdmin();
    $client = Client::factory()->create();
    $survey = app(SurveyService::class)->createBlank($client, 'Checkbox Survey', 'multi_step', $admin->id);
    $checkboxTypeId = QuestionType::query()->where('key', 'checkbox')->firstOrFail()->id;

    $this->actingAs($admin)->post(route('admin.surveys.questions.store', $survey), [
        'question_type_id' => $checkboxTypeId,
        'question_text' => 'Pick up to 1',
        'options_text' => "Red\nBlue\nGreen",
        'max_choices' => 1,
        'is_required' => '1',
    ])->assertRedirect();

    $survey = $survey->fresh();
    app(SurveyService::class)->publish($survey);
    $question = $survey->fresh()->questions()->first();

    $this->get("/s/{$survey->fresh()->slug}");
    $responseUuid = \App\Models\Response::query()->where('survey_id', $survey->id)->first()->uuid;

    $tooMany = $this->postJson("/s/{$survey->slug}/answer", [
        'response_uuid' => $responseUuid,
        'question_id' => $question->id,
        'answer' => ['Red', 'Blue'],
    ]);
    $tooMany->assertStatus(422);

    $justRight = $this->postJson("/s/{$survey->slug}/answer", [
        'response_uuid' => $responseUuid,
        'question_id' => $question->id,
        'answer' => ['Red'],
    ]);
    $justRight->assertOk();
});

test('file_upload max size and allowed types settings are stored from the builder', function () {
    $admin = settingsBuilderAdmin();
    $template = \App\Models\SurveyTemplate::factory()->create();
    $fileTypeId = QuestionType::query()->where('key', 'file_upload')->firstOrFail()->id;

    $this->actingAs($admin)->post(route('admin.templates.questions.store', $template), [
        'question_type_id' => $fileTypeId,
        'question_text' => 'Upload your ID',
        'max_file_size' => 2048,
        'allowed_types' => 'pdf, JPG, png',
        'is_required' => '1',
    ])->assertRedirect();

    $question = $template->fresh()->questions()->where('question_text', 'Upload your ID')->firstOrFail();
    expect($question->settings['max_file_size'])->toEqual(2048);
    expect($question->settings['allowed_types'])->toBe(['pdf', 'jpg', 'png']);
});
