<?php

use App\Http\Controllers\Public\ReviewClickController;
use App\Http\Controllers\Public\ShortLinkController;
use App\Http\Controllers\Public\SurveyResponseController;
use Illuminate\Support\Facades\Route;

Route::prefix('s')->name('survey.')->middleware(['throttle:60,1', 'survey-tracking'])->group(function () {
    Route::get('{slug}', [SurveyResponseController::class, 'show'])->name('show');
    Route::post('{slug}/answer', [SurveyResponseController::class, 'answer'])->name('answer');
    Route::post('{slug}/back', [SurveyResponseController::class, 'back'])->name('back');
    Route::post('{slug}/submit', [SurveyResponseController::class, 'submit'])->name('submit');
});

Route::get('/l/{code}', [ShortLinkController::class, 'redirect'])
    ->name('short-link.redirect')
    ->middleware('throttle:60,1');

Route::get('/r/{response}/{channel}', [ReviewClickController::class, 'redirect'])
    ->name('review-click.redirect')
    ->middleware('throttle:60,1');
