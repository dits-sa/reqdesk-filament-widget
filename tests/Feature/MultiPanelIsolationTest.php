<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Mockery\MockInterface;
use Reqdesk\Filament\ReqdeskWidgetPlugin;

it('does not register a render hook with a panel-id scope (would orphan the hook)', function (): void {
    FilamentView::spy();

    /** @var Panel&MockInterface $panel */
    $panel = Mockery::mock(Panel::class);
    $panel->shouldReceive('getId')->andReturn('admin');

    $plugin = ReqdeskWidgetPlugin::make()->injectScript(true);
    $plugin->boot($panel);

    // The hook MUST register without a scopes: argument (or an empty
    // default). If someone reintroduces scopes: $panel->getId() the
    // hook gets filed under a bucket Filament never consults, and the
    // widget silently never renders.
    FilamentView::shouldHaveReceived('registerRenderHook')
        ->withArgs(function (string $name, Closure $hook, string|array|null $scopes = null): bool {
            return $name === PanelsRenderHook::BODY_END && $scopes === null;
        })
        ->once();
});

it('honours a custom render hook override while staying unscoped', function (): void {
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
            return $name === 'panels::footer' && $scopes === null;
        })
        ->once();
});

it('always registers the hook unscoped; injection state is evaluated inside the closure at render time', function (): void {
    FilamentView::spy();

    /** @var Panel&MockInterface $panel */
    $panel = Mockery::mock(Panel::class);
    $panel->shouldReceive('getId')->andReturn('admin');

    // injectScript(false) must NOT skip the hook registration — the hook is
    // always registered, and the closure decides at render time whether to
    // emit markup or ''. Registering lazily would break mid-request toggles.
    $plugin = ReqdeskWidgetPlugin::make()->injectScript(false);
    $plugin->boot($panel);

    FilamentView::shouldHaveReceived('registerRenderHook')
        ->withArgs(function (string $name, Closure $hook, string|array|null $scopes = null): bool {
            return $name === PanelsRenderHook::BODY_END && $scopes === null;
        })
        ->once();
});

it('emits the widget markup only for the panel the plugin was booted on', function (): void {
    // Set up a minimal two-panel context. Boot the plugin on the 'admin'
    // panel; the closure should emit markup when the current panel is
    // 'admin' and emit nothing when it is anything else.
    config()->set('reqdesk-widget.api_key', 'rqd_pk_multi_panel_test');

    /** @var Panel&MockInterface $adminPanel */
    $adminPanel = Mockery::mock(Panel::class);
    $adminPanel->shouldReceive('getId')->andReturn('admin');

    ReqdeskWidgetPlugin::make()
        ->injectScript(true)
        ->boot($adminPanel);

    // Current panel = admin — closure should fire.
    Filament::shouldReceive('getCurrentOrDefaultPanel')
        ->andReturn($adminPanel)
        ->once();

    $adminHtml = (string) FilamentView::renderHook(
        PanelsRenderHook::BODY_END,
        scopes: [Dashboard::class],
    );

    expect($adminHtml)->toContain('reqdesk');

    // Current panel = something else — closure should return ''.
    /** @var Panel&MockInterface $agentPanel */
    $agentPanel = Mockery::mock(Panel::class);
    $agentPanel->shouldReceive('getId')->andReturn('agent');

    Filament::shouldReceive('getCurrentOrDefaultPanel')
        ->andReturn($agentPanel)
        ->once();

    $agentHtml = (string) FilamentView::renderHook(
        PanelsRenderHook::BODY_END,
        scopes: [Dashboard::class],
    );

    expect($agentHtml)->not->toContain('reqdesk');
})->skip('Requires a Filament panel boot + Blade view compilation; manual verification covered in the v1.3.2 release notes.');
