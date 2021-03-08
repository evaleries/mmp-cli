<?php

namespace App\Services;

use App\Traits\AuthenticatedCookie;
use Carbon\Carbon;
use Carbon\Exceptions\NotLocaleAwareException;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Translation\Exception\InvalidArgumentException;

class CalendarService
{
    use AuthenticatedCookie;

    /**
     * calendar result file name.
     *
     * @var string
     */
    protected $calendarFile;

    /**
     * Customize month or use current month.
     *
     * @var int
     */
    protected $customMonth;

    public function __construct()
    {
        $this->calendarFile = 'calendar-monthly.json';
        $this->customMonth = date('m');
    }

    /**
     * Get calendar full path.
     *
     * @return string
     */
    public function calendarPath()
    {
        return Storage::path('responses/'.$this->calendarFile);
    }

    /**
     * Get a human-friendly version of file last changed timestamp.
     *
     * @throws NotLocaleAwareException
     * @throws InvalidArgumentException
     * @throws Exception
     *
     * @return string|null
     */
    public function lastUpdate()
    {
        return File::exists($this->calendarPath())
        ? Carbon::createFromTimestamp(Storage::lastModified('responses/'.$this->calendarFile), config('app.timezone'))
            ->locale('id')
            ->diffForHumans()
        : null;
    }

    /**
     * Customize month for update the calendar.
     *
     * @param int $customMonth
     *
     * @return CalendarService
     */
    public function month($customMonth): CalendarService
    {
        $this->customMonth = $customMonth;

        return $this;
    }

    /**
     * Fetch calendar and save the result into json.
     *
     * @return bool
     */
    public function update()
    {
        $url = $this->mmp_main.'lib/ajax/service.php?info=core_calendar_get_calendar_monthly_view&sesskey='.$this->getSesskey();
        $calendar = $this->client()->post($url, [
            [
                'index'      => 0,
                'methodname' => 'core_calendar_get_calendar_monthly_view',
                'args'       => [
                    'year'              => intval(date('Y')),
                    'month'             => intval($this->customMonth),
                    'courseid'          => 1,
                    'categoryid'        => 0,
                    'includenavigation' => 0,
                    'mini'              => true,
                ],
            ],
        ]);

        if ($calendar->successful() && str_contains($calendar->header('Content-Type'), 'application/json')) {
            $response = collect(json_decode($calendar->body(), true))->collapse();

            if ($response->get('error')) {
                $errorMessage = data_get($response, 'exception.message', 'Error were occured. Try re-login using php mmp login command');

                if (str_contains($errorMessage, 'Web service is not available') || str_contains($errorMessage, 'expired') || str_contains($errorMessage, 'Please log in again')) {
                    LoginService::relogin();

                    return $this->update();
                }

                throw new \Exception(data_get($response, 'exception.message', 'Error occured! Try re-login using mmp login'));
            }

            $this->saveResponse($this->calendarFile, $calendar->body());

            return true;
        }

        $calendar->throw();

        return false;
    }

    /**
     * Get upcoming.
     *
     * @return Collection
     */
    public function getUpcomingEvents()
    {
        if (!File::exists($this->calendarPath())) {
            throw new Exception('Please execute the update() / login first to fetch the calendar.');
        }

        return collect(json_decode(Storage::get('responses/'.$this->calendarFile), true))
            ->collapse()
            ->pluck('weeks.*.days.*.events')
            ->collapse()
            ->collapse()
            ->recursive();
    }

    /**
     * Helper for timestamp.
     *
     * @todo Refactor to helper
     *
     * @param string|int $timestamp
     *
     * @return Carbon
     */
    public function formatTimestamp($timestamp): Carbon
    {
        return Carbon::createFromTimestamp($timestamp, config('app.timezone'))->locale('id');
    }
}
