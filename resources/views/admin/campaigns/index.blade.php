<x-admin-layout title="Campaigns">
    <form id="campaigns-filters" class="d-flex flex-wrap gap-2 align-items-end mb-3" data-data-url="{{ route('admin.campaigns.data') }}">
        <div>
            <label class="form-label small text-muted mb-1">Client</label>
            <select name="client_id" class="form-select form-select-sm" style="min-width: 220px;">
                <option value="">All clients</option>
                @foreach ($clients as $client)
                    <option value="{{ $client->id }}" @selected($selectedClient?->id === $client->id)>{{ $client->company_name }}</option>
                @endforeach
            </select>
        </div>
        <a href="{{ route('admin.campaigns.create', request()->only('client_id')) }}" class="btn btn-primary btn-sm ms-auto">
            <i class="bi bi-plus-lg"></i> New Campaign
        </a>
    </form>

    <div id="campaigns-fragment">
        @include('admin.campaigns._fragment')
    </div>

    <script src="{{ asset('assets/js/campaigns.js') }}" defer></script>
</x-admin-layout>
