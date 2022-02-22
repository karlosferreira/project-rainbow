<?php


namespace Apps\Core_MobileApi\Api\Form\Page;

use Apps\Core_MobileApi\Api\Form\GeneralForm;
use Apps\Core_MobileApi\Api\Form\Type\SubmitType;
use Apps\Core_MobileApi\Api\Form\Type\TextareaType;

class PageInfoForm extends GeneralForm
{
    protected $action = "page_info";

    /**
     * @param null  $options
     * @param array $data
     *
     * @return mixed|void
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     */
    function buildForm($options = null, $data = [])
    {
        $this
            ->addField('text', TextareaType::class, [
                'label'       => 'info',
                'placeholder' => 'type_something_dot'
            ])
            ->addField('submit', SubmitType::class, [
                'label' => 'update',
            ]);
    }
}