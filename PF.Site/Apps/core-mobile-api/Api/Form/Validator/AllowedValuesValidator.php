<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 29/6/18
 * Time: 5:36 PM
 */

namespace Apps\Core_MobileApi\Api\Form\Validator;

use Apps\Core_MobileApi\Api\Exception\ErrorException;

class AllowedValuesValidator implements ValidateInterface
{

    protected $allowedValues;

    /**
     * AllowedValuesValidator constructor.
     *
     * @param $values
     *
     * @throws ErrorException
     */
    public function __construct($values)
    {
        if (empty($values) || !is_array($values)) {
            throw new ErrorException('Array Values is required');
        }
        $this->allowedValues = $values;
    }

    /**
     * Validate value is valid or not
     *
     * @param mixed $value value to validate
     *
     * @return bool True if valid and False if not
     */
    function validate($value)
    {
        return in_array($value, $this->allowedValues);
    }

    /**
     * Get error message
     * @return string
     */
    function getError()
    {
        return "Only allow values " . implode(", ", $this->allowedValues);
    }
}