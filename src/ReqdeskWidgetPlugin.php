<?php

declare(strict_types=1);

namespace Reqdesk\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Reqdesk\Filament\Filament\Pages\ReqdeskSettings;
use Reqdesk\Filament\Settings\ReqdeskWidgetSettings;

final class ReqdeskWidgetPlugin implements Plugin
{
    private ?bool $injectScript = null;

    private ?string $renderHook = null;

    private ?bool $registerSettingsPage = null;

    /** @var list<string>|null */
    private ?array $panels = null;

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static */
        return filament(app(static::class)->getId());
    }

    public function getId(): string
    {
        return 'reqdesk-widget';
    }

    public function injectScript(bool $condition = true): static
    {
        $this->injectScript = $condition;

        return $this;
    }

    public function renderHook(string $hook): static
    {
        $this->renderHook = $hook;

        return $this;
    }

    public function registerSettingsPage(bool $condition = true): static
    {
        $this->registerSettingsPage = $condition;

        return $this;
    }

    /**
     * @param  list<string>  $panels
     */
    public function onlyPanels(array $panels): static
    {
        $this->panels = array_values($panels);

        return $this;
    }

    public function register(Panel $panel): void
    {
        if ($this->shouldRegisterSettingsPage()) {
            $panel->pages([ReqdeskSettings::class]);
        }
    }

    public function boot(Panel $panel): void
    {
        if (! $this->shouldInject($panel)) {
            return;
        }

        FilamentView::registerRenderHook(
            $this->renderHook ?? PanelsRenderHook::BODY_END,
            fn (): string => Blade::render('<x-reqdesk::widget />'),
            scopes: $panel::class,
        );
    }

    private function shouldInject(Panel $panel): bool
    {
        if ($this->injectScript === false) {
            return false;
        }

        $settings = $this->resolveSettings();
        if ($settings !== null && ! $settings->enabled) {
            return false;
        }

        $allowedPanels = $this->panels
            ?? ($settings !== null ? $settings->panels : [])
            ?: (array) config('reqdesk-widget.panels', []);

        if ($allowedPanels !== [] && ! in_array($panel->getId(), $allowedPanels, true)) {
            return false;
        }

        return true;
    }

    private function shouldRegisterSettingsPage(): bool
    {
        return $this->registerSettingsPage ?? true;
    }

    private function resolveSettings(): ?ReqdeskWidgetSettings
    {
        try {
            return app(ReqdeskWidgetSettings::class);
        } catch (\Throwable) {
            return null;
        }
    }
}
