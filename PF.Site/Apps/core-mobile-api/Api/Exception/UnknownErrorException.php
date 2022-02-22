<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 24/4/18
 * Time: 3:40 PM
 */

namespace Apps\Core_MobileApi\Api\Exception;


class UnknownErrorException extends ErrorException
{

    public function __construct($message = "", $code = 0, $previous = null)
    {
        $this->code = ($code != 0 ? $code : self::UNKNOWN_ERROR);
        parent::__construct($message, $code, $previous);
    }
}