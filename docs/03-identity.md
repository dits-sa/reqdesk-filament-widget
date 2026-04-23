# Signed host-app identity

How the plugin turns Laravel's authenticated user into a trusted Reqdesk identity — and how to customise the flow when your user model isn't a plain email/name pair.

## The contract

Reqdesk trusts three fields the widget presents to it:

```json
{
  "email": "admin@example.com",
  "userHash": "sha256=3f2a1b...",
  "userHashTimestamp": 1735000000
}
```

Where `userHash` is:

```
"sha256=" + hmac_sha256( timestamp_seconds + "." + strtolower(trim(email)), signing_secret ).hex()
```

The Reqdesk backend verifies the signature and timestamp (fresh within 10 minutes by default; rotation-window aware) before accepting the identity. No secret ever leaves your server.

## The default flow

1. A Laravel-authenticated user lands on any Filament page.
2. `ReqdeskWidgetPlugin::boot()` registers a render hook that emits `<x-reqdesk::widget />` at `PanelsRenderHook::BODY_END`.
3. The `Widget` Blade component:
   - Grabs `auth()->user()`.
   - Runs it through the configured `WidgetUserResolver` (default: `$user->email` + `$user->name`).
   - Signs the canonical email with `IdentitySigner::sign(...)`.
   - Serialises the full init payload as JSON.
4. The widget boots and uses the signature on every Reqdesk API call as `X-Widget-User-Signature` + `X-Widget-User-Timestamp`.
5. When the signature is older than ~5 minutes, the widget hits `/reqdesk/widget/identity` (handled by `SignIdentityController`) to get a fresh one. The route is gated behind your `web,auth` middleware stack — guests can't poll it.

Net effect: an admin who's already logged into Filament sees their Reqdesk ticket history instantly. No second login, no email prompt.

## Swapping the resolver

The default resolver works when `Authenticatable::email` and `::name` are what you want to send. When they aren't — aliases, tenancy-scoped identity, proxy emails — implement your own:

```php
namespace App\Reqdesk;

use Illuminate\Contracts\Auth\Authenticatable;
use Reqdesk\Filament\Contracts\WidgetUserResolver;

final class TenantUserResolver implements WidgetUserResolver
{
    public function resolve(?Authenticatable $user): ?array
    {
        if ($user === null) {
            return null;
        }

        $email = $user->support_email ?? $user->email;
        if (! $email) {
            return null;
        }

        return [
            'email' => $email,
            'name' => $user->display_name,
            'externalId' => sprintf('%s.%s', $user->tenant_id, $user->id),
        ];
    }
}
```

Point the plugin at it via:

```ini
REQDESK_USER_RESOLVER=App\Reqdesk\TenantUserResolver
```

…or through the settings page under **Identity → User resolver**. Validation rejects classes that don't exist or don't implement the contract before save.

## Returning `null`

Return `null` whenever you don't want a signed identity for this user:

- No resolvable email.
- The user is impersonating someone and you want to fall back to the email prompt.
- A compliance flag on the tenant disallows external identity leakage.

When the resolver returns `null`, the widget uses `auth_mode_when_anonymous` (default `['email']`) and shows the email-prompt flow instead.

## Two-mode auth configuration

| Case | Modes applied |
|------|---------------|
| Laravel user **and** signing secret present **and** resolver returns identity | `auth_mode_when_signed` (default `['signed']`) |
| Any of the above missing | `auth_mode_when_anonymous` (default `['email']`) |

Both are `CheckboxList` fields on the settings page — you can allow multiple modes simultaneously. For example, a kiosk panel could send `['signed', 'email']` so an authenticated user with a shared workstation can still submit on behalf of a colleague.

## Freshness & refresh

- Signatures are minted at page-render time.
- The widget considers them stale after ~5 minutes (`ofetch-client.ts:96-120`).
- Stale signatures trigger a call to `/reqdesk/widget/identity`, which re-signs with the current session and returns `Cache-Control: no-store`.
- The refresh dedupes: N parallel widget calls during a stale window trigger **one** endpoint hit.
- If the refresh fails (401, network, etc.) the widget falls back to the cached signature. Reqdesk rejects it with `INVALID_SIGNATURE`, which surfaces as a toast.

## Secret rotation

Reqdesk supports overlap-window rotation for the signing secret (default 24 h). To rotate:

1. Generate a new secret in the Reqdesk dashboard; the old one stays valid during the overlap.
2. Update `REQDESK_SIGNING_SECRET` (or the settings-page value) and deploy.
3. After the overlap window closes, old tokens start failing — refresh picks up new ones automatically.

There is no downtime; widgets already on-page just get one stale-refresh cycle early.

## Strict mode

For staging and CI, set `REQDESK_STRICT=true`. Missing `REQDESK_API_KEY`, missing signing secret, or an invalid `user_resolver` class will throw `ReqdeskConfigurationException` **at boot** — before any Filament page renders. Production should leave this off so missing config degrades to "widget not rendered" rather than "site is down".

## Testing the flow locally

```bash
php artisan reqdesk-widget:doctor
```

…reports every check. To eyeball the actual payload the browser receives, tail the Network tab on a Filament page and look for:

- The embedded `<script data-reqdesk-init>` tag in the page HTML.
- A `GET /reqdesk/widget/identity` request after the widget is open for ~5 minutes (or immediately after `ReqdeskWidget.init`).
- `X-Widget-User-Signature` + `X-Widget-User-Timestamp` on every `POST /api/v1/projects/.../tickets` request.

## The wire format in full

```
┌────────────────────────────────────────────────┐
│  Laravel session                               │
│    user = User { id, email, name }             │
│                                                │
│    ┌──────────────────────────────────────┐    │
│    │ WidgetUserResolver::resolve($user)   │    │
│    │   -> { email, name, externalId }     │    │
│    └──────────────────────────────────────┘    │
│                                                │
│    ┌──────────────────────────────────────┐    │
│    │ IdentitySigner::sign($email, ts,     │    │
│    │                       $secret)       │    │
│    │   -> { email, userHash, ts }         │    │
│    └──────────────────────────────────────┘    │
│                        │                       │
└────────────────────────┼───────────────────────┘
                         ▼
     init({ customer: { email, name, externalId,
                        userHash, userHashTimestamp,
                        refreshIdentity: () => fetch(
                          '/reqdesk/widget/identity'
                        ) } })
                         │
                         ▼
   POST /api/v1/projects/.../tickets
     X-Api-Key:               rqd_pk_...
     X-Widget-User-Signature: sha256=...
     X-Widget-User-Timestamp: 1735000000
```
