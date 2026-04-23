# Configuration reference

Every option the plugin exposes, in one place. The widget's own JS config surface is documented upstream — this page focuses on how each option is wired in Laravel.

## Resolution order

For every field, values are resolved in this order (highest wins):

1. **Runtime plugin setter** — e.g. `ReqdeskWidgetPlugin::make()->onlyPanels(['admin'])`.
2. **Filament settings page** — persisted via `spatie/laravel-settings` into `reqdesk_widget_settings`.
3. **Environment variable** — `REQDESK_*` in your `.env`.
4. **Built-in default** — hardcoded in `config/reqdesk-widget.php` / `ReqdeskWidgetSettings`.

This means a fresh deploy with only `.env` populated already works; admins can override any value through the Filament UI without code changes; and code can still pin per-panel behaviour through the plugin's fluent setters.

Secrets (`api_key`, `signing_secret`) are encrypted at rest by spatie/laravel-settings' `encrypted()` list.

## Connection

| Setting | Env | Default | Description |
|---------|-----|---------|-------------|
| `api_key` | `REQDESK_API_KEY` | — | **Required.** `rqd_pk_...` (project) or `rqd_ws_...` (workspace) key. Exposed in the browser. |
| `api_url` | `REQDESK_API_URL` | `https://app.reqdesk.com` | Your Reqdesk backend. Override for self-hosted deployments. |
| `signing_secret` | `REQDESK_SIGNING_SECRET` | — | HMAC-SHA256 secret for signed host-app identity. **Server-side only.** |
| `project_id` | `REQDESK_PROJECT_ID` | *(derived)* | Optional. Usually inferable from the API key prefix. |

## Identity

| Setting | Env | Default | Description |
|---------|-----|---------|-------------|
| `auth_mode_when_signed` | `REQDESK_AUTH_MODE_SIGNED` | `signed` | Comma list applied when Laravel's guard returns a user. Any of `signed,email,sso`. |
| `auth_mode_when_anonymous` | `REQDESK_AUTH_MODE_ANON` | `email` | Comma list applied to guest visitors. |
| `user_resolver` | `REQDESK_USER_RESOLVER` | `Reqdesk\Filament\Support\DefaultUserResolver` | FQCN implementing `WidgetUserResolver`. |

Deep dive: [`03-identity.md`](03-identity.md).

## Appearance

| Setting | Env | Default |
|---------|-----|---------|
| `theme_primary_color` | `REQDESK_THEME_PRIMARY` | `#0F5E56` |
| `theme_mode` | `REQDESK_THEME_MODE` | `auto` (`light` / `dark` / `auto`) |
| `theme_border_radius` | `REQDESK_THEME_RADIUS` | `6px` |
| `theme_font_family` | `REQDESK_THEME_FONT` | — |
| `theme_z_index` | `REQDESK_THEME_ZINDEX` | `9999` |
| `theme_logo` | `REQDESK_LOGO_URL` | — |
| `theme_brand_name` | `REQDESK_BRAND_NAME` | — |
| `theme_hide_branding` | `REQDESK_HIDE_BRANDING` | `false` |

## Layout

| Setting | Env | Default | Description |
|---------|-----|---------|-------------|
| `position` | `REQDESK_POSITION` | `bottom-end` | FAB corner. Logical — mirrors in RTL. `bottom-end`, `bottom-start`, `top-end`, `top-start`. |
| `display_mode` | `REQDESK_DISPLAY_MODE` | `popover` | `popover`, `side-sheet`, `bottom-sheet`. |
| `display_side` | `REQDESK_DISPLAY_SIDE` | `end` | Side-sheet edge. |
| `display_width` | `REQDESK_DISPLAY_WIDTH` | `420px` | Side-sheet width (CSS length). |
| `display_height` | `REQDESK_DISPLAY_HEIGHT` | `55vh` | Bottom-sheet height (CSS length). |
| `display_dismiss_on_backdrop` | `REQDESK_DISPLAY_DISMISS` | `true` | |
| `hide_fab` | `REQDESK_HIDE_FAB` | `false` | Suppress the FAB entirely — drive via `Reqdesk.open()` yourself. |
| `hide_display_mode_picker` | `REQDESK_HIDE_PICKER` | `false` | Hide the Preferences display-mode dropdown. |
| `fab_icon` | `REQDESK_FAB_ICON` | `help` | `help`, `chat`, or a raw SVG path `d`. |

## Localization

| Setting | Env | Default |
|---------|-----|---------|
| `default_language` | `REQDESK_LANGUAGE` | `en` (`en` / `ar`) |
| `widget_mode` | `REQDESK_WIDGET_MODE` | `ticket-form` (or `support-portal`) |
| `default_category` | `REQDESK_DEFAULT_CATEGORY` | — |
| `translations` | *(none)* | `[]` | KeyValue overrides for any i18n key. |

## Custom actions

Managed exclusively through the settings page's Repeater. Each row maps to a widget `MenuActionInput`:

| Field | Widget key | Notes |
|-------|-----------|-------|
| ID | `id` | `[a-z0-9][a-z0-9-_]*` |
| Label (EN) | `label.en` | required |
| Label (AR) | `label.ar` | optional; switches to bilingual format when set |
| Description | `description` | |
| Menu section | `section` | `top` / `bottom` |
| Icon (SVG path d) | `icon` | |
| Trigger kind | `trigger.kind` | `url`, `custom-event`, `call-global` |
| Trigger value | `trigger.href` / `.name` / `.path` | |
| Trigger target | `trigger.target` | URL only — `_blank` etc. |

Cookbook: [`05-custom-actions.md`](05-custom-actions.md).

## Advanced

| Setting | Env | Default | Description |
|---------|-----|---------|-------------|
| `enabled` | `REQDESK_ENABLED` | `true` | Kill switch. Render hook produces no output when false. |
| `inject_for_guests` | `REQDESK_INJECT_GUESTS` | `false` | Also render widget for unauthenticated visitors (e.g. marketing pages). |
| `panels` | `REQDESK_PANELS` | `[]` (all) | Comma list of panel IDs to limit injection to. |
| `script_url` | `REQDESK_SCRIPT_URL` | pinned unpkg URL | Override when self-hosting `@reqdesk/widget`. |
| *(config only)* | `REQDESK_STRICT` | `false` | Strict-mode boot validation — throws `ReqdeskConfigurationException` if required config is missing. |

## Identity endpoint

Only read from `config/reqdesk-widget.php` (not persisted as Settings) because it changes routing:

```php
'identity' => [
    'endpoint'   => env('REQDESK_IDENTITY_ENDPOINT', '/reqdesk/widget/identity'),
    'middleware' => ['web', 'auth'],
],
```

Change the middleware stack through `REQDESK_IDENTITY_MIDDLEWARE=web,auth,verified` to gate on email verification, 2FA, etc. The route's name is always `reqdesk.widget.identify` regardless of path.

## Plugin-only setters

Some options live on the `ReqdeskWidgetPlugin` class because they configure the Filament boot, not the widget payload:

```php
ReqdeskWidgetPlugin::make()
    ->injectScript(false)                             // disable script injection on this panel
    ->renderHook(PanelsRenderHook::BODY_START)        // move the injection point
    ->registerSettingsPage(false)                     // hide the Reqdesk settings page
    ->onlyPanels(['admin']);                          // limit to specific panel IDs
```

All of these are panel-local — register the plugin on multiple panels with different setters to get per-panel behaviour.
