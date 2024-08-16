<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\SuccessLoginEvent;
use App\Events\OrderCreatedEvent;
use App\Events\OrderDeletedEvent;
use App\Listeners\SuccessLoginListener;
use App\Listeners\OrderCreatedListener;
use App\Listeners\OrderDeletedListener;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        SuccessLoginEvent::class => [
            SuccessLoginListener::class
        ],
        OrderCreatedEvent::class => [
            OrderCreatedListener::class
        ],
        OrderDeletedEvent::class => [
            OrderDeletedListener::class
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
//        parent::boot();

        //
    }
}
