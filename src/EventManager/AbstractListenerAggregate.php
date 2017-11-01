<?php

namespace Savve\EventManager;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Savve\Service\ServiceRetrieverTrait;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\EventManager\AbstractListenerAggregate as ZendListenerAggregate;
use Zend\EventManager\EventManagerInterface;

/**
 * Convenience methods
 *
 * @method \Savve\Service\AbstractService service() service($serviceName)
 * @method \Savve\Form\Form getForm() getForm($formName)
 * @method \Savve\InputFilter\InputFilter inputFilter() inputFilter($inputFilterName)
 * @method \Savve\Hydrator\AbstractHydrator|\Savve\Hydrator\AggregateHydrator hydrator() hydrator($hydratorName)
 * @method \Savve\Entity\ArrayObject entity() entity($entityName)
 * @method \Savve\Table\TableGateway table() table($tableName)
 * @method \Savve\Filter\AbstractFilter getFilter() getFilter($filterName)
 * @method \Savve\Validator\AbstractValidator getValidator() getValidator($validatorName)
 * @method \Savve\View\Model\ViewModel block() block($blockName)
 * @method \Savve\Session\Container getSession() getSession($namespace)
 * @method \Zend\Config\Config|array config() config($configName)
 * @method \Zend\ServiceManager\ServiceManager getServiceManager()
 * @method \Zend\Mvc\Application getApplication()
 * @method \Zend\Mvc\Router\Http\RouteMatch routeMatch()
 * @method \Zend\Mvc\Router\Http\TreeRouteStack getRouter()
 * @method \Zend\I18n\Translator\Translator getTranslator()
 * @method \Zend\Stdlib\Parameters getPost() getPost($type)
 * @method \Zend\Mvc\Controller\Plugin\FlashMessenger flashMessenger()
 * @method \Zend\Mvc\Controller\Plugin\Redirect redirect()
 */
abstract class AbstractListenerAggregate extends ZendListenerAggregate implements
        ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;
    use ServiceRetrieverTrait;

    /**
     * Attach one or more listeners
     *
     * @see \Zend\EventManager\ListenerAggregateInterface::attach()
     */
    abstract public function attach (EventManagerInterface $event);
}