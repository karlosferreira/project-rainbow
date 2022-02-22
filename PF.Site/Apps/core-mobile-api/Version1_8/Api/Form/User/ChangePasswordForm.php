<?php

namespace Apps\Core_MobileApi\Version1_8\Api\Form\User;

use Apps\Core_MobileApi\Api\Form\GeneralForm;
use Apps\Core_MobileApi\Api\Form\Type\HiddenType;
use Apps\Core_MobileApi\Api\Form\Type\PasswordType;
use Apps\Core_MobileApi\Api\Form\Type\SubmitType;

class ChangePasswordForm extends GeneralForm
{
    private $passOldPassword = false;

    private $requestId = '';

    /**
     * @return mixed|void
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     */
    public function buildForm()
    {
        if (!$this->passOldPassword) {
            $this->addField('old_password', PasswordType::class, [
                'label'       => 'old_password',
                'placeholder' => 'enter_password',
                'required'    => true
            ]);
        }
        $this->addField('request_id', HiddenType::class, [
            'value' => $this->requestId
        ]);
        $this
            ->addField('new_password', PasswordType::class, [
                'label'       => 'new_password',
                'placeholder' => 'enter_password',
                'required'    => true
            ])
            ->addField('confirm_password', PasswordType::class, [
                'label'       => 'confirm_password',
                'placeholder' => 'enter_password',
                'required'    => true
            ])
            ->addField('submit', SubmitType::class, [
                'label' => 'submit'
            ]);
    }

    /**
     * @param bool $passOldPassword
     */
    public function setPassOldPassword($passOldPassword)
    {
        $this->passOldPassword = $passOldPassword;
    }

    /**
     * @param string $requestId
     */
    public function setRequestId($requestId)
    {
        $this->requestId = $requestId;
    }

}