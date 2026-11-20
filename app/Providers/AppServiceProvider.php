<?php

namespace App\Providers;

use App\Auth\EmployerUserProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Auth::provider('employer', function () {
            return new EmployerUserProvider();
        });

        // Re-apply Spatie role/permission route middleware on Livewire update
        // requests (button clicks), not only on the initial page load.
        Livewire::addPersistentMiddleware([
            RoleMiddleware::class,
            PermissionMiddleware::class,
        ]);
    }
}
