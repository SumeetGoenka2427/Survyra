@php
    $min = $question->settings['scale_min'] ?? 0;
    $max = $question->settings['scale_max'] ?? 10;
    $mid = (int) round(($min + $max) / 2);
    $fieldId = 'answer-'.$question->id;
@endphp
<div class="sq-slider-wrap mb-2">
    <div class="sq-slider-output" id="{{ $fieldId }}-output">{{ $mid }}</div>
    <input
        type="range"
        name="answer"
        id="{{ $fieldId }}"
        class="form-range"
        min="{{ $min }}"
        max="{{ $max }}"
        step="1"
        value="{{ $mid }}"
        oninput="document.getElementById('{{ $fieldId }}-output').textContent = this.value"
        @required($question->is_required)
    >
</div>
<div class="sq-scale-labels">
    <span>{{ $question->settings['low_label'] ?? 'Low' }}</span>
    <span>{{ $question->settings['high_label'] ?? 'High' }}</span>
</div>
