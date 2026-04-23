<?php

declare(strict_types=1);

namespace Reqdesk\Filament\Filament\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Reqdesk\Filament\Contracts\WidgetUserResolver;
use Reqdesk\Filament\Enums\AuthMode;
use Reqdesk\Filament\Support\DefaultUserResolver;

final class IdentitySchema
{
    public static function make(): Section
    {
        return Section::make(__('reqdesk-widget::reqdesk-widget.tabs.identity'))
            ->description(__('reqdesk-widget::reqdesk-widget.fields.user_resolver.help'))
            ->columns(2)
            ->schema([
                CheckboxList::make('auth_mode_when_signed')
                    ->label(__('reqdesk-widget::reqdesk-widget.fields.auth_mode_when_signed.label'))
                    ->helperText(__('reqdesk-widget::reqdesk-widget.fields.auth_mode_when_signed.help'))
                    ->options(AuthMode::class)
                    ->default(['signed'])
                    ->required(),

                CheckboxList::make('auth_mode_when_anonymous')
                    ->label(__('reqdesk-widget::reqdesk-widget.fields.auth_mode_when_anonymous.label'))
                    ->helperText(__('reqdesk-widget::reqdesk-widget.fields.auth_mode_when_anonymous.help'))
                    ->options(AuthMode::class)
                    ->default(['email'])
                    ->required(),

                TextInput::make('user_resolver')
                    ->label(__('reqdesk-widget::reqdesk-widget.fields.user_resolver.label'))
                    ->helperText(__('reqdesk-widget::reqdesk-widget.fields.user_resolver.help'))
                    ->default(DefaultUserResolver::class)
                    ->required()
                    ->rule(function () {
                        return function (string $attribute, mixed $value, \Closure $fail): void {
                            $class = is_string($value) ? $value : '';
                            if ($class === '' || ! class_exists($class) || ! is_subclass_of($class, WidgetUserResolver::class)) {
                                $fail(__('reqdesk-widget::reqdesk-widget.validation.invalid_resolver', [
                                    'class' => $class,
                                ]));
                            }
                        };
                    })
                    ->columnSpanFull(),
            ]);
    }
}
