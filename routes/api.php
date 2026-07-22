<?php

use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\ResponseController;
use App\Http\Controllers\Api\SurveyController;
use App\Http\Middleware\AuthenticateApiKey;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')
    ->name('api.')
    ->middleware([AuthenticateApiKey::class, 'throttle:1000,60'])
    ->group(function () {
        Route::get('surveys', [SurveyController::class, 'index'])->name('surveys.index');
        Route::get('surveys/{survey}', [SurveyController::class, 'show'])->name('surveys.show');
        Route::get('surveys/{survey}/responses', [ResponseController::class, 'index'])->name('surveys.responses.index');
        Route::get('responses/{response}', [ResponseController::class, 'show'])->name('responses.show');
        Route::get('contacts', [ContactController::class, 'index'])->name('contacts.index');
        Route::post('contacts', [ContactController::class, 'store'])->name('contacts.store');
    });
