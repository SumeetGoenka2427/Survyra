@php $fieldId = 'answer-'.$question->id; @endphp
<div class="sq-floating mb-2">
    <textarea name="answer" id="{{ $fieldId }}" class="sq-input-modern" rows="3" maxlength="5000" placeholder=" " data-autosize @required($question->is_required)>{{ $existingAnswer ?? '' }}</textarea>
    <label for="{{ $fieldId }}">Your answer</label>
</div>
