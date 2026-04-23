<?php

declare(strict_types=1);

namespace Reqdesk\Filament\Enums;

use Filament\Support\Contracts\HasLabel;

enum FabIcon: string implements HasLabel
{
    case Help = 'help';
    case Chat = 'chat';

    public function getLabel(): string
    {
        return match ($this) {
            self::Help => 'Help bubble',
            self::Chat => 'Chat bubble',
        };
    }
}
