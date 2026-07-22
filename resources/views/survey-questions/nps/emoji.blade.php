@php
    $min = $question->settings['scale_min'] ?? 0;
    $max = $question->settings['scale_max'] ?? 10;
    $markers = ['😡' => 0, '🙁' => round($max * 0.3), '😐' => round($max * 0.5), '🙂' => round($max * 0.7), '😄' => $max];
@endphp
<div class="sq-emoji-groups">
    @foreach ($markers as $emoji => $value)
        <span title="score {{ $value }}">{{ $emoji }}</span>
    @endforeach
</div>
<div class="sq-nps-row">
    @for ($i = $min; $i <= $max; $i++)
        <input type="radio" class="sq-option-input" name="answer" id="answer-{{ $i }}" value="{{ $i }}" autocomplete="off" @required($question->is_required)>
        <label class="sq-btn" for="answer-{{ $i }}">{{ $i }}</label>
    @endfor
</div>
<div class="sq-scale-labels">
    <span>{{ $question->settings['low_label'] ?? 'Not likely' }}</span>
    <span>{{ $question->settings['high_label'] ?? 'Very likely' }}</span>
</div>
