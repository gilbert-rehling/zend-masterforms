<?php

namespace Masterforms\Doctrine\Entity;

use \stdClass as StdClass;
//use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\HasLifecycleCallbacks
 */
abstract class AbstractEntity extends StdClass implements
    EntityInterface,
    \ArrayAccess,
    \Serializable
{
    /**
     * Checks if an inaccessible property exists
     *
     * @param string $offset
     * @return boolean
     */
    public function __isset ($offset)
    {
        $key = $this->camelCase($offset);
        return property_exists($this, $key) ? true : false;
    }

    /**
     * Returns the values of an inaccessible property
     *
     * @param string $offset
     * @return mixed
     */
    public function __get ($offset)
    {
        // if there is a getter for this property
        $method = 'get' . ucfirst($this->camelCase($offset));
        if (method_exists($this, $method)) {
            $value = call_user_func_array([
                $this,
                $method
            ], []);
        }
        else {
            $key = $this->camelCase($offset);
            $value = property_exists($this, $key) ? $this->$key : null;
        }

        return $value;
    }

    /**
     * Sets the value of an inaccessible property
     *
     * @param string $offset
     * @param mixed $value
     * @return void
     */
    public function __set ($offset, $value)
    {
        // if there is a setter for this property
        $method = 'set' . ucfirst($this->camelCase($offset));
        if (method_exists($this, $method)) {
            call_user_func_array([
                $this,
                $method
            ], [
                $value
            ]);
        }
        else {
            $key = $this->camelCase($offset);
            $this->$key = $value;
        }
    }

    /**
     * Unsets the value of an inaccessible property
     *
     * @param string $offset
     * @return void
     */
    public function __unset ($offset)
    {
        $key = $this->camelCase($offset);
        $this->$key = null;
    }

    /**
     * Method to use when an inaccessible or non-existent method is called
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call ($method, $parameters = [])
    {
        $patterns = [
            '/^get(?P<offset>[A-Z][a-zA-Z0-9]+)?$/U' => '__get',
            '/^set(?P<offset>[A-Z][a-zA-Z0-9]+)?$/U' => '__set',
            '/^has(?P<offset>[A-Z][a-zA-Z0-9]+)?$/U' => '__isset',
            '/^(uns|unset)(?P<offset>[A-Z][a-zA-Z0-9]+)?$/U' => '__unset'
        ];

        foreach ($patterns as $pattern => $function) {
            // if a matched pattern was found, call the associated function with the matches and args and return the result
            if ($found = preg_match($pattern, $method, $matches)) {
                $matches['offset'] = $this->underscore($matches['offset']);
                $options = [];
                foreach ($matches as $key => $value) {
                    if (!is_int($key)) {
                        $reflection = new \ReflectionMethod($this, $function);
                        $countParams = $reflection->getNumberOfParameters();
                        $params = array_slice($parameters, 0, $countParams);

                        // put the object variable name as the first value in the param array
                        array_unshift($params, $value);
                        $options = $params;
                    }
                }

                return call_user_func_array([
                    $this,
                    $function
                ], $options);
            }
        }
    }

    /**
     * Checks if property exists.
     * Interface to provide access to property as an array
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists ($offset)
    {
        return $this->__isset($offset);
    }

    /**
     * Returns the property value
     * Interface to provide access to property as an array
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet ($offset)
    {
        return $this->__get($offset);
    }

    /**
     * Sets the property value
     * Interface to provide access to property as an array
     *
     * @param $offset
     * @param $value
     */
    public function offsetSet ($offset, $value)
    {
        return $this->__set($offset, $value);
    }

    /**
     * Unsets the property value
     * Interface to provide access to property as an array
     *
     * @param $offset
     */
    public function offsetUnset ($offset)
    {
        return $this->__unset($offset);
    }

    /**
     * Serialise entity
     * Interface to provide serialisation of object
     *
     * @return string
     */
    public function serialize ()
    {
        return serialize($this);
    }

    /**
     * Unserialise entity
     * Interface to provide serialisation of object
     *
     * @param string $serialized
     * @return mixed
     */
    public function unserialize ($serialized)
    {
        return unserialize($serialized);
    }

    /**
     * Returns the object properties and its values as an array
     *
     * @return array
     */
    public function getArrayCopy ()
    {
        $array = get_object_vars($this);

        foreach ($array as $key => $value) {
            // if there is a getter for this property
            $method = 'get' . ucfirst($this->camelCase($key));
            if (method_exists($this, $method)) {
                $value = call_user_func_array([
                    $this,
                    $method
                ], []);
            }
            unset($array[$key]);
            $array[$this->underscore($key)] = $value;
        }
        return $array;
    }

    /**
     * Alias of getArrayCopy()
     *
     * @return array
     */
    public function toArray ()
    {
        return $this->getArrayCopy();
    }

    /**
     * Exchanges the values of the object with another array or object
     *
     * @param array|object $input
     */
    public function exchangeArray ($input)
    {
        if (is_object($input)) {
            if (method_exists($input, 'getArrayCopy')) {
                $input = $input->getArrayCopy();
            }
            else {
                $input = get_object_vars($input);
            }
        }
        foreach ($input as $key => $value) {
            $key = $this->camelCase($key);

            // if there is a setter for this property
            $method = 'set' . ucfirst($key);
            if (method_exists($this, $method)) {
                // call_user_func_array([ $this, $method ], [ $value ]);
                $this->{$method}($value);
            }

            // set directly through class property
            else {
                $this->$key = $value;
            }
        }
    }

    /**
     * Alias of exchangeArray($input)
     *
     * @param array|object $input
     */
    public function populate ($input)
    {
        return $this->exchangeArray($input);
    }

    /**
     * Converts an offset string to underscore-separated string
     *
     * @return string
     */
    public function underscore ($offset)
    {
        return $offset = strtolower(preg_replace('/(.)([A-Z])/', '$1_$2', $offset));
    }

    /**
     * Convert an offset string to camelCase string
     *
     * @param string $offset
     * @return string
     */
    public function camelCase ($offset)
    {
        return lcfirst(preg_replace("#[\s]+#", '', ucwords(preg_replace("#[^a-zA-Z0-9]+#", ' ', $offset))));
    }
}