<?php
namespace Masterforms\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

use Masterforms\Controller\MasterformsHelpController;
use Masterforms\Service\MasterformsSetupService;

/**
 * This is the factory for MasterformsSetupController. Its purpose is to instantiate the
 * controller.
 */
class MasterformsSetupControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $masterformsService = $container->get(MasterformsSetupService::class);

        return new MasterformsSetupController($masterformsService);
    }
}