<?php

declare(strict_types=1);

use Reqdesk\Filament\Contracts\WidgetUserResolver;
use Reqdesk\Filament\Services\ConfigValidator;
use Reqdesk\Filament\Support\DefaultUserResolver;

beforeEach(function (): void {
    config()->set('reqdesk-widget.api_key', '');
    config()->set('reqdesk-widget.api_url', '');
    config()->set('reqdesk-widget.signing_secret', '');
    config()->set('reqdesk-widget.user_resolver', DefaultUserResolver::class);
});

it('flags a missing api key as an error', function (): void {
    $report = (new ConfigValidator())->validateEnvironment();

    expect($report->isOk())->toBeFalse()
        ->and($report->errors)->toContain('REQDESK_API_KEY is not set. Grab a project key from https://app.reqdesk.com → Project → API Keys.');
});

it('warns about an unrecognised api key prefix', function (): void {
    config()->set('reqdesk-widget.api_key', 'xxx_not_a_key');

    $report = (new ConfigValidator())->validateEnvironment(requireSigningSecret: false);

    expect($report->warnings)->toContain('REQDESK_API_KEY does not start with rqd_pk_ or rqd_ws_ — double-check the value.');
});

it('downgrades signing secret to a warning when requireSigningSecret is false', function (): void {
    config()->set('reqdesk-widget.api_key', 'rqd_pk_abc');

    $report = (new ConfigValidator())->validateEnvironment(requireSigningSecret: false);

    expect($report->isOk())->toBeTrue()
        ->and($report->warnings)->not->toBeEmpty();
});

it('errors on an invalid resolver class', function (): void {
    config()->set('reqdesk-widget.api_key', 'rqd_pk_abc');
    config()->set('reqdesk-widget.signing_secret', 'secret');
    config()->set('reqdesk-widget.user_resolver', \stdClass::class);

    $report = (new ConfigValidator())->validateEnvironment();

    expect($report->errors)->toContain('User resolver class [stdClass] does not implement '.WidgetUserResolver::class.'.');
});

it('passes with a complete configuration', function (): void {
    config()->set('reqdesk-widget.api_key', 'rqd_pk_abc');
    config()->set('reqdesk-widget.api_url', 'https://reqdesk.example.com');
    config()->set('reqdesk-widget.signing_secret', 'secret');

    $report = (new ConfigValidator())->validateEnvironment();

    expect($report->isOk())->toBeTrue()
        ->and($report->errors)->toBeEmpty();
});
