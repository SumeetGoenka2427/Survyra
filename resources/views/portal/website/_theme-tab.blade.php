@php $theme = $website->theme; @endphp

<div class="mb-4">
    <label class="form-label">Choose a Theme</label>
    <form method="POST" action="{{ route('portal.website.theme.update') }}" class="row g-2 align-items-end">
        @csrf @method('PATCH')
        <div class="col-md-8">
            <select name="theme_id" class="form-select">
                <option value="">-- keep current --</option>
                @foreach ($themes as $available)
                    <option value="{{ $available->id }}" @selected($theme && $theme->id === $available->id)>
                        {{ $available->name }} @if($available->is_system) (Preset) @else (Custom) @endif
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-outline-primary w-100">Use This Theme</button>
        </div>
    </form>
</div>

<hr>

<h6 class="mb-3">Customize</h6>
<form method="POST" action="{{ route('portal.website.theme.update') }}">
    @csrf @method('PATCH')
    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Primary Color</label>
            <input type="color" name="primary_color" class="form-control form-control-color" value="{{ $theme->primary_color ?? '#0d6efd' }}">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Secondary Color</label>
            <input type="color" name="secondary_color" class="form-control form-control-color" value="{{ $theme->secondary_color ?? '#6c757d' }}">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Background</label>
            <input type="color" name="background" class="form-control form-control-color" value="{{ $theme->background ?? '#ffffff' }}">
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Heading Font</label>
            <input type="text" name="heading_font" class="form-control" value="{{ $theme->heading_font ?? 'system-ui' }}">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Body Font</label>
            <input type="text" name="body_font" class="form-control" value="{{ $theme->body_font ?? 'system-ui' }}">
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Button Style</label>
            <select name="button_style" class="form-select">
                @foreach (['rounded', 'square', 'pill'] as $style)
                    <option value="{{ $style }}" @selected(($theme->button_style ?? 'rounded') === $style)>{{ ucfirst($style) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Border Radius</label>
            <input type="number" name="border_radius" class="form-control" min="0" max="50" value="{{ $theme->border_radius ?? 8 }}">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Container Width</label>
            <select name="container_width" class="form-select">
                @foreach (['boxed', 'full'] as $width)
                    <option value="{{ $width }}" @selected(($theme->container_width ?? 'boxed') === $width)>{{ ucfirst($width) }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label">Custom CSS</label>
        <textarea name="custom_css" class="form-control font-monospace" rows="4">{{ $theme->custom_css ?? '' }}</textarea>
    </div>
    <button type="submit" class="btn btn-primary">Save Theme</button>
</form>
