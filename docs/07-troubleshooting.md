# Troubleshooting

A grab bag of the most common things that go wrong, with exact fixes.

## The widget does not appear

Run `php artisan reqdesk-widget:doctor`. It will tell you which of these is the cause.

### 1. `REQDESK_API_KEY` is missing

**Symptom:** no `<script>` tag in the page source. `Log::warning("REQDESK_API_KEY is not configured...")` in your laravel log.

**Fix:** add it to `.env` and `php artisan config:clear`.

### 2. The plugin is not registered

**Symptom:** settings page is missing from the nav. `php artisan reqdesk-widget:doctor` passes but the widget doesn't render.

**Fix:** add `->plugin(ReqdeskWidgetPlugin::make())` to your `PanelProvider::panel()`.

### 3. The panel ID is not in the allow-list

**Symptom:** widget works on one panel, not another.

**Fix:** either set `REQDESK_PANELS=` to empty (all panels), remove the `onlyPanels()` call, or add the missing panel ID.

### 4. You're a guest and guest injection is off

**Symptom:** widget appears for logged-in users but not on login/marketing pages.

**Fix:** set `REQDESK_INJECT_GUESTS=true` **and** make sure your login route is served by a panel that has the plugin registered. Filament's auth routes are typically under the panel's path, so this usually "just works".

## I see the widget but it asks for an email

Either the signing secret is missing or the resolver returned `null`.

### 1. `REQDESK_SIGNING_SECRET` is missing

**Fix:** add it to `.env` and `php artisan config:clear`.

### 2. Your resolver returned `null`

Resolvers return `null` when they can't produce an email. Check your resolver logic with:

```php
$resolver = app(\Reqdesk\Filament\Contracts\WidgetUserResolver::class);
dump($resolver->resolve(auth()->user()));
```

A common culprit is using a custom `User` model whose `email` column is named differently — override the resolver.

### 3. The signing secret on the server doesn't match Reqdesk's

**Symptom:** widget shows signed form, but submitting tickets fails with `INVALID_SIGNATURE` in the browser console.

**Fix:** re-copy the signing secret from the Reqdesk dashboard. If you've just rotated, the overlap window may have expired — generate a new one on both sides.

## The FAB is in the wrong place

### 1. In an RTL layout the FAB is on the wrong side

**Explanation:** `position` is logical. `bottom-end` is right in LTR, left in RTL — which is correct.

**Fix:** if you want a physical side, use the explicit CSS side classes by overriding the theme or pick `bottom-start` instead.

### 2. The FAB overlaps your own floating UI

**Fix:** bump `theme_z_index` up (default 9999) or move the position. Alternatively, hide the FAB entirely with `REQDESK_HIDE_FAB=true` and add your own trigger using `window.ReqdeskWidget.openMenu()`.

## `reqdesk-widget:install` exits with an error

The installer fails fast when required env vars are missing. The error message names the variable.

- Missing `REQDESK_API_KEY` → **always fatal**. No bypass.
- Missing `REQDESK_SIGNING_SECRET` → fatal unless `REQDESK_INSTALL_SKIP_SIGNING=true`.
- `REQDESK_USER_RESOLVER` pointing to a class that doesn't implement `WidgetUserResolver` → fatal.

## The settings page is empty / values don't save

### 1. Migration hasn't run

```bash
php artisan migrate
```

Check that a row with `group = reqdesk_widget` exists in the `settings` table.

### 2. Spatie settings cache is stale

```bash
php artisan cache:clear
```

### 3. The field uses an enum that your PHP version doesn't support

All enums are PHP 8.2+. Confirm with `php --version`.

## Strict mode keeps crashing in local dev

**Symptom:** `ReqdeskConfigurationException` thrown on every request.

**Fix:** `REQDESK_STRICT=false` in `.env.local` / `.env.testing`. Strict mode is intended for staging/CI.

## `composer test` fails with "driver not found"

**Symptom:** `could not find driver (SQL: ...)` during tests.

**Fix:** install `ext-sqlite3` on your PHP install or override `DB_CONNECTION` to a local MySQL/Postgres. The bundled `phpunit.xml.dist` uses SQLite in-memory by default.

## Widget works locally, broken in prod

### 1. `config:cache` has the wrong values

`config:cache` snapshots `.env` at the time of cache. If you changed env vars after caching, run:

```bash
php artisan config:clear && php artisan config:cache
```

### 2. Secrets are leaking to the browser

Inspect the page source for the `<script data-reqdesk-init>` block. It should contain:

- `apiKey`: *public* key (`rqd_pk_...`)
- `customer.userHash`: the signed hash
- `customer.userHashTimestamp`: a unix timestamp

It should **NOT** contain anything that looks like your signing secret. If it does, file a security report immediately (see `SECURITY.md`).

### 3. CSP blocks the inline init block

Check your browser's CSP console warnings. See [`04-security.md`](04-security.md) for nonce / hash approaches.

## Still stuck

1. Re-run `php artisan reqdesk-widget:doctor --skip-ping` and paste the output into an issue.
2. Include PHP version, Laravel version, Filament version, and plugin version (`composer show dits-sa/reqdesk-filament-widget`).
3. Open an issue at https://github.com/dits-sa/reqdesk-filament-widget/issues.
