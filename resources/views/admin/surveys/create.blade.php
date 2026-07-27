<x-admin-layout title="New Survey">
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            @if (!empty($aiQuestions))
                <div class="alert alert-info d-flex align-items-center gap-2">
                    <i class="bi bi-robot fs-5"></i>
                    <div>{{ count($aiQuestions) }} question{{ count($aiQuestions) === 1 ? '' : 's' }} from the AI Survey Generator will be added to this survey once created.</div>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.surveys.store') }}" x-data="{ mode: '{{ old('mode', !empty($aiQuestions) ? 'blank' : 'template') }}' }">
                @csrf
                @if (!empty($aiQuestions))
                    <input type="hidden" name="ai_questions" value="{{ json_encode($aiQuestions) }}">
                @endif

                <x-form-select
                    name="client_id"
                    label="Client"
                    :options="$clients->pluck('company_name', 'id')"
                    :value="$selectedClientId"
                    placeholder="Select a client..."
                    required
                />

                <x-form-input name="title" label="Survey Title" required placeholder="e.g. Customer Satisfaction Survey" :value="$aiTitle" />

                {{-- Mode toggle --}}
                <div class="mb-3">
                    <label class="form-label">Starting Point</label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="mode" id="mode-template" value="template" x-model="mode">
                            <label class="form-check-label" for="mode-template">From a template</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="mode" id="mode-blank" value="blank" x-model="mode">
                            <label class="form-check-label" for="mode-blank">Start from scratch</label>
                        </div>
                    </div>
                </div>

                {{-- Template picker --}}
                <div x-show="mode === 'template'" x-cloak>
                    <div class="mb-3">
                        <label for="survey_template_id" class="form-label">Template</label>
                        <select name="survey_template_id" id="survey_template_id" class="form-select @error('survey_template_id') is-invalid @enderror">
                            <option value="">Select a template...</option>
                            @foreach ($templatesByIndustry as $industry => $templates)
                                <optgroup label="{{ $industry }}">
                                    @foreach ($templates as $template)
                                        <option value="{{ $template->id }}" @selected(old('survey_template_id') == $template->id)>
                                            {{ $template->name }}
                                            ({{ $template->questions_count ?? $template->questions->count() }} questions)
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        @error('survey_template_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- Blank layout picker --}}
                <div x-show="mode === 'blank'" x-cloak>
                    <x-form-select
                        name="layout"
                        label="Survey Layout"
                        :options="['multi_step' => 'Multi-Step', 'one_page' => 'One Page', 'card_based' => 'Card Based', 'section_wizard' => 'Section Wizard', 'conversational' => 'Conversational']"
                        :value="old('layout', 'multi_step')"
                    />
                </div>

                @error('mode')<div class="text-danger small mb-2">{{ $message }}</div>@enderror

                <x-form-select
                    name="theme_id"
                    label="Theme (optional)"
                    :options="$themes->pluck('name', 'id')"
                    placeholder="Use the default theme"
                />
                <div class="form-text mb-3">You can change this later, or duplicate it into a custom theme for this client.</div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Create & Customize</button>
                    <a href="{{ route('admin.surveys.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
