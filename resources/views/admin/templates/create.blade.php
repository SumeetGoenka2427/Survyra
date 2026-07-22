<x-admin-layout title="New Template">
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.templates.store') }}">
                @csrf

                <x-form-input name="name" label="Template Name" required autofocus />
                <x-form-input name="industry_category" label="Industry" required placeholder="e.g. Healthcare, Restaurant" />
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                </div>
                <x-form-select
                    name="layout"
                    label="Survey Layout"
                    :options="['multi_step' => 'Multi-step (one question per screen)', 'conversational' => 'Conversational (Typeform-style)', 'one_page' => 'One-page (all questions at once)', 'card_based' => 'Card-based (all questions as cards)', 'section_wizard' => 'Section Wizard (grouped steps)']"
                    value="multi_step"
                />

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Create & Add Questions</button>
                    <a href="{{ route('admin.templates.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
