<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
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
        if ($this->app->environment('production') || request()->header('x-forwarded-proto') === 'https') {
            URL::forceScheme('https');
        }

        // Automatic SQLite-to-MySQL fallback:
        // If default database is set to sqlite but sqlite file does not exist,
        // automatically fallback to mysql so production/test hosting never crashes.
        if (config('database.default') === 'sqlite') {
            $sqlitePath = config('database.connections.sqlite.database');
            if ($sqlitePath && ! file_exists($sqlitePath) && ! $this->app->runningUnitTests()) {
                config(['database.default' => 'mysql']);
                DB::purge('sqlite');
                DB::setDefaultConnection('mysql');
            }
        }
    }
}
