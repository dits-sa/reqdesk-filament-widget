<?php

declare(strict_types=1);

namespace Reqdesk\Filament\Services;

final class ReqdeskPingResult
{
    public function __construct(
        public readonly bool $ok,
        public readonly int $status,
        public readonly string $message,
    ) {}
}
