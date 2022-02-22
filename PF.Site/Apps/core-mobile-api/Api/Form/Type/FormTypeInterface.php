<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 24/5/18
 * Time: 7:44 PM
 */

namespace Apps\Core_MobileApi\Api\Form\Type;


interface FormTypeInterface
{
    function getStructure();

    function getComponentName();
}