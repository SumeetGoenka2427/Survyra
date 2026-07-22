<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveyTemplate;
use App\Repositories\Contracts\SurveyRepositoryInterface;
use App\Services\Concerns\ReordersQuestions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class SurveyService
{
    use ReordersQuestions;

    /**
     * Question type keys that make sense as a survey's "primary score question".
     */
    private const SCORABLE_TYPES = ['nps', 'csat', 'ces', 'rating_stars', 'emoji_rating', 'slider'];

    public function __construct(private readonly SurveyRepositoryInterface $surveys)
    {
    }

    public function paginate(int $perPage = 15, ?int $clientId = null, ?string $status = null): LengthAwarePaginator
    {
        return $this->surveys->paginate($perPage, $clientId, $status);
    }

    public function find(int $id): Survey
    {
        return $this->surveys->find($id);
    }

    /**
     * Structural edits (add/remove/reorder a question, change its type) are locked
     * once a survey has real responses - editing live data out from under
     * respondents would silently corrupt their answers. See Phase 3 gap #4.
     */
    public function hasResponses(Survey $survey): bool
    {
        return $survey->responses()->exists();
    }

    public function createFromTemplate(Client $client, SurveyTemplate $template, string $title, int $createdByUserId): Survey
    {
        return DB::transaction(function () use ($client, $template, $title, $createdByUserId) {
            $survey = $this->surveys->create([
                'client_id' => $client->id,
                'survey_template_id' => $template->id,
                'title' => $title,
                'status' => 'draft',
                'version' => 1,
                'layout' => $template->layout ?? 'multi_step',
                'created_by' => $createdByUserId,
            ]);

            foreach ($template->questions as $templateQuestion) {
                $survey->questions()->create([
                    'question_type_id' => $templateQuestion->question_type_id,
                    'question_text' => $templateQuestion->question_text,
                    'options' => $templateQuestion->options,
                    'settings' => $templateQuestion->settings,
                    'order' => $templateQuestion->order,
                    'is_required' => $templateQuestion->is_required,
                ]);
            }

            $primaryQuestion = $survey->questions()
                ->whereHas('questionType', fn ($query) => $query->whereIn('key', self::SCORABLE_TYPES))
                ->orderBy('order')
                ->first() ?? $survey->questions()->orderBy('order')->first();

            if ($primaryQuestion) {
                $survey->update(['primary_score_question_id' => $primaryQuestion->id]);
            }

            $this->seedDefaultThankyouRules($survey, $primaryQuestion);

            return $survey->fresh();
        });
    }

    public function createBlank(Client $client, string $title, string $layout, int $createdByUserId): Survey
    {
        return DB::transaction(function () use ($client, $title, $layout, $createdByUserId) {
            $survey = $this->surveys->create([
                'client_id' => $client->id,
                'title' => $title,
                'status' => 'draft',
                'version' => 1,
                'layout' => $layout,
                'created_by' => $createdByUserId,
            ]);

            $this->seedDefaultThankyouRules($survey, null);

            return $survey->fresh();
        });
    }

    public function duplicate(Survey $survey, int $createdByUserId): Survey
    {
        return DB::transaction(function () use ($survey, $createdByUserId) {
            $copy = $this->surveys->create([
                'client_id' => $survey->client_id,
                'survey_template_id' => $survey->survey_template_id,
                'theme_id' => $survey->theme_id,
                'title' => 'Copy of '.$survey->title,
                'status' => 'draft',
                'version' => 1,
                'layout' => $survey->layout,
                'settings' => $survey->settings,
                'welcome_screen' => $survey->welcome_screen,
                'is_anonymous' => $survey->is_anonymous,
                'gdpr_enabled' => $survey->gdpr_enabled,
                'gdpr_text' => $survey->gdpr_text,
                'privacy_policy_url' => $survey->privacy_policy_url,
                'created_by' => $createdByUserId,
            ]);

            // Map old question IDs to new ones for logic rule remapping
            $questionIdMap = [];
            foreach ($survey->questions as $question) {
                $newQuestion = $copy->questions()->create([
                    'question_type_id' => $question->question_type_id,
                    'question_text' => $question->question_text,
                    'options' => $question->options,
                    'settings' => $question->settings,
                    'order' => $question->order,
                    'is_required' => $question->is_required,
                ]);
                $questionIdMap[$question->id] = $newQuestion->id;

                if ($survey->primary_score_question_id === $question->id) {
                    $copy->update(['primary_score_question_id' => $newQuestion->id]);
                }
            }

            foreach ($survey->logicRules as $rule) {
                $newConditions = collect($rule->conditions)->map(function ($c) use ($questionIdMap) {
                    $c['question_id'] = $questionIdMap[$c['question_id']] ?? $c['question_id'];
                    return $c;
                })->all();

                $copy->logicRules()->create([
                    'source_question_id' => $questionIdMap[$rule->source_question_id] ?? $rule->source_question_id,
                    'conditions' => $newConditions,
                    'action' => $rule->action,
                    'target_question_id' => $questionIdMap[$rule->target_question_id] ?? $rule->target_question_id,
                ]);
            }

            foreach ($survey->thankyouRules as $rule) {
                $copy->thankyouRules()->create($rule->only([
                    'sentiment', 'min_score', 'max_score', 'thank_you_message',
                    'show_google_review', 'show_facebook', 'show_instagram', 'show_website',
                    'show_coupon', 'coupon_code', 'show_complaint_form', 'show_support_number',
                    'show_whatsapp_button', 'manager_contact',
                ]));
            }

            return $copy->fresh();
        });
    }

    public function update(Survey $survey, array $data): Survey
    {
        return $this->surveys->update($survey, $data);
    }

    public function setPrimaryScoreQuestion(Survey $survey, SurveyQuestion $question): Survey
    {
        if ($question->survey_id !== $survey->id) {
            throw new InvalidArgumentException('Question does not belong to this survey.');
        }

        return $this->surveys->update($survey, ['primary_score_question_id' => $question->id]);
    }

    public function publish(Survey $survey): Survey
    {
        if ($survey->questions()->count() === 0) {
            throw new InvalidArgumentException('A survey needs at least one question before it can be published.');
        }

        return $this->surveys->update($survey, [
            'slug' => $survey->slug ?? $this->generateUniqueSlug(),
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public function archive(Survey $survey): Survey
    {
        return $this->surveys->update($survey, ['status' => 'archived']);
    }

    public function delete(Survey $survey): void
    {
        if ($survey->status !== 'draft') {
            throw new InvalidArgumentException('Only draft surveys can be deleted - archive published surveys instead.');
        }

        $this->surveys->delete($survey);
    }

    public function addQuestion(Survey $survey, array $data): SurveyQuestion
    {
        $data['order'] = $survey->questions()->max('order') + 1;

        return $survey->questions()->create($data);
    }

    public function duplicateQuestion(SurveyQuestion $question): SurveyQuestion
    {
        $survey = $question->survey;

        return $survey->questions()->create([
            'question_type_id' => $question->question_type_id,
            'question_text' => $question->question_text,
            'options' => $question->options,
            'settings' => $question->settings,
            'order' => $survey->questions()->max('order') + 1,
            'is_required' => $question->is_required,
        ]);
    }

    /**
     * Batch reorder questions. $items is [{id, order}, ...].
     *
     * @param  array<int, array{id: int, order: int}>  $items
     */
    public function reorderQuestions(Survey $survey, array $items): void
    {
        $ids = $survey->questions()->pluck('id')->flip();

        foreach ($items as $item) {
            if ($ids->has($item['id'])) {
                SurveyQuestion::where('id', $item['id'])->update(['order' => (int) $item['order']]);
            }
        }
    }

    public function updateQuestion(SurveyQuestion $question, array $data): SurveyQuestion
    {
        $question->update($data);

        return $question;
    }

    public function removeQuestion(SurveyQuestion $question): void
    {
        $question->delete();
    }

    public function moveQuestionUp(SurveyQuestion $question): void
    {
        $this->moveOrderUp($question, 'survey_id');
    }

    public function moveQuestionDown(SurveyQuestion $question): void
    {
        $this->moveOrderDown($question, 'survey_id');
    }

    private function generateUniqueSlug(): string
    {
        do {
            $slug = Str::lower(Str::random(8));
        } while (Survey::query()->where('slug', $slug)->exists());

        return $slug;
    }

    private function seedDefaultThankyouRules(Survey $survey, ?SurveyQuestion $primaryQuestion): void
    {
        $buckets = $primaryQuestion ? $this->scoreBucketsFor($primaryQuestion) : [
            'positive' => [null, null],
            'neutral' => [null, null],
            'negative' => [null, null],
        ];

        $survey->thankyouRules()->createMany([
            [
                'sentiment' => 'positive',
                'min_score' => $buckets['positive'][0],
                'max_score' => $buckets['positive'][1],
                'thank_you_message' => 'Thank you for your feedback! We\'re thrilled you had a great experience.',
                'show_google_review' => true,
                'show_facebook' => true,
                'show_website' => true,
            ],
            [
                'sentiment' => 'neutral',
                'min_score' => $buckets['neutral'][0],
                'max_score' => $buckets['neutral'][1],
                'thank_you_message' => 'Thanks for sharing your thoughts with us.',
            ],
            [
                'sentiment' => 'negative',
                'min_score' => $buckets['negative'][0],
                'max_score' => $buckets['negative'][1],
                'thank_you_message' => "We're sorry to hear that. Our team will reach out to help make it right.",
                'show_google_review' => false,
                'show_complaint_form' => true,
                'show_support_number' => true,
                'show_whatsapp_button' => true,
            ],
        ]);
    }

    /**
     * @return array<string, array{0: int|null, 1: int|null}>
     */
    private function scoreBucketsFor(SurveyQuestion $question): array
    {
        $settings = array_merge($question->questionType->settings_schema ?? [], $question->settings ?? []);
        $min = $settings['scale_min'] ?? 0;
        $max = $settings['scale_max'] ?? 10;

        if ($question->questionType->key === 'nps') {
            return ['positive' => [9, 10], 'neutral' => [7, 8], 'negative' => [$min, 6]];
        }

        $range = $max - $min;
        $third = (int) ceil($range / 3);

        return [
            'positive' => [$min + 2 * $third + 1, $max],
            'neutral' => [$min + $third + 1, $min + 2 * $third],
            'negative' => [$min, $min + $third],
        ];
    }
}
