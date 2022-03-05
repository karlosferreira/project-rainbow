<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 25/5/18
 * Time: 3:42 PM
 */

namespace Apps\Core_MobileApi\Api\Form\Type;


use Apps\Core_MobileApi\Api\Form\TransformerInterface;

class CountryStateType extends GeneralType implements TransformerInterface
{
    protected $componentName = 'Choice';

    protected $multiple = false;

    protected $valueType = "array";

    public function setAttrs($attrs)
    {
        if (!isset($attrs['value_type'])) {
            $attrs['value_type'] = $this->valueType;
        }
        return parent::setAttrs($attrs);
    }

    const COUNTRY = 'options';
    const STATE = 'suboptions';

    /**
     * This is require "country, state" attribute
     * @return bool
     */
    public function isValid()
    {
        if (!parent::isValid()) {
            return false;
        }
        if (!$this->isRequiredField() && $this->getValue() == null || empty($this->getOptions(self::COUNTRY))) {
            return true;
        }

        // Return invalid if the value not in option value
        $valid = true;
        $countryValues = $this->getCountryValues();
        $stateValues = $this->getStateValues();
        $values = $this->getValue();
        if (!is_array($values)) {
            $values = [$values];
        }

        if ($this->isRequiredField() && empty($values[0])) {
            return false;
        }

        $count = count($values);
        switch ($count) {
            case 0:
                $valid = true;
                break;
            case 1:
                if (!empty($values[0]) && (!is_string($values[0]) || !in_array($values[0], $countryValues))) {
                    $valid = false;
                }
                break;
            case 2:
                if (!empty($values[1]) && (!is_string($values[0]) || !in_array($values[0], $countryValues)
                        || !is_numeric($values[1]) || ($values[1] > 0 && !in_array($values[1], $stateValues[$values[0]])))) {
                    $valid = false;
                }
                break;
            default:
                $valid = false;
        }

        return $valid;
    }

    public function getOptions($type)
    {
        return $this->getAttr($type);
    }

    public function getCountryValues()
    {
        $result = [];
        $options = $this->getOptions(self::COUNTRY);
        foreach ($options as $option) {
            $result[] = $option['value'];
        }
        return $result;
    }

    public function getStateValues()
    {
        $result = [];
        $options = $this->getOptions(self::STATE);
        foreach ($options as $key => $option) {
            foreach ($option as $opt) {
                $result[$key][] = $opt['value'];
            }
        }
        return $result;
    }

    public function setOptions($type, $options)
    {
        $this->setAttr($type, $options);
    }

    public function transform($value)
    {
        return [
            'country_iso'      => (isset($value[0]) ? $value[0] : null),
            'country_child_id' => (isset($value[1]) ? (int)$value[1] : null)
        ];
    }

    public function reverseTransform($value)
    {
        $valueDefault = $this->getAttr('value_default');

        return [
            (isset($value['country_iso']) ? $value['country_iso'] : (isset($valueDefault[0]) ? $valueDefault[0] : null)),
            (isset($value['country_child_id']) ? (int)$value['country_child_id'] : (isset($valueDefault[1]) ? $valueDefault[1] : null))
        ];
    }

    public function getMetaValueFormat()
    {
        return "Array[country_iso, country_child_id]";
    }

    public function getMetaDescription()
    {
        return "Select Country and State control";
    }

    public function getAvailableAttributes()
    {
        $attrs = parent::getAvailableAttributes();
        $attrs[] = 'options';
        $attrs[] = 'returnKeyType';
        $attrs[] = 'suboptions';
        return $attrs;
    }
}