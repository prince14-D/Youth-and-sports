<?php

namespace App\Providers;

use App\Repositories\Contracts\EmployeeRepositoryInterface;
use App\Repositories\Eloquent\EmployeeRepository;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind Repository Interface to Implementation
        $this->app->bind(EmployeeRepositoryInterface::class, EmployeeRepository::class);
    }

    public function boot(): void
    {
        // Force HTTPS in production
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Set default string length for older MySQL versions
        \Illuminate\Support\Facades\Schema::defaultStringLength(191);

        // Force Bootstrap 5 pagination styling
        \Illuminate\Pagination\Paginator::useBootstrapFive();

        // Register custom Flux-compatible component namespace
        \Illuminate\Support\Facades\Blade::anonymousComponentPath(resource_path('views/components/flux'), 'flux');
    }
}
