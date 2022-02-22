<?php

namespace Apps\Core_MobileApi\Api\Form\Type;


use Apps\Core_MobileApi\Api\Form\TransformerInterface;

class TagsType extends TextType implements TransformerInterface
{
    protected $componentName = 'Tags';

    public function transform($value)
    {
        return [
            $this->getName() => $value
        ];
    }

    public function reverseTransform($data)
    {
        $value = '';
        if (!empty($data[$this->getName()])) {
            foreach ($data[$this->getName()] as $key => $datum) {
                if (isset($datum['tag_text'])) {
                    $value .= $datum['tag_text'] . ',';
                }
            }
        }
        return rtrim($value, ',');
    }
}