# 60-second quickstart

This guide gets you from `composer require` to a working, authenticated widget in a fresh Filament v4 app. Follow it end-to-end the first time; bookmark [Configuration reference](02-configuration.md) for everything else.

## 1. Install the package

```bash
composer require dits-sa/reqdesk-filament-widget
```

## 2. Run the installer

```bash
php artisan reqdesk-widget:install
```

The installer:

1. Publishes `config/reqdesk-widget.php`.
2. Publishes the `reqdesk_widget_settings` migration and asks whether to run it.
3. Validates your environment and **exits non-zero** if `REQDESK_API_KEY` or `REQDESK_SIGNING_SECRET` is missing.

If you intentionally want to skip the signing-secret requirement (e.g. you're only running anonymous email-prompt mode), run with `REQDESK_INSTALL_SKIP_SIGNING=true`.

## 3. Add the env vars

Grab your keys from the Reqdesk dashboard:

- **API key** — Project → API Keys → *Create public widget key* → `rqd_pk_...`
- **Signing secret** — Project → Widget identity → *Generate signing secret*

Add to `.env`:

```ini
REQDESK_API_KEY=rqd_pk_...
REQDESK_SIGNING_SECRET=...
# Optional: self-hosted Reqdesk
REQDESK_API_URL=https://reqdesk.yourdomain.com
```

A full list of every overridable variable lives in [`.env.example`](../.env.example) and is described per-setting in [Configuration reference](02-configuration.md).

## 4. Register the plugin

Open your `PanelProvider` — by default `app/Providers/Filament/AdminPanelProvider.php`:

```php
use Reqdesk\Filament\ReqdeskWidgetPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugin(ReqdeskWidgetPlugin::make());
}
```

## 5. Verify

Sign in to your Filament panel. You should see:

- The floating action button in the bottom-end corner on every panel page.
- Clicking it opens the widget pre-signed as the current Laravel user — *no email prompt*, *no second login*.
- A new nav item **Reqdesk → Support widget** where you can tweak every setting live.

## 6. Run the doctor

```bash
php artisan reqdesk-widget:doctor
```

This runs the same checks as the installer, non-destructively, and additionally pings the Reqdesk API with your key. Safe to run from CI after deploys.

## Next steps

- [Configuration reference](02-configuration.md) — tune every option the widget exposes.
- [Signed host-app identity](03-identity.md) — swap the resolver, handle edge cases, understand the wire protocol.
- [Custom menu actions cookbook](05-custom-actions.md) — add per-project shortcuts to the widget menu.
- [Troubleshooting](07-troubleshooting.md) — recipes for the top ten issues you'll hit.
