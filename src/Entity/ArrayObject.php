<?php

namespace Masterforms\Entity;

use Masterforms\Stdlib;
use \ArrayObject as BaseArrayObject;

class ArrayObject extends BaseArrayObject implements
        EntityInterface
{

    /**
     * Construct a new Array Object
     *
     * @param array $array
     */
    public function __construct (array $array = array())
    {
        parent::__construct($array);
        self::setFlags(\ArrayObject::ARRAY_AS_PROPS);
        if ($this->iteratorClass)
            self::setIteratorClass($this->iteratorClass);
    }

    /**
     * Alias of self::getArrayCopy()
     *
     * @see ArrayObject::getArrayCopy()
     */
    public function toArray ()
    {
        return self::getArrayCopy();
    }

    /**
     * Alias of self::exchangeArray()
     *
     * @param array $data
     * @return array
     */
    public function populate (array $data)
    {
        return self::exchangeArray($data);
    }

    /**
     *
     * @see ArrayObject::offsetExists()
     */
    public function offsetExists ($offset)
    {
        $offset = static::__underscore($offset);

        // check if a method exists for this variable
        $method = 'has' . static::camelCase($offset);
        if (method_exists($this, $method))
            return call_user_func(array(
                $this,
                $method
            ));

        return parent::offsetExists($offset);
    }

    /**
     *
     * @see ArrayObject::offsetGet()
     */
    public function offsetGet ($offset)
    {
        $offset = static::__underscore($offset);

        // check if a method exists for this variable
        $method = 'get' . static::camelCase($offset);
        if (method_exists($this, $method)) {
            return call_user_func(array($this,$method));
        }

        // if offset does not exists, return a null
        if (!$this->offsetExists($offset))
            return null;

        // if the offset is a closure
        if (is_callable(parent::offsetGet($offset))) {
            return call_user_func(parent::offsetGet($offset));
        }

        // use the default getter
        return parent::offsetGet($offset);
    }

    /**
     *
     * @see ArrayObject::offsetSet()
     */
    public function offsetSet ($offset, $value)
    {
        $offset = static::__underscore($offset);

        // check if a method exists for this variable
        $method = 'set' . static::camelCase($offset);
        if (method_exists($this, $method))
            return call_user_func(array(
                $this,
                $method
            ), $value);

        // use default setter
        parent::offsetSet($offset, $value);
    }

    /**
     *
     * @see ArrayObject::offsetUnset()
     */
    public function offsetUnset ($offset)
    {
        $offset = static::__underscore($offset);
        $value = false;
        // check if a method exists for this variable
        $method = 'unset' . static::camelCase($offset);
        if (method_exists($this, $method))
            return call_user_func(array(
                $this,
                $method
            ), $value);
        $method = 'uns' . static::camelCase($offset);
        if (method_exists($this, $method))
            return call_user_func(array(
                $this,
                $method
            ), $value);

        parent::offsetUnset($offset);
    }

    /**
     * Merge this entity with another entity
     *
     * @param (\Masterforms\Entity\ArrayObject |\ArrayObject $entity)
     * @return \Masterforms\Entity\ArrayObject | array
     */
    public function merge ()
    {
        if (func_num_args() < 1) {
            trigger_error(__METHOD__ . ' needs 1 or more entity arguments', E_USER_WARNING);
            return;
        }

        $self = clone $this;
        $arrays = func_get_args();
        array_unshift($arrays, $self);

        $merged = [];
        foreach ($arrays as $item) {
            if (!$item instanceof \ArrayObject) {
                trigger_error(sprintf('%s encountered a non ArrayObject argument. %s is not an instance of ArrayObject', __METHOD__, (string) get_class($item)), E_USER_WARNING);
                return;
            }
            $merged = Stdlib\ArrayUtils::recursiveMerge($merged, $item->getArrayCopy(), true);
        }

        return $this->exchangeArray($merged);
    }

    /**
     * Convert a string to CamelCase format
     *
     * @param string $offset
     * @return string
     */
    final protected function camelCase ($offset)
    {
        return Stdlib\StringUtils::camelCase($offset);
    }

    /**
     * Convert a string to under_score format
     *
     * @param string $offset
     * @return string
     */
    final protected function __underscore ($offset)
    {
        return Stdlib\StringUtils::underscore($offset);
    }
}