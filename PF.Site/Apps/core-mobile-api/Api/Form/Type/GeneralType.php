<?php

namespace Apps\Core_MobileApi\Api\Form\Type;


use Apps\Core_MobileApi\Adapter\Localization\LocalizationInterface;
use Apps\Core_MobileApi\Adapter\Localization\PhpfoxLocalization;
use Apps\Core_MobileApi\Adapter\Setting\PhpfoxSetting;
use Apps\Core_MobileApi\Adapter\Setting\SettingInterface;
use Apps\Core_MobileApi\Api\Exception\ErrorException;
use Apps\Core_MobileApi\Api\Form\Validator\ValidateInterface;

class GeneralType implements FormTypeInterface
{
    const REQUIRED_FIELD_ERROR = 'this_field_is_required';

    protected $name;

    /**
     * @var string default is Text Input
     */
    protected $componentName = 'input';

    /**
     * @var array Key Value control attributes
     */
    protected $attrs = [];

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @var ValidateInterface[]
     */
    protected $validators = [];

    protected $multiple;

    /**
     * @var LocalizationInterface
     */
    protected $local;

    protected $error;

    protected $errorPhraseParams;

    protected $section;

    /**
     * @var SettingInterface
     */
    protected $setting;

    /**
     * Get Structure
     *
     * @param $trans LocalizationInterface
     *
     * @return array Form file structure
     */
    function getStructure(LocalizationInterface $trans = null)
    {
        if ($trans == null) {
            $trans = $this->getLocal();
        }
        $structure = [
            'name'           => $this->getName(),
            'component_name' => $this->getComponentName(),
        ];

        if ($this->multiple !== null) {
            $structure['multiple'] = $this->multiple;
        }
        if ($this->isRequiredField()) {
            $structure['required'] = true;
        }

        if ($this->getValue() !== null) {
            $structure['value'] = $this->getValue();
        }
        $translatable = ['label', 'placeholder', 'description'];
        foreach ((array)$this->getAttrs() as $key => $value) {
            if (!isset($this->getServerAttributes()[$key])) {
                $structure[$key] = (in_array($key, $translatable) ? $trans->translate($value) : $value);
            }
        }

        // Dev parameters
        if ($this->getAttr('metadata')) {
            $structure['metadata'] = $this->getMetadata();
        }

        return $structure;
    }

    function getComponentName()
    {
        return $this->componentName;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get field title for display
     *
     * @return mixed|null
     */
    public function getTitle()
    {
        if ($this->hasAttr('label')) {
            return $this->getLocal()
                ->translate($this->getAttr('label'));
        } else {
            return $this->getName();
        }
    }

    /**
     * @param mixed $name
     *
     * @return GeneralType
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param array $attrs
     *
     * @return GeneralType
     */
    public function setAttrs($attrs)
    {
        if ($attrs) {
            foreach ($attrs as $key => $value) {
                $this->attrs[$key] = $value;
            }
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getAttrs()
    {
        return $this->attrs;
    }

    /**
     * Set attribute
     *
     * @param $key
     * @param $value
     *
     * @return $this
     */
    public function setAttr($key, $value)
    {
        $this->attrs[$key] = $value;
        return $this;
    }

    /**
     * Get Attribute
     *
     * @param       $key
     * @param mixed $default default value
     *
     * @return mixed|null
     */
    public function getAttr($key, $default = null)
    {
        return (isset($this->attrs[$key]) ? $this->attrs[$key] : $default);
    }

    public function hasAttr($attr)
    {
        return isset($this->attrs[$attr]);
    }

    /**
     * @param mixed $value
     * @param bool $isPost
     * @return GeneralType
     */
    public function setValue($value, $isPost = false)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Get current field value
     * If value not set, value_default will be used instead
     *
     * @return mixed
     */
    public function getValue()
    {
        if ($this->value === null && $this->hasAttr('value_default')) {
            return $this->getAttr('value_default');
        }
        return $this->value;
    }

    public function getValueDefault()
    {
        return ($this->hasAttr('value_default') ? $this->getAttr('value_default') : null);
    }

    /**
     * @param ValidateInterface[] $validators
     *
     * @return GeneralType
     * @throws ErrorException
     */
    public function setValidators($validators)
    {
        if (empty($validators)) {
            $validators = [];
        }
        if (!is_array($validators)) {
            throw new ErrorException("Validators must be a array of ValidateInterface");
        }
        $this->validators = $validators;
        return $this;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        if (!empty($this->error)) {
            return false;
        }

        $valid = true;
        if ($this->validators) {
            foreach ($this->validators as $validator) {
                if (!$validator->validate($this->value)) {
                    $valid = false;
                    if (empty($this->error) && method_exists($validator, 'getError')) {
                        $fieldErrorMessage = $validator->getError();
                        if (isset($fieldErrorMessage) && $fieldErrorMessage != '') {
                            $this->error = $validator->getError();
                        }
                    }
                }
            }
        }

        if ($this->isRequiredField() && ($this->getValue() === null || $this->getValue() === "")) {
            $valid = false;
        }
        return $valid;
    }

    public function isRequiredField()
    {
        return ($this->getAttr('required') == true ? true : false);
    }

    /**
     * @param $local
     *
     * @return $this
     */
    public function setLocal($local)
    {
        $this->local = $local;
        return $this;
    }

    /**
     * Get localization interface for translate and other local purpose
     *
     * @return LocalizationInterface
     */
    public function getLocal()
    {
        if ($this->local == null) {
            $this->local = new PhpfoxLocalization();
        }
        return $this->local;
    }

    /**
     * @return SettingInterface
     */
    public function getSetting()
    {
        if ($this->setting == null) {
            $this->setting = new PhpfoxSetting();
        }
        return $this->setting;
    }

    /**
     * @param SettingInterface $setting
     */
    public function setSetting($setting)
    {
        $this->setting = $setting;
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Get error message for displaying to client
     *
     * @return mixed
     */
    public function getErrorMessage()
    {
        if (empty($this->error)) {
            return trim($this->getLocal()->translate('field_name_field_is_invalid', [
                'field_name' => $this->getTitle(),
            ]), '.');
        }
        return trim($this->getLocal()->translate($this->error, !empty($this->errorPhraseParams) ? $this->errorPhraseParams : []), '.');
    }

    /**
     * @param mixed $error
     */
    public function setError($error, $errorPhraseParams = [])
    {
        $this->error = $error;
        $this->errorPhraseParams = $errorPhraseParams;
    }

    /**
     * @return ValidateInterface[]
     */
    public function getValidators()
    {
        if (!is_array($this->validators)) {
            $this->validators = [];
        }
        return $this->validators;
    }

    /**
     * Get field Meta data for development purpose
     *
     * @return array
     */
    public function getMetadata()
    {
        return [
            'description'          => $this->getMetaDescription(),
            'value_format'         => $this->getMetaValueFormat(),
            'validators'           => array_map(function ($validator) {
                $reflect = new \ReflectionClass($validator);
                return $reflect->getShortName();
            }, $this->getValidators()),
            'available_attributes' => $this->getAvailableAttributes(),
            'server_attributes'    => $this->getServerAttributes(),
        ];
    }

    public function getAvailableAttributes()
    {
        return [
            'label',
            'placeholder',
            'description',
            'returnKeyType',
            'value',
            'required',
        ];
    }

    public function getMetaDescription()
    {
        return 'General Text Field';
    }

    public function getMetaValueFormat()
    {
        return 'Text';
    }

    public function getServerAttributes()
    {
        return ['value_default' => $this->getAttr('value_default')];
    }

    /**
     * @return mixed
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * @param mixed $section
     *
     * @return $this
     */
    public function setSection($section)
    {
        $this->section = $section;
        return $this;
    }
}