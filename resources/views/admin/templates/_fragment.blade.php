@if ($templatesByIndustry->isEmpty())
    <x-ds-empty-state
        icon="bi-clipboard-data"
        title="No templates found"
        description="Try a different search, or create a new template from scratch."
        action-label="New Template"
        :action-url="route('admin.templates.create')"
    />
@else
    @foreach ($templatesByIndustry as $industry => $templates)
        <div class="mb-4">
            <h6 class="text-muted text-uppercase small fw-bold mb-2" style="letter-spacing: 0.05em;">{{ $industry }}</h6>
            <div class="row g-3">
                @foreach ($templates as $template)
                    @php $estimatedSeconds = $template->questions_count * 15; @endphp
                    <div class="col-md-6 col-lg-4">
                        <div class="card border-0 ds-hover h-100 ds-fade-in">
                            <div class="card-body d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge text-bg-{{ $template->is_active ? 'success' : 'secondary' }}">
                                        {{ $template->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                    <span class="badge text-bg-light text-dark border">
                                        <i class="bi bi-list-check"></i> {{ $template->questions_count }}
                                    </span>
                                </div>

                                <h6 class="fw-bold mb-1">{{ $template->name }}</h6>
                                <p class="text-muted small mb-2" style="min-height: 2.5em;">{{ \Illuminate\Support\Str::limit($template->description, 90) ?: 'No description.' }}</p>

                                <div class="d-flex flex-wrap gap-3 text-muted small mb-3">
                                    <span title="Estimated completion time"><i class="bi bi-clock"></i> ~{{ max(1, round($estimatedSeconds / 60)) }} min</span>
                                    <span title="Surveys created from this template"><i class="bi bi-copy"></i> {{ $template->surveys_count }} used</span>
                                </div>

                                <div class="text-muted small mb-3">
                                    <i class="bi bi-person"></i> {{ $template->createdBy?->name ?? 'Unknown' }}
                                    &middot; updated {{ $template->updated_at->diffForHumans() }}
                                </div>

                                <div class="mt-auto d-flex flex-wrap gap-2">
                                    <a href="{{ route('admin.survey-preview', ['template' => $template->id]) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> Preview
                                    </a>
                                    <a href="{{ route('admin.templates.edit', $template) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-template-duplicate data-url="{{ route('admin.templates.duplicate', $template) }}">
                                        <i class="bi bi-copy"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-template-delete data-url="{{ route('admin.templates.destroy', $template) }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
@endif
