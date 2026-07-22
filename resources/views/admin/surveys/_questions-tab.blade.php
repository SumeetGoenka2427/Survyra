@php $locked = $survey->responses()->exists(); @endphp

@if ($locked)
    <div class="alert alert-warning">
        <i class="bi bi-lock-fill"></i> This survey already has responses — its questions are locked to protect existing data. You can still mark a different primary score question.
    </div>
@endif

<div
    id="questions-sortable"
    class="list-group list-group-flush mb-4"
    @unless($locked)
    data-reorder-url="{{ route('admin.surveys.questions.reorder', $survey) }}"
    @endunless
>
    @forelse ($survey->questions as $question)
        <div class="list-group-item @unless($locked) sortable-item @endunless" data-id="{{ $question->id }}" x-data="{ editing: false }">
            <div class="d-flex justify-content-between align-items-start">
                <div class="d-flex align-items-start gap-2">
                    @unless($locked)
                        <span class="drag-handle text-muted mt-1" style="cursor:grab;" title="Drag to reorder">
                            <i class="bi bi-grip-vertical"></i>
                        </span>
                    @endunless
                    <div>
                        <span class="badge text-bg-light text-dark border me-2">{{ $question->questionType->label }}</span>
                        {{ $question->question_text }}
                        @if ($question->is_required)
                            <span class="badge text-bg-danger ms-1">Required</span>
                        @endif
                        @if ($survey->primary_score_question_id === $question->id)
                            <span class="badge text-bg-primary ms-1">Primary Score</span>
                        @endif
                    </div>
                </div>
                <div class="d-flex gap-1 flex-shrink-0">
                    @if ($survey->primary_score_question_id !== $question->id)
                        <form action="{{ route('admin.surveys.questions.set-primary-score', [$survey, $question]) }}" method="POST">
                            @csrf @method('PATCH')
                            <button class="btn btn-sm btn-outline-primary" title="Use as primary score">
                                <i class="bi bi-bullseye"></i>
                            </button>
                        </form>
                    @endif
                    @unless ($locked)
                        <form action="{{ route('admin.surveys.questions.duplicate', [$survey, $question]) }}" method="POST">
                            @csrf
                            <button class="btn btn-sm btn-outline-secondary" title="Duplicate question">
                                <i class="bi bi-copy"></i>
                            </button>
                        </form>
                        <button type="button" class="btn btn-sm btn-outline-primary" @click="editing = !editing">Edit</button>
                        <form action="{{ route('admin.surveys.questions.destroy', [$survey, $question]) }}" method="POST" onsubmit="return confirm('Remove this question?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    @endunless
                </div>
            </div>

            <div x-show="editing" class="mt-3 border-top pt-3">
                <form method="POST" action="{{ route('admin.surveys.questions.update', [$survey, $question]) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Question Text</label>
                        <input type="text" name="question_text" class="form-control" value="{{ $question->question_text }}" required>
                    </div>

                    <x-question-type-fields
                        :prefix="'sq-'.$question->id"
                        :question-types="$questionTypes"
                        :selected-type-id="$question->question_type_id"
                        :options-text="implode(\"\n\", $question->options ?? [])"
                        :settings="$question->settings ?? []"
                    />

                    <div class="form-check mb-3 mt-2">
                        <input type="hidden" name="is_required" value="0">
                        <input type="checkbox" name="is_required" value="1" class="form-check-input" id="sq-required-{{ $question->id }}" @checked($question->is_required)>
                        <label class="form-check-label" for="sq-required-{{ $question->id }}">Required</label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm">Save Question</button>
                </form>
            </div>
        </div>
    @empty
        <div class="list-group-item text-center text-muted py-4">
            <i class="bi bi-question-circle fs-3 d-block mb-2"></i>
            No questions yet. Add your first question below.
        </div>
    @endforelse
</div>

@unless ($locked)
<div class="card border-0 bg-light">
    <div class="card-body">
        <h6 class="mb-3">Add Question</h6>
        <form method="POST" action="{{ route('admin.surveys.questions.store', $survey) }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Question Text</label>
                <input type="text" name="question_text" class="form-control" required>
            </div>

            <x-question-type-fields prefix="sq-add" :question-types="$questionTypes" />

            <div class="form-check mb-3 mt-2">
                <input type="hidden" name="is_required" value="0">
                <input type="checkbox" name="is_required" value="1" class="form-check-input" id="sq-add-is_required" checked>
                <label class="form-check-label" for="sq-add-is_required">Required</label>
            </div>

            <button type="submit" class="btn btn-primary">Add Question</button>
        </form>
    </div>
</div>
@endunless

@unless($locked)
<script>
document.addEventListener('DOMContentLoaded', function () {
    const list = document.getElementById('questions-sortable');
    if (!list || !list.dataset.reorderUrl) return;

    // Use SortableJS if available, otherwise skip
    if (typeof Sortable === 'undefined') return;

    Sortable.create(list, {
        handle: '.drag-handle',
        animation: 150,
        onEnd: function () {
            const items = [...list.querySelectorAll('.sortable-item')].map((el, index) => ({
                id: parseInt(el.dataset.id),
                order: index + 1,
            }));

            fetch(list.dataset.reorderUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ items }),
            });
        },
    });
});
</script>
@endunless
