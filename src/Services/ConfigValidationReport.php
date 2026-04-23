<?php

declare(strict_types=1);

namespace Reqdesk\Filament\Services;

final class ConfigValidationReport
{
    /**
     * @param  list<string>  $errors
     * @param  list<string>  $warnings
     * @param  list<string>  $passed
     */
    public function __construct(
        public readonly array $errors,
        public readonly array $warnings,
        public readonly array $passed,
    ) {}

    public function isOk(): bool
    {
        return $this->errors === [];
    }
}
