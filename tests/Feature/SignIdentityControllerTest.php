<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Reqdesk\Filament\Http\Controllers\SignIdentityController;
use Reqdesk\Filament\Settings\ReqdeskWidgetSettings;
use Reqdesk\Filament\Tests\Fixtures\TestUser;

beforeEach(function (): void {
    Route::get('/reqdesk/widget/identity', SignIdentityController::class)->name('reqdesk.widget.identify');
});

it('returns 401 when the caller is not authenticated', function (): void {
    $this->getJson('/reqdesk/widget/identity')
        ->assertStatus(401);
});

it('returns 503 when no signing secret is configured', function (): void {
    $user = new TestUser();

    $this->actingAs($user)
        ->getJson('/reqdesk/widget/identity')
        ->assertStatus(503);
});

it('returns a fresh signed payload when configured', function (): void {
    $settings = app(ReqdeskWidgetSettings::class);
    $settings->signing_secret = 'super-secret';

    $user = new TestUser(email: 'user@example.com', name: 'Example');

    $response = $this->actingAs($user)->getJson('/reqdesk/widget/identity');

    $response
        ->assertOk()
        ->assertHeader('Cache-Control', 'no-store, max-age=0')
        ->assertJsonPath('email', 'user@example.com')
        ->assertJsonStructure(['email', 'userHash', 'userHashTimestamp']);

    expect($response->json('userHash'))->toStartWith('sha256=');
});
