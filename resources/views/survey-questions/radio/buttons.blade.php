<div class="sq-options">
    @foreach ($question->options ?? [] as $index => $option)
        <input type="radio" class="sq-option-input" name="answer" id="answer-{{ $index }}" value="{{ $option }}" autocomplete="off" @required($question->is_required)>
        <label class="sq-btn justify-content-start" for="answer-{{ $index }}">{{ $option }}</label>
    @endforeach
</div>
