<?php

namespace Core;

/**
 * Vezetaelea OS - Event Dispatcher
 * Manages event registration and broadcasting.
 */
class EventDispatcher
{
    private static array $listeners = [];

    /**
     * Register a listener for an event.
     * 
     * @param string $eventClass Fully qualified class name of the event.
     * @param callable $listener Callback function to execute.
     */
    public static function listen(string $eventClass, callable $listener): void
    {
        self::$listeners[$eventClass][] = $listener;
    }

    /**
     * Dispatch an event to all registered listeners.
     * 
     * @param Event $event The event instance.
     */
    public static function dispatch(Event $event): void
    {
        $eventClass = get_class($event);

        if (isset(self::$listeners[$eventClass])) {
            foreach (self::$listeners[$eventClass] as $listener) {
                call_user_func($listener, $event);
            }
        }

        // Optional: System-wide logging of all events for audit trail
        SecurityLogger::log("EVENT_DISPATCHED", [
            'event' => $eventClass,
            'timestamp' => $event->getTimestamp()
        ]);
    }
}
