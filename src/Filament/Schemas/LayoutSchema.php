<?php

declare(strict_types=1);

namespace Reqdesk\Filament\Filament\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Reqdesk\Filament\Enums\DisplayMode;
use Reqdesk\Filament\Enums\FabIcon;
use Reqdesk\Filament\Enums\Position;
use Reqdesk\Filament\Enums\SheetSide;

final class LayoutSchema
{
    public static function make(): Section
    {
        return Section::make(__('reqdesk-widget::reqdesk-widget.tabs.layout'))
            ->columns(2)
            ->schema([
                Select::make('position')
                    ->label(__('reqdesk-widget::reqdesk-widget.fields.position.label'))
                    ->options(Position::class)
                    ->default(Position::BottomEnd->value)
                    ->required(),

                Select::make('display_mode')
                    ->label(__('reqdesk-widget::reqdesk-widget.fields.display_mode.label'))
                    ->options(DisplayMode::class)
                    ->default(DisplayMode::Popover->value)
                    ->live()
                    ->required(),

                Select::make('display_side')
                    ->label(__('reqdesk-widget::reqdesk-widget.fields.display_side.label'))
                    ->options(SheetSide::class)
                    ->default(SheetSide::End->value)
                    ->visible(fn (Get $get): bool => $get('display_mode') === DisplayMode::SideSheet->value),

                TextInput::make('display_width')
                    ->label(__('reqdesk-widget::reqdesk-widget.fields.display_width.label'))
                    ->placeholder('420px')
                    ->visible(fn (Get $get): bool => $get('display_mode') === DisplayMode::SideSheet->value),

                TextInput::make('display_height')
                    ->label(__('reqdesk-widget::reqdesk-widget.fields.display_height.label'))
                    ->placeholder('55vh')
                    ->visible(fn (Get $get): bool => $get('display_mode') === DisplayMode::BottomSheet->value),

                Toggle::make('display_dismiss_on_backdrop')
                    ->label(__('reqdesk-widget::reqdesk-widget.fields.display_dismiss_on_backdrop.label'))
                    ->visible(fn (Get $get): bool => in_array(
                        $get('display_mode'),
                        [DisplayMode::SideSheet->value, DisplayMode::BottomSheet->value],
                        true,
                    )),

                Select::make('fab_icon')
                    ->label(__('reqdesk-widget::reqdesk-widget.fields.fab_icon.label'))
                    ->options(FabIcon::class)
                    ->default(FabIcon::Help->value),

                Toggle::make('hide_fab')
                    ->label(__('reqdesk-widget::reqdesk-widget.fields.hide_fab.label')),

                Toggle::make('hide_display_mode_picker')
                    ->label(__('reqdesk-widget::reqdesk-widget.fields.hide_display_mode_picker.label')),
            ]);
    }
}
