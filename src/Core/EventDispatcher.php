<?php

namespace Core;

class EventDispatcher
{
    private static $listeners = [];

    public static function listen($eventClass, $listenerClass)
    {
        if (!isset(self::$listeners[$eventClass])) {
            self::$listeners[$eventClass] = [];
        }

        self::$listeners[$eventClass][] = $listenerClass;
    }

    public static function dispatch($event)
    {
        $eventClass = get_class($event);

        if (!isset(self::$listeners[$eventClass])) {
            return;
        }

        foreach (self::$listeners[$eventClass] as $listenerClass) {
            try {
                $listener = new $listenerClass();
                $listener->handle($event);
            } catch (\Exception $e) {
                Logger::exception($e, [
                    'event' => $eventClass,
                    'listener' => $listenerClass
                ]);
            }
        }
    }

    public static function registerDefaults()
    {
        self::listen('Events\\OrderCreated', 'Listeners\\LogOrderCreation');
    }
}
