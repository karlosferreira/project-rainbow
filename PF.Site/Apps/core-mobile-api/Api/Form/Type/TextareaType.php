<?php

namespace Apps\Core_MobileApi\Api\Form\Type;

use Apps\Core_MobileApi\Api\Form\Validator\Filter\TextFilter;

class TextareaType extends GeneralType
{
    protected $componentName = 'TextArea';

    protected $attrs = [
        'returnKeyType' => 'default'
    ];

    public function setValue($value, $isPost = false)
    {
        $value = str_replace("<div class=\"newline\"></div>", "\n", $value);
        $value = str_replace("<p>", "", $value);
        $value = str_replace("</p>", "\n", $value);
        $value = str_replace("<br/>", "\n", $value);
        $value = trim($value);
        if (!$isPost) {
            $value = TextFilter::pureText($value, 0);
        }
        return parent::setValue($value, $isPost);
    }

}
