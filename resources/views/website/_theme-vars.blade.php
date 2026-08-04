@php
    $primary = $theme['primary_color'] ?? '#0d6efd';
    $secondary = $theme['secondary_color'] ?? '#6c757d';
    $background = $theme['background'] ?? '#ffffff';
    $headingFont = $theme['heading_font'] ?? 'system-ui';
    $bodyFont = $theme['body_font'] ?? 'system-ui';
    $radius = $theme['border_radius'] ?? 8;
    $buttonRadius = match ($theme['button_style'] ?? 'rounded') {
        'pill' => '999px',
        'square' => '0',
        default => '6px',
    };
    $maxWidth = ($theme['container_width'] ?? 'boxed') === 'full' ? '100%' : '1140px';

    $toRgb = function (string $hex): array {
        $hex = ltrim($hex, '#');
        $hex = strlen($hex) === 3 ? preg_replace('/(.)/', '$1$1', $hex) : $hex;
        return strlen($hex) === 6
            ? [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))]
            : [13, 110, 253];
    };
    $shade = function (array $rgb, float $amount): string {
        // amount < 0 darkens toward black, > 0 lightens toward white
        $mix = fn (int $channel) => (int) round($amount < 0
            ? $channel * (1 + $amount)
            : $channel + (255 - $channel) * $amount);
        return sprintf('#%02x%02x%02x', $mix($rgb[0]), $mix($rgb[1]), $mix($rgb[2]));
    };

    [$bgR, $bgG, $bgB] = $toRgb($background);
    [$primR, $primG, $primB] = $toRgb($primary);
    [$secR, $secG, $secB] = $toRgb($secondary);

    // Perceptual luminance (ITU-R BT.601) drives light/dark chrome for a client-chosen background.
    $isDark = (0.299 * $bgR + 0.587 * $bgG + 0.114 * $bgB) < 128;
    $text = $isDark ? '#f1f5f9' : '#1e293b';
    $muted = $isDark ? '#a1adc2' : '#64748b';
    $cardBg = $isDark ? $shade([$bgR, $bgG, $bgB], 0.14) : '#ffffff';
    $altSectionBg = $isDark ? $shade([$bgR, $bgG, $bgB], 0.07) : $shade([$bgR, $bgG, $bgB], -0.03);
    $borderColor = $isDark ? 'rgba(255,255,255,0.12)' : 'rgba(20,22,26,0.08)';
    $primaryDark = $shade([$primR, $primG, $primB], -0.18);
@endphp
<style>
    :root {
        --ws-primary: {{ $primary }};
        --ws-primary-dark: {{ $primaryDark }};
        --ws-primary-rgb: {{ $primR }}, {{ $primG }}, {{ $primB }};
        --ws-secondary: {{ $secondary }};
        --ws-secondary-rgb: {{ $secR }}, {{ $secG }}, {{ $secB }};
        --ws-bg: {{ $background }};
        --ws-alt-bg: {{ $altSectionBg }};
        --ws-card-bg: {{ $cardBg }};
        --ws-text: {{ $text }};
        --ws-muted: {{ $muted }};
        --ws-border: {{ $borderColor }};
        --ws-heading-font: '{{ $headingFont }}', system-ui, sans-serif;
        --ws-body-font: '{{ $bodyFont }}', system-ui, sans-serif;
        --ws-radius: {{ $radius }}px;
        --ws-btn-radius: {{ $buttonRadius }};
        --ws-max-width: {{ $maxWidth }};
        --ws-shadow: 0 20px 45px -20px rgba(0, 0, 0, {{ $isDark ? '0.55' : '0.25' }});
        --ws-shadow-sm: 0 8px 20px -12px rgba(0, 0, 0, {{ $isDark ? '0.5' : '0.2' }});
    }

    body { background: var(--ws-bg); font-family: var(--ws-body-font); color: var(--ws-text); }
    h1, h2, h3, h4, h5, h6 { font-family: var(--ws-heading-font); color: var(--ws-text); letter-spacing: -0.01em; }
    .container { max-width: var(--ws-max-width); }
    a { color: var(--ws-primary); }

    .ws-section { padding: clamp(3rem, 6vw, 5.5rem) 0; position: relative; }
    .ws-section-alt { background: var(--ws-alt-bg); }
    .ws-eyebrow {
        display: inline-block;
        padding: 0.35rem 0.9rem;
        border-radius: 999px;
        background: rgba(var(--ws-primary-rgb), {{ $isDark ? '0.22' : '0.12' }});
        color: var(--ws-primary);
        font-weight: 600;
        font-size: 0.8rem;
        letter-spacing: 0.03em;
    }

    .btn-primary { background-color: var(--ws-primary); border-color: var(--ws-primary); border-radius: var(--ws-btn-radius); font-weight: 600; transition: transform 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease; }
    .btn-primary:hover, .btn-primary:focus { background-color: var(--ws-primary-dark); border-color: var(--ws-primary-dark); transform: translateY(-2px); box-shadow: var(--ws-shadow-sm); }
    .btn-secondary { background-color: var(--ws-secondary); border-color: var(--ws-secondary); border-radius: var(--ws-btn-radius); transition: transform 0.15s ease; }
    .btn-secondary:hover { transform: translateY(-2px); }
    .btn-outline-ws { border: 1.5px solid var(--ws-border); color: var(--ws-text); border-radius: var(--ws-btn-radius); background: transparent; transition: all 0.15s ease; }
    .btn-outline-ws:hover { background: rgba(var(--ws-primary-rgb), 0.1); border-color: var(--ws-primary); color: var(--ws-primary); }

    .ws-card {
        background: var(--ws-card-bg);
        border: 1px solid var(--ws-border);
        border-radius: var(--ws-radius);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .card { background-color: var(--ws-card-bg); color: var(--ws-text); border-color: var(--ws-border); border-radius: var(--ws-radius); }
    .text-muted { color: var(--ws-muted) !important; }
    .form-control, .form-select {
        background-color: var(--ws-card-bg);
        color: var(--ws-text);
        border-color: var(--ws-border);
        border-radius: calc(var(--ws-radius) * 0.5);
        padding: 0.65rem 0.9rem;
    }
    .form-control:focus, .form-select:focus {
        background-color: var(--ws-card-bg);
        color: var(--ws-text);
        border-color: var(--ws-primary);
        box-shadow: 0 0 0 0.2rem rgba(var(--ws-primary-rgb), 0.2);
    }
    .form-label { font-weight: 600; font-size: 0.9rem; }

    .ws-fade-up { opacity: 0; transform: translateY(18px); animation: wsFadeUp 0.7s ease forwards; }
    .ws-fade-up-delay-1 { animation-delay: 0.1s; }
    .ws-fade-up-delay-2 { animation-delay: 0.2s; }
    @keyframes wsFadeUp { to { opacity: 1; transform: translateY(0); } }

    @media (max-width: 767.98px) {
        .ws-section { padding: 2.5rem 0; }
    }

    /* Visible keyboard-focus ring - browser defaults are easy to lose against
       a client-chosen background/theme, so make it explicit and on-brand. */
    a:focus-visible, button:focus-visible, .btn:focus-visible,
    .nav-link:focus-visible, .navbar-toggler:focus-visible,
    input:focus-visible, textarea:focus-visible, select:focus-visible {
        outline: 3px solid var(--ws-primary);
        outline-offset: 2px;
    }

    /* Gallery hover-zoom: pure CSS (was inline onmouseover/onmouseout JS) so it
       degrades cleanly - no-op on touch, and :focus-within covers keyboard
       navigation to any focusable element inside the tile. */
    .ws-gallery-zoom { overflow: hidden; }
    .ws-gallery-zoom img { transition: transform 0.4s ease; }
    .ws-gallery-zoom:hover img, .ws-gallery-zoom:focus-within img { transform: scale(1.06); }

    /* Bootstrap's stock carousel controls/indicators are well under the 44px
       touch-target floor. */
    .carousel-control-prev, .carousel-control-next { width: 15%; min-width: 44px; }
    .carousel-control-prev-icon, .carousel-control-next-icon { width: 2.5rem; height: 2.5rem; }
    .carousel-indicators [data-bs-target] { width: 12px; height: 12px; border-radius: 50%; margin: 0 6px; }

    {!! $theme['custom_css'] ?? '' !!}
</style>
