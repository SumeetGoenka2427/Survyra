<?php

use App\Models\Client;
use App\Models\ClientUser;
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
});

function makeSurveyBuilderAdmin(): User
{
    $user = User::factory()->create();
    $user->assignRole('survyra_admin');
    $user->givePermissionTo('manage-surveys');

    return $user;
}

function buildTemplateWithQuestions(): SurveyTemplate
{
    $template = SurveyTemplate::factory()->create();
    $nps = QuestionType::query()->where('key', 'nps')->firstOrFail();
    $textarea = QuestionType::query()->where('key', 'textarea')->firstOrFail();

    $template->questions()->create(['question_type_id' => $nps->id, 'question_text' => 'How likely to recommend?', 'order' => 0]);
    $template->questions()->create(['question_type_id' => $textarea->id, 'question_text' => 'Comments?', 'order' => 1, 'is_required' => false]);

    return $template;
}

test('creating a survey from a template clones every question', function () {
    $admin = makeSurveyBuilderAdmin();
    $client = Client::factory()->create();
    $template = buildTemplateWithQuestions();

    $survey = app(SurveyService::class)->createFromTemplate($client, $template, 'Test Survey', $admin->id);

    expect($survey->questions)->toHaveCount(2);
    expect($survey->questions->pluck('question_text')->all())->toBe(['How likely to recommend?', 'Comments?']);
    expect($survey->survey_template_id)->toBe($template->id);
    expect($template->fresh()->questions)->toHaveCount(2);
});

test('creating a survey auto-picks the NPS question as primary score and seeds 3 thank-you rules', function () {
    $admin = makeSurveyBuilderAdmin();
    $client = Client::factory()->create();
    $template = buildTemplateWithQuestions();

    $survey = app(SurveyService::class)->createFromTemplate($client, $template, 'Test Survey', $admin->id);

    $npsQuestion = $survey->questions->firstWhere('question_text', 'How likely to recommend?');
    expect($survey->primary_score_question_id)->toBe($npsQuestion->id);

    expect($survey->thankyouRules)->toHaveCount(3);
    $negative = $survey->thankyouRules->firstWhere('sentiment', 'negative');
    expect($negative->show_google_review)->toBeFalse();
    expect($negative->show_complaint_form)->toBeTrue();
    $positive = $survey->thankyouRules->firstWhere('sentiment', 'positive');
    expect($positive->show_google_review)->toBeTrue();
});

test('editing a survey question never touches the source template', function () {
    $admin = makeSurveyBuilderAdmin();
    $client = Client::factory()->create();
    $template = buildTemplateWithQuestions();
    $survey = app(SurveyService::class)->createFromTemplate($client, $template, 'Test Survey', $admin->id);

    $question = $survey->questions->first();

    $this->actingAs($admin)->put("/admin/surveys/{$survey->id}/questions/{$question->id}", [
        'question_type_id' => $question->question_type_id,
        'question_text' => 'Changed on the survey only',
        'is_required' => '1',
    ]);

    expect($question->fresh()->question_text)->toBe('Changed on the survey only');
    expect($template->questions()->first()->question_text)->toBe('How likely to recommend?');
});

test('the negative thank-you rule can never have show_google_review enabled', function () {
    $admin = makeSurveyBuilderAdmin();
    $client = Client::factory()->create();
    $template = buildTemplateWithQuestions();
    $survey = app(SurveyService::class)->createFromTemplate($client, $template, 'Test Survey', $admin->id);

    $response = $this->actingAs($admin)->put("/admin/surveys/{$survey->id}/thankyou-rules/negative", [
        'thank_you_message' => 'Sorry to hear that',
        'show_google_review' => '1',
    ]);

    $response->assertSessionHasErrors('show_google_review');
    expect($survey->thankyouRules()->where('sentiment', 'negative')->first()->show_google_review)->toBeFalse();
});

test('publishing requires at least one question', function () {
    $admin = makeSurveyBuilderAdmin();
    $client = Client::factory()->create();
    $template = buildTemplateWithQuestions();
    $survey = app(SurveyService::class)->createFromTemplate($client, $template, 'Test Survey', $admin->id);
    $survey->questions()->delete();

    expect(fn () => app(SurveyService::class)->publish($survey->fresh()))
        ->toThrow(InvalidArgumentException::class);
});

test('publishing a valid survey sets status published_at and an 8 character slug', function () {
    $admin = makeSurveyBuilderAdmin();
    $client = Client::factory()->create();
    $template = buildTemplateWithQuestions();
    $survey = app(SurveyService::class)->createFromTemplate($client, $template, 'Test Survey', $admin->id);

    $this->actingAs($admin)->post("/admin/surveys/{$survey->id}/publish");

    $survey->refresh();
    expect($survey->status)->toBe('published');
    expect($survey->published_at)->not->toBeNull();
    expect($survey->slug)->toHaveLength(8);
});

test('a draft survey can be deleted but a published survey cannot', function () {
    $admin = makeSurveyBuilderAdmin();
    $client = Client::factory()->create();
    $template = buildTemplateWithQuestions();
    $survey = app(SurveyService::class)->createFromTemplate($client, $template, 'Test Survey', $admin->id);

    app(SurveyService::class)->publish($survey);

    expect(fn () => app(SurveyService::class)->delete($survey->fresh()))
        ->toThrow(InvalidArgumentException::class);

    app(SurveyService::class)->archive($survey);
    $survey2 = app(SurveyService::class)->createFromTemplate($client, buildTemplateWithQuestions(), 'Draft Survey', $admin->id);
    app(SurveyService::class)->delete($survey2);

    $this->assertSoftDeleted($survey2);
});

test('the surveys ajax data endpoint filters by client and status', function () {
    $admin = makeSurveyBuilderAdmin();
    $client = Client::factory()->create();
    $surveyA = app(SurveyService::class)->createFromTemplate($client, buildTemplateWithQuestions(), 'Alpha Survey', $admin->id);
    app(SurveyService::class)->createFromTemplate($client, buildTemplateWithQuestions(), 'Beta Survey', $admin->id);
    app(SurveyService::class)->publish($surveyA);

    $result = $this->actingAs($admin)->getJson('/admin/surveys/data?status=published');

    $result->assertOk();
    expect($result->json('html'))->toContain('Alpha Survey');
    expect($result->json('html'))->not->toContain('Beta Survey');
});

test('publishing and archiving a survey through ajax returns the refreshed fragment', function () {
    $admin = makeSurveyBuilderAdmin();
    $client = Client::factory()->create();
    $survey = app(SurveyService::class)->createFromTemplate($client, buildTemplateWithQuestions(), 'AJAX Survey', $admin->id);

    $publish = $this->actingAs($admin)->postJson("/admin/surveys/{$survey->id}/publish");
    $publish->assertOk();
    expect($publish->json('html'))->toContain('Published');
    expect($survey->fresh()->status)->toBe('published');

    $archive = $this->actingAs($admin)->postJson("/admin/surveys/{$survey->id}/archive");
    $archive->assertOk();
    expect($archive->json('html'))->toContain('Archived');
    expect($survey->fresh()->status)->toBe('archived');
});

test('attempting to delete a published survey through ajax returns a 422 instead of a server error', function () {
    $admin = makeSurveyBuilderAdmin();
    $client = Client::factory()->create();
    $survey = app(SurveyService::class)->createFromTemplate($client, buildTemplateWithQuestions(), 'Locked Survey', $admin->id);
    app(SurveyService::class)->publish($survey);

    $result = $this->actingAs($admin)->deleteJson("/admin/surveys/{$survey->id}");

    $result->assertStatus(422);
    $this->assertNotSoftDeleted($survey);
});

test('duplicating a theme for a client creates a client scoped copy and assigns it to the survey', function () {
    $admin = makeSurveyBuilderAdmin();
    $client = Client::factory()->create();
    $template = buildTemplateWithQuestions();
    $survey = app(SurveyService::class)->createFromTemplate($client, $template, 'Test Survey', $admin->id);
    $systemTheme = SurveyTheme::query()->where('is_system', true)->first();

    $this->actingAs($admin)->post("/admin/surveys/{$survey->id}/theme/{$systemTheme->id}/duplicate");

    $survey->refresh();
    expect($survey->theme)->not->toBeNull();
    expect($survey->theme->client_id)->toBe($client->id);
    expect($survey->theme->is_system)->toBeFalse();
    expect($survey->theme->primary_color)->toBe($systemTheme->primary_color);
});

test('downloading the qr code works for published surveys and is rejected for drafts', function () {
    $admin = makeSurveyBuilderAdmin();
    $client = Client::factory()->create();
    $template = buildTemplateWithQuestions();
    $survey = app(SurveyService::class)->createFromTemplate($client, $template, 'Test Survey', $admin->id);

    $this->actingAs($admin)->get("/admin/surveys/{$survey->id}/qr")->assertStatus(422);

    app(SurveyService::class)->publish($survey);

    $response = $this->actingAs($admin)->get("/admin/surveys/{$survey->id}/qr");
    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toBe('image/svg+xml');
});

test('a user without manage-surveys permission cannot access surveys', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin/surveys')->assertForbidden();
});

test('a client portal user is redirected away from survey routes', function () {
    $clientUser = ClientUser::factory()->create();

    $this->actingAs($clientUser, 'client')->get('/admin/surveys')->assertRedirect(route('admin.login'));
});
