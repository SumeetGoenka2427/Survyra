<?php

namespace App\Providers;

use App\Repositories\ClientRepository;
use App\Repositories\Contracts\ClientRepositoryInterface;
use App\Repositories\Contracts\SurveyRepositoryInterface;
use App\Repositories\Contracts\SurveyTemplateRepositoryInterface;
use App\Repositories\SurveyRepository;
use App\Repositories\SurveyTemplateRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ClientRepositoryInterface::class, ClientRepository::class);
        $this->app->bind(SurveyTemplateRepositoryInterface::class, SurveyTemplateRepository::class);
        $this->app->bind(SurveyRepositoryInterface::class, SurveyRepository::class);
    }
}
