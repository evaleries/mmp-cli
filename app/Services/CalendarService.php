<?php

namespace App\Services;

use App\Traits\AuthenticatedCookie;
use Carbon\Carbon;
use Carbon\Exceptions\NotLocaleAwareException;
use Exception;
use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Translation\Exception\InvalidArgumentException;

class CalendarService
{
    use AuthenticatedCookie;
    use InteractsWithIO;

    /**
     * calendar result file name.
     *
     * @var string
     */
    protected $calendarFile;

    public function __construct()
    {
        $this->calendarFile = 'calendar-monthly.json';
        $this->output = resolve('console.output');
    }

    /**
     * Get calendar full path.
     *
     * @return string
     */
    public function calendarPath()
    {
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
        $calendar = $this->client()->post($url, [
        $calendar = $this->client()->timeout(20)->post($url, [
            [
                'methodname' => 'core_calendar_get_calendar_monthly_view',
                    'includenavigation' => false,
                ],
            ],
        ]);

        if (!preg_match('/Web service is not available/si', $calendar->body())) {
        if ($calendar->successful() && Str::contains($calendar->header('Content-Type'), 'application/json')) {

            $response = collect(json_decode($calendar->body(), true))->collapse();

            if ($response->get('error')) {
                throw new \Exception(data_get($response, 'exception.message', 'Error occured! Try re-login using mmp login'));
            }

            $this->saveResponse($this->calendarFile, $calendar->body());

            return true;
        } else {
        } elseif (preg_match('/Web service is not available/im', $calendar->body())) {
            (new LoginService())->withCredential(config('sister'))->execute();

            return $this->update();
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
