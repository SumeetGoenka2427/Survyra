@php $total = $survey->questions->count(); @endphp
<div class="text-muted small mb-2">Question {{ $position }} of {{ $total }}</div>

<div class="progress mb-4" style="height: 6px;">
    <div class="progress-bar" role="progressbar" style="width: {{ $total ? round(($position - 1) / $total * 100) : 0 }}%;"></div>
</div>

<form id="answer-form" data-question-id="{{ $question->id }}">
    <h4 class="sq-label">{{ $question->question_text }}</h4>

    @include($question->questionType->contract()->renderComponent($question->settings['display_style'] ?? 'default'), ['question' => $question])

    <div class="d-flex gap-2 mt-3">
        @if ($position > 1)
            <button type="button" id="back-button" class="btn btn-outline-secondary flex-shrink-0">
                <i class="bi bi-arrow-left"></i> Back
            </button>
        @endif
        <button type="submit" class="btn btn-survyra-primary flex-grow-1">Next</button>
    </div>
</form>
