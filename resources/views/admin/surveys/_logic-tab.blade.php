@php
    $operatorLabels = [
        'equals' => 'equals',
        'not_equals' => 'does not equal',
        'contains' => 'contains',
        'greater_than' => 'is greater than',
        'less_than' => 'is less than',
        'is_empty' => 'is empty',
        'is_not_empty' => 'is not empty',
    ];
    $actionLabels = [
        'show' => 'Show question',
        'hide' => 'Hide question',
        'jump_to_question' => 'Jump to question',
        'end_survey' => 'End survey',
    ];
@endphp

<div class="list-group list-group-flush mb-4">
    @forelse ($survey->logicRules as $rule)
        <div class="list-group-item" x-data="{ editing: false }">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong>IF</strong>
                    {{ collect($rule->conditions)->map(function ($condition) use ($survey, $operatorLabels) {
                        $question = $survey->questions->firstWhere('id', $condition['question_id']);
                        $label = $operatorLabels[$condition['operator']] ?? $condition['operator'];
                        return trim(($question->question_text ?? '?').' '.$label.' '.($condition['value'] ?? ''));
                    })->join(' '.($rule->condition_operator ?? 'AND').' ') }}
                    <br>
                    <strong>THEN</strong>
                    @if ($rule->action === 'end_survey')
                        end the survey
                    @elseif ($rule->action === 'jump_to_question')
                        jump to "{{ $rule->targetQuestion?->question_text ?? '?' }}"
                    @else
                        {{ $rule->action }} "{{ $rule->targetQuestion?->question_text ?? '?' }}"
                    @endif
                </div>
                <div class="d-flex gap-1 flex-shrink-0">
                    <button type="button" class="btn btn-sm btn-outline-primary" @click="editing = !editing">Edit</button>
                    <form action="{{ route('admin.surveys.logic-rules.destroy', [$survey, $rule]) }}" method="POST" onsubmit="return confirm('Remove this rule?');">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </div>
            </div>

            <div x-show="editing" class="mt-3 border-top pt-3">
                <form method="POST" action="{{ route('admin.surveys.logic-rules.update', [$survey, $rule]) }}">
                    @csrf
                    @method('PUT')

                    <x-logic-rule-fields
                        :questions="$survey->questions"
                        :initial-conditions="$rule->conditions"
                        :initial-action="$rule->action"
                        :initial-condition-operator="$rule->condition_operator ?? 'AND'"
                        :initial-target-question-id="$rule->target_question_id"
                    />

                    <button type="submit" class="btn btn-primary btn-sm mt-3">Save Rule</button>
                </form>
            </div>
        </div>
    @empty
        <div class="list-group-item text-center text-muted py-4">No logic rules yet.</div>
    @endforelse
</div>

<div class="card border-0 bg-light">
    <div class="card-body">
        <h6 class="mb-3">Add Logic Rule</h6>
        <form method="POST" action="{{ route('admin.surveys.logic-rules.store', $survey) }}">
            @csrf

            <x-logic-rule-fields :questions="$survey->questions" />

            <button type="submit" class="btn btn-primary mt-3">Add Rule</button>
        </form>
    </div>
</div>
