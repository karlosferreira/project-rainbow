<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 28/5/18
 * Time: 10:08 AM
 */

namespace Apps\Core_MobileApi\Api\Form\Type;


use Apps\Core_MobileApi\Api\Form\Validator\NumberRangeValidator;
use Apps\Core_MobileApi\Api\Form\Validator\TypeValidator;

class PriceType extends GeneralType
{
    protected $componentName = 'Price';

    protected $attrs = [
        'returnKeyType' => 'next'
    ];

    public function setAttrs($attrs)
    {
        $attrs['keyboard_type'] = 'numeric';
        return parent::setAttrs($attrs);
    }

    public function setValidators($validators)
    {
        $validators[] = new TypeValidator(TypeValidator::IS_NUMERIC);
        $validators[] = new NumberRangeValidator(0);
        return parent::setValidators($validators);
    }
}