<?php

namespace App\Services\Attendance;

use App\Services\CalendarService;
use Closure;
use Illuminate\Support\Collection;

class AttendanceService extends CalendarService
{
    /**
     * Get attendances from upcoming events
     * @return Collection
     * @throws Exception
     */
    public function attendances(): Collection
    {
        return $this->getUpcomingEvents()
            ->filter(fn($event) => $event->get('modulename') === 'attendance' || $event->get('eventtype') === 'attendance')
            ->values();
    }

    /**
     * Get attendances as a table rows
     *
     * @param Closure|null $callback
     * @return array
     */
    public function tableRows($callback = null)
    {
        $attendances = $this->attendances();

        if ($callback instanceof Closure) {
            return $attendances->map($callback)->values()->toArray();
        }

        return $attendances
            ->map(function ($event) {
                $schedule = $this->formatTimestamp($event->get('timestart'));
                return [
                    'index' => $event->get('id'),
                    'id' => $event->get('instance'),
                    'topic' => $event->get('course')->get('fullname'),
                    'when' => $schedule->isCurrentDay() ? 'Hari ini' : ($schedule->greaterThan(now()) ? $schedule->diffForHumans() : '-'),
                    'tanggal' => $schedule->format('D, d-m-y H:i A'),
                    'duration' => str_replace('&raquo;', '-', strip_tags($event->get('formattedtime'))),
                ];
            })
            ->values()
            ->toArray();
    }
}
