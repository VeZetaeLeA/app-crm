<?php

namespace Core;

/**
 * Vezetaelea OS - Core Event
 * Base class for all system events.
 */
abstract class Event
{
    protected string $name;
    protected $data;
    protected float $timestamp;

    public function __construct($data = null)
    {
        $this->name = get_called_class();
        $this->data = $data;
        $this->timestamp = microtime(true);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getTimestamp(): float
    {
        return $this->timestamp;
    }
}
