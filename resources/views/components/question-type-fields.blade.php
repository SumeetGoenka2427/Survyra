@props(['prefix', 'questionTypes', 'selectedTypeId' => null, 'optionsText' => '', 'settings' => []])
@php
    $initialGroup = $questionTypes->firstWhere('id', $selectedTypeId)?->contract()?->builderGroup() ?? '';
    $styleMap = $questionTypes->mapWithKeys(fn ($qt) => [$qt->id => $qt->contract()->availableStyles()]);
    $initialStyle = $settings['display_style'] ?? 'default';
@endphp
<div x-data="{ group: '{{ $initialGroup }}', typeId: {{ $selectedTypeId ?? 'null' }}, styles: @js($styleMap) }">
    <div class="mb-3">
        <label for="{{ $prefix }}-question_type_id" class="form-label">Question Type</label>
        <select
            name="question_type_id"
            id="{{ $prefix }}-question_type_id"
            class="form-select"
            required
            @change="group = $event.target.selectedOptions[0].dataset.group; typeId = Number($event.target.value)"
        >
            <option value="">Select a type...</option>
            @foreach ($questionTypes as $questionType)
                <option
                    value="{{ $questionType->id }}"
                    data-group="{{ $questionType->contract()->builderGroup() }}"
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

    <div x-show="group === 'choice' || group === 'matrix'" class="mb-3">
        <label for="{{ $prefix }}-options_text" class="form-label" x-text="group === 'matrix' ? 'Rows (one per line)' : 'Options (one per line)'"></label>
        <textarea name="options_text" id="{{ $prefix }}-options_text" class="form-control" rows="4">{{ $optionsText }}</textarea>
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
</div>
