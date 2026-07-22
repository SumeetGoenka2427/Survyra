<x-admin-layout title="Survey Templates">
    <form id="templates-filters" class="d-flex flex-wrap gap-2 align-items-center mb-3" data-data-url="{{ route('admin.templates.data') }}">
        <input type="search" name="search" value="{{ $search }}" class="form-control form-control-sm" style="max-width: 260px;" placeholder="Search templates...">
        <a href="{{ route('admin.templates.create') }}" class="btn btn-primary btn-sm ms-auto">
            <i class="bi bi-plus-lg"></i> New Template
        </a>
    </form>

    <div id="templates-fragment">
        @include('admin.templates._fragment')
    </div>

    <script src="{{ asset('assets/js/templates.js') }}" defer></script>
</x-admin-layout>
