<?php

namespace Mayush\Shipping\Onessta;

use Illuminate\Support\ServiceProvider;
use Mayush\Shipping\Onessta\Client\OnesstaClient;
use Mayush\Shipping\Onessta\Client\RequestSigner;
use Mayush\Shipping\Onessta\Client\WebhookSignatureVerifier;
use Mayush\Shipping\Onessta\Contracts\CarrierInterface;
use Mayush\Shipping\Onessta\Services\AuthService;
use Mayush\Shipping\Onessta\Services\CatalogService;
use Mayush\Shipping\Onessta\Services\LabelService;
use Mayush\Shipping\Onessta\Services\QuoteService;
use Mayush\Shipping\Onessta\Services\ReferenceDataService;
use Mayush\Shipping\Onessta\Services\ShipmentService;
use Mayush\Shipping\Onessta\Services\TrackingService;
use Mayush\Shipping\Onessta\Services\WebhookService;

class OnesstaShippingServiceProvider extends ServiceProvider
{
    protected string $packagePath;

    public function __construct($app)
    {
        parent::__construct($app);
        $this->packagePath = dirname(__DIR__);
    }

    public function register(): void
    {
        $this->mergeConfigFrom($this->packagePath . '/config/onessta.php', 'onessta');

        $this->app->singleton(OnesstaClient::class, function ($app) {
            return new OnesstaClient(
                (string) config('onessta.base_url', 'https://api.onessta.com/api/v1'),
                (string) config('onessta.auth.token', ''),
                (string) config('onessta.auth.api_key', ''),
                (string) config('onessta.auth.client_id', ''),
                (int) config('onessta.http.timeout', 30),
                (int) config('onessta.http.connect_timeout', 10),
                (int) config('onessta.http.retry_times', 3),
                (int) config('onessta.http.retry_sleep_ms', 500),
                (array) config('onessta.http.retry_codes', [408, 502, 503, 504])
            );
        });

        $this->app->singleton(RequestSigner::class);
        $this->app->singleton(WebhookSignatureVerifier::class);

        $this->app->bind(CarrierInterface::class, ShipmentService::class);

        $this->app->singleton(AuthService::class);
        $this->app->singleton(ShipmentService::class);
        $this->app->singleton(TrackingService::class);
        $this->app->singleton(ReferenceDataService::class);
        $this->app->singleton(WebhookService::class);
        $this->app->singleton(CatalogService::class);
        $this->app->singleton(QuoteService::class);
        $this->app->singleton(LabelService::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                $this->packagePath . '/config/onessta.php' => config_path('onessta.php'),
            ], 'onessta-config');

            $this->publishes([
                $this->packagePath . '/Database/Migrations/' => database_path('migrations'),
            ], 'onessta-migrations');

            $this->publishes([
                $this->packagePath . '/resources/views/' => resource_path('views/vendor/onessta/'),
            ], 'onessta-views');

            $this->loadMigrationsFrom($this->packagePath . '/Database/Migrations');

            $this->commands([
                \Mayush\Shipping\Onessta\Console\Commands\FlushCitiesCache::class,
                \Mayush\Shipping\Onessta\Console\Commands\FlushPickupCitiesCache::class,
                \Mayush\Shipping\Onessta\Console\Commands\SyncCities::class,
                \Mayush\Shipping\Onessta\Console\Commands\SyncPickupCities::class,
                \Mayush\Shipping\Onessta\Console\Commands\PollTracking::class,
            ]);
        }



        $this->loadViewsFrom($this->packagePath . '/resources/views', 'onessta');
    }

    public function provides(): array
    {
        return [
            OnesstaClient::class,
            RequestSigner::class,
            WebhookSignatureVerifier::class,
            AuthService::class,
            ShipmentService::class,
            TrackingService::class,
            ReferenceDataService::class,
            WebhookService::class,
            CatalogService::class,
            QuoteService::class,
            LabelService::class,
        ];
    }
}
