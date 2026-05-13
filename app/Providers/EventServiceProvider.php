<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Listeners\MergeGuestCartOnLogin;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Notikuma un klausītāja kartējumi lietojumprogrammai
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        // Apvieno viesu grozu, kad lietotājs piesakās
        Login::class => [
            MergeGuestCartOnLogin::class,
        ],
    ];

    /**
     * Reģistrē visus notikumus jūsu lietojumprogrammai
     */
    public function boot(): void
    {
        //
    }

    /**
     * Nosaka, vai notikumi un klausītāji ir jāatklāj automātiski
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
