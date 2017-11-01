<?php

namespace Masterforms\BlockManager;

use Masterforms\BlockManager\Block;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;

abstract class BlockAbstractServiceFactory implements
        AbstractFactoryInterface
{

    //public function canCreate(ContainerInterface $container, $requestedName)
    //{
    //    return class_exists($requestedName);
    //}

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
    //    $block = new Block();
    //    $block->setTemplate($requestedName);
    //    $block->setServiceLocator($container);
    //    return $block;
        return new Block();
    }

    /**
     * Determine if we can create a service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return bool
     *//*
    public function canCreateServiceWithName (ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        /* @var $viewManager \Zend\Mvc\View\Http\ViewManager *//*
        $serviceManager = $serviceLocator->getServiceLocator();
        $viewManager = $serviceManager->get('ViewManager');

        $resolver = $viewManager->getResolver();

        // if the block name does not exists, then return false
        if (!$resolver->resolve($requestedName)){
            return false;
        }
        return true;
    }

    /**
     * Create service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return mixed
     *//*
    public function createServiceWithName (ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $block = new Block();
        $block->setTemplate($requestedName);
        $block->setServiceLocator($serviceLocator);
        return $block;
    }
*/
}