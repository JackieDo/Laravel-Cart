<?php

namespace Jackiedo\Cart\Traits;

/**
 * The FireEvent traits.
 *
 * @package Jackiedo\Cart
 *
 * @author  Jackie Do <anhvudo@gmail.com>
 */
trait FireEvent
{
    /**
     * Fire an event and call the listeners.
     *
     * @param object|string $event
     * @param mixed         $payload
     * @param bool          $halt
     *
     * @return null|array
     */
    protected function fireEvent($event, $payload = [], $halt = true)
    {
        return event($event, $payload, $halt);
    }
}
