<?php

declare(strict_types=1);

namespace Reqdesk\Filament\Filament\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

final class ConnectionSchema
{
    public static function make(): Section
    {
        return Section::make(__('reqdesk-widget::reqdesk-widget.tabs.connection'))
            ->description(__('reqdesk-widget::reqdesk-widget.page.subheading'))
            ->columns(2)
            ->schema([
                TextInput::make('api_key')
                    ->label(__('reqdesk-widget::reqdesk-widget.fields.api_key.label'))
                    ->helperText(__('reqdesk-widget::reqdesk-widget.fields.api_key.help'))
                    ->password()
                    ->revealable()
                    ->autocomplete('off')
                    ->required()
                    ->columnSpanFull(),

                TextInput::make('api_url')
                    ->label(__('reqdesk-widget::reqdesk-widget.fields.api_url.label'))
                    ->helperText(__('reqdesk-widget::reqdesk-widget.fields.api_url.help'))
                    ->url()
                    ->placeholder('https://app.reqdesk.com'),

                TextInput::make('project_id')
                    ->label(__('reqdesk-widget::reqdesk-widget.fields.project_id.label'))
                    ->helperText(__('reqdesk-widget::reqdesk-widget.fields.project_id.help')),

                TextInput::make('signing_secret')
                    ->label(__('reqdesk-widget::reqdesk-widget.fields.signing_secret.label'))
                    ->helperText(__('reqdesk-widget::reqdesk-widget.fields.signing_secret.help'))
                    ->password()
                    ->revealable()
                    ->autocomplete('off')
                    ->columnSpanFull(),
            ]);
    }
}
