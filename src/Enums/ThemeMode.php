<?php

declare(strict_types=1);

namespace Reqdesk\Filament\Enums;

use Filament\Support\Contracts\HasLabel;

enum ThemeMode: string implements HasLabel
{
    case Light = 'light';
    case Dark = 'dark';
    case Auto = 'auto';

    public function getLabel(): string
    {
        return match ($this) {
            self::Light => 'Light',
            self::Dark => 'Dark',
            self::Auto => 'Auto (follow OS)',
        };
    }
}
