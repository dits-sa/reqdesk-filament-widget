<?php

declare(strict_types=1);

namespace Reqdesk\Filament\Filament\Pages;

use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Reqdesk\Filament\Filament\Schemas\ActionsSchema;
use Reqdesk\Filament\Filament\Schemas\AdvancedSchema;
use Reqdesk\Filament\Filament\Schemas\AppearanceSchema;
use Reqdesk\Filament\Filament\Schemas\ConnectionSchema;
use Reqdesk\Filament\Filament\Schemas\IdentitySchema;
use Reqdesk\Filament\Filament\Schemas\LayoutSchema;
use Reqdesk\Filament\Filament\Schemas\LocalizationSchema;
use Reqdesk\Filament\Services\ReqdeskClient;
use Reqdesk\Filament\Settings\ReqdeskWidgetSettings;

/**
 * @property-read Schema $form
 */
class ReqdeskSettings extends Page
{
    protected string $view = 'reqdesk::filament.pages.settings';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-lifebuoy';

    /** @var array<string, mixed> | null */
    public ?array $data = [];

    public static function getNavigationLabel(): string
    {
        return (string) __('reqdesk-widget::reqdesk-widget.navigation.label');
    }

    public static function getNavigationGroup(): ?string
    {
        return (string) __('reqdesk-widget::reqdesk-widget.navigation.group');
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
        $this->form->fill($this->settings()->toArray());
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
            if (property_exists($settings, $key)) {
                $settings->{$key} = $value;
            }
        }

        $settings->save();

        Notification::make()
            ->success()
            ->title(__('reqdesk-widget::reqdesk-widget.page.saved'))
            ->send();
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
}
