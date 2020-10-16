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

    public function __construct()
    {
        $this->calendarFile = 'calendar-monthly.json';
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
     * Fetch calendar and save the result into json.
     *
     * @return bool
     */
    public function update($month = null)
    {
        $url = $this->mmp_main.'lib/ajax/service.php?info=core_calendar_get_calendar_monthly_view&sesskey='.$this->getSesskey();
        $calendar = $this->client()->timeout(20)->post($url, [
            [
                'index'      => 0,
                'methodname' => 'core_calendar_get_calendar_monthly_view',
                'args'       => [
                    'year'              => date('Y'),
                    'month'             => $month ?: date('m'),
                    'courseid'          => 1,
                    'categoryid'        => 0,
                    'includenavigation' => false,
                    'mini'              => true,
                ],
            ],
        ]);

        if ($calendar->successful() && str_contains($calendar->header('Content-Type'), 'application/json')) {
            $response = collect(json_decode($calendar->body(), true))->collapse();

            if ($response->get('error')) {
                $exceptionMessage = data_get($response, 'exception.message', 'Error were occured. Try re-login using php mmp login command');

                if (str_contains($exceptionMessage, 'Web service is not available') || str_contains($exceptionMessage, 'expired')) {
                    (new LoginService())->withCredential(config('sister'))->execute();

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
