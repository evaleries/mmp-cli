<?php

namespace App\Commands;

use App\Services\Assignment\AssignmentService;
use App\Services\CalendarService;
use Exception;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class AssignmentListCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'assign:list {--latest : Data terbaru} {--custom : Pilih bulan}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Daftar tugas';

    /**
     * Calendar Service.
     *
     * @var CalendarService
     */
    protected CalendarService $calendarService;

    /**
     * Assignment Service.
     *
     * @var AssignmentService
     */
    protected AssignmentService $assignmentService;

    public function __construct(CalendarService $calendarService, AssignmentService $assignmentService)
    {
        parent::__construct();
        $this->calendarService = $calendarService;
        $this->assignmentService = $assignmentService;
    }

    /**
     * Execute the console command.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        if ($this->option('latest')) {
            $this->task('Updating assignments', fn () => $this->calendarService->update());
        }

        $month = now()->month;
        if ($this->option('custom')) {
            $month = $this->anticipate('Jadwal di bulan apa?', range(1, 12), now()->month);
            $this->task('Updating assignments for '.now()->setMonth($month)->monthName, fn () => $this->calendarService->month($month)->update());
        }

        $this->title('List Jadwal Tugas di Bulan '.now()->setMonth($month)->locale('id')->monthName);
        if ($this->calendarService->lastUpdate()) {
            $this->line('Terakhir diperbarui: '.$this->calendarService->lastUpdate());
        }

        $this->table(['id', 'topic', 'description', 'due_date', 'when'], $this->assignmentService->tableRows());
    }
}
