<?php

namespace Apps\Core_MobileApi\Api\Form\Type;


use Apps\Core_MobileApi\Api\Form\TransformerInterface;

class CustomGendersType extends TextType implements TransformerInterface
{
    protected $componentName = 'CustomGenders';

    public function transform($value)
    {
        $transformValue = explode(',', $value);
        $finalValue = [];
        if (count($transformValue)) {
            foreach ($transformValue as $item) {
                if (!in_array($item, $finalValue)) {
                    $finalValue[] = $item;
                }
            }
        }
        return [
            $this->getName() => $finalValue
        ];
    }

    public function reverseTransform($data)
    {
        $value = '';
        if (!empty($data[$this->getName()])) {
            if (!is_array($data[$this->getName()])) {
                $values = unserialize($data[$this->getName()]);
            } else {
                $values = $data[$this->getName()];
            }
            foreach ($values as $key => $datum) {
                $value .= $datum . ',';
            }
        }
        return rtrim($value, ',');
    }
}