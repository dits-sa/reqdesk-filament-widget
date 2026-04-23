<?php

declare(strict_types=1);

namespace Reqdesk\Filament\Enums;

use Filament\Support\Contracts\HasLabel;

enum Position: string implements HasLabel
{
    case BottomEnd = 'bottom-end';
    case BottomStart = 'bottom-start';
    case TopEnd = 'top-end';
    case TopStart = 'top-start';

    public function getLabel(): string
    {
        return match ($this) {
            self::BottomEnd => 'Bottom end',
            self::BottomStart => 'Bottom start',
            self::TopEnd => 'Top end',
            self::TopStart => 'Top start',
        };
    }
}
