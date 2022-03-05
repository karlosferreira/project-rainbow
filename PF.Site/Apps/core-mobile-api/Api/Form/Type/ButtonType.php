<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 25/5/18
 * Time: 3:44 PM
 */

namespace Apps\Core_MobileApi\Api\Form\Type;


class ButtonType extends GeneralType
{
    protected $componentName = 'Button';

    public function getMetaDescription()
    {
        return "Button control";
    }

    public function getMetaValueFormat()
    {
        return null;
    }

    public function getAvailableAttributes()
    {
        return ['label', 'returnKeyType',];
    }

    public function getServerAttributes()
    {
        return null;
    }

}