<?php

declare(strict_types=1);

namespace Reqdesk\Filament;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Blade;
use Reqdesk\Filament\Console\Commands\DoctorCommand;
use Reqdesk\Filament\Contracts\WidgetUserResolver;
use Reqdesk\Filament\Exceptions\ReqdeskConfigurationException;
use Reqdesk\Filament\Services\ConfigValidator;
use Reqdesk\Filament\Services\IdentitySigner;
use Reqdesk\Filament\Services\ReqdeskClient;
use Reqdesk\Filament\Services\WidgetConfigBuilder;
use Reqdesk\Filament\Settings\ReqdeskWidgetSettings;
use Reqdesk\Filament\Support\DefaultUserResolver;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class ReqdeskWidgetServiceProvider extends PackageServiceProvider
{
    public static string $name = 'reqdesk-widget';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(self::$name)
            ->hasConfigFile()
            ->hasTranslations()
            ->hasViews('reqdesk')
            ->hasRoute('widget')
            ->hasMigration('create_reqdesk_widget_settings')
            ->hasCommand(DoctorCommand::class)
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->endWith(function (InstallCommand $cmd): void {
                        $requireSigning = ! (bool) config('reqdesk-widget.install_skip_signing', false);
                        $report = app(ConfigValidator::class)->validateEnvironment(
                            requireSigningSecret: $requireSigning,
                        );

                        foreach ($report->passed as $passed) {
                            $cmd->info('  ✓ '.$passed);
                        }

                        foreach ($report->warnings as $warning) {
                            $cmd->warn('  ! '.$warning);
                        }

                        foreach ($report->errors as $error) {
                            $cmd->error('  ✗ '.$error);
                        }

                        if ($report->errors !== []) {
                            $cmd->error('Reqdesk widget install aborted — resolve the errors above and re-run.');
                            $cmd->info('To skip the signing-secret requirement, set REQDESK_INSTALL_SKIP_SIGNING=true and re-run.');
                            exit(1);
                        }

                        $cmd->info('Reqdesk widget installed. Add '.
                            '`->plugin(Reqdesk\\Filament\\ReqdeskWidgetPlugin::make())` to your PanelProvider.');
                    });
            });
    }

    public function registeringPackage(): void
    {
        $this->app->singleton(ConfigValidator::class);
        $this->app->singleton(IdentitySigner::class);
        $this->app->singleton(ReqdeskClient::class);
        $this->app->singleton(WidgetConfigBuilder::class);
        $this->app->singleton(DefaultUserResolver::class);

        $this->app->bind(WidgetUserResolver::class, function ($app): WidgetUserResolver {
            $class = $this->resolveResolverClass($app);

            /** @var WidgetUserResolver */
            return $app->make($class);
        });
    }

    public function packageBooted(): void
    {
        Blade::componentNamespace('Reqdesk\\Filament\\View\\Components', 'reqdesk');

        $this->app->booted(function (): void {
            $strict = (bool) config('reqdesk-widget.strict', false);

            if (! $strict) {
                return;
            }

            $report = app(ConfigValidator::class)->validateEnvironment();

            if ($report->errors !== []) {
                throw new ReqdeskConfigurationException(
                    'Reqdesk widget strict-mode validation failed: '.implode(' | ', $report->errors),
                );
            }
        });
    }

    /**
     * @param  Application  $app
     */
    private function resolveResolverClass($app): string
    {
        try {
            $settings = $app->make(ReqdeskWidgetSettings::class);
            $candidate = $settings->user_resolver;
        } catch (\Throwable) {
            $candidate = null;
        }

        if (! is_string($candidate) || $candidate === '') {
            $candidate = (string) config('reqdesk-widget.user_resolver', DefaultUserResolver::class);
        }

        if (! class_exists($candidate) || ! is_subclass_of($candidate, WidgetUserResolver::class)) {
            return DefaultUserResolver::class;
        }

        return $candidate;
    }
}
