<select name="answer" class="sq-input-modern mb-2" @required($question->is_required)>
    <option value="">Select an option...</option>
    @foreach ($question->options ?? [] as $option)
        <option value="{{ $option }}" @selected((string) ($existingAnswer ?? '') === (string) $option)>{{ $option }}</option>
    @endforeach
</select>
