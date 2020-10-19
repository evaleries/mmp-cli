<?php

namespace App\Commands;

use App\Services\Attendance\AttendanceService;
use App\Services\Attendance\SubmitAttendanceService;
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
    protected $signature = 'attend:submit {--course= : id mata kuliah} {--status=Present : Attendance Status}';

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
     * Calendar Service.
     *
     * @var SubmitAttendanceService
     */
    protected $submitAttendanceService;

    /**
     * Assignment Service.
     *
     * @var AttendanceService
     */
    protected $attendanceService;

    /**
     * @param SubmitAttendanceService $submitAttendanceService
     * @param AttendanceService       $attendanceService
     *
     * @throws InvalidArgumentException
     * @throws ExceptionInvalidArgumentException
     * @throws LogicException
     *
     * @return void
     */
    public function __construct(SubmitAttendanceService $submitAttendanceService, AttendanceService $attendanceService)
    {
        parent::__construct();

        $this->submitAttendanceService = $submitAttendanceService;
        $this->attendanceService = $attendanceService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->attendances = $this->attendanceService->attendances()->pluck('instance')->unique();

        if ($this->option('course')) {
            return $this->handleCourse();
        }

        $selectedCourse = $this->choice('ID Matkul', $this->attendances->toArray());
        $this->task('Gathering information from attendance', fn () => $this->submitAttendanceService->prepare($selectedCourse));

        if (!empty($this->submitAttendanceService->attendanceOptions)) {
            $attendanceOptions = $this->submitAttendanceService->attendanceOptions;
            $selectedOption = $this->choice('Pilih status absen', $attendanceOptions, 'Present');

            $optionKey = array_search($selectedOption, $attendanceOptions);

            if (!$optionKey) {
                return $this->error('Invalid option');
            }

            $this->task('Submitting attendance', fn () => $this->submitAttendanceService->execute($optionKey));
        }
    }

    /**
     * WIP.
     *
     * @return void
     */
    protected function handleCourse()
    {
        // $selectedCourse = $this->option('course');
        // if ($this->attendances->contains($selectedCourse)) {
        //     $optionValue = $this->attendanceService->attendanceOptions[array_search('Present', $this->submitAttendanceService->attendanceOptions)];
        //     return $this->submitAttendanceService->prepare($selectedCourse)->execute();
        // } else {
        //     throw new Exception('Invalid course id');
        // }
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
