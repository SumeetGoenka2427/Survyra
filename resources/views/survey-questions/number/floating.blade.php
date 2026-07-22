@php $fieldId = 'answer-'.$question->id; @endphp
<div class="sq-floating mb-2">
    <input type="number" name="answer" id="{{ $fieldId }}" class="sq-input-modern" placeholder=" " @required($question->is_required)>
    <label for="{{ $fieldId }}">Your answer</label>
</div>
