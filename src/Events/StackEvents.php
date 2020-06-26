<?php

namespace Digbang\Utils\Events;

use Illuminate\Contracts\Events\Dispatcher;

interface StackEvents
{
    public function fireEvents(Dispatcher $dispatcher): void;

    public function pullEvent();
}
