<?php
namespace Masterforms\Service\Factory;

use Interop\Container\ContainerInterface;

use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\Session\SessionManager;
use Masterforms\Session\Container;

use Masterforms\Service\MasterformsService;

/**
 * The factory responsible for creating of Masterforms service.
 */
class MasterformsServiceFactory implements FactoryInterface
{
    /**
     * This method creates the Masterforms\Service\MasterformsService service
     * and returns its instance.
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager      = $container->get('doctrine.entitymanager.orm_default');
        $sessionManager     = $container->get(SessionManager::class);
        $masterformsStorage = new Container('MasterformsStorage', $sessionManager);

        $config = $container->get('Config');
        if (isset($config['masterforms_options'])) {
            $config = $config['masterforms_options'];
        } else {
            $config = [];
        }

        // Create the service and inject dependencies into its constructor.
        return new MasterformsService( $entityManager, $config, $masterformsStorage );
    }
}