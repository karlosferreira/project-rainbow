<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 24/4/18
 * Time: 3:45 PM
 */

namespace Apps\Core_MobileApi\Api\Exception;


/**
 * Class PermissionErrorException
 * ERROR CODE: 300 - 399
 * @package Apps\Core_MobileApi\Api\Exception
 */
class PermissionErrorException extends ErrorException
{
    public function __construct($message = "", $code = 0, $previous = null)
    {
        $this->code = ($code != 0 ? $code : self::PERMISSION_DENIED);
        parent::__construct($message, $code, $previous);
    }

}