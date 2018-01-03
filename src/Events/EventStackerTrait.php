<?php

namespace Digbang\Utils\Events;

use Illuminate\Contracts\Events\Dispatcher;

class EventStackerTrait
{
    /** @var array */
    protected $eventStack;

    public function fireEvents(Dispatcher $dispatcher): void
    {
        foreach ($this->eventStack as $event) {
            $dispatcher->dispatch($event);
        }
    }

    public function pullEvent()
    {
        return array_shift($this->eventStack);
    }

    final protected function pushEvent($event): void
    {
        if (! is_object($event)) {
            throw new \InvalidArgumentException('Event must be an object');
        }

        $this->eventStack[] = $event;
    }
}
