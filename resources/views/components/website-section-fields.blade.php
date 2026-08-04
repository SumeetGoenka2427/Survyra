@props(['prefix', 'sectionKey', 'content' => [], 'pages' => []])
@php
    $linkPicker = function (string $field) use ($content) {
        $link = $content[$field] ?? null;
        return [
            'type' => is_array($link) ? ($link['type'] ?? 'none') : 'none',
            'page_id' => is_array($link) ? ($link['page_id'] ?? '') : '',
            'url' => is_array($link) ? ($link['url'] ?? '') : '',
        ];
    };
@endphp

@switch ($sectionKey)
    @case ('hero')
        @php $cta = $linkPicker('cta_link'); @endphp
        <div class="mb-2">
            <label class="form-label">Heading</label>
            <input type="text" name="heading" class="form-control" value="{{ $content['heading'] ?? '' }}" required>
        </div>
        <div class="mb-2">
            <label class="form-label">Subheading</label>
            <input type="text" name="subheading" class="form-control" value="{{ $content['subheading'] ?? '' }}">
        </div>
        <div class="row">
            <div class="col-md-6 mb-2">
                <label class="form-label">Button Text</label>
                <input type="text" name="cta_text" class="form-control" value="{{ $content['cta_text'] ?? '' }}">
            </div>
            <div class="col-md-6 mb-2">
                <label class="form-label">Background Image URL</label>
                <input type="url" name="background_image" class="form-control" value="{{ $content['background_image'] ?? '' }}">
            </div>
        </div>
        <x-website-link-picker :prefix="$prefix.'-cta'" name="cta_link" :value="$cta" :pages="$pages" />
        @break

    @case ('text_block')
        <div class="mb-2">
            <label class="form-label">Heading</label>
            <input type="text" name="heading" class="form-control" value="{{ $content['heading'] ?? '' }}">
        </div>
        <div class="mb-2">
            <label class="form-label">Body</label>
            <textarea name="body" class="form-control" rows="5" required>{{ $content['body'] ?? '' }}</textarea>
        </div>
        @break

    @case ('gallery')
        <div x-data="{ images: @js(!empty($content['images']) ? array_values($content['images']) : []) }" class="mb-2">
            <label class="form-label">Images</label>
            <template x-for="(image, index) in images" :key="index">
                <div class="row g-2 align-items-center mb-2">
                    <div class="col-md-7">
                        <input type="url" class="form-control form-control-sm" placeholder="Image URL" x-model="image.image_path">
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control form-control-sm" placeholder="Caption (optional)" x-model="image.caption">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-sm btn-outline-danger" @click="images.splice(index, 1)">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </template>
            <button type="button" class="btn btn-sm btn-outline-secondary" @click="images.push({ image_path: '', caption: '' })">
                <i class="bi bi-plus"></i> Add Image
            </button>
            <input type="hidden" name="images_json" :value="JSON.stringify(images.filter(i => i.image_path.trim() !== ''))">
        </div>
        @break

    @case ('testimonials')
        <div x-data="{ items: @js(!empty($content['items']) ? array_values($content['items']) : []) }" class="mb-2">
            <label class="form-label">Testimonials</label>
            <template x-for="(item, index) in items" :key="index">
                <div class="row g-2 align-items-center mb-2">
                    <div class="col-md-5">
                        <input type="text" class="form-control form-control-sm" placeholder="Quote" x-model="item.quote">
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control form-control-sm" placeholder="Author" x-model="item.author">
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control form-control-sm" placeholder="Role (optional)" x-model="item.role">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-sm btn-outline-danger" @click="items.splice(index, 1)">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </template>
            <button type="button" class="btn btn-sm btn-outline-secondary" @click="items.push({ quote: '', author: '', role: '' })">
                <i class="bi bi-plus"></i> Add Testimonial
            </button>
            <input type="hidden" name="items_json" :value="JSON.stringify(items.filter(i => i.quote.trim() !== ''))">
        </div>
        @break

    @case ('cta')
        @php $button = $linkPicker('button_link'); @endphp
        <div class="mb-2">
            <label class="form-label">Heading</label>
            <input type="text" name="heading" class="form-control" value="{{ $content['heading'] ?? '' }}" required>
        </div>
        <div class="row">
            <div class="col-md-6 mb-2">
                <label class="form-label">Button Text</label>
                <input type="text" name="button_text" class="form-control" value="{{ $content['button_text'] ?? '' }}" required>
            </div>
            <div class="col-md-6 mb-2">
                <label class="form-label">Variant</label>
                <select name="variant" class="form-select">
                    @foreach (['primary', 'secondary'] as $variant)
                        <option value="{{ $variant }}" @selected(($content['variant'] ?? 'primary') === $variant)>{{ ucfirst($variant) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <x-website-link-picker :prefix="$prefix.'-button'" name="button_link" :value="$button" :pages="$pages" />
        @break

    @case ('contact_form')
        <div class="mb-2">
            <label class="form-label">Heading</label>
            <input type="text" name="heading" class="form-control" value="{{ $content['heading'] ?? '' }}">
        </div>
        <div class="mb-2">
            <label class="form-label">Intro Text</label>
            <input type="text" name="intro" class="form-control" value="{{ $content['intro'] ?? '' }}">
        </div>
        <div x-data="{ fields: @js(!empty($content['fields']) ? array_values($content['fields']) : []) }" class="mb-2">
            <label class="form-label">Form Fields</label>
            <template x-for="(field, index) in fields" :key="index">
                <div class="row g-2 align-items-center mb-2">
                    <div class="col-md-3">
                        <input type="text" class="form-control form-control-sm" placeholder="Field key (e.g. name)" x-model="field.key">
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control form-control-sm" placeholder="Label" x-model="field.label">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select form-select-sm" x-model="field.type">
                            <option value="text">Text</option>
                            <option value="email">Email</option>
                            <option value="tel">Phone</option>
                            <option value="textarea">Textarea</option>
                        </select>
                    </div>
                    <div class="col-md-2 form-check">
                        <input type="checkbox" class="form-check-input" x-model="field.required">
                        <label class="form-check-label small">Required</label>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-sm btn-outline-danger" @click="fields.splice(index, 1)">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </template>
            <button type="button" class="btn btn-sm btn-outline-secondary" @click="fields.push({ key: '', label: '', type: 'text', required: false })">
                <i class="bi bi-plus"></i> Add Field
            </button>
            <input type="hidden" name="fields_json" :value="JSON.stringify(fields.filter(f => f.key.trim() !== ''))">
        </div>
        @break
@endswitch
