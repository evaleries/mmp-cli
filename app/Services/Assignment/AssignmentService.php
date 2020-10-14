<?php

namespace App\Services\Assignment;

use App\Services\CalendarService;
use Carbon\Exceptions\NotLocaleAwareException;
use Closure;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\Translation\Exception\InvalidArgumentException;

class AssignmentService extends CalendarService
{

    /**
     * Options for table()
     *
     * @var array
     */
    protected $options = [];

    /**
     * Get assignments as a collection
     *
     * @return Collection
     * @throws Exception
     */
    public function assignments(): Collection
    {
        return $this->getUpcomingEvents()
            ->filter(fn($event) => $event->get('modulename') === 'assign')
            ->values();
    }

    /**
     * Get assignments as a table rows
     *
     * @param closure $callback
     * @return array
     */
    public function tableRows($callback = null)
    {
        $assignments = $this->assignments();

        if ($callback instanceof Closure) {
            return $assignments->map($callback)->values()->toArray();
        }

        return $assignments->map(function ($event) {
            $due_date = $this->formatTimestamp($event->get('timestart'));
            $description = strip_tags($event->get('description'));
            return [
                'index' => $event->get('id'),
                'topic' => $event->get('course')->get('fullname'),
                'description' => Str::limit($description, 50),
                'due_date' => $due_date->format('D, d-m-y H:i A'),
                'when' => $due_date->diffForHumans(),
            ];
        })
        ->values()
        ->toArray();
    }
}
