@php $total = $questions->count(); @endphp
<div class="text-muted small mb-2">Section {{ $sectionNumber }} of {{ $totalSections }}</div>

<div class="progress mb-4" style="height: 6px;">
    <div class="progress-bar" style="width: {{ round(($sectionNumber - 1) / max($totalSections, 1) * 100) }}%;"></div>
</div>

<div id="one-page-list" data-question-ids="{{ $questions->pluck('id')->implode(',') }}">
    @foreach ($questions as $question)
        <div class="mb-4 pb-3 border-bottom">
            <form data-question-id="{{ $question->id }}" class="one-page-answer-form">
                <h5 class="sq-label">{{ $question->question_text }}</h5>

                @if (!empty($question->settings['help_text']))
                    <div class="text-muted small mb-2">{{ $question->settings['help_text'] }}</div>
                @endif

                @include($question->questionType->contract()->renderComponent($question->settings['display_style'] ?? 'default'), ['question' => $question, 'existingAnswer' => ($answers ?? [])[$question->id] ?? null])

                <div class="small one-page-save-status" style="min-height: 1.2em;"></div>
            </form>
        </div>
    @endforeach
</div>

@if ($isLastSection)
    <div id="one-page-submit-error" class="alert alert-danger d-none"></div>
    <button type="button" id="one-page-submit" class="btn btn-survyra-primary w-100">
        Submit Survey
    </button>
@else
    <div class="text-center text-muted small">
        <i class="bi bi-arrow-down-circle"></i> Answering these will take you to the next section automatically.
    </div>
@endif
