<?php

namespace App\SectionTypes;

class GallerySectionType extends AbstractSectionType
{
    public function key(): string
    {
        return 'gallery';
    }

    public function label(): string
    {
        return 'Image Gallery';
    }

    public function category(): string
    {
        return 'media';
    }

    public function availableStyles(): array
    {
        return [
            'grid' => 'Grid',
            'carousel' => 'Carousel',
        ];
    }

    public function renderComponent(string $style = 'default'): string
    {
        return 'website-sections.gallery.'.$this->resolveStyle($style);
    }

    public function validationRules(array $content): array
    {
        return [
            'images' => ['nullable', 'array'],
            'images.*.image_path' => ['required_with:images', 'string'],
            'images.*.caption' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function defaultContent(): array
    {
        return [
            'images' => [],
        ];
    }
}
