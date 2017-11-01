<?php

namespace Masterforms\Stdlib;

use Masterforms\Stdlib;
use Masterforms\Stdlib\Exception;

abstract class ObjectUtils
{


    /**
     * Returns the class variables including private, protected and public
     *
     * @param object $class
     * @return array
     */
    static public function getClassVars ($class)
    {
        if (!is_object($class)) {
            trigger_error(__METHOD__ . ' requires that the argument is of type object');
            return;
        }
        $reflection = new \ReflectionObject($class);
        $classProperties = $reflection->getProperties();

        $properties = [];
        foreach ($classProperties as $property) {
            $property->setAccessible(true);
            $name = $property->getName();
            $properties[$name] = $property->getValue($class);
        }
        return $properties;
    }

    /**
     * Returns the class property value regardless if property is public, protected or private
     *
     * @param object $class
     * @param string $property
     * @return void|mixed
     */
    static public function getClassPropertyValue ($class, $property)
    {
        if (!is_object($class)) {
            trigger_error(__METHOD__ . ' requires that the first argument is of type object', E_USER_WARNING);
            return;
        }

        // if property is not a string
        if (!is_string($property)) {
            trigger_error(__METHOD__ . ' requires that the second argument is a string', E_USER_WARNING);
            return;
        }

        $reflection = new \ReflectionObject($class);
        if (!$reflection->hasProperty($property)) {
            trigger_error(sprintf('"%1$s" property does not exists', $property), E_USER_WARNING);
            return;
        }

        // get the property data
        $property = $reflection->getProperty($property);
        $property->setAccessible(true);
        $value = $property->getValue(self::class);

        return $value;
    }

    /**
     * Merge 2 objects of Traversable instance
     *
     * @param \Traversable $a
     * @param \Traversable $b
     * @throws \InvalidArgumentException
     * @return \Traversable
     */
    static public function merge ($a, $b)
    {
        $args = func_get_args();
        $argc = func_num_args();

        foreach ($b as $key => $value) {
            if (array_key_exists($key, $a)) {
                if (is_int($key)) {
                    $a[] = $value;
                }
                elseif (is_array($value) && is_array($a[$key])) {
                    $a[$key] = static::merge($a[$key], $value);
                }
                else {
                    $a[$key] = $value;
                }
            }
            else {
                $a[$key] = $value;
            }
        }
        return $a;
    }

    /**
     * Checks whether an object is serialise, and unserialise if valid
     *
     * @param $object
     * @return mixed
     */
    static public function unserialize ($object)
    {
        $serialized = @unserialize($object);
        if (null !== $object && ($object === 'b:0;' || $serialized !== false)) {
            return $serialized;
        }
        return $object;
    }

    /**
     * Extract the namespace from a class
     *
     * @param string|object $class
     * @return string
     */
    static public function getNamespace ($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }
        return substr($class, 0, strrpos($class, '\\'));
    }

    /**
     * Hydrates an object with data
     *
     * @param array|\Traversable $data
     * @param \stdClass $object
     * @return \stdClass $object
     */
    static public function hydrate ($data, $object)
    {
        $data = self::toArray($data);

        foreach ($data as $key => $value) {
            $key = StringUtils::camelCase($key);

            // if there is a setter for this property
            $method = 'set' . ucfirst($key);
            if (method_exists($object, $method)) {
                call_user_func_array([ $object, $method ], [ $value ]);
            }
            elseif (property_exists($object, $key) || ($object instanceof \stdClass)) {
                $object->$key = $value;
            }
        }

        return $object;
    }

    /**
     * Alias of static::hydrate($data, $object)
     *
     * @param array|\Traversable $data
     * @param \stdClass $object
     * @return \stdClass $object
     */
    static public function populate ($data, $object)
    {
        return static::hydrate($data, $object);
    }

    /**
     * Extract values from the object and return as array
     *
     * @param \ArrayObject $object
     * @throws \InvalidArgumentException
     * @return array
     */
    static public function extract ($object)
    {
        if (is_array($object)) {
            return $object;
        }
        if (!is_object($object)) {
            throw new Exception\InvalidArgumentException("Passed variable is not an object", 500);
        }

        if ($object instanceof \IteratorAggregate || $object instanceof \Iterator) {
            return iterator_to_array($object);
        }

        if (is_callable(array( $object, 'getArrayCopy' ))) {
            return $object->getArrayCopy();
        }
        elseif (is_callable(array( $object, 'toArray' ))) {
            return $object->toArray();
        }
        else {
            return get_object_vars($object);
        }
    }

    /**
     * Alias of static::extract($object)
     *
     * @param \ArrayObject $object
     * @throws \InvalidArgumentException
     * @return array
     */
    static public function toArray ($object)
    {
        return static::extract($object);
    }
}