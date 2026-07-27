@props([
    'title' => 'Survyra',
    'description' => 'Collect customer feedback, grow reviews and build a professional business website with Survyra. Simple surveys, QR feedback, analytics and review tools for small businesses.',
])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ $description }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <title>{{ $title === 'Survyra' ? 'Survyra | Customer Feedback & Review Growth for Small Businesses' : 'Survyra | '.$title }}</title>
    <meta property="og:title" content="{{ $title === 'Survyra' ? 'Survyra | Customer Feedback & Review Growth for Small Businesses' : 'Survyra | '.$title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $title === 'Survyra' ? 'Survyra | Customer Feedback & Review Growth for Small Businesses' : 'Survyra | '.$title }}">
    <meta name="twitter:description" content="{{ $description }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/public.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Organization',
                    'name' => 'Survyra',
                    'url' => url('/'),
                    'description' => 'Survyra is a customer feedback and review-growth platform for small businesses, combining customer surveys, QR feedback, review requests, analytics and a professional business website.',
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
