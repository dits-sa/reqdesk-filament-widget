<?php

declare(strict_types=1);

namespace Reqdesk\Filament\Services;

use Reqdesk\Filament\Exceptions\ReqdeskConfigurationException;

final class IdentitySigner
{
    /**
     * Sign the canonical email so Reqdesk can trust the widget-supplied
     * identity without an SSO round-trip. Mirrors the contract documented in
     * sdk/widget/examples/identify-README.md and verified server-side by
     * Reqdesk.Api's WidgetIdentityVerifier.
     *
     * @return array{email:string,userHash:string,userHashTimestamp:int}
     */
    public function sign(string $rawEmail, ?int $timestamp = null, ?string $secret = null): array
    {
        $secret = (string) ($secret ?? '');

        if ($secret === '') {
            throw new ReqdeskConfigurationException('Reqdesk signing secret is not configured.');
        }

        $email = strtolower(trim($rawEmail));

        if ($email === '') {
            throw new ReqdeskConfigurationException('Cannot sign an empty email address.');
        }

        $timestamp ??= time();
        $signature = 'sha256='.hash_hmac('sha256', $timestamp.'.'.$email, $secret);

        return [
            'email' => $email,
            'userHash' => $signature,
            'userHashTimestamp' => $timestamp,
        ];
    }
}
