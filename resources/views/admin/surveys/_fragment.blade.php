@if ($surveys->isEmpty())
    <x-ds-empty-state
        icon="bi-ui-checks"
        title="No surveys found"
        description="Try adjusting your filters, or create a survey from a template."
        action-label="New Survey"
        :action-url="route('admin.surveys.create')"
    />
@else
    <div class="row g-3">
        @foreach ($surveys as $survey)
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 ds-hover h-100 ds-fade-in">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge text-bg-{{ $survey->status === 'published' ? 'success' : ($survey->status === 'draft' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($survey->status) }}
                            </span>
                            <span class="text-muted small">{{ $survey->theme?->name ?? 'No theme' }}</span>
                        </div>

                        <h6 class="fw-bold mb-1">{{ $survey->title }}</h6>
                        <div class="text-muted small mb-3">{{ $survey->client->company_name }}</div>

                        <div class="d-flex gap-3 text-muted small mb-3">
                            <span><i class="bi bi-list-check"></i> {{ $survey->questions_count }} questions</span>
                            <span><i class="bi bi-inboxes"></i> {{ $survey->responses_count }} responses</span>
                        </div>

                        <div class="mt-auto d-flex flex-wrap gap-2">
                            <a href="{{ route('admin.surveys.edit', $survey) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-pencil"></i> Edit
                            </a>

                            <form action="{{ route('admin.surveys.duplicate', $survey) }}" method="POST">
                                @csrf
                                <button class="btn btn-sm btn-outline-secondary" title="Duplicate survey">
                                    <i class="bi bi-copy"></i>
                                </button>
                            </form>

                            @if ($survey->status === 'draft')
                                <button type="button" class="btn btn-sm btn-success" data-survey-publish data-url="{{ route('admin.surveys.publish', $survey) }}">
                                    <i class="bi bi-rocket-takeoff"></i> Publish
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" data-survey-delete data-url="{{ route('admin.surveys.destroy', $survey) }}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            @elseif ($survey->status === 'published')
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-survey-archive data-url="{{ route('admin.surveys.archive', $survey) }}">
                                    <i class="bi bi-archive"></i> Archive
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-3 surveys-pagination">
        {{ $surveys->links() }}
    </div>
@endif
