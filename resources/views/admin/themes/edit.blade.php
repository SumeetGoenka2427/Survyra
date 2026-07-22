<x-admin-layout :title="$theme->name">
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('admin.survey-preview', ['theme' => $theme->id]) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-eye"></i> Preview Theme
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.themes.update', $theme) }}">
                @csrf
                @method('PUT')
                @include('admin.themes._form', ['theme' => $theme])
            </form>
        </div>
    </div>
</x-admin-layout>
