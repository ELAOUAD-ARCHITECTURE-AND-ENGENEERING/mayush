<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
  /**
   * The event listener mappings for the application.
   *
   * @var array
   */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        \App\Events\CriticalSystemError::class => [
            \App\Listeners\SendCriticalErrorNotification::class,
        ],
        \App\Events\ProductRestockedEvent::class => [
            \App\Listeners\SendStockAlertNotifications::class,
        ],
        \Mayush\Shipping\Onessta\Events\ShipmentStatusUpdated::class => [
            \Mayush\Shipping\Onessta\Listeners\UpdateOrderDeliveryStatus::class,
        ],
        \Mayush\Shipping\Onessta\Events\ShipmentCreationFailed::class => [
            \Mayush\Shipping\Onessta\Listeners\NotifyAdminOnShipmentFailure::class,
        ],
        \App\Events\NewCustomerMessageReceived::class => [
            \App\Listeners\ProcessBotResponse::class,
        ],
    ];

  /**
   * The subscriber classes to register.
   *
   * @var array
   */
  protected $subscribe = [
    \App\Listeners\SecurityEventSubscriber::class,
  ];

  /**
   * Register any events for your application.
   *
   * @return void
   */
  public function boot()
  {
    parent::boot();

    \App\Models\Product::observe(\App\Observers\ProductObserver::class);
    \App\Models\OrderTrackingHistory::observe(\App\Observers\OrderTrackingHistoryObserver::class);
  }
}
