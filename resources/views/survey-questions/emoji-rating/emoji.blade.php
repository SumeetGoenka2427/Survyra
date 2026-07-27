@php
    $emojis = ['1' => '😡', '2' => '🙁', '3' => '😐', '4' => '🙂', '5' => '😄'];
@endphp
<div class="sq-emoji-row">
    @foreach ($emojis as $value => $emoji)
        <input type="radio" class="sq-option-input" name="answer" id="answer-{{ $value }}" value="{{ $value }}" autocomplete="off" @required($question->is_required) @checked((string) ($existingAnswer ?? '') === (string) $value)>
        <label class="sq-emoji-btn" for="answer-{{ $value }}">{{ $emoji }}</label>
    @endforeach
</div>
