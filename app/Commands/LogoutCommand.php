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
     * @return int
     */
    public function handle()
    {
        if (!$this->confirm('This command will delete the storage\'s contents? Continue', false)) {
            $this->info('Logout cancelled');
            return 0;
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
        return 0;
    }
}
