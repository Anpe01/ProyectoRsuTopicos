<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

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
        // Fallback manual del hint 'adminlte'
        $vendorPath = base_path('vendor/jeroennoten/laravel-adminlte/resources/views');
        if (is_dir($vendorPath)) {
            View::addNamespace('adminlte', $vendorPath);
        }
    }
}
