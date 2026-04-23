# Contributing

Thanks for thinking about contributing. This plugin's reason-to-exist is to make the Reqdesk widget feel native inside Filament, so patches that make that story sharper are very welcome.

## Local development

```bash
cd packages/filament
composer install
composer qa            # pint + phpstan + pest — must pass before a PR
```

`composer qa` is the single gate that has to be green before we merge. CI runs the same command across PHP 8.2 / 8.3 / 8.4 × Laravel 11 / 12.

### Sandbox

To see changes in a real Filament panel without publishing, the recommended flow is a throwaway Laravel app next to the package:

```bash
mkdir -p sandbox && cd sandbox
composer create-project laravel/laravel . ^12
composer require filament/filament:^4
composer config repositories.local path ../ '--'
composer require dits-sa/reqdesk-filament-widget:@dev
php artisan reqdesk-widget:install
php artisan serve
```

The sandbox is `.gitignore`d — every save in `packages/filament/src/**` is live because the path repo is symlinked.

## Coding standards

- PHP: **Laravel Pint** with the `laravel` preset plus `declare_strict_types=true`. Run `composer lint`.
- Static analysis: **PHPStan level 8** with Larastan. Run `composer stan`. No baseline — fail on any new error.
- Tests: **Pest 3** on Orchestra Testbench. Aim for ≥85% coverage on `src/Services`, `src/Http`, `src/Settings`.

## Commit style

- No AI co-author tags.
- Natural commit messages focusing on **why**, not what — the diff shows the what.
- Prefer small commits. One meaningful change per commit.

## Pull requests

1. Fork + branch off `main`.
2. Make your change with tests.
3. Update `CHANGELOG.md`'s `[Unreleased]` section with a Keep-a-Changelog entry.
4. If you touch documentation, link the change from the README's table of contents.
5. Open a PR. CI must be green.

## Documentation changes

- User-facing docs live in `docs/` — keep them task-oriented, not reference-dumps.
- The README is the entry point — keep it **short** and push detail into `docs/`.
- Don't duplicate across files. Link from one canonical spot.

## Release process

Maintainers only — documented in the root-level plan (`plans/`). In short:

1. `composer qa` on all three PHP versions.
2. Smoke test in a sandbox app.
3. Move `[Unreleased]` → `## [x.y.z] - YYYY-MM-DD` in `CHANGELOG.md`.
4. `git commit -am "Release vx.y.z"`; `git tag -a vx.y.z`; push.
5. `release.yml` handles the GitHub Release + Packagist notify.
