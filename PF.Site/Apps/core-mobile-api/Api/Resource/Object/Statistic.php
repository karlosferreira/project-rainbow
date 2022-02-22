<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 11/4/18
 * Time: 4:53 PM
 */

namespace Apps\Core_MobileApi\Api\Resource\Object;


use Apps\Core_MobileApi\Api\Mapping\ResourceMetadata;

class Statistic
{
    public $total_like;
    // public $total_dislike;
    public $total_comment;
    public $total_view;
    public $total_attachment;


    public static function fromArray($data)
    {
        $obj = new Statistic();
        foreach ($obj as $key => $property) {
            if (isset($data[$key])) {
                $obj->$key = ResourceMetadata::convertValue($data[$key], ['type' => ResourceMetadata::INTEGER]);
            }
        }
        return $obj;
    }

    public function toArray()
    {
        $return = (array)$this;
        foreach ($return as $key => $value) {
            if ($value === null) {
                unset($return[$key]);
            }
        }

        return $return;
    }
}