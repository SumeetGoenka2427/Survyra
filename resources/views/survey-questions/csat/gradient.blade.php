@php
    $min = $question->settings['scale_min'] ?? 1;
    $max = $question->settings['scale_max'] ?? 5;
    $range = max($max - $min, 1);
@endphp
<div class="sq-nps-row sq-nps-gradient">
    @for ($i = $min; $i <= $max; $i++)
        @php $hue = round((($i - $min) / $range) * 120); @endphp
        <input type="radio" class="sq-option-input" name="answer" id="answer-{{ $i }}" value="{{ $i }}" autocomplete="off" @required($question->is_required) @checked((string) ($existingAnswer ?? '') === (string) $i)>
        <label class="sq-btn sq-nps-gradient-btn" for="answer-{{ $i }}" style="background: hsl({{ $hue }}, 70%, 50%);">{{ $i }}</label>
    @endfor
</div>
<div class="sq-scale-labels">
    <span>{{ $question->settings['low_label'] ?? 'Very dissatisfied' }}</span>
    <span>{{ $question->settings['high_label'] ?? 'Very satisfied' }}</span>
</div>
