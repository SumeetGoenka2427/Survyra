<?php

namespace App\Services;

use App\Models\AiContentCache;
use App\Models\Response;
use App\Models\Survey;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * AI-powered features: survey generation, question suggestions,
 * response summarization, quality scoring, NLP sentiment analysis,
 * keyword extraction, and recommended actions.
 *
 * This service uses OpenAI-compatible API endpoints. Configure via:
 * AI_API_KEY, AI_API_BASE_URL, AI_MODEL env vars.
 */
class AiService
{
    protected ?string $apiKey;
    protected ?string $apiBase;
    protected ?string $model;

    public function __construct()
    {
        $this->apiKey = config('services.ai.api_key') ?? env('AI_API_KEY');
        $this->apiBase = config('services.ai.base_url') ?? env('AI_API_BASE_URL', 'https://api.openai.com/v1');
        $this->model = config('services.ai.model') ?? env('AI_MODEL', 'gpt-4o-mini');
    }

    /**
     * TASK-036: Generate a full survey with questions from a prompt.
     *
     * @return array{title: string, questions: array<int, array{type: string, text: string, options?: array<int, string>}>}
     */
    public function generateSurvey(string $prompt, string $language = 'en'): array
    {
        $cache = AiContentCache::where('ai_related_type', 'global')
            ->where('type', 'survey_generator')
            ->where('input_context->prompt', $prompt)
            ->first();

        if ($cache) {
            return $cache->output_content;
        }

        $response = $this->chat([
            ['role' => 'system', 'content' => 'You are a professional survey designer. Generate a complete survey JSON with title and questions. Each question should have: type (one of: radio, checkbox, textbox, textarea, nps, csat, ces, rating_stars, dropdown, yes_no, date, email, phone, number, slider, emoji_rating, matrix, ranking), text, options (for choice types), and is_required. Return ONLY valid JSON.'],
            ['role' => 'user', 'content' => "Create a survey in {$language} about: {$prompt}"],
        ]);

        $result = $this->parseJsonResponse($response);
        $result = $this->validateSurveyStructure($result);

        AiContentCache::create([
            'ai_related_type' => 'global',
            'ai_related_id' => 0,
            'type' => 'survey_generator',
            'input_context' => ['prompt' => $prompt, 'language' => $language],
            'output_content' => $result,
            'token_count' => $response['usage']['total_tokens'] ?? null,
        ]);

        return $result;
    }

    /**
     * TASK-037: Suggest questions based on existing survey content.
     *
     * @return array<int, array{type: string, text: string, options?: array}>
     */
    public function suggestQuestions(Survey $survey, int $count = 5): array
    {
        $existingQuestions = $survey->questions->pluck('question_text')->implode(', ');
        $cacheKey = md5("suggest:{$survey->id}:{$count}");

        $cache = AiContentCache::where('ai_related_type', 'survey')
            ->where('ai_related_id', $survey->id)
            ->where('type', 'question_suggestion')
            ->where('input_context->count', $count)
            ->first();

        if ($cache) {
            return $cache->output_content;
        }

        $response = $this->chat([
            ['role' => 'system', 'content' => "You are a survey design expert. Suggest {$count} additional questions for a survey. Return ONLY a JSON array of questions, each with: type, text, and options (if applicable)."],
            ['role' => 'user', 'content' => "Existing questions: {$existingQuestions}. Suggest {$count} more that would complement these."],
        ]);

        $result = $this->parseJsonResponse($response);

        AiContentCache::create([
            'ai_related_type' => 'survey',
            'ai_related_id' => $survey->id,
            'type' => 'question_suggestion',
            'input_context' => ['count' => $count],
            'output_content' => $result,
            'token_count' => $response['usage']['total_tokens'] ?? null,
        ]);

        return $result;
    }

    /**
     * TASK-038: Generate an AI summary of survey responses.
     */
    public function summarizeResponses(Survey $survey): string
    {
        $cache = AiContentCache::where('ai_related_type', 'survey')
            ->where('ai_related_id', $survey->id)
            ->where('type', 'response_summary')
            ->first();

        if ($cache) {
            return $cache->output_content['summary'] ?? '';
        }

        $completedResponses = $survey->responses()->where('status', 'completed')->count();
        $sentiments = $survey->responses()
            ->where('status', 'completed')
            ->whereNotNull('sentiment')
            ->selectRaw('sentiment, count(*) as count')
            ->groupBy('sentiment')
            ->pluck('count', 'sentiment');

        $response = $this->chat([
            ['role' => 'system', 'content' => 'You are a data analyst. Summarize the following survey results concisely in 2-3 paragraphs. Focus on key insights, trends, and actionable takeaways.'],
            ['role' => 'user', 'content' => "Survey: {$survey->title}\nTotal responses: {$completedResponses}\nSentiment breakdown: " . json_encode($sentiments) . "\n\nProvide a professional executive summary."],
        ]);

        $summary = $this->extractText($response);

        AiContentCache::create([
            'ai_related_type' => 'survey',
            'ai_related_id' => $survey->id,
            'type' => 'response_summary',
            'input_context' => ['response_count' => $completedResponses],
            'output_content' => ['summary' => $summary],
            'token_count' => $response['usage']['total_tokens'] ?? null,
        ]);

        return $summary;
    }

    /**
     * TASK-039: Calculate a survey quality score based on design best practices.
     *
     * @return array{score: int, feedback: array<int, string>, suggestions: array<int, string>}
     */
    public function qualityScore(Survey $survey): array
    {
        $score = 100;
        $feedback = [];
        $suggestions = [];
        $questions = $survey->questions;
        $count = $questions->count();

        // Rule 1: Survey should have at least 1 question
        if ($count === 0) {
            $score -= 20;
            $feedback[] = 'Survey has no questions';
            $suggestions[] = 'Add at least one question to your survey';
        }

        // Rule 2: Mix of question types
        $types = $questions->pluck('questionType.key')->unique();
        if ($types->count() < 2 && $count > 2) {
            $score -= 10;
            $feedback[] = 'Limited question type variety';
            $suggestions[] = 'Mix different question types (rating, choice, text) for better engagement';
        }

        // Rule 3: NPS/CSAT presence (good practice)
        $hasMetricQuestion = $types->intersect(['nps', 'csat', 'ces', 'rating_stars'])->isNotEmpty();
        if ($count > 3 && ! $hasMetricQuestion) {
            $score -= 5;
            $suggestions[] = 'Consider adding a standardized metric (NPS, CSAT, or CES) question';
        }

        // Rule 4: Has title
        if (empty($survey->title)) {
            $score -= 15;
            $feedback[] = 'Survey has no title';
            $suggestions[] = 'Give your survey a clear, descriptive title';
        }

        // Rule 5: Welcome screen
        if (empty($survey->welcome_screen['title'])) {
            $score -= 5;
            $suggestions[] = 'Add a welcome screen with context about the survey';
        }

        // Rule 6: Logic rules for complex surveys
        if ($count > 5 && $survey->logicRules->isEmpty()) {
            $score -= 5;
            $suggestions[] = 'Use logic rules to show/hide questions based on previous answers for a personalized experience';
        }

        // Rule 7: Required questions shouldn't exceed 50%
        if ($count > 0) {
            $requiredCount = $questions->where('is_required', true)->count();
            if ($requiredCount / $count > 0.5) {
                $score -= 10;
                $feedback[] = 'More than 50% of questions are required';
                $suggestions[] = 'Reduce required questions to improve completion rates';
            }
        }

        // Rule 8: Thank-you rules configured
        if ($survey->thankyouRules->isEmpty()) {
            $score -= 5;
            $suggestions[] = 'Configure thank-you rules with different messages based on sentiment';
        }

        // Rule 9: Theme customization
        if (! $survey->theme_id) {
            $score -= 5;
            $suggestions[] = 'Customize the survey theme with your branding';
        }

        return [
            'score' => max(0, $score),
            'grade' => $this->gradeScore(max(0, $score)),
            'feedback' => array_unique($feedback),
            'suggestions' => array_unique($suggestions),
        ];
    }

    /**
     * TASK-046: NLP sentiment analysis on text answers.
     *
     * @return array{positive: float, negative: float, neutral: float, summary: string}
     */
    public function analyzeSentiment(Survey $survey): array
    {
        $textAnswers = $survey->responses()
            ->where('status', 'completed')
            ->with(['answers' => fn ($q) => $q->whereHas('question.questionType', fn ($qt) => $qt->where('key', 'textarea'))])
            ->get()
            ->pluck('answers')
            ->flatten()
            ->pluck('answer')
            ->filter()
            ->values();

        if ($textAnswers->isEmpty()) {
            return ['positive' => 0, 'negative' => 0, 'neutral' => 0, 'summary' => 'No text answers to analyze.'];
        }

        $cacheContent = $textAnswers->take(50)->implode(' ');
        $cacheKey = md5("sentiment:{$survey->id}:{$cacheContent}");

        $cache = AiContentCache::where('ai_related_type', 'survey')
            ->where('ai_related_id', $survey->id)
            ->where('type', 'nlp_sentiment')
            ->first();

        if ($cache) {
            return $cache->output_content;
        }

        $sample = $textAnswers->take(20)->map(fn ($a) => "- " . mb_substr((string) $a, 0, 200))->implode("\n");

        $response = $this->chat([
            ['role' => 'system', 'content' => 'Analyze the sentiment of these survey text responses. Return a JSON object with: positive (float 0-1), negative (float 0-1), neutral (float 0-1), and summary (string).'],
            ['role' => 'user', 'content' => "Text responses from survey \"{$survey->title}\":\n{$sample}\n\nReturn JSON only."],
        ]);

        $result = $this->parseJsonResponse($response);

        AiContentCache::create([
            'ai_related_type' => 'survey',
            'ai_related_id' => $survey->id,
            'type' => 'nlp_sentiment',
            'input_context' => ['response_count' => $textAnswers->count()],
            'output_content' => $result,
        ]);

        return $result;
    }

    /**
     * TASK-048: Extract keywords from text answers.
     *
     * @return array<int, array{word: string, count: int}>
     */
    public function extractKeywords(Survey $survey): array
    {
        $textAnswers = $survey->responses()
            ->where('status', 'completed')
            ->with(['answers' => fn ($q) => $q->whereHas('question.questionType', fn ($qt) => $qt->whereIn('key', ['textarea', 'textbox']))])
            ->get()
            ->pluck('answers')
            ->flatten()
            ->pluck('answer')
            ->filter()
            ->values();

        if ($textAnswers->isEmpty()) {
            return [];
        }

        $cache = AiContentCache::where('ai_related_type', 'survey')
            ->where('ai_related_id', $survey->id)
            ->where('type', 'keyword_extraction')
            ->first();

        if ($cache) {
            return $cache->output_content;
        }

        $sample = $textAnswers->take(30)->map(fn ($a) => "- " . mb_substr((string) $a, 0, 200))->implode("\n");

        $response = $this->chat([
            ['role' => 'system', 'content' => 'Extract the top 20 most significant keywords/phrases from these survey responses. Return a JSON array of objects with: word (string) and count (integer). Sort by count descending.'],
            ['role' => 'user', 'content' => "Responses:\n{$sample}\n\nReturn JSON array only."],
        ]);

        $result = $this->parseJsonResponse($response);

        AiContentCache::create([
            'ai_related_type' => 'survey',
            'ai_related_id' => $survey->id,
            'type' => 'keyword_extraction',
            'input_context' => ['response_count' => $textAnswers->count()],
            'output_content' => $result,
        ]);

        return $result;
    }

    /**
     * TASK-049: Generate recommended actions based on survey results.
     *
     * @return array<int, array{priority: string, action: string, impact: string}>
     */
    public function recommendedActions(Survey $survey): array
    {
        $responses = $survey->responses()
            ->where('status', 'completed')
            ->get();
        $total = $responses->count();

        if ($total === 0) {
            return [['priority' => 'info', 'action' => 'Collect more responses to generate recommendations.', 'impact' => 'medium']];
        }

        $cache = AiContentCache::where('ai_related_type', 'survey')
            ->where('ai_related_id', $survey->id)
            ->where('type', 'recommended_actions')
            ->first();

        if ($cache) {
            return $cache->output_content;
        }

        $sentiments = $responses->groupBy('sentiment')->map->count();
        $negative = $sentiments['negative'] ?? 0;
        $positive = $sentiments['positive'] ?? 0;
        $avgScore = $responses->avg('score');

        $response = $this->chat([
            ['role' => 'system', 'content' => 'Based on survey results, generate 3-5 recommended actions. Return a JSON array of objects with: priority (high/medium/low), action (string), and impact (string).'],
            ['role' => 'user', 'content' => "Survey: {$survey->title}\nTotal responses: {$total}\nPositive: {$positive}\nNegative: {$negative}\nAvg Score: {$avgScore}\n\nGenerate actionable recommendations."],
        ]);

        $result = $this->parseJsonResponse($response);

        AiContentCache::create([
            'ai_related_type' => 'survey',
            'ai_related_id' => $survey->id,
            'type' => 'recommended_actions',
            'input_context' => ['response_count' => $total],
            'output_content' => $result,
        ]);

        return $result;
    }

    /**
     * TASK-050: Generate a weekly digest email content.
     */
    public function weeklyDigest(int $clientId): array
    {
        $response = $this->chat([
            ['role' => 'system', 'content' => 'You are an AI analytics assistant. Generate a weekly digest summary with sections: overview, top_insights (array), recommendations (array). Return JSON.'],
            ['role' => 'user', 'content' => "Generate a weekly analytics digest for client #{$clientId}. Focus on survey response trends and actionable insights."],
        ]);

        return $this->parseJsonResponse($response);
    }

    /**
     * TASK-045: Generate a natural language executive report.
     */
    public function executiveReport(Survey $survey): string
    {
        $responses = $survey->responses()->where('status', 'completed');
        $total = $responses->count();
        $sentiments = (clone $responses)->whereNotNull('sentiment')
            ->selectRaw('sentiment, count(*) as count')
            ->groupBy('sentiment')
            ->pluck('count', 'sentiment');
        $avgScore = (clone $responses)->avg('score');

        $response = $this->chat([
            ['role' => 'system', 'content' => 'Generate a professional executive report in HTML format. Include: Overview, Key Metrics, Sentiment Analysis, Detailed Findings, and Recommendations. Use clean HTML with Tailwind CSS classes.'],
            ['role' => 'user', 'content' => "Survey: {$survey->title}\nTotal Responses: {$total}\nSentiments: " . json_encode($sentiments) . "\nAvg Score: {$avgScore}\nQuestions: " . $survey->questions->pluck('question_text')->implode(', ')],
        ]);

        return $this->extractText($response);
    }

    /**
     * Send a chat completion request.
     *
     * @param array<int, array{role: string, content: string}> $messages
     * @return array<string, mixed>
     */
    protected function chat(array $messages): array
    {
        if (! $this->apiKey) {
            // Return mock data for development without AI API key
            return $this->mockResponse($messages);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post("{$this->apiBase}/chat/completions", [
                'model' => $this->model,
                'messages' => $messages,
                'temperature' => 0.7,
                'max_tokens' => 2000,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'content' => $data['choices'][0]['message']['content'] ?? '',
                    'usage' => $data['usage'] ?? ['total_tokens' => 0],
                ];
            }

            Log::warning('AI API request failed', ['status' => $response->status(), 'body' => $response->body()]);
            return ['content' => '', 'usage' => ['total_tokens' => 0]];
        } catch (\Throwable $e) {
            Log::error('AI API request exception', ['error' => $e->getMessage()]);
            return ['content' => '', 'usage' => ['total_tokens' => 0]];
        }
    }

    /**
     * Parse JSON from the AI response.
     */
    protected function parseJsonResponse(array $response): array
    {
        $content = $response['content'] ?? '';

        // Try to extract JSON from markdown code blocks
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $content, $matches)) {
            $content = trim($matches[1]);
        }

        $decoded = json_decode($content, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        // Fallback: try to find JSON array/object in the content
        if (preg_match('/(\{[\s\S]*\}|\[[\s\S]*\])/', $content, $matches)) {
            $decoded = json_decode($matches[1], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }

    /**
     * Extract plain text from AI response.
     */
    protected function extractText(array $response): string
    {
        return trim($response['content'] ?? '');
    }

    /**
     * Mock AI response for development without API key.
     */
    protected function mockResponse(array $messages): array
    {
        $lastMessage = end($messages)['content'] ?? '';

        if (str_contains($lastMessage, 'Generate a complete survey')) {
            return [
                'content' => json_encode([
                    'title' => 'Customer Feedback Survey',
                    'questions' => [
                        ['type' => 'nps', 'text' => 'How likely are you to recommend us?', 'is_required' => true],
                        ['type' => 'csat', 'text' => 'How satisfied are you with our service?', 'is_required' => true],
                        ['type' => 'textarea', 'text' => 'What can we improve?', 'is_required' => false],
                        ['type' => 'radio', 'text' => 'How did you hear about us?', 'options' => ['Social Media', 'Friend', 'Google', 'Other'], 'is_required' => true],
                    ],
                ]),
                'usage' => ['total_tokens' => 150],
            ];
        }

        if (str_contains($lastMessage, 'suggest')) {
            return [
                'content' => json_encode([
                    ['type' => 'rating_stars', 'text' => 'How would you rate the overall experience?', 'is_required' => true],
                    ['type' => 'dropdown', 'text' => 'Which feature do you use most?', 'options' => ['Feature A', 'Feature B', 'Feature C', 'All of the above']],
                    ['type' => 'yes_no', 'text' => 'Would you recommend us to a colleague?', 'is_required' => true],
                ]),
                'usage' => ['total_tokens' => 100],
            ];
        }

        if (str_contains($lastMessage, 'sentiment')) {
            return [
                'content' => json_encode([
                    'positive' => 0.45,
                    'negative' => 0.20,
                    'neutral' => 0.35,
                    'summary' => 'Responses show generally positive sentiment with some concerns about response time. Customers appreciate the product quality but suggest improvements in customer support availability.',
                ]),
                'usage' => ['total_tokens' => 80],
            ];
        }

        if (str_contains($lastMessage, 'keywords')) {
            return [
                'content' => json_encode([
                    ['word' => 'customer service', 'count' => 24],
                    ['word' => 'pricing', 'count' => 18],
                    ['word' => 'user interface', 'count' => 15],
                    ['word' => 'response time', 'count' => 12],
                    ['word' => 'quality', 'count' => 10],
                ]),
                'usage' => ['total_tokens' => 60],
            ];
        }

        if (str_contains($lastMessage, 'recommendations') || str_contains($lastMessage, 'actions')) {
            return [
                'content' => json_encode([
                    ['priority' => 'high', 'action' => 'Improve customer support response time', 'impact' => 'Could increase satisfaction by 25%'],
                    ['priority' => 'high', 'action' => 'Address pricing concerns with transparent communication', 'impact' => 'May reduce negative feedback by 30%'],
                    ['priority' => 'medium', 'action' => 'Enhance onboarding process', 'impact' => 'Improve user adoption and reduce churn'],
                ]),
                'usage' => ['total_tokens' => 90],
            ];
        }

        if (str_contains($lastMessage, 'executive report') || str_contains($lastMessage, 'executive')) {
            return [
                'content' => '<div class="prose max-w-none"><h2>Executive Report</h2><p>This executive report summarizes the key findings from your survey. Overall response rates indicate strong engagement with your feedback program.</p><h3>Key Metrics</h3><ul><li>High satisfaction scores across key metrics</li><li>Positive sentiment from the majority of respondents</li><li>Actionable feedback collected for continuous improvement</li></ul><h3>Recommendations</h3><p>Based on the analysis, we recommend focusing on response time improvements and enhancing the user experience.</p></div>',
                'usage' => ['total_tokens' => 120],
            ];
        }

        return [
            'content' => json_encode([
                'overview' => 'This week showed strong engagement with your surveys.',
                'top_insights' => ['Response rates increased by 15%', 'Customer satisfaction remains high at 4.2/5'],
                'recommendations' => ['Consider sending targeted follow-up surveys to detractors'],
            ]),
            'usage' => ['total_tokens' => 70],
        ];
    }

    /**
     * Validate and normalize the survey structure from AI.
     */
    protected function validateSurveyStructure(array $data): array
    {
        if (! isset($data['title'])) {
            $data['title'] = 'Untitled Survey';
        }

        if (! isset($data['questions']) || ! is_array($data['questions'])) {
            $data['questions'] = [];
        }

        $validTypes = ['radio', 'checkbox', 'textbox', 'textarea', 'nps', 'csat', 'ces', 'rating_stars', 'dropdown', 'yes_no', 'date', 'email', 'phone', 'number', 'slider', 'emoji_rating', 'matrix', 'ranking'];

        $data['questions'] = array_filter($data['questions'], fn ($q) => isset($q['type'], $q['text']) && in_array($q['type'], $validTypes));

        return $data;
    }

    protected function gradeScore(int $score): string
    {
        return match (true) {
            $score >= 90 => 'Excellent',
            $score >= 75 => 'Good',
            $score >= 50 => 'Average',
            default => 'Needs Improvement',
        };
    }
}