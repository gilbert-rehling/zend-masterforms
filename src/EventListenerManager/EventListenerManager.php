<?php

namespace Masterforms\EventListenerManager;

use Masterforms\ServiceManager\Plugin\AbstractPluginManager;

use Zend\ServiceManager\ConfigInterface;
use RuntimeException;
use Zend\ServiceManager\Exception\InvalidServiceException;
use Zend\Stdlib\InitializableInterface;

//use Zend\ServiceManager\PluginInterface;

class EventListenerManager extends AbstractPluginManager
{

    /**
     * EventListenerManager constructor.
     *
     * @param \Interop\Container\ContainerInterface|null|ConfigInterface $configInstanceOrParentLocator
     * @param array $config
     *//*
    public function __construct($configInstanceOrParentLocator, array $config)
    {
        parent::__construct($configInstanceOrParentLocator, $config);
    }*/

    /**
     * @param object $instance
     */
    public function validate($instance)
    {
        if (! $instance instanceof $this->instanceOf) {
            throw new InvalidServiceException(sprintf(
                'Invalid plugin "%s" created; not an instance of %s',
                get_class($instance),
                $this->instanceOf
            ));
        }
        $instance->init();
    }

    /**
     * Validate the plugin
     *
     * Checks that the plugin loaded is either a valid callback or an instance of GuardProviderInterface.
     *
     * @param mixed $plugin
     * @return void
     * @throws RuntimeException if invalid
     */
    public function validatePlugin ($plugin)
    {
        // Hook to perform various initialization, when the element is not created through the factory
     //   if ($plugin instanceof InitializableInterface) {
     //       $plugin->init();
     //   }
        try {
            $this->validate($plugin);
        } catch (InvalidServiceException $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        // since this is an extension of the Zend\ServiceManager\ServiceManager,
        // everything is great! This also allows using peering service
        return;
    }
}