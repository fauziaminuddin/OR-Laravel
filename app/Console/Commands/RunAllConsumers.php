<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Log;

class RunAllConsumers extends Command
{
    protected $signature = 'kafka:consume-all 
        {--log-to-file : Log output to files instead of console}
        {--max-restarts=3 : Maximum number of restarts per consumer before giving up}';

    protected $description = 'Run all Kafka consumers';

    private $consumers = [
        'consume:classroom_updates',
        'consume:group_updates',
        'consume:assign_updates',
    ];

    private $restartCounts = [];
    private $startTimes = [];

    public function handle()
    {
        $this->info('Starting all Kafka consumers...');
        
        // Initialize counters
        foreach ($this->consumers as $consumer) {
            $this->restartCounts[$consumer] = 0;
            $this->startTimes[$consumer] = time();
        }

        $processes = [];

        // Start each consumer
        foreach ($this->consumers as $consumer) {
            $processes[$consumer] = $this->startConsumer($consumer);
        }

        // Monitor processes
        while (true) {
            foreach ($processes as $consumer => $process) {
                if (!$process->isRunning()) {
                    // Reset restart count if it's been running for more than an hour
                    if (time() - $this->startTimes[$consumer] > 3600) {
                        $this->restartCounts[$consumer] = 0;
                    }

                    // Check if max restarts reached
                    if ($this->restartCounts[$consumer] >= $this->option('max-restarts')) {
                        $this->error("Consumer $consumer has failed too many times. Stopping...");
                        $this->logMessage("Consumer $consumer has failed too many times. Stopping...", 'error');
                        continue;
                    }

                    $this->restartCounts[$consumer]++;
                    $this->startTimes[$consumer] = time();
                    $processes[$consumer] = $this->startConsumer($consumer);
                }
            }
            
            usleep(500000); // 0.5 seconds
        }
    }

    private function startConsumer($consumer)
    {
        $this->info("Starting consumer: $consumer");
        
        $process = new Process(['php', 'artisan', $consumer]);
        $process->start(function ($type, $buffer) use ($consumer) {
            $this->handleOutput($consumer, $type, $buffer);
        });

        return $process;
    }

    private function handleOutput($consumer, $type, $buffer)
    {
        $message = "[$consumer] " . ($type === Process::ERR ? 'ERR' : 'OUT') . " > $buffer";
        
        if ($this->option('log-to-file')) {
            Log::channel('kafka')->info($message);
        } else {
            if ($type === Process::ERR) {
                $this->error($message);
            } else {
                $this->info($message);
            }
        }
    }

    private function logMessage($message, $level = 'info')
    {
        if ($this->option('log-to-file')) {
            Log::channel('kafka')->$level($message);
        } else {
            $this->$level($message);
        }
    }

    public function __destruct()
    {
        $this->info('Stopping all consumers...');
    }
}

/* COMMAND
php artisan kafka:consume-all

php artisan kafka:consume-all --log-to-file

php artisan kafka:consume-all --max-restarts=5
*/
