<?php

use App\Http\Controllers\LandingController;
use App\Http\Controllers\LeadController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('home');
Route::post('/leads', [LeadController::class, 'store'])->name('leads.store')->middleware('throttle:5,1');
Route::view('/privacy', 'legal.privacy')->name('legal.privacy');
Route::view('/terms', 'legal.terms')->name('legal.terms');

Route::get('/sitemap.xml', function () {
    $urls = [
        ['loc' => route('home'), 'priority' => '1.0'],
        ['loc' => route('legal.privacy'), 'priority' => '0.3'],
        ['loc' => route('legal.terms'), 'priority' => '0.3'],
    ];

    $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
    foreach ($urls as $url) {
        $xml .= "  <url>\n    <loc>{$url['loc']}</loc>\n    <priority>{$url['priority']}</priority>\n  </url>\n";
    }
    $xml .= '</urlset>';

    return response($xml, 200)->header('Content-Type', 'application/xml');
})->name('sitemap');

Route::get('/robots.txt', function () {
    $content = "User-agent: *\nAllow: /\nDisallow: /admin\n\nSitemap: ".route('sitemap');

    return response($content, 200)->header('Content-Type', 'text/plain');
});

require __DIR__.'/admin.php';
require __DIR__.'/portal.php';
require __DIR__.'/survey.php';
// Website builder is disabled while Survyra is survey-only (routes/website.php kept for future use).
require __DIR__.'/api.php';
