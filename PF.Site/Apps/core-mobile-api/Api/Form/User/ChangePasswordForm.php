<?php

namespace Apps\Core_MobileApi\Api\Form\User;

use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\Form\GeneralForm;
use Apps\Core_MobileApi\Api\Form\Type\PasswordType;
use Apps\Core_MobileApi\Api\Form\Type\SubmitType;

class ChangePasswordForm extends GeneralForm
{
    private $passOldPassword = false;

    /**
     * @return mixed|void
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     */
    public function buildForm()
    {
        $this->setTitle('change_password')
            ->setMethod('put')
            ->setAction(UrlUtility::makeApiUrl('account/password'));
        if (!$this->passOldPassword) {
            $this->addField('old_password', PasswordType::class, [
                'label'       => 'old_password',
                'placeholder' => 'enter_password',
                'required'    => true
            ]);
        }
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


}