<?php

namespace Masterforms\Doctrine\Factory\Service;

use Masterforms\Doctrine\ORM\EntityManager;
use DoctrineORMModule\Service\EntityManagerFactory as AbstractFactory;
use Zend\ServiceManager\ServiceLocatorInterface;

class EntityManagerFactory extends AbstractFactory
{

    /**
     * {@inheritDoc}
     *
     * @return EntityManager
     */
    public function createService (ServiceLocatorInterface $serviceLocator)
    {
        /* @var $options \DoctrineORMModule\Options\EntityManager */
        $options = $this->getOptions($serviceLocator, 'entitymanager');
        $connection = $serviceLocator->get($options->getConnection());
        $config = $serviceLocator->get($options->getConfiguration());

        // initializing the resolver
        // @todo should actually attach it to a fetched event manager here, and not
        // rely on its factory code
        $entityResolver = $serviceLocator->get($options->getEntityResolver());

        return EntityManager::create($connection, $config);
    }
}