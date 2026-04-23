<?php

declare(strict_types=1);

namespace Reqdesk\Filament\Filament\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

final class ActionsSchema
{
    public static function make(): Section
    {
        return Section::make(__('reqdesk-widget::reqdesk-widget.tabs.actions'))
            ->schema([
                Repeater::make('actions')
                    ->label(__('reqdesk-widget::reqdesk-widget.fields.actions.label'))
                    ->schema([
                        TextInput::make('id')
                            ->label('ID')
                            ->required()
                            ->regex('/^[a-z0-9][a-z0-9-_]*$/i'),

                        TextInput::make('label_en')
                            ->label('Label (English)')
                            ->required(),

                        TextInput::make('label_ar')
                            ->label('Label (Arabic)'),

                        TextInput::make('description')
                            ->label('Description')
                            ->columnSpanFull(),

                        Select::make('section')
                            ->label('Menu section')
                            ->options([
                                'top' => 'Top',
                                'bottom' => 'Bottom',
                            ])
                            ->default('top'),

                        TextInput::make('icon')
                            ->label('Icon (SVG path d)'),

                        Select::make('trigger_kind')
                            ->label('Trigger kind')
                            ->options([
                                'url' => 'URL',
                                'custom-event' => 'Custom DOM event',
                                'call-global' => 'Call global function',
                            ]),

                        TextInput::make('trigger_value')
                            ->label('Trigger value'),

                        TextInput::make('trigger_target')
                            ->label('Link target (URL triggers only)')
                            ->placeholder('_blank'),
                    ])
                    ->columns(2)
                    ->itemLabel(fn (array $state): ?string => $state['label_en'] ?? $state['id'] ?? null)
                    ->collapsible()
                    ->reorderable()
                    ->cloneable()
                    ->addActionLabel('Add custom action'),
            ]);
    }
}
