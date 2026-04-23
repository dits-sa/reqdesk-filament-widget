# Changelog

All notable changes to `dits-sa/reqdesk-filament-widget` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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
