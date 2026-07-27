<input type="text" name="answer" class="sq-input-modern mb-2" maxlength="255" placeholder="Type your answer..." value="{{ $existingAnswer ?? '' }}" @required($question->is_required)>
