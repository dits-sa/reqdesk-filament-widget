<?php

declare(strict_types=1);

use Reqdesk\Filament\Support\DefaultUserResolver;

return [

    /*
    |--------------------------------------------------------------------------
    | Connection
    |--------------------------------------------------------------------------
    |
    | Credentials issued by the Reqdesk dashboard.
    |
    |   api_key         Project-scoped public key (rqd_pk_...) or workspace key
    |                   (rqd_ws_...). Exposed in the browser.
    |   signing_secret  HMAC secret for signed host-app identity. Server-side
    |                   ONLY — the browser receives the resulting signature,
    |                   never the secret.
    |
    */

    'api_key' => env('REQDESK_API_KEY'),
    'api_url' => env('REQDESK_API_URL', 'https://app.reqdesk.com'),
    'signing_secret' => env('REQDESK_SIGNING_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Appearance
    |--------------------------------------------------------------------------
    */

    'theme_primary_color' => env('REQDESK_THEME_PRIMARY', '#0F5E56'),
    'theme_mode' => env('REQDESK_THEME_MODE', 'auto'),
    'theme_border_radius' => env('REQDESK_THEME_RADIUS', '6px'),
    'theme_font_family' => env('REQDESK_THEME_FONT'),
    'theme_z_index' => (int) env('REQDESK_THEME_ZINDEX', 9999),
    'theme_logo' => env('REQDESK_LOGO_URL'),
    'theme_brand_name' => env('REQDESK_BRAND_NAME'),
    'theme_hide_branding' => (bool) env('REQDESK_HIDE_BRANDING', false),

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    */

    'position' => env('REQDESK_POSITION', 'bottom-end'),
    'display_mode' => env('REQDESK_DISPLAY_MODE', 'popover'),
    'display_side' => env('REQDESK_DISPLAY_SIDE', 'end'),
    'display_width' => env('REQDESK_DISPLAY_WIDTH', '420px'),
    'display_height' => env('REQDESK_DISPLAY_HEIGHT', '55vh'),
    'display_dismiss_on_backdrop' => (bool) env('REQDESK_DISPLAY_DISMISS', true),
    'hide_fab' => (bool) env('REQDESK_HIDE_FAB', false),
    'hide_display_mode_picker' => (bool) env('REQDESK_HIDE_PICKER', false),
    'fab_icon' => env('REQDESK_FAB_ICON', 'help'),

    /*
    |--------------------------------------------------------------------------
    | Localization & behavior
    |--------------------------------------------------------------------------
    */

    'default_language' => env('REQDESK_LANGUAGE', 'en'),
    'widget_mode' => env('REQDESK_WIDGET_MODE', 'support-portal'),
    'default_category' => env('REQDESK_DEFAULT_CATEGORY'),

    /*
    |--------------------------------------------------------------------------
    | Signed host-app identity
    |--------------------------------------------------------------------------
    |
    | Laravel's authenticated user is signed automatically. Override the
    | resolver if your user model does not expose ->email / ->name directly.
    | The two auth-mode lists let the widget switch surfaces cleanly between
    | anonymous visitors and signed-in users.
    |
    */

    'auth_mode_when_signed' => array_values(array_filter(explode(',', (string) env('REQDESK_AUTH_MODE_SIGNED', 'signed')))),
    'auth_mode_when_anonymous' => array_values(array_filter(explode(',', (string) env('REQDESK_AUTH_MODE_ANON', 'email')))),
    'user_resolver' => env('REQDESK_USER_RESOLVER', DefaultUserResolver::class),

    /*
    |--------------------------------------------------------------------------
    | Distribution & runtime
    |--------------------------------------------------------------------------
    */

    'enabled' => (bool) env('REQDESK_ENABLED', true),
    'inject_for_guests' => (bool) env('REQDESK_INJECT_GUESTS', false),
    'panels' => array_values(array_filter(explode(',', (string) env('REQDESK_PANELS', '')))),
    'script_url' => env('REQDESK_SCRIPT_URL'),
    'strict' => (bool) env('REQDESK_STRICT', false),
    'install_skip_signing' => (bool) env('REQDESK_INSTALL_SKIP_SIGNING', false),

    /*
    |--------------------------------------------------------------------------
    | Identity endpoint
    |--------------------------------------------------------------------------
    |
    | The widget's refreshIdentity() callback hits this route when the
    | cached HMAC is older than five minutes. Middleware is required so the
    | route cannot be polled by unauthenticated visitors.
    |
    */

    'identity' => [
        'endpoint' => env('REQDESK_IDENTITY_ENDPOINT', '/reqdesk/widget/identity'),
        'middleware' => array_values(array_filter(explode(',', (string) env('REQDESK_IDENTITY_MIDDLEWARE', 'web,auth')))),
    ],

    /*
    |--------------------------------------------------------------------------
    | CDN / self-hosted widget version pin
    |--------------------------------------------------------------------------
    |
    | Pinned default, served from the Reqdesk-owned CDN
    | (cdn.reqdesk.support). Hosted on Dokploy behind Traefik +
    | Let's Encrypt — see docs/guides/cdn-dokploy.md in the main Reqdesk
    | repo. Override via REQDESK_SCRIPT_URL when pinning a specific
    | version, hosting the widget yourself, or testing a pre-release
    | build.
    |
    */

    'script_url_default' => 'https://cdn.reqdesk.support/widget/1.2.22/index.iife.js',
];
