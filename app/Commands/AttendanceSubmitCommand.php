<?php

namespace App\Commands;

use App\Services\Attendance\AttendanceService;
use App\Services\Attendance\SubmitAttendanceService;
use Exception;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException as ExceptionInvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;

class AttendanceSubmitCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'attend:submit {--course= : id mata kuliah} {--status=Present : Attendance Status} {--all}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Submit an attendance for selected course';

    /**
     * List of attendance Ids.
     *
     * @var Collection
     */
    protected $attendances;

    /**
     * Submit Attendance Service.
     *
     * @var SubmitAttendanceService
     */
    protected $submitAttendance;

    /**
     * Assignment Service.
     *
     * @var AttendanceService
     */
    protected $attendanceService;

    /**
     * @param SubmitAttendanceService $submitAttendance
     * @param AttendanceService       $attendanceService
     *
     * @throws InvalidArgumentException
     * @throws ExceptionInvalidArgumentException
     * @throws LogicException
     *
     * @return void
     */
    public function __construct(SubmitAttendanceService $submitAttendance, AttendanceService $attendanceService)
    {
        parent::__construct();

        $this->submitAttendance = $submitAttendance;
        $this->attendanceService = $attendanceService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->attendances = ($this->option('all')
            ? $this->attendanceService->attendances()
            : $this->attendanceService->today()
        )->pluck('instance')->unique();

        if ($this->attendances->isEmpty()) {
            return $this->info('No attendance for today. Add --all to list all attendances');
        }

        if ($this->option('course')) {
            return $this->handleCourse();
        }

        $selectedCourse = $this->choice('ID Matkul', $this->attendances->toArray());
        $this->task('Gathering information from attendance', fn () => $this->submitAttendance->prepare($selectedCourse));

        if (!empty($this->submitAttendance->attendanceOptions)) {
            $attendanceOptions = $this->submitAttendance->attendanceOptions;
            $selectedOption = $this->choice('Pilih status absen', $attendanceOptions, 'Present');

            $optionKey = array_search($selectedOption, $attendanceOptions);

            if (!$optionKey) {
                return $this->error('Invalid option');
            }

            $this->task('Submitting attendance', fn () => $this->submitAttendance->execute($optionKey));
        }
    }

    /**
     * Handle given options | no interactive.
     *
     * @return void
     */
    protected function handleCourse()
    {
        $selectedCourse = $this->option('course');
        if (!$this->attendances->contains($selectedCourse)) {
            throw new Exception('Invalid course id');
        }

        $attendStatus = array_search($this->option('status'), $this->submitAttendance->attendanceOptions);
        if (!$attendStatus) {
            throw new Exception('Invalid attendance option. Given: '.$this->option('status'));
        }

        $this->submitAttendance
            ->prepare($selectedCourse)
            ->execute($attendStatus);
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
