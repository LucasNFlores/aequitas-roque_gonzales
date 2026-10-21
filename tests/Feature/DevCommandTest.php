<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class DevCommandTest extends TestCase
{
    public function test_dev_command_builds_the_default_concurrently_command(): void
    {
        $exitCode = Artisan::call('dev', ['--dry-run' => true]);
        $output = Artisan::output();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('concurrently', $output);
        $this->assertStringContainsString('artisan serve --no-reload', $output);
        $this->assertStringContainsString('npm run dev', $output);
        $this->assertStringContainsString('--names=server,vite', $output);
        $this->assertStringContainsString('--kill-others', $output);
        $this->assertStringContainsString('--kill-timeout=3000', $output);
        $this->assertStringNotContainsString('queue:listen', $output);
        $this->assertStringNotContainsString('pail', $output);
    }

    public function test_dev_command_includes_queue_and_logs_when_full_option_is_used(): void
    {
        $exitCode = Artisan::call('dev', ['--full' => true, '--dry-run' => true]);
        $output = Artisan::output();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('artisan serve --no-reload', $output);
        $this->assertStringContainsString('artisan queue:listen --tries=1 --timeout=0', $output);
        $this->assertStringContainsString('npm run dev', $output);
        $this->assertStringContainsString('--kill-others', $output);

        if (function_exists('pcntl_fork')) {
            $this->assertStringContainsString('artisan pail --timeout=0', $output);
            $this->assertStringContainsString('--names=server,queue,logs,vite', $output);
        } else {
            $this->assertStringNotContainsString('artisan pail', $output);
            $this->assertStringContainsString('--names=server,queue,vite', $output);
        }
    }
}
