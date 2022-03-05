<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 5/6/18
 * Time: 2:45 PM
 */

namespace Apps\Core_MobileApi\Api\Form\Type;


use Apps\Core_MobileApi\Api\Form\Validator\TypeValidator;

class MultiChoiceType extends ChoiceType
{
    protected $componentName = 'Choice';

    protected $valueType = "array";

    public function setAttrs($attrs)
    {
        if (!isset($attrs['value_type'])) {
            $attrs['value_type'] = $this->valueType;
        }
        return parent::setAttrs($attrs);
    }

    protected $multiple = true;

    /**
     * @return bool
     */
    public function isMultiple()
    {
        return true;
    }

    public function setValidators($validators)
    {
        $validators[] = new TypeValidator(TypeValidator::IS_ARRAY);
        return parent::setValidators($validators);
    }
}