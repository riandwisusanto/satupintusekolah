<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;

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
        if (config('APP_ENV', 'production') === 'production') {
            $this->app->bind('path.public', function () {
                return base_path('../public_html');
            });

            // Auto-copy storage/app/public -> public_html/storage
            $from = storage_path('app/public');
            $to = base_path('../storage');

            if (!File::exists($to)) {
                File::makeDirectory($to, 0755, true);
            }

            File::copyDirectory($from, $to);
        }
    }
}
