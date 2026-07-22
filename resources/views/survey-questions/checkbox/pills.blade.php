<div class="sq-options sq-options-row">
    @foreach ($question->options ?? [] as $index => $option)
        <input type="checkbox" class="sq-option-input" name="answer[]" id="answer-{{ $index }}" value="{{ $option }}">
        <label class="sq-pill" for="answer-{{ $index }}">{{ $option }}</label>
    @endforeach
</div>
