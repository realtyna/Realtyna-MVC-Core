<?php

namespace Realtyna\MvcCore;

use ReflectionClass;

class Event
{
    public static $hook;
    public static $event;

    /**
     * @throws \ReflectionException
     */
    public static function trigger(string $event, ...$args)
    {
        self::$hook = "realtyna_event_hook_" . $event;
        $reflector = new ReflectionClass($event);
        $event = $reflector->newInstanceArgs($args);
        self::$event = [$event];

        add_action('shutdown', [self::class, 'schedule']);
    }

    public static function schedule()
    {
        $group = 'events';
        as_enqueue_async_action(self::$hook, self::$event, $group);
    }

}