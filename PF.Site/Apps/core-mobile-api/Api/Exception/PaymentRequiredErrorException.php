<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 24/4/18
 * Time: 2:55 PM
 */

namespace Apps\Core_MobileApi\Api\Exception;


/**
 * Class NotFoundErrorException
 * ERROR CODE: 404
 * @package Apps\Core_MobileApi\Api\Exception
 */
class PaymentRequiredErrorException extends ErrorException
{
    public function __construct($message = "", $code = 0, $previous = null)
    {
        if ($code === 0) {
            $this->code = self::PAYMENT_REQUIRED;
        }
        parent::__construct($message, $code, $previous);
    }

}