<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Response as SurveyResponse;
use App\Models\Survey;
use App\Services\ResponseService;
use App\Services\UsageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;

class SurveyResponseController extends Controller
{
    private const COOKIE_MINUTES = 60 * 24 * 30;

    public function __construct(
        private readonly ResponseService $responses,
        private readonly UsageService $usage,
    ) {
    }

    public function show(string $slug, Request $request): Response
    {
        $survey = $this->findPublishedSurvey($slug);

        if (! $survey) {
            return response()->view('survey.unavailable', [], 404);
        }

        // Enforce expiry and response cap
        if ($survey->isExpired() || $survey->isResponseLimitReached()) {
            return response()->view('survey.unavailable', ['reason' => 'closed'], 410);
        }

        // Enforce subscription plan monthly response limit
        if (! $this->usage->canAcceptResponse($survey->client)) {
            return response()->view('survey.unavailable', ['reason' => 'closed'], 410);
        }

        $cookieName = $this->cookieName($slug);
        $response = $this->responses->startOrResume($survey, $request->cookie($cookieName), $request, $request->query('cr'));

        if ($response->status === 'completed') {
            $rule = $this->ruleFor($survey, $response->sentiment);

            return response()
                ->view('survey.show', ['survey' => $survey, 'response' => $response, 'question' => null, 'rule' => $rule])
                ->cookie($cookieName, $response->uuid, self::COOKIE_MINUTES);
        }

        // Show welcome screen if configured and not yet started answering
        if (! empty($survey->welcome_screen['title']) && $response->answers()->count() === 0 && $request->query('start') !== '1') {
            return response()
                ->view('survey.show', ['survey' => $survey, 'response' => $response, 'question' => null, 'showWelcome' => true])
                ->cookie($cookieName, $response->uuid, self::COOKIE_MINUTES);
        }

        if ($this->usesAllQuestionsLayout($survey)) {
            return response()
                ->view('survey.show', [
                    'survey' => $survey,
                    'response' => $response,
                    'question' => null,
                    'questions' => $this->responses->visibleQuestions($response),
                ])
                ->cookie($cookieName, $response->uuid, self::COOKIE_MINUTES);
        }

        if ($survey->layout === 'section_wizard') {
            return response()
                ->view('survey.show', [
                    'survey' => $survey,
                    'response' => $response,
                    'question' => null,
                    'section' => $this->responses->currentSection($response),
                ])
                ->cookie($cookieName, $response->uuid, self::COOKIE_MINUTES);
        }

        $question = $this->responses->nextQuestion($response);

        if (! $question) {
            $result = $this->responses->submit($response);

            return response()
                ->view('survey.show', ['survey' => $survey, 'response' => $response, 'question' => null, 'rule' => $result['rule']])
                ->cookie($cookieName, $response->uuid, self::COOKIE_MINUTES);
        }

        $position = $response->answers()->count() + 1;

        return response()
            ->view('survey.show', compact('survey', 'response', 'question', 'position'))
            ->cookie($cookieName, $response->uuid, self::COOKIE_MINUTES);
    }

    public function answer(Request $request, string $slug): JsonResponse
    {
        $survey = $this->findPublishedSurvey($slug) ?? abort(404);

        // Enforce expiry and response cap
        if ($survey->isExpired() || $survey->isResponseLimitReached()) {
            return response()->json(['error' => 'This survey is no longer accepting responses.'], 410);
        }

        $response = SurveyResponse::query()
            ->where('uuid', $request->input('response_uuid'))
            ->where('survey_id', $survey->id)
            ->firstOrFail();

        $question = $survey->questions->firstWhere('id', (int) $request->input('question_id'));

        abort_if(! $question, 404, 'Question not found on this survey.');

        $this->responses->saveAnswer($response, $question, $request->input('answer'));

        if ($this->usesAllQuestionsLayout($survey)) {
            $visible = $this->responses->visibleQuestions($response);

            return response()->json([
                'done' => false,
                'html' => View::make($this->allQuestionsView($survey), [
                    'survey' => $survey,
                    'response' => $response,
                    'questions' => $visible,
                ])->render(),
                'questionIds' => $visible->pluck('id')->values(),
            ]);
        }

        if ($survey->layout === 'section_wizard') {
            $section = $this->responses->currentSection($response);

            return response()->json([
                'done' => false,
                'html' => View::make('survey._section-questions', array_merge([
                    'survey' => $survey,
                    'response' => $response,
                ], $section))->render(),
                'questionIds' => $section['questions']->pluck('id')->values(),
            ]);
        }

        $next = $this->responses->nextQuestion($response);

        if ($next) {
            $position = $response->answers()->count() + 1;

            return response()->json([
                'done' => false,
                'html' => View::make($this->questionFrameView($survey), ['question' => $next, 'position' => $position, 'survey' => $survey])->render(),
            ]);
        }

        $result = $this->responses->submit($response);

        return response()->json([
            'done' => true,
            'html' => View::make('survey._thankyou-frame', ['rule' => $result['rule'], 'survey' => $survey, 'response' => $response])->render(),
        ]);
    }

    public function back(Request $request, string $slug): JsonResponse
    {
        $survey = $this->findPublishedSurvey($slug) ?? abort(404);

        $response = SurveyResponse::query()
            ->where('uuid', $request->input('response_uuid'))
            ->where('survey_id', $survey->id)
            ->firstOrFail();

        $previousQuestion = $this->responses->previousQuestion($response);

        if (! $previousQuestion) {
            return response()->json(['html' => View::make('survey.show', [
                'survey' => $survey,
                'response' => $response,
                'question' => null,
                'showWelcome' => true,
            ])->render()]);
        }

        $position = $response->answers()->count();

        return response()->json([
            'html' => View::make($this->questionFrameView($survey), [
                'question' => $previousQuestion,
                'position' => $position,
                'survey' => $survey,
            ])->render(),
        ]);
    }

    public function submit(Request $request, string $slug): JsonResponse
    {
        $survey = $this->findPublishedSurvey($slug) ?? abort(404);

        // Enforce expiry and response cap
        if ($survey->isExpired() || $survey->isResponseLimitReached()) {
            return response()->json(['error' => 'This survey is no longer accepting responses.'], 410);
        }

        // Verify reCAPTCHA if configured
        if (config('recaptcha.secret_key')) {
            $recaptchaToken = $request->input('g-recaptcha-response');
            if (! $recaptchaToken) {
                return response()->json(['errors' => ['captcha' => ['Bot detection required. Please try again.']]], 422);
            }

            $verify = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => config('recaptcha.secret_key'),
                'response' => $recaptchaToken,
                'remoteip' => $request->ip(),
            ])->json();

            $score = $verify['score'] ?? 0;
            if ($score < config('recaptcha.threshold', 0.5)) {
                return response()->json(['errors' => ['captcha' => ['Bot detection triggered. Please try again.']]], 422);
            }
        }

        $response = SurveyResponse::query()
            ->where('uuid', $request->input('response_uuid'))
            ->where('survey_id', $survey->id)
            ->firstOrFail();

        $rule = $response->status === 'completed'
            ? $this->ruleFor($survey, $response->sentiment)
            : $this->responses->submit($response)['rule'];

        return response()->json([
            'html' => View::make('survey._thankyou-frame', ['rule' => $rule, 'survey' => $survey, 'response' => $response])->render(),
        ]);
    }

    private function findPublishedSurvey(string $slug): ?Survey
    {
        return Survey::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->with(['client', 'theme', 'questions.questionType', 'logicRules', 'thankyouRules'])
            ->first();
    }

    private function questionFrameView(Survey $survey): string
    {
        return $survey->layout === 'conversational'
            ? 'survey._question-frame-conversational'
            : 'survey._question-frame';
    }

    /**
     * one_page and card_based share one engine (every applicable question
     * autosaved independently, one explicit submit button) - they differ
     * only in which Blade partial renders the question list.
     */
    private function usesAllQuestionsLayout(Survey $survey): bool
    {
        return in_array($survey->layout, ['one_page', 'card_based'], true);
    }

    private function allQuestionsView(Survey $survey): string
    {
        return $survey->layout === 'card_based'
            ? 'survey._card-based-questions'
            : 'survey._one-page-questions';
    }

    private function ruleFor(Survey $survey, ?string $sentiment)
    {
        return $survey->thankyouRules->firstWhere('sentiment', $sentiment)
            ?? $survey->thankyouRules->firstWhere('sentiment', 'neutral');
    }

    private function cookieName(string $slug): string
    {
        return 'survyra_response_'.$slug;
    }
}
