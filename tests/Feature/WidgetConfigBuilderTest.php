<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Reqdesk\Filament\Http\Controllers\SignIdentityController;
use Reqdesk\Filament\Services\WidgetConfigBuilder;
use Reqdesk\Filament\Settings\ReqdeskWidgetSettings;
use Reqdesk\Filament\Support\DefaultUserResolver;
use Reqdesk\Filament\Tests\Fixtures\TestUser;

beforeEach(function (): void {
    Route::get('/reqdesk/widget/identity', SignIdentityController::class)->name('reqdesk.widget.identify');
    config()->set('reqdesk-widget.api_key', 'rqd_pk_abc');
    config()->set('reqdesk-widget.api_url', 'https://reqdesk.example.com');
    config()->set('reqdesk-widget.user_resolver', DefaultUserResolver::class);
});

it('returns null when the widget is disabled', function (): void {
    $settings = app(ReqdeskWidgetSettings::class);
    $settings->enabled = false;

    $result = app(WidgetConfigBuilder::class)->build(null);

    expect($result)->toBeNull();
});

it('returns null when no api key is configured', function (): void {
    config()->set('reqdesk-widget.api_key', '');

    $result = app(WidgetConfigBuilder::class)->build(null);

    expect($result)->toBeNull();
});

it('builds a guest payload when no user is authenticated', function (): void {
    $result = app(WidgetConfigBuilder::class)->build(null);

    expect($result)->not->toBeNull()
        ->and($result['init']['apiKey'])->toBe('rqd_pk_abc')
        ->and($result['init']['authMode'])->toBe(['email'])
        ->and($result['init'])->not->toHaveKey('customer')
        ->and($result['identifyEndpoint'])->toBeNull();
});

it('injects a signed customer payload when a user is authenticated', function (): void {
    $settings = app(ReqdeskWidgetSettings::class);
    $settings->signing_secret = 'super-secret';

    $user = new TestUser(email: 'signed@example.com', name: 'Signed User');

    $result = app(WidgetConfigBuilder::class)->build($user);

    expect($result)->not->toBeNull()
        ->and($result['init']['authMode'])->toBe(['signed'])
        ->and($result['init']['customer']['email'])->toBe('signed@example.com')
        ->and($result['init']['customer']['userHash'])->toStartWith('sha256=')
        ->and($result['init']['customer']['userHashTimestamp'])->toBeInt()
        ->and($result['identifyEndpoint'])->toContain('/reqdesk/widget/identity');
});

it('falls back to anonymous modes when signing secret is missing', function (): void {
    $user = new TestUser;

    $result = app(WidgetConfigBuilder::class)->build($user);

    expect($result['init']['authMode'])->toBe(['email'])
        ->and($result['init'])->not->toHaveKey('customer');
});
