<?php

namespace Apps\Core_MobileApi\Api\Form\Report;

use Apps\Core_MobileApi\Api\Form\GeneralForm;
use Apps\Core_MobileApi\Api\Form\Type\ChoiceType;
use Apps\Core_MobileApi\Api\Form\Type\HiddenType;
use Apps\Core_MobileApi\Api\Form\Type\SubmitType;
use Apps\Core_MobileApi\Api\Form\Type\TextareaType;

class ReportForm extends GeneralForm
{
    private $reasonOptions;

    /**
     * @return mixed|void
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     */
    function buildForm()
    {
        $this->addField('item_id', HiddenType::class, [
            'required' => true
        ])
            ->addField('item_type', HiddenType::class, [
                'required' => true
            ])
            ->addField('reason', ChoiceType::class, [
                'options'  => $this->getReasonOptions(),
                'label'    => 'reason',
                'value'    => "1",
                'required' => true
            ])
            ->addField('feedback', TextareaType::class, [
                'label'       => 'a_comment_optional',
                'placeholder' => 'write_a_comment'
            ])
            ->addField('submit', SubmitType::class, [
                'label' => 'submit'
            ]);
    }

    public function getReasonOptions()
    {
        $options = [];
        foreach ($this->reasonOptions as $reason) {
            $options[] = [
                'label' => ucfirst($this->getLocal()->translate($reason['message'])),
                'value' => $reason['report_id']
            ];
        }

        return $options;
    }

    /**
     * @param mixed $reasonOptions
     */
    public function setReasonOptions($reasonOptions)
    {
        $this->reasonOptions = $reasonOptions;
    }
}