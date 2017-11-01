<?php

namespace Masterforms\View\Model;

use Masterforms\Stdlib;
use Masterforms\Stdlib\Exception;
use Masterforms\View\Model;
use Masterforms\View\ViewEvent as Event;
use Masterforms\Service\ServiceRetrieverTrait;
use Masterforms\EventManager\EventManagerAwareTrait;

use Zend\Mvc\MvcEvent;
use Zend\View\Renderer\PhpRenderer as ViewRenderer;
use Zend\EventManager\EventManagerAwareInterface;
//use Zend\ServiceManager\ServiceLocatorAwareTrait;
//use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\Stdlib\InitializableInterface;
use Zend\Stdlib\PriorityQueue;
use Zend\View\Model\ModelInterface;
use Zend\View\Model\ViewModel as ZendViewModel;

/**
 * Convenience methods
 *
 * @method \Savve\Service\AbstractService service() service($serviceName)
 * @method \Zend\Session\AbstractContainer session() session($sessionName);
 * @method \\Savve\InputFilter\InputFilter inputFilter() inputFilter($inputFilterName)
 * @method \\Savve\Hydrator\AggregateHydrator hydrator() hydrator($hydratorName)
 * @method \Savve\Entity\ArrayObject entity() entity($entityName)
 * @method \Savve\Table\TableGateway table() table($tableName)
 * @method \\Savve\Filter\AbstractFilter filter() filter($filterName)
 * @method \\Savve\Validator\AbstractValidator validator() validator($validatorName)
 * @method \Zend\Config\Config|array config() config($configName)

 * @method \Savve\Form\Form getForm() getForm($formName)
 * @method \Zend\ServiceManager\ServiceManager getServiceManager()
 * @method \Zend\Mvc\Application getApplication()
 * @method \Zend\Router\Http\RouteMatch routeMatch()
 * @method \Zend\I18n\Translator\Translator getTranslator()
 */
 abstract class ViewModel extends ZendViewModel implements
        Model\ViewModelInterface,
        EventManagerAwareInterface,
        InitializableInterface
{
    use EventManagerAwareTrait, ServiceRetrieverTrait;

    /**
     * Always append child view models as default
     */
    protected $append = true;

    /**
     * Priority Queue
     */
    protected $priorityQueue;

    /**
     * Priority id of this view model in the queue
     * @var int
     */
    protected $priority = 1;

    /**
     * Flag to check if the view variables has been loaded
     * @var boolean
     */
    public $loaded = false;

    /**
     * Constructor
     *
     * @param  null|array|Traversable $variables
     * @param  array|Traversable $options
     */
    public function __construct($variables = null, $options = null)
    {
        parent::__construct($variables, $options);
        $this->priorityQueue = new PriorityQueue();
    }

    /**
     * If non-existent method is called, invoke this magic method
     */
    public function __call ($method, $args = array())
    {
        $patterns = array(
            '/^get(?P<variable>[A-Z][a-zA-Z0-9]+)?$/U' => 'getVariable',
            '/^set(?P<variable>[A-Z][a-zA-Z0-9]+)?$/U' => 'setVariable'
        );
        foreach ($patterns as $pattern => $function) {
            $matches = null;
            $found = preg_match($pattern, $method, $matches);
            // if a matched pattern was found, call the associated function with the
            // matches and args and return the result
            if ($found) {
                // variable key to assign to
                $key = Stdlib\StringUtils::underscore($matches['variable']);

                // what is the number of args of the function
                $reflection = new \ReflectionMethod($this, $function);
                $countParams = $reflection->getNumberOfParameters();

                // create the args to be passed to the function
                $params = array_slice($args, 0, $countParams);
                array_unshift($params, $key);

                // execute the function
                return call_user_func_array(array($this, $function), $params);
            }
        }
    }

    /**
     * Init the class
     *
     * @return void
     */
    public function init ()
    {
        $event = new Event();
        $event->setTarget($this);
        $event->setName(Event::EVENT_INIT);
        $this->getEventManager()->trigger($event->getName(), $event);
    }

    /**
     * Register default event listeners
     */
    public function attachDefaultListeners ()
    {
        $events = $this->getEventManager();

        // preInit
        if (method_exists($this, 'preInit'))
            $events->attach(Event::EVENT_INIT, array($this, 'preInit'), 1000);

        // onInit
        if (method_exists($this, 'onInit'))
            $events->attach(Event::EVENT_INIT, array($this, 'onInit'));

        // postInit
        if (method_exists($this, 'postInit')) {
            $events->attach(Event::EVENT_INIT, array($this,'postInit'), -100);
        }

        // render event
        if (method_exists($this, 'load')) {
            $sharedEventManager = $events->getSharedManager();
            // $sharedEventManager->attach('Zend\Mvc\Application', MvcEvent::EVENT_RENDER, array( $this, 'load' ), -10000);
        }
    }

    /**
     * Add a child model
     *
     * @param  ModelInterface $child
     * @param  null|string $captureTo Optional; if specified, the "capture to" value to set on the child
     * @param  null|bool $append Optional; if specified, append to child  with the same capture
     * @return ViewModel
     */
    public function addChild(ModelInterface $child, $captureTo = null, $append = null)
    {
        // if priority is set in the child model, then retrieve that
        if (method_exists($child, 'getPriority')) {
            $priority = (int) $child->getPriority();
        }
        else {
            $priority = 1;
        }

        if (null !== $captureTo) {
            if (is_numeric($captureTo))
                $priority = $captureTo;
            elseif (is_string($captureTo))
                $child->setCaptureTo($captureTo);
        }

        if (null !== $append) {
            $child->setAppend($append);
            $priority = 1;
        }

        // add the child view to the priority queue
        $this->priorityQueue->insert($child, $priority);

        $iterator = $this->priorityQueue->getIterator();
        $i = 0;
        while ($iterator->valid()) {
            $item = $iterator->extract();
            $this->children[$i] = $item;
            $i++;
        }
        return $this;
    }

    /**
     * Remove a child model from the view model
     * @return \Savve\View\Model\ViewModel
     */
    public function removeChild ($child)
    {
        $this->priorityQueue->remove($child);
        foreach ($this->children as $key => $datum) {
            if ($datum === $child)
                unset($this->children[$key]);
        }
        return $this;
    }

    /**
     * Return a string when this class is echoed
     *
     * @return string
     */
    public function __toString ()
    {
        if (method_exists($this, 'render')){
            return $this->render();
        }
        return '';
    }

    /**
     * Renders the block view model
     * @return string
     */
    public function render ()
    {
        try {
            if (!$this->getTemplate()){
                throw new Exception\Exception("Template was not set for this view model", 500);
            }

            // if method load exists
            if (method_exists($this, 'load')){
                $this->load();
            }

            $template = $this->getTemplate();
            $renderer = $this->getRenderer();
            if (!$renderer->resolver($template)){
                throw new Exception\DomainException(sprintf("Cannot resolve template %s", $template), 500);
            }

            // get the current view model variables
            $modelVars = $this->getVariables();

            // set the variable from the PhpRenderer
            $__vars = $renderer->vars()
                ->getArrayCopy();
            foreach ($__vars as $key => $value) {
                if (!$this->getVariable($key)) {
                    $this->setVariable($key, $value);
                }
            }
            unset($__vars);

            $result = $renderer->render($this);
            if ($this->hasChildren()) {
                foreach ($this->getChildren() as $child) {
                    $result .= $renderer->render($child,$renderer->vars());
                }
            }

            return $result;
        }
        catch (\Exception $e) {
            trigger_error(sprintf("Cannot render model. Error: %s", $e->getMessage()), E_USER_WARNING);
        }
    }

    /**
     * Clears all child models
     * @return \Savve\View\Model\ViewModel
     */
    public function clearChildren ()
    {
        $iterator = $this->priorityQueue->getIterator();
        while ($iterator->valid()) {
            $item = $iterator->extract();
            $this->priorityQueue->remove($item);
        }
        return parent::clearChildren();
    }

    /**
     * Find a variable through the current view model and the child models
     * @param string $name Variable name
     * @param $default default value if the variable is not present.
     * @return mixed
     */
    public function getVariableRecursively ($name, $default = null)
    {
        $name = (string) $name;
        if (array_key_exists($name, $this->variables)) {
            return $this->variables[$name];
        }
        if ($this->hasChildren()) {
            foreach ($this->getChildren() as $child) {
                if ($variable = $child->getVariable($name, $default)) {
                    return $variable;
                }
            }
        }

        return $default;
    }

    /**
     * Get the view model's priority
     * @return int
     */
    public function getPriority ()
    {
        return $this->priority;
    }

    /**
     * Set the view model's priority
     */
    public function setPriority ($priority)
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * Get the current route
     * @return \Zend\Router\Http\RouteMatch
     */
    public function getRouteMatch()
    {
        $serviceManager = $this->getServiceManager();
        $application = $serviceManager->get('Application');
        $routeMatch = $application->getMvcEvent()->getRouteMatch();
        return $routeMatch;
    }

    /**
     * Get the current controller instance
     * @return \Savve\Controller\AbstractController
     */
    public function getController ()
    {
        /* @var $controllerLoader \Zend\Mvc\Controller\ControllerManager */
        $routeMatch = $this->routeMatch();
        $controller = $routeMatch->getParam('controller');
        $controllerLoader = $this->getServiceManager()->get('ControllerLoader');
        $controller = $controllerLoader->get($controller);
        return $controller;
    }

    /**
     * Get the ViewRenderer
     *
     * @return \Zend\View\Renderer\PhpRenderer
     */
    public function getRenderer ()
    {
        $serviceLocator = $this->getServiceLocator();
        if (!$serviceLocator)
            return new ViewRenderer();

        if ($serviceLocator->has('ViewRenderer'))
            return $serviceLocator->get('ViewRenderer');

        $serviceManager = $serviceLocator->getServiceLocator();
        if ($serviceManager->has('ViewRenderer'))
            $renderer = $serviceManager->get('ViewRenderer');
        else
            $renderer = new ViewRenderer();
        return $renderer;
    }
}