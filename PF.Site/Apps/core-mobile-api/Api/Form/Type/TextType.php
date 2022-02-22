<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 25/5/18
 * Time: 3:43 PM
 */

namespace Apps\Core_MobileApi\Api\Form\Type;


use Apps\Core_MobileApi\Api\Form\Validator\Filter\TextFilter;

class TextType extends GeneralType
{

    protected $componentName = "Text";

    protected $attrs = [
        'returnKeyType' => 'next'
    ];

    /**
     * Add filter content Security
     *
     * @param mixed $value
     * @param       $isPost
     *
     * @return GeneralType
     */
    public function setValue($value, $isPost = false)
    {
        $value = TextFilter::pureText($value);
        return parent::setValue($value, $isPost);
    }

}