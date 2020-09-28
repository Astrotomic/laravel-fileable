<?php

namespace Astrotomic\Fileable;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class FileableServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->bootConfig();
            $this->bootMigrations();
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/fileable.php', 'fileable');
    }

    protected function bootConfig(): void
    {
        $this->publishes([
            __DIR__.'/../config/fileable.php' => config_path('fileable.php'),
        ], 'config');
    }

    protected function bootMigrations(): void
    {
        foreach (['CreateFilesTable'] as $i => $migration) {
            if (! class_exists($migration)) {
                $this->publishes([
                    __DIR__.'/../migrations/'.Str::snake($migration).'.php.stub' => database_path(sprintf(
                        'migrations/%s_%s.php',
                        date('Y_m_d_His', time() + $i),
                        Str::snake($migration)
                    )),
                ], 'migrations');
            }
        }
    }
}
