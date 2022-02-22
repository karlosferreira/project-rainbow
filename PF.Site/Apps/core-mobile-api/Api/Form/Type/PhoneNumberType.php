<?php

namespace Apps\Core_MobileApi\Api\Form\Type;


use Phpfox;

class PhoneNumberType extends GeneralType implements FormTypeInterface
{
    protected $componentName = 'PhoneNumber';

    protected $attrs = [
        'returnKeyType' => 'next'
    ];

    public function setAttrs($attrs)
    {
        if (empty($attrs['keyboard_type'])) {
            $attrs['keyboard_type'] = 'numeric';
        }
        if (empty($attrs['default_country_iso'])) {
            $attrs['default_country_iso'] = $this->getDefaultCountryIso();
        }
        return parent::setAttrs($attrs);
    }

    public function isValid()
    {
        if ($this->getAttr('ignore_validate')) {
            return true;
        }
        if (!parent::isValid()) {
            return false;
        }
        $phoneLib = Phpfox::getLib('phone');
        $value = $this->getValue();
        if ((empty($value) && !$this->isRequiredField()) || ($phoneLib->setRawPhone($value) && $phoneLib->isValidPhone())) {
            return true;
        }
        return false;
    }

    public function getDefaultCountryIso()
    {
        return Phpfox::getLib('request')->getIpInfo(null, 'country_code');
    }
}