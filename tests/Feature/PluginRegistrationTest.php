<?php

declare(strict_types=1);

use Reqdesk\Filament\ReqdeskWidgetPlugin;

enum FakeNavGroupBare
{
    case Support;
}

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

it('returns a fresh instance from make() so builder state does not bleed', function (): void {
    $a = ReqdeskWidgetPlugin::make()->navigationSort(10);
    $b = ReqdeskWidgetPlugin::make();

    expect($a)->not->toBe($b)
        ->and($a->getNavigationSort())->toBe(10)
        ->and($b->getNavigationSort())->toBeNull();
});

it('stores a string navigation group', function (): void {
    $plugin = ReqdeskWidgetPlugin::make()->navigationGroup('Support');

    expect($plugin->getNavigationGroup())->toBe('Support');
});

it('stores a bare unit-enum navigation group', function (): void {
    $plugin = ReqdeskWidgetPlugin::make()->navigationGroup(FakeNavGroupBare::Support);

    expect($plugin->getNavigationGroup())->toBe(FakeNavGroupBare::Support);
});

it('stores a string navigation icon and an int sort', function (): void {
    $plugin = ReqdeskWidgetPlugin::make()
        ->navigationIcon('heroicon-o-chat-bubble-left-right')
        ->navigationSort(42);

    expect($plugin->getNavigationIcon())->toBe('heroicon-o-chat-bubble-left-right')
        ->and($plugin->getNavigationSort())->toBe(42);
});

it('stores a string authorize ability', function (): void {
    $plugin = ReqdeskWidgetPlugin::make()->authorize('reqdesk.settings.manage');

    expect($plugin->getAuthorizeCallback())->toBe('reqdesk.settings.manage');
});

it('stores a closure authorize callback', function (): void {
    $closure = fn (): bool => true;
    $plugin = ReqdeskWidgetPlugin::make()->authorize($closure);

    expect($plugin->getAuthorizeCallback())->toBe($closure);
});

it('defaults every navigation and authorize getter to null', function (): void {
    $plugin = ReqdeskWidgetPlugin::make();

    expect($plugin->getNavigationGroup())->toBeNull()
        ->and($plugin->getNavigationSort())->toBeNull()
        ->and($plugin->getNavigationIcon())->toBeNull()
        ->and($plugin->getAuthorizeCallback())->toBeNull();
});
