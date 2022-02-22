<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 18/6/18
 * Time: 4:48 PM
 */

namespace Apps\Core_MobileApi\Adapter\Utility;


class ArrayUtility
{

    /**
     * Merge 2 array
     *
     * @param $array1
     * @param $array2
     *
     * @return array
     */
    public static function merge($array1, $array2)
    {
        if (null == $array1) {
            return $array2;
        }
        if (null == $array2) {
            return $array1;
        }
        return array_merge($array1, $array2);
    }

    /**
     * Append more field into a array
     *
     * @param $main
     * @param $more
     */
    public static function append(&$main, $more)
    {
        if (empty($more) || !is_array($more)) {
            return;
        }
        // This is key/value base array
        if (array_keys($more) === range(0, count($more) - 1)) {
            foreach ($more as $value) {
                $main[] = $value;
            }
        } else {
            foreach ($more as $key => $value) {
                $main[$key] = $value;
            }
        }

    }

}