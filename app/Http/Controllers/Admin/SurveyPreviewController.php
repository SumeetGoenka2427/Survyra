<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuestionType;
use App\Models\Survey;
use App\Models\SurveyTemplate;
use App\Models\SurveyTemplateQuestion;
use App\Models\SurveyTheme;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class SurveyPreviewController extends Controller
{
    /**
     * A generic sample question set used when previewing a theme on its own
     * (no template/survey context) - a theme isn't tied to any one template,
     * so this gives every theme preview the same realistic 3-question feel.
     */
    private const SAMPLE_QUESTIONS = [
        ['type' => 'nps', 'text' => 'How likely are you to recommend us to a friend or colleague?'],
        ['type' => 'csat', 'text' => 'How satisfied were you with your experience?'],
        ['type' => 'textarea', 'text' => 'What could we do better?'],
    ];

    /**
     * Mirrors ResponseService::QUESTIONS_PER_SECTION for the section-wizard
     * preview grouping - this is a mock stepper only, so it doesn't share
     * the constant, just the same grouping size for a faithful preview.
     */
    private const QUESTIONS_PER_SECTION = 3;

    public function index(Request $request): View
    {
        $this->authorize('viewAny', SurveyTemplate::class);

        $survey = $request->integer('survey')
            ? Survey::query()->with(['questions.questionType', 'theme'])->find($request->integer('survey'))
            : null;

        $template = ! $survey && $request->integer('template')
            ? SurveyTemplate::query()->find($request->integer('template'))
            : null;

        $theme = $request->integer('theme')
            ? SurveyTheme::query()->find($request->integer('theme'))
            : ($survey?->theme);

        $theme ??= SurveyTheme::query()->where('is_system', true)->orderBy('id')->first();

        $questions = match (true) {
            $survey !== null => $survey->questions,
            $template !== null => $template->questions()->orderBy('order')->with('questionType')->get(),
            default => $this->sampleQuestions(),
        };

        $layout = $survey->layout ?? $template->layout ?? 'multi_step';

        return view('admin.survey-preview', [
            'label' => $survey->title ?? $template->name ?? 'Sample survey',
            'survey' => $survey,
            'template' => $template,
            'theme' => $theme,
            'layout' => $layout,
            'themes' => SurveyTheme::query()->orderBy('is_system', 'desc')->orderBy('name')->get(),
            'steps' => $this->chunkForLayout($questions, $layout),
        ]);
    }

    /**
     * Groups the question list into "steps" the mock preview stepper walks
     * through, matching how each real layout actually paces a respondent:
     * one question per step (multi-step/conversational), fixed-size groups
     * per step (section-wizard), or a single step holding everything
     * (one-page/card-based, which show every question at once for real).
     *
     * @return Collection<int, Collection<int, SurveyTemplateQuestion>>
     */
    private function chunkForLayout(Collection $questions, string $layout): Collection
    {
        return match ($layout) {
            'section_wizard' => $questions->chunk(self::QUESTIONS_PER_SECTION)->values()->map(fn ($chunk) => $chunk->values()),
            'one_page', 'card_based' => collect([$questions->values()]),
            default => $questions->values()->map(fn ($question) => collect([$question])),
        };
    }

    /**
     * @return Collection<int, SurveyTemplateQuestion>
     */
    private function sampleQuestions(): Collection
    {
        $types = QuestionType::query()->whereIn('key', array_column(self::SAMPLE_QUESTIONS, 'type'))->get()->keyBy('key');

        return collect(self::SAMPLE_QUESTIONS)->values()->map(function (array $sample, int $index) use ($types) {
            $type = $types->get($sample['type']);
            $question = new SurveyTemplateQuestion([
                'question_text' => $sample['text'],
                'options' => [],
                'settings' => [],
                'order' => $index,
                'is_required' => true,
            ]);
            $question->id = $index + 1;
            $question->setRelation('questionType', $type);

            return $question;
        })->filter(fn (SurveyTemplateQuestion $q) => $q->questionType !== null)->values();
    }
}
