<?php

declare(strict_types=1);

namespace Reqdesk\Filament\Filament\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Reqdesk\Filament\Enums\WidgetMode;

final class LocalizationSchema
{
    public static function make(): Section
    {
        return Section::make(__('reqdesk-widget::reqdesk-widget.tabs.localization'))
            ->columns(2)
            ->schema([
                Select::make('default_language')
                    ->label(__('reqdesk-widget::reqdesk-widget.fields.default_language.label'))
                    ->options([
                        'en' => 'English',
                        'ar' => 'العربية',
                    ])
                    ->default('en')
                    ->required(),

                Select::make('widget_mode')
                    ->label(__('reqdesk-widget::reqdesk-widget.fields.widget_mode.label'))
                    ->options(WidgetMode::class)
                    ->default(WidgetMode::TicketForm->value)
                    ->required(),

                TextInput::make('default_category')
                    ->label(__('reqdesk-widget::reqdesk-widget.fields.default_category.label'))
                    ->placeholder('01H...')
                    ->columnSpanFull(),

                KeyValue::make('translations')
                    ->label(__('reqdesk-widget::reqdesk-widget.fields.translations.label'))
                    ->keyLabel('i18n key')
                    ->valueLabel('Translation')
                    ->reorderable()
                    ->columnSpanFull(),
            ]);
    }
}
