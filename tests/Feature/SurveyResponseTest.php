<?php

use App\Models\Client;
use App\Models\QuestionType;
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

function publishedSurveyWithLogic(): Survey
{
    $client = Client::factory()->create(['whatsapp_number' => '+911234567890', 'support_number' => '+911234567890']);
    $template = SurveyTemplate::factory()->create();

    $nps = QuestionType::query()->where('key', 'nps')->firstOrFail();
    $radio = QuestionType::query()->where('key', 'radio')->firstOrFail();
    $textarea = QuestionType::query()->where('key', 'textarea')->firstOrFail();

    $template->questions()->create(['question_type_id' => $nps->id, 'question_text' => 'How likely to recommend?', 'order' => 0]);
    $template->questions()->create(['question_type_id' => $radio->id, 'question_text' => 'Was the wait time ok?', 'options' => ['Yes', 'No'], 'order' => 1]);
    $template->questions()->create(['question_type_id' => $textarea->id, 'question_text' => 'Tell us about the wait', 'order' => 2, 'is_required' => false]);
    $template->questions()->create(['question_type_id' => $textarea->id, 'question_text' => 'Any other comments?', 'order' => 3, 'is_required' => false]);

    $admin = User::factory()->create();
    $admin->assignRole('survyra_admin');
    $admin->givePermissionTo('manage-surveys');

    $surveyService = app(SurveyService::class);
    $survey = $surveyService->createFromTemplate($client, $template, 'Test Survey', $admin->id);
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

test('visiting a published survey starts a response and shows the first question', function () {
    $survey = publishedSurveyWithLogic();

    $response = $this->get("/s/{$survey->slug}");

    $response->assertOk();
    $response->assertSee('How likely to recommend?');
    $this->assertDatabaseHas('responses', ['survey_id' => $survey->id, 'status' => 'started']);
});

test('a hidden question only appears when its show rule condition is met', function () {
    $survey = publishedSurveyWithLogic();
    [$nps, $wait] = $survey->questions->all();

    $this->get("/s/{$survey->slug}");
    $responseUuid = \App\Models\Response::query()->where('survey_id', $survey->id)->first()->uuid;

    // Answer NPS, then answer "Was the wait ok?" = Yes -> complaint question should stay hidden.
    $this->postJson("/s/{$survey->slug}/answer", ['response_uuid' => $responseUuid, 'question_id' => $nps->id, 'answer' => 8]);
    $second = $this->postJson("/s/{$survey->slug}/answer", ['response_uuid' => $responseUuid, 'question_id' => $wait->id, 'answer' => 'Yes']);

    $second->assertOk();
    expect($second->json('html'))->toContain('Any other comments?');
    expect($second->json('html'))->not->toContain('Tell us about the wait');
});

test('a hidden question appears when its show rule condition is met', function () {
    $survey = publishedSurveyWithLogic();
    [$nps, $wait] = $survey->questions->all();

    $this->get("/s/{$survey->slug}");
    $responseUuid = \App\Models\Response::query()->where('survey_id', $survey->id)->first()->uuid;

    $this->postJson("/s/{$survey->slug}/answer", ['response_uuid' => $responseUuid, 'question_id' => $nps->id, 'answer' => 3]);
    $second = $this->postJson("/s/{$survey->slug}/answer", ['response_uuid' => $responseUuid, 'question_id' => $wait->id, 'answer' => 'No']);

    $second->assertOk();
    expect($second->json('html'))->toContain('Tell us about the wait');
});

test('an out of range answer is rejected and nothing is saved', function () {
    $survey = publishedSurveyWithLogic();
    $nps = $survey->questions->first();

    $this->get("/s/{$survey->slug}");
    $responseUuid = \App\Models\Response::query()->where('survey_id', $survey->id)->first()->uuid;

    $response = $this->postJson("/s/{$survey->slug}/answer", ['response_uuid' => $responseUuid, 'question_id' => $nps->id, 'answer' => 99]);

    $response->assertStatus(422);
    $this->assertDatabaseMissing('response_answers', ['question_id' => $nps->id]);
});

test('completing a survey with a high nps score shows the positive thank-you screen with no google review lock', function () {
    $survey = publishedSurveyWithLogic();
    [$nps, $wait, , $comments] = $survey->questions->all();

    $this->get("/s/{$survey->slug}");
    $responseUuid = \App\Models\Response::query()->where('survey_id', $survey->id)->first()->uuid;

    $this->postJson("/s/{$survey->slug}/answer", ['response_uuid' => $responseUuid, 'question_id' => $nps->id, 'answer' => 10]);
    $this->postJson("/s/{$survey->slug}/answer", ['response_uuid' => $responseUuid, 'question_id' => $wait->id, 'answer' => 'Yes']);
    $final = $this->postJson("/s/{$survey->slug}/answer", ['response_uuid' => $responseUuid, 'question_id' => $comments->id, 'answer' => 'great!']);

    $final->assertOk();
    expect($final->json('done'))->toBeTrue();

    $this->assertDatabaseHas('responses', ['uuid' => $responseUuid, 'status' => 'completed', 'sentiment' => 'positive']);
});

test('completing a survey with a low nps score shows the negative thank-you screen and never a google review button', function () {
    $survey = publishedSurveyWithLogic();
    [$nps, $wait, $complaint, $comments] = $survey->questions->all();

    $this->get("/s/{$survey->slug}");
    $responseUuid = \App\Models\Response::query()->where('survey_id', $survey->id)->first()->uuid;

    $this->postJson("/s/{$survey->slug}/answer", ['response_uuid' => $responseUuid, 'question_id' => $nps->id, 'answer' => 2]);
    $this->postJson("/s/{$survey->slug}/answer", ['response_uuid' => $responseUuid, 'question_id' => $wait->id, 'answer' => 'No']);
    $this->postJson("/s/{$survey->slug}/answer", ['response_uuid' => $responseUuid, 'question_id' => $complaint->id, 'answer' => 'too slow']);
    $final = $this->postJson("/s/{$survey->slug}/answer", ['response_uuid' => $responseUuid, 'question_id' => $comments->id, 'answer' => 'meh']);

    $final->assertOk();
    $this->assertDatabaseHas('responses', ['uuid' => $responseUuid, 'status' => 'completed', 'sentiment' => 'negative']);
    expect($final->json('html'))->not->toContain('Leave a Google Review');
    expect($final->json('html'))->toContain('WhatsApp');
});

test('returning with the resume cookie continues the same response', function () {
    $survey = publishedSurveyWithLogic();

    $first = $this->get("/s/{$survey->slug}");
    $cookieName = 'survyra_response_'.$survey->slug;
    $uuid = $first->getCookie($cookieName)->getValue();

    expect(\App\Models\Response::query()->where('survey_id', $survey->id)->count())->toBe(1);

    // withCookie() re-encrypts for the outgoing request, same as a real browser
    // echoing back the encrypted Set-Cookie value it was given.
    $this->withCookie($cookieName, $uuid)->get("/s/{$survey->slug}");

    expect(\App\Models\Response::query()->where('survey_id', $survey->id)->count())->toBe(1);
});

test('a missing resume cookie starts a brand new response', function () {
    $survey = publishedSurveyWithLogic();

    $this->get("/s/{$survey->slug}");
    expect(\App\Models\Response::query()->where('survey_id', $survey->id)->count())->toBe(1);

    $this->get("/s/{$survey->slug}");
    expect(\App\Models\Response::query()->where('survey_id', $survey->id)->count())->toBe(2);
});

test('a survey with no responses can still have its questions edited', function () {
    $survey = publishedSurveyWithLogic();
    $admin = User::query()->whereHas('roles', fn ($q) => $q->where('name', 'survyra_admin'))->first();
    $question = $survey->questions->first();

    $this->actingAs($admin)->put("/admin/surveys/{$survey->id}/questions/{$question->id}", [
        'question_type_id' => $question->question_type_id,
        'question_text' => 'Updated text',
        'is_required' => '1',
    ])->assertRedirect();

    expect($question->fresh()->question_text)->toBe('Updated text');
});

test('a survey with at least one response has its questions locked', function () {
    $survey = publishedSurveyWithLogic();
    $admin = User::query()->whereHas('roles', fn ($q) => $q->where('name', 'survyra_admin'))->first();
    $question = $survey->questions->first();

    $this->get("/s/{$survey->slug}");

    $this->actingAs($admin)->put("/admin/surveys/{$survey->id}/questions/{$question->id}", [
        'question_type_id' => $question->question_type_id,
        'question_text' => 'Should not save',
        'is_required' => '1',
    ])->assertForbidden();

    expect($question->fresh()->question_text)->not->toBe('Should not save');
});

test('mark-abandoned command flips stale in-progress responses', function () {
    $survey = publishedSurveyWithLogic();

    $stale = \App\Models\Response::query()->create([
        'client_id' => $survey->client_id,
        'survey_id' => $survey->id,
        'status' => 'in_progress',
        'started_at' => now()->subDays(2),
    ]);
    $stale->timestamps = false;
    $stale->updated_at = now()->subHours(48);
    $stale->save();

    $fresh = \App\Models\Response::query()->create([
        'client_id' => $survey->client_id,
        'survey_id' => $survey->id,
        'status' => 'in_progress',
        'started_at' => now(),
    ]);

    $this->artisan('responses:mark-abandoned')->assertSuccessful();

    expect($stale->fresh()->status)->toBe('abandoned');
    expect($fresh->fresh()->status)->toBe('in_progress');
});
