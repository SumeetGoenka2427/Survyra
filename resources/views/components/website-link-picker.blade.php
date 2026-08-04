@props(['prefix', 'name', 'value' => ['type' => 'none', 'page_id' => '', 'url' => ''], 'pages' => []])

<div x-data="{ linkType: '{{ $value['type'] }}' }" class="mb-2">
    <label class="form-label">Link (optional)</label>
    <select name="{{ $name }}_type" class="form-select form-select-sm mb-1" x-model="linkType">
        <option value="none">No link</option>
        <option value="page">Link to a page on this site</option>
        <option value="external">External URL</option>
    </select>
    <div x-show="linkType === 'page'">
        <select name="{{ $name }}_page_id" class="form-select form-select-sm">
            @foreach ($pages as $page)
                <option value="{{ $page->id }}" @selected((string) $value['page_id'] === (string) $page->id)>{{ $page->title }}</option>
            @endforeach
        </select>
    </div>
    <div x-show="linkType === 'external'">
        <input type="url" name="{{ $name }}_url" class="form-control form-control-sm" placeholder="https://..." value="{{ $value['url'] }}">
    </div>
</div>
