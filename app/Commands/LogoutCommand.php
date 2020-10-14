<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use LaravelZero\Framework\Commands\Command;

class LogoutCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'logout';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Clear storage folder contents';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (!$this->confirm('This command will delete the storage\'s contents? Continue', false)) {
            return $this->info('Logout cancelled');
        }

        $directories = ['cookies', 'responses'];
        foreach ($directories as $directory) {
            $this->task("Cleaning the {$directory} folder", function () use ($directory) {
                $fullPath = Storage::path($directory);

                return File::isDirectory($fullPath)
                    && File::cleanDirectory($fullPath)
                    && File::deleteDirectory($fullPath);
            });
        }

        $this->line('Logout completed');
    }

    /**
     * Define the command's schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
