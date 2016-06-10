<?php
/**
 * Abstract level of events class
 */

namespace AllClasses\Events;

abstract class Event
{
    abstract public function callback($data);
}