<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Services\AiService;
use App\Services\SurveyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

class AiSurveyController extends Controller
{
    public function __construct(
        private readonly AiService $ai,
        private readonly SurveyService $surveys,
    ) {}

    /**
     * Show the AI survey generator page.
     */
    public function index(): View
    {
        return view('admin.surveys.ai-generator');
    }

    /**
     * Generate a survey from a prompt.
     */
    public function generate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'prompt' => ['required', 'string', 'min:10', 'max:1000'],
            'language' => ['nullable', 'string', 'max:10'],
        ]);

        $result = $this->ai->generateSurvey($data['prompt'], $data['language'] ?? 'en');

        return response()->json($result);
    }

    /**
     * Suggest questions for an existing survey.
     */
    public function suggestQuestions(Survey $survey): JsonResponse
    {
        $suggestions = $this->ai->suggestQuestions($survey);

        return response()->json($suggestions);
    }

    /**
     * Get AI summary of survey responses.
     */
    public function summary(Survey $survey): JsonResponse
    {
        $summary = $this->ai->summarizeResponses($survey);

        return response()->json(['summary' => $summary]);
    }

    /**
     * Get survey quality score.
     */
    public function qualityScore(Survey $survey): JsonResponse
    {
        $score = $this->ai->qualityScore($survey);

        return response()->json($score);
    }

    /**
     * Get NLP sentiment analysis on text answers.
     */
    public function sentiment(Survey $survey): JsonResponse
    {
        $sentiment = $this->ai->analyzeSentiment($survey);

        return response()->json($sentiment);
    }

    /**
     * Get keywords from text answers.
     */
    public function keywords(Survey $survey): JsonResponse
    {
        $keywords = $this->ai->extractKeywords($survey);

        return response()->json($keywords);
    }

    /**
     * Get recommended actions based on results.
     */
    public function actions(Survey $survey): JsonResponse
    {
        $actions = $this->ai->recommendedActions($survey);

        return response()->json($actions);
    }

    /**
     * Generate an executive report.
     */
    public function executiveReport(Survey $survey): JsonResponse
    {
        $report = $this->ai->executiveReport($survey);

        return response()->json(['html' => $report]);
    }
}