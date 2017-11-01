<?php

namespace Savve\EventManager;

use Zend\EventManager\Event as ZendEvent;

class Event extends ZendEvent implements
        EventInterface
{
    const EVENT_INIT = 'init';
    const EVENT_LOAD = 'load';
    const EVENT_SAVE = 'save';
    const EVENT_ADD = 'add';
    const EVENT_CREATE = 'create';
    const EVENT_CREATED = 'created';
    const EVENT_EDIT = 'edit';
    const EVENT_UPDATE = 'update';
    const EVENT_UPDATED = 'updated';
    const EVENT_DELETE = 'delete';
    const EVENT_DIRECTORY = 'directory';
    const EVENT_STATUS = 'status';
    const EVENT_ACTIVATE = 'activate';
    const EVENT_ACTIVATED = 'activated';
    const EVENT_DEACTIVATE = 'deactivate';
    const EVENT_DEACTIVATED = 'deactivated';

    /**
     * Get the event names
     * @return array
     */
    static public function getEventNames ()
    {
        $reflection = new \ReflectionClass(get_called_class());
        $constants = $reflection->getConstants();

        // retrieve only the constants that have "EVENT_" as prefix
        $keys = preg_grep('/^EVENT_/i', array_keys($constants));
        $keys = array_combine($keys, $keys);

        // intersect the keys with the constants
        $constants = array_intersect_key($constants, $keys);

        return $constants;
    }
}