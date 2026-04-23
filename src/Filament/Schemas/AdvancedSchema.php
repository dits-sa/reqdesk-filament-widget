<?php

declare(strict_types=1);

namespace Reqdesk\Filament\Filament\Schemas;

use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;

final class AdvancedSchema
{
    public static function make(): Section
    {
        return Section::make(__('reqdesk-widget::reqdesk-widget.tabs.advanced'))
            ->columns(2)
            ->schema([
                Toggle::make('enabled')
                    ->label(__('reqdesk-widget::reqdesk-widget.fields.enabled.label'))
                    ->default(true),

                Toggle::make('inject_for_guests')
                    ->label(__('reqdesk-widget::reqdesk-widget.fields.inject_for_guests.label')),

                TagsInput::make('panels')
                    ->label(__('reqdesk-widget::reqdesk-widget.fields.panels.label'))
                    ->placeholder('admin, agent')
                    ->columnSpanFull(),

                TextInput::make('script_url')
                    ->label(__('reqdesk-widget::reqdesk-widget.fields.script_url.label'))
                    ->helperText(__('reqdesk-widget::reqdesk-widget.fields.script_url.help'))
                    ->url()
                    ->columnSpanFull(),
            ]);
    }
}
