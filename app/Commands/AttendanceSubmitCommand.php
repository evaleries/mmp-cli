<?php

namespace App\Commands;

use App\Services\Attendance\AttendanceService;
use App\Services\Attendance\SubmitAttendanceService;
use Exception;
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
    protected Collection $attendances;

    /**
     * Submit Attendance Service.
     *
     * @var SubmitAttendanceService
     */
    protected SubmitAttendanceService $submitAttendance;

    /**
     * Assignment Service.
     *
     * @var AttendanceService
     */
    protected AttendanceService $attendanceService;

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
     * @return int|void
     * @throws Exception
     */
    public function handle()
    {
        $this->attendances = (
            $this->option('all')
            ? $this->attendanceService->attendances()
            : $this->attendanceService->today()
        )->pluck('instance')->unique();

        if ($this->attendances->isEmpty()) {
            $this->info('No attendance for today. Add --all to list all attendances');
            return 0;
        }

        if ($this->option('course')) {
            $this->handleCourse();
            return 0;
        }

        $selectedCourse = $this->choice('ID Matkul', $this->attendances->toArray());
        $this->task('Gathering information from attendance', fn () => $this->submitAttendance->prepare($selectedCourse));

        if (!empty($this->submitAttendance->attendanceOptions)) {
            $attendanceOptions = $this->submitAttendance->attendanceOptions;
            $selectedOption = $this->choice('Pilih status absen', $attendanceOptions, 'Present');

            $optionKey = array_search($selectedOption, $attendanceOptions);

            if (!$optionKey) {
                $this->error('Invalid option');
                return -1;
            }

            $this->task('Submitting attendance', fn () => $this->submitAttendance->execute($optionKey));
        }
    }

    /**
     * Handle given options | no interactive.
     *
     * @return void
     * @throws Exception
     */
    protected function handleCourse()
    {
        $selectedCourse = $this->option('course');
        if (!$this->attendances->contains($selectedCourse)) {
            throw new Exception('Invalid course id');
        }

        $this->task('Gathering attendance information', fn () => $this->submitAttendance->prepare($selectedCourse));

        $attendStatus = array_search($this->option('status'), $this->submitAttendance->attendanceOptions);
        if (!$attendStatus) {
            throw new Exception('Invalid attendance option. Given: '.$this->option('status'));
        }

        $this->task('Submitting attendance', fn () => $this->submitAttendance->execute($attendStatus));
    }
}
