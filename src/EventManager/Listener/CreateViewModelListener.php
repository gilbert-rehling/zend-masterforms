<?php
/**
 * @deprecated
 */

namespace Savve\EventManager\Listener;

use Savve\BlockManager\Block;
use Savve\View\Model\ViewModel;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\ArrayUtils;
use Zend\Filter\Word\CamelCaseToDash as CamelCaseToDashFilter;
use Zend\Mvc\View\Http\CreateViewModelListener as ZendCreateViewModelListener;

class CreateViewModelListener extends ZendCreateViewModelListener
{

    /**
     * FilterInterface/inflector used to normalize names for use as template identifiers
     *
     * @var mixed
     */
    protected $inflector;

    /**
     * Inspect the result, and cast it to a ViewModel if an assoc array is detected
     *
     * @see \Zend\Mvc\View\Http\CreateViewModelListener::createViewModelFromArray($event)
     * @param MvcEvent $event
     */
    public function createViewModelFromArray (MvcEvent $event)
    {
        $result = $event->getResult();
        // if the action returns an array, the proceed
        if (!is_array($result)) {
            return;
        }
        if (!ArrayUtils::hasStringKeys($result, true)) {
            return;
        }
        $model = new ViewModel($result);
        $event->setResult($model);
    }

    /**
     * Inspect the result, and cast it to a ViewModel if null is detected
     *
     * @see \Zend\Mvc\View\Http\CreateViewModelListener::createViewModelFromNull($event)
     * @param MvcEvent $event
     */
    public function createViewModelFromNull (MvcEvent $event)
    {
        $result = $event->getResult();
        if (null !== $result) {
            return;
        }
        $application = $event->getApplication();
        $serviceManager = $application->getServiceManager();
        $routeMatch = $event->getRouteMatch();
        $controller = $event->getTarget();
        if (is_object($controller)) {
            $controller = get_class($controller);
        }
        if (!$controller) {
            $controller = $routeMatch->getParam('controller', '');
        }

        // get the module namespace
        $module = $this->deriveModuleNamespace($controller);
        $controller = $this->deriveControllerClass($controller);
        $template = $this->inflectName($module);
        if (!empty($template)) {
            $template .= '/';
        }
        $template .= $this->inflectName($controller);

        $action = $routeMatch->getParam('action');
        if (null !== $action) {
            $template .= '/' . $this->inflectName($action);
        }

        // get the named template in the service manager configs
        $model = null;
        $config = $serviceManager->get('Config');
        if (isset($config['blocks'])) {
            $blockConfig = $config['blocks'];
            if (isset($blockConfig[$template])) {
                $block = $blockConfig[$template];
                if ($serviceManager->has($block)) {
                    $model = $serviceManager->get($block);
                }
            }
        }

        // use the BlockManager Block class to create a ViewModel
        if (!$model) {
            $blockManager = $serviceManager->get('BlockManager');
            $model = $blockManager->has($template) ? $blockManager->get($template) : new Block();
        }

        // if model is still null, then use default
        if (!$model) {
            $model = new ViewModel();
        }

        $event->setResult($model);
    }

    /**
     * Determine the top-level namespace of the controller
     *
     * @param string $controller
     * @return string
     */
    protected function deriveModuleNamespace ($controller)
    {
        if (!strstr($controller, '\\')) {
            return '';
        }
        $module = substr($controller, 0, strpos($controller, '\\'));
        return $module;
    }

    /**
     * Determine the name of the controller
     *
     * Strip the namespace, and the suffix "Controller" if present.
     *
     * @param string $controller
     * @return string
     */
    protected function deriveControllerClass ($controller)
    {
        if (strstr($controller, '\\')) {
            $controller = substr($controller, strrpos($controller, '\\') + 1);
        }

        if ((10 < strlen($controller)) && ('Controller' == substr($controller, -10))) {
            $controller = substr($controller, 0, -10);
        }

        return $controller;
    }

    /**
     * Inflect a name to a normalized value
     *
     * @param string $name
     * @return string
     */
    protected function inflectName ($name)
    {
        if (!$this->inflector) {
            $this->inflector = new CamelCaseToDashFilter();
        }
        $name = $this->inflector->filter($name);
        return strtolower($name);
    }
}