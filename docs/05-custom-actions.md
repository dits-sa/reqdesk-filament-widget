# Custom menu actions cookbook

The widget's menu exposes built-in actions (`new-ticket`, `my-tickets`, `track`, `preferences`). You can add your own — a "Report a billing issue" shortcut, a deep link to a specific category, a `window.alert()` during development — entirely through the Filament settings page.

## Quick reference

Every action needs an ID and an English label. Everything else is optional.

```
id             → unique, [a-z0-9-_]+
label_en       → displayed in English
label_ar       → displayed in Arabic (optional)
description    → secondary text
section        → top | bottom (menu slot)
icon           → raw SVG path d (24x24 viewBox)
trigger_kind   → url | custom-event | call-global
trigger_value  → URL / event name / global function path
trigger_target → link target (URL only), e.g. _blank
```

## Recipe 1 — Link to an external form

Open a Google Form for detailed escalations in a new tab.

| Field | Value |
|-------|-------|
| id | `escalate` |
| label_en | Escalate to engineering |
| label_ar | تصعيد إلى قسم الهندسة |
| description | Use only when a blocker affects more than one team |
| section | bottom |
| trigger_kind | `url` |
| trigger_value | `https://forms.gle/...` |
| trigger_target | `_blank` |

## Recipe 2 — Open a pre-filled ticket form

Dispatch a DOM event that the widget's built-in `openAction('new-ticket', {...})` listens to.

| Field | Value |
|-------|-------|
| id | `report-billing` |
| label_en | Report a billing issue |
| section | top |
| trigger_kind | `custom-event` |
| trigger_value | `reqdesk:report-billing` |

Then in your host JS (typically in `resources/js/app.js`):

```js
window.addEventListener('reqdesk:report-billing', () => {
    window.ReqdeskWidget.openAction('new-ticket', {
        category: 'billing',
        title: 'Billing issue — ',
    });
});
```

## Recipe 3 — Call a global function

Useful for triggering a Livewire/Alpine modal that already exists in your app.

| Field | Value |
|-------|-------|
| id | `live-chat` |
| label_en | Live chat |
| icon | `M2 5a3 3 0 013-3h14...` *(your SVG path)* |
| trigger_kind | `call-global` |
| trigger_value | `window.LiveChat.open` |

The widget walks the `trigger_value` path on `window` and calls the result as a function — pass the full dotted path. No arguments are forwarded.

## Ordering and visibility

- The Repeater is reorderable — drag handles change the order of the `actions` array, which the widget renders in-order.
- `section: top` actions render above built-ins; `section: bottom` below.
- To temporarily hide an action without deleting it, clear the trigger fields — the plugin skips rows without both `id` + `label_en`.

## Programmatic actions

For actions that need a closure (`onClick`, `visible`), you can't use the settings page — those require JS callbacks. Register them at runtime on the client:

```js
window.ReqdeskWidget.addAction({
    id: 'clear-cache',
    label: 'Clear cache',
    visible: (ctx) => ctx.user?.email?.endsWith('@your-company.com'),
    onClick: async (ctx) => {
        ctx.setLoading(true);
        await fetch('/admin/cache/clear', { method: 'POST' });
        ctx.setBadge('✓');
    },
});
```

This can live in your `resources/js/reqdesk-custom.js` and be added to Vite's input list. The plugin does not manage this file for you — it is pure frontend glue.

## Validation

The Filament Repeater enforces:

- `id` is present and matches `[a-z0-9][a-z0-9-_]*`.
- `label_en` is present.
- `trigger_kind` is one of the three accepted kinds when any trigger field is filled.

Rows failing validation prevent the form from saving and the builder silently skips them at render time (defence in depth).

## Debugging

Open the widget, click your action, and check the browser console for `[reqdesk]` errors. Common causes:

- `call-global` target does not exist → check the path exactly (`window.` prefix is implicit).
- `custom-event` does not fire → make sure the listener is attached on `window`, not `document`.
- `url` opens a blank page → `trigger_target: _blank` needs a full URL including scheme.
