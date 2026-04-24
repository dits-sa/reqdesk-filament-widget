<?php

declare(strict_types=1);

namespace Reqdesk\Filament\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\Log;
use Reqdesk\Filament\Contracts\WidgetUserResolver;
use Reqdesk\Filament\Settings\ReqdeskWidgetSettings;
use Reqdesk\Filament\Support\DefaultUserResolver;
use Throwable;

/**
 * Merges ReqdeskWidgetSettings + env-backed config into the JSON payload
 * consumed by ReqdeskWidget.init(...). Layers a signed identity when the
 * resolver returns an email and a signing secret is available. Always
 * degrades gracefully — never throws into a render hook.
 *
 * Settings are resolved lazily so the builder can be instantiated on a
 * fresh install before the settings migration has populated the table.
 */
final class WidgetConfigBuilder
{
    private ?ReqdeskWidgetSettings $settings = null;

    public function __construct(
        private readonly IdentitySigner $signer,
        private readonly Container $container,
    ) {}

    /**
     * @return array{scriptUrl:string,init:array<string,mixed>,identifyEndpoint:?string,panels:list<string>,injectForGuests:bool}|null
     */
    public function build(?Authenticatable $user): ?array
    {
        $settings = $this->settings();

        if (! $settings->enabled) {
            return null;
        }

        $apiKey = $this->resolveSettingOrConfig('api_key');
        if ($apiKey === null || $apiKey === '') {
            Log::warning(__('reqdesk-widget::reqdesk-widget.errors.missing_api_key'));

            return null;
        }

        $identity = $this->resolveIdentity($user);
        $signingSecret = $this->resolveSettingOrConfig('signing_secret');
        $canSign = $identity !== null && is_string($signingSecret) && $signingSecret !== '';

        $authMode = $canSign
            ? $settings->auth_mode_when_signed
            : $settings->auth_mode_when_anonymous;

        $init = array_filter([
            'apiKey' => $apiKey,
            'apiUrl' => $this->resolveSettingOrConfig('api_url'),
            'widget' => $settings->widget_mode,
            'position' => $settings->position,
            'language' => $settings->default_language,
            'theme' => $this->buildTheme(),
            'display' => $this->buildDisplay(),
            'hideFab' => $settings->hide_fab,
            'hideDisplayModePicker' => $settings->hide_display_mode_picker,
            'fabIcon' => $settings->fab_icon,
            'authMode' => $authMode,
            'defaultCategory' => $settings->default_category,
            'translations' => $settings->translations !== [] ? $settings->translations : null,
            'actions' => $this->buildActions(),
        ], fn ($value): bool => $value !== null && $value !== '' && $value !== []);

        if ($canSign) {
            try {
                $signed = $this->signer->sign($identity['email'], null, (string) $signingSecret);
                $init['customer'] = array_filter([
                    'email' => $signed['email'],
                    'name' => $identity['name'] ?? null,
                    'externalId' => $identity['externalId'] ?? null,
                    'userHash' => $signed['userHash'],
                    'userHashTimestamp' => $signed['userHashTimestamp'],
                ], fn ($value): bool => $value !== null && $value !== '');
            } catch (Throwable $exception) {
                report($exception);
                $canSign = false;
                $init['authMode'] = $settings->auth_mode_when_anonymous;
            }
        }

        return [
            'scriptUrl' => $this->resolveScriptUrl(),
            'init' => $init,
            'identifyEndpoint' => $canSign ? $this->identifyEndpoint() : null,
            'panels' => $settings->panels,
            'injectForGuests' => $this->resolveBoolSettingOrConfig('inject_for_guests', false),
        ];
    }

    private function settings(): ReqdeskWidgetSettings
    {
        return $this->settings ??= $this->container->make(ReqdeskWidgetSettings::class);
    }

    /**
     * @return array{email:string,name?:string,externalId?:string}|null
     */
    private function resolveIdentity(?Authenticatable $user): ?array
    {
        if ($user === null) {
            return null;
        }

        try {
            $resolver = $this->resolveUserResolver();

            return $resolver->resolve($user);
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }
    }

    private function resolveUserResolver(): WidgetUserResolver
    {
        $class = $this->settings()->user_resolver;

        if ($class === '') {
            $class = (string) config('reqdesk-widget.user_resolver', DefaultUserResolver::class);
        }

        if (! class_exists($class) || ! is_subclass_of($class, WidgetUserResolver::class)) {
            $class = DefaultUserResolver::class;
        }

        /** @var WidgetUserResolver */
        return $this->container->make($class);
    }

    private function resolveSettingOrConfig(string $key): ?string
    {
        /** @var mixed $value */
        $value = $this->settings()->{$key} ?? null;

        if (is_string($value) && $value !== '') {
            return $value;
        }

        $fallback = config("reqdesk-widget.{$key}");

        return is_string($fallback) && $fallback !== '' ? $fallback : null;
    }

    private function resolveBoolSettingOrConfig(string $key, bool $default): bool
    {
        /** @var mixed $value */
        $value = $this->settings()->{$key} ?? null;

        if (is_bool($value)) {
            return $value;
        }

        /** @var mixed $fallback */
        $fallback = config("reqdesk-widget.{$key}");

        if (is_bool($fallback)) {
            return $fallback;
        }

        return $default;
    }

    /**
     * @return array<string,mixed>
     */
    private function buildTheme(): array
    {
        $settings = $this->settings();

        return array_filter([
            'primaryColor' => $settings->theme_primary_color,
            'mode' => $settings->theme_mode,
            'borderRadius' => $settings->theme_border_radius,
            'fontFamily' => $settings->theme_font_family,
            'zIndex' => $settings->theme_z_index,
            'logo' => $settings->theme_logo,
            'brandName' => $settings->theme_brand_name,
            'hideBranding' => $settings->theme_hide_branding,
        ], fn ($value): bool => $value !== null && $value !== '');
    }

    /**
     * @return array<string,mixed>
     */
    private function buildDisplay(): array
    {
        $settings = $this->settings();

        return array_filter([
            'mode' => $settings->display_mode,
            'side' => $settings->display_side,
            'width' => $settings->display_width,
            'height' => $settings->display_height,
            'dismissOnBackdrop' => $settings->display_dismiss_on_backdrop,
        ], fn ($value): bool => $value !== null && $value !== '');
    }

    /**
     * @return list<array<string,mixed>>
     */
    private function buildActions(): array
    {
        $result = [];

        foreach ($this->settings()->actions as $action) {
            $id = $action['id'];
            $labelEn = $action['label_en'];
            if ($id === '' || $labelEn === '') {
                continue;
            }

            $label = $labelEn;
            $labelAr = $action['label_ar'] ?? null;
            if (is_string($labelAr) && $labelAr !== '') {
                $label = ['en' => $labelEn, 'ar' => $labelAr];
            }

            $payload = array_filter([
                'id' => $id,
                'label' => $label,
                'description' => $action['description'] ?? null,
                'section' => $action['section'] ?? null,
                'icon' => $action['icon'] ?? null,
                'trigger' => $this->buildTrigger($action),
            ], fn ($value): bool => $value !== null && $value !== '');

            $result[] = $payload;
        }

        return $result;
    }

    /**
     * @param  array<string,mixed>  $action
     * @return array<string,mixed>|null
     */
    private function buildTrigger(array $action): ?array
    {
        $kind = $action['trigger_kind'] ?? null;
        $value = $action['trigger_value'] ?? null;
        if (! is_string($kind) || $kind === '' || ! is_string($value) || $value === '') {
            return null;
        }

        return match ($kind) {
            'url' => array_filter([
                'kind' => 'url',
                'href' => $value,
                'target' => $action['trigger_target'] ?? null,
            ], fn ($v): bool => $v !== null && $v !== ''),
            'custom-event' => ['kind' => 'custom-event', 'name' => $value],
            'call-global' => ['kind' => 'call-global', 'path' => $value],
            default => null,
        };
    }

    private function resolveScriptUrl(): string
    {
        $override = $this->settings()->script_url;
        if (is_string($override) && $override !== '') {
            return $override;
        }

        $configured = (string) config('reqdesk-widget.script_url', '');
        if ($configured !== '') {
            return $configured;
        }

        return (string) config(
            'reqdesk-widget.script_url_default',
            'https://unpkg.com/@reqdesk/widget@1.2.20/dist/index.iife.js',
        );
    }

    private function identifyEndpoint(): string
    {
        try {
            return route('reqdesk.widget.identify');
        } catch (Throwable) {
            return (string) config('reqdesk-widget.identity.endpoint', '/reqdesk/widget/identity');
        }
    }
}
