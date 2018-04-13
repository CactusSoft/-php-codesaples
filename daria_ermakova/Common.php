<?php

namespace TwentyThree;

/**
 * Class contains some common frequently needed data
 *
 * Class Common
 */
class Common
{

    /**
     * @param array|object $collection
     * @param string $key
     * @param null $defaultValue
     * @return mixed|NULL
     */
    public static function emptyDefault($collection, $key, $defaultValue = NULL)
    {
        if (is_array($collection)) {
            return !empty($collection[$key]) ? $collection[$key] : $defaultValue;
        } elseif (is_object($collection)) {
            return !empty($collection->$key) ? $collection->$key : $defaultValue;
        }

        return $defaultValue;
    }
}
