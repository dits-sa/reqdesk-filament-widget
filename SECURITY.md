# Security policy

## Supported versions

Until the first `1.0.0` release, only the latest published `0.x` version receives security patches.

After `1.0.0`, the current major and the previous major will both receive security fixes for six months after the new major's GA.

## Reporting a vulnerability

Please **do not** open a public GitHub issue for security reports.

Email `security@reqdesk.com` with:

- A description of the issue.
- Steps to reproduce, including a minimal test case where possible.
- Your suggested fix, if you have one.

We will acknowledge receipt within two business days and coordinate a fix + disclosure timeline with you.

## What counts as a vulnerability

- Ways to recover or leak `api_key` / `signing_secret` client-side.
- Signature forgery — producing a valid `sha256=...` `userHash` without the secret.
- Authorization bypass on the `/reqdesk/widget/identity` route.
- Any way to cause the render hook to throw and take down Filament pages (it is designed to degrade silently).

## What doesn't

- The API key being visible in the browser — that's by design for public widget keys. Use Reqdesk's `AllowedOrigins` list to scope usage.
- User-supplied `user_resolver` classes that leak data — those are your own code.
- Misconfigured CSP headers that allow the inline init script — your app's CSP is your responsibility. See `docs/04-security.md` for guidance.
