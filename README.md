# Reqdesk Filament widget

[![Latest Version on Packagist](https://img.shields.io/packagist/v/dits-sa/reqdesk-filament-widget.svg?style=flat-square)](https://packagist.org/packages/dits-sa/reqdesk-filament-widget)
[![Total Downloads](https://img.shields.io/packagist/dt/dits-sa/reqdesk-filament-widget.svg?style=flat-square)](https://packagist.org/packages/dits-sa/reqdesk-filament-widget)
[![License](https://img.shields.io/packagist/l/dits-sa/reqdesk-filament-widget.svg?style=flat-square)](LICENSE.md)

Drop the Reqdesk support widget into any Filament v4 or v5 panel, configure every option from a native settings page, and HMAC-sign authenticated Laravel users so they never see a second login.

```php
use Reqdesk\Filament\ReqdeskWidgetPlugin;

public function panel(Panel $panel): Panel
{
    return $panel->plugin(ReqdeskWidgetPlugin::make());
}
```

That's the whole wire-up. `REQDESK_API_KEY` + `REQDESK_SIGNING_SECRET` in `.env`, plus the line above, and your admins see the widget already logged in as themselves.

## Table of contents

- [Why this plugin](#why-this-plugin)
- [Requirements](#requirements)
- [60-second quickstart](docs/01-quickstart.md)
- [Configuration reference](docs/02-configuration.md) — every setting, every env var
- [Signed host-app identity](docs/03-identity.md) — how the no-login flow works + custom resolvers
- [Security & threat model](docs/04-security.md)
- [Custom menu actions cookbook](docs/05-custom-actions.md)
- [Multi-panel & multi-tenant](docs/06-multi-panel.md)
- [Troubleshooting](docs/07-troubleshooting.md)
- [Upgrade guide](docs/08-upgrading.md)
- [Contributing](CONTRIBUTING.md)
- [Security policy](SECURITY.md)
- [Changelog](CHANGELOG.md)

## Why this plugin

Laravel teams running Filament already have the information Reqdesk needs in order to trust a user: the session and the user's email. This plugin closes the loop so there's no second login prompt, no user-pasted email form, and no redirect ping-pong — the widget opens straight into the admin's ticket history.

- **One-line registration** on your `PanelProvider`.
- **Render hook injection** into `PanelsRenderHook::BODY_END`, scoped to the panel you register it on. No global script tag, no layout edits.
- **Signed host-app identity by default** — Laravel's authenticated user is HMAC-signed (`sha256={hex}` over `"{ts}.{email}"`) so the widget trusts the email without an SSO round-trip.
- **Swappable resolver** — implement `WidgetUserResolver` when your user model doesn't expose `->email` / `->name` directly.
- **Two-mode auth** — the widget automatically switches between `auth_mode_when_signed` (default `signed`) and `auth_mode_when_anonymous` (default `email`) based on Laravel's session.
- **Full settings page** covering every `ReqdeskWidgetConfig` key across seven tabs.
- **`spatie/laravel-settings` persistence** with encrypted `api_key` and `signing_secret` columns and env-backed first-load defaults.
- **Install + doctor commands** that fail fast on missing env vars and run a non-destructive health check.
- **Strict mode** (`REQDESK_STRICT=true`) promotes missing configuration to a hard exception at boot — great for staging, off by default in production.
- **Arabic and English translations** shipped.

## Requirements

| Package | Version |
|---------|---------|
| PHP | 8.2, 8.3, 8.4 |
| Laravel | 11.28+, 12.x |
| Filament | 4.x or 5.x |
| spatie/laravel-settings | 3.4+ |

Laravel 11.0 – 11.27 is supported only on the Filament 4.x lane; Filament 5
requires Laravel 11.28+.

## Installation at a glance

```bash
composer require dits-sa/reqdesk-filament-widget
php artisan reqdesk-widget:install
```

```ini
REQDESK_API_KEY=rqd_pk_your_project_key
REQDESK_SIGNING_SECRET=your_server_side_hmac_secret
```

```php
->plugin(ReqdeskWidgetPlugin::make())
```

Full walkthrough: [`docs/01-quickstart.md`](docs/01-quickstart.md).

## License

MIT — see [LICENSE.md](LICENSE.md).
