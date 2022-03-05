<?php


namespace Apps\Core_MobileApi\Api\Form\Type;


use Apps\Core_MobileApi\Api\Form\Validator\DateTimeFormatValidator;

class TimeType extends GeneralType
{
    const FORMAT = "H:i";
    protected $componentName = "Time";

    protected $attrs = [
        'returnKeyType' => 'next'
    ];

    public function setValidators($validators)
    {
        $validators[] = new DateTimeFormatValidator(self::FORMAT);
        return parent::setValidators($validators);
    }

    public function getMetaValueFormat()
    {
        return "H:i (example: 23:01)";
    }

    public function getMetaDescription()
    {
        return "Time Picker control";
    }
}