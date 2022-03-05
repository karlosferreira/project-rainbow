<?php


namespace Apps\Core_MobileApi\Api\Form\Validator;


interface ValidateInterface
{
    /**
     * Validate value is valid or not
     *
     * @param mixed $value value to validate
     *
     * @return bool True if valid and False if not
     */
    function validate($value);

    /**
     * Get error message
     * @return string
     */
    function getError();
}