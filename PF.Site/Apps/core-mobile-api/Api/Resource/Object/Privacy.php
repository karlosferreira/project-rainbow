<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 11/4/18
 * Time: 4:50 PM
 */

namespace Apps\Core_MobileApi\Api\Resource\Object;


class Privacy
{
    public $privacy;
    public $privacy_comment;

    public static function fromArray($data)
    {
        $obj = new Privacy();
        foreach ($obj as $key => $property) {
            if (isset($data[$key])) {
                $obj->$key = $data[$key];
            }
        }
        return $obj;
    }

    public function toArray()
    {
        return (array)$this;
    }
}