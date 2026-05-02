<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Force HTTPS in production
        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        // Prevent lazy loading in development to catch N+1 queries early
        Model::preventLazyLoading(! $this->app->isProduction());

        // Prevent silently discarding attributes not in $fillable
        Model::preventSilentlyDiscardingAttributes(! $this->app->isProduction());
    }
}
