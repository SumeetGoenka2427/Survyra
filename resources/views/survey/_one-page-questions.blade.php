@php $total = $questions->count(); @endphp
<div class="text-muted small mb-3">{{ $total }} question{{ $total === 1 ? '' : 's' }} — answer at your own pace, then submit below.</div>

<div id="one-page-list" data-question-ids="{{ $questions->pluck('id')->implode(',') }}">
    @foreach ($questions as $question)
        <div class="mb-4 pb-4 border-bottom">
            <form data-question-id="{{ $question->id }}" class="one-page-answer-form">
                <h5 class="sq-label">{{ $question->question_text }}</h5>

                @include($question->questionType->contract()->renderComponent($question->settings['display_style'] ?? 'default'), ['question' => $question])

                <div class="small one-page-save-status" style="min-height: 1.2em;"></div>
            </form>
        </div>
    @endforeach
</div>

<div id="one-page-submit-error" class="alert alert-danger d-none"></div>

<button type="button" id="one-page-submit" class="btn btn-survyra-primary w-100">
    Submit Survey
</button>
