@php
    $min = $question->settings['scale_min'] ?? 1;
    $max = $question->settings['scale_max'] ?? 5;
@endphp
<div class="sq-nps-row sq-nps-circles">
    @for ($i = $min; $i <= $max; $i++)
        <input type="radio" class="sq-option-input" name="answer" id="answer-{{ $i }}" value="{{ $i }}" autocomplete="off" @required($question->is_required)>
        <label class="sq-btn" for="answer-{{ $i }}">{{ $i }}</label>
    @endfor
</div>
<div class="sq-scale-labels">
    <span>{{ $question->settings['low_label'] ?? 'Very dissatisfied' }}</span>
    <span>{{ $question->settings['high_label'] ?? 'Very satisfied' }}</span>
</div>
