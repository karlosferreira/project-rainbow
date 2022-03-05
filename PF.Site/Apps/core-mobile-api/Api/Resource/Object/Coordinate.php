<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 11/4/18
 * Time: 4:50 PM
 */

namespace Apps\Core_MobileApi\Api\Resource\Object;


class Coordinate
{
    public $latitude;
    public $longitude;

    public static function fromArray($data, $mapping)
    {
        $obj = new Coordinate();
        foreach ($obj as $key => $property) {
            if (isset($mapping[$key]) && isset($data[$mapping[$key]])) {
                $obj->$key = round($data[$mapping[$key]], 10);
            } else if (isset($data[$key])) {
                $obj->$key = round($data[$key], 10);
            }
        }
        return $obj;
    }

    public function toArray()
    {
        return (array)$this;
    }
}