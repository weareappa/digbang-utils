<?php

namespace Digbang\Utils\Events;

use Illuminate\Contracts\Events\Dispatcher;

interface EventStacker
{
    public function fireEvents(Dispatcher $dispatcher): void;

    public function pullEvent();
}
