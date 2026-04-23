<?php

declare(strict_types=1);

namespace Reqdesk\Filament\Support;

use Illuminate\Contracts\Auth\Authenticatable;
use Reqdesk\Filament\Contracts\WidgetUserResolver;

final class DefaultUserResolver implements WidgetUserResolver
{
    public function resolve(?Authenticatable $user): ?array
    {
        if ($user === null) {
            return null;
        }

        $email = $this->extract($user, 'email');

        if ($email === '') {
            return null;
        }

        $resolved = ['email' => $email];

        $name = $this->extract($user, 'name');
        if ($name !== '') {
            $resolved['name'] = $name;
        }

        $externalId = (string) ($user->getAuthIdentifier() ?? '');
        if ($externalId !== '') {
            $resolved['externalId'] = $externalId;
        }

        return $resolved;
    }

    private function extract(Authenticatable $user, string $key): string
    {
        /** @var mixed $value */
        $value = $user->{$key} ?? null;

        return trim((string) ($value ?? ''));
    }
}
