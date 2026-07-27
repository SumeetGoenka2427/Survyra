 <?php

use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\CampaignController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\QrCodeController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ResponseController;
use App\Http\Controllers\Admin\SearchController;
use App\Http\Controllers\Admin\SurveyController;
use App\Http\Controllers\Admin\SurveyLogicRuleController;
use App\Http\Controllers\Admin\SurveyQuestionController;
use App\Http\Controllers\Admin\SurveyPreviewController;
use App\Http\Controllers\Admin\SurveyTemplateController;
use App\Http\Controllers\Admin\SurveyTemplateQuestionController;
use App\Http\Controllers\Admin\SurveyThankyouRuleController;
use App\Http\Controllers\Admin\SurveyThemeController;
use App\Http\Controllers\Admin\TwoFactorController;
use App\Http\Controllers\Admin\AiSurveyController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\TwoFactorChallengeController;
use App\Http\Controllers\Admin\ClientAnalyticsController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:web')->group(function () {
        Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('login', [AuthenticatedSessionController::class, 'store']);

        Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
        Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

        Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
        Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');

        Route::get('two-factor-challenge', [TwoFactorChallengeController::class, 'create'])->name('two-factor.challenge');
        Route::post('two-factor-challenge', [TwoFactorChallengeController::class, 'store'])->name('two-factor.challenge.store');
    });

    Route::middleware('auth:web')->group(function () {
        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('dashboard/recent-clients', [DashboardController::class, 'recentClientsFragment'])->name('dashboard.recent-clients');
        Route::get('dashboard/recent-responses', [DashboardController::class, 'recentResponsesFragment'])->name('dashboard.recent-responses');
        Route::get('search', [SearchController::class, 'index'])->name('search');
        Route::post('notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');

        Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('profile/password', [\App\Http\Controllers\Auth\PasswordController::class, 'update'])->name('profile.password');

        Route::middleware('role:super_admin|survyra_admin')->group(function () {
            Route::get('clients/data', [ClientController::class, 'data'])->name('clients.data');
            Route::resource('clients', ClientController::class)->except(['show']);
            Route::patch('clients/{client}/toggle-status', [ClientController::class, 'toggleStatus'])->name('clients.toggle-status');

            // Client Analytics Dashboard
            Route::get('clients/{client}/analytics', [ClientAnalyticsController::class, 'show'])->name('clients.analytics');
            Route::get('clients/{client}/analytics/data', [ClientAnalyticsController::class, 'data'])->name('clients.analytics.data');
            Route::get('clients/{client}/analytics/export/{format}', [ClientAnalyticsController::class, 'export'])->name('clients.analytics.export');

            Route::get('audit-log', [\App\Http\Controllers\Admin\AuditLogController::class, 'index'])->name('audit-log.index');

            Route::get('leads', [LeadController::class, 'index'])->name('leads.index');
            Route::patch('leads/{lead}/status', [LeadController::class, 'updateStatus'])->name('leads.update-status');
        });

        Route::middleware('permission:manage-surveys')->group(function () {
            // AI Survey Generator
            Route::get('ai-survey', [AiSurveyController::class, 'index'])->name('ai-survey.index');
            Route::post('ai-survey/generate', [AiSurveyController::class, 'generate'])->name('ai-survey.generate');
            Route::get('surveys/{survey}/ai/suggest', [AiSurveyController::class, 'suggestQuestions'])->name('surveys.ai.suggest');
            Route::get('surveys/{survey}/ai/summary', [AiSurveyController::class, 'summary'])->name('surveys.ai.summary');
            Route::get('surveys/{survey}/ai/quality-score', [AiSurveyController::class, 'qualityScore'])->name('surveys.ai.quality-score');
            Route::get('surveys/{survey}/ai/sentiment', [AiSurveyController::class, 'sentiment'])->name('surveys.ai.sentiment');
            Route::get('surveys/{survey}/ai/keywords', [AiSurveyController::class, 'keywords'])->name('surveys.ai.keywords');
            Route::get('surveys/{survey}/ai/actions', [AiSurveyController::class, 'actions'])->name('surveys.ai.actions');
            Route::get('surveys/{survey}/ai/executive-report', [AiSurveyController::class, 'executiveReport'])->name('surveys.ai.executive-report');

            Route::get('templates/data', [SurveyTemplateController::class, 'data'])->name('templates.data');
            Route::resource('templates', SurveyTemplateController::class)->except(['show']);
            Route::post('templates/{template}/duplicate', [SurveyTemplateController::class, 'duplicate'])->name('templates.duplicate');

            Route::post('templates/{template}/questions', [SurveyTemplateQuestionController::class, 'store'])->name('templates.questions.store');
            Route::put('templates/{template}/questions/{question}', [SurveyTemplateQuestionController::class, 'update'])->name('templates.questions.update');
            Route::delete('templates/{template}/questions/{question}', [SurveyTemplateQuestionController::class, 'destroy'])->name('templates.questions.destroy');
            Route::post('templates/{template}/questions/{question}/duplicate', [SurveyTemplateQuestionController::class, 'duplicate'])->name('templates.questions.duplicate');
            Route::patch('templates/{template}/questions/{question}/move-up', [SurveyTemplateQuestionController::class, 'moveUp'])->name('templates.questions.move-up');
            Route::patch('templates/{template}/questions/{question}/move-down', [SurveyTemplateQuestionController::class, 'moveDown'])->name('templates.questions.move-down');

            Route::get('themes/data', [SurveyThemeController::class, 'data'])->name('themes.data');
            Route::resource('themes', SurveyThemeController::class)->except(['show']);

            Route::get('survey-preview', [SurveyPreviewController::class, 'index'])->name('survey-preview');

            Route::get('surveys/data', [SurveyController::class, 'data'])->name('surveys.data');
            Route::resource('surveys', SurveyController::class)->except(['show']);
            Route::post('surveys/{survey}/publish', [SurveyController::class, 'publish'])->name('surveys.publish');
            Route::post('surveys/{survey}/archive', [SurveyController::class, 'archive'])->name('surveys.archive');
            Route::post('surveys/{survey}/duplicate', [SurveyController::class, 'duplicate'])->name('surveys.duplicate');
            Route::get('surveys/{survey}/qr', [SurveyController::class, 'downloadQr'])->name('surveys.qr');
            Route::post('surveys/{survey}/theme/{theme}/duplicate', [SurveyController::class, 'duplicateTheme'])->name('surveys.theme.duplicate');

            Route::post('surveys/{survey}/questions', [SurveyQuestionController::class, 'store'])->name('surveys.questions.store');
            Route::put('surveys/{survey}/questions/{question}', [SurveyQuestionController::class, 'update'])->name('surveys.questions.update');
            Route::delete('surveys/{survey}/questions/{question}', [SurveyQuestionController::class, 'destroy'])->name('surveys.questions.destroy');
            Route::post('surveys/{survey}/questions/{question}/duplicate', [SurveyQuestionController::class, 'duplicate'])->name('surveys.questions.duplicate');
            Route::post('surveys/{survey}/questions/reorder', [SurveyQuestionController::class, 'reorder'])->name('surveys.questions.reorder');
            Route::patch('surveys/{survey}/questions/{question}/move-up', [SurveyQuestionController::class, 'moveUp'])->name('surveys.questions.move-up');
            Route::patch('surveys/{survey}/questions/{question}/move-down', [SurveyQuestionController::class, 'moveDown'])->name('surveys.questions.move-down');
            Route::patch('surveys/{survey}/questions/{question}/set-primary-score', [SurveyQuestionController::class, 'setPrimaryScore'])->name('surveys.questions.set-primary-score');

            Route::post('surveys/{survey}/logic-rules', [SurveyLogicRuleController::class, 'store'])->name('surveys.logic-rules.store');
            Route::put('surveys/{survey}/logic-rules/{rule}', [SurveyLogicRuleController::class, 'update'])->name('surveys.logic-rules.update');
            Route::delete('surveys/{survey}/logic-rules/{rule}', [SurveyLogicRuleController::class, 'destroy'])->name('surveys.logic-rules.destroy');

            Route::put('surveys/{survey}/thankyou-rules/{sentiment}', [SurveyThankyouRuleController::class, 'update'])->name('surveys.thankyou-rules.update');

            Route::post('surveys/{survey}/qr-codes', [QrCodeController::class, 'store'])->name('surveys.qr-codes.store');
            Route::get('surveys/{survey}/qr-codes/{qrCode}/download', [QrCodeController::class, 'download'])->name('surveys.qr-codes.download');
            Route::delete('surveys/{survey}/qr-codes/{qrCode}', [QrCodeController::class, 'destroy'])->name('surveys.qr-codes.destroy');
        });

        Route::middleware('permission:send-campaigns')->group(function () {
            Route::get('clients/{client}/contacts', [ContactController::class, 'index'])->name('clients.contacts.index');
            Route::get('clients/{client}/contacts/create', [ContactController::class, 'create'])->name('clients.contacts.create');
            Route::post('clients/{client}/contacts', [ContactController::class, 'store'])->name('clients.contacts.store');
            Route::get('clients/{client}/contacts/{contact}/edit', [ContactController::class, 'edit'])->name('clients.contacts.edit');
            Route::put('clients/{client}/contacts/{contact}', [ContactController::class, 'update'])->name('clients.contacts.update');
            Route::delete('clients/{client}/contacts/{contact}', [ContactController::class, 'destroy'])->name('clients.contacts.destroy');
            Route::get('clients/{client}/contacts-import', [ContactController::class, 'importForm'])->name('clients.contacts.import-form');
            Route::post('clients/{client}/contacts-import', [ContactController::class, 'import'])->name('clients.contacts.import');

            Route::get('campaigns/data', [CampaignController::class, 'data'])->name('campaigns.data');
            Route::resource('campaigns', CampaignController::class)->only(['index', 'create', 'store', 'show']);
            Route::post('campaigns/{campaign}/send', [CampaignController::class, 'send'])->name('campaigns.send');
            Route::post('campaigns/{campaign}/retry', [CampaignController::class, 'retry'])->name('campaigns.retry');
        });

        Route::middleware('permission:view-analytics')->prefix('analytics')->name('analytics.')->group(function () {
            Route::get('/', [AnalyticsController::class, 'index'])->name('index');
            Route::get('data', [AnalyticsController::class, 'data'])->name('data');
            Route::get('export/{format}', [AnalyticsController::class, 'export'])->name('export');

            Route::get('responses', [ResponseController::class, 'index'])->name('responses.index');
            Route::get('responses/{response}', [ResponseController::class, 'show'])->name('responses.show');
            Route::get('uploads/{upload}', [\App\Http\Controllers\Admin\ResponseUploadController::class, 'download'])->name('uploads.download');

            Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
            Route::post('reports', [ReportController::class, 'store'])->name('reports.store');
            Route::delete('reports/{report}', [ReportController::class, 'destroy'])->name('reports.destroy');

            // Real-time analytics polling
            Route::get('poll/{survey}', [AnalyticsController::class, 'poll'])->name('poll');
        });

        // Two-Factor Authentication
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('two-factor', [TwoFactorController::class, 'index'])->name('two-factor');
            Route::get('two-factor/setup', [TwoFactorController::class, 'setup'])->name('two-factor.setup');
            Route::post('two-factor/confirm', [TwoFactorController::class, 'confirm'])->name('two-factor.confirm');
            Route::post('two-factor/disable', [TwoFactorController::class, 'disable'])->name('two-factor.disable');
            Route::get('two-factor/recovery-codes', [TwoFactorController::class, 'recoveryCodes'])->name('two-factor.recovery-codes');
        });
    });
});
