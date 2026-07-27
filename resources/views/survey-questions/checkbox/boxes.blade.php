<div class="sq-options">
    @foreach ($question->options ?? [] as $index => $option)
        <div class="form-check">
            <input type="checkbox" class="form-check-input" name="answer[]" id="answer-{{ $index }}" value="{{ $option }}" @checked(is_array($existingAnswer ?? null) && in_array((string) $option, array_map('strval', $existingAnswer), true))>
            <label class="form-check-label" for="answer-{{ $index }}">{{ $option }}</label>
        </div>
    @endforeach
</div>
