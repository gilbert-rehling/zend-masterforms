<?php

namespace Masterforms\EventListenerManager;

use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\EventManager\SharedEventManagerInterface;
use Zend\EventManager\SharedListenerAggregateInterface;

abstract class AbstractSharedListenerAggregate implements
        SharedListenerAggregateInterface,
        ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     *
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = [];

    /**
     * Attach one or more listeners
     *
     * Implementors may add an optional $priority argument; the SharedEventManager
     * implementation will pass this to the aggregate.
     *
     * @param SharedEventManagerInterface $events
     */
    abstract public function attachShared (SharedEventManagerInterface $events);

    /**
     * Detach all previously attached listeners
     *
     * @param SharedEventManagerInterface $events
     */
    public function detachShared (SharedEventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $callback) {
            if ($events->detach($callback)) {
                unset($this->listeners[$index]);
            }
        }
    }

    /**
     * Get the main ServiceManager
     *
     * @return \Zend\ServiceManager\ServiceManager
     */
    public function getServiceManager ()
    {
        /* @var $listenerManager \Savve\EventListenerManager\EventListenerManager */
        $listenerManager = $this->getServiceLocator();
        $serviceManager = $listenerManager->getServiceLocator();
        return $serviceManager;
    }
}