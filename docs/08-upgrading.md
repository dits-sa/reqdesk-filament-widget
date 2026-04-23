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

## From 0.x to 1.0 (placeholder)

*No 1.0 release yet. This section will be filled in when we cut it.*

Things likely to move at 1.0:

- The `user_resolver` settings field may become a typed enum of "built-in resolvers" with a separate overflow field for custom FQCNs.
- Auth modes may switch from comma-separated strings to native enum arrays on the config file (settings page is already enum-backed).
- The render hook may move to a named constant exported by the plugin to guarantee parity across Filament versions.

Nothing listed above is committed to — it is planning context only.
