<?php

namespace Masterforms\Service;

use Masterforms\EventListenerManager\EventListenerInterface;
//use Savve\BlockManager\BlockInterface;
//use Savve\BlockManager\Block;
use Masterforms\Stdlib;
use Masterforms\Table\TableGateway;
use Masterforms\View\Model\ViewModel;
use Masterforms\Session\Container as SessionContainer;
use Zend\EventManager\Event;
use Masterforms\Entity\ArrayObject;

use Zend\Db\TableGateway\Feature;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Config\Config;
use Zend\Hydrator\HydratorInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\Form\Form;
use Zend\Form\ElementInterface;
use Zend\Http\PhpEnvironment\Request;
use Zend\Http\PhpEnvironment\Response;
use Zend\ServiceManager\AbstractPluginManager;

use \Exception;
use \DomainException;

/**
 * Convenience methods
 *
 * @method \Zend\ServiceManager\ServiceLocator getServiceLocator()
 */
trait ServiceRetrieverTrait
{
    /**
     * Retrieve the entity model instance from the ServiceManager
     * @param string $entityName
     * @return unknown
     */
    public function entity ($entityName)
    {
        $serviceLocator = $this->getServiceLocator();

        // get the entity from the ServiceManager
        if ($serviceLocator->has($entityName)) {
            $entity = $serviceLocator->get($entityName);
        }

        // instantiate the class manually
        elseif (class_exists($entityName, true)) {
            $entity = new $entityName();
        }

        return $entity;
    }

    /**
     * Retrieve a hydrator instance from HydratorPluginManager
     *
     * @param string $hydratorName
     * @return \Zend\Hydrator\AbstractHydrator
     */
    public function hydrator ($hydratorName)
    {
        /* @var $hydratorManager \Zend\Hydrator\HydratorPluginManager */
        $serviceLocator = $this->getServiceLocator();
        if ($serviceLocator instanceof AbstractPluginManager) {
            $serviceLocator = $serviceLocator->getServiceLocator();
        }
        $hydratorManager = $serviceLocator->get('HydratorManager');

        // retrieve from HydratorPluginManager
        if ($hydratorManager->has($hydratorName)) {
            return $hydratorManager->get($hydratorName);
        }

        // else, retrieve from ServiceManager
        elseif ($serviceLocator->has($hydratorName)) {
            return $serviceLocator->get($hydratorName);
        }

        else {
            throw new DomainException(sprintf("%s is not a valid hydrator class. HydratorManager cannot instantiate the class", $hydratorName));
        }
    }

    /**
     * Retrieve the input filter instance from the InputFilterPluginManager
     *
     * @param string $inputFilterName
     * @return \Zend\InputFilter\InputFilter
     */
    public function inputFilter ($inputFilterName)
    {
        /* @var $inputFilterManager \Zend\InputFilter\InputFilterPluginManager */
        $serviceLocator = $this->getServiceLocator();
        if ($serviceLocator instanceof AbstractPluginManager) {
            $serviceLocator = $serviceLocator->getServiceLocator();
        }
        $inputFilterManager = $serviceLocator->get('InputFilterManager');

        // retrieve from InputFilterManager
        if ($inputFilterManager->has($inputFilterName)) {
            return $inputFilterManager->get($inputFilterName);
        }

        // else, retrieve from ServiceManager
        elseif ($serviceLocator->has($inputFilterName)) {
            return $serviceLocator->get($inputFilterName);
        }

        else {
            throw new DomainException(sprintf("%1.s' is not a valid input filter class. InputFilterPluginManager cannot instantiate the class", $inputFilterName));
        }
    }

    /**
     * Retrieve the application config
     *
     * @return array|\Zend\Config\Config
     */
    public function config ($configKey = null, $returnType = Stdlib\Config::TYPE_ARRAY)
    {
        $serviceLocator = $this->getServiceLocator();
        $config = $serviceLocator->get('Config');

        // if $configKey is not null, return the config key value
        if (null !== $configKey) {
            if (isset($config[$configKey]) && (null !== $config[$configKey])) {
                $config = $config[$configKey];
            }
            else {
                $config = array();
            }
        }
        if (empty($config))
            return null;

        $returnType = strtolower($returnType);
        switch ($returnType) {
            case 'config':
            case Stdlib\Config::TYPE_CONFIG:
                $config = new Stdlib\Config($config);
                break;

            default:
            case 'array':
            case Stdlib\Config::TYPE_ARRAY:
                if ($config instanceof Config)
                    $config = $config->toArray();
                break;
        }

        return $config;
    }

    /**
     * Get service from the ServiceLocator/ServiceManager
     *
     * @param string $serviceName
     */
    public function service ($serviceName)
    {
        $serviceLocator = $this->getServiceManager();

        // get the service from the ServiceManager
        if ($serviceLocator->has($serviceName)) {
            return $service = $serviceLocator->get($serviceName);
        }
    }

    /**
     * Get the table gateway from the ServiceManager
     *
     * @todo need to move some of these to the abstract factory
     *
     * @param string $tableName
     * @return \Savve\Table\AbstractTableGateway
     */
    public function table ($tableName)
    {
        $serviceLocator = $this->getServiceManager();

        // get the db adapter
        if ($serviceLocator->has('dbAdapter')) {
            $adapter = $serviceLocator->get('dbAdapter');
        }
        else {
            $adapter = Feature\GlobalAdapterFeature::getStaticAdapter();
        }
        $resultSet = new HydratingResultSet();

        // get the mapper explicitly if it exists
        if ($serviceLocator->has($tableName)) {
            $tableGateway = $serviceLocator->get($tableName);
        }

        // if it is not in the ServiceManager, then instantiate it
        elseif (class_exists($tableName)) {
            $tableGateway = new $tableName(null, $adapter, null, $resultSet);
        }

        // if this is a table name, initilize a new table instance
        else {
            $table = new TableGateway($tableName, $adapter, null, $resultSet);
        }

        return $tableGateway;
    }

    /**
     * Get a block service instance from the BlockManager
     * @param string $templateName
     * @param array $params
     * @return \Savve\BlockManager\AbstractBlock
     */
    public function block ($templateName, $params = array())
    {
        /* @var $blockManager \Savve\BlockManager\BlockPluginManager */

        // check if the calling class is a controller plugin
        if ($this instanceof \Zend\Mvc\Controller\Plugin\PluginInterface) {
    //        return $this($templateName, $params);
        }

        // check if the calling class is a view helper
        elseif ($this instanceof \Zend\View\Helper\HelperInterface) {
    //       return $this($templateName, $params);
        }

        // if the calling class is already is Block
        if ($this instanceof BlockInterface) {
            $blockManager = $this->getServiceLocator();
        }

        // if the calling class is a ServiceManager plugin service
        elseif ($this instanceof AbstractPluginManager || $this instanceof EventListenerInterface) {
            $serviceLocator = $this->getServiceLocator();
            $serviceManager = $serviceLocator->getServiceLocator();
            $blockManager = $serviceManager->get('BlockManager');
        }
        else {
            $serviceLocator = $this->getServiceLocator();
            $serviceManager = $serviceLocator->has('ServiceManager') ? $serviceLocator->get('ServiceManager') : $serviceLocator;

            // load from the block manager
            $blockManager = $serviceManager->get('BlockManager');
        }
        if ($blockManager->has($templateName)) {
            $block = $blockManager->get($templateName);
        }
        else {
            $block = new Block();
            $block->setTemplate($templateName);
            $blockManager->setService($templateName, $block);
        }

        if (!empty($params)){
            $block->setVariables($params);
        }

        return $block;
    }

    /**
     * Get the session container
     *
     * @param string $namespace Session namespace
     * @return SessionContainer
     */
    public function session ($namespace = 'default')
    {
        return new SessionContainer($namespace);
    }

    /**
     * Get the validator from the ValidatorPluginManager
     *
     * @param string $validatorName
     * @return \Zend\Validator\ValidatorInterface
     */
    public function validator ($validatorName)
    {
        $validator = false;
        $serviceManager = $this->getServiceManager();
        $pluginManager = $serviceManager->get('ValidatorManager');

        // get from the ValidatorManager
        if ($pluginManager->has($validatorName)) {
            $validator = $pluginManager->get($validatorName);
        }

        // get the hydrator from the ServiceManager
        elseif ($serviceManager->has($validatorName)) {
            $validator = $serviceManager->get($validatorName);
        }

        return $validator;
    }

    /**
     * Get the filter from the FilterPluginManager
     *
     * @param string $filterName
     * @return \Zend\Filter\FilterInterface
     */
    public function filter ($filterName)
    {
        $filter = false;
        $serviceManager = $this->getServiceManager();
        $pluginManager = $serviceManager->get('FilterManager');

        // get from the HydratorPluginManager
        if ($pluginManager->has($filterName)) {
            $filter = $pluginManager->get($filterName);
        }

        // get the hydrator from the ServiceManager
        elseif ($serviceManager->has($filterName)) {
            $filter = $serviceManager->get($filterName);
        }

        return $filter;
    }

    /**
     * Get the current matched route from the router
     *
     * @return \Zend\Router\Http\RouteMatch
     */
    public function routeMatch ()
    {
        $routeMatch = $this->getServiceManager()
            ->get('Application')
            ->getMvcEvent()
            ->getRouteMatch();
        return $routeMatch;
    }

    /**
     * Get the router instance
     *
     * @return \Zend\Router\Http\TreeRouteStack
     */
    public function router ()
    {
        $serviceManager = $this->getServiceManager();
        return $router = $serviceManager->get('HttpRouter');
    }

    /**
     * Get the translator
     *
     * @return \Zend\I18n\Translator\Translator
     */
    public function translator ()
    {
        $serviceManager = $this->getServiceManager();
        return $serviceManager->get('translator');
    }

    /**
     * Redirect controller plugin
     *
     * @return \Zend\Mvc\Controller\Plugin\Redirect
     */
    public function redirect ()
    {
        $plugin = $this->plugin('redirect');
        return $plugin;
    }

    /**
     * FlashMessenger controller plugin
     *
     * @return \Zend\Mvc\Plugin\FlashMessenger\FlashMessenger
     */
    public function flashMessenger ()
    {
        $plugin = $this->plugin('flashMessenger');
        return $plugin;
    }

    /**
     * Get plugin instance
     *
     * @param string $name Name of plugin to return
     * @param null|array $options Options to pass to plugin constructor (if not already instantiated)
     * @return mixed
     */
    public function plugin ($pluginName, array $options = null)
    {
        $controllerPluginManager = $this->service('ControllerPluginManager');
        return $controllerPluginManager->get($pluginName, $options);
    }

    /**
     * Helper for making easy links and getting urls that depend on the routes and router.
     *
     * @see \Zend\View\Helper\Url
     * @return string Url link URL
     */
    public function url ($name = null, $params = array(), $options = array(), $reuseMatchedParams = false)
    {
        $routeMatch = $this->routeMatch();
        $router = $this->getRouter();

        $basePath = $this->getServiceManager()->get('ViewHelperManager')->get('basePath');
        $url = $this->getServiceManager()->get('ViewHelperManager')->get('url');
        $url->setRouter($router);
        $url->setRouteMatch($routeMatch);
        return $basePath() . $url($name, $params, $options, $reuseMatchedParams);
    }

    /**
     * Retrieve the form element from the FormElementManager
     * @param string $elementName The name of the form element to retrieve
     * @return \Zend\Form\ElementInterface The instance of the form element from the ServiceManager
     */
    public function getFormElement ($elementName = null)
    {
        /* @var $serviceLocator \Zend\ServiceManager\ServiceLocator */
        /* @var $formElementManager \Zend\Form\FormElementManager */

        $serviceLocator = $this->getServiceLocator();
        $formElementManager = $serviceLocator->get('FormElementManager');

        // get the form element from the FormElementManager
        if ($formElementManager->has($elementName)) {
            $element = $formElementManager->get($elementName);
        }

        // else, retrieve from the ServiceLocator
        elseif ($serviceLocator->has($elementName)) {
            $element = $serviceLocator->get($elementName);
        }

        // else, if class exists
        elseif (class_exists($elementName, true)) {
            $element = new $elementName;
        }

        // if the element is not a form element
        if (!$element instanceof ElementInterface)
            throw new \DomainException(sprintf("Element %s does not exists", $elementName), 500);

        return $element;
    }

    /**
     * Get form instance from FormElementManager or ServiceMAnager
     *
     * @param string $formName
     * @return \Savve\Form\Form|\Zend\Form\Form
     */
    public function getForm ($formName = null)
    {
        /* @var $form \Zend\Form\Form */
        $args = func_get_args();
        $argv = func_num_args();


        // first argument is the form name
        if ($argv >= 1) {
            $formName = array_shift($args);
        }

        // second argument is the entity
        if (!empty($args)) {
            $entity = array_shift($args);
        }

        // third argument is the hydrator
        if (!empty($args))
            $hydrator = array_shift($args);

            // fourth argument is the input filter
        if (!empty($args))
            $inputFilter = array_shift($args);


        /* @var $formElementManager \Zend\Form\FormElementManager */
        $serviceLocator = $this->getServiceLocator();
        $serviceLocator = $this->getServiceManager();
        $formElementManager = $serviceLocator->get('FormElementManager');

        // get the form from the FormElementManager
        if ($formElementManager->has($formName)) {
            $form = $formElementManager->get($formName);
        }

        // get the form from the ServiceManager
        elseif ($serviceLocator->has($formName)) {
            $form = $serviceLocator->get($formName);
        }

        // instantiate the class
        elseif (class_exists($formName, true)) {
            $form = new $formName();
        }

        // form does not exists
        if (!$form instanceof Form) {
            throw new \DomainException(sprintf("Form '%s' does not exist", $formName), 500);
        }

        // if entity is provided
        if (isset($entity) && $entity) {
            if (is_string($entity))
                $entity = $this->getEntity($entity);
            if (is_object($entity))
                $form->bind($entity);
        }

        // if hydrator is provided
        if (isset($hydrator) && $hydrator) {
            if (is_string($hydrator))
                $hydrator = $this->getHydrator($hydrator);
            if ($hydrator instanceof HydratorInterface)
                $form->setHydrator($hydrator);
        }

        // if input filter is provided
        if (isset($inputFilter) && $inputFilter) {
            if (is_string($inputFilter))
                $inputFilter = $this->getInputFilter($inputFilter);
            if ($inputFilter instanceof InputFilterInterface)
                $form->setInputFilter($inputFilter);
        }

        return $form;
    }

    /**
     * Alias of static::entity()
     * @deprecated
     *
     * @param string $entityName
     * @return \stdClass
     */
    public function getEntity ($entityName)
    {
        return $this->entity($entityName);
    }

    /**
     * Get the mapper instance from the ServiceManager
     * @deprecated
     *
     * @param string $mapperName
     * @return \Savve\Mapper\MapperInterface
     *
     */
    public function getMapper ($mapperName = null)
    {
        $serviceLocator = $this->getServiceLocator();

        // get the mapper from the ServiceManager
        if ($serviceLocator->has($mapperName)) {
            $mapper = $serviceLocator->get($mapperName);
        }

        return $mapper;
    }

    /**
     * Alias of static::config($configKey);
     * @deprecated
     *
     * @param string $configKey
     * @return array Zend\Config\Config
     */
    public function getConfig ($configKey = null, $returnType = 'config')
    {
        return $this->config($configKey, $returnType);
    }

    /**
     * Get the service manager instance
     *
     * @return \Zend\ServiceManager\ServiceManager
     */
    public function getServiceManager ()
    {
        $serviceLocator = $this->getServiceLocator();

        if ($serviceLocator instanceof \Zend\ServiceManager\AbstractPluginManager)
            return $serviceLocator->getServiceLocator();

        elseif ($serviceLocator instanceof \Zend\ServiceManager\ServiceManager)
            return $serviceLocator;

        elseif (method_exists($serviceLocator, 'getServiceLocator'))
            return $serviceLocator->getServiceLocator();

        return $serviceLocator->get('ServiceManager');
    }

    /**
     * Get the Mvc Application instance
     *
     * @return \Zend\Mvc\Application
     */
    public function getApplication ()
    {
        return $this->getServiceManager()
            ->get('Application');
    }

//     /**
//      * Alias of static::routeMatch()
//      */
//     public function getRouteMatch ()
//     {
//         return $this->routeMatch();
//     }

    /**
     * Alias of static::router()
     * @deprecated
     * @return \Zend\Router\Http\TreeRouteStack
     */
    public function getRouter ()
    {
        return $this->router();
    }


    /**
     * Alias of static::session($namespace)
     *
     * @deprecated
     * @return SessionContainer
     */
    public function getSession ($namespace = 'default')
    {
        return static::session($namespace);
    }

    /**
     * Alias of static::translator()
     *
     * @deprecated
     *
     * @return \Zend\I18n\Translator\Translator
     */
    public function getTranslator ()
    {
        return $this->translator();
    }

    /**
     * Get the form post data from PostRedirectGet or Request
     *
     * @param string $type Type of request to use getting the POST params
     * @return array
     */
    public function getPost ($type = 'post')
    {
        /* @var $application \Zend\Mvc\Application */
        /* @var $serviceManager \Zend\ServiceManager\ServiceManager */
        /* @var $event \Zend\Mvc\MvcEvent */
        /* @var $request \Zend\Http\PhpEnvironment\Request */
        /* @var $response \Zend\Http\PhpEnvironment\Response */
        /* @var $prg \Zend\Mvc\Controller\Plugin\PostRedirectGet */

        $application = $this->getApplication();
        $serviceManager = $application->getServiceManager();
        $controllerPluginManager = $serviceManager->get('ControllerPluginManager');
        $prg = $controllerPluginManager->get('PostRedirectGet');

        $event = $application->getMvcEvent();
        $request = $event->getRequest();
        $response = $event->getResponse();
        $routeMatch = $event->getRouteMatch();
        $controllerName = $routeMatch->getParam('controller');
        $controller = $event->getTarget();

        // only proceed  if this an HTTP request
        if (!$request instanceof \Zend\Http\PhpEnvironment\Request) {
            return;
        }

        switch (strtolower($type)) {
            case 'prg':
        //        $prg = $prg($request->getRequestUri(), true);
                $post = (!($prg instanceof Response) && ($prg !== false)) ? $prg : null;
                break;

            default:
            case 'post':
                $post = $request->isPost() ? $request->getPost()
                    ->toArray() : null;
                break;
        }
        return $post;
    }

    /**
     * Build a full URL based on components
     * @return string
     */
    public function buildFullUrl ($baseUrl, $scheme = 'http', $routeParams = [], $routeOptions = [])
    {
        // create URI
        $uri = new \Zend\Uri\Http();
        $uri->setScheme($scheme)->setHost($baseUrl);

        // create a new Request
        $request = new \Zend\Http\PhpEnvironment\Request();
        $request->setUri($uri);
        $request->setBaseUrl($baseUrl);

        $config = $this->service('Config');
        $routerConfig = $config['router'];
        $router = \Zend\Router\Http\TreeRouteStack::factory($routerConfig);
        $router->match($request);

        if (!array_key_exists('name', $routeOptions)) {
            $routeOptions['name'] = 'home';
        }

        return $request->getUri()->getScheme() . '://' . $router->assemble($routeParams, $routeOptions, ['force_canonical' => true]);
    }
}