<?php

namespace App\Services\Attendance;

use App\Services\CalendarService;
use Closure;
use Exception;
use Illuminate\Support\Collection;

class AttendanceService extends CalendarService
{
    /**
     * List of attendance.
     *
     * @var Collection
     */
    private Collection $attendanceList;

    /**
     * Get attendances from upcoming events.
     *
     * @throws Exception
     *
     * @return Collection
     */
    public function attendances(): Collection
    {
        return $this->attendanceList = $this->getUpcomingEvents()
            ->filter(fn ($event) => $event->get('modulename') === 'attendance' || $event->get('eventtype') === 'attendance')
            ->values();
    }

    /**
     * Retrieve today's attendance.
     *
     * @return Collection
     * @throws Exception
     */
    public function today(): Collection
    {
        return $this->attendanceList = $this->attendances()
            ->filter(fn ($event) => $this->formatTimestamp($event->get('timestart'))->isCurrentDay())
            ->values();
    }

    /**
     * Retrieve tommorrow's attendance.
     *
     * @return Collection
     * @throws Exception
     */
    public function tomorrow(): Collection
    {
        return $this->attendanceList = $this->attendances()
            ->filter(fn ($event) => $this->formatTimestamp($event->get('timestart'))->isNextDay())
            ->values();
    }

    /**
     * Retrieve upcoming attendances.
     *
     * @return Collection
     * @throws Exception
     */
    public function upcoming(): Collection
    {
        return $this->attendanceList = $this->attendances()
            ->filter(fn ($event) => !$this->formatTimestamp($event->get('timestart'))->isPast())
            ->values();
    }

    /**
     * Re-order the attendance list with date.
     *
     * @return Collection
     * @throws Exception
     */
    public function orderByDate(): Collection
    {
        return $this->attendanceList = $this->attendanceList ?: $this->attendances()
            ->reverse();
    }

    /**
     * Get attendances as a table rows.
     *
     * @param Closure|null $callback
     *
     * @return array
     * @throws Exception
     */
    public function tableRows(?Closure $callback = null)
    {
        $attendances = $this->attendanceList ?? $this->attendances();

        if ($callback instanceof Closure) {
            return $attendances->map($callback)->values()->toArray();
        }

        return $attendances
            ->map(function ($event) {
                $schedule = $this->formatTimestamp($event->get('timestart'));

                return [
                    'index'    => $event->get('id'),
                    'id'       => $event->get('instance'),
                    'topic'    => $event->get('course')->get('fullname'),
                    'when'     => $schedule->isCurrentDay() ? 'Hari ini' : ($schedule->greaterThan(now()) ? $schedule->diffForHumans() : '-'),
                    'tanggal'  => $schedule->format('D, d-m-y H:i A'),
                    'duration' => str_replace('&raquo;', '-', strip_tags($event->get('formattedtime'))),
                ];
            })
            ->values()
            ->toArray();
    }
}
