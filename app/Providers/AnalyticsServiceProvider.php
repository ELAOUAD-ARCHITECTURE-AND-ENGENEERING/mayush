<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\Analytics\TechnicalAnalyticsRepositoryInterface;
use App\Repositories\Analytics\TechnicalAnalyticsRepository;

class AnalyticsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            TechnicalAnalyticsRepositoryInterface::class,
            TechnicalAnalyticsRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}
