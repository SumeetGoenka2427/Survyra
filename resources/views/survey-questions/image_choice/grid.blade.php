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

    <div class="row g-3 image-choice-grid">
        @foreach ($options as $index => $option)
            @php
                $imageUrl = is_array($option) ? ($option['image'] ?? '') : '';
                $label = is_array($option) ? ($option['label'] ?? $option) : $option;
                $value = is_array($option) ? ($option['value'] ?? $label) : $option;
            @endphp
            <div class="col-6 col-md-4 col-lg-3">
                <label class="image-choice-card d-block border rounded-3 p-2 text-center cursor-pointer" style="cursor: pointer;">
                    <input type="{{ $multiple ? 'checkbox' : 'radio' }}"
                           name="{{ $inputName }}"
                           value="{{ $value }}"
                           class="d-none image-choice-input"
                           @if ($question->is_required && !$multiple) required @endif
                           @checked($multiple
                               ? (is_array($existingAnswer ?? null) && in_array((string) $value, array_map('strval', $existingAnswer), true))
                               : ((string) ($existingAnswer ?? '') === (string) $value))>
                    <div class="image-wrapper mb-2">
                        @if ($imageUrl)
                            <img src="{{ $imageUrl }}" alt="{{ $label }}" class="img-fluid rounded" style="height: 120px; object-fit: cover; width: 100%;">
                        @else
                            <div class="bg-light d-flex align-items-center justify-content-center rounded" style="height: 120px;">
                                <i class="bi bi-image text-muted fs-1"></i>
                            </div>
                        @endif
                    </div>
                    <span class="small d-block text-truncate">{{ $label }}</span>
                </label>
            </div>
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