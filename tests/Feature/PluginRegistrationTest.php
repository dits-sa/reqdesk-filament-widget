<?php

declare(strict_types=1);

use Reqdesk\Filament\ReqdeskWidgetPlugin;

it('exposes a stable id and fluent make()', function (): void {
    $plugin = ReqdeskWidgetPlugin::make();

    expect($plugin)
        ->toBeInstanceOf(ReqdeskWidgetPlugin::class)
        ->and($plugin->getId())->toBe('reqdesk-widget');
});

it('supports fluent configuration', function (): void {
    $plugin = ReqdeskWidgetPlugin::make()
        ->injectScript(false)
        ->registerSettingsPage(false)
        ->onlyPanels(['admin']);

    expect($plugin)->toBeInstanceOf(ReqdeskWidgetPlugin::class);
});
