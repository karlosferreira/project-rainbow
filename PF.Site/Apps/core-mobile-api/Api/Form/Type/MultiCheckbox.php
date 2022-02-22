<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 15/6/18
 * Time: 11:52 AM
 */

namespace Apps\Core_MobileApi\Api\Form\Type;


class MultiCheckbox extends MultiChoiceType
{
    protected $componentName = "Choice";

    protected $valueType = "array";

    protected $multiple = true;

    public function setAttrs($attrs)
    {
        if (!isset($attrs['value_type'])) {
            $attrs['value_type'] = $this->valueType;
        }
        return parent::setAttrs($attrs);
    }

    public function getMetaDescription()
    {
        return "Multiple Checkbox control";
    }

    public function getMetaValueFormat()
    {
        return 'Array';
    }

}