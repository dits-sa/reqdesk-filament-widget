<?php

declare(strict_types=1);

namespace Reqdesk\Filament\Enums;

use Filament\Support\Contracts\HasLabel;

enum SheetSide: string implements HasLabel
{
    case Start = 'start';
    case End = 'end';

    public function getLabel(): string
    {
        return match ($this) {
            self::Start => 'Start (left / right in RTL)',
            self::End => 'End (right / left in RTL)',
        };
    }
}
