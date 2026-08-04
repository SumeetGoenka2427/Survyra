<?php

use App\Http\Controllers\Public\WebsiteContactController;
use App\Http\Controllers\Public\WebsiteController;
use Illuminate\Support\Facades\Route;

Route::prefix('site')->name('website.')->middleware('throttle:60,1')->group(function () {
    Route::get('{slug}', [WebsiteController::class, 'show'])->name('show');
    Route::get('{slug}/{page}', [WebsiteController::class, 'show'])->name('show.page');
    Route::post('{slug}/contact', [WebsiteContactController::class, 'store'])->name('contact.store')->middleware('throttle:5,1');
});
