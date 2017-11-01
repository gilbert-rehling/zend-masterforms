<?php

namespace Savve\EventListenerManager\Factory;

use Interop\Container\ContainerInterface;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class EventListenersServiceConfigFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {

        $config = $this->getConfig($container);

        return $config;
    }

    /**
     * Get config array
     *
     * @param ContainerInterface $container
     * @return array
     */
    protected function getConfig (ContainerInterface $container)
    {
        if (!$container->has('Config')) {
            return [];
        }

        $config = $container->get('Config');
        if (!isset($config['event_listeners']) || !is_array($config['event_listeners'])) {
            return [];
        }

        $config = $config['event_listeners'];
        return $config;
    }
}