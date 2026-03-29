<?php

namespace Tests\Unit;

use Tests\TestCase;
use Core\EventDispatcher;
use Core\Event;

class ExampleEvent extends Event
{
}

class EventDispatcherTest extends TestCase
{
    public function test_it_can_dispatch_events_and_trigger_listeners()
    {
        $triggered = false;
        $eventData = ['id' => 123];

        EventDispatcher::listen(ExampleEvent::class, function ($event) use (&$triggered, $eventData) {
            $triggered = true;
            $this->assertEquals($eventData, $event->getData());
        });

        $event = new ExampleEvent($eventData);
        EventDispatcher::dispatch($event);

        $this->assertTrue($triggered, 'The listener was not triggered.');
    }
}
