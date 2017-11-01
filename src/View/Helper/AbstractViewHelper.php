<?php

namespace Masterforms\View\Helper;

//use Savve\BlockManager\Block;
//use Savve\View\Model\ViewModel;
//use Savve\Stdlib;
//use Savve\Stdlib\Exception;
//use Savve\Service\ServiceRetrieverTrait;

use Traversable;

//use Zend\ServiceManager\ServiceManager;
//use Zend\Router\RouterFactory;
//use Zend\Router\Http\RouteInterface;
//use Zend\Router\RouteInvokableFactory;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\Router\RouterConfigTrait;

use Zend\View\Helper\EscapeHtml;
//use Zend\View\Model\ModelInterface as Model;
//use Zend\View\HelperPluginManager;
//use Zend\EventManager\Event;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;
//use Zend\EventManager\EventManagerAwareTrait;
//use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\I18n\View\Helper\AbstractTranslatorHelper as ZendAbstractViewHelper;

//use Zend\ServiceManager\ServiceManager;
//use Zend\Authentication\AuthenticationService;
//use Authorization\Service\AuthorizationService;
//use Authorization\Factory\View\Helper\IsGrantedHelperFactory;
//use Authorization\View\Helper\IsGranted as Grant;

use Interop\Container\ContainerInterface;

/**
 * Convenience methods
 *
 * @method \Zend\View\Renderer\PhpRenderer getView()
 */
abstract class AbstractViewHelper extends ZendAbstractViewHelper implements
    EventManagerAwareInterface
{
    use RouterConfigTrait;

    /**
     * Instance of Zend\EventManager\EventManagerInterface
     *
     * @var EventManagerInterface
     */
    protected $events;

    /**
     * Event Manager identifiers
     *
     * @var array
     */
    protected $eventIdentifiers = [];

    /**
     * EventManager parameters
     *
     * @var array
     */
    protected $eventParams = [];

    /**
     * ViewModel instance
     *
     * @var \Zend\View\Model\ViewModel
     */
    protected $block;

    /**
     * Flag to display the border hint around the current view script
     *
     * @var boolean
     */
    protected static $showTemplateHints = false;

    /**
     * Flag to display the current view script filename
     *
     * @var boolean
     */
    protected static $showTemplateFilename = false;

    //protected $serviceManager;

    /**
     * Escape HTML helper
     *
     * @var EscapeHtml
     */
    protected $escapeHtmlHelper;

    /**
     * Magic method called when a non-existent method is called
     *
     * @param string $method
     * @param mixed $args
     */
    public function __call ($method, array $args = [])
    {
        /* @var $helperPluginManager \Zend\View\HelperPluginManager */
        $helperPluginManager = $this->getPluginManager();

        // if view helper plugin exists, call that plugin
        if ($helperPluginManager->has($method)) {
            $plugin = $helperPluginManager->get($method);
            return call_user_func_array($plugin, $args);
        }

        throw new Exception\BadMethodCallException(sprintf('%1$s is an invalid method. It does not exists'), 500);
    }

    /**
     * Get the PhpRenderer
     *
     * @return \Zend\View\Renderer\PhpRenderer
     */
    public function getRenderer ()
    {
        return $this->getView();
    }

    /**
     * Get the ServiceManager instance
     *
     * @return \Zend\ServiceManager\ServiceManager
     */
    public function getServiceManager ()
    {
        return $this->container;
        // the first service locator is the view helper plugin manager to gives access to other view helpers
        //    $helperPluginManager = $this->getServiceLocator();

        // the second service locator is the ServiceManager that gives access to all the services
        //    $serviceManager = $helperPluginManager->getServiceLocator();

        //    $service = new ServiceManager([

        //   ]);

        //   return $service;
    }

    /**
     * Get the BlockManager instance
     *
     * @return \Masterforms\BlockManager\BlockPluginManager
     */
    public function getBlockManager ()
    {
        $serviceManager = $this->getServiceManager();
        $blockManager = $serviceManager->get('BlockManager');
        return $blockManager;
    }

    /**
     * Get the block view model
     *
     * @param string $templateName
     * @return Block
     */
    public function block ($templateName)
    {
        $blockManager = $this->getBlockManager();
        return $blockManager->get($templateName);
    }

    /**
     * Get the current route params
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
     * Get the current route
     *
     * @return \Zend\Router\Http\RouteMatch
     */
    public function getRouteMatch ()
    {
        return $this->routeMatch();
    }

    /**
     * Get the Request instance
     *
     * @return \Zend\Http\PhpEnvironment\Request
     */
    public function getRequest ()
    {
        $serviceManager = $this->getServiceManager();
        $request = $serviceManager->get('Request');
        return $request;
    }

    /**
     * Get the block view model
     *
     * @return \Savve\View\Model\ViewModel
     */
    public function getBlock ()
    {
        return $this->block;
    }

    /**
     * Set the block view model
     *
     * @param \Savve\View\Model\ViewModel
     * @return AbstractViewHelper
     */
    public function setBlock (ViewModel $viewModel)
    {
        $this->block = $viewModel;
        return $this;
    }

    /**
     * Render the view
     *
     * @return string
     */
    public function render ()
    {
        $block = $this->block;
        $template = $block->getTemplate();
        $renderer = $this->getRenderer();
        $string = $renderer->render($this->getBlock());
        $filename = $renderer->resolver($template);
        $filename = realpath($filename);

        if (self::$showTemplateHints) {
            $wrapper = '<div style="position:relative;border:1px dotted #f00">';
            if (self::$showTemplatFilename) {
                $wrapper .= '<div style="position:absolute;right:0;top:0; padding:2px 5px; background:rgba(181, 39, 39, 0.46); color:white; font:normal 11px Arial; text-align:left !important; z-index:998;" onmouseover="this.style.zIndex=\'999\'" onmouseout="this.style.zIndex=\'998\'" title="%2$s">%2$s</div>';
            }
            $wrapper .= '%1$s</div>';
            $string = sprintf($wrapper, $string, $filename);
        }

        return $string;
    }

    /**
     * Magic method called when the view helper class is echoed or cast as a string
     *
     * @return string
     */
    public function __toString ()
    {
        $html = '';
        if (method_exists($this, 'toString')) {
            $html = $this->toString();
        }

        // event listener for access control
        // if its a 'route' we need to use the isGranted view helper from Learner\Authorization
        if (array_key_exists("route", $this->eventParams)) {
            $route = $this->eventParams['route'];
            $allowed = $this->isGranted($route, 'route');
        } else {
            $allowed = $this->isAllowed($this->eventParams);
        }
        if (!$allowed) {
            $html = '';
        }

        // trigger event listeners
        $eventManager = $this->getEventManager();
        $results = $eventManager->trigger(__FUNCTION__, $this, [
            'results' => $html
        ]);
        if ($results->stopped()) {
            $html = $results->last();
            return $html;
        }

        return $html;
    }

    /**
     * Get the HelperPluginManager instance
     *
     * @return \Zend\View\HelperPluginManager
     */
    public function getHelperPluginManager ()
    {
        $view = $this->getView();
        return $view->getHelperPluginManager();
    }

    /**
     * Alias of self::getHelperPluginManager
     */
    public function getPluginManager ()
    {
        return $this->getHelperPluginManager();
    }

    /**
     * Get plugin instance
     *
     * @param string $name Name of plugin to return
     * @param array $options Options to pass to plugin constructor (if not already instantiated)
     * @return AbstractHelper
     */
    public function plugin ($name, $options = [])
    {
        if (!is_array($options) || !$options) {
            $options = func_get_args();
        }
        $name = array_shift($options);


        /* @var $helperPluginManager \Zend\View\HelperPluginManager */
        $helperPluginManager = $this->getPluginManager();

        // if view helper plugin exists, call that plugin
        if ($helperPluginManager->has($name)) {
            $plugin = $helperPluginManager->get($name);
            return call_user_func_array($plugin, $options);
        }

        throw new Exception\BadMethodCallException(sprintf('%s is an invalid method. It does not exists', $name), 500);
    }

    /**
     * Check if View Helper plugin exists
     *
     * @param string $method
     * @return boolean
     */
    public function hasPlugin ($method)
    {
        $helperPluginManager = $this->getHelperPluginManager();
        return $helperPluginManager->has($method);
    }

    /**
     * Determines whether a view helper should be allowed given certain parameters
     *
     * @param array $params
     * @return bool
     */
    protected function isAllowed ($params)
    {
        $eventManager = $this->getEventManager();
        $results = $eventManager->trigger(__FUNCTION__, $this, $params);
        $allowed = $results->last();
        return $allowed;
    }

    /**
     * ViewModel view helper
     *
     * @var \Zend\View\Helper\ViewModel
     */
    protected $viewModelHelper;

    /**
     * Get the ViewModel view helper
     *
     * @return \Zend\View\Helper\ViewModel
     */
    protected function getViewModelHelper ()
    {
        if ($this->viewModelHelper)
            return $this->viewModelHelper;

        if (method_exists($this->getView(), 'plugin')) {
            $this->viewModelHelper = $this->view->plugin('view_model');
        }

        return $this->viewModelHelper;
    }

    /**
     * Get the current view model
     *
     * @throws Exception\RuntimeException
     * @return \Zend\View\Model\ViewModel
     */
    protected function getCurrent ()
    {
        $helper = $this->getViewModelHelper();
        if (!$helper->hasCurrent()) {
            throw new Exception\RuntimeException(sprintf('%s: no view model currently registered in renderer; cannot query for children', __METHOD__));
        }

        return $helper->getCurrent();
    }

    /**
     * Set the event manager instance used by this context
     *
     * @param EventManagerInterface $events
     * @return mixed
     */
    public function setEventManager (EventManagerInterface $events)
    {
        $identifiers = $this->getEventIdentifiers();
        if (isset($this->eventIdentifier)) {
            if ((is_string($this->eventIdentifier)) || (is_array($this->eventIdentifier)) || ($this->eventIdentifier instanceof Traversable)) {
                $identifiers = array_unique(array_merge($identifiers, (array) $this->eventIdentifier));
            }
            elseif (is_object($this->eventIdentifier)) {
                $identifiers[] = $this->eventIdentifier;
            }
            // silently ignore invalid eventIdentifier types
        }
        $events->setIdentifiers($identifiers);
        $this->events = $events;

        if (method_exists($this, 'attachDefaultListeners')) {
            $this->attachDefaultListeners();
        }

        return $this;
    }

    /**
     * Retrieve the event manager
     *
     * Lazy-loads an EventManager instance if none registered.
     *
     * @return EventManagerInterface
     */
    public function getEventManager ()
    {
        if (!$this->events instanceof EventManagerInterface) {
            $this->setEventManager(new EventManager());
        }

        return $this->events;
    }

    /**
     * Get the EventManager identifiers
     *
     * @return array
     */
    public function getEventIdentifiers ()
    {
        if (!$this->eventIdentifiers) {
            $this->eventIdentifiers = [
                'Zend\View\Helper\AbstractHelper',
                __CLASS__,
                get_class($this)
            ];
        }
        return $this->eventIdentifiers;
    }

    /**
     * Set the EventManager identifiers
     *
     * @return AbstractViewHelper
     */
    public function setEventIdentifiers ($identifier)
    {
        if (is_string($identifier)) {
            $this->eventIdentifiers[] = $identifier;
        }
        elseif (is_array($identifier)) {
            $this->eventIdentifiers = $identifier;
        }
        return $this;
    }

    /**
     * Attach default listeners
     *
     * @return AbstractViewHelper
     */
    public function attachDefaultListeners ()
    {
        $events = $this->getEventManager();
        $sharedEventManager = $events->getSharedManager();

        // default event listener, always returns true
        $id = __CLASS__; // [ __CLASS__, get_class($this) ];
        if (isset( $sharedEventManager)) {
            $sharedEventManager->attach($id, 'isAllowed', function  ($event)
            {
                return true;
            });
        }

        return $this;
    }

    /**
     * Checks if the URL have http/https/ftp prefixes
     *
     * @param string $url
     * @return bool
     */
    protected function hasUrlScheme ($url)
    {
        return Stdlib\StringUtils::hasURlScheme($url);
    }

    /**
     * Process the attributes
     *
     * @param array $attributes
     * @return string
     */
    public function createAttributeString (array $attributes)
    {
        $escape = $this->getEscapeHtmlHelper();
        $strings = array();
        foreach ($attributes as $key => $value) {
            $strings[] = sprintf('%s="%s"', $escape($key), $escape($value));
        }

        return implode(' ', $strings);
    }

    /**
     * Retrieve the escapeHtml helper
     *
     * @return \Zend\View\Helper\EscapeHtml
     */
    protected function getEscapeHtmlHelper ()
    {
        if ($this->escapeHtmlHelper) {
            return $this->escapeHtmlHelper;
        }

        if (method_exists($this->view, 'plugin')) {
            $this->escapeHtmlHelper = $this->view->plugin('escapehtml');
        }

        if (!$this->escapeHtmlHelper instanceof \Zend\View\Helper\EscapeHtml) {
            $this->escapeHtmlHelper = new \Zend\View\Helper\EscapeHtml();
        }

        return $this->escapeHtmlHelper;
    }

    /**
     * Translate a string
     *
     * @param string $string
     * @return string
     */
    protected function translate ($string)
    {
        $translator = $this->getTranslator();
        if ($translator) {
            return $translator->translate($string);
        }
        return $string;
    }
}