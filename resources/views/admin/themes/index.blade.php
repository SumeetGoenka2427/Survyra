<x-admin-layout title="Survey Themes">
    <form id="themes-filters" class="d-flex flex-wrap gap-2 align-items-center mb-3" data-data-url="{{ route('admin.themes.data') }}">
        <input type="search" name="search" value="{{ $search }}" class="form-control form-control-sm" style="max-width: 260px;" placeholder="Search themes...">
        <a href="{{ route('admin.themes.create') }}" class="btn btn-primary btn-sm ms-auto">
            <i class="bi bi-plus-lg"></i> New Theme
        </a>
    </form>

    <div id="themes-fragment">
        @include('admin.themes._fragment')
    </div>

    <script src="{{ asset('assets/js/themes.js') }}" defer></script>
</x-admin-layout>
