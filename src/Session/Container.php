<?php

namespace Masterforms\Session;

use Zend\Session\AbstractContainer as SessionContainer;
use Zend\Session\Container as ZendSessionContainer;

class Container extends ZendSessionContainer
{

    /**
     * Clears the session container
     *
     * @return SessionContainer
     */
    public function clear ($name = null)
    {
        if (null === $name) {
            $name = $this->getName();
        }
        $manager = $this->getManager();
        $storage = $manager->getStorage();
        $storage->clear($name);
        return $this;
    }
}