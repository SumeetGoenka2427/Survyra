@php
    // Exactly one <h1> per page: the first hero-category section renders it;
    // every other section (including a second hero) renders <h2>. Pages with
    // no hero at all still get a real (visually-hidden) <h1> so the page
    // title is never entirely absent from the heading hierarchy.
    $hasHero = collect($sections)->contains(fn ($s) => $s['type'] === 'hero');
    $h1Rendered = false;
    $isPreview = $isPreview ?? false;
@endphp
@include('website._theme-vars', ['theme' => $theme ?? []])

@unless ($hasHero)
    <h1 class="visually-hidden">{{ $pageTitle ?? '' }}</h1>
@endunless

@foreach ($sections as $section)
    @php
        $sectionType = app(\App\Services\SectionTypeRegistry::class)->resolve($section['type']);
        $headingTag = (! $h1Rendered && $section['type'] === 'hero') ? 'h1' : 'h2';
        $h1Rendered = $h1Rendered || $headingTag === 'h1';
    @endphp
    @include($sectionType->renderComponent($section['style']), [
        'content' => $section['content'],
        'contactAction' => $contactAction ?? null,
        'sectionId' => $section['id'],
        'pageId' => $pageId,
        'headingTag' => $headingTag,
        'isPreview' => $isPreview,
    ])
@endforeach
