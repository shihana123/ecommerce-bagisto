<?php

namespace App\Providers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\ParallelTesting;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Webkul\Sales\Generators\OrderSequencer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        ParallelTesting::setUpTestDatabase(function (string $database, int $token) {
            Artisan::call('db:seed');
        });
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $allowedIPs = array_map('trim', explode(',', config('app.debug_allowed_ips')));

        $allowedIPs = array_filter($allowedIPs);

        if (empty($allowedIPs)) {
            return;
        }

        if (in_array(Request::ip(), $allowedIPs)) {
            \Debugbar::enable();
        } else {
            \Debugbar::disable();
        }
// Temporary override for OrderSequencer to prevent infinite loop
        $this->app->bind(OrderSequencer::class, function ($app) {
            return new class extends OrderSequencer {
                public function getLastId()
                {
                    return 0; // Return a dummy ID to avoid DB calls and recursion
                }
            };
        });
        
    }
}


