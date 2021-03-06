<?php

namespace Apps\Core_MobileApi\Api\Form\Type;


class ActivityPointPackageType extends GeneralType
{
    protected $componentName = 'PointPackage';

    protected $multiple = false;

    protected $valueType = "numeric";

    public function setAttrs($attrs)
    {
        if (!isset($attrs['value_type'])) {
            $attrs['value_type'] = $this->valueType;
        }
        return parent::setAttrs($attrs);
    }

    const PACKAGE = 'options';

    public function isValid()
    {
        if (!parent::isValid()) {
            return false;
        }
        if (!$this->isRequiredField() && $this->getValue() == null || empty($this->getOptions(self::PACKAGE))) {
            return true;
        }

        $valid = true;
        $value = $this->getValue();

        if (($this->isRequiredField() && empty($value)) || (isset($value) && !is_numeric($value))) {
            $valid = false;
        }

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

    public function getMetaValueFormat()
    {
        return "Array[point_package_id]";
    }

    public function getMetaDescription()
    {
        return "Select Activity Point Package";
    }

    public function getAvailableAttributes()
    {
        $attrs = parent::getAvailableAttributes();
        $attrs[] = 'options';
        $attrs[] = 'returnKeyType';
        return $attrs;
    }

    public function getErrorMessage()
    {
        return $this->getLocal()->translate(empty($this->getValue()) ? 'please_select_an_activity_points_package' : 'invalid_activity_points_package');
    }
}