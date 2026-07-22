<?php

namespace App\QuestionTypes;

use App\Contracts\QuestionTypeContract;

class FileUploadQuestionType extends AbstractQuestionType implements QuestionTypeContract
{
    public function key(): string
    {
        return 'file_upload';
    }

    public function label(): string
    {
        return 'File Upload';
    }

    public function scoringType(): string
    {
        return 'none';
    }

    public function builderGroup(): string
    {
        return 'plain';
    }

    public function validationRules(array $settings, bool $required): array
    {
        $rules = ['nullable', 'file'];

        $maxSize = $settings['max_file_size'] ?? 10240; // 10MB default
        $rules[] = "max:{$maxSize}";

        $allowedTypes = $settings['allowed_types'] ?? ['pdf', 'doc', 'docx', 'jpg', 'png', 'xls', 'xlsx'];
        $mimes = implode(',', $allowedTypes);
        $rules[] = "mimes:{$mimes}";

        if ($required) {
            array_unshift($rules, 'required');
        }

        return $rules;
    }

    public function score(mixed $answer, array $settings): ?float
    {
        return null; // File uploads are not scored
    }

    public function availableStyles(): array
    {
        return [
            'default' => 'Default',
            'drag_drop' => 'Drag & Drop Zone',
        ];
    }

    public function renderComponent(string $style = 'default'): string
    {
        return "survey-questions.{$this->key()}.{$this->resolveStyle($style)}";
    }
}