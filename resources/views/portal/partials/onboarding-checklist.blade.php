@php
    $checklist = app(\App\Services\OnboardingService::class)->checklistFor(auth('client')->user()->client);
    $items = $checklist->toChecklistArray();
    $progress = $checklist->progressPercent();
    $allDone = $progress === 100;
@endphp

@if (!$checklist->dismissed && !$allDone)
    <div class="card border-0 shadow-sm mb-4 onboarding-checklist">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">
                    <i class="bi bi-rocket-takeoff text-primary me-1"></i>
                    Getting Started
                </h6>
                <form method="POST" action="{{ route('portal.onboarding.dismiss') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary" title="Dismiss">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </form>
            </div>

            <div class="progress mb-3" style="height: 8px;">
                <div class="progress-bar bg-success" role="progressbar"
                     style="width: {{ $progress }}%"
                     aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                </div>
            </div>
            <small class="text-muted">{{ $progress }}% complete</small>

            <div class="list-group list-group-flush mt-2">
                @foreach ($items as $item)
                    <div class="list-group-item d-flex align-items-center px-0 py-2 border-0">
                        @if ($item['done'])
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            <span class="text-muted text-decoration-line-through small">{{ $item['label'] }}</span>
                        @else
                            <i class="bi bi-circle text-secondary me-2"></i>
                            <span class="small">{{ $item['label'] }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif