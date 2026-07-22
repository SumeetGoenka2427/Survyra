<?php

namespace App\Models;

use App\Services\QuestionTypeRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionType extends Model
{
    protected $fillable = [
        'key',
        'label',
        'scoring_type',
        'settings_schema',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'settings_schema' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * The QuestionTypeContract implementation this row is backed by.
     */
    public function contract(): \App\Contracts\QuestionTypeContract
    {
        return app(QuestionTypeRegistry::class)->resolve($this->key);
    }

    /**
     * @return HasMany<SurveyTemplateQuestion, $this>
     */
    public function templateQuestions(): HasMany
    {
        return $this->hasMany(SurveyTemplateQuestion::class);
    }
}
