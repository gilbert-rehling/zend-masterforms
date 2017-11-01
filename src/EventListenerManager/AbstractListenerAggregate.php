<?php

namespace Masterforms\EventListenerManager;

use Masterforms\Service\ServiceRetrieverTrait;

//use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Masterforms\EventListenerManager\EventListenerInterface;
use Zend\EventManager\AbstractListenerAggregate as ZendListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Convenience methods
 *
 * @method \Savve\Service\AbstractService service() service($serviceName)
 * @method \Savve\Form\Form getForm() getForm($formName)
 * @method /\Savve\InputFilter\InputFilter inputFilter() inputFilter($inputFilterName)
 * @method /\Savve\Hydrator\AbstractHydrator|\Savve\Hydrator\AggregateHydrator hydrator() hydrator($hydratorName)
 * @method \Savve\Entity\ArrayObject entity() entity($entityName)
 * @method \Savve\Table\TableGateway table() table($tableName)
 * @method /\Savve\Filter\AbstractFilter filter() filter($filterName)
 * @method /\Savve\Validator\AbstractValidator validator() validator($validatorName)
 * @method \Savve\View\Model\ViewModel block() block($blockName)
 * @method \Savve\Session\Container session() session($namespace)
 * @method \Zend\Config\Config|array config() config($configName)
 * @method \Zend\ServiceManager\ServiceManager getServiceManager()
 * @method \Zend\Mvc\Application getApplication()
 * @method \Zend\Router\Http\RouteMatch routeMatch()
 * @method \Zend\Router\Http\TreeRouteStack router()
 * @method \Zend\I18n\Translator\Translator translator()
 * @method \Zend\Stdlib\Parameters getPost() getPost($type)
 * @method \Zend\Mvc\Controller\Plugin\FlashMessenger flashMessenger()
 * @method \Zend\Mvc\Controller\Plugin\Redirect redirect()
 */
abstract class AbstractListenerAggregate extends ZendListenerAggregate implements
        EventListenerInterface,
        ServiceLocatorInterface
{
    use ServiceRetrieverTrait;

    /**
     * Attach one or more listeners
     *
     * @return void
     * @see \Zend\EventManager\ListenerAggregateInterface::attach()
     */
    abstract public function attach (EventManagerInterface $event, $priority = 0);
}