<?php

namespace Apps\Core_MobileApi\Api\Form\Type;


use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\Form\Validator\TypeValidator;

class FriendSelectType extends GeneralType
{

    protected $componentName = "FriendSelect";

    public function setValidators($validators)
    {
        $validators[] = new TypeValidator(TypeValidator::IS_ARRAY_NUMERIC);
        return parent::setValidators($validators);
    }

    public function setAttrs($attrs)
    {
        $attrs['friend_search_api'] = UrlUtility::makeApiUrl("friend");
        return parent::setAttrs($attrs);
    }

    public function getMetaDescription()
    {
        return "Friend select component";
    }

}