<div class="sq-toggle-wrap">
    <input type="radio" class="sq-option-input" name="answer" id="answer-no" value="no" autocomplete="off" @required($question->is_required) @checked(($existingAnswer ?? null) === 'no')>
    <label class="sq-toggle-half sq-toggle-no" for="answer-no">No</label>

    <input type="radio" class="sq-option-input" name="answer" id="answer-yes" value="yes" autocomplete="off" @required($question->is_required) @checked(($existingAnswer ?? null) === 'yes')>
    <label class="sq-toggle-half sq-toggle-yes" for="answer-yes">Yes</label>
</div>
