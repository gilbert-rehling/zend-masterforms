<?php

namespace Masterforms\EventListenerManager;

use Interop\Container\ContainerInterface;

use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

use Masterforms\EventListenerManager\EventListenerManager as ListenerManager;
use Masterforms\Stdlib;

class EventListenerManagerFactory implements FactoryInterface
{

    /**
     *  This is the factory for EventListenerManager. Its purpose is to instantiate the controller.
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return \Masterforms\EventListenerManager\EventListenerManager
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $this->getConfig($container);
        $config = new Config($config);
        return new ListenerManager($container, Stdlib\ObjectUtils::toArray($config));
    }

    /**
     * Get config array
     *
     * @return array
     */
    protected function getConfig (ContainerInterface $container)
    {
        if (!$container->has('Config')) {
            return [];
        }
    //    $config->merge($container->get('Config'));

        $config = $container->get('Config');

        if (!isset($config['listener_manager']) || !is_array($config['listener_manager'])) {
            return [];
        }
        $config = $config['listener_manager'];

        return $config;
    }
}