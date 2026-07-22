@props(['title' => 'Survyra'])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} - Survyra</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/design-system.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    <script>
        (function () {
            document.documentElement.setAttribute('data-bs-theme', localStorage.getItem('ds-theme') || 'light');
        })();
    </script>
    <style>
        .guest-shell {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            background:
                radial-gradient(circle at 15% 20%, rgba(99, 102, 241, 0.12), transparent 45%),
                radial-gradient(circle at 85% 80%, rgba(16, 185, 129, 0.10), transparent 45%),
                var(--bs-body-bg);
        }
        .guest-brand {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -0.02em;
        }
        .guest-card {
            width: 100%;
            max-width: 420px;
        }
    </style>
</head>
<body>
    <div class="guest-shell">
        <div class="guest-card ds-fade-in">
            <div class="text-center mb-4">
                <a href="/" class="guest-brand text-decoration-none">
                    <i class="bi bi-hexagon-fill text-primary"></i>
                    <span>Survyra</span>
                </a>
            </div>
            <div class="card">
                <div class="card-body p-4">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/toast.js') }}"></script>
</body>
</html>
