<?php

namespace Apps\Core_MobileApi\Api\Form\Validator;


use DateTime;

class DateTimeFormatValidator implements ValidateInterface
{
    protected $format;
    protected $error;

    public function __construct($format, $errorMessage = null)
    {
        $this->format = $format;
        if ($errorMessage) {
            $this->error = $errorMessage;
        }
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
        if (empty($value)) {
            return true;
        }
        return (DateTime::createFromFormat($this->format, $value) !== false);
    }

    /**
     * Get error message
     * @return string
     */
    function getError()
    {
        return '';
    }
}