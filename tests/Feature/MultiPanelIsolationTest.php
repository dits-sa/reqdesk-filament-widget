<?php

declare(strict_types=1);

use Filament\Panel;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Mockery\MockInterface;
use Reqdesk\Filament\ReqdeskWidgetPlugin;

it('registers the body-end render hook scoped to the panel id, not the panel class', function (): void {
    FilamentView::spy();

    /** @var Panel&MockInterface $panel */
    $panel = Mockery::mock(Panel::class);
    $panel->shouldReceive('getId')->andReturn('admin');

    $plugin = ReqdeskWidgetPlugin::make()
        ->injectScript(true);

    $plugin->boot($panel);

    FilamentView::shouldHaveReceived('registerRenderHook')
        ->withArgs(function (string $name, Closure $hook, string|array|null $scopes = null): bool {
            return $name === PanelsRenderHook::BODY_END
                && $scopes === 'admin';
        })
        ->once();
});

it('honours a custom render hook override while keeping the panel-id scope', function (): void {
    FilamentView::spy();

    /** @var Panel&MockInterface $panel */
    $panel = Mockery::mock(Panel::class);
    $panel->shouldReceive('getId')->andReturn('agent');

    $plugin = ReqdeskWidgetPlugin::make()
        ->injectScript(true)
        ->renderHook('panels::footer');

    $plugin->boot($panel);

    FilamentView::shouldHaveReceived('registerRenderHook')
        ->withArgs(function (string $name, Closure $hook, string|array|null $scopes = null): bool {
            return $name === 'panels::footer'
                && $scopes === 'agent';
        })
        ->once();
});

it('does not register any render hook when injectScript is disabled', function (): void {
    FilamentView::spy();

    /** @var Panel&MockInterface $panel */
    $panel = Mockery::mock(Panel::class);
    $panel->shouldReceive('getId')->andReturn('customer');

    $plugin = ReqdeskWidgetPlugin::make()->injectScript(false);

    $plugin->boot($panel);

    FilamentView::shouldNotHaveReceived('registerRenderHook');
});
