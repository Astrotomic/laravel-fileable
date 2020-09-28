<?php

namespace Astrotomic\Fileable;

use Illuminate\Support\ServiceProvider;

class FileableServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/fileable.php' => config_path('fileable.php'),
            ], 'fileable');
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/fileable.php', 'fileable'
        );
    }
}
