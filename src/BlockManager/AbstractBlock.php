<?php

namespace Masterforms\BlockManager;

use Masterforms\Stdlib\Exception;
use Masterforms\View\Model\ViewModel;
use Masterforms\BlockManager\BlockInterface;

use Zend\EventManager\EventManagerInterface;

/**
 * Convenience methods
 *
 * @method \Masterforms\BlockManager\AbstractBlock block() block($templateName)
 * @method \Zend\Session\AbstractContainer session() session($sessionName);
 */
abstract class AbstractBlock extends ViewModel implements
        BlockInterface
{

    /**
     * Event Manager identifiers
     *
     * @var array
     */
    protected $eventIdentifiers = [];

    /**
     * Determines whether a view helper should be allowed given certain parameters
     *
     * @param array $template
     * @return bool
     */
    public function isAllowed ($template)
    {
        $params = [
            'template' => $template
        ];
        $eventManager = $this->getEventManager();
        $results = $eventManager->trigger(__FUNCTION__, $this, $params);
        $allowed = $results->last();
        return $allowed;
    }

    /**
     * Set the event manager instance used by this context
     *
     * @param EventManagerInterface $event
     * @return mixed
     */
    public function setEventManager (EventManagerInterface $event)
    {
        $identifiers = $this->getEventIdentifiers();
        $event->setIdentifiers($identifiers);
        $this->eventManager = $event;

        // attach default listeners
        if (method_exists($this, 'attachDefaultListeners')) {
            $this->attachDefaultListeners();
        }

        return $this;
    }

    public function removeEventManager()
    {
        $this->eventManager = null;

        return $this;
    }

    /**
     * Attach/register default event listeners to the event manager
     *
     * @return void
     */
    public function attachDefaultListeners ()
    {
        $event = $this->getEventManager();
    //    $sharedEventManager = $event->getSharedManager();

        // attach a default event listener
        // Todo: This is currently throwing an error when running the 'attach' method...
    //    $id = __CLASS__;
    //    $sharedEventManager->attach($id, 'isAllowed', function  ($event)
    //    {
    //        return true; // always allowed
    //    });

        return parent::attachDefaultListeners();
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
                'Zend\View\Model\ViewModel',
                'Masterforms\View\Model\ViewModel',
                'Masterforms\BlockManager\BlockInterface',
                __CLASS__,
                get_class($this)
            ];
        }
        return $this->eventIdentifiers;
    }

    /**
     * Set the EventManager identifiers
     *
     * @return AbstractBlock
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
     * Return a string when this class is echoed
     *
     * @return string
     */
    public function __toString ()
    {
        $html = '';

        if (method_exists($this, 'toString')) {
            $html = $this->toString();
        }

        elseif (method_exists($this, 'render')) {
            $html = $this->render();
        }

        // event listener for access control
        $template = $this->getTemplate();

        $allowed = $this->isAllowed($template);
        if (!$allowed) {
            $html = '';
        }

        // trigger event listeners
        $eventManager = $this->getEventManager();
        $results = $eventManager->trigger('render', $this, ['template' => $template, 'content' => $html]);
        if ($results->stopped()) {
            $html = $results->last();
            return $html;
        }

        return $html;
    }

    /**
     * Magic method when the object is cloned
     *
     * @return void
     */
    public function __clone ()
    {
        $this->clearVariables();
    }
}