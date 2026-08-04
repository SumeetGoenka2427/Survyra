<div id="pages-sortable" class="mb-4" data-reorder-url="{{ route('portal.website.pages.reorder') }}">
    @forelse ($website->pages as $page)
        <div class="card mb-3 sortable-item" data-id="{{ $page->id }}" x-data="{ open: false }">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="drag-handle text-muted" style="cursor:grab;" title="Drag to reorder">
                        <i class="bi bi-grip-vertical"></i>
                    </span>
                    <strong>{{ $page->title }}</strong>
                    @if ($page->is_home)
                        <span class="badge text-bg-primary">Home</span>
                    @endif
                    <span class="text-muted small">{{ $page->sections->count() }} section(s)</span>
                </div>
                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-sm btn-outline-primary" @click="open = !open">
                        <span x-text="open ? 'Hide' : 'Edit'"></span>
                    </button>
                    @unless ($page->is_home && $website->pages->count() === 1)
                        <form action="{{ route('portal.website.pages.destroy', $page) }}" method="POST" onsubmit="return confirm('Delete this page?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    @endunless
                </div>
            </div>

            <div class="card-body" x-show="open" x-cloak>
                <form method="POST" action="{{ route('portal.website.pages.update', $page) }}" class="row g-2 align-items-end mb-4">
                    @csrf @method('PUT')
                    <div class="col-md-6">
                        <label class="form-label">Page Title</label>
                        <input type="text" name="title" class="form-control" value="{{ $page->title }}" required>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check mt-4">
                            <input type="hidden" name="is_home" value="0">
                            <input type="checkbox" name="is_home" value="1" class="form-check-input" id="home-{{ $page->id }}" @checked($page->is_home)>
                            <label class="form-check-label" for="home-{{ $page->id }}">Set as Home page</label>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-primary w-100">Save</button>
                    </div>
                </form>

                <h6 class="mb-2">Sections</h6>
                <div class="sections-sortable" data-reorder-url="{{ route('portal.website.sections.reorder', $page) }}">
                    @forelse ($page->sections as $section)
                        <div class="list-group-item sortable-item border rounded mb-2" data-id="{{ $section->id }}" x-data="{ editing: false }">
                            <div class="d-flex justify-content-between align-items-center p-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="drag-handle text-muted" style="cursor:grab;">
                                        <i class="bi bi-grip-vertical"></i>
                                    </span>
                                    <span class="badge text-bg-light text-dark border">{{ $section->sectionType->label }}</span>
                                    <span class="text-muted small">style: {{ $section->settings['style'] ?? 'default' }}</span>
                                </div>
                                <div class="d-flex gap-1">
                                    <form action="{{ route('portal.website.sections.duplicate', $section) }}" method="POST">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-secondary" title="Duplicate">
                                            <i class="bi bi-copy"></i>
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-outline-primary" @click="editing = !editing">Edit</button>
                                    <form action="{{ route('portal.website.sections.destroy', $section) }}" method="POST" onsubmit="return confirm('Remove this section?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </div>
                            </div>

                            <div class="p-2 border-top" x-show="editing" x-cloak>
                                <form method="POST" action="{{ route('portal.website.sections.update', $section) }}">
                                    @csrf @method('PUT')
                                    <div class="mb-2">
                                        <label class="form-label">Style</label>
                                        <select name="style" class="form-select">
                                            @foreach ($section->sectionType->contract()->availableStyles() as $key => $label)
                                                <option value="{{ $key }}" @selected(($section->settings['style'] ?? 'default') === $key)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <x-website-section-fields
                                        :prefix="'ws-'.$section->id"
                                        :section-key="$section->sectionType->key"
                                        :content="$section->content ?? []"
                                        :pages="$website->pages"
                                    />
                                    <button type="submit" class="btn btn-primary btn-sm mt-2">Save Section</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted small">No sections yet - add one below.</p>
                    @endforelse
                </div>

                <form method="POST" action="{{ route('portal.website.sections.store', $page) }}" class="row g-2 align-items-end mt-2">
                    @csrf
                    <div class="col-md-8">
                        <select name="section_type_id" class="form-select" required>
                            <option value="">Add a section...</option>
                            @foreach ($sectionTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-outline-primary w-100">Add Section</button>
                    </div>
                </form>
            </div>
        </div>
    @empty
        <p class="text-muted">No pages yet.</p>
    @endforelse
</div>

<form method="POST" action="{{ route('portal.website.pages.store') }}" class="row g-2 align-items-end">
    @csrf
    <div class="col-md-8">
        <input type="text" name="title" class="form-control" placeholder="New page title (e.g. About)" required>
    </div>
    <div class="col-md-4">
        <button type="submit" class="btn btn-primary w-100">Add Page</button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Sortable === 'undefined') return;
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    function wireSortable(list) {
        if (!list || !list.dataset.reorderUrl) return;
        Sortable.create(list, {
            handle: '.drag-handle',
            animation: 150,
            onEnd: function () {
                const items = [...list.querySelectorAll(':scope > .sortable-item')].map((el, index) => ({
                    id: parseInt(el.dataset.id),
                    order: index,
                }));

                fetch(list.dataset.reorderUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ items }),
                });
            },
        });
    }

    wireSortable(document.getElementById('pages-sortable'));
    document.querySelectorAll('.sections-sortable').forEach(wireSortable);
});
</script>
