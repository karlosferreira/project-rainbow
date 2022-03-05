<?php

namespace Apps\Core_MobileApi\Api\Form\Validator;


class NumberRangeValidator implements ValidateInterface
{
    const UNLIMITED = -1;

    protected $message;

    protected $min;
    protected $max;

    public function __construct($min, $max = self::UNLIMITED, $errorMessage = null)
    {
        $this->message = $errorMessage;
        $this->min = $min;
        $this->max = $max;
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
        if ($value !== null) {
            if (!is_numeric($value)) {
                return false;
            }
            if ($value < $this->min) {
                return false;
            }
            if ($this->max != self::UNLIMITED && $value > $this->max) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get error message
     * @return string
     */
    function getError()
    {
        return $this->message;
    }
}