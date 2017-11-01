<?php

namespace Masterforms\EventManager;

use Traversable;
use Zend\EventManager\Event as ZendEvent;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;

use Zend\EventManager\EventInterface;

trait EventManagerAwareTrait
{

    /**
     * Instance of Zend\EventManager\EventManagerInterface
     *
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     * Declare the event identifiers used by the calling class
     *
     * @var mixed
     */
    protected $eventIdentifier;

    /**
     * Retrieve the event manager
     *
     * Lazy-loads an EventManager instance if none registered.
     *
     * @return EventManager
     */
    public function getEventManager ()
    {
        if (!$this->eventManager instanceof EventManagerInterface) {
            $this->setEventManager(new EventManager());
        }
        return $this->eventManager;
    }

    /**
     * Set the event manager instance used by this context
     *
     * @param EventManagerInterface $events
     * @return mixed
     */
    public function setEventManager (EventManagerInterface $events)
    {
        if (method_exists($this, 'getEventIdentifier')) {
            $identifiers = $this->getEventIdentifier();
        }
        else {
            $identifiers = $this->getDefaultEventIdentifier();
        }

        if (isset($this->eventIdentifier)) {
            if ((is_string($this->eventIdentifier)) || (is_array($this->eventIdentifier)) || ($this->eventIdentifier instanceof Traversable)) {
                $identifiers = array_unique(array_merge($identifiers, (array) $this->eventIdentifier));
            }
            elseif (is_object($this->eventIdentifier)) {
                $identifiers[] = $this->eventIdentifier;
            }
            // silently ignore invalid eventIdentifier types
        }
        $events->setIdentifiers($identifiers);
        $this->eventManager = $events;

        if (method_exists($this, 'attachDefaultListeners')) {
            $this->attachDefaultListeners();
        }

        return $this;
    }

    /**
     * Triggers event listeners
     *
     * @param ZendEvent $event
     * @return \Zend\EventManager\ResponseCollection
     */
    public function triggerListeners (ZendEvent $event)
    {
        $eventManager = $this->getEventManager();
        if ($eventManager) {
         //   return $eventManager->trigger($event);
            return $eventManager->trigger($event->getName(), null, $event->getParams());
        }
    }

    /**
     * Get all the event manager identifiers
     *
     * @return array
     */
    public function getDefaultEventIdentifier ()
    {
        $identifiers = [
            'Zend\Mvc\Application'
        ];

        // get all the interfaces of the current calling class
        $interfaces = class_implements(get_called_class());
        $interfaces = array_reverse($interfaces);
        foreach ($interfaces as $interface) {
            $identifiers[] = $interface;
        }

        // get all the parent class names from the child class
        $parentClasses = class_parents($this);
        $parentClasses = array_reverse($parentClasses);
        foreach ($parentClasses as $parent) {
            $identifiers[] = $parent;
        }

        // add the module name as an identifier
        array_push($identifiers, __CLASS__, get_called_class(), substr(get_parent_class($this), 0, strrpos(get_parent_class($this), '\\')), substr(get_class($this), 0, strrpos(get_class($this), '\\')), substr(get_class($this), 0, strpos(get_class($this), '\\')));
        $identifiers = array_unique($identifiers);
        return $identifiers;
    }
}