<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        Event::listen(function (\Illuminate\Auth\Events\Login $event) {
            activity('auth')
                ->causedBy($event->user)
                ->withProperties(['ip' => request()->ip(), 'user_agent' => request()->userAgent()])
                ->log('User logged in');
        });

        Event::listen(function (\Illuminate\Auth\Events\Logout $event) {
            activity('auth')
                ->causedBy($event->user)
                ->log('User logged out');
        });

        Event::listen(function (\Illuminate\Auth\Events\Failed $event) {
            activity('auth')
                ->withProperties(['email' => $event->credentials['email'] ?? null, 'ip' => request()->ip()])
                ->log('Failed login attempt');
        });
    }
}
