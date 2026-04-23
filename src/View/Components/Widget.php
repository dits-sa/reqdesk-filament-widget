<?php

declare(strict_types=1);

namespace Reqdesk\Filament\View\Components;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\View\Component;
use Reqdesk\Filament\Services\WidgetConfigBuilder;

final class Widget extends Component
{
    /** @var array{scriptUrl:string,init:array<string,mixed>,identifyEndpoint:?string,panels:list<string>,injectForGuests:bool}|null */
    public ?array $config;

    public function __construct(WidgetConfigBuilder $builder, Guard $guard)
    {
        $user = $guard->user();
        $built = $builder->build($user);

        if ($built !== null && $user === null && ! $built['injectForGuests']) {
            $built = null;
        }

        $this->config = $built;
    }

    public function render(): Renderable
    {
        return view('reqdesk::widget-script');
    }
}
