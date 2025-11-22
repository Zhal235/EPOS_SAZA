<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register SimpelsApiService as singleton
        $this->app->singleton(\App\Services\SimpelsApiService::class, function ($app) {
            return new \App\Services\SimpelsApiService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Define authorization gates
        \Illuminate\Support\Facades\Gate::define('access-admin', function ($user) {
            return $user->canAccessAdmin();
        });
        
        // Define gate to check if user can login (customers cannot login)
        \Illuminate\Support\Facades\Gate::define('can-login', function ($user) {
            return in_array($user->role, ['admin', 'manager', 'cashier']);
        });

        // Set default redirect after login
        $this->app->resolving(Request::class, function ($request) {
            if ($request->route()?->getName() === 'login' && auth()->check()) {
                return redirect()->route('dashboard');
            }
        });
    }
}
