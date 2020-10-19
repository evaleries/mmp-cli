<?php

namespace App\Services\Attendance;

use App\Services\LoginService;
use App\Traits\AuthenticatedCookie;
use Exception;
use Illuminate\Console\Concerns\InteractsWithIO;

class SubmitAttendanceService
{
    use AuthenticatedCookie, InteractsWithIO;

    /**
     * Default form action.
     *
     * @var string
     */
    private $defaultFormAction = 'https://mmp.unej.ac.id/mod/attendance/attendance.php';

    /**
     * Retrieved sessid.
     *
     * @var string
     */
    private $sessid;

    /**
     * Attendance options.
     * @var mixed
     */
    public $attendanceOptions;

    /**
     * Retrieved form action.
     *
     * @var string
     */
    private $formAction;

    public function __construct()
    {
        $this->output = resolve('console.output');
    }

    /**
     * Extract form data.
     */
    protected function extractFormData($response)
    {
        preg_match_all('/sessid=(.+)\&amp;/m', $response, $sessIds);
        preg_match_all('/amp;sesskey=(.+)"/m', $response, $sesskeys);

        $this->sessid = $sessIds[1][0] ?? null;
        $this->sesskey = $sesskeys[1][0] ?? null;

        if (! $this->sessid || ! $this->sesskey) {
            throw new Exception('Sessid or sesskey is empty. Maybe the class hasn\'t started yet or already over.');
        }

        return $this;
    }

    protected function extractAttendanceOptions($response)
    {
        preg_match_all('/(?=statusdesc">(.*?)<\/)/im', $response, $optionsLabel);
        preg_match_all('/(?=status"\s*value="(.*?)")/im', $response, $optionsValue);

        if (!$optionsLabel[1] || !$optionsValue[1]) {
            throw new Exception('Couldn\'t parse options. Regex failed.');
        }

        return $this->attendanceOptions = array_combine($optionsValue[1], $optionsLabel[1]);
    }

    protected function checkResponse($response)
    {
        if (preg_match('/errormessage/im', $response)) {
            $this->error('Something happened!');
            preg_match_all('/"errormessage">(.*?)<\/p>/im', $response, $errors);
            throw new Exception($errors[1][0] ? html_entity_decode($errors[1][0]) : 'Unknown Error!');
        }

        if (preg_match('/Please log in/im', $response)) {
            $this->info('Session is expired, trying to re-login');
            (new LoginService)->withCredential(config('sister'))->execute();
        }
    }

    public function prepare($courseId)
    {
        $attendanceForm = $this->client()->timeout(15)->get($this->mmp_main . 'mod/attendance/view.php?id=' . $courseId);

        $this->checkResponse($attendanceForm->body());
        $this->saveResponse('attendance-view.html', $attendanceForm->body());
        $this->extractFormData($attendanceForm->body());

        $viewAttendance = $this->client()->get($this->formAction ?: $this->defaultFormAction, [
            'sessid' => $this->sessid,
            'sesskey' => $this->sesskey,
        ]);

        if (!$viewAttendance->successful() || !preg_match('/There are required fields in this form marked/im', $viewAttendance->body())) {
            throw new Exception('Can\'t fetch attendance options form');
        }

        $this->attendanceOptions = $this->extractAttendanceOptions($viewAttendance->body());
        return $this;
    }

    public function execute($status)
    {
        $doAttendance = $this->client()->asForm()->post($this->formAction ?: $this->defaultFormAction, [
            'sessid' => $this->sessid,
            'sesskey' => $this->sesskey,
            'status' => $status,
            'mform_isexpanded_id_session' => 1,
            '_qf__mod_attendance_student_attendance_form' => 1,
        ]);

        $this->saveResponse('attendance-submit.html', $doAttendance->body());

        if ($doAttendance->redirect() || preg_match('/You have selected an invalid status/im', $doAttendance->body())) {
            throw new Exception('Invalid options were sent. Please try again');
        }

        return preg_match('/this session has been recorded/im', $doAttendance->body());
    }
}
