<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 28/5/18
 * Time: 10:05 AM
 */

namespace Apps\Core_MobileApi\Api\Form\Type;


class ColorType extends GeneralType
{
    protected $componentName = 'Color';

    public function getMetaDescription()
    {
        return "Color picker control";
    }

    public function getMetaValueFormat()
    {
        return "Text";
    }
}