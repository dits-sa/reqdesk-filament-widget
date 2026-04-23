<?php

declare(strict_types=1);

use Reqdesk\Filament\Exceptions\ReqdeskConfigurationException;
use Reqdesk\Filament\Services\IdentitySigner;

it('produces the HMAC-SHA256 signature matching the widget contract', function (): void {
    $signer = new IdentitySigner;

    $result = $signer->sign('Admin@Example.COM', 1_700_000_000, 'super-secret');

    $expected = 'sha256='.hash_hmac('sha256', '1700000000.admin@example.com', 'super-secret');
    expect($result['email'])->toBe('admin@example.com')
        ->and($result['userHash'])->toBe($expected)
        ->and($result['userHashTimestamp'])->toBe(1_700_000_000);
});

it('canonicalises the email before signing', function (): void {
    $signer = new IdentitySigner;

    $result = $signer->sign('  MIXED@Case.Example  ', 1_700_000_000, 'secret');

    expect($result['email'])->toBe('mixed@case.example');
});

it('throws when no secret is supplied', function (): void {
    (new IdentitySigner)->sign('admin@example.com', 1_700_000_000, '');
})->throws(ReqdeskConfigurationException::class);

it('throws when the email is empty', function (): void {
    (new IdentitySigner)->sign('   ', 1_700_000_000, 'secret');
})->throws(ReqdeskConfigurationException::class);
