<?php

namespace App\Commands;

use App\Services\Attendance\AttendanceService;
use App\Services\CalendarService;
use Illuminate\Console\Scheduling\Schedule;
use InvalidArgumentException;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException as ExceptionInvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;

class AttendanceListCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'attend:list 
        {--latest   : Update jadwal terbaru} 
        {custom   : Memilih bulan tertentu} 
        {--t|--today    : Daftar absen hari ini}
        {--tomorrow : Daftar absen besok}
        {--u|--upcoming : Daftar absen yang akan datang}
        {--desc     : Mengurutkan daftar absensi dengan tanggal terbaru}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Daftar absensi online';

    /**
     * Calendar Service.
     *
     * @var CalendarService
     */
    protected $calendarService;

    /**
     * Assignment Service.
     *
     * @var AttendanceService
     */
    protected $attendanceService;

    /**
     * @param CalendarService   $calendarService
     * @param AttendanceService $attendanceService
     *
     * @throws InvalidArgumentException
     * @throws ExceptionInvalidArgumentException
     * @throws LogicException
     *
     * @return void
     */
    public function __construct(CalendarService $calendarService, AttendanceService $attendanceService)
    {
        parent::__construct();
        $this->calendarService = $calendarService;
        $this->attendanceService = $attendanceService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->handleOptions();

        $month = now()->month;
        if ($this->option('custom')) {
            $month = $this->anticipate('Jadwal di bulan apa?', range(1, 12), $month);
            $this->task('Updating attendances for '.now()->setMonth($month)->locale('id')->monthName, fn () => $this->calendarService->month($month)->update());
        }

        $this->title('List Jadwal Absen di Bulan '.now()->setMonth($month)->locale('id')->monthName);
        if ($this->calendarService->lastUpdate()) {
            $this->line('Terakhir diperbarui: '.$this->calendarService->lastUpdate());
        }

        $this->table(['#', 'id', 'topic', 'when', 'schedule', 'duration'], $this->attendanceService->tableRows());
    }

    protected function handleOptions()
    {
        if ($this->option('latest')) {
            $this->task('Updating attendances', fn () => $this->calendarService->update());
        }

        if ($this->option('today')) {
            $this->attendanceService->today();
        }

        if ($this->option('tomorrow')) {
            $this->attendanceService->tomorrow();
        }

        if ($this->option('upcoming')) {
            $this->attendanceService->upcoming();
        }

        if ($this->option('desc')) {
            $this->attendanceService->orderByDate();
        }
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
