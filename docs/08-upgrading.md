# Upgrade guide

This guide covers upgrades between major versions. Minor and patch releases are always backwards-compatible — check `CHANGELOG.md` for their notable additions.

Versioning policy:

- **Patch (x.y.Z)** — bugfixes, no public-surface changes.
- **Minor (x.Y.0)** — new settings fields, new plugin setters, new Blade components, new translation keys. Always additive.
- **Major (X.0.0)** — removed or renamed public methods, removed settings fields, changed HMAC algorithm, or changed migration structure non-additively.

## Upgrading in general

```bash
composer update dits-sa/reqdesk-filament-widget
php artisan migrate         # only if a new migration ships
php artisan reqdesk-widget:doctor
```

The doctor command is the safety net — run it after every upgrade.

## Breaking-change protocol

When we ship a breaking change:

1. The upgrade guide adds a section `## From <previous> to <current>` with migration steps.
2. The previous major stays supported for security fixes for six months after the new major's GA.
3. We write a `UPGRADING-<previous>-to-<current>.md` in the repo root for complex transitions.

## From 1.0 to 1.1 — Filament v5 support

`1.1.0` adds support for Filament v5 alongside the existing v4 support. No
code changes are required on the host-app side; the widening is in
`composer.json`.

If your host app is still on Filament v4 and Laravel 11.28+:

```bash
composer require dits-sa/reqdesk-filament-widget:^1.1
```

If you are upgrading the host app from Filament v4 to v5, bump both in one
step:

```bash
composer require dits-sa/reqdesk-filament-widget:^1.1 filament/filament:^5.0 -W
php artisan reqdesk-widget:doctor
```

**One caveat:** `1.1.0` raises the minimum `illuminate/contracts` to
`^11.28`, mirroring Filament v5's own Laravel floor. If your app is on
Laravel 11.0 – 11.27, stay on `1.0.x` or upgrade Laravel first.
