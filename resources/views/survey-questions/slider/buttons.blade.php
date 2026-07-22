@php
    $min = $question->settings['scale_min'] ?? 0;
    $max = $question->settings['scale_max'] ?? 10;
@endphp
<div class="sq-nps-row">
    @for ($i = $min; $i <= $max; $i++)
        <input
            type="radio"
            class="sq-option-input"
            name="answer"
            id="answer-{{ $question->id }}-{{ $i }}"
            value="{{ $i }}"
            autocomplete="off"
            @required($question->is_required)
        >
        <label class="sq-btn" for="answer-{{ $question->id }}-{{ $i }}">{{ $i }}</label>
    @endfor
</div>
<div class="sq-scale-labels">
    <span>{{ $question->settings['low_label'] ?? 'Low' }}</span>
    <span>{{ $question->settings['high_label'] ?? 'High' }}</span>
</div>
