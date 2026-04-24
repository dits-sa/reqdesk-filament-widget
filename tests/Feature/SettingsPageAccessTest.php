<?php

declare(strict_types=1);

use Reqdesk\Filament\Filament\Pages\ReqdeskSettings;

it('allows access by default when no plugin-level authorize callback is configured', function (): void {
    expect(ReqdeskSettings::canAccess())->toBeTrue();
});
