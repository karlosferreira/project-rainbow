<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 28/5/18
 * Time: 10:09 AM
 */

namespace Apps\Core_MobileApi\Api\Form\Type;


class PasswordType extends TextType
{
    protected $attrs = [
        'returnKeyType' => 'next'
    ];

    protected $componentName = 'Password';
}