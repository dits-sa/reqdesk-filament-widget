<?php

declare(strict_types=1);

namespace Reqdesk\Filament\Filament\Pages;

use BackedEnum;
use Closure;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Gate;
use Reqdesk\Filament\Filament\Schemas\ActionsSchema;
use Reqdesk\Filament\Filament\Schemas\AdvancedSchema;
use Reqdesk\Filament\Filament\Schemas\AppearanceSchema;
use Reqdesk\Filament\Filament\Schemas\ConnectionSchema;
use Reqdesk\Filament\Filament\Schemas\IdentitySchema;
use Reqdesk\Filament\Filament\Schemas\LayoutSchema;
use Reqdesk\Filament\Filament\Schemas\LocalizationSchema;
use Reqdesk\Filament\ReqdeskWidgetPlugin;
use Reqdesk\Filament\Services\ReqdeskClient;
use Reqdesk\Filament\Settings\ReqdeskWidgetSettings;
use Throwable;
use UnitEnum;

/**
 * @property-read Schema $form
 */
class ReqdeskSettings extends Page
{
    protected string $view = 'reqdesk::filament.pages.settings';

    /** @var array<string, mixed> | null */
    public ?array $data = [];

    public static function getNavigationLabel(): string
    {
        return (string) __('reqdesk-widget::reqdesk-widget.navigation.label');
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        $group = self::plugin()?->getNavigationGroup();

        if ($group !== null) {
            return $group;
        }

        return (string) __('reqdesk-widget::reqdesk-widget.navigation.group');
    }

    public static function getNavigationSort(): ?int
    {
        return self::plugin()?->getNavigationSort();
    }

    public static function getNavigationIcon(): string|BackedEnum|Htmlable|null
    {
        return self::plugin()?->getNavigationIcon() ?? 'heroicon-o-lifebuoy';
    }

    public static function canAccess(): bool
    {
        $ability = self::plugin()?->getAuthorizeCallback();

        if ($ability === null) {
            return true;
        }

        if ($ability instanceof Closure) {
            return (bool) $ability(auth()->user());
        }

        return Gate::allows($ability);
    }

    public function getTitle(): string
    {
        return (string) __('reqdesk-widget::reqdesk-widget.page.title');
    }

    public function getSubheading(): ?string
    {
        return (string) __('reqdesk-widget::reqdesk-widget.page.subheading');
    }

    public function mount(): void
    {
        $data = $this->settings()->toArray();

        foreach ($data as $key => $value) {
            if ($value !== null && $value !== '') {
                continue;
            }

            $fallback = config("reqdesk-widget.{$key}");
            if ($fallback === null || $fallback === '') {
                continue;
            }

            $data[$key] = $fallback;
        }

        $this->form->fill($data);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('reqdesk-settings')
                    ->tabs([
                        Tab::make(__('reqdesk-widget::reqdesk-widget.tabs.connection'))
                            ->schema([ConnectionSchema::make()]),
                        Tab::make(__('reqdesk-widget::reqdesk-widget.tabs.identity'))
                            ->schema([IdentitySchema::make()]),
                        Tab::make(__('reqdesk-widget::reqdesk-widget.tabs.appearance'))
                            ->schema([AppearanceSchema::make()]),
                        Tab::make(__('reqdesk-widget::reqdesk-widget.tabs.layout'))
                            ->schema([LayoutSchema::make()]),
                        Tab::make(__('reqdesk-widget::reqdesk-widget.tabs.localization'))
                            ->schema([LocalizationSchema::make()]),
                        Tab::make(__('reqdesk-widget::reqdesk-widget.tabs.actions'))
                            ->schema([ActionsSchema::make()]),
                        Tab::make(__('reqdesk-widget::reqdesk-widget.tabs.advanced'))
                            ->schema([AdvancedSchema::make()]),
                    ])
                    ->persistTabInQueryString(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $settings = $this->settings();

        foreach ($data as $key => $value) {
            if (! property_exists($settings, $key)) {
                continue;
            }

            // Filament + Livewire + JSON round-tripping all hand back
            // values whose type does not always match the spatie-settings
            // declaration: BackedEnum cases from Select, floats from
            // `TextInput->numeric()` (Livewire serialises numbers as JSON
            // numbers, which become PHP floats on decode), strings instead
            // of bools from toggle widgets in some Filament versions, and
            // empty strings where the property is `?type` and expects null.
            // Coerce against the property's declared reflection type so
            // every assignment becomes legal under strict_types.
            $settings->{$key} = self::coerceForProperty($settings::class, $key, $value);
        }

        $settings->save();

        Notification::make()
            ->success()
            ->title(__('reqdesk-widget::reqdesk-widget.page.saved'))
            ->send();
    }

    /**
     * Coerce a single form value to match the type declaration of a
     * settings property. Static so tests can exercise it without needing
     * to instantiate the settings class — instantiation goes through
     * spatie's cast factory, which has a different (orthogonal) failure
     * surface in some environments.
     *
     * @param  class-string  $settingsClass
     */
    public static function coerceForProperty(string $settingsClass, string $key, mixed $value): mixed
    {
        $value = self::unwrapEnums($value);

        try {
            $reflection = new \ReflectionProperty($settingsClass, $key);
        } catch (\ReflectionException) {
            return $value;
        }

        $type = $reflection->getType();
        if (! $type instanceof \ReflectionNamedType) {
            return $value;
        }

        $allowsNull = $type->allowsNull();
        $name = $type->getName();

        if (($value === null || $value === '') && $allowsNull) {
            return null;
        }

        return match ($name) {
            'int' => self::toInt($value, $reflection),
            'float' => self::toFloat($value, $reflection),
            'bool' => self::toBool($value),
            'string' => self::toString($value, $allowsNull),
            'array' => is_array($value) ? $value : [],
            default => $value,
        };
    }

    private static function unwrapEnums(mixed $value): mixed
    {
        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        if ($value instanceof UnitEnum) {
            return $value->name;
        }

        if (is_array($value)) {
            return array_map(fn ($item): mixed => self::unwrapEnums($item), $value);
        }

        return $value;
    }

    private static function toInt(mixed $value, \ReflectionProperty $reflection): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_float($value)) {
            return (int) round($value);
        }

        if (is_numeric($value)) {
            return (int) round((float) $value);
        }

        if ($value === null || $value === '' || is_bool($value)) {
            $default = $reflection->hasDefaultValue() ? $reflection->getDefaultValue() : 0;

            return is_int($default) ? $default : 0;
        }

        return 0;
    }

    private static function toFloat(mixed $value, \ReflectionProperty $reflection): float
    {
        if (is_float($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        if ($value === null || $value === '' || is_bool($value)) {
            $default = $reflection->hasDefaultValue() ? $reflection->getDefaultValue() : 0.0;

            return is_float($default) || is_int($default) ? (float) $default : 0.0;
        }

        return 0.0;
    }

    private static function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return $value !== 0 && $value !== 0.0;
        }

        if (is_string($value)) {
            $lower = strtolower(trim($value));

            return ! in_array($lower, ['', '0', 'false', 'off', 'no', 'null'], true);
        }

        return (bool) $value;
    }

    private static function toString(mixed $value, bool $allowsNull): ?string
    {
        if (is_string($value)) {
            return $value;
        }

        if ($value === null) {
            return $allowsNull ? null : '';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        if (is_array($value)) {
            return $allowsNull ? null : '';
        }

        return (string) $value;
    }

    public function testConnection(): void
    {
        $result = app(ReqdeskClient::class)->ping();

        Notification::make()
            ->title(sprintf('Reqdesk ping — HTTP %d', $result->status))
            ->body($result->message)
            ->{$result->ok ? 'success' : 'danger'}()
            ->send();
    }

    private function settings(): ReqdeskWidgetSettings
    {
        return app(ReqdeskWidgetSettings::class);
    }

    private static function plugin(): ?ReqdeskWidgetPlugin
    {
        try {
            return ReqdeskWidgetPlugin::get();
        } catch (Throwable) {
            return null;
        }
    }
}
