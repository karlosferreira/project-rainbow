<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 25/5/18
 * Time: 5:55 PM
 */

namespace Apps\Core_MobileApi\Api\Form\Validator;


class EmailValidator implements ValidateInterface
{
    protected $message;

    public function __construct($errorMessage = null)
    {
        $this->message = $errorMessage;
    }

    /**
     * @inheritdoc
     */
    function validate($value)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL);
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