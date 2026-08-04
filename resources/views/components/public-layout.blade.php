@props([
    'title' => 'Survyra',
    'description' => 'Survyra is customer survey software for small businesses. Build smart online surveys, collect QR and link feedback, and turn responses into 5-star reviews from one simple dashboard.',
])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ $description }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon-32.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/apple-touch-icon.png') }}">
    <title>{{ $title === 'Survyra' ? 'Survyra | Customer Survey Software & Feedback Platform for Small Businesses' : 'Survyra | '.$title }}</title>
    <meta property="og:title" content="{{ $title === 'Survyra' ? 'Survyra | Customer Survey Software & Feedback Platform for Small Businesses' : 'Survyra | '.$title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('assets/images/og-image.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="Survyra — customer survey software for small businesses">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title === 'Survyra' ? 'Survyra | Customer Survey Software & Feedback Platform for Small Businesses' : 'Survyra | '.$title }}">
    <meta name="twitter:description" content="{{ $description }}">
    <meta name="twitter:image" content="{{ asset('assets/images/og-image.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/public.css') }}">
    <noscript><style>.sv-reveal, .sv-anim { opacity: 1 !important; transform: none !important; }</style></noscript>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
    @if (config('services.analytics.ga_measurement_id'))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.analytics.ga_measurement_id') }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ config('services.analytics.ga_measurement_id') }}');
        </script>
    @endif
    <script type="application/ld+json">
        {!! json_encode([
            '@@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Organization',
                    'name' => 'Survyra',
                    'url' => url('/'),
                    'description' => 'Survyra is customer survey software for small businesses, combining online surveys, QR feedback, review requests and analytics in one dashboard.',
                ],
                [
                    '@type' => 'WebSite',
                    'name' => 'Survyra',
                    'url' => url('/'),
                ],
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
</head>
<body class="sv-landing">
    {{ $slot }}

    @if (config('services.whatsapp.number'))
        <a
            href="https://wa.me/{{ config('services.whatsapp.number') }}?text={{ urlencode('Hi! I\'d like to know more about Survyra.') }}"
            class="sv-whatsapp-fab"
            target="_blank"
            rel="noopener"
            aria-label="Chat on WhatsApp"
        ><i class="bi bi-whatsapp"></i></a>
    @endif

    <script src="{{ asset('assets/js/toast.js') }}"></script>
    @if (session('status'))
        <script>document.addEventListener('DOMContentLoaded', () => Toast.success(@json(session('status'))));</script>
    @endif
    @if ($errors->any())
        <script>document.addEventListener('DOMContentLoaded', () => Toast.error(@json($errors->first())));</script>
    @endif
</body>
</html>
