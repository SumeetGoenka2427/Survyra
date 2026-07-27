<?php

use App\Models\Client;
use App\Models\QuestionType;
use App\Models\Response as SurveyResponse;
use App\Models\Survey;
use App\Models\SurveyTemplate;
use App\Models\User;
use App\Services\SurveyService;
use Database\Seeders\QuestionTypeSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(QuestionTypeSeeder::class);
});

function restorationSurvey(array $questionSpecs, string $layout = 'multi_step'): Survey
{
    $client = Client::factory()->create();
    $template = SurveyTemplate::factory()->create(['layout' => $layout]);

    foreach ($questionSpecs as $order => $spec) {
        $type = QuestionType::query()->where('key', $spec['type'])->firstOrFail();
        $template->questions()->create([
            'question_type_id' => $type->id,
            'question_text' => $spec['text'],
            'options' => $spec['options'] ?? null,
            'settings' => $spec['settings'] ?? [],
            'order' => $order,
            'is_required' => $spec['is_required'] ?? true,
        ]);
    }

    $admin = User::factory()->create();
    $survey = app(SurveyService::class)->createFromTemplate($client, $template, 'Restoration Survey', $admin->id);
    app(SurveyService::class)->publish($survey);

    return $survey->fresh('questions');
}

function startResponse(Survey $survey): array
{
    $page = test()->get("/s/{$survey->slug}?start=1");
    $uuid = SurveyResponse::query()->where('survey_id', $survey->id)->first()->uuid;

    return [$page, $uuid];
}

test('a radio answer is restored (checked) when navigating back to it', function () {
    $survey = restorationSurvey([
        ['type' => 'radio', 'text' => 'Was the wait time acceptable?', 'options' => ['Yes', 'No', 'Somewhat']],
        ['type' => 'textarea', 'text' => 'Filler', 'is_required' => false],
        ['type' => 'textarea', 'text' => 'Anything else?', 'is_required' => false],
    ]);
    [, $uuid] = startResponse($survey);
    $questions = $survey->questions;

    $this->postJson("/s/{$survey->slug}/answer", [
        'response_uuid' => $uuid,
        'question_id' => $questions[0]->id,
        'answer' => 'No',
    ])->assertOk();
    $this->postJson("/s/{$survey->slug}/answer", [
        'response_uuid' => $uuid,
        'question_id' => $questions[1]->id,
        'answer' => 'filler',
    ])->assertOk();

    $back = $this->postJson("/s/{$survey->slug}/back", ['response_uuid' => $uuid]);
    $back->assertOk();

    $html = $back->json('html');
    expect($html)->toContain('value="No"');
    // The "No" input specifically must carry the checked attribute.
    preg_match('/<input[^>]*value="No"[^>]*>/', $html, $noInputMatch);
    expect($noInputMatch[0] ?? '')->toContain('checked');
    preg_match('/<input[^>]*value="Yes"[^>]*>/', $html, $yesInputMatch);
    expect($yesInputMatch[0] ?? '')->not->toContain('checked');
});

test('navigating back and forward again without touching the field does not wipe the saved answer', function () {
    $survey = restorationSurvey([
        ['type' => 'radio', 'text' => 'Was the wait time acceptable?', 'options' => ['Yes', 'No', 'Somewhat']],
        ['type' => 'textarea', 'text' => 'Anything else?', 'is_required' => false],
    ]);
    [, $uuid] = startResponse($survey);
    $questions = $survey->questions;
    $response = SurveyResponse::where('uuid', $uuid)->firstOrFail();

    $this->postJson("/s/{$survey->slug}/answer", [
        'response_uuid' => $uuid,
        'question_id' => $questions[0]->id,
        'answer' => 'No',
    ])->assertOk();

    // Simulate the real client-side flow: back button re-submits whatever is
    // currently in the form before navigating - which, now that it's
    // restored, is still "No", not blank.
    $this->postJson("/s/{$survey->slug}/answer", [
        'response_uuid' => $uuid,
        'question_id' => $questions[0]->id,
        'answer' => 'No',
    ])->assertOk();

    $this->postJson("/s/{$survey->slug}/back", ['response_uuid' => $uuid])->assertOk();

    $answer = $response->answers()->where('question_id', $questions[0]->id)->first();
    expect($answer->answer)->toBe('No');
});

test('a checkbox answer array is restored when navigating back', function () {
    $survey = restorationSurvey([
        ['type' => 'checkbox', 'text' => 'Which apply?', 'options' => ['Red', 'Blue', 'Green']],
        ['type' => 'textarea', 'text' => 'Filler', 'is_required' => false],
        ['type' => 'textarea', 'text' => 'Anything else?', 'is_required' => false],
    ]);
    [, $uuid] = startResponse($survey);
    $questions = $survey->questions;

    $this->postJson("/s/{$survey->slug}/answer", [
        'response_uuid' => $uuid,
        'question_id' => $questions[0]->id,
        'answer' => ['Red', 'Green'],
    ])->assertOk();
    $this->postJson("/s/{$survey->slug}/answer", [
        'response_uuid' => $uuid,
        'question_id' => $questions[1]->id,
        'answer' => 'filler',
    ])->assertOk();

    $back = $this->postJson("/s/{$survey->slug}/back", ['response_uuid' => $uuid]);
    $html = $back->json('html');

    preg_match('/<input[^>]*value="Red"[^>]*>/', $html, $red);
    preg_match('/<input[^>]*value="Blue"[^>]*>/', $html, $blue);
    preg_match('/<input[^>]*value="Green"[^>]*>/', $html, $green);

    expect($red[0] ?? '')->toContain('checked');
    expect($blue[0] ?? '')->not->toContain('checked');
    expect($green[0] ?? '')->toContain('checked');
});

test('a textarea answer is restored as the fields value when navigating back', function () {
    $survey = restorationSurvey([
        ['type' => 'radio', 'text' => 'Was it good?', 'options' => ['Yes', 'No']],
        ['type' => 'textarea', 'text' => 'Tell us more', 'is_required' => false],
        ['type' => 'textarea', 'text' => 'Anything else?', 'is_required' => false],
    ]);
    [, $uuid] = startResponse($survey);
    $questions = $survey->questions;

    $this->postJson("/s/{$survey->slug}/answer", [
        'response_uuid' => $uuid,
        'question_id' => $questions[0]->id,
        'answer' => 'Yes',
    ])->assertOk();

    $this->postJson("/s/{$survey->slug}/answer", [
        'response_uuid' => $uuid,
        'question_id' => $questions[1]->id,
        'answer' => 'This is my detailed feedback.',
    ])->assertOk();

    $this->postJson("/s/{$survey->slug}/answer", [
        'response_uuid' => $uuid,
        'question_id' => $questions[2]->id,
        'answer' => 'filler',
    ])->assertOk();

    $back = $this->postJson("/s/{$survey->slug}/back", ['response_uuid' => $uuid]);

    $back->assertOk();
    expect($back->json('html'))->toContain('This is my detailed feedback.');
});

test('an nps scale answer is restored when navigating back', function () {
    $survey = restorationSurvey([
        ['type' => 'nps', 'text' => 'How likely to recommend?'],
        ['type' => 'textarea', 'text' => 'Filler', 'is_required' => false],
        ['type' => 'textarea', 'text' => 'Anything else?', 'is_required' => false],
    ]);
    [, $uuid] = startResponse($survey);
    $questions = $survey->questions;

    $this->postJson("/s/{$survey->slug}/answer", [
        'response_uuid' => $uuid,
        'question_id' => $questions[0]->id,
        'answer' => 9,
    ])->assertOk();
    $this->postJson("/s/{$survey->slug}/answer", [
        'response_uuid' => $uuid,
        'question_id' => $questions[1]->id,
        'answer' => 'filler',
    ])->assertOk();

    $back = $this->postJson("/s/{$survey->slug}/back", ['response_uuid' => $uuid]);
    $html = $back->json('html');

    preg_match('/<input[^>]*value="9"[^>]*>/', $html, $nine);
    expect($nine[0] ?? '')->toContain('checked');
});

test('a matrix answer is restored per row when navigating back', function () {
    $survey = restorationSurvey([
        ['type' => 'matrix', 'text' => 'Rate these', 'options' => ['Service', 'Price']],
        ['type' => 'textarea', 'text' => 'Filler', 'is_required' => false],
        ['type' => 'textarea', 'text' => 'Anything else?', 'is_required' => false],
    ]);
    [, $uuid] = startResponse($survey);
    $questions = $survey->questions;

    $this->postJson("/s/{$survey->slug}/answer", [
        'response_uuid' => $uuid,
        'question_id' => $questions[0]->id,
        'answer' => [0 => 4, 1 => 2],
    ])->assertOk();
    $this->postJson("/s/{$survey->slug}/answer", [
        'response_uuid' => $uuid,
        'question_id' => $questions[1]->id,
        'answer' => 'filler',
    ])->assertOk();

    $back = $this->postJson("/s/{$survey->slug}/back", ['response_uuid' => $uuid]);
    $html = $back->json('html');

    expect($html)->toContain('data-matrix-row="0"');
    // Row 0's "4" input and row 1's "2" input should each carry checked,
    // regardless of attribute order in the markup.
    preg_match('/<input(?=[^>]*\bdata-matrix-row="0")(?=[^>]*\bvalue="4")[^>]*>/', $html, $r0v4);
    preg_match('/<input(?=[^>]*\bdata-matrix-row="1")(?=[^>]*\bvalue="2")[^>]*>/', $html, $r1v2);
    expect($r0v4[0] ?? '')->toContain('checked');
    expect($r1v2[0] ?? '')->toContain('checked');
});

test('a ranking answer restores the previously saved order when navigating back', function () {
    $survey = restorationSurvey([
        ['type' => 'ranking', 'text' => 'Rank these', 'options' => ['Price', 'Quality', 'Support']],
        ['type' => 'textarea', 'text' => 'Filler', 'is_required' => false],
        ['type' => 'textarea', 'text' => 'Anything else?', 'is_required' => false],
    ]);
    [, $uuid] = startResponse($survey);
    $questions = $survey->questions;

    $this->postJson("/s/{$survey->slug}/answer", [
        'response_uuid' => $uuid,
        'question_id' => $questions[0]->id,
        'answer' => ['Quality', 'Support', 'Price'],
    ])->assertOk();
    $this->postJson("/s/{$survey->slug}/answer", [
        'response_uuid' => $uuid,
        'question_id' => $questions[1]->id,
        'answer' => 'filler',
    ])->assertOk();

    $back = $this->postJson("/s/{$survey->slug}/back", ['response_uuid' => $uuid]);
    $html = $back->json('html');

    expect($html)->toContain('value="Quality,Support,Price"');
});

test('a file_upload answer is not wiped when the respondent moves on without choosing a new file', function () {
    Storage::fake('local');

    $survey = restorationSurvey([
        ['type' => 'file_upload', 'text' => 'Upload your receipt', 'settings' => ['allowed_types' => ['pdf']]],
        ['type' => 'textarea', 'text' => 'Anything else?', 'is_required' => false],
    ]);
    [, $uuid] = startResponse($survey);
    $questions = $survey->questions;
    $response = SurveyResponse::where('uuid', $uuid)->firstOrFail();

    $file = UploadedFile::fake()->create('receipt.pdf', 100, 'application/pdf');
    $this->post("/s/{$survey->slug}/answer", [
        'response_uuid' => $uuid,
        'question_id' => $questions[0]->id,
        'answer' => $file,
    ], ['Accept' => 'application/json'])->assertOk();

    $original = $response->answers()->where('question_id', $questions[0]->id)->first()->answer;
    expect($original['original_name'])->toBe('receipt.pdf');

    // Respondent re-submits the same question with no new file attached
    // (e.g. after navigating back to it and clicking Next without
    // re-choosing a file, since the browser can't repopulate that input).
    $this->postJson("/s/{$survey->slug}/answer", [
        'response_uuid' => $uuid,
        'question_id' => $questions[0]->id,
        'answer' => null,
    ])->assertOk();

    $stillThere = $response->answers()->where('question_id', $questions[0]->id)->first()->answer;
    expect($stillThere['original_name'])->toBe('receipt.pdf');
});

test('going back to before the first question never crashes even without a welcome screen configured', function () {
    $survey = restorationSurvey([
        ['type' => 'radio', 'text' => 'Was it good?', 'options' => ['Yes', 'No']],
    ]);
    [, $uuid] = startResponse($survey);

    $back = $this->postJson("/s/{$survey->slug}/back", ['response_uuid' => $uuid]);

    $back->assertOk();
    expect($back->json('html'))->not->toBeEmpty();
});

test('one_page layout restores each questions previous answer after a logic-driven re-render', function () {
    $survey = restorationSurvey([
        ['type' => 'radio', 'text' => 'Show more?', 'options' => ['Yes', 'No'], 'is_required' => false],
        ['type' => 'textarea', 'text' => 'Comments', 'is_required' => false],
    ], layout: 'one_page');

    $questions = $survey->questions;
    $survey->logicRules()->create([
        'source_question_id' => $questions[0]->id,
        'conditions' => [['question_id' => $questions[0]->id, 'operator' => 'equals', 'value' => 'Yes']],
        'action' => 'hide',
        'target_question_id' => $questions[1]->id,
    ]);

    [, $uuid] = startResponse($survey);

    $this->postJson("/s/{$survey->slug}/answer", [
        'response_uuid' => $uuid,
        'question_id' => $questions[1]->id,
        'answer' => 'My comment',
    ])->assertOk();

    // Answering the radio triggers the logic rule, which re-renders the
    // whole one-page set - the textarea's own answer must survive that.
    $result = $this->postJson("/s/{$survey->slug}/answer", [
        'response_uuid' => $uuid,
        'question_id' => $questions[0]->id,
        'answer' => 'No',
    ]);

    $result->assertOk();
    expect($result->json('html'))->toContain('My comment');
});
