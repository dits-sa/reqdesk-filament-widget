<?php

declare(strict_types=1);

namespace Reqdesk\Filament\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Reqdesk\Filament\ReqdeskWidgetServiceProvider;
use Spatie\LaravelSettings\LaravelSettingsServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LaravelSettingsServiceProvider::class,
            ReqdeskWidgetServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        config()->set('database.default', 'testing');
        config()->set('settings.repositories.database.model', null);
        config()->set('settings.migrations_paths', [
            __DIR__.'/../database/migrations',
        ]);
        config()->set('settings.cache', [
            'enabled' => false,
            'store' => null,
            'prefix' => 'settings',
        ]);
        config()->set('app.key', 'base64:2fl+Ktvkfl+Fuz4Qp/A75G2RTiWVA/ZoKZvp6fiiM10=');
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../vendor/spatie/laravel-settings/database/migrations');

        $migrationFile = glob(__DIR__.'/../database/migrations/*.php.stub');
        foreach ($migrationFile ?: [] as $file) {
            require_once $file;
        }
    }
}
