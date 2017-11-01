<?php

namespace Masterforms\Service;

use Masterforms\EventManager\EventManagerAwareTrait;
use Masterforms\EventManager\Event;
use Masterforms\Service\ServiceRetrieverTrait;

use Zend\ServiceManager\ServiceLocatorAwareTrait as ZendServiceLocatorAwareTrait;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

/**
 * Convenience methods
 *
 * @method \Savve\Service\AbstractService service() service($serviceName)
 * @method \Savve\Form\Form getForm() getForm($formName)
 * @method /\Savve\InputFilter\InputFilter inputFilter() inputFilter($inputFilterName)
 * @method /\Savve\Hydrator\AbstractHydrator|\Savve\Hydrator\AggregateHydrator hydrator() hydrator($hydratorName)
 * @method /\Savve\Entity\ArrayObject entity() entity($entityName)
 * @method \Savve\Table\TableGateway table() table($tableName)
 * @method /\Savve\Filter\AbstractFilter filter() filter($filterName)
 * @method /\Savve\Validator\AbstractValidator validator() validator($validatorName)
 * @method \Savve\BlockManager\AbstractBlock block() block($blockName)
 * @method \Savve\Stdlib\Config config() config($configKey,$returnType)
 * @method \Zend\ServiceManager\ServiceManager getServiceManager()
 * @method \Zend\Mvc\Application getApplication()
 * @method \Zend\Router\Http\RouteMatch routeMatch()
 * @method \Zend\Router\Http\TreeRouteStack router()
 * @method \Zend\Session\AbstractContainer session() session($namespace)
 * @method \Zend\I18n\Translator\Translator translator()
 * @method \Zend\Mvc\Controller\Plugin\FlashMessenger flashMessenger()
 * @method \Zend\Mvc\Controller\Plugin\Redirect redirect()
 */
abstract class AbstractService implements
        ServiceInterface,
        EventManagerAwareInterface
{
    use EventManagerAwareTrait, ServiceRetrieverTrait;

    /**
     * @param $entity
     *
     * @return mixed
     * @throws \Exception
     */
    public function save ($entity)
    {
        $argv = array('entity' => $entity);
        $args = func_get_args();
        if (!empty($args)) {
            $second = array_shift($args);
            if (is_array($second))
                $argv = array_merge($argv, $second);
            $third = array_shift($args);
            if (is_array($third))
                $argv = array_merge($argv, $third);
        }

        try{
            // create the event
            $event = new Event(Event::EVENT_SAVE, $this, $argv);

            // trigger event
            $result = $this->getEventManager()->trigger($event);
            if ($result->stopped())
                $entity = $result->last();

            return $entity;
        }

        catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Register the default events for this service
     */
    protected function attachDefaultListeners()
    {
        /* @var $events \Zend\EventManager\EventManager */
        $events = $this->getEventManager();
        $event  = new Event;
        $events->setEventPrototype($event);

        // preSave
        if (method_exists($this, 'preSave')){
            $events->attach(Event::EVENT_SAVE, array($this, 'preSave'), 100);
        }

        // onSave
        if (method_exists($this, 'onSave')) {
            $events->attach(Event::EVENT_SAVE, array($this, 'onSave'));
        }

        // postSave
        if (method_exists($this, 'postSave')) {
            $events->attach(Event::EVENT_SAVE, array($this, 'postSave'), -100);
        }
    }

    /**
     * Converts a status string to value acceptable by the database
     *
     * @param string $status
     * @return string
     */
    public function convertStatus ($status = 'active')
    {
        return \Savve\Stdlib\StaticHelper::convertStatus($status);
    }
}