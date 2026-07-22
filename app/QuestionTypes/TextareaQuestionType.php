<?php

namespace App\QuestionTypes;

class TextareaQuestionType extends AbstractQuestionType
{
    public function key(): string
    {
        return 'textarea';
    }

    public function label(): string
    {
        return 'Textarea (Long Answer)';
    }

    public function scoringType(): string
    {
        return 'none';
    }

    public function builderGroup(): string
    {
        return 'plain';
    }

    public function availableStyles(): array
    {
        return [
            'modern' => 'Modern Auto-resize',
            'floating' => 'Floating Label',
        ];
    }

    public function renderComponent(string $style = 'default'): string
    {
        return 'survey-questions.textarea.'.$this->resolveStyle($style);
    }

    public function validationRules(array $settings, bool $required): array
    {
        return [$this->requiredRule($required), 'string', 'max:5000'];
    }

    public function score(mixed $answer, array $settings): ?float
    {
        return null;
    }
}
