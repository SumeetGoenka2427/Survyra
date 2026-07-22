<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyLogicRule extends Model
{
    protected $fillable = [
        'survey_id',
        'source_question_id',
        'conditions',
        'condition_operator',
        'action',
        'target_question_id',
    ];

    protected function casts(): array
    {
        return [
            'conditions' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Survey, $this>
     */
    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    /**
     * @return BelongsTo<SurveyQuestion, $this>
     */
    public function sourceQuestion(): BelongsTo
    {
        return $this->belongsTo(SurveyQuestion::class, 'source_question_id');
    }

    /**
     * @return BelongsTo<SurveyQuestion, $this>
     */
    public function targetQuestion(): BelongsTo
    {
        return $this->belongsTo(SurveyQuestion::class, 'target_question_id');
    }
}
