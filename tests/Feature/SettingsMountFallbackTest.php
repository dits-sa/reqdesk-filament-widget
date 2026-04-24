<?php

declare(strict_types=1);

use Reqdesk\Filament\Settings\ReqdeskWidgetSettings;

it('does not expose a project_id property', function (): void {
    expect(property_exists(ReqdeskWidgetSettings::class, 'project_id'))->toBeFalse();
});

it('falls back to config values when persisted settings are null or empty', function (): void {
    config()->set('reqdesk-widget.api_key', 'rqd_pk_env_fallback');
    config()->set('reqdesk-widget.api_url', 'https://env.example.com');

    $persisted = [
        'api_key' => null,
        'api_url' => '',
        'signing_secret' => null,
        'theme_hide_branding' => false,
        'panels' => [],
    ];

    $data = applyMountFallback($persisted);

    expect($data['api_key'])->toBe('rqd_pk_env_fallback')
        ->and($data['api_url'])->toBe('https://env.example.com')
        ->and($data['theme_hide_branding'])->toBeFalse()
        ->and($data['panels'])->toBe([]);
});

it('does not overwrite persisted values with config fallbacks', function (): void {
    config()->set('reqdesk-widget.api_key', 'rqd_pk_env_fallback');

    $persisted = ['api_key' => 'rqd_pk_saved_in_db'];

    $data = applyMountFallback($persisted);

    expect($data['api_key'])->toBe('rqd_pk_saved_in_db');
});

/**
 * Mirrors the ReqdeskSettings::mount() fallback loop so we can test
 * the behaviour without booting Livewire + Filament.
 *
 * @param  array<string, mixed>  $persisted
 * @return array<string, mixed>
 */
function applyMountFallback(array $persisted): array
{
    foreach ($persisted as $key => $value) {
        if ($value !== null && $value !== '') {
            continue;
        }

        $fallback = config("reqdesk-widget.{$key}");
        if ($fallback === null || $fallback === '') {
            continue;
        }

        $persisted[$key] = $fallback;
    }

    return $persisted;
}
