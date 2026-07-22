<?php

namespace App\QuestionTypes;

use App\Contracts\QuestionTypeContract;

class ImageChoiceQuestionType extends AbstractQuestionType implements QuestionTypeContract
{
    public function key(): string
    {
        return 'image_choice';
    }

    public function label(): string
    {
        return 'Image Choice';
    }

    public function scoringType(): string
    {
        return 'none';
    }

    public function builderGroup(): string
    {
        return 'choice';
    }

    public function validationRules(array $settings, bool $required): array
    {
        $rules = ['nullable'];

        if ($settings['multiple'] ?? false) {
            $rules[] = 'array';
            $rules[] = 'min:1';
            if ($max = $settings['max_choices'] ?? null) {
                $rules[] = "max:{$max}";
            }
        }

        if ($required) {
            array_unshift($rules, 'required');
        }

        return $rules;
    }

    public function score(mixed $answer, array $settings): ?float
    {
        return null;
    }

    public function availableStyles(): array
    {
        return [
            'grid' => 'Image Grid',
            'carousel' => 'Carousel',
            'list' => 'List with Thumbnails',
        ];
    }

    public function renderComponent(string $style = 'default'): string
    {
        return "survey-questions.{$this->key()}.{$this->resolveStyle($style)}";
    }
}