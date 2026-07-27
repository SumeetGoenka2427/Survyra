@php
    $emojis = ['1' => ['😡', 'Terrible'], '2' => ['🙁', 'Bad'], '3' => ['😐', 'Okay'], '4' => ['🙂', 'Good'], '5' => ['😄', 'Great']];
@endphp
<div class="sq-options sq-options-row">
    @foreach ($emojis as $value => [$emoji, $label])
        <input type="radio" class="sq-option-input" name="answer" id="answer-{{ $value }}" value="{{ $value }}" autocomplete="off" @required($question->is_required) @checked((string) ($existingAnswer ?? '') === (string) $value)>
        <label class="sq-emoji-card" for="answer-{{ $value }}">
            <span class="emoji">{{ $emoji }}</span>
            <span class="label">{{ $label }}</span>
        </label>
    @endforeach
</div>
