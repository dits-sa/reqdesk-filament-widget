<?php

declare(strict_types=1);

namespace Reqdesk\Filament\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Reqdesk\Filament\Settings\ReqdeskWidgetSettings;

/**
 * Minimal Guzzle-backed client for the Reqdesk REST API. Used by the
 * "Test connection" action in the settings page and by the doctor command.
 * Kept intentionally small — the plugin's primary integration is the
 * browser-side widget, not server-to-server calls.
 *
 * Settings are resolved lazily so the service can be instantiated on a
 * fresh install before the settings migration has populated the table.
 */
final class ReqdeskClient
{
    private ?Client $client = null;

    private ?ReqdeskWidgetSettings $settings = null;

    public function ping(): ReqdeskPingResult
    {
        $apiKey = $this->apiKey();
        $apiUrl = $this->apiUrl();

        if ($apiKey === '' || $apiUrl === '') {
            return new ReqdeskPingResult(false, 0, 'REQDESK_API_KEY or REQDESK_API_URL is not configured.');
        }

        try {
            $response = $this->guzzle()->get('/api/v1/widget/ping', [
                'headers' => [
                    'X-Api-Key' => $apiKey,
                    'Accept' => 'application/json',
                ],
                'http_errors' => false,
                'timeout' => 8,
            ]);
        } catch (GuzzleException $exception) {
            return new ReqdeskPingResult(false, 0, $exception->getMessage());
        }

        $status = $response->getStatusCode();
        $ok = $status >= 200 && $status < 300;

        return new ReqdeskPingResult($ok, $status, $ok ? 'OK' : (string) $response->getBody());
    }

    private function guzzle(): Client
    {
        return $this->client ??= new Client([
            'base_uri' => rtrim($this->apiUrl(), '/').'/',
            'timeout' => 10,
            'connect_timeout' => 5,
        ]);
    }

    private function settings(): ReqdeskWidgetSettings
    {
        return $this->settings ??= app(ReqdeskWidgetSettings::class);
    }

    private function apiKey(): string
    {
        $value = $this->settings()->api_key;
        if (is_string($value) && $value !== '') {
            return $value;
        }

        return (string) config('reqdesk-widget.api_key', '');
    }

    private function apiUrl(): string
    {
        $value = $this->settings()->api_url;
        if (is_string($value) && $value !== '') {
            return $value;
        }

        return (string) config('reqdesk-widget.api_url', 'https://app.reqdesk.com');
    }
}
