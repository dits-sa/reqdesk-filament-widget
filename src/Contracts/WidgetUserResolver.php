<?php

declare(strict_types=1);

namespace Reqdesk\Filament\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

interface WidgetUserResolver
{
    /**
     * Resolve the host-app identity for the widget's signed-identity payload.
     *
     * Return null when the caller is anonymous or when no usable email is
     * available — the widget will fall back to the `auth_mode_when_anonymous`
     * modes (email prompt by default).
     *
     * @return array{email:string,name?:string,externalId?:string}|null
     */
    public function resolve(?Authenticatable $user): ?array;
}
