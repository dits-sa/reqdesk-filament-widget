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

## [1.5.1] - 2026-04-27

### Fixed

- **`script_url_default` bumped to widget 1.5.1.** The 1.5.0 panel chrome
  (header + footer) and the preferences picker hard-coded light-mode
  fallback colours, leaving the surfaces visually mismatched in dark mode
  and on hosts that override the panel via `--rqd-bg`/`--rqd-text`. 1.5.1
  drives every shell surface from the live theme tokens — header/footer
  read `var(--rqd-bg-secondary)`, the body reads `var(--rqd-bg)`, hairlines
  read `var(--rqd-border)`, and the brand accent comes from
  `var(--rqd-primary)` so workspace-coloured widgets see THEIR accent
  instead of Reqdesk cedar. The widget now also exposes the full Ledger
  token set (`--rqd-ink`, `--rqd-paper`, `--rqd-bone`, `--rqd-cedar`,
  `--rqd-hair`, `--rqd-text-muted`, font + tracking tokens) inside the
  shadow root so every sdk-react primitive (`LedgerSegmented`,
  `LedgerButton`, `LedgerInput`, …) inherits dark mode automatically.
- **Footer link target.** "Powered by Reqdesk" now links to
  `https://reqdesk.support` (was `reqdesk.com`) and is fronted by the
  inline three-bar `BrandMark` glyph used across the SignInScreen,
  HomeShell, and ledger eyebrows.
- **Preferences panel rebuilt.** Each row is a numbered Ledger entry
  (`01 / 02 / 03 …` mono index + Instrument Serif italic label) with a
  hairline border + 2 px cedar gutter. The accent-colour swatches use a
  2 px theme-coloured ring; the corner-position picker fills the active
  tile with `var(--rqd-bg-secondary)` and a primary-coloured edge dot.

## [1.5.0] - 2026-04-26

### Changed

- **`script_url_default` bumped to widget 1.5.0.** The 1.5 widget line collapses
  the four widget-local view duplicates (`SubmitTicketView`, `MyTicketsView`,
  `TicketDetailView`, `TrackTicketView`) onto the canonical `@reqdesk/sdk-react`
  implementations via an internal `ApiClient`/`StorageBackend` adapter — every
  surface the embedded widget renders now matches the browser-extension popup
  pixel-for-pixel. The `FloatingWidget` shell (header, footer, preferences
  panel, FAB chrome) is rebuilt against the locked Reqdesk Ledger primitives:
  Instrument Serif italic titles, mono `REQDESK · LEDGER` eyebrows, hairline
  ink borders, no shadows. The IIFE bundle moves from ~118 kB gzip → ~128 kB
  gzip (+10 kB) for the consolidated views; consumers pay it once per
  immutable version pin. New form features inherited from sdk-react: copyable
  ticket-number pill on the list and detail header, image-thumbnail previews
  on every previewable attachment, multi-select tag picker with inline tag
  creation, per-field 422 error breakdown, redesigned success "entry logged"
  card. No host-side code changes required.

## [1.4.0] - 2026-04-26

### Changed

- **`script_url_default` bumped to widget 1.4.1.** The 1.4.x widget line ships
  the `HostAdapter` contract — the same React tree the browser-extension popup
  consumes — and lays the groundwork for server-resolved auto-detect of the
  current page's project (`POST /widget/projects/resolve-by-url` in the
  Reqdesk API). The IIFE bundle grew from ~114 kB gzip → ~115 kB gzip
  (+1 kB) for the adapter wiring and is served `Cache-Control: immutable`
  from the CDN so consumers pay it once per version. Override chain is
  unchanged — `REQDESK_SCRIPT_URL` still pins the URL site-wide and 1.3.0
  remains live on the CDN via the retention list, so anyone who copy-pasted
  the previous default into their `.env` keeps working unchanged.

## [1.3.7] - 2026-04-25

### Changed

- **`script_url_default` bumped to widget 1.3.0.** The 1.3.0 widget IIFE bundle now boots the full React tree instead of the legacy stripped-down vanilla renderer, closing a real gap in `support-portal` mode where the new-ticket form only had title + description. Existing integrations get categories, tags, attachments, priority, signed identity, multi-project picker, and RTL out of the box on next page load. Bundle size grew (~114 kB gzip from the CDN) but is served `Cache-Control: immutable` so consumers pay it once per version. Override chain is unchanged — anyone pinning `REQDESK_SCRIPT_URL` keeps working, and 1.2.23 stays live on the CDN via the retention list.

## [1.3.6] - 2026-04-25

### Fixed

- **Settings save now type-coerces every value against the destination property's reflection type, not just enums.** v1.3.5 unwrapped enums but missed every other Filament/Livewire/JSON round-trip mismatch — `theme_z_index` accepted `9999.0` (float, not int) from `TextInput->numeric()` and threw `TypeError: Cannot assign float to property … of type int`. Same class of failure waited on `?string` fields receiving `""` instead of `null`, `bool` properties receiving `"1"`/`"0"`, and `array` properties receiving non-arrays.

  `ReqdeskSettings::coerceForProperty()` now reflects on the property's declared type and coerces accordingly: int (rounds floats and numeric strings, falls back to the property default for empty input), float (same), bool (handles `"1"`/`"0"`/`"true"`/`"false"`/numeric/null), string (stringifies ints/floats, returns null for `""` on `?string`, returns null for arrays on `?string`), array (forces `[]` on non-array). Enums unwrap first via `BackedEnum->value` / `UnitEnum->name`, recursing into arrays. 11 Pest tests lock the contract per shape.

- **`theme_z_index` field tightened.** The TextInput now declares `->numeric()->integer()->step(1)->minValue(0)->maxValue(2147483647)` so users can't even submit a non-integer.

## [1.3.5] - 2026-04-25

### Fixed

- **Saving the settings page no longer throws `TypeError: Cannot assign Reqdesk\Filament\Enums\ThemeMode to property … of type string`.** Filament's `Select` returns the `BackedEnum` case when its options are typed as an enum class, but the spatie-settings properties are typed `string` so spatie can persist them as JSON. `ReqdeskSettings::save()` now coerces every enum back to its scalar form (BackedEnum → `->value`, UnitEnum → `->name`, recursively into arrays) before assigning. Affects `theme_mode`, `position`, `display_mode`, `display_side`, `fab_icon`, `widget_mode`, and any future enum-backed Select.

### Changed

- **`widget_mode` moved from the Localization tab to the Layout tab** — it controls layout/structure, not language or copy. Existing values are unaffected.
- **`widget_mode` default flipped from `ticket-form` → `support-portal`.** The portal mode is the better landing experience for the average consumer (browse + search before filing). Existing installs are unchanged — defaults only apply to fresh installs and to fields whose persisted value is null.

## [1.3.4] - 2026-04-25

### Changed

- **`script_url_default` moved to `cdn.reqdesk.support` and bumped to widget 1.2.22.** The previous URL on `cdn.reqdesk.mod-sol-sa.com` is being retired in favour of the product-owned `reqdesk.support` apex. The 1.2.22 widget bundle also fixes a critical regression where the IIFE distribution shipped with `ofetch` externalised — every consumer of v1.2.20 / v1.2.21 saw `Uncaught ReferenceError: ofetch is not defined` and `window.ReqdeskWidget` never registered. Old pin URLs (`mod-sol-sa.com` host or 1.2.20 / 1.2.21 versions) keep working until the old domain is unwired in Dokploy, so consumers have time to upgrade. The override chain (`REQDESK_SCRIPT_URL` env → `reqdesk-widget.script_url` config → `ReqdeskWidgetSettings::$script_url`) is unchanged.

## [1.3.3] - 2026-04-25

### Changed

- **`script_url_default` now points at the Reqdesk-owned CDN** (`https://cdn.reqdesk.mod-sol-sa.com/widget/1.2.20/index.iife.js`) instead of `unpkg.com`. Consumers on the default start loading the widget from infrastructure Reqdesk controls end-to-end — no more executable JavaScript from a third-party origin in authenticated admin sessions. The explicit override chain (`REQDESK_SCRIPT_URL` env → `reqdesk-widget.script_url` config → `ReqdeskWidgetSettings::$script_url`) is unchanged, so anyone pinning a version or self-hosting the bundle is unaffected. See `docs/guides/cdn-dokploy.md` in the main Reqdesk repo for the CDN architecture + deployment runbook.

## [1.3.2] - 2026-04-24

### Fixed

- **Widget render hook now actually fires.** v1.2.0 changed the render-hook registration from `scopes: $panel::class` to `scopes: $panel->getId()` to stop cross-panel leakage. That stopped the leak — but it also silenced the hook entirely. Filament's `BasePage::getRenderHookScopes()` only passes the active page's own class (e.g. `App\Filament\Pages\Dashboard::class`) when matching render hooks. Neither `\Filament\Panel` (v1.1.0 behaviour) nor `'admin'` (v1.2.0 behaviour) is ever in that scope array, so the hook bucket was orphaned and the widget never rendered on any page. The widget is now registered in the global scope bucket (no `scopes:` argument) and gates on the current panel inside the closure via `Filament::getCurrentOrDefaultPanel()`, which also makes `onlyPanels()` work correctly when a single plugin instance might be booted on more than one panel. Any consumer on v1.2.0 or v1.2.1 who couldn't see the widget should now see it on upgrade.

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
