@php $max = $question->settings['max_stars'] ?? 5; @endphp
<div class="sq-star-row">
    @for ($i = $max; $i >= 1; $i--)
        <input type="radio" class="sq-option-input" name="answer" id="answer-{{ $i }}" value="{{ $i }}" autocomplete="off" @required($question->is_required)>
        <label class="sq-star-label" for="answer-{{ $i }}"><i class="bi bi-star-fill"></i></label>
    @endfor
</div>
