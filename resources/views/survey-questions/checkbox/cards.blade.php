<div class="sq-options">
    @foreach ($question->options ?? [] as $index => $option)
        <input type="checkbox" class="sq-option-input" name="answer[]" id="answer-{{ $index }}" value="{{ $option }}" @checked(is_array($existingAnswer ?? null) && in_array((string) $option, array_map('strval', $existingAnswer), true))>
        <label class="sq-card sq-card-checkbox" for="answer-{{ $index }}">
            <span class="sq-card-indicator"></span>
            <span>{{ $option }}</span>
        </label>
    @endforeach
</div>
