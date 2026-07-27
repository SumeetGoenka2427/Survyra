<?php

use App\Models\Client;
use App\Models\QuestionType;
use App\Models\Response as SurveyResponseModel;
use App\Models\ResponseUpload;
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

function fileUploadSurvey(Client $client): Survey
{
    $template = SurveyTemplate::factory()->create();
    $fileType = QuestionType::query()->where('key', 'file_upload')->firstOrFail();
    $template->questions()->create([
        'question_type_id' => $fileType->id,
        'question_text' => 'Upload your receipt',
        'settings' => ['allowed_types' => ['pdf', 'png'], 'max_file_size' => 5120],
        'order' => 0,
    ]);

    $admin = User::factory()->create();
    $survey = app(SurveyService::class)->createFromTemplate($client, $template, 'Receipt Survey', $admin->id);
    app(SurveyService::class)->publish($survey);

    return $survey->fresh(['questions']);
}

function startedResponse(Survey $survey): SurveyResponseModel
{
    return SurveyResponseModel::query()->create([
        'client_id' => $survey->client_id,
        'survey_id' => $survey->id,
        'status' => 'started',
        'started_at' => now(),
    ]);
}

test('uploading a real file for a file_upload question stores it and records a ResponseUpload row', function () {
    Storage::fake('local');

    $client = Client::factory()->create();
    $survey = fileUploadSurvey($client);
    $question = $survey->questions->first();
    $response = startedResponse($survey);

    $file = UploadedFile::fake()->create('receipt.pdf', 200, 'application/pdf');

    $result = $this->post("/s/{$survey->slug}/answer", [
        'response_uuid' => $response->uuid,
        'question_id' => $question->id,
        'answer' => $file,
    ]);

    $result->assertOk();

    $upload = ResponseUpload::query()->where('response_id', $response->id)->where('question_id', $question->id)->first();
    expect($upload)->not->toBeNull();
    expect($upload->original_name)->toBe('receipt.pdf');

    Storage::disk('local')->assertExists($upload->stored_path);

    $answer = $response->answers()->where('question_id', $question->id)->first();
    expect($answer->answer)->toMatchArray([
        'upload_id' => $upload->id,
        'original_name' => 'receipt.pdf',
    ]);
});

test('re-uploading for the same question replaces the previous file', function () {
    Storage::fake('local');

    $client = Client::factory()->create();
    $survey = fileUploadSurvey($client);
    $question = $survey->questions->first();
    $response = startedResponse($survey);

    $first = UploadedFile::fake()->create('first.pdf', 100, 'application/pdf');
    $this->post("/s/{$survey->slug}/answer", [
        'response_uuid' => $response->uuid,
        'question_id' => $question->id,
        'answer' => $first,
    ])->assertOk();

    $firstUpload = ResponseUpload::query()->where('response_id', $response->id)->first();
    $firstPath = $firstUpload->stored_path;

    $second = UploadedFile::fake()->create('second.pdf', 100, 'application/pdf');
    $this->post("/s/{$survey->slug}/answer", [
        'response_uuid' => $response->uuid,
        'question_id' => $question->id,
        'answer' => $second,
    ])->assertOk();

    expect(ResponseUpload::query()->where('response_id', $response->id)->count())->toBe(1);

    Storage::disk('local')->assertMissing($firstPath);

    $upload = ResponseUpload::query()->where('response_id', $response->id)->first();
    expect($upload->original_name)->toBe('second.pdf');
});

test('the drag_drop display style renders on the public survey page without error', function () {
    $client = Client::factory()->create();
    $template = SurveyTemplate::factory()->create();
    $fileType = QuestionType::query()->where('key', 'file_upload')->firstOrFail();
    $template->questions()->create([
        'question_type_id' => $fileType->id,
        'question_text' => 'Upload your receipt',
        'settings' => ['allowed_types' => ['pdf', 'png'], 'max_file_size' => 5120, 'display_style' => 'drag_drop'],
        'order' => 0,
    ]);

    $admin = User::factory()->create();
    $survey = app(SurveyService::class)->createFromTemplate($client, $template, 'Receipt Survey', $admin->id);
    app(SurveyService::class)->publish($survey);

    $page = $this->get("/s/{$survey->fresh()->slug}");

    $page->assertOk();
    $page->assertSee('Upload your receipt');
    $page->assertSee('Drag & drop a file here', false);
});

test('a disallowed file type is rejected with a validation error, not silently accepted', function () {
    Storage::fake('local');

    $client = Client::factory()->create();
    $survey = fileUploadSurvey($client);
    $question = $survey->questions->first();
    $response = startedResponse($survey);

    $badFile = UploadedFile::fake()->create('malware.exe', 100, 'application/x-msdownload');

    $result = $this->post("/s/{$survey->slug}/answer", [
        'response_uuid' => $response->uuid,
        'question_id' => $question->id,
        'answer' => $badFile,
    ], ['Accept' => 'application/json']);

    $result->assertStatus(422);
    expect(ResponseUpload::query()->where('response_id', $response->id)->count())->toBe(0);
});
