<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 28/5/18
 * Time: 10:33 AM
 */

namespace Apps\Core_MobileApi\Api\Form\Type;


class RadioType extends AbstractOptionType
{

    protected $componentName = 'Radio';

    public function getMetaValueFormat()
    {
        return "mixed";
    }

    public function getMetaDescription()
    {
        return "Radio checkbox";
    }
}