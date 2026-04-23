<?php

declare(strict_types=1);

namespace Reqdesk\Filament\Services;

use Reqdesk\Filament\Contracts\WidgetUserResolver;
use Reqdesk\Filament\Support\DefaultUserResolver;

/**
 * Single source of truth for environment/settings validation shared by the
 * install command, the doctor command, and the strict-mode boot check.
 */
final class ConfigValidator
{
    public function validateEnvironment(bool $requireSigningSecret = true): ConfigValidationReport
    {
        $errors = [];
        $warnings = [];
        $passed = [];

        $apiKey = (string) config('reqdesk-widget.api_key', '');
        if ($apiKey === '') {
            $errors[] = 'REQDESK_API_KEY is not set. Grab a project key from https://app.reqdesk.com → Project → API Keys.';
        } else {
            $passed[] = 'REQDESK_API_KEY is set.';

            if (! $this->looksLikeApiKey($apiKey)) {
                $warnings[] = 'REQDESK_API_KEY does not start with rqd_pk_ or rqd_ws_ — double-check the value.';
            }
        }

        $apiUrl = (string) config('reqdesk-widget.api_url', '');
        if ($apiUrl === '' || $apiUrl === 'https://app.reqdesk.com') {
            $warnings[] = 'REQDESK_API_URL falls back to https://app.reqdesk.com. Set it explicitly for self-hosted deployments.';
        } else {
            $passed[] = 'REQDESK_API_URL is set.';
        }

        $signingSecret = (string) config('reqdesk-widget.signing_secret', '');
        if ($signingSecret === '') {
            $message = 'REQDESK_SIGNING_SECRET is not set. Signed host-app identity is recommended so authenticated Laravel users skip the email prompt.';

            if ($requireSigningSecret) {
                $errors[] = $message;
            } else {
                $warnings[] = $message;
            }
        } else {
            $passed[] = 'REQDESK_SIGNING_SECRET is set.';
        }

        $resolver = (string) config('reqdesk-widget.user_resolver', DefaultUserResolver::class);
        if ($resolver === '' || ! class_exists($resolver)) {
            $errors[] = "User resolver class [{$resolver}] does not exist.";
        } elseif (! is_subclass_of($resolver, WidgetUserResolver::class)) {
            $errors[] = "User resolver class [{$resolver}] does not implement ".WidgetUserResolver::class.'.';
        } else {
            $passed[] = "User resolver {$resolver} is valid.";
        }

        $themeMode = (string) config('reqdesk-widget.theme_mode', 'auto');
        if (! in_array($themeMode, ['light', 'dark', 'auto'], true)) {
            $warnings[] = "REQDESK_THEME_MODE must be light|dark|auto — got '{$themeMode}'.";
        }

        $displayMode = (string) config('reqdesk-widget.display_mode', 'popover');
        if (! in_array($displayMode, ['popover', 'side-sheet', 'bottom-sheet'], true)) {
            $warnings[] = "REQDESK_DISPLAY_MODE must be popover|side-sheet|bottom-sheet — got '{$displayMode}'.";
        }

        return new ConfigValidationReport($errors, $warnings, $passed);
    }

    private function looksLikeApiKey(string $value): bool
    {
        return str_starts_with($value, 'rqd_pk_') || str_starts_with($value, 'rqd_ws_');
    }
}
