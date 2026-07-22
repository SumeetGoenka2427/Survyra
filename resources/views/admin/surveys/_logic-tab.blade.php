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
        <div class="list-group-item">
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
                <form action="{{ route('admin.surveys.logic-rules.destroy', [$survey, $rule]) }}" method="POST" onsubmit="return confirm('Remove this rule?');">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Delete</button>
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
        <form
            method="POST"
            action="{{ route('admin.surveys.logic-rules.store', $survey) }}"
            x-data="{ conditions: [{ question_id: '', operator: 'equals', value: '' }], action: 'show', conditionOperator: 'AND' }"
        >
            @csrf

            <template x-for="(condition, index) in conditions" :key="index">
                <div class="row g-2 mb-2 align-items-center">
                    <div class="col-auto" x-show="index > 0">
                        <select x-model="conditionOperator" name="condition_operator" class="form-select form-select-sm" style="width:80px;">
                            <option value="AND">AND</option>
                            <option value="OR">OR</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select :name="`conditions[${index}][question_id]`" class="form-select" x-model="condition.question_id" required>
                            <option value="">IF question...</option>
                            @foreach ($survey->questions as $q)
                                <option value="{{ $q->id }}">{{ $q->question_text }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select :name="`conditions[${index}][operator]`" class="form-select" x-model="condition.operator">
                            <option value="equals">equals</option>
                            <option value="not_equals">does not equal</option>
                            <option value="contains">contains</option>
                            <option value="greater_than">is greater than</option>
                            <option value="less_than">is less than</option>
                            <option value="is_empty">is empty</option>
                            <option value="is_not_empty">is not empty</option>
                        </select>
                    </div>
                    <div class="col-md-3" x-show="condition.operator !== 'is_empty' && condition.operator !== 'is_not_empty'">
                        <input type="text" :name="`conditions[${index}][value]`" class="form-control" placeholder="value" x-model="condition.value">
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-sm btn-outline-danger" @click="conditions.length > 1 && conditions.splice(index, 1)">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                </div>
            </template>

            <input type="hidden" name="condition_operator" :value="conditionOperator">

            <button type="button" class="btn btn-sm btn-outline-secondary mb-3" @click="conditions.push({ question_id: '', operator: 'equals', value: '' })">
                <i class="bi bi-plus"></i> Add condition
            </button>

            <div class="row g-2">
                <div class="col-md-4">
                    <label class="form-label">Then</label>
                    <select name="action" class="form-select" x-model="action" required>
                        <option value="show">Show question</option>
                        <option value="hide">Hide question</option>
                        <option value="jump_to_question">Jump to question</option>
                        <option value="end_survey">End survey</option>
                    </select>
                </div>
                <div class="col-md-8" x-show="action !== 'end_survey'">
                    <label class="form-label">Target question</label>
                    <select name="target_question_id" class="form-select">
                        <option value="">Select question...</option>
                        @foreach ($survey->questions as $q)
                            <option value="{{ $q->id }}">{{ $q->question_text }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-3">Add Rule</button>
        </form>
    </div>
</div>
