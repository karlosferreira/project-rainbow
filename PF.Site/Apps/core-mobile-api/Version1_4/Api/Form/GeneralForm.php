<?php

namespace Apps\Core_MobileApi\Version1_4\Api\Form;

use Apps\Core_MobileApi\Api\Form\Type\GeneralType;

abstract class GeneralForm extends \Apps\Core_MobileApi\Api\Form\GeneralForm
{
    protected $step = 1;
    protected $nextStep = 1;
    protected $submitButton;
    protected $cancelButton;

    protected $isMultiStep = false;


    /**
     * Generate form Structure
     * @return array
     */
    public function getFormStructure()
    {
        $this->isPost = false;

        if (!$this->isBuild) {
            $this->buildForm();
            $this->buildValues();
        }
        $schema = [
            'title'         => $this->getLocal()->translate($this->title),
            'description'   => $this->getLocal()->translate($this->description),
            'action'        => $this->getAction(),
            'method'        => ($this->method ? $this->method : "post"),
            'multipleSteps' => $this->isMultiStep(),
            'currentStep'   => $this->step,
            'nextStep'      => $this->nextStep,
            'submitButton'  => $this->submitButton,
            'cancelButton'  => $this->cancelButton
        ];

        /**
         * @var GeneralType $section
         * @var GeneralType $field
         */
        foreach ($this->sections as $sectionName => $section) {
            if (!empty($section['label'])) {
                $schema['sections'][$sectionName]['label'] = $this->getLocal()->translate($section['label']);
            }
            if (!empty($section['fields'])) {
                $fields = [];
                foreach ($section['fields'] as $name => $field) {
                    $fields[$name] = $field->getStructure($this->local);
                }
                $schema['sections'][$sectionName]['fields'] = $fields;
            }
        }

        /** @var GeneralType $field */
        foreach ($this->fields as $name => $field) {
            if ($field->getSection() == null) {
                $schema['fields'][$name] = $field->getStructure($this->local);
            }
        }
        if ($this->request && $this->request->isGet() && $this->request->get('help')) {
            $schema['help'] = $this->getHelpInformation();
        }
        return $schema;
    }

    /**
     * @return bool
     */
    public function isMultiStep()
    {
        return $this->isMultiStep;
    }

    /**
     * @param bool $isMultiStep
     */
    public function setIsMultiStep($isMultiStep)
    {
        $this->isMultiStep = $isMultiStep;
    }

    /**
     * @return int
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * @param int $step
     */
    public function setStep($step)
    {
        $this->step = $step;
    }

    /**
     * @param int $nextStep
     */
    public function setNextStep($nextStep)
    {
        $this->nextStep = $nextStep;
    }

    /**
     * @param mixed $submitButton
     */
    public function setSubmitButton($submitButton)
    {
        $this->submitButton = $submitButton;
    }

    /**
     * @param mixed $cancelButton
     */
    public function setCancelButton($cancelButton)
    {
        $this->cancelButton = $cancelButton;
    }

}