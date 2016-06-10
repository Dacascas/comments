<?php

/**
 * EventManager class to encapsulation all events
 */

namespace EventManager;

use AllClasses\Observers;
use AllClasses\Store;

class EventManager
{
    public static $instance = null;
    private $_listeners = array();

    /**
     * @return EventManager
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * BLOCK CLONE MAGIC METHOD
     */
    private function __clone() {}
    
    /**
     * BLOCK CONSTRUCT MAGIC METHOD and do what we need to do
     */
    private function __construct()
    {
        $events = (new Observers(Store::getInstance()))->getAll();
        
        foreach ($events as $event) {
            $class = 'AllClasses\Events\\' .  $event;
            $this->on($event, new $class);
        }
    }

    /**
     * @param string $event_name
     * @param array $data
     * @return EventManager
     */
    public function emit($event_name, array $data = array())
    {
        $listener = $this->getListener($event_name);

        if (!$listener) {
            return $this;
        }

        foreach ($listener as $event) {
            $event['callback']->callback($data);
        }

        return $this;
    }

    /**
     *
     * @param string $event_name
     * @param mixed $callback
     * @return EventManager
     */
    public function on($event_name, $callback)
    {
        return $this->registerEvent($event_name, $callback);
    }

    /**
     *
     * @param string $event_name
     * @return EventManager
     */
    public function detach($event_name)
    {
        return $this->deRegisterEvent($event_name);
    }

    /**
     * @param $event_name
     * @param $callback
     * @return $this
     */
    public final function registerEvent($event_name, $callback)
    {
        $event_name = trim($event_name);

        if (!isset($this->_listeners[$event_name])) {
            $this->_listeners[$event_name] = array();
        }

        $event = array(
            'event_name' => $event_name,
            'callback' => $callback
        );

        array_push($this->_listeners[$event_name], $event);

        return $this;
    }

    /**
     *
     * @param string $event_name
     * @return EventManager
     */
    public final function deRegisterEvent($event_name)
    {
        if (isset($this->_listeners[$event_name])) {
            unset($this->_listeners[$event_name]);
        }
        return $this;
    }

    /**
     *
     * @return array
     */
    public function getListeners()
    {
        return $this->_listeners;
    }

    /**
     * @param $listener
     * @return bool | array
     */
    public function getListener($listener)
    {
        if (isset($this->_listeners[$listener])) {
            return $this->_listeners[$listener];
        }
        return false;
    }
}