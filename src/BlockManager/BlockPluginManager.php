<?php

namespace Masterforms\BlockManager;

use Masterforms\BlockManager\BlockInterface;

use Zend\View\Model\ViewModel;
use Zend\Stdlib\InitializableInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Exception;

class BlockPluginManager extends AbstractPluginManager
{

    /**
     * Whether or not to share by default
     *
     * @var bool
     */
    protected $shareByDefault = true;

    /**
     * Validate the plugin
     *
     * Checks that the plugin loaded is a valid callback or instance of Savve\View\Model\ViewModelInterface
     *
     * @param \Masterforms\View\Model\ViewModelInterface $plugin
     * @return void
     * @throws Exception\RuntimeException if invalid
     */
    public function validatePlugin ($plugin)
    {
        // Hook to perform various initialization, when the element is not created through the factory
        if ($plugin instanceof InitializableInterface) {
            $plugin->init();
        }
        if ($plugin instanceof BlockInterface) {
            // we're okay
            return;
        }

        throw new Exception\RuntimeException(sprintf('Plugin of type %s is invalid; must implement Savve\BlockManager\BlockInterface', (is_object($plugin) ? get_class($plugin) : gettype($plugin)), __NAMESPACE__));
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Zend\ServiceManager\AbstractPluginManager::get()
     */
    public function get ($name, $options = array(), $usePeeringServiceManagers = true)
    {
        $serviceLocator = $this->getServiceLocator();
        $instance = parent::get($name, $options, $usePeeringServiceManagers);

        if ($instance instanceof ViewModel) {
            // if template was not predefined, then use the service name as the template name
            !$instance->getTemplate() ? $instance->setTemplate($name) : null;
        }
        return $instance;
    }
}