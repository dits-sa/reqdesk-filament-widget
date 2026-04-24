<?php

declare(strict_types=1);

use Reqdesk\Filament\Services\ReqdeskClient;
use Reqdesk\Filament\Services\ReqdeskPingResult;
use Reqdesk\Filament\Services\WidgetConfigBuilder;
use Reqdesk\Filament\Settings\ReqdeskWidgetSettings;

it('hydrates ReqdeskWidgetSettings without tripping the phpdoc parser', function (): void {
    expect(fn () => app(ReqdeskWidgetSettings::class))
        ->not->toThrow(Throwable::class);
});

it('lets ReqdeskClient::ping() run end-to-end without a phpdoc parser failure', function (): void {
    config()->set('reqdesk-widget.api_key', '');
    config()->set('reqdesk-widget.api_url', '');

    $result = app(ReqdeskClient::class)->ping();

    expect($result)->toBeInstanceOf(ReqdeskPingResult::class);
});

it('builds a widget payload without tripping the phpdoc parser on the actions property', function (): void {
    config()->set('reqdesk-widget.api_key', 'rqd_pk_abc');

    $result = app(WidgetConfigBuilder::class)->build(null);

    expect($result)->not->toBeNull();
});
