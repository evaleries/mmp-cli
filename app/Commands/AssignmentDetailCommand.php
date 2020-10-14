<?php

namespace App\Commands;

use App\Services\Assignment\AssignmentService;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class AssignmentDetailCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'assign:detail';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Detail assignment';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(AssignmentService $assignmentService)
    {
        $assignments = $assignmentService->assignments();
        $choices = $assignments->pluck('id');

        $selectedCourse = $this->choice('Number', $choices->toArray());
        $matkul = $assignments->firstWhere('id', '=', $selectedCourse);

        if (!$matkul) {
            return $this->error('Invalid Options');
        }

        $due = $assignmentService->formatTimestamp($matkul->get('timestart'));
        $this->line("Mata Kuliah:\t".data_get($matkul, 'course.fullname'));
        $this->line("URL:\t\t".$matkul->get('url'));
        $this->line("Due Date:\t".$due->format('D F Y H:i A'));
        $this->line("When:\t\t".$due->diffForHumans());
        $this->line("Description: \n".strip_tags($matkul->get('description')));
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
