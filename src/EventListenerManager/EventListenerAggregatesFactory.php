<?php

namespace Masterforms\EventListenerManager;

use Interop\Container\ContainerInterface;

use Zend\Stdlib\ArrayUtils;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

use Masterforms\EventListenerManager\EventListenerManager as ListenerManager;

class EventListenerAggregatesFactory implements FactoryInterface
{
    /**
     * This is the factory for ListenerManagers. Its purpose is to return all listeners.
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return array
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $pluginManager = $container->get('Savve\EventListenerManager\EventListenerManager');//(ListenerManager::class);

        $listeners = [];
        if (method_exists($pluginManager, 'getCanonicalNames')) {
            foreach ($pluginManager->getCanonicalNames() as $name => $item) {
                $listeners = ArrayUtils::merge($listeners, [$pluginManager->get($name)]);
            }
        }

        return $listeners;
    }
}
