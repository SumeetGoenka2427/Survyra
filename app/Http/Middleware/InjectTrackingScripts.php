<?php

namespace App\Http\Middleware;

use App\Models\Survey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Injects Google Analytics and Meta Pixel tracking scripts into survey pages.
 * This is the last middleware before the response is sent, so it can safely
 * modify the HTML content after all view rendering is complete.
 */
class InjectTrackingScripts
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only process HTML responses for survey pages
        if (! $response instanceof \Illuminate\Http\Response) {
            return $response;
        }

        $contentType = $response->headers->get('Content-Type', '');
        if (! str_contains($contentType, 'text/html')) {
            return $response;
        }

        $content = $response->getContent();

        // Find survey slug from the route
        $survey = $request->route('slug')
            ? Survey::where('slug', $request->route('slug'))->where('status', 'published')->first()
            : null;

        if (! $survey) {
            return $response;
        }

        $scripts = '';

        // Google Analytics
        if ($gaId = $survey->ga_tracking_id) {
            $scripts .= <<<HTML
<!-- Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id={$gaId}"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', '{$gaId}');
</script>
HTML;
        }

        // Meta Pixel
        if ($pixelId = $survey->meta_pixel_id) {
            $scripts .= <<<HTML
<!-- Meta Pixel -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '{$pixelId}');
fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id={$pixelId}&ev=PageView&noscript=1"
/></noscript>
HTML;
        }

        if ($scripts) {
            $content = str_replace('</head>', $scripts . "\n</head>", $content);
            $response->setContent($content);
        }

        return $response;
    }
}