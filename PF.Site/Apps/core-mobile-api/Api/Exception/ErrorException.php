<?php

namespace Apps\Core_MobileApi\Api\Exception;


/**
 * Class ErrorException
 * Holding general API call errors
 * @package Apps\Core_MobileApi\Api\Exception
 */
class ErrorException extends \Exception
{
    const API_SESSION = 102;
    const API_UNKNOWN = 1;
    const API_SERVICE = 2;
    const API_TOO_MANY_CALLS = 4;

    const VALIDATION_ERROR = 201;
    const INVALID_REQUEST_PARAMETERS = 202;

    const PERMISSION_DENIED = 301;
    const TEMPORARY_BLOCKED = 368;

    const UNAUTHORIZED = 401;
    const PAYMENT_REQUIRED = 402;
    const RESOURCE_NOT_FOUND = 404;

    const DUPLICATE_POST = 506;

    const ACCESS_TOKEN_HAS_EXPIRED = 190;
    const INVALID_ACCESS_TOKEN = 191;

    const UNKNOWN_ERROR = 777;

    /**
     * Describe how to fix this issue
     * @var string
     */
    protected $describe;

    /**
     * Track ID use for checking log
     * @var string
     */
    protected $traceId;

    public function __construct($message = "", $code = 0, $previous = null)
    {
        parent::__construct($message, $code, $previous);
        if ($message == "") {
            $this->getDescribe($this->code);
        }
        $this->traceId = md5($this->getTraceAsString());
    }

    public function getResponse($errorData = null)
    {
        $error = [
            'message'         => html_entity_decode($this->message, ENT_QUOTES),
            'type'            => $this->getErrorType(),
            'code'            => $this->getCode(),
            'support_message' => html_entity_decode($this->describe, ENT_QUOTES),
            'tracer'          => $this->traceId
        ];
        if ($errorData) {
            $error['error_data'] = $errorData;
        }
        if (PHPFOX_DEBUG) {
            $error['tracer_string'] = $this->getTraceAsString();
        }
        return $error;
    }

    protected function getDescribe($code)
    {
        $describe = "";
        switch ($code) {
            case self::API_SESSION:
                $message = _p('api_session');
                break;
            case self::API_UNKNOWN:
                $message = _p('api_unknown');
                break;
            case self::API_SERVICE:
                $message = _p('aoi_service_unavailable');
                break;
            case self::API_TOO_MANY_CALLS:
                $message = _p('api_too_many_calls');
                break;
            case self::PERMISSION_DENIED:
                $message = _p('permission_denied');
                break;
            case self::DUPLICATE_POST:
                $message = _p('duplicate_post');
                break;
            case self::TEMPORARY_BLOCKED:
                $message = _p('temporarily_blocked_for_policies_violations');
                $describe = _p('wait_and_retry_the_operation');
                break;
            case self::RESOURCE_NOT_FOUND:
                $message = _p('item_not_found');
                break;
            case self::ACCESS_TOKEN_HAS_EXPIRED:
                $message = _p('access_token_has_expired');
                break;
            case self::INVALID_ACCESS_TOKEN:
                $message = _p('invalid_access_token');
                break;
            case self::VALIDATION_ERROR:
                $message = _p('api_request_validation_error');
                break;
            case self::INVALID_REQUEST_PARAMETERS:
                $message = _p('invalid_api_request_parameters');
                break;
            case self::UNAUTHORIZED:
                $message = _p('unauthorized');
                break;
            case self::PAYMENT_REQUIRED:
                $message = _p('payment_required');
                break;
            default:
                $message = _p('unknown_error');
        }
        $this->message = $message;
        $this->describe = $describe;
    }

    /**
     * @param string $describe
     *
     * @todo not used anywhere.
     * @codeCoverageIgnore
     */
    public function setDescribe($describe)
    {
        $this->describe = $describe;
    }

    protected function getErrorType()
    {
        $class = new \ReflectionClass(static::class);
        return $class->getShortName();
    }

    /**
     * @param string $message
     *
     * @todo not used anywhere.
     * @codeCoverageIgnore
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

}