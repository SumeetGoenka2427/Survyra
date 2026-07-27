@php $max = $question->settings['max_stars'] ?? 5; @endphp
<div class="sq-heart-row">
    @for ($i = $max; $i >= 1; $i--)
        <input type="radio" class="sq-option-input" name="answer" id="answer-{{ $i }}" value="{{ $i }}" autocomplete="off" @required($question->is_required) @checked((string) ($existingAnswer ?? '') === (string) $i)>
        <label class="sq-heart-label" for="answer-{{ $i }}"><i class="bi bi-heart-fill"></i></label>
    @endfor
</div>
