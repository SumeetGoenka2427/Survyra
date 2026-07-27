<?php

use App\Models\Client;
use App\Models\Response as SurveyResponseModel;
use App\Models\Survey;
use Database\Seeders\PremiumThemeShowcaseSeeder;
use Database\Seeders\QuestionTypeSeeder;

beforeEach(function () {
    $this->seed(QuestionTypeSeeder::class);
    $this->seed(PremiumThemeShowcaseSeeder::class);
});

test('all 12 premium themes and their demo surveys are created and published', function () {
    $client = Client::where('company_name', 'Design Gallery')->firstOrFail();
    $surveys = Survey::where('client_id', $client->id)->get();

    expect($surveys)->toHaveCount(12);
    expect($surveys->where('status', 'published'))->toHaveCount(12);
    expect($surveys->pluck('theme_id')->unique())->toHaveCount(12);
});

test('every demo survey can be completed end to end - welcome through thank you', function () {
    $client = Client::where('company_name', 'Design Gallery')->firstOrFail();
    $surveys = Survey::where('client_id', $client->id)->with('questions.questionType')->get();

    foreach ($surveys as $survey) {
        // Welcome screen on first visit.
        $this->get("/s/{$survey->slug}")
            ->assertOk()
            ->assertSee($survey->welcome_screen['title']);

        // Past the welcome screen: first question.
        $this->get("/s/{$survey->slug}?start=1")->assertOk();

        $response = SurveyResponseModel::where('survey_id', $survey->id)->firstOrFail();

        foreach ($survey->questions->sortBy('order') as $question) {
            $answer = match ($question->questionType->key) {
                'nps' => 9,
                'csat', 'ces', 'rating_stars', 'emoji_rating' => 4,
                'radio' => $question->options[0],
                'textarea' => 'Looks great.',
                'email' => 'demo@example.com',
                default => 'Test answer',
            };

            $this->postJson("/s/{$survey->slug}/answer", [
                'response_uuid' => $response->uuid,
                'question_id' => $question->id,
                'answer' => $answer,
            ])->assertOk();
        }

        expect(SurveyResponseModel::find($response->id)->status)->toBe('completed');
    }
});
