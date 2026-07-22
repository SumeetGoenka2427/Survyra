<x-admin-layout title="Surveys">
    <form id="surveys-filters" class="d-flex flex-wrap gap-2 align-items-end mb-3" data-data-url="{{ route('admin.surveys.data') }}">
        <div>
            <label class="form-label small text-muted mb-1">Client</label>
            <select name="client_id" class="form-select form-select-sm" style="min-width: 200px;">
                <option value="">All clients</option>
                @foreach ($clients as $client)
                    <option value="{{ $client->id }}" @selected($clientId == $client->id)>{{ $client->company_name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label small text-muted mb-1">Status</label>
            <select name="status" class="form-select form-select-sm" style="min-width: 160px;">
                <option value="">All statuses</option>
                <option value="draft" @selected($status === 'draft')>Draft</option>
                <option value="published" @selected($status === 'published')>Published</option>
                <option value="archived" @selected($status === 'archived')>Archived</option>
            </select>
        </div>
        <a href="{{ route('admin.surveys.create') }}" class="btn btn-primary btn-sm ms-auto">
            <i class="bi bi-plus-lg"></i> New Survey
        </a>
    </form>

    <div id="surveys-fragment">
        @include('admin.surveys._fragment')
    </div>

    <script src="{{ asset('assets/js/surveys.js') }}" defer></script>
</x-admin-layout>
