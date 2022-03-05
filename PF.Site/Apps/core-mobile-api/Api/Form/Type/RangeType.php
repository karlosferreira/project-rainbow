<?php

namespace Apps\Core_MobileApi\Api\Form\Type;


use Apps\Core_MobileApi\Api\Form\TransformerInterface;

class RangeType extends GeneralType implements TransformerInterface
{
    protected $componentName = 'Range';

    protected $multiple = false;

    protected $valueType = "array";

    public function setAttrs($attrs)
    {
        if (!isset($attrs['value_type'])) {
            $attrs['value_type'] = $this->valueType;
        }
        return parent::setAttrs($attrs);
    }

    /**
     * This is require "country, state" attribute
     * @return bool
     */
    public function isValid()
    {
        if (!parent::isValid()) {
            return false;
        }
        if (!$this->isRequiredField() && $this->getValue() == null) {
            return true;
        }
        $value = $this->getValue();
        $minValue = $this->getAttr('min_value');
        $maxValue = $this->getAttr('max_value');
        $valid = true;

        if (isset($value['from']) && $value['from'] < $minValue) {
            $valid = false;
        }
        if (isset($value['to']) && $value['to'] > $maxValue) {
            $valid = false;
        }
        if (isset($value['from'], $value['to']) && $value['from'] > $value['to']) {
            $valid = false;
        }
        // Return invalid if the value not in option value
        return $valid;
    }

    public function getOptions($type)
    {
        return $this->getAttr($type);
    }

    public function setOptions($type, $options)
    {
        $this->setAttr($type, $options);
    }

    public function transform($value)
    {
        $fromField = $this->getOptions('from_field_key');
        $toField = $this->getOptions('to_field_key');
        return [
            $fromField => (isset($value['from']) ? $value['from'] : null),
            $toField   => (isset($value['to']) ? $value['to'] : null)
        ];
    }

    public function reverseTransform($value)
    {
        $valueDefault = $this->getAttr('value_default');
        $fromField = $this->getOptions('from_field_key');
        $toField = $this->getOptions('to_field_key');
        return [
            'from' => (isset($value[$fromField]) ? $value[$fromField] : (!empty($valueDefault[$fromField]) ? $valueDefault[$fromField] : null)),
            'to'   => (isset($value[$toField]) ? (int)$value[$toField] : (!empty($valueDefault[$toField]) ? $valueDefault[$toField] : null))
        ];
    }

    public function getMetaValueFormat()
    {
        return "Array['from' => 1, 'to' => 100]";
    }

    public function getMetaDescription()
    {
        return "Select in range";
    }

    public function getAvailableAttributes()
    {
        $attrs = parent::getAvailableAttributes();
        $attrs[] = 'min_value';
        $attrs[] = 'max_value';
        $attrs[] = 'returnKeyType';
        return $attrs;
    }
}