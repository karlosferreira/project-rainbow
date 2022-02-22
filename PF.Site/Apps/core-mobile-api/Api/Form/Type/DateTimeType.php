<?php

namespace Apps\Core_MobileApi\Api\Form\Type;


use Apps\Core_MobileApi\Api\Form\Validator\DateTimeFormatValidator;

class DateTimeType extends GeneralType
{
    protected $componentName = 'DateTime';

    protected $attrs = [
        'returnKeyType' => 'next'
    ];

    public function setValidators($validators)
    {
        $validators[] = new DateTimeFormatValidator(\DateTime::ISO8601);
        return parent::setValidators($validators);
    }

    public function getMetaValueFormat()
    {
        return "ISO8601 = " . \DateTime::ISO8601;
    }

    public function getMetaDescription()
    {
        return "Date Time picker control";
    }

}