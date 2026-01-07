<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        // Register User Observer
        \App\Models\Qestass\User::observe(\App\Observers\UserObserver::class);

        // Register CaseRecord Observer for audit trail
        \App\Models\CaseRecord::observe(\App\Observers\CaseRecordObserver::class);

        Gate::define('use-translation-manager', function (?User $user) {
            // Your authorization logic
            return true;
            // return $user !== null && ($user->hasRole('admin') || $user->hasRole('Super Admin'));
        });
    }
}
