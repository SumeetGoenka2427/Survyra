@php $fieldId = 'answer-'.$question->id; @endphp
<div class="sq-floating mb-2">
    <input type="tel" name="answer" id="{{ $fieldId }}" class="sq-input-modern" placeholder=" " value="{{ $existingAnswer ?? '' }}" @required($question->is_required)>
    <label for="{{ $fieldId }}">Your phone number</label>
</div>
