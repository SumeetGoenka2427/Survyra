<?php

namespace App\QuestionTypes;

class EmojiRatingQuestionType extends AbstractQuestionType
{
    public function key(): string
    {
        return 'emoji_rating';
    }

    public function label(): string
    {
        return 'Rating (Emoji)';
    }

    public function scoringType(): string
    {
        return 'scale';
    }

    public function builderGroup(): string
    {
        return 'scale';
    }

    public function availableStyles(): array
    {
        return [
            'emoji' => 'Emoji Row',
            'cards' => 'Labeled Cards',
        ];
    }

    public function renderComponent(string $style = 'default'): string
    {
        return 'survey-questions.emoji-rating.'.$this->resolveStyle($style);
    }

    public function validationRules(array $settings, bool $required): array
    {
        return [$this->requiredRule($required), 'integer', 'min:1', 'max:5'];
    }

    public function score(mixed $answer, array $settings): ?float
    {
        return is_numeric($answer) ? (float) $answer : null;
    }
}
