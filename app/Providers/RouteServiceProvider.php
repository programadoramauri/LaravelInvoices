<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
       * Register services.
       */
    public function register(): void
    {
        //
    }

    /**
       * Bootstrap services.
       */
    public function boot(): void
    {

        RateLimiter::for('login', function ($request) {
            return Limit::perMinute(5)->by($request->email . $request->ip());
        });

        Route::middleware('web')
          ->group(base_path('routes/web.php'));

    }
}
