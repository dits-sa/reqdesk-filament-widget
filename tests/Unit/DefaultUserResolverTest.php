<?php

declare(strict_types=1);

use Reqdesk\Filament\Support\DefaultUserResolver;
use Reqdesk\Filament\Tests\Fixtures\TestUser;

it('returns null when no user is supplied', function (): void {
    expect((new DefaultUserResolver)->resolve(null))->toBeNull();
});

it('extracts email, name and external id from Authenticatable', function (): void {
    $user = new TestUser(id: 7, email: 'user@example.com', name: 'Example User');

    $resolved = (new DefaultUserResolver)->resolve($user);

    expect($resolved)->toBe([
        'email' => 'user@example.com',
        'name' => 'Example User',
        'externalId' => '7',
    ]);
});

it('returns null when the email is missing or blank', function (): void {
    $user = new TestUser(email: '');

    expect((new DefaultUserResolver)->resolve($user))->toBeNull();
});

it('omits the name when it is blank', function (): void {
    $user = new TestUser(name: '');

    $resolved = (new DefaultUserResolver)->resolve($user);

    expect($resolved)->toHaveKey('email')
        ->and($resolved)->not->toHaveKey('name');
});
