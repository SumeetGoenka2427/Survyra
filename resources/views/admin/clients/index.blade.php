<x-admin-layout title="Clients">
    <form id="clients-filters" class="d-flex flex-wrap gap-2 align-items-center mb-3" data-data-url="{{ route('admin.clients.data') }}">
        <input type="search" name="search" value="{{ $search }}" class="form-control form-control-sm" style="max-width: 260px;" placeholder="Search company name...">
        <select name="status" class="form-select form-select-sm" style="max-width: 180px;">
            <option value="">All statuses</option>
            <option value="active" @selected($status === 'active')>Active</option>
            <option value="trial" @selected($status === 'trial')>Trial</option>
            <option value="inactive" @selected($status === 'inactive')>Inactive</option>
        </select>
        <a href="{{ route('admin.clients.create') }}" class="btn btn-primary btn-sm ms-auto">
            <i class="bi bi-plus-lg"></i> Add Client
        </a>
    </form>

    <div id="clients-fragment">
        @include('admin.clients._fragment')
    </div>

    <script src="{{ asset('assets/js/clients.js') }}" defer></script>
</x-admin-layout>
