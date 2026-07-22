<div class="d-flex gap-3 justify-content-center mb-2">
    <input type="radio" class="sq-option-input" name="answer" id="answer-yes" value="yes" autocomplete="off" @required($question->is_required)>
    <label class="sq-btn px-4" for="answer-yes">Yes</label>

    <input type="radio" class="sq-option-input" name="answer" id="answer-no" value="no" autocomplete="off" @required($question->is_required)>
    <label class="sq-btn px-4" for="answer-no">No</label>
</div>
