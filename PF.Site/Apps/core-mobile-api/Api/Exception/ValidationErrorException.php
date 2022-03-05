<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 24/4/18
 * Time: 4:19 PM
 */

namespace Apps\Core_MobileApi\Api\Exception;


/**
 * Class ValidationErrorException
 * Code start form: 200 - 299
 * @package Apps\Core_MobileApi\Api\Exception
 */
class ValidationErrorException extends ErrorException
{
    protected $validationDetail;

    public function __construct($message = "", $code = 0, $previous = null, $validationDetail = null)
    {
        $this->code = ($code != 0 ? $code : self::INVALID_REQUEST_PARAMETERS);
        $this->validationDetail = $validationDetail;
        parent::__construct($message, $code, $previous);
    }

    public function getResponse($errorData = null)
    {
        $response = parent::getResponse();
        if ($this->validationDetail) {
            $response['validation_detail'] = $this->validationDetail;
        }
        return $response;
    }

}