<?php

use App\Models\Client;
use App\Models\Survey;
use App\Models\SurveyTheme;
use App\Models\User;
use Database\Seeders\QuestionTypeSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(QuestionTypeSeeder::class);
    Role::findOrCreate('survyra_admin', 'web');
    Permission::findOrCreate('manage-surveys', 'web');
});

function aiSurveyAdmin(): User
{
    $user = User::factory()->create();
    $user->assignRole('survyra_admin');
    $user->givePermissionTo('manage-surveys');

    return $user;
}

test('the create page shows AI-drafted questions and pre-fills the title', function () {
    $admin = aiSurveyAdmin();
    $questions = [
        ['type' => 'nps', 'text' => 'How likely are you to recommend us?', 'is_required' => true],
        ['type' => 'radio', 'text' => 'How did you hear about us?', 'options' => ['Social Media', 'Friend'], 'is_required' => false],
    ];

    $response = $this->actingAs($admin)->get('/admin/surveys/create?'.http_build_query([
        'title' => 'AI Drafted Survey',
        'questions' => json_encode($questions),
        'source' => 'ai_generator',
    ]));

    $response->assertOk();
    $response->assertSee('AI Drafted Survey', false);
    $response->assertSee('2 questions from the AI Survey Generator', false);
});

test('submitting the create form with ai_questions actually creates those questions on the survey', function () {
    $admin = aiSurveyAdmin();
    $client = Client::factory()->create();
    $questions = [
        ['type' => 'nps', 'text' => 'How likely are you to recommend us?', 'is_required' => true],
        ['type' => 'radio', 'text' => 'How did you hear about us?', 'options' => ['Social Media', 'Friend'], 'is_required' => false],
        ['type' => 'textarea', 'text' => 'Anything else?', 'is_required' => false],
    ];

    $response = $this->actingAs($admin)->post('/admin/surveys', [
        'client_id' => $client->id,
        'title' => 'AI Drafted Survey',
        'mode' => 'blank',
        'layout' => 'multi_step',
        'ai_questions' => json_encode($questions),
    ]);

    $survey = Survey::where('title', 'AI Drafted Survey')->firstOrFail();
    $response->assertRedirect(route('admin.surveys.edit', $survey));

    expect($survey->questions)->toHaveCount(3);
    expect($survey->questions->pluck('question_text')->all())->toBe([
        'How likely are you to recommend us?',
        'How did you hear about us?',
        'Anything else?',
    ]);
    expect($survey->questions->firstWhere('question_text', 'How did you hear about us?')->options)
        ->toBe(['Social Media', 'Friend']);
});

test('an invalid question type in the AI draft is skipped instead of crashing', function () {
    $admin = aiSurveyAdmin();
    $client = Client::factory()->create();
    $questions = [
        ['type' => 'nps', 'text' => 'Real question', 'is_required' => true],
        ['type' => 'not_a_real_type', 'text' => 'Should be skipped'],
        ['type' => 'radio', 'text' => ''], // blank text, should be skipped too
    ];

    $this->actingAs($admin)->post('/admin/surveys', [
        'client_id' => $client->id,
        'title' => 'Partially Valid AI Survey',
        'mode' => 'blank',
        'layout' => 'multi_step',
        'ai_questions' => json_encode($questions),
    ]);

    $survey = Survey::where('title', 'Partially Valid AI Survey')->firstOrFail();
    expect($survey->questions)->toHaveCount(1);
    expect($survey->questions->first()->question_text)->toBe('Real question');
});

test('a system theme can be selected at survey creation time', function () {
    $admin = aiSurveyAdmin();
    $client = Client::factory()->create();
    $theme = SurveyTheme::query()->create(['name' => 'Halo', 'is_system' => true]);

    $createPage = $this->actingAs($admin)->get('/admin/surveys/create');
    $createPage->assertOk();
    $createPage->assertSee('Halo');

    $this->actingAs($admin)->post('/admin/surveys', [
        'client_id' => $client->id,
        'title' => 'Themed Survey',
        'mode' => 'blank',
        'layout' => 'multi_step',
        'theme_id' => $theme->id,
    ])->assertRedirect();

    $survey = Survey::where('title', 'Themed Survey')->firstOrFail();
    expect($survey->theme_id)->toBe($theme->id);
});

test('creating a survey without a theme selection leaves it unthemed', function () {
    $admin = aiSurveyAdmin();
    $client = Client::factory()->create();

    $this->actingAs($admin)->post('/admin/surveys', [
        'client_id' => $client->id,
        'title' => 'Unthemed Survey',
        'mode' => 'blank',
        'layout' => 'multi_step',
    ])->assertRedirect();

    $survey = Survey::where('title', 'Unthemed Survey')->firstOrFail();
    expect($survey->theme_id)->toBeNull();
});

test('creating a survey without ai_questions behaves exactly as before', function () {
    $admin = aiSurveyAdmin();
    $client = Client::factory()->create();

    $this->actingAs($admin)->post('/admin/surveys', [
        'client_id' => $client->id,
        'title' => 'Plain Blank Survey',
        'mode' => 'blank',
        'layout' => 'multi_step',
    ])->assertRedirect();

    $survey = Survey::where('title', 'Plain Blank Survey')->firstOrFail();
    expect($survey->questions)->toHaveCount(0);
});
