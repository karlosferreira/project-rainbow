<?php

namespace Apps\Core_MobileApi\Api\Form\Type;


class CheckboxType extends GeneralType
{

    const ON = 1;
    const OFF = 0;

    protected $componentName = 'Checkbox';

    public function isValid()
    {
        if ($this->isRequiredField() && !$this->isChecked()) {
            if ($this->name == 'agree') {
                $this->setError('check_our_agreement_in_order_to_join_our_site');
            } else {
                $this->setError(self::REQUIRED_FIELD_ERROR);
            }
            return false;
        }
        return true;
    }

    public function isChecked()
    {
        if (!empty($this->value) && $this->value == self::ON) {
            return true;
        }
        return false;
    }

    public function getAvailableAttributes()
    {
        return [
            'label', 'description', 'value', 'required', 'returnKeyType',
        ];
    }

    public function getMetaDescription()
    {
        return 'Checkbox only allow 0(unchecked)/1(checked) value';
    }

    public function getMetaValueFormat()
    {
        return "[0,1]";
    }

}