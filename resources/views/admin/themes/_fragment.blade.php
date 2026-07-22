@if ($themes->isEmpty())
    <x-ds-empty-state
        icon="bi-palette"
        title="No themes found"
        description="Try a different search, or create a new theme."
        action-label="New Theme"
        :action-url="route('admin.themes.create')"
    />
@else
    <div class="row g-3">
        @foreach ($themes as $theme)
            <div class="col-md-4">
                <div class="card border-0 ds-hover h-100 ds-fade-in">
                    <div class="p-4 text-center" style="background: {{ $theme->background }}; border-radius: {{ $theme->border_radius }}px {{ $theme->border_radius }}px 0 0; font-family: {{ $theme->font }};">
                        <div class="mb-2" style="color: {{ $theme->primary_color }}; font-weight: 600;">{{ $theme->name }}</div>
                        <span class="btn btn-sm"
                              style="background: {{ $theme->primary_color }}; color: #fff; border-radius: {{ $theme->button_style === 'pill' ? '999px' : ($theme->button_style === 'square' ? '0' : '6px') }};">
                            Sample Button
                        </span>
                        <div class="mt-2 small" style="color: {{ $theme->secondary_color }};">Secondary text</div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge text-bg-{{ $theme->is_system ? 'secondary' : 'primary' }}">
                                {{ $theme->is_system ? 'System' : 'Custom' }}
                            </span>
                            <span class="text-muted small" title="Surveys using this theme">
                                <i class="bi bi-ui-checks"></i> {{ $theme->surveys_count }} in use
                            </span>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('admin.survey-preview', ['theme' => $theme->id]) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> Preview
                            </a>
                            <a href="{{ route('admin.themes.edit', $theme) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <button type="button" class="btn btn-sm btn-outline-danger" data-theme-delete data-url="{{ route('admin.themes.destroy', $theme) }}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
