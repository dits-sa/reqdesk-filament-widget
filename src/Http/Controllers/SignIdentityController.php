<?php

declare(strict_types=1);

namespace Reqdesk\Filament\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Reqdesk\Filament\Contracts\WidgetUserResolver;
use Reqdesk\Filament\Services\IdentitySigner;
use Reqdesk\Filament\Settings\ReqdeskWidgetSettings;
use Throwable;

final class SignIdentityController
{
    public function __construct(
        private readonly IdentitySigner $signer,
        private readonly WidgetUserResolver $resolver,
        private readonly ReqdeskWidgetSettings $settings,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user === null) {
            abort(401);
        }

        $secret = (string) ($this->settings->signing_secret ?? config('reqdesk-widget.signing_secret', ''));
        if ($secret === '') {
            abort(503, 'Reqdesk signing secret is not configured.');
        }

        $identity = $this->resolver->resolve($user);
        $email = $identity['email'] ?? null;
        if (! is_string($email) || $email === '') {
            abort(401, 'No resolvable email for the current user.');
        }

        try {
            $signed = $this->signer->sign($email, null, $secret);
        } catch (Throwable $exception) {
            report($exception);
            abort(500, 'Unable to sign Reqdesk identity.');
        }

        return response()
            ->json(array_filter([
                'email' => $signed['email'],
                'name' => $identity['name'] ?? null,
                'externalId' => $identity['externalId'] ?? null,
                'userHash' => $signed['userHash'],
                'userHashTimestamp' => $signed['userHashTimestamp'],
            ], fn ($value): bool => $value !== null && $value !== ''))
            ->header('Cache-Control', 'no-store, max-age=0');
    }
}
