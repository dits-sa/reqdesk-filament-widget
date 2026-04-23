<?php

declare(strict_types=1);

namespace Reqdesk\Filament\Filament\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Reqdesk\Filament\Enums\ThemeMode;

final class AppearanceSchema
{
    public static function make(): Section
    {
        return Section::make(__('reqdesk-widget::reqdesk-widget.tabs.appearance'))
            ->columns(2)
            ->schema([
                ColorPicker::make('theme_primary_color')
                    ->label(__('reqdesk-widget::reqdesk-widget.fields.theme_primary_color.label'))
                    ->required()
                    ->rule('regex:/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6}|[A-Fa-f0-9]{8})$/')
                    ->default('#0F5E56'),

                Select::make('theme_mode')
                    ->label(__('reqdesk-widget::reqdesk-widget.fields.theme_mode.label'))
                    ->options(ThemeMode::class)
                    ->default(ThemeMode::Auto->value)
                    ->required(),

                TextInput::make('theme_border_radius')
                    ->label(__('reqdesk-widget::reqdesk-widget.fields.theme_border_radius.label'))
                    ->placeholder('6px'),

                TextInput::make('theme_font_family')
                    ->label(__('reqdesk-widget::reqdesk-widget.fields.theme_font_family.label'))
                    ->placeholder("'Geist', 'Rubik', sans-serif"),

                TextInput::make('theme_z_index')
                    ->label(__('reqdesk-widget::reqdesk-widget.fields.theme_z_index.label'))
                    ->integer()
                    ->default(9999),

                TextInput::make('theme_brand_name')
                    ->label(__('reqdesk-widget::reqdesk-widget.fields.theme_brand_name.label')),

                TextInput::make('theme_logo')
                    ->label(__('reqdesk-widget::reqdesk-widget.fields.theme_logo.label'))
                    ->url()
                    ->columnSpanFull(),

                Toggle::make('theme_hide_branding')
                    ->label(__('reqdesk-widget::reqdesk-widget.fields.theme_hide_branding.label'))
                    ->columnSpanFull(),
            ]);
    }
}
