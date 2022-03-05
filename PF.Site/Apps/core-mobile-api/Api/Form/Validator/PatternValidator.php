<?php

namespace Apps\Core_MobileApi\Api\Form\Validator;


class PatternValidator implements ValidateInterface
{
    const EMAIL_REGEX = '/^[0-9a-zA-Z]([\-+.\w]*[0-9a-zA-Z]?)*@([0-9a-zA-Z\-.\w]*[0-9a-zA-Z]\.)+[a-zA-Z]{2,}$/';
    const URL_REGEX = '/^(?:(ftp|http|https):)?(?:\/\/(?:((?:%[0-9a-f]{2}|[\-a-z0-9_.!~*\'\(\);:&=\+\$,])+)@)?(?:((?:[a-z0-9](?:[\-a-z0-9]*[a-z0-9])?\.)*[a-z](?:[\-a-z0-9]*[a-z0-9])?)|([0-9]{1,3}(?:\.[0-9]{1,3}){3}))(?::([0-9]*))?)?((?:\/(?:%[0-9a-f]{2}|[\-a-z0-9_.!~*\'\(\):@&=\+\$,;])+)+)?\/?(?:\?.*)?$/i';
    const USERNAME_REGEX = '/^[a-zA-Z][a-zA-Z0-9\-_]$/';
    const PHONE_NUMBER = '/^[0-9]{3}-[0-9]{3}-[0-9]{4}$/';

    protected $regex = "";

    protected $message;

    public function __construct($regex, $errorMessage = null)
    {
        $this->regex = $regex;
        $this->message = $errorMessage;
    }

    /**
     * @inheritdoc
     */
    function validate($value)
    {
        return preg_match($this->regex, $value);
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