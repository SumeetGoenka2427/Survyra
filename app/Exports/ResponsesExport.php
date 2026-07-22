<?php

namespace App\Exports;

use App\Models\Response;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * One stable column schema regardless of whether this is a single survey or
 * a client's whole response history - per-question answers are flattened
 * into one "Answers" summary column instead of dynamic per-question columns,
 * which would differ survey to survey.
 */
class ResponsesExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private readonly Collection $responses)
    {
    }

    public function collection(): Collection
    {
        return $this->responses;
    }

    public function headings(): array
    {
        return ['UUID', 'Survey', 'Status', 'Score', 'Sentiment', 'Started At', 'Completed At', 'Device', 'Browser', 'Source', 'Answers'];
    }

    public function map($response): array
    {
        /** @var Response $response */
        return [
            $response->uuid,
            $response->survey->title,
            $response->status,
            $response->score,
            $response->sentiment,
            optional($response->started_at)->toDateTimeString(),
            optional($response->completed_at)->toDateTimeString(),
            $response->device,
            $response->browser,
            $response->source,
            $response->answers->map(function ($answer) {
                $value = is_array($answer->answer) ? implode(', ', $answer->answer) : $answer->answer;

                return "{$answer->question->question_text}: {$value}";
            })->implode(' | '),
        ];
    }
}
