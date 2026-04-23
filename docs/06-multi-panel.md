# Multi-panel & multi-tenant patterns

When your Filament app has more than one panel or serves more than one tenant, the plugin gives you three composition points. Pick what you need.

## Per-panel registration

Each `PanelProvider` registers the plugin independently:

```php
// AdminPanelProvider.php
return $panel->plugin(ReqdeskWidgetPlugin::make());

// AgentPanelProvider.php — widget is NOT injected here
return $panel;

// CustomerPortalProvider.php — different settings page visibility
return $panel->plugin(
    ReqdeskWidgetPlugin::make()->registerSettingsPage(false)
);
```

The render hook is scoped to `$panel::class` via `FilamentView::registerRenderHook(..., scopes: $panel::class)`, so registering on one panel never leaks the script tag into another.

## Allow-listing specific panel IDs

When the same plugin instance is reused (e.g. via a shared base provider), limit injection to a named panel:

```php
ReqdeskWidgetPlugin::make()
    ->onlyPanels(['admin', 'agent']);
```

Equivalent env: `REQDESK_PANELS=admin,agent`. The plugin's runtime check is:

1. `onlyPanels()` list (highest priority), then
2. settings-page `panels` field, then
3. `REQDESK_PANELS`, then
4. *(empty = all panels)*.

## Alternate render hooks

The default is `PanelsRenderHook::BODY_END`. Move it with:

```php
ReqdeskWidgetPlugin::make()
    ->renderHook(PanelsRenderHook::BODY_START);
```

Useful when another plugin claims `BODY_END` or when your layout scripts depend on the widget being available earlier.

## Tenancy

### Same Reqdesk project for all tenants

Nothing to do. The signing secret is shared. The resolver returns each tenant's user's email as-is.

### Different Reqdesk project per tenant

Two paths:

**Path A — swap config at runtime per tenant**

In your tenant bootstrapping middleware, override the config values for this request:

```php
// app/Http/Middleware/TenantBootstrap.php
public function handle(Request $request, Closure $next)
{
    $tenant = app('current-tenant');

    config([
        'reqdesk-widget.api_key' => $tenant->reqdesk_api_key,
        'reqdesk-widget.signing_secret' => $tenant->reqdesk_signing_secret,
    ]);

    return $next($request);
}
```

This keeps the spatie/laravel-settings store as the *global* default but lets a per-tenant value win for this request.

**Path B — per-panel plugin instance**

Register a subclass of `ReqdeskWidgetPlugin` per tenant panel, reading from a tenant-scoped config:

```php
final class TenantReqdeskPlugin extends ReqdeskWidgetPlugin
{
    public function boot(Panel $panel): void
    {
        $tenant = app('current-tenant');
        config(['reqdesk-widget.api_key' => $tenant->reqdesk_api_key]);
        config(['reqdesk-widget.signing_secret' => $tenant->reqdesk_signing_secret]);

        parent::boot($panel);
    }
}
```

### Tenant-aware resolver

The cleanest option when the *email format* changes per tenant:

```php
final class TenantAwareResolver implements WidgetUserResolver
{
    public function resolve(?Authenticatable $user): ?array
    {
        if ($user === null) return null;

        $tenant = app('current-tenant');

        return [
            'email' => sprintf('%s+%s@reqdesk-alias.com', $user->id, $tenant->slug),
            'name' => "{$user->name} ({$tenant->name})",
            'externalId' => "{$tenant->id}.{$user->id}",
        ];
    }
}
```

Set `REQDESK_USER_RESOLVER=App\Reqdesk\TenantAwareResolver` and every tenant's users are emailed to Reqdesk under a per-tenant namespace.

## Multiple widget instances on one page

Not currently supported. The widget registers a single global `ReqdeskWidget` — running two instances (e.g. one for customers, one for internal agents) would require two IIFE globals. If this is critical for your use case, open an issue.

## Settings page visibility

By default the Reqdesk settings page appears in every panel where the plugin is registered. Hide it in operator-only panels:

```php
ReqdeskWidgetPlugin::make()->registerSettingsPage(false);
```

The settings are still read from the same spatie/laravel-settings record — you just don't expose the UI to end users in that panel.

## Filament Shield / policies

If you use `bezhansalleh/filament-shield` and want to gate the settings page behind a permission:

```php
// in ReqdeskSettings::class via subclass
public static function canAccess(): bool
{
    return auth()->user()?->can('manage-reqdesk-widget') ?? false;
}
```

You'll need to extend `Reqdesk\Filament\Filament\Pages\ReqdeskSettings` and register the subclass on your panel via `->pages([YourSubclass::class])`, then `->plugin(ReqdeskWidgetPlugin::make()->registerSettingsPage(false))`.
