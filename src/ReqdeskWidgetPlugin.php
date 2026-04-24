<?php

declare(strict_types=1);

namespace Reqdesk\Filament;

use BackedEnum;
use Closure;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Reqdesk\Filament\Filament\Pages\ReqdeskSettings;
use Reqdesk\Filament\Settings\ReqdeskWidgetSettings;
use Throwable;
use UnitEnum;

final class ReqdeskWidgetPlugin implements Plugin
{
    private ?bool $injectScript = null;

    private ?string $renderHook = null;

    private ?bool $registerSettingsPage = null;

    /** @var list<string>|null */
    private ?array $panels = null;

    private string|UnitEnum|null $navigationGroup = null;

    private ?int $navigationSort = null;

    private string|BackedEnum|null $navigationIcon = null;

    private string|Closure|null $authorizeUsing = null;

    public static function make(): static
    {
        return new self;
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
        $this->panels = $panels;

        return $this;
    }

    public function navigationGroup(string|UnitEnum|null $group): static
    {
        $this->navigationGroup = $group;

        return $this;
    }

    public function navigationSort(?int $sort): static
    {
        $this->navigationSort = $sort;

        return $this;
    }

    public function navigationIcon(string|BackedEnum|null $icon): static
    {
        $this->navigationIcon = $icon;

        return $this;
    }

    public function authorize(string|Closure|null $ability): static
    {
        $this->authorizeUsing = $ability;

        return $this;
    }

    public function getNavigationGroup(): string|UnitEnum|null
    {
        return $this->navigationGroup;
    }

    public function getNavigationSort(): ?int
    {
        return $this->navigationSort;
    }

    public function getNavigationIcon(): string|BackedEnum|null
    {
        return $this->navigationIcon;
    }

    public function getAuthorizeCallback(): string|Closure|null
    {
        return $this->authorizeUsing;
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
            scopes: $panel->getId(),
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
        } catch (Throwable) {
            return null;
        }
    }
}
