@php
    $t = $theme ?? null;
@endphp
<div x-data="{
    name: '{{ old('name', $t->name ?? '') }}',
    primary: '{{ old('primary_color', $t->primary_color ?? '#0d6efd') }}',
    secondary: '{{ old('secondary_color', $t->secondary_color ?? '#6c757d') }}',
    background: '{{ old('background', $t->background ?? '#ffffff') }}',
    font: '{{ old('font', $t->font ?? 'system-ui') }}',
    buttonStyle: '{{ old('button_style', $t->button_style ?? 'rounded') }}',
    radius: {{ (int) old('border_radius', $t->border_radius ?? 8) }},
}">
    <div class="row">
        <div class="col-md-7">
            <div class="mb-3">
                <label for="name" class="form-label">Theme Name</label>
                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" x-model="name" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">Primary Color</label>
                    <input type="color" name="primary_color" class="form-control form-control-color w-100" x-model="primary">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Secondary Color</label>
                    <input type="color" name="secondary_color" class="form-control form-control-color w-100" x-model="secondary">
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-6">
                    <label class="form-label">Background</label>
                    <input type="color" name="background" class="form-control form-control-color w-100" x-model="background">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Font</label>
                    <input type="text" name="font" class="form-control" x-model="font">
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-6">
                    <label class="form-label">Button Style</label>
                    <select name="button_style" class="form-select" x-model="buttonStyle">
                        <option value="rounded">Rounded</option>
                        <option value="square">Square</option>
                        <option value="pill">Pill</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Border Radius (px)</label>
                    <input type="number" name="border_radius" class="form-control" x-model.number="radius" min="0" max="32">
                </div>
            </div>
            <div class="mb-3 mt-2">
                <label class="form-label">Progress Bar Style</label>
                <select name="progress_bar_style" class="form-select">
                    <option value="bar" @selected(old('progress_bar_style', $t->progress_bar_style ?? 'bar') === 'bar')>Bar</option>
                    <option value="dots" @selected(old('progress_bar_style', $t->progress_bar_style ?? 'bar') === 'dots')>Dots</option>
                    <option value="steps" @selected(old('progress_bar_style', $t->progress_bar_style ?? 'bar') === 'steps')>Steps</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Custom CSS</label>
                <textarea name="custom_css" class="form-control" rows="4">{{ old('custom_css', $t->custom_css ?? '') }}</textarea>
            </div>
        </div>

        <div class="col-md-5">
            <label class="form-label">Live Preview</label>
            <div class="p-4 text-center border rounded"
                 :style="`background: ${background}; font-family: ${font}; border-radius: ${radius}px;`">
                <div class="mb-2 fw-semibold" :style="`color: ${primary}`" x-text="name || 'Theme Name'"></div>
                <span class="btn btn-sm text-white"
                      :style="`background: ${primary}; border-radius: ${buttonStyle === 'pill' ? '999px' : (buttonStyle === 'square' ? '0' : '6px')};`">
                    Sample Button
                </span>
                <div class="mt-2 small" :style="`color: ${secondary}`">Secondary text</div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary">{{ $t ? 'Save Changes' : 'Create Theme' }}</button>
        <a href="{{ route('admin.themes.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </div>
</div>
