@php $total = $survey->questions->count(); @endphp
<div class="conv-progress-rail" style="width: {{ $total ? round(($position - 1) / $total * 100) : 0 }}%;"></div>

<div class="conv-question">
    <div class="text-muted small mb-2">{{ $position }} / {{ $total }}</div>

    <form id="answer-form" data-question-id="{{ $question->id }}">
        <h1 class="sq-label">{{ $question->question_text }}</h1>

        @include($question->questionType->contract()->renderComponent($question->settings['display_style'] ?? 'default'), ['question' => $question])

        <button type="submit" class="btn btn-survyra-primary conv-next px-4 py-2">
            Next <i class="bi bi-arrow-right"></i>
        </button>
    </form>
</div>
