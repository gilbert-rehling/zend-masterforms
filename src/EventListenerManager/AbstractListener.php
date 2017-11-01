<?php

namespace Masterforms\EventListenerManager;

use Masterforms\Service\ServiceRetrieverTrait;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\EventManager\EventInterface;

abstract class AbstractListener implements
        ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait, ServiceRetrieverTrait;

    /**
     * Invokable class
     *
     * @param EventInterface $event
     */
    public function __invoke (EventInterface $event)
    {
    }
}