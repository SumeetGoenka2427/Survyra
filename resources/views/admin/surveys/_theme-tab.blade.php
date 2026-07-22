<form method="POST" action="{{ route('admin.surveys.update', $survey) }}" class="row g-2 align-items-end mb-4">
    @csrf
    @method('PUT')
    <input type="hidden" name="title" value="{{ $survey->title }}">
    <div class="col-md-5">
        <x-form-select
            name="theme_id"
            label="Theme"
            :options="$themes->pluck('name', 'id')"
            :value="$survey->theme_id"
            placeholder="No theme selected"
        />
    </div>
    <div class="col-md-4">
        <x-form-select
            name="layout"
            label="Survey Layout"
            :options="['multi_step' => 'Multi-step (one question per screen)', 'conversational' => 'Conversational (Typeform-style)', 'one_page' => 'One-page (all questions at once)', 'card_based' => 'Card-based (all questions as cards)', 'section_wizard' => 'Section Wizard (grouped steps)']"
            :value="$survey->layout"
        />
    </div>
    <div class="col-md-3">
        <button type="submit" class="btn btn-primary w-100">Save</button>
    </div>
</form>

@if ($survey->theme)
    <div class="card border-0 shadow-sm mb-3" style="max-width: 360px;">
        <div class="p-4 text-center" style="background: {{ $survey->theme->background }}; font-family: {{ $survey->theme->font }};">
            <div class="mb-2" style="color: {{ $survey->theme->primary_color }}; font-weight: 600;">{{ $survey->theme->name }}</div>
            <span class="btn btn-sm" style="background: {{ $survey->theme->primary_color }}; color: #fff; border-radius: {{ $survey->theme->button_style === 'pill' ? '999px' : ($survey->theme->button_style === 'square' ? '0' : '6px') }};">
                Sample Button
            </span>
        </div>
    </div>
@endif

<h6 class="mb-2">Duplicate a theme to customize for {{ $survey->client->company_name }}</h6>
<p class="text-muted small">Creates a copy of the chosen theme scoped to this client, then opens it for editing.</p>
<div class="row g-2">
    @foreach ($themes->where('is_system', true) as $theme)
        <div class="col-md-3">
            <form action="{{ route('admin.surveys.theme.duplicate', [$survey, $theme]) }}" method="POST">
                @csrf
                <div class="card border-0 shadow-sm mb-2">
                    <div class="p-3 text-center" style="background: {{ $theme->background }};">
                        <span style="color: {{ $theme->primary_color }}; font-weight: 600;">{{ $theme->name }}</span>
                    </div>
                </div>
                <button type="submit" class="btn btn-sm btn-outline-primary w-100">Duplicate for this client</button>
            </form>
        </div>
    @endforeach
</div>
