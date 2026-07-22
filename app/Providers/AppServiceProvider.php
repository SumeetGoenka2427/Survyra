<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\SettingsService::class);
        $this->app->singleton(\App\Services\QuestionTypeRegistry::class);
        $this->app->singleton(\App\Services\LogicEngine::class);
        $this->app->singleton(\App\Services\CampaignProviderRegistry::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        Gate::define('viewAuditLog', function (\App\Models\User $user) {
            return $user->hasRole(['super_admin', 'survyra_admin']);
        });
    }
}
