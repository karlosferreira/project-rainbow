<?php

namespace Apps\Core_MobileApi\Api\Form\Type;


class ChoiceType extends AbstractOptionType
{
    protected $componentName = 'Choice';

    protected $valueType = "numeric";

    protected $attrs = [
        'returnKeyType' => 'next'
    ];

    public function setAttrs($attrs)
    {
        if (!isset($attrs['value_type'])) {
            $attrs['value_type'] = $this->valueType;
        }
        return parent::setAttrs($attrs);
    }

    /**
     * @return bool
     */
    public function isMultiple()
    {
        return false;
    }

    public function getAvailableAttributes()
    {
        $attrs = parent::getAvailableAttributes();
        $attrs[] = 'options';
        $attrs[] = 'returnKeyType';
        return $attrs;
    }

    public function getMetaDescription()
    {
        return "Single Select Type";
    }

}