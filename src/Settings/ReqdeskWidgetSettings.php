<?php

declare(strict_types=1);

namespace Reqdesk\Filament\Settings;

use Reqdesk\Filament\Support\DefaultUserResolver;
use Spatie\LaravelSettings\Settings;

class ReqdeskWidgetSettings extends Settings
{
    public ?string $api_key = null;

    public ?string $api_url = null;

    public ?string $signing_secret = null;

    public string $theme_primary_color = '#0F5E56';

    public string $theme_mode = 'auto';

    public string $theme_border_radius = '6px';

    public ?string $theme_font_family = null;

    public int $theme_z_index = 9999;

    public ?string $theme_logo = null;

    public ?string $theme_brand_name = null;

    public bool $theme_hide_branding = false;

    public string $position = 'bottom-end';

    public string $display_mode = 'popover';

    public ?string $display_side = 'end';

    public string $display_width = '420px';

    public string $display_height = '55vh';

    public bool $display_dismiss_on_backdrop = true;

    public bool $hide_fab = false;

    public bool $hide_display_mode_picker = false;

    public string $fab_icon = 'help';

    public string $default_language = 'en';

    public string $widget_mode = 'support-portal';

    public ?string $default_category = null;

    /** @var array<string, string> */
    public array $translations = [];

    /** @var list<string> */
    public array $auth_mode_when_signed = ['signed'];

    /** @var list<string> */
    public array $auth_mode_when_anonymous = ['email'];

    public string $user_resolver = DefaultUserResolver::class;

    /**
     * Persisted as a JSON blob by spatie/laravel-settings — the runtime
     * shape is documented by @phpstan-var so static analysis keeps the
     * per-key guarantees, while the native `array` type lets spatie skip
     * nested casting. Do NOT add a plain @var with a nested array shape
     * here: spatie's PropertyReflector goes through
     * phpdocumentor/type-resolver and throws on nested array shapes.
     *
     * @phpstan-var list<array{id:string,label_en:string,label_ar?:string,description?:string,section?:string,icon?:string,trigger_kind?:string,trigger_value?:string,trigger_target?:string}>
     */
    public array $actions = [];

    public bool $enabled = true;

    public bool $inject_for_guests = false;

    /** @var list<string> */
    public array $panels = [];

    public ?string $script_url = null;

    public static function group(): string
    {
        return 'reqdesk_widget';
    }

    /**
     * @return list<string>
     */
    public static function encrypted(): array
    {
        return ['api_key', 'signing_secret'];
    }
}
