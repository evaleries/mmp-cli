<?php

namespace App\Services\Assignment;

use App\Services\CalendarService;
use Closure;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AssignmentService extends CalendarService
{
    /**
     * Options for table().
     *
     * @var array
     */
    protected array $options = [];

    /**
     * Get assignments as a collection.
     *
     * @throws Exception
     *
     * @return Collection
     */
    public function assignments(): Collection
    {
        return $this->getUpcomingEvents()
            ->filter(fn ($event) => $event->get('modulename') === 'assign')
            ->values();
    }

    /**
     * Get assignments as a table rows.
     *
     * @param closure|null $callback
     *
     * @return array
     * @throws Exception
     */
    public function tableRows(?Closure $callback = null): array
    {
        $assignments = $this->assignments();

        if ($callback instanceof Closure) {
            return $assignments->map($callback)->values()->toArray();
        }

        return $assignments->map(function ($event) {
            $due_date = $this->formatTimestamp($event->get('timestart'));
            $description = trim(str_replace(["\r", "\n"], '', strip_tags($event->get('description'))));

            return [
                'index'       => $event->get('id'),
                'topic'       => $event->get('course')->get('fullname'),
                'description' => Str::limit($description, 50),
                'due_date'    => $due_date->format('D, d-m-y H:i A'),
                'when'        => $due_date->diffForHumans(),
            ];
        })
        ->values()
        ->toArray();
    }
}
