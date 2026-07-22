<div class="sq-options">
    @foreach ($question->options ?? [] as $index => $option)
        <input type="radio" class="sq-option-input" name="answer" id="answer-{{ $index }}" value="{{ $option }}" autocomplete="off" @required($question->is_required)>
        <label class="sq-card sq-card-radio" for="answer-{{ $index }}">
            <span class="sq-card-indicator"></span>
            <span>{{ $option }}</span>
        </label>
    @endforeach
</div>
