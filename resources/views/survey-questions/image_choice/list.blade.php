<div class="mb-3">
    <label class="form-label fw-semibold">
        {{ $question->question_text }}
        @if ($question->is_required) <span class="text-danger">*</span> @endif
    </label>

    @php
        $multiple = $question->settings['multiple'] ?? false;
        $inputName = $multiple ? 'answer[]' : 'answer';
        $options = $question->options ?? [];
    @endphp

    <div class="d-flex flex-column gap-2 image-choice-list">
        @foreach ($options as $index => $option)
            @php
                $imageUrl = is_array($option) ? ($option['image'] ?? '') : '';
                $label = is_array($option) ? ($option['label'] ?? $option) : $option;
                $value = is_array($option) ? ($option['value'] ?? $label) : $option;
            @endphp
            <label class="image-choice-card d-flex align-items-center gap-3 border rounded-3 p-2" style="cursor: pointer;">
                <input type="{{ $multiple ? 'checkbox' : 'radio' }}"
                       name="{{ $inputName }}"
                       value="{{ $value }}"
                       class="d-none image-choice-input"
                       @if ($question->is_required && !$multiple) required @endif
                       @checked($multiple
                           ? (is_array($existingAnswer ?? null) && in_array((string) $value, array_map('strval', $existingAnswer), true))
                           : ((string) ($existingAnswer ?? '') === (string) $value))>
                <div class="image-wrapper flex-shrink-0">
                    @if ($imageUrl)
                        <img src="{{ $imageUrl }}" alt="{{ $label }}" class="rounded" style="height: 56px; width: 56px; object-fit: cover;">
                    @else
                        <div class="bg-light d-flex align-items-center justify-content-center rounded" style="height: 56px; width: 56px;">
                            <i class="bi bi-image text-muted"></i>
                        </div>
                    @endif
                </div>
                <span>{{ $label }}</span>
            </label>
        @endforeach
    </div>

    @error('answer')
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>

@push('scripts')
<script>
document.querySelectorAll('.image-choice-card').forEach(card => {
    const input = card.querySelector('.image-choice-input');
    if (input.checked) card.classList.add('border-primary', 'bg-primary-subtle');
    card.addEventListener('click', () => {
        if (input.type === 'radio') {
            document.querySelectorAll('.image-choice-card').forEach(c => c.classList.remove('border-primary', 'bg-primary-subtle'));
        }
        if (input.checked) {
            input.checked = false;
            card.classList.remove('border-primary', 'bg-primary-subtle');
        } else {
            input.checked = true;
            card.classList.add('border-primary', 'bg-primary-subtle');
        }
    });
});
</script>
@endpush
