<?php

declare(strict_types=1);

use Reqdesk\Filament\Services\ReqdeskClient;
use Reqdesk\Filament\Services\WidgetConfigBuilder;

it('resolves ReqdeskClient without requiring ReqdeskWidgetSettings in the constructor', function (): void {
    $reflection = new ReflectionClass(ReqdeskClient::class);
    $constructor = $reflection->getConstructor();

    $params = $constructor?->getParameters() ?? [];

    expect($params)->toBe([]);
});

it('resolves WidgetConfigBuilder without requiring ReqdeskWidgetSettings in the constructor', function (): void {
    $reflection = new ReflectionClass(WidgetConfigBuilder::class);
    $constructor = $reflection->getConstructor();

    $paramTypes = array_map(
        fn (ReflectionParameter $param): string => (string) $param->getType(),
        $constructor?->getParameters() ?? [],
    );

    expect($paramTypes)->not->toContain('Reqdesk\\Filament\\Settings\\ReqdeskWidgetSettings');
});

it('constructs ReqdeskClient via new without any arguments', function (): void {
    expect(fn () => new ReqdeskClient)
        ->not->toThrow(Throwable::class);
});
