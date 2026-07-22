<div class="sq-options">
    @foreach ($question->options ?? [] as $index => $option)
        <div class="form-check">
            <input type="checkbox" class="form-check-input" name="answer[]" id="answer-{{ $index }}" value="{{ $option }}">
            <label class="form-check-label" for="answer-{{ $index }}">{{ $option }}</label>
        </div>
    @endforeach
</div>
