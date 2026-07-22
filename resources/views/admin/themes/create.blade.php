<x-admin-layout title="New Theme">
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.themes.store') }}">
                @csrf
                @include('admin.themes._form')
            </form>
        </div>
    </div>
</x-admin-layout>
