<?php

declare(strict_types=1);

namespace Reqdesk\Filament\Enums;

use Filament\Support\Contracts\HasLabel;

enum WidgetMode: string implements HasLabel
{
    case TicketForm = 'ticket-form';
    case SupportPortal = 'support-portal';

    public function getLabel(): string
    {
        return match ($this) {
            self::TicketForm => 'Ticket form',
            self::SupportPortal => 'Support portal',
        };
    }
}
