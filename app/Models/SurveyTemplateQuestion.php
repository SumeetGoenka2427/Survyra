<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyTemplateQuestion extends Model
{
    protected $fillable = [
        'survey_template_id',
        'question_type_id',
        'question_text',
        'options',
        'settings',
        'order',
        'is_required',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'settings' => 'array',
            'is_required' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<SurveyTemplate, $this>
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(SurveyTemplate::class, 'survey_template_id');
    }

    /**
     * @return BelongsTo<QuestionType, $this>
     */
    public function questionType(): BelongsTo
    {
        return $this->belongsTo(QuestionType::class);
    }
}
