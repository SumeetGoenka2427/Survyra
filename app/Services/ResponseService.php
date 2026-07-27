<?php

namespace App\Services;

use App\Jobs\ResolveResponseGeoJob;
use App\Jobs\SendSlackNotificationJob;
use App\Models\CampaignRecipient;
use App\Models\Response;
use App\Models\ResponseAnswer;
use App\Models\ResponseUpload;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveyThankyouRule;
use App\Notifications\NegativeFeedbackReceived;
use App\Services\WebhookService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ResponseService
{
    /**
     * Questions per step for the section-wizard layout. Not user-configurable
     * yet - a fixed group size keeps the first version simple; a per-survey
     * setting can follow if that proves too rigid in practice.
     */
    private const QUESTIONS_PER_SECTION = 3;

    public function __construct(
        private readonly LogicEngine $logicEngine,
        private readonly UserAgentParser $userAgentParser,
        private readonly WebhookService $webhooks,
    ) {}

    /**
     * $campaignRecipientId comes from a tracked short link's `cr=` query param
     * (see ShortLinkService::resolveAndTrack) - it's how a response started
     * from a campaign message gets attributed back to that contact/campaign.
     */
    public function startOrResume(Survey $survey, ?string $cookieUuid, Request $request, ?string $campaignRecipientId = null): Response
    {
        if ($cookieUuid) {
            $existing = Response::query()
                ->where('uuid', $cookieUuid)
                ->where('survey_id', $survey->id)
                ->first();

            if ($existing) {
                return $existing;
            }
        }

        $userAgent = $request->userAgent();
        $recipient = $campaignRecipientId ? CampaignRecipient::query()->find($campaignRecipientId) : null;

        $data = [
            'client_id' => $survey->client_id,
            'survey_id' => $survey->id,
            'status' => 'started',
            'started_at' => now(),
        ];

        if (! $survey->is_anonymous) {
            $data['device'] = $this->userAgentParser->device($userAgent);
            $data['browser'] = $this->userAgentParser->browser($userAgent);
            $data['ip'] = $request->ip();
            $data['source'] = $recipient?->channel ?? (in_array($request->query('src'), ['qr', 'sms', 'whatsapp', 'email'], true)
                ? $request->query('src')
                : 'direct');
            $data['contact_id'] = $recipient?->contact_id;
            $data['campaign_id'] = $recipient?->campaign_id;
        }

        $response = Response::query()->create($data);
        $this->webhooks->fire('response.started', $response);

        // Async geo lookup (non-blocking)
        if (! $survey->is_anonymous && $request->ip()) {
            ResolveResponseGeoJob::dispatch($response->id, $request->ip())->onQueue('default');
        }

        return $response;
    }

    public function saveAnswer(Response $response, SurveyQuestion $question, mixed $rawAnswer): void
    {
        $contract = $question->questionType->contract();
        $settings = array_merge($question->settings ?? [], ['options' => $question->options ?? []]);

        Validator::make(
            ['answer' => $rawAnswer],
            ['answer' => $contract->validationRules($settings, $question->is_required)]
        )->validate();

        $storedAnswer = $rawAnswer instanceof UploadedFile
            ? $this->storeUpload($response, $question, $rawAnswer)
            : $rawAnswer;

        $response->answers()->updateOrCreate(
            ['question_id' => $question->id],
            ['answer' => $storedAnswer, 'score' => $contract->score($storedAnswer, $settings)]
        );

        // Track last answered question for drop-off analysis
        $updateData = ['last_question_id' => $question->id];
        if ($response->status === 'started') {
            $updateData['status'] = 'in_progress';
        }
        $response->update($updateData);
    }

    /**
     * Persist an uploaded file to private storage and record it, returning a
     * small JSON-safe reference (never the UploadedFile itself) to store as
     * the answer. Replaces any previously uploaded file for this question on
     * this response, since a respondent re-uploading supersedes their prior file.
     *
     * @return array{upload_id: int, original_name: string, file_size: int}
     */
    private function storeUpload(Response $response, SurveyQuestion $question, UploadedFile $file): array
    {
        $existing = ResponseUpload::query()
            ->where('response_id', $response->id)
            ->where('question_id', $question->id)
            ->first();

        if ($existing) {
            Storage::disk('local')->delete($existing->stored_path);
        }

        $path = $file->store("response-uploads/{$response->id}", 'local');

        $upload = ResponseUpload::updateOrCreate(
            ['response_id' => $response->id, 'question_id' => $question->id],
            [
                'original_name' => $file->getClientOriginalName(),
                'stored_path' => $path,
                'mime_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
            ]
        );

        return [
            'upload_id' => $upload->id,
            'original_name' => $upload->original_name,
            'file_size' => $upload->file_size,
        ];
    }

    /**
     * The respondent's already-saved answer for a question, if any - used to
     * pre-fill the question view when they revisit it (e.g. via the Back
     * button) instead of showing it blank and risking the blank re-submit
     * silently overwriting their real answer with nothing.
     */
    public function existingAnswer(Response $response, SurveyQuestion $question): mixed
    {
        return $this->answersMap($response)[$question->id] ?? null;
    }

    /**
     * Bulk form of existingAnswer() for layouts that render several
     * questions at once (one_page/card_based/section_wizard) - one query
     * for the whole visible set instead of one per question.
     *
     * @return array<int, mixed>
     */
    public function answersByQuestionId(Response $response): array
    {
        return $this->answersMap($response);
    }

    public function previousQuestion(Response $response): ?SurveyQuestion
    {
        $survey = $response->survey;
        $answeredIds = $response->answers()->pluck('question_id')->all();
        $answers = $this->answersMap($response);
        $hidden = $this->hiddenQuestionIds($survey, $answers);

        // Get visible questions in order
        $visible = $survey->questions->reject(fn (SurveyQuestion $q) => in_array($q->id, $hidden, true))->values();

        // Find the last answered question that is still visible
        $lastAnsweredId = null;
        foreach ($visible as $question) {
            if (in_array($question->id, $answeredIds, true)) {
                $lastAnsweredId = $question->id;
            }
        }

        if (! $lastAnsweredId) {
            return null;
        }

        // Return the question before the last answered one
        $lastIndex = $visible->search(fn (SurveyQuestion $q) => $q->id === $lastAnsweredId);

        if ($lastIndex === false || $lastIndex === 0) {
            return null;
        }

        return $visible[$lastIndex - 1];
    }

    public function nextQuestion(Response $response): ?SurveyQuestion
    {
        $survey = $response->survey;
        $answered = $response->answers()->pluck('question_id')->flip();
        $answers = $this->answersMap($response);
        $hidden = $this->hiddenQuestionIds($survey, $answers);

        // Check for end_survey action
        if ($this->shouldEndSurvey($survey, $answers)) {
            return null;
        }

        // Check for jump_to_question action
        $jumpTarget = $this->jumpTarget($survey, $answers, $answered);
        if ($jumpTarget) {
            return $jumpTarget;
        }

        foreach ($survey->questions as $question) {
            if ($answered->has($question->id)) {
                continue;
            }

            if (in_array($question->id, $hidden, true)) {
                continue;
            }

            return $question;
        }

        return null;
    }

    /**
     * Every question currently applicable given the answers-so-far (i.e. not
     * hidden by a logic rule), regardless of whether it's already been
     * answered. Used by the one-page layout, which shows every applicable
     * question at once rather than stepping through them one at a time.
     *
     * @return Collection<int, SurveyQuestion>
     */
    public function visibleQuestions(Response $response): Collection
    {
        $survey = $response->survey;
        $answers = $this->answersMap($response);
        $hidden = $this->hiddenQuestionIds($survey, $answers);

        return $survey->questions->reject(fn (SurveyQuestion $q) => in_array($q->id, $hidden, true))->values();
    }

    /**
     * The section-wizard layout groups visible questions into fixed-size
     * steps and auto-advances once every required question in the current
     * step is answered - the "current step" is never stored, it's derived
     * fresh each time from (visible questions, answered ids), the same
     * stateless-HTTP approach nextQuestion() already uses for single
     * questions. This means, like multi-step today, there is no "back"
     * button in this first version - going back would need an explicit
     * stored position rather than a derived one.
     *
     * @return array{questions: Collection<int, SurveyQuestion>, sectionNumber: int, totalSections: int, isLastSection: bool}
     */
    public function currentSection(Response $response): array
    {
        $visible = $this->visibleQuestions($response);
        $answeredIds = $response->answers()->pluck('question_id')->all();
        $chunks = $visible->chunk(self::QUESTIONS_PER_SECTION)->values();

        if ($chunks->isEmpty()) {
            return ['questions' => collect(), 'sectionNumber' => 1, 'totalSections' => 1, 'isLastSection' => true];
        }

        $currentIndex = $chunks->search(
            fn (Collection $chunk) => $chunk->contains(fn (SurveyQuestion $q) => $q->is_required && ! in_array($q->id, $answeredIds, true))
        );

        if ($currentIndex === false) {
            $currentIndex = $chunks->count() - 1;
        }

        return [
            'questions' => $chunks[$currentIndex]->values(),
            'sectionNumber' => $currentIndex + 1,
            'totalSections' => $chunks->count(),
            'isLastSection' => $currentIndex === $chunks->count() - 1,
        ];
    }

    /**
     * @return array{sentiment: string, rule: SurveyThankyouRule}
     */
    public function submit(Response $response): array
    {
        $survey = $response->survey;

        $this->assertRequiredQuestionsAnswered($response, $survey);

        $score = null;

        if ($survey->primary_score_question_id) {
            $score = $response->answers()
                ->where('question_id', $survey->primary_score_question_id)
                ->value('score');
        }

        $rule = $this->matchThankyouRule($survey, $score);

        $response->update([
            'status' => 'completed',
            'completed_at' => now(),
            'score' => $score,
            'sentiment' => $rule->sentiment,
        ]);

        // Invalidate analytics cache for this client
        Cache::flush();

        $this->webhooks->fire('response.completed', $response);

        SendSlackNotificationJob::dispatch($response->client_id, 'response_completed', [
            'Survey' => $survey->title,
            'Sentiment' => $rule->sentiment ? ucfirst($rule->sentiment) : 'n/a',
            'Score' => $score ?? '—',
        ]);

        if ($rule->sentiment === 'negative') {
            $clientUsers = $response->client->clientUsers;

            if ($clientUsers->isNotEmpty()) {
                Notification::send($clientUsers, new NegativeFeedbackReceived($response));
            }

            SendSlackNotificationJob::dispatch($response->client_id, 'negative_feedback', [
                'Survey' => $survey->title,
                'Score' => $score ?? '—',
                'Response' => "#{$response->id}",
            ]);
        }

        return ['sentiment' => $rule->sentiment, 'rule' => $rule];
    }

    /**
     * The stepped layouts (multi-step/conversational) already force every
     * required-and-visible question to be answered before "next question"
     * runs out - this is a no-op safety net there. The one-page layout has
     * no such forced order (every applicable question is on screen at
     * once), so this is the only place completeness is actually enforced
     * for it.
     */
    private function assertRequiredQuestionsAnswered(Response $response, Survey $survey): void
    {
        $answeredIds = $response->answers()->pluck('question_id')->all();
        $hidden = $this->hiddenQuestionIds($survey, $this->answersMap($response));

        $missing = $survey->questions
            ->reject(fn (SurveyQuestion $q) => in_array($q->id, $hidden, true))
            ->where('is_required', true)
            ->reject(fn (SurveyQuestion $q) => in_array($q->id, $answeredIds, true));

        if ($missing->isNotEmpty()) {
            throw ValidationException::withMessages([
                'answer' => ["Please answer: \"{$missing->first()->question_text}\" before submitting."],
            ]);
        }
    }

    private function shouldEndSurvey(Survey $survey, array $answers): bool
    {
        foreach ($survey->logicRules->where('action', 'end_survey') as $rule) {
            if ($this->logicEngine->evaluate($rule, $answers)) {
                return true;
            }
        }
        return false;
    }

    private function jumpTarget(Survey $survey, array $answers, \Illuminate\Support\Collection $answered): ?SurveyQuestion
    {
        foreach ($survey->logicRules->where('action', 'jump_to_question') as $rule) {
            if (! $this->logicEngine->evaluate($rule, $answers)) {
                continue;
            }
            $target = $survey->questions->firstWhere('id', $rule->target_question_id);
            if ($target && ! $answered->has($target->id)) {
                return $target;
            }
        }
        return null;
    }

    /**
     * @return array<int, mixed>
     */
    private function answersMap(Response $response): array
    {
        // get()->mapWithKeys(), not pluck(): pluck() reads raw columns straight
        // from the query builder and skips the model's 'array' cast on `answer`,
        // which would leave every value as an undecoded JSON string.
        return $response->answers()->get()
            ->mapWithKeys(fn (ResponseAnswer $answer) => [$answer->question_id => $answer->answer])
            ->all();
    }

    /**
     * @param  array<int, mixed>  $answers
     * @return array<int, int>
     */
    private function hiddenQuestionIds(Survey $survey, array $answers): array
    {
        $hidden = $survey->logicRules->where('action', 'show')->pluck('target_question_id')->unique()->values()->all();

        foreach ($survey->logicRules->sortBy('id') as $rule) {
            if (! $this->logicEngine->evaluate($rule, $answers)) {
                continue;
            }

            if ($rule->action === 'show') {
                $hidden = array_values(array_diff($hidden, [$rule->target_question_id]));
            } else {
                $hidden[] = $rule->target_question_id;
            }
        }

        return array_unique($hidden);
    }

    private function matchThankyouRule(Survey $survey, ?float $score): SurveyThankyouRule
    {
        $rules = $survey->thankyouRules;

        if ($score !== null) {
            // Safety-first order: a misconfigured overlap should never favor a review ask.
            foreach (['negative', 'positive', 'neutral'] as $sentiment) {
                $rule = $rules->firstWhere('sentiment', $sentiment);

                if ($rule && $this->scoreWithinBucket($score, $rule)) {
                    return $rule;
                }
            }
        }

        return $rules->firstWhere('sentiment', 'neutral');
    }

    private function scoreWithinBucket(float $score, SurveyThankyouRule $rule): bool
    {
        if ($rule->min_score === null || $rule->max_score === null) {
            return false;
        }

        return $score >= $rule->min_score && $score <= $rule->max_score;
    }
}
