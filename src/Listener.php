<?php

namespace Realtyna\MvcCore;


abstract class Listener
{
    public function __construct()
    {
        add_action('realtyna_event_hook_' . $this->event(), [$this, 'handle']);
    }

    abstract public function event():string;
    abstract public function handle($args);
}