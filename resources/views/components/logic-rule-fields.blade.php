@props([
    'questions',
    'initialConditions' => [['question_id' => '', 'operator' => 'equals', 'value' => '']],
    'initialAction' => 'show',
    'initialConditionOperator' => 'AND',
    'initialTargetQuestionId' => '',
])
<div x-data="{ conditions: @js($initialConditions), action: '{{ $initialAction }}', conditionOperator: '{{ $initialConditionOperator }}' }">
    <template x-for="(condition, index) in conditions" :key="index">
        <div class="row g-2 mb-2 align-items-center">
            <div class="col-auto" x-show="index > 0">
                <select x-model="conditionOperator" class="form-select form-select-sm" style="width:80px;">
                    <option value="AND">AND</option>
                    <option value="OR">OR</option>
                </select>
            </div>
            <div class="col-md-4">
                <select :name="`conditions[${index}][question_id]`" class="form-select" x-model="condition.question_id" required>
                    <option value="">IF question...</option>
                    @foreach ($questions as $q)
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
                @foreach ($questions as $q)
                    <option value="{{ $q->id }}" @selected((string) $initialTargetQuestionId === (string) $q->id)>{{ $q->question_text }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
