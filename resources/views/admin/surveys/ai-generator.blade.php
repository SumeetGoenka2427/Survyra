<x-admin-layout title="AI Survey Generator">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center mb-4">
                <i class="bi bi-magic fs-1 me-3 text-primary"></i>
                <div>
                    <h4 class="mb-1">AI Survey Generator</h4>
                    <p class="text-muted mb-0">Describe your survey in plain language and let AI create it for you.</p>
                </div>
            </div>

            <form id="aiSurveyForm" class="row g-3">
                @csrf
                <div class="col-md-9">
                    <label class="form-label">Describe your survey</label>
                    <textarea name="prompt" class="form-control" rows="3"
                              placeholder="e.g., Create a customer satisfaction survey for our e-commerce store. Include NPS, product quality rating, and delivery experience questions."
                              required minlength="10"></textarea>
                    <div class="form-text small">Be specific about the topic, question types, and any particular metrics you want.</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Language</label>
                    <select name="language" class="form-select">
                        <option value="en">English</option>
                        <option value="es">Spanish</option>
                        <option value="fr">French</option>
                        <option value="de">German</option>
                        <option value="pt">Portuguese</option>
                        <option value="hi">Hindi</option>
                        <option value="ar">Arabic</option>
                        <option value="zh">Chinese</option>
                        <option value="ja">Japanese</option>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary btn-lg" id="generateBtn">
                        <i class="bi bi-stars me-1"></i> Generate Survey
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="resultArea" class="d-none">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>Generated Survey</strong>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="regenerateBtn">
                        <i class="bi bi-arrow-repeat me-1"></i> Regenerate
                    </button>
                    <button type="button" class="btn btn-sm btn-success" id="createSurveyBtn">
                        <i class="bi bi-plus-circle me-1"></i> Create Survey
                    </button>
                </div>
            </div>
            <div class="card-body" id="generatedContent">
                <!-- Generated content will be rendered here -->
            </div>
        </div>
    </div>

    <div id="loadingArea" class="d-none text-center py-5">
        <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Generating...</span>
        </div>
        <h5>Generating your survey...</h5>
        <p class="text-muted">This may take a few seconds.</p>
    </div>
</x-admin-layout>

@push('scripts')
<script>
document.getElementById('aiSurveyForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.target;
    const prompt = form.querySelector('[name="prompt"]').value;
    const language = form.querySelector('[name="language"]').value;

    document.getElementById('loadingArea').classList.remove('d-none');
    document.getElementById('resultArea').classList.add('d-none');
    document.getElementById('generateBtn').disabled = true;

    try {
        const response = await fetch('{{ route("admin.ai-survey.generate") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({ prompt, language }),
        });

        const data = await response.json();
        renderGeneratedSurvey(data);
    } catch (error) {
        alert('Failed to generate survey. Please try again.');
    } finally {
        document.getElementById('loadingArea').classList.add('d-none');
        document.getElementById('generateBtn').disabled = false;
    }
});

function renderGeneratedSurvey(data) {
    const container = document.getElementById('generatedContent');
    const resultArea = document.getElementById('resultArea');

    let html = `<h4 class="mb-3">${escapeHtml(data.title || 'Untitled Survey')}</h4>`;
    html += `<div class="list-group list-group-flush mb-3">`;

    (data.questions || []).forEach((q, i) => {
        const required = q.is_required ? '<span class="text-danger ms-1">*</span>' : '';
        const options = q.options ? `<div class="text-muted small mt-1">Options: ${q.options.join(', ')}</div>` : '';
        html += `
            <div class="list-group-item">
                <div class="d-flex justify-content-between">
                    <span><strong>Q${i + 1}.</strong> ${escapeHtml(q.text)}${required}</span>
                    <span class="badge text-bg-light text-dark border">${q.type}</span>
                </div>
                ${options}
            </div>
        `;
    });

    html += `</div>`;
    container.innerHTML = html;
    resultArea.classList.remove('d-none');

    // Store for creation
    window._generatedSurvey = data;
}

document.getElementById('createSurveyBtn')?.addEventListener('click', () => {
    const data = window._generatedSurvey;
    if (!data) return;

    // Redirect to survey creation with pre-filled data
    const params = new URLSearchParams({
        title: data.title || 'AI Generated Survey',
        questions: JSON.stringify(data.questions || []),
        source: 'ai_generator',
    });
    window.location.href = `{{ route('admin.surveys.create') }}?${params.toString()}`;
});

document.getElementById('regenerateBtn')?.addEventListener('click', () => {
    document.getElementById('aiSurveyForm').dispatchEvent(new Event('submit'));
});

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endpush