<?php

declare(strict_types=1);

namespace Reqdesk\Filament\Console\Commands;

use Illuminate\Console\Command;
use Reqdesk\Filament\Services\ConfigValidator;
use Reqdesk\Filament\Services\ReqdeskClient;

final class DoctorCommand extends Command
{
    protected $signature = 'reqdesk-widget:doctor {--skip-ping : Do not hit the Reqdesk API}';

    protected $description = 'Validate the Reqdesk widget configuration non-destructively.';

    public function handle(ConfigValidator $validator, ReqdeskClient $client): int
    {
        $this->info('Reqdesk widget configuration report');
        $this->line('');

        $report = $validator->validateEnvironment();

        foreach ($report->passed as $line) {
            $this->line('  <fg=green>✓</> '.$line);
        }

        foreach ($report->warnings as $line) {
            $this->line('  <fg=yellow>!</> '.$line);
        }

        foreach ($report->errors as $line) {
            $this->line('  <fg=red>✗</> '.$line);
        }

        if (! $this->option('skip-ping') && $report->isOk()) {
            $this->line('');
            $this->line('Pinging Reqdesk API...');
            $ping = $client->ping();

            $tag = $ping->ok ? 'green' : 'red';
            $this->line(sprintf('  <fg=%s>%s</> HTTP %d — %s', $tag, $ping->ok ? '✓' : '✗', $ping->status, $ping->message));
        }

        $this->line('');

        if (! $report->isOk()) {
            $this->error('Configuration issues detected.');

            return self::FAILURE;
        }

        $this->info('Configuration looks healthy.');

        return self::SUCCESS;
    }
}
