<?php

namespace Limelight\Events;

use Limelight\Exceptions\EventErrorException;

class Dispatcher
{
    /**
     * Registered listeners.
     *
     * @var array
     */
    protected $registeredListeners = [];

    /**
     * Construct.
     *
     * @param array $configListeners
     */
    public function __construct(array $configListeners)
    {
        $this->addAllListeners($configListeners);
    }

    /**
     * Add array of listeners from config.
     *
     * @param array $configListeners
     */
    public function addAllListeners(array $configListeners)
    {
        array_walk($configListeners, [$this, 'addListeners']);
    }

    /**
     * Add a single listener.
     *
     * @param LimelightListener|array $listeners
     * @param string                  $eventName
     */
    public function addListeners($listeners, $eventName)
    {
        $listeners = (is_array($listeners) ? $listeners : [$listeners]);

        array_walk($listeners, function ($listener) use ($eventName) {
            if (is_string($listener) && !class_exists($listener)) {
                throw new EventErrorException("Class {$listener} does not exist.");
            }

            $this->registeredListeners[$eventName][] = new $listener();
        });
    }

    /**
     * Get all registered listeners.
     *
     * @return array
     */
    public function getListeners()
    {
        return $this->registeredListeners;
    }

    /**
     * Call handle method on all listeners for event.
     *
     * @param string $eventName
     * @param mixed  $payload
     *
     * @return mixed
     */
    public function fire($eventName, $payload = null)
    {
        if (isset($this->registeredListeners[$eventName])) {
            $listeners = $this->registeredListeners[$eventName];

            return array_map(function (LimelightListener $listener) use ($payload) {
                return $listener->handle($payload);
            }, $listeners);
        }

        return false;
    }
}