<?php

namespace Masterforms\Stdlib;

use Masterforms\Stdlib;
use Masterforms\Stdlib\Exception;
use Zend\Stdlib\ArrayUtils as AbstractArrayUtils;

abstract class ArrayUtils extends AbstractArrayUtils
{
    /**
     * Extract the values that does not exists in the first array
     *
     * @return array
     */
    static public function unique ()
    {
        if (func_num_args() < 2) {
            trigger_error(__METHOD__ . ' needs two or more array arguments', E_USER_WARNING);
            return;
        }

        // get all the method arguments
        $arrays = func_get_args();

        // create a new array
        $merged = array();
        while ($arrays) {
            $array = array_shift($arrays);

            if (!is_array($array) && !($array instanceof \Traversable)) {
                trigger_error(__METHOD__ . ' encountered a non array argument', E_USER_WARNING);
                return;
            }
            if (!$array && !$array instanceof \Traversable) {
                continue;
            }

            $merged = array_diff($array, $merged);
        }

        return $merged;
    }

    /**
     * Converts array values to alphanumeric
     * @param array $array
     * @return array
     */
    static public function alphanumericValues (array $array)
    {
        $return = [];
        foreach ($array as $key => $value) {
            if (is_array($value)){
                $return[$key] = static::alphanumericValues($value);
                continue;
            }
            $return[$key] = Stdlib\StringUtils::alphanumeric($value);
        }
        return $return;
    }

    /**
     * Extract columns from an array given the column names
     *
     * @param array $data
     * @param array $columns
     * @return array
     */
    static public function extractColumns ($data, array $columns = array())
    {
        $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($data));

        $return = [];
        foreach ($iterator as $key => $value) {
            if (in_array($key, $columns)) {
                $return[$key] = $value;
            }
        }
        return $return;

        //         if ($data instanceof \Iterator) {
        //             $data = iterator_to_array($data);
        //         }

        //         $array = array();
        //         array_walk($data, function  (&$item, $key) use( &$array, $columns)
        //         {
        //             if (in_array($key, $columns))
            //                 $array[$key] = $item;
        //         });
        //         return $array;
    }

    static public function removeColumns ($data, array $columns = array())
    {
        if ($data instanceof \Iterator) {
            $data = iterator_to_array($data);
        }

        $array = array();
        array_walk($data, function  (&$item, $key) use( &$array, $columns)
        {
            if (!in_array($key, $columns))
                $array[$key] = $item;
        });
        return $array;
    }

    /**
     * Filter an single-dimensional array
     *
     * @param array $array
     * @param boolean $removeZeroes Flag if zero values are included in the filter
     * @return array The filtered array
     */
    static public function filterEmptyCells (array $array, $removeZeroes = false)
    {
        $array = array_filter($array, function  ($var) use( $removeZeroes)
        {
            // exclude cell if current value meets the following criteria
            if (null === $var)
                return false;
            if ('' === trim($var))
                return false;
            if (is_array($var) && empty($var))
                return false;
            if ($removeZeroes && (0 === $var))
                return false;

            // cell is valid, then return
            return true;
        });

        return $array;
    }

    /**
     * Remove empty (null) cells in the array
     *
     * @param array $array
     * @param bool $removeZeroes
     * @return array
     */
    static public function removeEmptyCells (array $array, $removeZeroes = false)
    {
        foreach ($array as $k => $v) {

            // multi-dimensional array
            if (is_array($v)) {
                $array[$k] = call_user_func(__METHOD__, $v, $removeZeroes);
            }

            // remove null values
            if (is_null($v)) {
                unset($array[$k]);
            }

            // remove empty string or null values
            if ('' === $v || is_null($v)) {
                unset($array[$k]);
            }

            // remove empty arrays
            if (is_array($v) && empty($v)) {
                unset($array[$k]);
            }

            // remove values of zero
            if (!$v && $removeZeroes) {
                unset($array[$k]);
            }
        }
        return $array;
    }

    /**
     * Converts an array to key-value pairs
     *
     * @param $data
     * @param array $pairs
     * @return array
     */
    static public function getPairs ($data, $pairs = array())
    {
        $args = func_get_args();
        $argc = func_num_args();

        if (empty($pairs) || null === $pairs) {
            $first = current($data);
            $metaKey = key($first);
            array_shift($first);
            $metaValue = key($first);
        }
        elseif (is_array($pairs) && !empty($pairs)) {
            $metaKey = key($pairs);
            $metaValue = current($pairs);
        }
        elseif ((is_string($pairs) || is_numeric($pairs)) && $argc >= 3) {
            $data = array_shift($args);
            $metaKey = array_shift($args);
            $metaValue = array_shift($args);
        }

        $data = self::toArray($data);

        $array = [];
        foreach ($data as $item) {
            $array[$item[$metaKey]] = $item[$metaValue];
        }
        return $array;
    }

    /**
     * Returns an array from a multidimensional array of only the column given its column name/key
     *
     * @param array $data
     * @param string|int $columnName
     * @return array
     */
    static public function extractColumnValues (array $data, $columnName)
    {
        $data = array_map(function  ($var) use( $columnName)
        {
            if (is_array($columnName)) {
                $ret = array_intersect_key($var, array_combine($columnName, $columnName));
                return $ret;
            }
            elseif ((is_string($columnName) || is_numeric($columnName)) && isset($var[$columnName])) {
                return $var[$columnName];
            }
        }, $data);

        return $data;
    }

    /**
     * @param $array
     * @return mixed
     */
    static public function normalizeKeys ($array)
    {
        foreach ($array as $key => $value) {
            $originalKey = $key;

            $key = Stdlib\StringUtils::normalize($key, false);
            unset($array[$originalKey]);
            if (is_array($value)) {
                $value = call_user_func(__METHOD__, $value);
            }

            $array[$key] = $value;
        }
        return $array;
    }

    /**
     * @param $array
     * @return mixed
     */
    static public function normalizeValues ($array)
    {
        foreach ($array as $key => $value) {
            if (is_string($value)) {
                $value = Stdlib\StringUtils::normalize($value, false);
            }
            elseif (is_array($value)) {
                $value = call_user_func(__METHOD__, $value);
            }
            $array[$key] = $value;
        }
        return $array;
    }

    /**
     * Converts the keys of an array to camelCase format
     *
     * @param array|\Traversable|\Iterator $array
     * @return array
     */
    static public function camelCaseKeys ($array)
    {
        foreach ($array as $key => $value) {
            $originalKey = $key;
            $key = Stdlib\StringUtils::camelCase($key, false);
            unset($array[$originalKey]);
            if (is_array($value)) {
                $value = call_user_func(__METHOD__, $value);
            }

            $array[$key] = $value;
        }
        return $array;
    }

    /**
     * Converts the values of an array to camelCase format
     *
     * @param array $array
     * @return array
     */
    static public function camelCaseValues ($array)
    {
        foreach ($array as $key => $value) {
            if (is_string($value)) {
                $value = Stdlib\StringUtils::camelCase($value, false);
            }
            elseif (is_array($value)) {
                $value = call_user_func(__METHOD__, $value);
            }
            $array[$key] = $value;
        }
        return $array;
    }

    /**
     * Converts the keys of an array to under_score format
     *
     * @param array|\Traversable|\Iterator $array
     * @return array
     */
    static public function underscoreKeys ($array)
    {
        foreach ($array as $key => $value) {
            $originalKey = $key;
            $key = Stdlib\StringUtils::underscore($key, false);
            unset($array[$originalKey]);
            if (is_array($value)) {
                $value = call_user_func(__METHOD__, $value);
            }
            $array[$key] = $value;
        }
        return $array;
    }

    /**
     * Converts the values of an array to underscore format
     *
     * @param array $array
     * @return array
     */
    static public function underscoreValues ($array)
    {
        foreach ($array as $key => $value) {
            if (is_string($value)) {
                $value = Stdlib\StringUtils::underscore($value, false);
            }
            elseif (is_array($value)) {
                $value = call_user_func(__METHOD__, $value);
            }
            $array[$key] = $value;
        }
        return $array;
    }

    /**
     * Merges 2 or more arrays recursively, replacing each cell with the values of the
     * latter array
     *
     * @param array (Array1, Array2, Array3)
     * @return array
     */
    static public function recursiveMerge ()
    {
        if (func_num_args() < 2) {
            trigger_error(__METHOD__ . ' needs two or more array arguments', E_USER_WARNING);
            return;
        }

        // get all the method arguments
        $arrays = func_get_args();

        // if the last argument is a boolean, then the keys of the arrays are rendered
        // as string instead of numeric
        $keysAsString = false;
        if (is_bool($arrays[count($arrays) - 1])) {
            $keysAsString = array_pop($arrays);
        }

        // create a new array
        $merged = array();
        while ($arrays) {
            $array = array_shift($arrays);

            if (!is_array($array) && !($array instanceof \Traversable)) {
                trigger_error(__METHOD__ . ' encountered a non array argument', E_USER_WARNING);
                return;
            }
            if (!$array && !$array instanceof \Traversable) {
                continue;
            }
            foreach ($array as $key => $value) {
                if ($keysAsString === true) {
                    $key = (string) $key;
                    if (is_array($value) && array_key_exists($key, $merged) && is_array($merged[$key])) {
                        $merged[$key] = call_user_func(__METHOD__, $merged[$key], $value);
                    }
                    else {
                        $merged[$key] = $value;
                    }
                }
                else {
                    $merged[] = $value;
                }
            }
        }
        return $merged;
    }

    /**
     * Merge two arrays together.
     *
     * If an integer key exists in both arrays, the value from the second array
     * will be appended the the first array. If both values are arrays, they
     * are merged together, else the value of the second array overwrites the
     * one of the first array.
     *
     * If the third argument is a boolean value, it checks if the value from either
     * array is NULL and uses the non-NULL value from either array
     *
     * @param array $a
     * @param array $b
     * @param boolean $preserveNumericKeys Flag whether to preserve the numeric keys in merged arrays
     * @param boolean $replaceNull Flag whether to replace null values with non null values in either arrays
     * @return array
     */
    static public function merge (array $a, array $b, $preserveNumericKeys = false, $replaceNull = false)
    {
        $args = func_get_args();
        $argc = func_num_args();

        // flag if null values should be replaced by second array values
        $replaceNull = false;
        if ($argc >= 3 && is_bool($args[count($args) - 1])) {
            $replaceNull = array_pop($args);
        }

        // use the default if just normal merge is required
        if (!$replaceNull) {
            return parent::merge($a, $b, $preserveNumericKeys);
        }

        // replace null values with non-null values from either arrays
        foreach ($b as $key => $value) {
            if (array_key_exists($key, $a)) {
                if (is_null($value)) {
                    $value = $a[$key];
                }
                if (is_int($key)) {
                    $a[] = $value;
                }

                // recursive
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
     * @param array $array1
     * @param array $array2
     * @return array
     */
    static public function intersect (array $array1, array $array2)
    {
        $args = func_get_args();
        $argc = func_num_args();

        if ($argc <= 1 || count($args) <= 1) {
            throw new \InvalidArgumentException(sprintf('Function "%1$s" requires at least 2 arguments', __METHOD__), 500);
        }

        $return = [];

        return $return;
    }

    /**
     * @param array $array1
     * @param array $array2
     * @return array|void
     */
    static public function recursiveDiff (array $array1, array $array2)
    {
        $args = func_get_args();
        $argc = func_num_args();

        if ($argc <= 1 || count($args) <= 1) {
            trigger_error(__METHOD__ . ' needs two or more array arguments', E_USER_WARNING);
            return;
        }

        $return = [];
        foreach ($array1 as $key => $value) {
            if (array_key_exists($key, $array2)) {
                if (is_array($value)) {
                    $recursiveDiff = static::recursiveDiff($value, $array2[$key]);
                    if (count($recursiveDiff)) {
                        $return[$key] = $recursiveDiff;
                    }
                }
                else {
                    if ($value != $array2[$key]) {
                        $return[$key] = $value;
                    }
                }
            }
            else {
                $return[$key] = $value;
            }
        }
        return $return;
    }

    /**
     * Recursive diff based on column values of 2 arrays
     * @param array $array1
     * @param array $array2
     * @param string $arrayKey
     * @return array
     */
    static public function recursiveDiffValue ($array1, $array2, $arrayKey = null)
    {
        $args = func_get_args();
        $argc = func_num_args();

        if ($argc < 2 || count($args) < 2) {
            trigger_error(__METHOD__ . ' needs two or more array arguments', E_USER_WARNING);
            return;
        }

        if ($arrayKey === null) {
            $arrayKey = key(current($array1));
        }

        $return = array_udiff(static::toArray($array1), static::toArray($array2), function  ($item1, $item2) use ($arrayKey)
        {
            return strcasecmp($item1[$arrayKey], $item2[$arrayKey]);
        });

        return $return;
    }

    /**
     * Resets the key using the second array and the keys are not present removes the key
     * @param array $array
     * @param array $indices
     * @param array $array
     * @param array $indices
     *
     * @return array
     */
    static public function reIndex2dArray (array $array, array $indices)
    {
        ksort($array);
        $reindexed = [];
        $argc = func_num_args();
        if ($argc < 2) {
            trigger_error(__METHOD__ . ' needs two or more array arguments', E_USER_WARNING);
            return;
        }

        foreach ($array as $key => $value) {
            if (!is_array($value)) {
                continue;
            }
            $extractedValues = array_intersect_key($value, $indices);

            $arr = [];
            foreach ($indices as $someKey => $newKey){
                $arr[$newKey] = array_key_exists($someKey, $extractedValues) ? $extractedValues[$someKey] : null;
            }
            $reindexed[$key] = $arr;
        }
        return $reindexed;
    }

    /**
     * Recursively flatten a multidimensional array
     *
     * @param array $array
     * @return array boolean
     */
    static public function flatten (array $array)
    {
        $flat = array();
        foreach (new \RecursiveIteratorIterator(new \RecursiveArrayIterator($array), \RecursiveIteratorIterator::SELF_FIRST) as $key => $value) {
            if (!is_array($value)) {
                $flat[] = $value;
            }
        }

        return $flat;
    }

    /**
     * @param array $array
     * @return array
     */
    static public function flat (array $array)
    {
        $flat = [];
        foreach ($array as $key => $item) {
            if (is_array($item)) {
                $flat = static::merge($flat, static::flat($item));
                continue;
            }
            $flat[$key] = $item;
        }

        $flat = array_unique($flat);
        sort($flat);
        return $flat;
    }

    /**
     * @param $set
     * @return array
     */
    static public function toArray ($set)
    {
        if(is_array($set)){
            return $set;
        }
        if (is_object($set)) {
        	$set = Stdlib\ObjectUtils::toArray($set);
        }

        $return = [];
        foreach ($set as $key => $item) {
            $return[$key] = Stdlib\ObjectUtils::toArray($item);
        }
        return $return;
    }

    /**
     * Converts a multidimensional array to XML format
     *
     * @param array $array
     * @param string $rootNode The wrapping node in the XML
     * @return string The XML string
     */
    static public function toXml (array $array, $rootNode = '<root></root>')
    {
        $xml = new \SimpleXMLElement($rootNode);
        static::createXmlNodes($array, $xml);
        return $xml->asXML();
    }

    /**
     * Adds nodes to the XML
     *
     * @param $array
     * @param $xml
     * @return mixed
     */
    static public function createXmlNodes ($array, &$xml)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (!is_numeric($key)) {
                    $subnode = $xml->addChild((string) $key);
                    static::createXmlNodes($value, $subnode);
                }
                else {
                    static::createXmlNodes($value, $xml);
                }
            }
            else {
                $xml->addChild($key, $value);
            }
        }
        return $xml;
    }

    /**
     * @param array $array
     */
    static public function normaliseColumnNames (array $array)
    {
        $colLastName = [ 'last_name', 'surname' ];
        $colEmail = [ 'email', 'email_address' ];
        $colAddress = [ 'street_address', 'street_address1', 'address1', 'address' ];
        $colTelephone = [ 'telephone', 'phone', 'landline', 'phone1', 'home', 'work' ];
        $colMobileNumber = [ 'mobile_number', 'mobile', 'cell', 'cell_phone', 'cellphone', 'phone2' ];
        $colState = [ 'state', 'province', 'region', 'prefecture' ];
        $colSuburb = [ 'suburb', 'county', 'town', 'city' ];
        $colPostcode = ['postcode','zip'];
    }

    /**
     * Extract values with keys containing strings following a regular expression
     *
     * @param array $input
     * @param string $pattern
     * @return array
     */
    static public function extractValuesByRegexKeys (array $input, $pattern = 'string', $flags = 0)
    {
        $keys = preg_grep('/^' . $pattern . '/i', array_keys($input), $flags);
        $keys = array_combine($keys, $keys);
        return array_intersect_key($input, $keys);

        // alternative:
        // $return = array();
        // foreach ($keys as $key) {
        // $return[$key] = $input[$key];
        // }
        // return $return;
    }

    /**
     * @param array $input
     * @param string $pattern
     * @param int $flags
     * @return array
     */
    static public function groupValuesByRegexKeys (array $input, $pattern = 'string', $flags = 0)
    {
        // reduce the input to array containing keys meeting the pattern
        $keys = preg_grep('/^' . $pattern . '/i', array_keys($input), $flags);
        $keys = array_combine($keys, $keys);
        $extract = array_intersect_key($input, $keys);

        // group them into an array
        $array = [];
        foreach ($extract as $key => $value) {
            $key = preg_replace('/^' . $pattern . '/i', '', $key);
            $array[$key] = $value;
        }
        return $array;
    }

    /**
     * @param array $data
     * @param array $columns
     * @return array
     */
    static public function sortArrayByArray (array $data, array $columns)
    {
        $columns = array_flip($columns);
        $ordered = [];
        foreach ($data as $key => $value) {
            if (array_key_exists($key, $columns)) {
                $ordered[$key] = $value;
                unset($data[$key]);
            }
        }
        return $ordered;
    }

    /**
     * Re-orders an array
     *
     * @param (array $array string $field string $order)
     * @return array
     */
    static public function orderBy ()
    {
        $args = func_get_args();
        $data = array_shift($args);

        if (!is_array($data)) {
            return array();
        }

        $multisortParams = array();
        foreach ($args as $n => $field) {
            if (is_string($field)) {
                $tmp = array();
                foreach ($data as $row) {
                    $tmp[] = $row[$field];
                }
                $args[$n] = $tmp;
            }
            $multisortParams[] = &$args[$n];
        }

        $multisortParams[] = &$data;
        call_user_func_array('array_multisort', $multisortParams);
        return end($multisortParams);
    }

    /**
     * Append contents of arrays to the end of an array
     *
     * @param array $array
     * @return boolean|number
     */
    static public function arrayPush (array &$array)
    {
        $args = func_get_args();
        $argc = func_num_args();
        if ($argc < 2) {
            trigger_error(sprintf('%s: expects at least 2 parameters, %s given', __FUNCTION__, $argc), E_USER_WARNING);
            return false;
        }

        array_shift($args);
        foreach ($args as $key => $item) {
            if (is_array($item)) {
                if (count($item) > 0) {
                    foreach ($item as $subKey => $subItem) {
                        $array[$subKey] = $subItem;
                    }
                }
            }
            else {
                $array[$key] = $item;
            }
        }

        return count($array);
    }

    /**
     * @param $needle
     * @param $haystack
     * @param $field
     * @return bool
     */
    static public function inMultiArray ($needle, $haystack, $field)
    {
        $top = sizeof($haystack) - 1;
        $bottom = 0;
        while ($bottom <= $top) {
            if ($haystack[$bottom][$field] == $needle){
                return true;
            }
            else if (is_array($haystack[$bottom][$field])){
                if (static::inMultiArray($needle, ($haystack[$bottom][$field]), $field)){
                    return true;
                }
            }

            $bottom++;
        }
        return false;
    }

    /**
     * @param $field
     * @param $haystack
     */
    static public function findInMultiArray ($field, $haystack)
    {
    }

    /**
     * @param array $array
     * @param $pattern
     */
    static public function fnmathKey (array $array, $pattern)
    {
    }

    /**
     * @param array $array
     * @param $pattern
     * @return array
     */
    static public function fnmatchValue (array $array, $pattern)
    {
        $found = array_filter($array, function  ($value) use( $pattern)
        {
            return fnmatch($value, $pattern, FNM_CASEFOLD) ? true : false;
        });

        return $found;
    }

    /**
     * Cleans an array
     *
     * @param array $array
     * @return array
     */
    static public function underscoreFieldNormaliseValue (array $array)
    {
        $array = array_map(function  ($item)
        {
            // convert each field (key) to underscore
            $heading = array_map(function  ($a)
            {
                return Stdlib\StringUtils::underscore(trim($a));
            }, array_keys($item));

            // remove null or N/A and trim spaces in values
            $item = array_map(function  ($a)
            {
                return strtoupper(trim($a)) == 'N/A' || trim($a) == '' || trim($a) == 'null' ? null : trim($a);
            }, $item);

            // return the normalised row
            return array_combine($heading, $item);
        }, $array);

        return $array;
    }
}