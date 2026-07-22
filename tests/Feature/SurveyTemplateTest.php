<?php

use App\Models\ClientUser;
use App\Models\QuestionType;
use App\Models\SurveyTemplate;
use App\Models\User;
use Database\Seeders\QuestionTypeSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(QuestionTypeSeeder::class);

    Role::findOrCreate('survyra_admin', 'web');
    Permission::findOrCreate('manage-surveys', 'web');
});

function makeSurveyAdmin(): User
{
    $user = User::factory()->create();
    $user->assignRole('survyra_admin');
    $user->givePermissionTo('manage-surveys');

    return $user;
}

test('a survyra admin can create a template and add a question to it', function () {
    $admin = makeSurveyAdmin();

    $response = $this->actingAs($admin)->post('/admin/templates', [
        'name' => 'Test Template',
        'industry_category' => 'Retail',
        'description' => 'A test template',
    ]);

    $template = SurveyTemplate::query()->where('name', 'Test Template')->firstOrFail();
    $response->assertRedirect(route('admin.templates.edit', $template));

    $npsType = QuestionType::query()->where('key', 'nps')->firstOrFail();

    $this->actingAs($admin)->post("/admin/templates/{$template->id}/questions", [
        'question_type_id' => $npsType->id,
        'question_text' => 'How likely are you to recommend us?',
        'is_required' => '1',
    ])->assertRedirect();

    $this->assertDatabaseHas('survey_template_questions', [
        'survey_template_id' => $template->id,
        'question_text' => 'How likely are you to recommend us?',
    ]);
});

test('choice question options are parsed from newline text into an array', function () {
    $admin = makeSurveyAdmin();
    $template = SurveyTemplate::factory()->create();
    $radioType = QuestionType::query()->where('key', 'radio')->firstOrFail();

    $this->actingAs($admin)->post("/admin/templates/{$template->id}/questions", [
        'question_type_id' => $radioType->id,
        'question_text' => 'Pick one',
        'options_text' => "Yes\nNo\nMaybe",
        'is_required' => '1',
    ]);

    $question = $template->questions()->firstOrFail();

    expect($question->options)->toBe(['Yes', 'No', 'Maybe']);
});

test('questions can be reordered with move up and move down', function () {
    $admin = makeSurveyAdmin();
    $template = SurveyTemplate::factory()->create();
    $type = QuestionType::query()->where('key', 'textbox')->firstOrFail();

    $first = $template->questions()->create(['question_type_id' => $type->id, 'question_text' => 'First', 'order' => 0]);
    $second = $template->questions()->create(['question_type_id' => $type->id, 'question_text' => 'Second', 'order' => 1]);

    $this->actingAs($admin)->patch("/admin/templates/{$template->id}/questions/{$second->id}/move-up");

    expect($first->fresh()->order)->toBe(1);
    expect($second->fresh()->order)->toBe(0);
});

test('save as new template duplicates the template and its questions without touching the original', function () {
    $admin = makeSurveyAdmin();
    $template = SurveyTemplate::factory()->create(['name' => 'Original']);
    $type = QuestionType::query()->where('key', 'textbox')->firstOrFail();
    $template->questions()->create(['question_type_id' => $type->id, 'question_text' => 'Q1', 'order' => 0]);

    $this->actingAs($admin)->post("/admin/templates/{$template->id}/duplicate");

    $copy = SurveyTemplate::query()->where('name', 'Original (Copy)')->firstOrFail();

    expect($copy->questions)->toHaveCount(1);
    expect($template->fresh()->questions)->toHaveCount(1);
    expect(SurveyTemplate::query()->count())->toBe(2);
});

test('a user without the manage-surveys permission cannot access templates', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin/templates')->assertForbidden();
});

test('the templates ajax data endpoint filters by search', function () {
    $admin = makeSurveyAdmin();
    SurveyTemplate::factory()->create(['name' => 'Restaurant Feedback']);
    SurveyTemplate::factory()->create(['name' => 'Clinic Checkup']);

    $result = $this->actingAs($admin)->getJson('/admin/templates/data?search=Restaurant');

    $result->assertOk();
    expect($result->json('html'))->toContain('Restaurant Feedback');
    expect($result->json('html'))->not->toContain('Clinic Checkup');
});

test('duplicating a template through ajax returns the refreshed fragment and the new edit url', function () {
    $admin = makeSurveyAdmin();
    $template = SurveyTemplate::factory()->create(['name' => 'Original']);

    $result = $this->actingAs($admin)->postJson("/admin/templates/{$template->id}/duplicate");

    $result->assertOk();
    expect($result->json('html'))->toContain('Original (Copy)');
    expect($result->json('editUrl'))->not->toBeNull();
});

test('a client portal user is redirected away from template routes', function () {
    $clientUser = ClientUser::factory()->create();

    $this->actingAs($clientUser, 'client')->get('/admin/templates')->assertRedirect(route('admin.login'));
});
