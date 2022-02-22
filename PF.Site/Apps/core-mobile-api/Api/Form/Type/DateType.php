<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 28/5/18
 * Time: 10:04 AM
 */

namespace Apps\Core_MobileApi\Api\Form\Type;


use Apps\Core_MobileApi\Api\Form\Validator\DateTimeFormatValidator;
use Core\Request\Exception;

class DateType extends GeneralType
{
    const FORMAT = "Y-m-d";

    protected $componentName = 'Date';

    protected $attrs = [
        'returnKeyType' => 'next'
    ];

    public function setValidators($validators)
    {
        $validators[] = new DateTimeFormatValidator(self::FORMAT);
        return parent::setValidators($validators);
    }

    public function getValue()
    {
        $value =  parent::getValue();
        if ($value) {
            try {
                $date = \DateTime::createFromFormat('Y-m-d', $value);
            } catch (Exception $e) {
                return $value;
            }
            if ($date instanceof \DateTime) {
                if ($this->getAttr('timestamp')) {
                    $value = $date->getTimestamp();
                } elseif ($this->getAttr('separate')) {
                    $prefix = $this->getAttr('prefix') ? $this->getAttr('prefix') : $this->getName();
                    $value = [
                        $prefix . 'year'  => $date->format('Y'),
                        $prefix . 'month' => $date->format('m'),
                        $prefix . 'day'   => $date->format('d'),
                    ];
                }
            }
        }
        return $value;
    }
}