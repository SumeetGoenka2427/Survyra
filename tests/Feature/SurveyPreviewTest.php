<?php

use App\Models\Client;
use App\Models\QuestionType;
use App\Models\SurveyTemplate;
use App\Models\SurveyTheme;
use App\Models\User;
use App\Services\SurveyService;
use Database\Seeders\QuestionTypeSeeder;
use Database\Seeders\SurveyThemeSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(QuestionTypeSeeder::class);
    $this->seed(SurveyThemeSeeder::class);
    Role::findOrCreate('survyra_admin', 'web');
    Permission::findOrCreate('manage-surveys', 'web');

    $this->admin = User::factory()->create();
    $this->admin->assignRole('survyra_admin');
    $this->admin->givePermissionTo('manage-surveys');
});

function previewTemplateWithLayout(string $layout, int $questionCount = 5): SurveyTemplate
{
    $template = SurveyTemplate::factory()->create(['layout' => $layout]);
    $textbox = QuestionType::query()->where('key', 'textbox')->firstOrFail();

    for ($i = 0; $i < $questionCount; $i++) {
        $template->questions()->create(['question_type_id' => $textbox->id, 'question_text' => "Question {$i}", 'order' => $i]);
    }

    return $template;
}

test('multi-step layout preview shows one question per step', function () {
    $template = previewTemplateWithLayout('multi_step');

    $page = $this->actingAs($this->admin)->get("/admin/survey-preview?template={$template->id}");

    $page->assertOk();
    $page->assertSee('Question 1 of 5');
    $page->assertSee('Question 0');
});

test('section-wizard layout preview groups questions three per step', function () {
    $template = previewTemplateWithLayout('section_wizard', 5);

    $page = $this->actingAs($this->admin)->get("/admin/survey-preview?template={$template->id}");

    $page->assertOk();
    $page->assertSee('Section 1 of 2');
    $page->assertSee('Question 0');
    $page->assertSee('Question 1');
    $page->assertSee('Question 2');
});

test('one-page layout preview shows every question in a single step', function () {
    $template = previewTemplateWithLayout('one_page', 4);

    $page = $this->actingAs($this->admin)->get("/admin/survey-preview?template={$template->id}");

    $page->assertOk();
    $page->assertSee('Question 0');
    $page->assertSee('Question 1');
    $page->assertSee('Question 2');
    $page->assertSee('Question 3');
    $page->assertSee('Finish');
});

test('card-based layout preview renders numbered cards for every question', function () {
    $template = previewTemplateWithLayout('card_based', 3);

    $page = $this->actingAs($this->admin)->get("/admin/survey-preview?template={$template->id}");

    $page->assertOk();
    $page->assertSeeInOrder(['card-based-question', 'Question 0', 'Question 1', 'Question 2']);
});

test('conversational layout preview shows one question per step like multi-step', function () {
    $template = previewTemplateWithLayout('conversational', 2);

    $page = $this->actingAs($this->admin)->get("/admin/survey-preview?template={$template->id}");

    $page->assertOk();
    $page->assertSee('Question 1 of 2');
});

test('previewing a theme alone with no survey or template falls back to sample questions', function () {
    $theme = SurveyTheme::query()->where('is_system', true)->firstOrFail();

    $page = $this->actingAs($this->admin)->get("/admin/survey-preview?theme={$theme->id}");

    $page->assertOk();
    $page->assertSee('How likely are you to recommend');
});

test('previewing a real published survey reflects its own layout', function () {
    $client = Client::factory()->create();
    $template = previewTemplateWithLayout('card_based', 3);
    $survey = app(SurveyService::class)->createFromTemplate($client, $template, 'Preview Survey', $this->admin->id);

    $page = $this->actingAs($this->admin)->get("/admin/survey-preview?survey={$survey->id}");

    $page->assertOk();
    $page->assertSee('card-based-question');
});
