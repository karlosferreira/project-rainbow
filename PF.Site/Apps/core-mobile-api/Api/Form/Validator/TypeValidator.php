<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 14/6/18
 * Time: 1:11 PM
 */

namespace Apps\Core_MobileApi\Api\Form\Validator;


use Apps\Core_MobileApi\Api\Exception\ErrorException;

class TypeValidator implements ValidateInterface
{
    const IS_NUMERIC = 'numeric';
    const IS_STRING = 'string';
    const IS_ARRAY = 'array';
    const IS_INTEGER = 'integer';
    const IS_ARRAY_NUMERIC = 'array_numeric';
    const IS_ARRAY_STRING = 'array_string';

    protected $messageError;
    protected $type;

    /**
     * TypeValidator constructor.
     *
     * @param      $type
     * @param null $error
     *
     * @throws ErrorException
     */
    public function __construct($type, $error = null)
    {
        $this->type = $type;
        $this->messageError = $error;

        $supportedTypes = [self::IS_ARRAY, self::IS_ARRAY_NUMERIC, self::IS_NUMERIC, self::IS_STRING, self::IS_ARRAY_STRING, self::IS_INTEGER];

        if (!in_array($this->type, $supportedTypes)) {
            throw new ErrorException('TypeValidator only supports ' . implode(", ", $supportedTypes) . " types");
        }
    }

    /**
     * Validate value is valid or not.
     * If value is null or empty it return true
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
        $isValid = true;
        switch ($this->type) {
            case self::IS_INTEGER:
                $isValid = is_numeric($value);
                if ($isValid) {
                    $isValid = (string)intval($value) == (string)$value;
                }
                break;
            case self::IS_NUMERIC:
                $isValid = is_numeric($value);
                break;
            case self::IS_STRING:
                $isValid = is_string($value);
                break;
            case self::IS_ARRAY:
                $isValid = is_array($value);
                break;
            case self::IS_ARRAY_STRING:
                $isValid = is_array($value);
                if ($isValid) {
                    foreach ($value as $v) {
                        if (!is_string($v)) {
                            $isValid = false;
                            break;
                        }
                    }
                }
                break;
            case self::IS_ARRAY_NUMERIC:
                $isValid = is_array($value);
                if ($isValid) {
                    foreach ($value as $v) {
                        if (!is_numeric($v)) {
                            $isValid = false;
                            break;
                        }
                    }
                }
                break;

        }

        return $isValid;
    }

    /**
     * Get error message
     * @return string
     */
    function getError()
    {
        return $this->messageError;
    }
}