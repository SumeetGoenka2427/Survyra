<?php

use App\Http\Controllers\Portal\AnalyticsController;
use App\Http\Controllers\Portal\ApiKeyController;
use App\Http\Controllers\Portal\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Portal\Auth\NewPasswordController;
use App\Http\Controllers\Portal\Auth\PasswordResetLinkController;
use App\Http\Controllers\Portal\CompanyProfileController;
use App\Http\Controllers\Portal\NotificationController;
use App\Http\Controllers\Portal\ProfileController;
use App\Http\Controllers\Portal\ReportController;
use App\Http\Controllers\Portal\ResponseController;
use App\Http\Controllers\Portal\TeamController;
use App\Http\Controllers\Portal\WebhookController;
use App\Http\Controllers\Portal\SlackIntegrationController;
use App\Http\Controllers\Portal\OnboardingController;
use Illuminate\Support\Facades\Route;

Route::prefix('portal')->name('portal.')->group(function () {
    Route::middleware('guest:client')->group(function () {
        Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('login', [AuthenticatedSessionController::class, 'store']);

        Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
        Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

        Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
        Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
    });

    Route::middleware('auth:client')->group(function () {
        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

        Route::get('dashboard', [AnalyticsController::class, 'index'])->name('dashboard');

        Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

        Route::get('company', [CompanyProfileController::class, 'edit'])->name('company.edit');
        Route::patch('company', [CompanyProfileController::class, 'update'])->name('company.update')->middleware('client.can-edit');

        Route::post('notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');

        Route::prefix('analytics')->name('analytics.')->group(function () {
            Route::get('data', [AnalyticsController::class, 'data'])->name('data');
            Route::get('export/{format}', [AnalyticsController::class, 'export'])->name('export');

            Route::get('responses', [ResponseController::class, 'index'])->name('responses.index');
            Route::get('responses/{response}', [ResponseController::class, 'show'])->name('responses.show');

            Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
            Route::post('reports', [ReportController::class, 'store'])->name('reports.store')->middleware('client.can-edit');
            Route::delete('reports/{report}', [ReportController::class, 'destroy'])->name('reports.destroy')->middleware('client.can-edit');
        });

        // Team management
        Route::get('team', [TeamController::class, 'index'])->name('team.index');
        Route::post('team/invite', [TeamController::class, 'invite'])->name('team.invite');
        Route::delete('team/{member}', [TeamController::class, 'destroy'])->name('team.destroy');

        // Integrations
        Route::prefix('integrations')->name('integrations.')->group(function () {
            Route::get('api-keys', [ApiKeyController::class, 'index'])->name('api-keys');
            Route::post('api-keys', [ApiKeyController::class, 'store'])->name('api-keys.store')->middleware('client.can-edit');
            Route::delete('api-keys/{apiKey}', [ApiKeyController::class, 'destroy'])->name('api-keys.destroy')->middleware('client.can-edit');

            Route::get('webhooks', [WebhookController::class, 'index'])->name('webhooks');
            Route::post('webhooks', [WebhookController::class, 'store'])->name('webhooks.store')->middleware('client.can-edit');
            Route::delete('webhooks/{webhook}', [WebhookController::class, 'destroy'])->name('webhooks.destroy')->middleware('client.can-edit');

            Route::get('slack', [SlackIntegrationController::class, 'index'])->name('slack');
            Route::post('slack', [SlackIntegrationController::class, 'store'])->name('slack.store')->middleware('client.can-edit');
            Route::delete('slack', [SlackIntegrationController::class, 'destroy'])->name('slack.destroy')->middleware('client.can-edit');
        });

        // Onboarding
        Route::post('onboarding/dismiss', [OnboardingController::class, 'dismiss'])->name('onboarding.dismiss');
    });
});

// Public invitation acceptance (no auth)
Route::get('portal/team/accept-invitation/{token}', [TeamController::class, 'acceptInvitation'])->name('portal.team.accept-invitation');
Route::post('portal/team/accept-invitation/{token}', [TeamController::class, 'completeInvitation'])->name('portal.team.complete-invitation');
