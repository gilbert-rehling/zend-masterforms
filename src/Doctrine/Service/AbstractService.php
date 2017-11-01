<?php
namespace Masterforms\Doctrine\Service;

use Masterforms\Doctrine\ORM\EntityManagerAwareTrait;
use Masterforms\Doctrine\ORM\EntityManagerAwareInterface;
use Masterforms\Doctrine\Service\ServiceInterface;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

use Zend\EventManager\Event;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManagerAwareTrait;
use Zend\EventManager\EventManagerAwareInterface;

abstract class AbstractService implements
    EventManagerAwareInterface,
    EntityManagerAwareInterface,
    ServiceInterface
{
    use EventManagerAwareTrait, EntityManagerAwareTrait;

    /**
     * Constructor
     *
     * @param EntityManager $entityManager
     */
    public function __construct (EntityManager $entityManager)
    {
        $this->setEntityManager($entityManager);
        $this->setEventIdentifiers();
    }

    /**
     * Set the event identifiers for the event manager
     *
     * @return AbstractService
     */
    protected function setEventIdentifiers ()
    {
        $identifiers = [];

        // get all the parent class names from the child class
        $parentClasses = class_parents($this);
        $parentClasses = array_reverse($parentClasses);
        foreach ($parentClasses as $parent) {
            $identifiers[] = $parent;
        }

        // get the namespace of this class
        $parentNamespace = substr(get_parent_class($this), 0, strrpos(get_parent_class($this), '\\'));
        $identifiers[] = $parentNamespace;

        // get the calling class's namespace
        $childNamespace = substr(get_class($this), 0, strrpos(get_class($this), '\\'));
        $identifiers[] = $childNamespace;

        // module namespace
        $moduleNamespace = substr(get_class($this), 0, strpos(get_class($this), '\\'));
        $identifiers[] = $moduleNamespace;

        $eventManager = $this->getEventManager();
        $eventManager->setIdentifiers(array_unique(array_merge($identifiers, $eventManager->getIdentifiers())));

        return $this;
    }

    /**
     * Triggers event listeners
     *
     * @return mixed
     */
    public function triggerListeners (Event $event)
    {
        if ($eventManager = $this->getEventManager()) {
            $eventManager->trigger($event->getName(), $event);
        }
        return $this;
    }

    /**
     * Get the RouteMatch instance
     *
     * @return \Zend\Router\Http\RouteMatch
     */
    public function routeMatch ()
    {
        /* @var $serviceManager \Zend\ServiceManager\ServiceManager */
        $serviceManager = $this->getServiceLocator();
        return $serviceManager->get('Application')
            ->getMvcEvent()
            ->getRouteMatch();
    }

    /**
     * Alias of self::routeMatch
     *
     * @return \Zend\Router\Http\RouteMatch
     */
    public function getRouteMatch ()
    {
        return $this->routeMatch();
    }
}