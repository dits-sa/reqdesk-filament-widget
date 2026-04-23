<?php

declare(strict_types=1);

namespace Reqdesk\Filament\Enums;

use Filament\Support\Contracts\HasLabel;

enum AuthMode: string implements HasLabel
{
    case Signed = 'signed';
    case Email = 'email';
    case Sso = 'sso';

    public function getLabel(): string
    {
        return match ($this) {
            self::Signed => 'Signed host-app identity',
            self::Email => 'Email prompt',
            self::Sso => 'SSO (OIDC)',
        };
    }
}
