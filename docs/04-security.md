# Security & threat model

## Secrets handling

- `api_key` and `signing_secret` are persisted through `spatie/laravel-settings`' `encrypted()` list — stored encrypted at rest with Laravel's `APP_KEY`.
- Both fields render as `password` inputs on the settings page, with revealable toggles.
- The signing secret is **never** sent to the browser. Only `signing_secret`-derived `userHash` values leave the server.
- `.env` precedence means ops teams can keep the secrets out of the database entirely by not touching the settings page — the plugin falls back to `env()` transparently.

## HMAC choice

- Algorithm: `HMAC-SHA256`. Matches Reqdesk's server-side verifier exactly.
- Canonicalisation: lowercase-trim the email before signing. Prevents downstream case-sensitivity bugs on either side.
- Payload format: `"{timestamp_seconds}.{email}"`. The dot separator is safe — emails cannot contain it after the `@` in our canonical form (RFC 5321 forbids consecutive dots; plugin's `strtolower+trim` does not change that).
- Output format: `"sha256=" + hex`. Same prefix scheme used by GitHub, Stripe and Reqdesk itself.

## Threat model

### What the design defends against

| Threat | Mitigation |
|--------|------------|
| An attacker modifies the `customer.email` before it reaches Reqdesk | Signature covers the email. Modified emails fail verification. |
| An attacker captures a signature and replays it an hour later | 10-minute TTL enforced server-side; refresh endpoint issues fresh signatures every ~5 minutes. |
| An attacker injects their own signed payload by calling the widget init directly | They'd need the signing secret to produce a valid signature. Secret never leaves the server. |
| A malicious page embeds the widget and uses a stolen API key | Reqdesk's per-key `AllowedOrigins` list blocks cross-origin requests. Set it in the Reqdesk dashboard. |
| The refresh endpoint is polled by a guest | `config('reqdesk-widget.identity.middleware')` defaults to `['web','auth']`. Guests get a 401. |
| A user tries to impersonate a teammate by editing the DOM | The email is signed on the server; any client-side edit breaks the signature. |

### What the design does not defend against

- A compromised `signing_secret` — treat it like a database password. Use Laravel's encrypted env if you ship it in repos.
- A compromised Laravel session — if the attacker has the session cookie, they *are* the user from the plugin's perspective. Use session-hijacking defences at the Laravel layer (encrypted cookies, CSRF, short TTLs).
- XSS on the host app — if an attacker can inject JS, they can call the widget with any payload. Hard-bound to your `Content-Security-Policy`; this plugin does not relax CSP for you.

## CSP / headers

The Blade component emits **two** inline pieces:

1. `<script src="{scriptUrl}" defer></script>` — remote JS.
2. `<script data-reqdesk-init>` — inline JS that calls `ReqdeskWidget.init(...)`.

If your app runs with a strict CSP:

- Add `script-src 'self' https://unpkg.com 'sha256-...'` or `'nonce-...'` to allow the inline init block. The simplest path is a nonce, which Laravel doesn't ship by default; the `Spatie\Csp` package integrates cleanly.
- Alternatively, self-host the widget script (`REQDESK_SCRIPT_URL=https://your.cdn/reqdesk-widget.js`) and pre-computed SRI.

The identity endpoint emits `Cache-Control: no-store` so intermediate caches never persist signed payloads.

## Rate limits

The plugin does not rate-limit the identity endpoint itself. Recommended addition in your app's router:

```php
Route::middleware(['throttle:60,1'])
    ->get(config('reqdesk-widget.identity.endpoint'), ...);
```

Or override by adding `throttle:60,1` to `REQDESK_IDENTITY_MIDDLEWARE`.

## Logging

- Missing `api_key` logs `Log::warning(...)` once per request.
- Resolver exceptions are `report()`ed (go through Laravel's exception handler).
- Sign failures inside the render builder are reported and downgrade to anonymous modes — **never** bubble into the render hook.
- The identity endpoint aborts with 401/503/500 depending on which constraint failed; no payload is echoed back to the caller.

Request/response payloads are *never* logged — Laravel's default logging does not include them unless you opted in elsewhere.

## Strict mode in staging

```ini
REQDESK_STRICT=true
```

Makes missing `REQDESK_API_KEY`, missing signing secret, or an invalid `REQDESK_USER_RESOLVER` throw `ReqdeskConfigurationException` at boot, before any Filament page renders. This catches misconfigured deploys **before** users hit them.

Leave it off in production so a transient config problem degrades to "widget not rendered" instead of "site down".

## Reporting vulnerabilities

See [`SECURITY.md`](../SECURITY.md).
