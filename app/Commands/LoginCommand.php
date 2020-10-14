<?php

namespace App\Commands;

use App\Services\CalendarService;
use App\Services\LoginService;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class LoginCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'login';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Login SSO via HTTP client';

    /**
     * To-do list.
     *
     * @return array
     */
    protected function tasks()
    {
        return [
            'Login SSO Sister via HTTP Client' => fn () => (new LoginService())->withCredential(config('sister'))->execute(),
            'Getting calendar updates'         => fn ()         => (new CalendarService())->update(),
        ];
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $tasks = $this->tasks();
        $bar = $this->output->createProgressBar(count($tasks));
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% <fg=white;bg=blue>%elapsed:6s%/%estimated:-6s%</> ');

        $bar->start();

        foreach ($tasks as $taskName => $executor) {
            $this->task($taskName, $executor);
            $bar->advance();
        }

        $bar->finish();
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
