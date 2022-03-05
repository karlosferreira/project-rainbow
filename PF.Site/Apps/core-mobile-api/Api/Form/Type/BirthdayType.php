<?php

namespace Apps\Core_MobileApi\Api\Form\Type;


use Apps\Core_MobileApi\Api\Form\TransformerInterface;
use Apps\Core_MobileApi\Api\Form\Validator\DateTimeFormatValidator;
use DateTime;

class BirthdayType extends GeneralType implements TransformerInterface
{
    const FORMAT = 'Y-m-d';

    protected $componentName = "Birthday";

    protected $attrs = [
        'returnKeyType' => 'next'
    ];

    public function setValidators($validators)
    {
        $validators[] = new DateTimeFormatValidator(self::FORMAT);
        return parent::setValidators($validators);
    }

    public function getMetaValueFormat()
    {
        return self::FORMAT . " (ex: 2000-02-03)";
    }

    public function getMetaDescription()
    {
        return "Birthday input control";
    }

    /**
     * Convert client format to database format
     *
     * @param $value
     *
     * @return mixed
     */
    public function transform($value)
    {
        if (empty($value) || !($date = DateTime::createFromFormat(self::FORMAT, $value))) {
            return $value;
        }

        return [
            $this->getName() => ($date->format('m') . $date->format('d') . $date->format('y')),
            'month'          => $date->format('m'),
            'day'            => $date->format('d'),
            'year'           => $date->format('Y')
        ];
    }

    /**
     * Convert database format to client format
     *
     * @param $data
     *
     * @return mixed
     */
    public function reverseTransform($data)
    {
        if (empty($data)
            || empty($data[$this->getName()])
            || !($date = DateTime::createFromFormat("mdY", $data[$this->getName()]))) {
            return null;
        }
        return $date->format(self::FORMAT);
    }
}