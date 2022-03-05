<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 22/6/18
 * Time: 10:11 AM
 */

namespace Apps\Core_MobileApi\Version1_8\Api\Form\User;


use Apps\Core_MobileApi\Api\Form\GeneralForm;
use Apps\Core_MobileApi\Api\Form\Type\EmailType;
use Apps\Core_MobileApi\Api\Form\Type\PhoneNumberType;
use Apps\Core_MobileApi\Api\Form\Type\RadioType;
use Apps\Core_MobileApi\Api\Form\Type\SubmitType;

class ForgetPasswordRequest extends GeneralForm
{
    protected $isUsePhone = null;
    /**
     * @return mixed|void
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     */
    public function buildForm()
    {
        if ($this->getSetting()->getAppSetting('core.enable_register_with_phone_number')) {
            $this->addField('email', EmailType::class, [
                'label'           => 'email',
                'placeholder'     => 'enter_email_address',
                'required'        => !$this->isUsePhone(),
                'ignore_validate' => $this->isUsePhone(),
                'inline'          => true,
                'hidden_by'       => 'use_phone'
            ]);
            $this->addField('phone', PhoneNumberType::class, [
                'label'           => 'phone_upper',
                'placeholder'     => 'enter_phone_number',
                'required'        => $this->isUsePhone() === null || $this->isUsePhone(),
                'ignore_validate' => !$this->isUsePhone(),
                'inline'          => true,
                'hidden_by'       => '!use_phone'
            ]);
            $this->addField('use_phone', RadioType::class, [
                'options'       => [
                    [
                        'value' => 0,
                        'label' => $this->getLocal()->translate('use_your_email_address')
                    ],
                    [
                        'value' => 1,
                        'label' => $this->getLocal()->translate('use_your_phone_number')
                    ]
                ],
                'label'         => '',
                'required'      => true,
                'value_default' => 0
            ]);
        } else {
            $this->addField('email', EmailType::class, [
                'label'       => 'email',
                'autoFocus'   => true,
                'placeholder' => 'enter_email_address',
                'required'    => true,
                'inline'      => true
            ]);
        }
        $this->addField('submit', SubmitType::class, [
                'label' => 'request_new_password'
            ]);
    }

    /**
     * @return bool
     */
    public function isUsePhone()
    {
        return $this->isUsePhone;
    }

    /**
     * @param bool $isUsePhone
     */
    public function setIsUsePhone($isUsePhone)
    {
        $this->isUsePhone = $isUsePhone;
    }
}