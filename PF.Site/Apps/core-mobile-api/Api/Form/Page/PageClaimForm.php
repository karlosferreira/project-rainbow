<?php


namespace Apps\Core_MobileApi\Api\Form\Page;

use Apps\Core_MobileApi\Api\Form\GeneralForm;
use Apps\Core_MobileApi\Api\Form\Type\SubmitType;
use Apps\Core_MobileApi\Api\Form\Type\TextareaType;

class PageClaimForm extends GeneralForm
{
    protected $action = "page_claim";

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
            ->addField('message', TextareaType::class, [
                'label'    => 'message',
                'required' => true
            ])
            ->addField('submit', SubmitType::class, [
                'label' => 'send_request',
            ]);
    }
}