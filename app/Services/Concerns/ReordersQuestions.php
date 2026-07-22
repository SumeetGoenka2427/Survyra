<?php

namespace App\Services\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Shared move-up/move-down-by-swapping-order logic for any "question belongs
 * to an owner" model (survey_template_questions -> survey_template_id,
 * survey_questions -> survey_id).
 */
trait ReordersQuestions
{
    protected function moveOrderUp(Model $question, string $ownerColumn): void
    {
        $this->swapWithNeighbor($question, $ownerColumn, '<', 'desc');
    }

    protected function moveOrderDown(Model $question, string $ownerColumn): void
    {
        $this->swapWithNeighbor($question, $ownerColumn, '>', 'asc');
    }

    private function swapWithNeighbor(Model $question, string $ownerColumn, string $operator, string $direction): void
    {
        $neighbor = $question->newQuery()
            ->where($ownerColumn, $question->{$ownerColumn})
            ->where('order', $operator, $question->order)
            ->orderBy('order', $direction)
            ->first();

        if (! $neighbor) {
            return;
        }

        DB::transaction(function () use ($question, $neighbor) {
            [$questionOrder, $neighborOrder] = [$question->order, $neighbor->order];
            $question->update(['order' => $neighborOrder]);
            $neighbor->update(['order' => $questionOrder]);
        });
    }
}
