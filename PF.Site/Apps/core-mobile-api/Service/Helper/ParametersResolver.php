<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 23/4/18
 * Time: 1:52 PM
 */

namespace Apps\Core_MobileApi\Service\Helper;


use Apps\Core_MobileApi\Api\ApiRequestInterface;
use Apps\Core_MobileApi\Api\Exception\ErrorException;
use Apps\Core_MobileApi\Api\Exception\ValidationErrorException;
use Apps\Core_MobileApi\Api\Form\Validator\Filter\TextFilter;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\FileBag;

class ParametersResolver
{
    protected $request;

    private $defined;
    private $required;
    private $default;
    private $missing;
    private $parameters;

    private $resolved;
    private $invalidTypes;
    private $invalidValues;

    private $allowedTypes = [];
    private $allowedValues = [];
    private $validators = [];

    public function __construct(ApiRequestInterface $request)
    {
        $this->request = $request;
        $this->defined = [];
        $this->required = [];
        $this->missing = [];
        $this->parameters = [];
        $this->default = [];
        $this->invalidValues = [];
        $this->invalidTypes = [];
    }

    /**
     * Resolve parameters
     *
     * @param array $params request data
     *
     * @return $this ;
     * @internal param $defined
     * @internal param array $required
     */
    public function resolve($params = null)
    {
        // Merge defined, required and default
        $this->defined = array_merge($this->defined, $this->required);
        foreach ($this->default as $key => $value) {
            if (!in_array($key, $this->defined)) {
                $this->defined[] = $key;
            }
        }
        if ($params === null) {
            $params = $this->request->getRequests();
        }

        foreach ($this->defined as $param) {
            $this->setParameter($param, (isset($params[$param]) && $params[$param] !== '' ? $params[$param]
                : (isset($this->default[$param]) ? $this->default[$param] : null)));
        }

        foreach ($this->required as $requireParam) {
            // Not allow null and empty value for required params
            if (!in_array($requireParam, $this->defined)
                || $this->parameters[$requireParam] === null
                || $this->parameters[$requireParam] === "") {
                $this->missing[] = $requireParam;
            }
        }
        $this->resolved = true;
        return $this;
    }

    /**
     * Setter parameters and control SQL injection
     *
     * @param      $param
     * @param      $value
     * @param null $default
     */
    public function setParameter($param, $value, $default = null)
    {
        if (empty($value) && $value === null) {
            $value = $default;
        }
        if (!empty($value) && ((empty($this->allowedTypes[$param]) || $this->allowedTypes[$param] == 'string') && is_string($value))) {
            $value = TextFilter::secureText($value);
        }
        $this->parameters[$param] = $value;
    }

    /**
     * Resolve single parameter
     *
     * @param       $params
     * @param       $key
     * @param null  $allowedTypes
     * @param array $validator
     * @param null  $default
     *
     * @return mixed|null
     * @throws ErrorException
     * @throws ValidationErrorException
     */
    public function resolveSingle($params, $key, $allowedTypes = null, $validator = [], $default = null)
    {
        $this->setDefined([$key])->resolve($params);
        if (!empty($allowedTypes)) {
            $this->setAllowedTypes($key, $allowedTypes, $validator);
        }

        if ($this->isValid()) {
            return (isset($this->getParameters()[$key]) && $this->getParameters()[$key] !== '') ? ($this->getParameters()[$key]) : $default;
        }
        throw new ValidationErrorException();
    }

    /**
     * Resolve Integer ID
     *
     * @param $params
     *
     * @return mixed
     */
    public function resolveId($params)
    {
        $id = $this->resolveSingle($params, 'id', 'int', ['min' => 1]);
        return $id;
    }

    /**
     * Get parameters after resolved the request
     * @return array
     */
    public function getParameters()
    {
        if ($this->resolved == false) {
            $this->resolve($this->request->getRequests());
        }
        return $this->parameters;
    }

    /**
     * Get defined parameters for current API and return its values
     * @return array
     * @codeCoverageIgnore
     * @todo not used anywhere.
     */
    public function getDefined()
    {
        return $this->defined;
    }

    /**
     * Get missing required parameters. Use for validation purpose
     * @return array
     */
    public function getMissing()
    {
        return $this->missing;
    }

    /**
     * @param ApiRequestInterface $request
     *
     * @return ParametersResolver
     */
    public static function createResolver(ApiRequestInterface $request)
    {
        return new self($request);
    }

    /**
     * Defined array request parameters is supported by the API
     *
     * @param array $defined
     *
     * @return ParametersResolver
     */
    public function setDefined($defined)
    {
        if (!is_array($defined)) {
            $defined = [$defined];
        }
        $this->defined = $defined;
        return $this;
    }

    /**
     * @param string       $name
     * @param array|string $types     supported int, string, array
     * @param array        $validator validate min/max value or param
     *
     * @return $this
     */
    public function setAllowedTypes($name, $types, $validator = [])
    {
        $this->allowedTypes[$name] = is_array($types) ? $types : [$types];
        if (!empty($validator)) {
            $this->validators[$name] = $validator;
        }
        if (!in_array($name, $this->defined)) {
            $this->defined[] = $name;
        }
        return $this;

    }

    public function setAllowedValues($name, $values)
    {
        $this->allowedValues[$name] = $values;
        if (!in_array($name, $this->defined)) {
            $this->defined[] = $name;
        }
        return $this;
    }

    /**
     * Defined required parameters
     *
     * @param array $required
     *
     * @return ParametersResolver
     */
    public function setRequired($required)
    {
        if (!is_array($required)) {
            $required = [$required];
        }
        $this->required = $required;
        return $this;
    }

    /**
     * Check is missing params
     * @return bool
     */
    public function isMissing()
    {
        return !empty($this->missing);
    }

    /**
     * Check request parameters is valid or not
     * @return bool
     * @throws ErrorException
     */
    public function isValid()
    {
        $isValidType = $this->isValidTypes();
        $isValidValue = $this->isValidValues();
        return (!$this->isMissing() && $isValidType && $isValidValue);
    }

    public function isValidTypes()
    {
        $valid = true;
        // Check type
        foreach ($this->allowedTypes as $name => $types) {
            if (empty($this->parameters[$name])) {
                continue;
            }
            foreach ($types as $type) {
                $validateMethod = "is_$type";
                if ($type == "int") {
                    if (!(is_numeric($this->parameters[$name]))) {
                        $this->invalidTypes[] = $name;
                        $valid = false;
                    }
                } else if (function_exists($validateMethod)) {
                    if (!($validateMethod($this->parameters[$name]))) {
                        $this->invalidTypes[] = $name;
                        $valid = false;
                    }

                } else {
                    throw new ErrorException("Un-supported type '$type' because 'is_$type' function doesn't exists");
                }
            }
        }
        if ($valid) {
            // Validate MIN and MAX value
            foreach ($this->validators as $name => $validator) {
                if (empty($this->parameters[$name])) {
                    // If empty and not required, No need to validate, The value is valid
                    if (isset($this->required[$name])) {
                        $valid = false;
                        $this->invalidTypes[] = $name;
                    }
                } else {
                    if (!empty($validator['min']) && $this->parameters[$name] < $validator['min']) {
                        $valid = false;
                        $this->invalidTypes[] = $name;
                    }
                    if (!empty($validator['max']) && $this->parameters[$name] > $validator['max']) {
                        $valid = false;
                        $this->invalidTypes[] = $name;
                    }
                }
            }
        }

        return $valid;
    }

    public function isValidValues()
    {
        $valid = true;
        foreach ($this->allowedValues as $name => $values) {
            if (empty($this->parameters[$name])) {
                continue;
            }
            if (!in_array($this->parameters[$name], $values)) {
                $this->invalidValues[] = $name;
                $valid = false;
            }
        }
        return $valid;
    }

    public function clearConfigure()
    {
        $this->defined = [];
        $this->required = [];
        $this->missing = [];
        $this->parameters = [];
        $this->default = [];
        $this->resolved = false;
        $this->invalidTypes = [];
        $this->invalidValues = [];
        $this->allowedValues = [];
        $this->allowedTypes = [];
        $this->validators = [];

        return $this;
    }

    /**
     * Set default parameters
     *
     * @param array $default key value pair
     *
     * @return ParametersResolver
     */
    public function setDefault($default)
    {
        $this->default = $default;
        return $this;
    }

    public function getInvalidParameters()
    {
        return array_merge($this->invalidValues, $this->invalidTypes, $this->missing);
    }

    /**
     * @return FileBag
     * @codeCoverageIgnore
     * @todo not used anywhere.
     */
    public function getFiles()
    {
        return $this->request->getFiles();
    }

    /**
     * @param $key
     *
     * @return UploadedFile
     */
    public function getFile($key)
    {
        return $this->request->getFile($key);
    }

}