<?php

namespace Astrotomic\Fileable;

use CreateFilesTable;
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
        foreach ([CreateFilesTable::class] as $i => $migration) {
            if (class_exists($migration)) {
                continue;
            }

            $this->publishes([
                __DIR__.'/../migrations/0000_00_00_000000_'.Str::snake($migration).'.php' => database_path(sprintf(
                    'migrations/%s_%s.php',
                    date('Y_m_d_His', time() + $i),
                    Str::snake($migration)
                )),
            ], 'migrations');
        }
    }
}
