<?php

declare(strict_types=1);

namespace Reqdesk\Filament\Tests\Fixtures;

use Illuminate\Contracts\Auth\Authenticatable;

final class TestUser implements Authenticatable
{
    public function __construct(
        public ?int $id = 42,
        public ?string $email = 'Admin@Example.COM',
        public ?string $name = 'Admin User',
    ) {}

    public function getAttribute(string $key): mixed
    {
        return $this->{$key} ?? null;
    }

    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    public function getAuthIdentifier(): mixed
    {
        return $this->id;
    }

    public function getAuthPasswordName(): string
    {
        return 'password';
    }

    public function getAuthPassword(): string
    {
        return '';
    }

    public function getRememberToken(): string
    {
        return '';
    }

    public function setRememberToken($value): void
    {
        // no-op
    }

    public function getRememberTokenName(): string
    {
        return 'remember_token';
    }
}
