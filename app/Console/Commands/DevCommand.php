<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Exception\ProcessStartFailedException;
use Symfony\Component\Process\Process;

class DevCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'dev
                            {--full : Incluye el worker de colas y los logs en vivo}
                            {--dry-run : Muestra el comando sin ejecutarlo}';

    /**
     * @var string
     */
    protected $description = 'Levanta Laravel y Vite en una sola consola';

    public function handle(): int
    {
        $process = new Process($this->concurrentCommand(), base_path(), null, null, null);
        $process->setTimeout(null);

        if ($this->option('full') && ! function_exists('pcntl_fork')) {
            $this->components->warn('Se omite Pail: la extensión pcntl no está disponible en este sistema.');
        }

        if ($this->option('dry-run')) {
            $this->line($process->getCommandLine());

            return self::SUCCESS;
        }

        if (! is_file(base_path('node_modules/concurrently/dist/bin/concurrently.js'))) {
            $this->components->error('No se encontró concurrently. Ejecutá "npm install" primero.');

            return self::FAILURE;
        }

        $this->components->info('Levantando Laravel y Vite. Presioná Ctrl+C para detenerlos.');

        try {
            $exitCode = $process->run(function (string $type, string $buffer): void {
                $this->output->write($buffer);
            });
        } catch (ProcessStartFailedException) {
            $this->components->error('No se pudo iniciar Node. Verificá que esté instalado y disponible en el PATH.');

            return self::FAILURE;
        }

        return $exitCode === 0 ? self::SUCCESS : self::FAILURE;
    }

    /**
     * @return list<string>
     */
    private function concurrentCommand(): array
    {
        $command = ['node', 'node_modules/concurrently/dist/bin/concurrently.js'];

        if ($this->option('full')) {
            $withPail = function_exists('pcntl_fork');
            $command = [
                ...$command,
                '-c', $withPail ? '#93c5fd,#c4b5fd,#fb7185,#fdba74' : '#93c5fd,#c4b5fd,#fb7185',
                'php artisan serve --no-reload',
                'php artisan queue:listen --tries=1 --timeout=0',
            ];

            if ($withPail) {
                $command[] = 'php artisan pail --timeout=0';
            }

            $command[] = 'npm run dev';
            $command[] = '--names='.($withPail ? 'server,queue,logs,vite' : 'server,queue,vite');
        } else {
            $command = [
                ...$command,
                '-c', '#93c5fd,#c4b5fd',
                'php artisan serve --no-reload',
                'npm run dev',
                '--names=server,vite',
            ];
        }

        return [...$command, '--kill-others', '--kill-timeout=3000'];
    }
}
