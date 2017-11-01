<?php
namespace Masterforms\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

use Masterforms\Controller\MasterformsController;
use Masterforms\Service\MasterformsService;

/**
 * This is the factory for MasterformsController. Its purpose is to instantiate the
 * controller.
 */
class MasterformsControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $masterformsService = $container->get(MasterformsService::class);

        return new MasterformsController($masterformsService);
    }
}