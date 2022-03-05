<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 22/6/18
 * Time: 10:11 AM
 */

namespace Apps\Core_MobileApi\Api\Form\User;


use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\Form\GeneralForm;
use Apps\Core_MobileApi\Api\Form\Type\EmailType;
use Apps\Core_MobileApi\Api\Form\Type\SubmitType;

class ForgetPasswordRequest extends GeneralForm
{
    /**
     * @return mixed|void
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     */
    public function buildForm()
    {
        $this->setTitle('password_request')
            ->setAction(UrlUtility::makeApiUrl('user/password/request'))
            ->setMethod('put')
            ->addField('email', EmailType::class, [
                'label'       => 'email',
                'autoFocus'   => true,
                'placeholder' => 'enter_email_address',
                'required'    => true,
                'inline'      => true
            ])
            ->addField('submit', SubmitType::class, [
                'label' => 'request_new_password'
            ]);
    }
}