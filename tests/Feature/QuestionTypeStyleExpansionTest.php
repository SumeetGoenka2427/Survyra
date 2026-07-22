<?php

use App\Models\Client;
use App\Models\QuestionType;
use App\Models\Response as SurveyResponse;
use App\Models\SurveyTemplate;
use App\Models\User;
use App\QuestionTypes\CesQuestionType;
use App\QuestionTypes\CsatQuestionType;
use App\QuestionTypes\DateQuestionType;
use App\QuestionTypes\DropdownQuestionType;
use App\QuestionTypes\EmailQuestionType;
use App\QuestionTypes\NumberQuestionType;
use App\QuestionTypes\PhoneQuestionType;
use App\Services\SurveyService;
use Database\Seeders\QuestionTypeSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(QuestionTypeSeeder::class);
    Role::findOrCreate('survyra_admin', 'web');
    Permission::findOrCreate('manage-surveys', 'web');
});

test('each newly expanded question type falls back to its first style for an unknown stored style', function () {
    expect((new CsatQuestionType())->renderComponent('nope'))->toBe('survey-questions.csat.numbers');
    expect((new CesQuestionType())->renderComponent('nope'))->toBe('survey-questions.ces.numbers');
    expect((new DropdownQuestionType())->renderComponent('nope'))->toBe('survey-questions.dropdown.select');
    expect((new NumberQuestionType())->renderComponent('nope'))->toBe('survey-questions.number.modern');
    expect((new EmailQuestionType())->renderComponent('nope'))->toBe('survey-questions.email.modern');
    expect((new PhoneQuestionType())->renderComponent('nope'))->toBe('survey-questions.phone.modern');
    expect((new DateQuestionType())->renderComponent('nope'))->toBe('survey-questions.date.modern');
});

test('each newly expanded question type resolves its non-default styles', function () {
    expect((new CsatQuestionType())->renderComponent('gradient'))->toBe('survey-questions.csat.gradient');
    expect((new CesQuestionType())->renderComponent('circles'))->toBe('survey-questions.ces.circles');
    expect((new DropdownQuestionType())->renderComponent('pills'))->toBe('survey-questions.dropdown.pills');
    expect((new NumberQuestionType())->renderComponent('floating'))->toBe('survey-questions.number.floating');
    expect((new EmailQuestionType())->renderComponent('floating'))->toBe('survey-questions.email.floating');
    expect((new PhoneQuestionType())->renderComponent('floating'))->toBe('survey-questions.phone.floating');
    expect((new DateQuestionType())->renderComponent('labeled'))->toBe('survey-questions.date.labeled');
});

function publishedSurveyWithStyledQuestion(string $typeKey, ?string $style, array $extraSettings = []): array
{
    $admin = User::factory()->create();
    $admin->assignRole('survyra_admin');
    $admin->givePermissionTo('manage-surveys');

    $client = Client::factory()->create();
    $template = SurveyTemplate::factory()->create();
    $questionType = QuestionType::query()->where('key', $typeKey)->firstOrFail();

    $settings = $extraSettings;
    if ($style !== null) {
        $settings['display_style'] = $style;
    }

    $options = in_array($typeKey, ['dropdown', 'radio', 'checkbox'], true) ? ['Yes', 'No'] : null;

    $template->questions()->create([
        'question_type_id' => $questionType->id,
        'question_text' => 'Sample question',
        'order' => 0,
        'settings' => $settings,
        'options' => $options,
    ]);

    $surveyService = app(SurveyService::class);
    $survey = $surveyService->createFromTemplate($client, $template, ucfirst($typeKey).' Style Survey', $admin->id);
    $surveyService->publish($survey);

    return [$survey->fresh(['questions', 'thankyouRules']), $admin];
}

test('a csat question rendered with the gradient style shows the gradient markup on the public page', function () {
    [$survey] = publishedSurveyWithStyledQuestion('csat', 'gradient');

    $page = $this->get("/s/{$survey->slug}");

    $page->assertOk();
    $page->assertSee('sq-nps-gradient', false);
});

test('a dropdown question rendered with the buttons style shows radio-style markup instead of a select', function () {
    [$survey] = publishedSurveyWithStyledQuestion('dropdown', 'buttons');

    $page = $this->get("/s/{$survey->slug}");

    $page->assertOk();
    $page->assertDontSee('<select', false);
    $page->assertSee('sq-options', false);
});

test('an email question rendered with the floating style shows the floating label markup', function () {
    [$survey] = publishedSurveyWithStyledQuestion('email', 'floating');

    $page = $this->get("/s/{$survey->slug}");

    $page->assertOk();
    $page->assertSee('sq-floating', false);
});

test('a date question rendered with the labeled style shows a static label above the input', function () {
    [$survey] = publishedSurveyWithStyledQuestion('date', 'labeled');

    $page = $this->get("/s/{$survey->slug}");

    $page->assertOk();
    $page->assertSee('Select a date');
});

test('a csat answer still scores correctly regardless of which style rendered it', function () {
    [$survey] = publishedSurveyWithStyledQuestion('csat', 'circles');
    $question = $survey->questions->first();

    $this->get("/s/{$survey->slug}");
    $responseUuid = SurveyResponse::query()->where('survey_id', $survey->id)->first()->uuid;

    $answer = $this->postJson("/s/{$survey->slug}/answer", [
        'response_uuid' => $responseUuid,
        'question_id' => $question->id,
        'answer' => 4,
    ]);

    $answer->assertOk();
    $this->assertDatabaseHas('response_answers', ['question_id' => $question->id, 'score' => 4]);
});
