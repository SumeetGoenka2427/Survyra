@php $max = $question->settings['max_stars'] ?? 5; @endphp
<div class="sq-badge-row">
    @for ($i = 1; $i <= $max; $i++)
        <input type="radio" class="sq-option-input" name="answer" id="answer-{{ $i }}" value="{{ $i }}" autocomplete="off" @required($question->is_required)>
        <label class="sq-btn sq-badge" for="answer-{{ $i }}">{{ $i }}</label>
    @endfor
</div>
