<input type="email" name="answer" class="sq-input-modern mb-2" value="{{ $existingAnswer ?? '' }}" @required($question->is_required)>
