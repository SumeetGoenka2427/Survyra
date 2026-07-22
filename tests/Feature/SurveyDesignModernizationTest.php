<?php

use App\Models\Client;
use App\Models\QuestionType;
use App\Models\SurveyTemplate;
use App\Models\User;
use App\QuestionTypes\NpsQuestionType;
use App\Services\SurveyService;
use Database\Seeders\QuestionTypeSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(QuestionTypeSeeder::class);
    Role::findOrCreate('survyra_admin', 'web');
    Permission::findOrCreate('manage-surveys', 'web');
});

function styleAdmin(): User
{
    $user = User::factory()->create();
    $user->assignRole('survyra_admin');
    $user->givePermissionTo('manage-surveys');

    return $user;
}

test('an unknown stored display style falls back to the types first style instead of a missing view', function () {
    $nps = new NpsQuestionType();

    expect($nps->renderComponent('does-not-exist'))->toBe('survey-questions.nps.numbers');
    expect($nps->renderComponent('gradient'))->toBe('survey-questions.nps.gradient');
});

test('a templates layout and question display style carry over to a survey created from it', function () {
    $admin = styleAdmin();
    $client = Client::factory()->create();
    $template = SurveyTemplate::factory()->create(['layout' => 'conversational']);

    $nps = QuestionType::query()->where('key', 'nps')->firstOrFail();
    $template->questions()->create([
        'question_type_id' => $nps->id,
        'question_text' => 'How likely to recommend?',
        'order' => 0,
        'settings' => ['display_style' => 'emoji'],
    ]);

    $survey = app(SurveyService::class)->createFromTemplate($client, $template, 'Styled Survey', $admin->id);

    expect($survey->layout)->toBe('conversational');
    expect($survey->questions->first()->settings['display_style'])->toBe('emoji');
});

test('nps answers score identically regardless of which display style renders them', function () {
    $admin = styleAdmin();
    $client = Client::factory()->create(['whatsapp_number' => '+911234567890', 'support_number' => '+911234567890']);
    $template = SurveyTemplate::factory()->create();

    $nps = QuestionType::query()->where('key', 'nps')->firstOrFail();
    $template->questions()->create([
        'question_type_id' => $nps->id,
        'question_text' => 'How likely to recommend?',
        'order' => 0,
        'settings' => ['display_style' => 'gradient'],
    ]);

    $surveyService = app(SurveyService::class);
    $survey = $surveyService->createFromTemplate($client, $template, 'Gradient NPS Survey', $admin->id);
    $surveyService->publish($survey);
    $survey = $survey->fresh(['questions', 'thankyouRules']);
    $question = $survey->questions->first();

    $page = $this->get("/s/{$survey->slug}");
    $page->assertOk();
    $page->assertSee('sq-nps-gradient', false);

    $responseUuid = \App\Models\Response::query()->where('survey_id', $survey->id)->first()->uuid;

    $answer = $this->postJson("/s/{$survey->slug}/answer", [
        'response_uuid' => $responseUuid,
        'question_id' => $question->id,
        'answer' => 9,
    ]);

    $answer->assertOk();
    $this->assertDatabaseHas('response_answers', [
        'question_id' => $question->id,
        'score' => 9,
    ]);
});

test('a survey using the conversational layout renders and answers through the conversational frame', function () {
    $admin = styleAdmin();
    $client = Client::factory()->create(['whatsapp_number' => '+911234567890', 'support_number' => '+911234567890']);
    $template = SurveyTemplate::factory()->create(['layout' => 'conversational']);

    $nps = QuestionType::query()->where('key', 'nps')->firstOrFail();
    $textarea = QuestionType::query()->where('key', 'textarea')->firstOrFail();
    $template->questions()->create(['question_type_id' => $nps->id, 'question_text' => 'Score?', 'order' => 0]);
    $template->questions()->create(['question_type_id' => $textarea->id, 'question_text' => 'Why?', 'order' => 1, 'is_required' => false]);

    $surveyService = app(SurveyService::class);
    $survey = $surveyService->createFromTemplate($client, $template, 'Conversational Survey', $admin->id);
    $surveyService->publish($survey);
    $survey = $survey->fresh(['questions', 'thankyouRules']);

    expect($survey->layout)->toBe('conversational');

    $page = $this->get("/s/{$survey->slug}");
    $page->assertOk();
    $page->assertSee('conv-question', false);

    $responseUuid = \App\Models\Response::query()->where('survey_id', $survey->id)->first()->uuid;
    $firstQuestion = $survey->questions->first();

    $answer = $this->postJson("/s/{$survey->slug}/answer", [
        'response_uuid' => $responseUuid,
        'question_id' => $firstQuestion->id,
        'answer' => 8,
    ]);

    $answer->assertOk();
    expect($answer->json('html'))->toContain('conv-question');
});

test('a survyra admin can set a questions display style through the builder and it persists', function () {
    $admin = styleAdmin();
    $client = Client::factory()->create();
    $template = SurveyTemplate::factory()->create();
    $survey = app(SurveyService::class)->createFromTemplate($client, $template, 'Builder Style Survey', $admin->id);

    $radio = QuestionType::query()->where('key', 'radio')->firstOrFail();

    $response = $this->actingAs($admin)->post("/admin/surveys/{$survey->id}/questions", [
        'question_type_id' => $radio->id,
        'question_text' => 'Which channel did you use?',
        'options_text' => "Store\nOnline",
        'display_style' => 'cards',
        'is_required' => '1',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('survey_questions', [
        'survey_id' => $survey->id,
        'question_text' => 'Which channel did you use?',
    ]);

    $question = \App\Models\SurveyQuestion::query()->where('question_text', 'Which channel did you use?')->firstOrFail();
    expect($question->settings['display_style'])->toBe('cards');
});
