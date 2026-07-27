<div class="mb-3">
    <label class="form-label fw-semibold">
        {{ $question->question_text }}
        @if ($question->is_required) <span class="text-danger">*</span> @endif
    </label>

    <div class="file-upload-wrapper">
        <div class="border-2 border-dashed rounded-3 p-4 text-center bg-light" style="border-style: dashed !important;">
            <i class="bi bi-cloud-upload fs-1 text-muted"></i>
            <p class="mt-2 mb-1 text-muted">Drag & drop files here or click to browse</p>
            <p class="text-muted small mb-2">
                Allowed: {{ implode(', ', $question->settings['allowed_types'] ?? ['pdf', 'doc', 'docx', 'jpg', 'png']) }}
                (Max: {{ ($question->settings['max_file_size'] ?? 10240) / 1024 }}MB)
            </p>
            <input type="file"
                   name="answer"
                   class="form-control d-none file-input"
                   accept="{{ implode(',', array_map(fn($t) => ".$t", $question->settings['allowed_types'] ?? ['pdf','doc','docx','jpg','png'])) }}"
                   @if ($question->is_required && empty($existingAnswer ?? null)) required @endif>
            <button type="button" class="btn btn-outline-primary browse-btn">
                <i class="bi bi-folder2-open me-1"></i> Browse Files
            </button>
            <div class="selected-file mt-2 d-none">
                <span class="badge text-bg-info file-name"></span>
                <button type="button" class="btn btn-sm btn-outline-danger ms-2 clear-file">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        </div>

        @if (!empty($existingAnswer['original_name']))
            <div class="text-muted small mt-2">
                <i class="bi bi-paperclip"></i> Already uploaded: {{ $existingAnswer['original_name'] }} - choose a new file to replace it.
            </div>
        @endif
    </div>

    @error('answer')
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>

@push('scripts')
<script>
document.querySelectorAll('.file-upload-wrapper').forEach(wrapper => {
    const input = wrapper.querySelector('.file-input');
    const browseBtn = wrapper.querySelector('.browse-btn');
    const selectedFile = wrapper.querySelector('.selected-file');
    const fileName = wrapper.querySelector('.file-name');
    const clearBtn = wrapper.querySelector('.clear-file');

    browseBtn?.addEventListener('click', () => input?.click());

    input?.addEventListener('change', () => {
        if (input.files.length > 0) {
            fileName.textContent = input.files[0].name;
            selectedFile.classList.remove('d-none');
            browseBtn.classList.add('d-none');
        }
    });

    clearBtn?.addEventListener('click', () => {
        input.value = '';
        selectedFile.classList.add('d-none');
        browseBtn.classList.remove('d-none');
    });
});
</script>
@endpush