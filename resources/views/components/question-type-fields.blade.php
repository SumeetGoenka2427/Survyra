@props(['prefix', 'questionTypes', 'selectedTypeId' => null, 'options' => [], 'settings' => []])
@php
    $initialGroup = $questionTypes->firstWhere('id', $selectedTypeId)?->contract()?->builderGroup() ?? '';
    $initialKey = $questionTypes->firstWhere('id', $selectedTypeId)?->key ?? '';
    $styleMap = $questionTypes->mapWithKeys(fn ($qt) => [$qt->id => $qt->contract()->availableStyles()]);
    $initialStyle = $settings['display_style'] ?? 'default';
    $optionsText = collect($options)->map(fn ($option) => is_array($option) ? ($option['label'] ?? '') : $option)->implode("\n");
    $imageOptionRows = collect($options)
        ->map(fn ($option) => is_array($option)
            ? ['label' => $option['label'] ?? '', 'image' => $option['image'] ?? '', 'value' => $option['value'] ?? '']
            : ['label' => $option, 'image' => '', 'value' => ''])
        ->values()
        ->all();
    if (empty($imageOptionRows)) {
        $imageOptionRows = [['label' => '', 'image' => '', 'value' => '']];
    }
@endphp
<div
    x-data="{
        group: '{{ $initialGroup }}',
        typeId: {{ $selectedTypeId ?? 'null' }},
        typeKey: '{{ $initialKey }}',
        styles: @js($styleMap),
        imageOptions: @js($imageOptionRows),
    }"
>
    <div class="mb-3">
        <label for="{{ $prefix }}-question_type_id" class="form-label">Question Type</label>
        <select
            name="question_type_id"
            id="{{ $prefix }}-question_type_id"
            class="form-select"
            required
            @change="group = $event.target.selectedOptions[0].dataset.group; typeId = Number($event.target.value); typeKey = $event.target.selectedOptions[0].dataset.key"
        >
            <option value="">Select a type...</option>
            @foreach ($questionTypes as $questionType)
                <option
                    value="{{ $questionType->id }}"
                    data-group="{{ $questionType->contract()->builderGroup() }}"
                    data-key="{{ $questionType->key }}"
                    @selected($selectedTypeId === $questionType->id)
                >{{ $questionType->label }}</option>
            @endforeach
        </select>
    </div>

    <div x-show="Object.keys(styles[typeId] || {}).length > 1" class="mb-3">
        <label for="{{ $prefix }}-display_style" class="form-label">Display Style</label>
        <select name="display_style" id="{{ $prefix }}-display_style" class="form-select">
            <template x-for="(styleLabel, styleKey) in (styles[typeId] || {})" :key="styleKey">
                <option :value="styleKey" x-text="styleLabel" :selected="styleKey === '{{ $initialStyle }}'"></option>
            </template>
        </select>
        <div class="form-text">How this question is drawn on the public survey page.</div>
    </div>

    <div x-show="(group === 'choice' || group === 'matrix') && typeKey !== 'image_choice'" class="mb-3">
        <label for="{{ $prefix }}-options_text" class="form-label" x-text="group === 'matrix' ? 'Rows (one per line)' : 'Options (one per line)'"></label>
        <textarea name="options_text" id="{{ $prefix }}-options_text" class="form-control" rows="4">{{ $optionsText }}</textarea>
    </div>

    <div x-show="typeKey === 'image_choice'" class="mb-3">
        <label class="form-label">Image Options</label>
        <template x-for="(option, index) in imageOptions" :key="index">
            <div class="row g-2 align-items-center mb-2">
                <div class="col-md-4">
                    <input type="text" class="form-control form-control-sm" placeholder="Label" x-model="option.label">
                </div>
                <div class="col-md-5">
                    <input type="url" class="form-control form-control-sm" placeholder="Image URL" x-model="option.image">
                </div>
                <div class="col-md-2">
                    <input type="text" class="form-control form-control-sm" placeholder="Value (optional)" x-model="option.value">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-sm btn-outline-danger" @click="imageOptions.length > 1 && imageOptions.splice(index, 1)">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </template>
        <button type="button" class="btn btn-sm btn-outline-secondary" @click="imageOptions.push({ label: '', image: '', value: '' })">
            <i class="bi bi-plus"></i> Add Option
        </button>
        <input type="hidden" name="image_options_json" :value="JSON.stringify(imageOptions.filter(o => o.label.trim() !== ''))">
        <div class="form-text">Each option needs a label. The image URL is optional — a placeholder icon shows if left blank.</div>
    </div>

    <div x-show="group === 'scale' || group === 'matrix'" class="row">
        <div class="col-md-3">
            <label class="form-label">Scale Min</label>
            <input type="number" name="scale_min" class="form-control" value="{{ $settings['scale_min'] ?? '' }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Scale Max</label>
            <input type="number" name="scale_max" class="form-control" value="{{ $settings['scale_max'] ?? '' }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Low Label</label>
            <input type="text" name="low_label" class="form-control" value="{{ $settings['low_label'] ?? '' }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">High Label</label>
            <input type="text" name="high_label" class="form-control" value="{{ $settings['high_label'] ?? '' }}">
        </div>
        <div class="col-md-3 mt-2">
            <label class="form-label">Max Stars (rating only)</label>
            <input type="number" name="max_stars" class="form-control" value="{{ $settings['max_stars'] ?? '' }}">
        </div>
    </div>

    <div x-show="typeKey === 'image_choice'" class="row mb-3">
        <div class="col-md-6">
            <div class="form-check mt-4">
                <input type="checkbox" name="multiple" value="1" class="form-check-input" id="{{ $prefix }}-multiple" @checked($settings['multiple'] ?? false)>
                <label class="form-check-label" for="{{ $prefix }}-multiple">Allow multiple selections</label>
            </div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Max choices (optional)</label>
            <input type="number" name="max_choices" min="1" class="form-control" value="{{ $settings['max_choices'] ?? '' }}">
        </div>
    </div>

    <div x-show="typeKey === 'checkbox'" class="mb-3">
        <label class="form-label">Max choices (optional)</label>
        <input type="number" name="max_choices" min="1" class="form-control" value="{{ $settings['max_choices'] ?? '' }}">
        <div class="form-text">Limit how many boxes a respondent can check. Leave blank for no limit.</div>
    </div>

    <div x-show="typeKey === 'file_upload'" class="row mb-3">
        <div class="col-md-6">
            <label class="form-label">Max file size (KB)</label>
            <input type="number" name="max_file_size" min="1" class="form-control" value="{{ $settings['max_file_size'] ?? '' }}" placeholder="10240">
        </div>
        <div class="col-md-6">
            <label class="form-label">Allowed file types</label>
            <input type="text" name="allowed_types" class="form-control" value="{{ implode(', ', $settings['allowed_types'] ?? []) }}" placeholder="pdf, doc, docx, jpg, png">
            <div class="form-text">Comma-separated extensions, no dots.</div>
        </div>
    </div>

    <div class="mb-3">
        <label for="{{ $prefix }}-help_text" class="form-label">Help Text (optional)</label>
        <input type="text" name="help_text" id="{{ $prefix }}-help_text" class="form-control" value="{{ $settings['help_text'] ?? '' }}" placeholder="Shown under the question to guide respondents">
    </div>
</div>
