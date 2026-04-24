<?php

declare(strict_types=1);

use Reqdesk\Filament\Filament\Pages\ReqdeskSettings;

it('falls back to the translated default nav group when no plugin is registered', function (): void {
    $group = ReqdeskSettings::getNavigationGroup();

    expect($group)->toBeString()
        ->and($group)->not->toBe('');
});

it('falls back to the hardcoded lifebuoy icon when no plugin is registered', function (): void {
    expect(ReqdeskSettings::getNavigationIcon())->toBe('heroicon-o-lifebuoy');
});

it('returns null for nav sort when no plugin is registered', function (): void {
    expect(ReqdeskSettings::getNavigationSort())->toBeNull();
});
