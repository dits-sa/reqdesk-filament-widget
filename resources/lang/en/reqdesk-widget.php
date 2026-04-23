<?php

declare(strict_types=1);

return [
    'navigation' => [
        'label' => 'Support widget',
        'group' => 'Reqdesk',
    ],
    'page' => [
        'title' => 'Reqdesk support widget',
        'subheading' => 'Configure the embedded Reqdesk widget for this panel.',
        'saved' => 'Settings saved.',
    ],
    'tabs' => [
        'connection' => 'Connection',
        'appearance' => 'Appearance',
        'layout' => 'Layout',
        'localization' => 'Localization',
        'identity' => 'Identity',
        'actions' => 'Custom actions',
        'advanced' => 'Advanced',
    ],
    'fields' => [
        'api_key' => [
            'label' => 'API key',
            'help' => 'Project (rqd_pk_) or workspace (rqd_ws_) key from the Reqdesk dashboard.',
        ],
        'api_url' => [
            'label' => 'API URL',
            'help' => 'Leave blank to use https://app.reqdesk.com.',
        ],
        'signing_secret' => [
            'label' => 'Signing secret',
            'help' => 'HMAC secret used to sign the authenticated user identity. Keep this server-side.',
        ],
        'project_id' => [
            'label' => 'Project ID',
            'help' => 'Optional — usually derived from the API key prefix.',
        ],
        'theme_primary_color' => ['label' => 'Primary colour'],
        'theme_mode' => ['label' => 'Theme mode'],
        'theme_border_radius' => ['label' => 'Border radius'],
        'theme_font_family' => ['label' => 'Font family'],
        'theme_z_index' => ['label' => 'Z-index'],
        'theme_logo' => ['label' => 'Logo URL'],
        'theme_brand_name' => ['label' => 'Brand name'],
        'theme_hide_branding' => ['label' => 'Hide Reqdesk branding'],
        'position' => ['label' => 'FAB position'],
        'display_mode' => ['label' => 'Display mode'],
        'display_side' => ['label' => 'Sheet side'],
        'display_width' => ['label' => 'Sheet width'],
        'display_height' => ['label' => 'Sheet height'],
        'display_dismiss_on_backdrop' => ['label' => 'Dismiss on backdrop click'],
        'hide_fab' => ['label' => 'Hide floating action button'],
        'hide_display_mode_picker' => ['label' => 'Hide display-mode picker'],
        'fab_icon' => ['label' => 'FAB icon'],
        'default_language' => ['label' => 'Default language'],
        'widget_mode' => ['label' => 'Widget mode'],
        'default_category' => ['label' => 'Default category ULID'],
        'translations' => ['label' => 'Translation overrides'],
        'auth_mode_when_signed' => [
            'label' => 'Auth modes when signed in',
            'help' => 'Applied when a Laravel user is authenticated.',
        ],
        'auth_mode_when_anonymous' => [
            'label' => 'Auth modes when anonymous',
            'help' => 'Applied when no Laravel user is authenticated.',
        ],
        'user_resolver' => [
            'label' => 'User resolver',
            'help' => 'FQCN implementing WidgetUserResolver. Defaults to DefaultUserResolver which reads $user->email and $user->name.',
        ],
        'actions' => ['label' => 'Custom menu actions'],
        'enabled' => ['label' => 'Enable widget'],
        'inject_for_guests' => ['label' => 'Inject for guests too'],
        'panels' => ['label' => 'Limit to panels'],
        'script_url' => [
            'label' => 'Script URL override',
            'help' => 'Leave blank to use the pinned CDN default.',
        ],
    ],
    'actions' => [
        'save' => 'Save settings',
        'test_connection' => 'Test connection',
        'preview_signature' => 'Preview signature',
    ],
    'validation' => [
        'invalid_resolver' => 'The class :class does not exist or does not implement WidgetUserResolver.',
        'invalid_color' => 'The primary colour must be a valid CSS hex value.',
    ],
    'errors' => [
        'missing_api_key' => 'REQDESK_API_KEY is not configured. The Reqdesk widget will not render.',
        'missing_signing_secret' => 'REQDESK_SIGNING_SECRET is not configured. Signed host-app identity is disabled.',
    ],
];
