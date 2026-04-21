<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\Analytics\FinanceAnalyticsRepositoryInterface;
use App\Repositories\Analytics\FinanceAnalyticsRepository;

class AnalyticsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            FinanceAnalyticsRepositoryInterface::class,
            FinanceAnalyticsRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}
