<x-admin-layout :title="$survey->title">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.surveys.update', $survey) }}" class="row g-2 align-items-end">
                @csrf
                @method('PUT')
                <div class="col-md-8">
                    <label class="form-label">Survey Title</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $survey->title) }}" required>
                </div>
                <input type="hidden" name="theme_id" value="{{ $survey->theme_id }}">
                <div class="col-md-4">
                    <button type="submit" class="btn btn-outline-primary">Save Title</button>
                    <span class="badge text-bg-{{ $survey->status === 'published' ? 'success' : ($survey->status === 'draft' ? 'warning' : 'secondary') }} ms-2">
                        {{ ucfirst($survey->status) }}
                    </span>
                    <a href="{{ route('admin.survey-preview', ['survey' => $survey->id]) }}" target="_blank" rel="noopener" class="btn btn-outline-primary ms-2">
                        <i class="bi bi-eye"></i> Preview
                    </a>
                </div>
            </form>
            <div class="text-muted small mt-2">
                Client: <strong>{{ $survey->client->company_name }}</strong>
                @if ($survey->template)
                    &middot; From template: <strong>{{ $survey->template->name }}</strong>
                @endif
            </div>
        </div>
    </div>

    <ul class="nav nav-tabs" id="builderTabs" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-questions" type="button">Questions</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-logic" type="button">Logic</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-theme" type="button">Theme</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-thankyou" type="button">Thank You</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-publish" type="button">Publish</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-qr-codes" type="button">QR Codes</button></li>
    </ul>

    <div class="tab-content bg-white border border-top-0 p-4 shadow-sm">
        <div class="tab-pane fade show active" id="tab-questions">
            @include('admin.surveys._questions-tab')
        </div>
        <div class="tab-pane fade" id="tab-logic">
            @include('admin.surveys._logic-tab')
        </div>
        <div class="tab-pane fade" id="tab-theme">
            @include('admin.surveys._theme-tab')
        </div>
        <div class="tab-pane fade" id="tab-thankyou">
            @include('admin.surveys._thankyou-tab')
        </div>
        <div class="tab-pane fade" id="tab-publish">
            @include('admin.surveys._publish-tab')
        </div>
        <div class="tab-pane fade" id="tab-qr-codes">
            @include('admin.surveys._qr-codes-tab', ['qrCodes' => app(\App\Services\QrCodeService::class)->forSurvey($survey)])
        </div>
    </div>
</x-admin-layout>
