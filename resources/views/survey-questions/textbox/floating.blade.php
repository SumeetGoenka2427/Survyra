@php $fieldId = 'answer-'.$question->id; @endphp
<div class="sq-floating mb-2">
    <input type="text" name="answer" id="{{ $fieldId }}" class="sq-input-modern" maxlength="255" placeholder=" " value="{{ $existingAnswer ?? '' }}" @required($question->is_required)>
    <label for="{{ $fieldId }}">Your answer</label>
</div>
