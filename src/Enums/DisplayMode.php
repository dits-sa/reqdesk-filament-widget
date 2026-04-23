<?php

declare(strict_types=1);

namespace Reqdesk\Filament\Enums;

use Filament\Support\Contracts\HasLabel;

enum DisplayMode: string implements HasLabel
{
    case Popover = 'popover';
    case SideSheet = 'side-sheet';
    case BottomSheet = 'bottom-sheet';

    public function getLabel(): string
    {
        return match ($this) {
            self::Popover => 'Popover',
            self::SideSheet => 'Side sheet',
            self::BottomSheet => 'Bottom sheet',
        };
    }
}
