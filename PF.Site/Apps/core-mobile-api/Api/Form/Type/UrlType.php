<?php

namespace Apps\Core_MobileApi\Api\Form\Type;


use Apps\Core_MobileApi\Api\Form\Validator\PatternValidator;

class UrlType extends GeneralType
{
    protected $componentName = "Url";

    protected $attrs = [
        'returnKeyType' => 'next'
    ];

    public function setValidators($validators)
    {
        $validators[] = new PatternValidator(PatternValidator::URL_REGEX);
        return parent::setValidators($validators);
    }

    public function getAvailableAttributes()
    {
        return [
            'label',
            'value',
            'returnKeyType',
            'preview_endpoint'
        ];
    }
}