<?php

namespace App\Services\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;

/**
 * Shared ordering logic for any "child belongs to an owner, ordered by an
 * `order` column" model. Used by the survey builder (survey_template_questions
 * -> survey_template_id, survey_questions -> survey_id) and the website
 * builder (website_pages -> website_id, website_sections -> page_id).
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

    /**
     * Batch-persists a full reorder. $items is [{id, order}, ...]. $scope
     * constrains which ids are allowed to be reordered (e.g. $website->pages(),
     * $page->sections()) so a caller can't reorder rows it doesn't own.
     *
     * @param  array<int, array{id: int, order: int}>  $items
     */
    protected function reorderBatch(Relation $scope, array $items): void
    {
        $ids = $scope->pluck('id')->flip();
        $modelClass = get_class($scope->getModel());

        foreach ($items as $item) {
            if ($ids->has($item['id'])) {
                $modelClass::where('id', $item['id'])->update(['order' => (int) $item['order']]);
            }
        }
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
