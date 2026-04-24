# Changelog

All notable changes to `dits-sa/reqdesk-filament-widget` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

### Changed

### Deprecated

### Removed

### Fixed

### Security

## [1.3.1] - 2026-04-24

Patch release. No consumer-visible changes — internal pruning only.

## [1.3.0] - 2026-04-24

### Removed

- **`project_id` field — declared but never used.** The `ReqdeskWidgetSettings::$project_id` property, the `project_id` form input, the `REQDESK_PROJECT_ID` env / `reqdesk-widget.project_id` config key, the `fields.project_id` English + Arabic translation blocks, and the migration default are all gone. `WidgetConfigBuilder::build()` never read this value in any v1.x release, so the widget init payload never contained a project id; `rqd_pk_*` and `rqd_ws_*` API keys already encode project scope server-side, so there was nothing for the field to do.

### Fixed

- **Connection tab now shows env defaults on first load.** `ReqdeskSettings::mount()` falls back to `config('reqdesk-widget.*')` for any null or empty settings value, matching the runtime fallback that `WidgetConfigBuilder::resolveSettingOrConfig()` already applied to the widget payload. Admins no longer have to paste `.env` values into the form manually before Save works — the form mirrors what the widget is already using. `save()` behaviour is unchanged; persisted values take precedence on subsequent loads.

## [1.2.1] - 2026-04-24

### Fixed

- **Settings hydration no longer throws on `ReqdeskWidgetSettings::$actions`.**
  The property's `@var` shape was `list<array{id:string,label_en:string,...}>`
  — valid PHPStan/Psalm syntax but not parseable by
  `phpdocumentor/type-resolver`, which is what `spatie/laravel-settings`
  uses internally. Any code path that fully hydrates the settings class
  (notably `ReqdeskClient::ping()`, every save of the settings page, and
  the `reqdesk-widget:doctor` command without `--skip-ping`) was throwing
  `RuntimeException: Unexpected token "", expected '>'`. The plain `@var`
  is dropped in favour of the native PHP `array` type so spatie skips the
  nested cast entirely; the full shape is preserved in `@phpstan-var`
  for IDE/static-analysis fidelity. No runtime behaviour change — the
  property was already persisted as an untyped JSON blob.

## [1.2.0] - 2026-04-24

### Added

- Fluent `navigationGroup()`, `navigationSort()` and `navigationIcon()`
  builders on `ReqdeskWidgetPlugin`. The settings page now honours whatever
  the host panel configures, falling back to the previous translated group
  label and `heroicon-o-lifebuoy` icon when unset. Group accepts
  `string|UnitEnum|null` to cover both bare-case enums (the common Filament
  pattern) and backed enums.
- Fluent `authorize(string|Closure|null)` builder on `ReqdeskWidgetPlugin`
  plus a `canAccess()` override on `ReqdeskSettings`. When set the settings
  page is gated by the given Gate ability or closure; when unset the page
  stays accessible (no BC break).
- README sections covering navigation, access control, multi-panel usage
  and custom-guard identity middleware.

### Fixed

- **Multi-panel render-hook leakage.** `FilamentView::registerRenderHook` is
  now scoped to `$panel->getId()` instead of `$panel::class`, so the widget
  injects only into the panels that actually register the plugin.
- **`make()` returns a fresh instance.** Previously deferred to the
  container, which would have leaked fluent state between panels if anyone
  bound `ReqdeskWidgetPlugin` as a singleton. `new static()` makes the
  always-fresh semantics explicit.
- **PostgreSQL migration abort.** The settings migration now opts out of
  Laravel's wrapping transaction (`public bool $withinTransaction = false`)
  and guards every `add()` with `SettingsMigrator::exists()`, fixing the
  `SQLSTATE[25P02] current transaction is aborted` failure when running
  `php artisan migrate --path=...` on PG. The migration is also idempotent
  across re-runs.
- **Install command no longer skips migrations in CI.** Switched from
  `askToRunMigrations()` to `runsMigrations()`, so `--no-interaction`
  deploys populate the settings table automatically.
- **Fresh-install boot order.** `ReqdeskClient` and `WidgetConfigBuilder`
  now resolve `ReqdeskWidgetSettings` lazily inside methods instead of
  receiving it as a constructor argument. The plugin can register before
  migrations have run without throwing at container-resolution time.
- **`inject_for_guests` env fallback.** `WidgetConfigBuilder::build()` now
  reads `REQDESK_INJECT_GUESTS` when the settings row is absent, matching
  every other field's behaviour.

## [1.1.0] - 2026-04-23

### Added

- Filament v5 support. The plugin now resolves and runs on host apps using
  `filament/filament:^5.0` in addition to the existing v4 support. Every
  Filament API the plugin uses (`Plugin` contract, `Panel`, render hooks,
  `Schema` / `Schemas\Components\*`, `Forms\Components\*`) is source-
  compatible between v4 and v5.
- CI matrix now exercises PHP 8.2 / 8.3 / 8.4 × Laravel 11 / 12 × Filament
  4 / 5 (excluding the Laravel 11 × Filament 5 combination, which is
  prevented by Filament's own Laravel 11.28+ floor).
- New `reqdesk-widget.install_skip_signing` config key (reads
  `REQDESK_INSTALL_SKIP_SIGNING`) so the install-time signing-secret bypass
  survives `config:cache`.

### Changed

- Minimum `illuminate/contracts` raised to `^11.28` to match Filament v5's
  Laravel floor. Consumers on Laravel 11.27 or earlier stay on the
  Filament v4 lane.
- `DefaultUserResolver` now reads user attributes via property access only
  (Eloquent's `__get` still routes through `getAttribute()`). Removes the
  redundant `method_exists()` probe that PHPStan level 8 flagged as always
  true on `Authenticatable`.
- Install-command method updated from the deprecated `publishConfig()` to
  `publishConfigFile()`, matching `spatie/laravel-package-tools` 1.16+.

### Fixed

- `phpstan.neon` no longer references the removed
  `checkMissingIterableValueType` parameter (incompatible with PHPStan 2+).
  All 14 pre-existing level-8 findings that the broken config was hiding
  are now resolved.

## [1.0.0] - 2026-04-23

### Changed

- First stable release. The public surface (plugin class, settings fields,
  resolver contract, HMAC signing contract, install/doctor commands) is now
  covered by Semantic Versioning — breaking changes will require a major
  version bump. No functional changes from `0.1.0`.

## [0.1.0] - 2026-04-23

### Added

- Initial release of the Reqdesk Filament v4 plugin.
- `ReqdeskWidgetPlugin` Filament plugin with `BODY_END` render-hook injection.
- `ReqdeskSettings` Filament page covering every `ReqdeskWidgetConfig` key,
  split across Connection, Appearance, Layout, Localization, Identity,
  Actions and Advanced tabs.
- `spatie/laravel-settings` backed persistence with encrypted `api_key` and
  `signing_secret` columns and an env-driven first-load default for every
  field.
- `WidgetUserResolver` contract with `DefaultUserResolver` reading
  `$user->email` and `$user->name` — authenticated Laravel users get a signed
  identity injected automatically; swap the resolver per project without
  touching plugin code.
- `IdentitySigner` implementing the HMAC-SHA256 contract
  (`sha256=` + hex over `"{ts}.{canonicalEmail}"`).
- `SignIdentityController` for the widget's `refreshIdentity` callback,
  returning `Cache-Control: no-store` signed payloads.
- `reqdesk-widget:install` and `reqdesk-widget:doctor` commands that fail
  fast on missing `REQDESK_API_KEY`, missing signing secret (unless
  `--no-signed-identity` is passed) or an invalid `REQDESK_USER_RESOLVER`.
- Strict-mode runtime validation (`REQDESK_STRICT=true`) that promotes
  configuration warnings to `ReqdeskConfigurationException`.
- English and Arabic translation bundles.
- Comprehensive documentation set under `docs/`: quickstart, configuration
  reference, identity deep-dive, security/threat model, custom-actions
  cookbook, multi-panel patterns, troubleshooting and upgrade guide. Root-
  level `CONTRIBUTING.md` and `SECURITY.md`.
