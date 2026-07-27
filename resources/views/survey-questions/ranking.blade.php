@php
    $items = $question->options ?? [];
    if (is_array($existingAnswer ?? null) && count(array_intersect($existingAnswer, $items)) === count($items)) {
        // Respect the respondent's previously saved order rather than the
        // template's default order when they revisit this question.
        $items = array_values($existingAnswer);
    }
@endphp
<ul class="sq-ranking-list" data-ranking-list>
    @foreach ($items as $index => $item)
        <li class="sq-ranking-item" data-ranking-item data-value="{{ $item }}">
            <span class="sq-ranking-rank">{{ $index + 1 }}</span>
            <span class="sq-ranking-label">{{ $item }}</span>
            <span class="sq-ranking-controls">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-ranking-up aria-label="Move up">
                    <i class="bi bi-arrow-up"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-ranking-down aria-label="Move down">
                    <i class="bi bi-arrow-down"></i>
                </button>
            </span>
        </li>
    @endforeach
</ul>
<input type="hidden" name="answer" data-ranking-order value="{{ implode(',', $items) }}">
