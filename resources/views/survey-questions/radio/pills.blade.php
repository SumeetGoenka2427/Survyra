<div class="sq-options sq-options-row">
    @foreach ($question->options ?? [] as $index => $option)
        <input type="radio" class="sq-option-input" name="answer" id="answer-{{ $index }}" value="{{ $option }}" autocomplete="off" @required($question->is_required)>
        <label class="sq-pill" for="answer-{{ $index }}">{{ $option }}</label>
    @endforeach
</div>
