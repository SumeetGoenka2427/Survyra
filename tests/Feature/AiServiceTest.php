<?php

use App\Models\AiContentCache;
use App\Models\Client;
use App\Models\QuestionType;
use App\Models\SurveyTemplate;
use App\Models\User;
use App\Services\AiService;
use App\Services\SurveyService;
use Database\Seeders\QuestionTypeSeeder;

beforeEach(function () {
    $this->seed(QuestionTypeSeeder::class);
});

test('AiContentCache actually persists - regression for the ai_content_cache/ai_content_caches table name mismatch', function () {
    // AiContentCache had no $table override, so Eloquent guessed the
    // pluralized `ai_content_caches`, but the migration created the table
    // as `ai_content_cache` (singular) - every AiService method that
    // touches the cache (summarizeResponses, analyzeSentiment,
    // extractKeywords, recommendedActions, generateSurvey, suggestQuestions)
    // threw "table doesn't exist" on every single call.
    $cache = AiContentCache::create([
        'ai_related_type' => 'survey',
        'ai_related_id' => 1,
        'type' => 'response_summary',
        'input_context' => ['response_count' => 5],
        'output_content' => ['summary' => 'test'],
    ]);

    expect($cache->exists)->toBeTrue();
    expect(AiContentCache::find($cache->id)->output_content['summary'])->toBe('test');
});

test('every AiService method that reads/writes the cache table runs without a table-not-found error', function () {
    $admin = User::factory()->create();
    $client = Client::factory()->create();
    $template = SurveyTemplate::factory()->create();
    $nps = QuestionType::query()->where('key', 'nps')->firstOrFail();
    $template->questions()->create(['question_type_id' => $nps->id, 'question_text' => 'How likely to recommend?', 'order' => 0]);
    $survey = app(SurveyService::class)->createFromTemplate($client, $template, 'AI Cache Survey', $admin->id);
    app(SurveyService::class)->publish($survey);
    $survey = $survey->fresh(['questions']);

    $ai = app(AiService::class);

    expect(fn () => $ai->summarizeResponses($survey))->not->toThrow(Exception::class);
    expect(fn () => $ai->analyzeSentiment($survey))->not->toThrow(Exception::class);
    expect(fn () => $ai->extractKeywords($survey))->not->toThrow(Exception::class);
    expect(fn () => $ai->recommendedActions($survey))->not->toThrow(Exception::class);
    expect(fn () => $ai->suggestQuestions($survey))->not->toThrow(Exception::class);
    expect(fn () => $ai->generateSurvey('a customer feedback survey'))->not->toThrow(Exception::class);
});
