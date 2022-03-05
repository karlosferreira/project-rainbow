<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 12/6/18
 * Time: 2:49 PM
 */

namespace Apps\Core_MobileApi\Api\Form\Validator\Filter;


use Apps\Core_MobileApi\Api\Exception\ErrorException;

class NumberFilter
{

    /**
     * Get number between max and min
     *
     * @param     $number Number to check
     * @param int $min    minimum allowed
     * @param int $max    maximum allowed
     *
     * @return int
     * @throws ErrorException
     * @internal param $number1
     * @internal param $number2
     */
    public static function getBetween($number, $min, $max)
    {
        if (!is_numeric($number)) {
            throw new ErrorException("Invalid number");
        }
        return ($number < $min ? (int)$min : ($number > $max ? (int)$max : (int)$number));
    }

    /**
     * Get max between 2 number
     *
     * @param mixed $number number to check
     * @param int   $min    minimum allowed
     *
     * @return int
     * @throws ErrorException
     */
    public static function getMax($number, $min)
    {
        if (!is_numeric($number)) {
            throw new ErrorException("Invalid number");
        }
        return ($number > $min ? (int)$number : (int)$min);
    }

    /**
     * Get min between 2 number
     *
     * @param $number
     * @param $max
     *
     * @return int
     * @throws ErrorException
     */
    public static function getMin($number, $max)
    {
        if (!is_numeric($number)) {
            throw new ErrorException("Invalid number");
        }
        return ($number < $max ? (int)$number : (int)$max);
    }

    /**
     * Get unsigned number
     *
     * @param $number
     *
     * @return int
     * @throws ErrorException
     */
    public static function getNoneZeroUnsigned($number)
    {
        if (!is_numeric($number) || $number <= 0) {
            throw new ErrorException("Invalid number");
        }
        return (int)$number;
    }

}