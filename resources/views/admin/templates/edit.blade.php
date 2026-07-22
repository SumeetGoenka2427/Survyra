<x-admin-layout :title="$template->name">
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('admin.survey-preview', ['template' => $template->id]) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-eye"></i> Preview Survey
        </a>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white"><strong>Template Details</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.templates.update', $template) }}">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <x-form-input name="name" label="Template Name" :value="$template->name" required />
                    </div>
                    <div class="col-md-6">
                        <x-form-input name="industry_category" label="Industry" :value="$template->industry_category" required />
                    </div>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" class="form-control" rows="2">{{ old('description', $template->description) }}</textarea>
                </div>
                <x-form-select
                    name="layout"
                    label="Survey Layout"
                    :options="['multi_step' => 'Multi-step (one question per screen)', 'conversational' => 'Conversational (Typeform-style)', 'one_page' => 'One-page (all questions at once)', 'card_based' => 'Card-based (all questions as cards)', 'section_wizard' => 'Section Wizard (grouped steps)']"
                    :value="$template->layout"
                />
                <div class="form-check form-switch mb-3">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1" class="form-check-input" @checked($template->is_active)>
                    <label for="is_active" class="form-check-label">Active</label>
                </div>

                <button type="submit" class="btn btn-primary btn-sm">Save Details</button>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white"><strong>Questions</strong></div>
        <div class="list-group list-group-flush">
            @forelse ($template->questions as $question)
                <div class="list-group-item" x-data="{ editing: false }">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="badge text-bg-light text-dark border me-2">{{ $question->questionType->label }}</span>
                            {{ $question->question_text }}
                            @if ($question->is_required)
                                <span class="badge text-bg-danger ms-1">Required</span>
                            @endif
                        </div>
                        <div class="d-flex gap-1 flex-shrink-0">
                            <form action="{{ route('admin.templates.questions.move-up', [$template, $question]) }}" method="POST">
                                @csrf @method('PATCH')
                                <button class="btn btn-sm btn-outline-secondary" title="Move up"><i class="bi bi-arrow-up"></i></button>
                            </form>
                            <form action="{{ route('admin.templates.questions.move-down', [$template, $question]) }}" method="POST">
                                @csrf @method('PATCH')
                                <button class="btn btn-sm btn-outline-secondary" title="Move down"><i class="bi bi-arrow-down"></i></button>
                            </form>
                            <button type="button" class="btn btn-sm btn-outline-primary" @click="editing = !editing">Edit</button>
                            <form action="{{ route('admin.templates.questions.destroy', [$template, $question]) }}" method="POST" onsubmit="return confirm('Remove this question?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </div>
                    </div>

                    <div x-show="editing" class="mt-3 border-top pt-3">
                        <form method="POST" action="{{ route('admin.templates.questions.update', [$template, $question]) }}">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label">Question Text</label>
                                <input type="text" name="question_text" class="form-control" value="{{ $question->question_text }}" required>
                            </div>

                            <x-question-type-fields
                                :prefix="'q-'.$question->id"
                                :question-types="$questionTypes"
                                :selected-type-id="$question->question_type_id"
                                :options-text="implode(\"\n\", $question->options ?? [])"
                                :settings="$question->settings ?? []"
                            />

                            <div class="form-check mb-3 mt-2">
                                <input type="hidden" name="is_required" value="0">
                                <input type="checkbox" name="is_required" value="1" class="form-check-input" id="required-{{ $question->id }}" @checked($question->is_required)>
                                <label class="form-check-label" for="required-{{ $question->id }}">Required</label>
                            </div>

                            <button type="submit" class="btn btn-primary btn-sm">Save Question</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="list-group-item text-center text-muted py-4">No questions yet - add one below.</div>
            @endforelse
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white"><strong>Add Question</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.templates.questions.store', $template) }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Question Text</label>
                    <input type="text" name="question_text" class="form-control" required>
                </div>

                <x-question-type-fields prefix="add" :question-types="$questionTypes" />

                <div class="form-check mb-3 mt-2">
                    <input type="hidden" name="is_required" value="0">
                    <input type="checkbox" name="is_required" value="1" class="form-check-input" id="add-is_required" checked>
                    <label class="form-check-label" for="add-is_required">Required</label>
                </div>

                <button type="submit" class="btn btn-primary">Add Question</button>
            </form>
        </div>
    </div>
</x-admin-layout>
