<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 20/6/18
 * Time: 4:29 PM
 */

namespace Apps\Core_MobileApi\Version1_7_3\Api\Form\User;

use Apps\Core_MobileApi\Api\Form\Type\ChoiceType;
use Apps\Core_MobileApi\Api\Form\Type\ClickableType;
use Apps\Core_MobileApi\Api\Form\Type\EmailType;
use Apps\Core_MobileApi\Api\Form\Type\HiddenType;
use Apps\Core_MobileApi\Api\Form\Type\PhoneNumberType;
use Apps\Core_MobileApi\Api\Form\Type\SubmitType;
use Apps\Core_MobileApi\Api\Form\Type\TextType;
use Apps\Core_MobileApi\Api\Form\Validator\EmailValidator;
use Apps\Core_MobileApi\Api\Form\Validator\RequiredValidator;
use Apps\Core_MobileApi\Api\Form\Validator\StringLengthValidator;

class AccountSettingForm extends \Apps\Core_MobileApi\Version1_4\Api\Form\User\AccountSettingForm
{
    protected $gateways;

    /**
     * Override build form to generate form
     *
     * @return mixed|void
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\ValidationErrorException
     */
    public function buildForm()
    {
        $sectionName = 'account_setting';
        $this->addSection($sectionName, 'account_settings');
        $enablePhoneSignUp = $this->getSetting()->getAppSetting('core.enable_register_with_phone_number');
        if ($this->getSetting()->getAppSetting('user.split_full_name')) {
            $this->addField('first_name', TextType::class, [
                'label'       => 'first_name',
                'editable'    => !!$this->canChangeFullName,
                'description' => $this->fullNameDescription,
                'inline'      => true,
                'placeholder' => 'enter_first_name',
                'required'    => true
            ], [new RequiredValidator()], $sectionName)
                ->addField('last_name', TextType::class, [
                    'label'       => 'last_name',
                    'editable'    => !!$this->canChangeFullName,
                    'inline'      => true,
                    'placeholder' => 'enter_last_name',
                    'required'    => true
                ], [new RequiredValidator()], $sectionName);
        } else {
            $this->addField('full_name', TextType::class, [
                'label'       => 'full_name',
                'editable'    => !!$this->canChangeFullName,
                'description' => $this->fullNameDescription,
                'inline'      => true,
                'placeholder' => 'enter_full_name',
                'required'    => true
            ], [new RequiredValidator(), new StringLengthValidator(1, $this->getSetting()->getAppSetting('user.maximum_length_for_full_name'))], $sectionName);
        }
        $this->addField('user_name', TextType::class, [
            'label'          => 'username',
            'editable'       => !!$this->canChangeUserName,
            'description'    => $this->userNameDescription,
            'inline'         => true,
            'placeholder'    => 'enter_username',
            'required'       => true,
            'autoCapitalize' => 'none'
        ], [new RequiredValidator()], $sectionName)
            ->addField('email', EmailType::class, [
                'label'       => 'email',
                'editable'    => $this->getSetting()->getUserSetting('user.can_change_email'),
                'description' => $this->getSetting()->getUserSetting('user.can_change_email') && $this->getSetting()->getAppSetting('user.verify_email_at_signup') ? $this->getLocal()->translate('changing_your_email_address_requires_you_to_verify_your_new_email') : '',
                'inline'      => true,
                'placeholder' => 'enter_email_address',
                'required'    => !$enablePhoneSignUp
            ], !$enablePhoneSignUp ? [new RequiredValidator(), new EmailValidator()] : [new EmailValidator()], $sectionName);
        if ($this->getSetting()->getAppSetting('core.enable_register_with_phone_number')) {
            $this->addField('phone_number', PhoneNumberType::class, [
                'label'       => 'phone_number',
                'description' => $this->getLocal()->translate('changing_your_phone_number_requires_you_to_verify_your_new_one'),
                'inline'      => true,
                'placeholder' => 'enter_phone_number'
            ], null, $sectionName);
        }
        $this
            ->addField('language_id', HiddenType::class, [
                'label' => 'primary_language'
            ], null, $sectionName);
        if ($this->getSetting()->getUserSetting('user.can_edit_currency')) {
            $this->addField('default_currency', ChoiceType::class, [
                'options'     => $this->getCurrencies(),
                'label'       => 'preferred_currency',
                'description' => $this->getLocal()->translate('show_prices_and_make_purchases_in_this_currency'),
            ], null, $sectionName);
        }
        $this->addField('time_zone', ChoiceType::class, [
            'options' => $this->getTimezones(),
            'label'   => 'time_zone',
        ], null, $sectionName);

        // Membership Field
        if ($this->getSetting()->isApp('Core_Subscriptions') && $this->getSetting()->getAppSetting('subscribe.enable_subscription_packages') && (int)\Phpfox::getUserBy('user_group_id') != 1) {
            $this->addMembershipPackageField(false, 'package', 'membership');
        }

        if ($this->getSetting()->getUserSetting('user.can_delete_own_account')) {
            $this->addSection('manage_account', 'manage_account')
                ->addField('cancel', ClickableType::class, [
                    'label'    => '',
                    'action'   => 'route',
                    'value'    => 'cancel_account',
                    'severity' => 'danger',
                    'params'   => [
                        'routeName' => 'formEditItem',
                        'params'    => [
                            'module_name'   => 'user',
                            'resource_name' => 'user',
                            'formType'      => 'cancelAccount',
                            'formName'      => 'formEditItem'
                        ]
                    ]
                ], null, 'manage_account');
        }
        $this->addGatewayField();
        $this->addField('submit', SubmitType::class, [
            'label' => 'update'
        ]);
    }
}