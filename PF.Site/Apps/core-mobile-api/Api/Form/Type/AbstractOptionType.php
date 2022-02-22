<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 8/6/18
 * Time: 11:55 AM
 */

namespace Apps\Core_MobileApi\Api\Form\Type;


use Apps\Core_MobileApi\Adapter\Localization\LocalizationInterface;
use Apps\Core_MobileApi\Api\Exception\ErrorException;

abstract class AbstractOptionType extends GeneralType
{

    /**
     * This is require "options" attribute
     * @return bool
     */
    public function isValid()
    {
        if (!parent::isValid()) {
            return false;
        }
        $values = $this->getValue();
        if (!$this->isRequiredField() && ($values === null || (int)$values === 0 || $values === '' || empty($this->getOptions()) )) {
            return true;
        }

        // Return invalid if the value not in option value
        $valid = true;
        $optionValues = $this->getOptionValues();
        if (!is_array($values)) {
            $values = [$values];
        }
        foreach ($values as $item) {
            if (!in_array($item, $optionValues)) {
                $valid = false;
                break;
            }
        }

        return $valid;
    }

    /**
     * @param LocalizationInterface|null $trans
     *
     * @return array
     * @throws ErrorException
     */
    public function getStructure(LocalizationInterface $trans = null)
    {
        if ($this->getOptions() === null) {
            throw new ErrorException("`options` attribute is required for `" . $this->getComponentName() . "` component");
        }
        return parent::getStructure($trans);
    }

    public function getOptions()
    {
        return $this->getAttr('options');
    }

    public function getOptionValues()
    {
        $result = [];
        $options = $this->getOptions();
        foreach ($options as $option) {
            // Cast value to string to fix issue 1 == "1x"
            $result[] = (string)$option['value'];
        }
        return $result;
    }

    public function setOptions($options)
    {
        $this->setAttr('options', $options);
    }


    public function getAvailableAttributes()
    {
        $attrs = parent::getAvailableAttributes();
        $attrs[] = ['options'];
        $attrs['returnKeyType'] = 'next';
        return $attrs;
    }

    public function getValue()
    {
        if ($this->value === null && $this->hasAttr('value_default')) {
            return $this->getAttr('value_default');
        }
        if (is_array($this->value) && count($this->value) > 0) {
            $newVal = null;
            foreach ($this->value as $val) {
                if (isset($val) && $val !== '') {
                    $newVal[] = $val;
                }
            }
            $this->value = $newVal;
        }
        return $this->value;
    }
}