<?php

namespace App\Services;

use App\Models\SurveyTemplate;
use App\Models\SurveyTemplateQuestion;
use App\Repositories\Contracts\SurveyTemplateRepositoryInterface;
use App\Services\Concerns\ReordersQuestions;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SurveyTemplateService
{
    use ReordersQuestions;

    public function __construct(private readonly SurveyTemplateRepositoryInterface $templates)
    {
    }

    public function allGroupedByIndustry(?string $search = null): Collection
    {
        return $this->templates->allGroupedByIndustry($search);
    }

    public function find(int $id): SurveyTemplate
    {
        return $this->templates->find($id);
    }

    public function create(array $data, int $createdByUserId): SurveyTemplate
    {
        return $this->templates->create([...$data, 'created_by' => $createdByUserId]);
    }

    public function update(SurveyTemplate $template, array $data): SurveyTemplate
    {
        return $this->templates->update($template, $data);
    }

    public function delete(SurveyTemplate $template): void
    {
        $this->templates->delete($template);
    }

    /**
     * "Save as New Template" - clones a template and every one of its questions.
     */
    public function duplicate(SurveyTemplate $template, int $createdByUserId): SurveyTemplate
    {
        return DB::transaction(function () use ($template, $createdByUserId) {
            $copy = $this->templates->create([
                'name' => "{$template->name} (Copy)",
                'industry_category' => $template->industry_category,
                'description' => $template->description,
                'created_by' => $createdByUserId,
            ]);

            foreach ($template->questions as $question) {
                $copy->questions()->create([
                    'question_type_id' => $question->question_type_id,
                    'question_text' => $question->question_text,
                    'options' => $question->options,
                    'settings' => $question->settings,
                    'order' => $question->order,
                    'is_required' => $question->is_required,
                ]);
            }

            return $copy;
        });
    }

    public function addQuestion(SurveyTemplate $template, array $data): SurveyTemplateQuestion
    {
        $data['order'] = $template->questions()->max('order') + 1;

        return $template->questions()->create($data);
    }

    public function updateQuestion(SurveyTemplateQuestion $question, array $data): SurveyTemplateQuestion
    {
        $question->update($data);

        return $question;
    }

    public function removeQuestion(SurveyTemplateQuestion $question): void
    {
        $question->delete();
    }

    public function duplicateQuestion(SurveyTemplateQuestion $question): SurveyTemplateQuestion
    {
        $template = $question->template;

        return $template->questions()->create([
            'question_type_id' => $question->question_type_id,
            'question_text' => $question->question_text,
            'options' => $question->options,
            'settings' => $question->settings,
            'order' => $template->questions()->max('order') + 1,
            'is_required' => $question->is_required,
        ]);
    }

    public function moveQuestionUp(SurveyTemplateQuestion $question): void
    {
        $this->moveOrderUp($question, 'survey_template_id');
    }

    public function moveQuestionDown(SurveyTemplateQuestion $question): void
    {
        $this->moveOrderDown($question, 'survey_template_id');
    }
}
