<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Reqdesk\Filament\Http\Controllers\SignIdentityController;

Route::middleware((array) config('reqdesk-widget.identity.middleware', ['web', 'auth']))
    ->get(
        (string) config('reqdesk-widget.identity.endpoint', '/reqdesk/widget/identity'),
        SignIdentityController::class,
    )
    ->name('reqdesk.widget.identify');
